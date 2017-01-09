<?php

class HAF_RSS_helper
{
	/*
		For trains, we get data from RSS feeds.
		Parsing of the feed is done in the api class
	*/

	private $rss_api;

	public function __construct()
	{
		require_once PATH_THIRD.'hop_alerts_fetch/api/rss_api.php';

		$this->rss_api = new RSS_api();
	}

	public function update_alerts()
	{
		$refresh_delay = HAF_settings_helper::get_time_refresh();
		$now = ee()->localize->now;
		$last_train_marc_update = HAF_settings_helper::get_setting('last_update_train_marc');
		$last_train_vavre_update = HAF_settings_helper::get_setting('last_update_train_vavre');
		$last_bus_art_update = HAF_settings_helper::get_setting('last_update_bus_art');
		$last_bus_montgomery_rideon_update = HAF_settings_helper::get_setting('last_update_bus_montgomery_rideon');
		if ($last_train_marc_update != NULL && $last_train_marc_update != '')
		{
			$last_train_marc_update = intval($last_train_marc_update);
			if ($now - $last_train_marc_update > ($refresh_delay))
			{
				$this->_update_train_marc_alerts();
			}
		}
		else
		{
			$this->_update_train_marc_alerts();
		}

		if ($last_train_vavre_update != NULL && $last_train_vavre_update != '')
		{
			$last_train_vavre_update = intval($last_train_vavre_update);
			if ($now - $last_train_vavre_update > ($refresh_delay))
			{
				$this->_update_train_vavre_alerts();
			}
		}
		else
		{
			$this->_update_train_vavre_alerts();
		}

		if ($last_bus_art_update != NULL && $last_bus_art_update != '')
		{
			$last_bus_art_update = intval($last_bus_art_update);
			if ($now - $last_bus_art_update > ($refresh_delay))
			{
				$this->_update_bus_art_alerts();
			}
		}
		else
		{
			$this->_update_bus_art_alerts();
		}

		if ($last_bus_montgomery_rideon_update != NULL && $last_bus_montgomery_rideon_update != '')
		{
			$last_bus_montgomery_rideon_update = intval($last_bus_montgomery_rideon_update);
			if ($now - $last_bus_montgomery_rideon_update > ($refresh_delay))
			{
				$this->_update_bus_montgomery_rideon_alerts();
			}
		}
		else
		{
			$this->_update_bus_montgomery_rideon_alerts();
		}
	}

	private function _update_train_marc_alerts()
	{
		$channel_id = HAF_settings_helper::get_alerts_channel_id();
		$channel_id_expired = HAF_settings_helper::get_alerts_channel_id_expired();
		$field_id_type = HAF_settings_helper::get_field_id_alert_type();
		$field_id_ext_id = HAF_settings_helper::get_field_id_alert_ext_id();
		$entries_res = ee('Model')->get('ChannelEntry')
			->filter('channel_id', $channel_id)
			->filter('status', 'IN', array('open'))
			->filter('field_id_'.$field_id_type, 'train_marc')
			->order('entry_date', 'DESC')
			->all();

		$entries = array();
		foreach ($entries_res as $entry_res)
		{
			$entries[$entry_res->{'field_id_'.$field_id_ext_id}] = $entry_res;
		}

		$results = $this->rss_api->get_marc_train_updates();

		if (is_array($results) && count($results) > 0)
		{
			foreach ($results as $incident)
			{
				if (array_key_exists($incident['guid'], $entries))
				{
					// nothing to do, entry is present and alert is still going
					// Remove it from the entries array
					unset($entries[$incident['guid']]);
				}
				else
				{
					$title = 'MARC Train: '.$incident['title'];
					
					// Datetime in GMT, EE stores it in GMT/UTC, so we're good
					$dt = new DateTime($incident['date']);
					// Create a new entry with alert data
					HAF_helper::create_alert_entry($title, 'train_marc', $incident['guid'], $incident['desc'], $dt->format('U'));
				}
			}

			foreach ($entries as $entry)
			{
				// Close remaining entries, as that means they're not going anymore
				$entry->status = 'closed';
				$entry->channel_id = $channel_id_expired;
				$entry->save();
			}

			// I'm not sure how the feed is cleaned up, it seems that old/not valid alerts are removed from the feed.
			// That means old entries will be automatically closed so we should be fine

			HAF_settings_helper::save_setting('last_update_train_marc', ee()->localize->now);
		}
	}

