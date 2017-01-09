<?php

class HAF_Twitter_helper
{
	/*
		We fetch traffic updates from a Twitter account https://twitter.com/WTOPtraffic

		Each tweet will become an alert entry
		We have no clue about when to close them though...
	*/


	private $twitter_api;

	public function __construct()
	{
		require_once PATH_THIRD.'hop_alerts_fetch/api/twitter_api.php';

		$this->twitter_api = new Twitter_api(
			HAF_settings_helper::get_setting('twitter_oauth_access_token'),
			HAF_settings_helper::get_setting('twitter_oauth_access_token_secret'),
			HAF_settings_helper::get_setting('twitter_consumer_key'),
			HAF_settings_helper::get_setting('twitter_consumer_secret')
		);

		ee()->load->library('logger');
	}

	public function update_alerts()
	{
		$refresh_delay = HAF_settings_helper::get_time_refresh();
		$now = ee()->localize->now;
		$last_car_update = HAF_settings_helper::get_setting('last_update_car');
		$last_update_bus_dc_circulator = HAF_settings_helper::get_setting('last_update_bus_dc_circulator');

		if ($last_car_update != NULL && $last_car_update != '')
		{
			$last_car_update = intval($last_car_update);
			if ($now - $last_car_update > ($refresh_delay))
			{
				$this->_update_car_alerts();
			}
		}
		else
		{
			$this->_update_car_alerts();
		}

		if ($last_update_bus_dc_circulator != NULL && $last_update_bus_dc_circulator != '')
		{
			$last_update_bus_dc_circulator = intval($last_update_bus_dc_circulator);
			if ($now - $last_update_bus_dc_circulator > ($refresh_delay))
			{
				$this->_update_bus_dc_circulator_alerts();
			}
		}
		else
		{
			$this->_update_bus_dc_circulator_alerts();
		}

	}

	private function _update_car_alerts()
	{
		$channel_id = HAF_settings_helper::get_alerts_channel_id();
		$channel_id_expired = HAF_settings_helper::get_alerts_channel_id_expired();
		$field_id_type = HAF_settings_helper::get_field_id_alert_type();
		$field_id_ext_id = HAF_settings_helper::get_field_id_alert_ext_id();
		$time_expired = HAF_settings_helper::get_time_expired();
		$entries_res = ee('Model')->get('ChannelEntry')
			->filter('channel_id', $channel_id)
			->filter('status', 'IN', array('open'))
			->filter('field_id_'.$field_id_type, 'car')
			->order('entry_date', 'DESC')
			->all();

		$entries = array();
		foreach ($entries_res as $entry_res)
		{
			$entries[$entry_res->{'field_id_'.$field_id_ext_id}] = $entry_res;
		}

		$results = json_decode($this->twitter_api->get_traffic_tweets());
		if ($results == NULL)
		{
			if (HAF_settings_helper::get_debug())
			{
				ee()->logger->developer('HAF: json received for car is not valid');
			}
			return;
		}

		if (is_array($results) && count($results) > 0)
		{
			foreach ($results as $tweet)
			{
				if (array_key_exists($tweet->id_str, $entries))
				{
					// nothing to do, entry is present and alert is still going
					// Remove it from the entries array
					unset($entries[$tweet->id_str]);
				}
				else
				{
					$title = 'Traffic: ';
					if (strlen($tweet->text) > 50)
					{
						$title .= substr($tweet->text, 0, 47) . '...';
					}
					else
					{
						$title .= $tweet->text;
					}

					$dt = new DateTime($tweet->created_at);
					// Twitter gives datetime including timezone (YAY !)
					// Might not be always in UTC, so we'll convert it, just in case
					$dt->setTimezone(new DateTimeZone('UTC'));
					// Create a new entry with alert data
					HAF_helper::create_alert_entry($title, 'car', $tweet->id_str, $tweet->text, $dt->format('U'));
				}
			}

			// Handle remaining opened entries
			// There's no real way to tell if still active or not
			// For now, just close those 12 hours old
			// entry_date is GMT, needs to be converted to locale
			$now = ee()->localize->now;
			foreach ($entries as $entry)
			{
				if ( ( $now - intval(ee()->localize->format_date('%U', $entry->entry_date)) ) > $time_expired )
				{
					$entry->status = 'closed';
					$entry->channel_id = $channel_id_expired;
					$entry->save();
				}
			}

			HAF_settings_helper::save_setting('last_update_car', ee()->localize->now);
		}
		
	}

	private function _update_bus_dc_circulator_alerts()
	{
		
		$channel_id = HAF_settings_helper::get_alerts_channel_id();
		$channel_id_expired = HAF_settings_helper::get_alerts_channel_id_expired();
		$field_id_type = HAF_settings_helper::get_field_id_alert_type();
		$field_id_ext_id = HAF_settings_helper::get_field_id_alert_ext_id();
		$time_expired = HAF_settings_helper::get_time_expired();
		$entries_res = ee('Model')->get('ChannelEntry')
			->filter('channel_id', $channel_id)
			->filter('status', 'IN', array('open'))
			->filter('field_id_'.$field_id_type, 'bus_dc_circulator')
			->order('entry_date', 'DESC')
			->all();

		$entries = array();
		foreach ($entries_res as $entry_res)
		{
			$entries[$entry_res->{'field_id_'.$field_id_ext_id}] = $entry_res;
		}

		$results = json_decode($this->twitter_api->get_dc_circulator_tweets());
		if ($results == NULL)
		{
			if (HAF_settings_helper::get_debug())
			{
				ee()->logger->developer('HAF: json received for DC Circulator is not valid');
			}
			return;
		}

		if (is_array($results) && count($results) > 0)
		{
			foreach ($results as $tweet)
			{
				if (array_key_exists($tweet->id_str, $entries))
				{
					// nothing to do, entry is present and alert is still going
					// Remove it from the entries array
					unset($entries[$tweet->id_str]);
				}
				else
				{
					$title = 'DC Circulator: ';
					if (strlen($tweet->text) > 50)
					{
						$title .= substr($tweet->text, 0, 47) . '...';
					}
					else
					{
						$title .= $tweet->text;
					}

					$dt = new DateTime($tweet->created_at);
					// Twitter gives datetime including timezone (YAY !)
					// Might not be always in UTC, so we'll convert it, just in case
					$dt->setTimezone(new DateTimeZone('UTC'));
					// Create a new entry with alert data
					HAF_helper::create_alert_entry($title, 'bus_dc_circulator', $tweet->id_str, $tweet->text, $dt->format('U'));
				}
			}

			// Handle remaining opened entries
			// There's no real way to tell if still active or not
			// For now, just close those 12 hours old
			// entry_date is GMT, needs to be converted to locale
			$now = ee()->localize->now;
			foreach ($entries as $entry)
			{
				if ( ( $now - intval(ee()->localize->format_date('%U', $entry->entry_date)) ) > $time_expired )
				{
					$entry->status = 'closed';
					$entry->channel_id = $channel_id_expired;
					$entry->save();
				}
			}

			HAF_settings_helper::save_setting('last_update_bus_dc_circulator', ee()->localize->now);
		}
		
	}

}