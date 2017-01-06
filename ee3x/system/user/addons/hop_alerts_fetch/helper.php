<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD.'hop_alerts_fetch/helper_settings.php';
require_once PATH_THIRD.'hop_alerts_fetch/helper_wmata.php';
require_once PATH_THIRD.'hop_alerts_fetch/helper_twitter.php';
require_once PATH_THIRD.'hop_alerts_fetch/helper_rss.php';

class HAF_helper
{

	public static function create_alert_entry($title, $type, $custom_id, $content, $timestamp = NULL)
	{
		ee()->load->library('logger');
		ee()->load->helper('url');

		$channel_id = HAF_settings_helper::get_alerts_channel_id();
		if ($channel_id == 0)
		{
			ee()->logger->developer('HAF: channel id parameter not set !');
			return FALSE;
		}
		$field_id_type = HAF_settings_helper::get_field_id_alert_type();
		if ($field_id_type == 0)
		{
			ee()->logger->developer('HAF: field id type parameter not set !');
			return FALSE;
		}
		$field_id_ext_id = HAF_settings_helper::get_field_id_alert_ext_id();
		if ($field_id_ext_id == 0)
		{
			ee()->logger->developer('HAF: field id external id parameter not set !');
			return FALSE;
		}
		$field_id_body = HAF_settings_helper::get_field_id_alert_body();
		if ($field_id_body == 0)
		{
			ee()->logger->developer('HAF: field id body parameter not set !');
			return FALSE;
		}

		// override the current session with a new "special" one with generic account
		$member_id = intval(HAF_settings_helper::get_setting('alert_member_id'));
		if ($member_id == 0)
		{
			ee()->logger->developer('HAF: member id parameter not set !');
			return FALSE;
		}
		$member = ee('Model')->get('Member', $member_id)->with('MemberGroup')->first();

		// Override current session with predefined member
		$tmp_member_id = ee()->session->userdata['member_id'];
		$tmp_group_id = ee()->session->userdata['group_id'];
		ee()->session->userdata['group_id'] = $member->MemberGroup->getId();
		ee()->session->userdata['member_id'] = $member->member_id;

		// TODO : check uniqid
		$url_title = uniqid(url_title($title));

		ee()->load->library('api');
		ee()->legacy_api->instantiate('channel_entries');
		ee()->legacy_api->instantiate('channel_fields');

		// Creating a new entry
		$data = array(
			'title'							=> $title,
			'status'						=> 'open',
			'field_id_'.$field_id_type		=> $type, // Type
			'field_id_'.$field_id_ext_id	=> $custom_id, // Custom Id
			'field_id_'.$field_id_body		=> $content,
		);
		
		$type_to_setting = array(
			'bus'			=> 'cat_id_bus',
			'rail'			=> 'cat_id_rail',
			'car'			=> 'cat_id_car',
			'train_marc'	=> 'cat_id_train_marc',
			'train_vavre'	=> 'cat_id_train_vavre',
		);

		// Check if the type is valid
		if (!array_key_exists($type, $type_to_setting))
		{
			ee()->logger->developer('HAF: tried to create an entry with wrong type: '.$type);
			return FALSE;
		}

		$cat_id_setting_name = $type_to_setting[$type];
		$cat_id = intval(HAF_settings_helper::get_setting($cat_id_setting_name));
		if ($cat_id != 0)
		{
			$data['category'] = array($cat_id);
		}

		if ($timestamp != NULL)
		{
			$data['entry_date'] = $timestamp;
		}

		ee()->api_channel_fields->setup_entry_settings($channel_id, $data);

		$success = ee()->api_channel_entries->save_entry($data, $channel_id);

		if ($success)
		{
			ee()->logger->developer('HAF: Created entry "'.$title.'"');

			// $entry_id = ee()->api_channel_entries->entry_id;
		}
		else
		{
			ee()->logger->developer('HAF: Creating entry "'.$title.'" failed');
		}

		// Set the current session back to normal
		ee()->session->userdata['group_id'] = $tmp_group_id;
		ee()->session->userdata['member_id'] = $tmp_member_id;

		return $success;
	}
}