	private function _update_train_vavre_alerts()
	{
		$channel_id = HAF_settings_helper::get_alerts_channel_id();
		$channel_id_expired = HAF_settings_helper::get_alerts_channel_id_expired();
		$field_id_type = HAF_settings_helper::get_field_id_alert_type();
		$field_id_ext_id = HAF_settings_helper::get_field_id_alert_ext_id();
		$time_expired = HAF_settings_helper::get_time_expired();
		$entries_res = ee('Model')->get('ChannelEntry')
			->filter('channel_id', $channel_id)
			->filter('status', 'IN', array('open'))
			->filter('field_id_'.$field_id_type, 'train_vavre')
			->order('entry_date', 'DESC')
			->all();

		$entries = array();
		foreach ($entries_res as $entry_res)
		{
			$entries[$entry_res->{'field_id_'.$field_id_ext_id}] = $entry_res;
		}

		$results = $this->rss_api->get_vavre_train_updates();

		if (is_array($results) && count($results) > 0)
		{
			foreach ($results as $incident)
			{
				if (array_key_exists($incident['guid'], $entries))
				{
					// nothing to do, entry is present and alert is still going
					// Remove it from the entries array
					unset($entries[$incident['guid']]);
				}
				else
				{
					// TODO : Check age of alert, if to old, not insert ?

					$title = 'VAVRE Train: '.$incident['title'];
					
					// Datetime has timezone in it (YAY !)
					$dt = new DateTime($incident['date']);
					// Not in UTC, so we'll convert it
					$dt->setTimezone(new DateTimeZone('UTC'));
					// Create a new entry with alert data
					HAF_helper::create_alert_entry($title, 'train_vavre', $incident['guid'], $incident['desc'], $dt->format('U'));
				}
			}

			foreach ($entries as $entry)
			{
				// Close remaining entries, as that means they're not going anymore
				$entry->status = 'closed';
				$entry->channel_id = $channel_id_expired;
				$entry->save();
			}

			// The feed is definitely not cleaned up and show old/not accurate info.
			// No idea how to clean those.

			// In doubt, clean all older than setting defined
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

			HAF_settings_helper::save_setting('last_update_train_vavre', ee()->localize->now);
		}
	}

	private function _update_bus_art_alerts()
	{
		$channel_id = HAF_settings_helper::get_alerts_channel_id();
		$channel_id_expired = HAF_settings_helper::get_alerts_channel_id_expired();
		$field_id_type = HAF_settings_helper::get_field_id_alert_type();
		$field_id_ext_id = HAF_settings_helper::get_field_id_alert_ext_id();
		$entries_res = ee('Model')->get('ChannelEntry')
			->filter('channel_id', $channel_id)
			->filter('status', 'IN', array('open'))
			->filter('field_id_'.$field_id_type, 'bus_art')
			->order('entry_date', 'DESC')
			->all();

		$entries = array();
		foreach ($entries_res as $entry_res)
		{
			$entries[$entry_res->{'field_id_'.$field_id_ext_id}] = $entry_res;
		}

		$results = $this->rss_api->get_art_bus_updates();

		if (is_array($results) && count($results) > 0)
		{
			foreach ($results as $incident)
			{
				if (array_key_exists($incident['guid'], $entries))
				{
					// nothing to do, entry is present and alert is still going
					// Remove it from the entries array
					unset($entries[$incident['guid']]);
				}
				else
				{
					$title = 'ART Bus: '.$incident['title'];
					
					// Datetime has timezone in it (YAY !)
					$dt = new DateTime($incident['date']);
					// Not in UTC, so we'll convert it
					$dt->setTimezone(new DateTimeZone('UTC'));
					// Create a new entry with alert data
					HAF_helper::create_alert_entry($title, 'bus_art', $incident['guid'], $incident['desc'], $dt->format('U'));
				}
			}

			foreach ($entries as $entry)
			{
				// Close remaining entries, as that means they're not going anymore
				$entry->status = 'closed';
				$entry->channel_id = $channel_id_expired;
				$entry->save();
			}

			// I'm not sure how the feed is cleaned up, it seems that old/not valid alerts are removed from the feed.
			// That means old entries will be automatically closed so we should be fine

			HAF_settings_helper::save_setting('last_update_bus_art', ee()->localize->now);
		}
	}

	private function _update_bus_montgomery_rideon_alerts()
	{
		$channel_id = HAF_settings_helper::get_alerts_channel_id();
		$channel_id_expired = HAF_settings_helper::get_alerts_channel_id_expired();
		$field_id_type = HAF_settings_helper::get_field_id_alert_type();
		$field_id_ext_id = HAF_settings_helper::get_field_id_alert_ext_id();
		$entries_res = ee('Model')->get('ChannelEntry')
			->filter('channel_id', $channel_id)
			->filter('status', 'IN', array('open'))
			->filter('field_id_'.$field_id_type, 'bus_montgomery_rideon')
			->order('entry_date', 'DESC')
			->all();

		$entries = array();
		foreach ($entries_res as $entry_res)
		{
			$entries[$entry_res->{'field_id_'.$field_id_ext_id}] = $entry_res;
		}

		$results = $this->rss_api->get_montgomery_rideon_bus_updates();

		if (is_array($results) && count($results) > 0)
		{
			foreach ($results as $incident)
			{
				if (array_key_exists($incident['guid'], $entries))
				{
					// nothing to do, entry is present and alert is still going
					// Remove it from the entries array
					unset($entries[$incident['guid']]);
				}
				else
				{
					$title = 'Montgomery RideOn: '.$incident['title'];
					
					// Datetime has timezone in it (YAY !)
					$dt = new DateTime($incident['date']);
					// Not in UTC, so we'll convert it
					$dt->setTimezone(new DateTimeZone('UTC'));
					// Create a new entry with alert data
					HAF_helper::create_alert_entry($title, 'bus_montgomery_rideon', $incident['guid'], $incident['desc'], $dt->format('U'));
				}
			}

			foreach ($entries as $entry)
			{
				// Close remaining entries, as that means they're not going anymore
				$entry->status = 'closed';
				$entry->channel_id = $channel_id_expired;
				$entry->save();
			}

			// I'm not sure how the feed is cleaned up, it seems that old/not valid alerts are removed from the feed.
			// That means old entries will be automatically closed so we should be fine

			HAF_settings_helper::save_setting('last_update_bus_montgomery_rideon', ee()->localize->now);
		}
	}
}