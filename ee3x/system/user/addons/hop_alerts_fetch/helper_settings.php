<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD.'hop_alerts_fetch/config.php';

/**
 * General Helper class for the add-on
 * 
 */
class HAF_settings_helper
{
	private static $_settings_table_name = "hop_alerts_fetch_settings";
	private static $_settings;

	private static function _get_default_settings()
	{
		return array(
			'alerts_channel_id'					=> 0, // Channel id of the alerts entries
			'alerts_expired_channel_id'			=> 0, // Channel id of where to put the expired alerts
			'field_id_alert_type'				=> 0,
			'field_id_alert_ext_id'				=> 0,
			'field_id_alert_body'				=> 0,
			'alert_member_id'					=> 0, // Member to use when creating a new Alert entry
			'cat_id_bus'						=> 0, // category id for the Bus alerts
			'cat_id_rail'						=> 0,
			'cat_id_car'						=> 0,
			'cat_id_train_marc'					=> 0,
			'cat_id_train_vavre'				=> 0,
			'time_refresh'						=> (5*60), // Delay in seconds before refreshing results
			'time_expired'						=> (60*60*12), //Delay in seconds to set an entry as expired, default 12 hours, used for some alert types (not all)
			'wmata_api_key'						=> '', // Api key of wmata api
			'twitter_oauth_access_token'		=> '',
			'twitter_oauth_access_token_secret' => '',
			'twitter_consumer_key'				=> '',
			'twitter_consumer_secret'			=> '',
			'last_update_bus'					=> 1, // last update of the bus alerts in sec (timestamp)
			'last_update_rail'					=> 1, // last update of the rail alerts in sec (timestamp)
			'last_update_car'					=> 1,
			'last_update_train_marc'			=> 1,
			'last_update_train_vavre'			=> 1,
		);
	}

	/**
	 * Get settings saved into DB; if no settings found, get default ones.
	 * @return array Settings
	 */
	public static function get_settings()
	{
		if (! isset(self::$_settings) || self::$_settings == null)
		{
			$settings = array();
			//Get the actual saved settings
			$query = ee()->db->get(self::$_settings_table_name);
			foreach ($query->result_array() as $row)
			{
				$settings[$row["setting_name"]] = $row["setting_value"];
			}
			self::$_settings = array_merge(self::_get_default_settings(), $settings);
		}
		return self::$_settings;
	}

	/**
	 * Get one unique setting
	 * @param  string $setting_name [description]
	 * @return string|null		  [description]
	 */
	public static function get_setting($setting_name)
	{
		if (! isset(self::$_settings))
		{
			//Load the settings from DB if not already done
			self::get_settings();
		}
		if (array_key_exists($setting_name, self::$_settings))
		{
			return self::$_settings[$setting_name];
		}
		return null;
	}

	public static function get_alerts_channel_id()
	{
		$channel_id = self::get_setting('alerts_channel_id');
		if ($channel_id != '')
		{
			return intval($channel_id);
		}
		return 0;
	}

	public static function get_alerts_channel_id_expired()
	{
		$channel_id = self::get_setting('alerts_expired_channel_id');
		if ($channel_id != '')
		{
			return intval($channel_id);
		}
		return 0;
	}

	public static function get_field_id_alert_type()
	{
		$field_id = self::get_setting('field_id_alert_type');
		if ($field_id != '')
		{
			return intval($field_id);
		}
		return 0;
	}

	public static function get_field_id_alert_ext_id()
	{
		$field_id = self::get_setting('field_id_alert_ext_id');
		if ($field_id != '')
		{
			return intval($field_id);
		}
		return 0;
	}

	public static function get_field_id_alert_body()
	{
		$field_id = self::get_setting('field_id_alert_body');
		if ($field_id != '')
		{
			return intval($field_id);
		}
		return 0;
	}

	public static function get_time_refresh()
	{
		$time = self::get_setting('time_refresh');
		if ($time != '')
		{
			return intval($time);
		}
		else
		{
			$default = self::_get_default_settings();
			return $default['time_refresh'];
		}
	}

	public static function get_time_expired()
	{
		$time = self::get_setting('time_expired');
		if ($time != '')
		{
			return intval($time);
		}
		else
		{
			$default = self::_get_default_settings();
			return $default['time_expired'];
		}
	}

	public static function get_wmata_api_key()
	{
		$api_key = self::get_setting('wmata_api_key');
		if ($api_key != '')
		{
			return $api_key;
		}
		return 0;
	}

	/**
	 * Save Add-on settings into database
	 * @param  array  $settings [description]
	 * @return array			[description]
	 */
	public static function save_settings($settings = array())
	{
		//be sure to save all settings possible
		$_tmp_settings = array_merge(self::_get_default_settings(), $settings);
		//No way to do INSERT IF NOT EXISTS so...
		foreach ($_tmp_settings as $setting_name => $setting_value)
		{
			$query = ee()->db->get_where(self::$_settings_table_name, array('setting_name'=>$setting_name), 1, 0);
			if ($query->num_rows() == 0) {
			  // A record does not exist, insert one.
			  $query = ee()->db->insert(self::$_settings_table_name, array('setting_name' => $setting_name, 'setting_value' => $setting_value));
			} else {
			  // A record does exist, update it.
			  $query = ee()->db->update(self::$_settings_table_name, array('setting_value' => $setting_value), array('setting_name'=>$setting_name));
			}
		}
		self::$_settings = $_tmp_settings;
	}

	/**
	 * Save a single setting into database (will override if exists)
	 * @param  [type] $setting_name  [description]
	 * @param  [type] $setting_value [description]
	 * @return [type]				[description]
	 */
	public static function save_setting($setting_name, $setting_value)
	{
		$query = ee()->db->get_where(self::$_settings_table_name, array('setting_name'=>$setting_name), 1, 0);
		if ($query->num_rows() == 0) {
		  // A record does not exist, insert one.
		  $query = ee()->db->insert(self::$_settings_table_name, array('setting_name' => $setting_name, 'setting_value' => $setting_value));
		} else {
		  // A record does exist, update it.
		  $query = ee()->db->update(self::$_settings_table_name, array('setting_value' => $setting_value), array('setting_name'=>$setting_name));
		}

		//Refresh our local copy of the settings
		self::$_settings = null;
		self::get_settings();
	}
}