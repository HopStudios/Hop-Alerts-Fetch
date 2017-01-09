<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use EllisLab\ExpressionEngine\Library\CP\Table;

require_once PATH_THIRD.'hop_alerts_fetch/helper_settings.php';

class hop_alerts_fetch_mcp
{
	function _build_nav()
	{
		$sidebar = ee('CP/Sidebar')->make();
		$sidebar->addHeader(lang('settings'), ee('CP/URL', 'addons/settings/hop_alerts_fetch/settings'));
	}

	function index()
	{
		$this->_build_nav();

		$vars = array();

		return array(
			'heading'		=> lang('home'),
			'body'			=> ee('View')->make('hop_alerts_fetch:index')->render($vars),
			'breadcrumb'	=> array(
			  ee('CP/URL', 'addons/settings/hop_alerts_fetch')->compile() => lang('hop_alerts_fetch_module_name')
			),
		);
	}

	function settings()
	{
		$this->_build_nav();

		$settings = HAF_settings_helper::get_settings();
	
		$vars = array(
			'cp_page_title' => lang('nav_settings'),
			'base_url' => ee('CP/URL', 'addons/settings/hop_alerts_fetch/settings')->compile(),
			'save_btn_text' => lang('settings_save'),
			'save_btn_text_working' => lang('settings_save_working'),
		);
		
		// Using EE3 API to create config form
		$vars['sections'] = array(
			array(
				array(
					'title' => 'alerts_channel_id',
					'desc' => 'alerts_channel_id_desc',
					'fields' => array(
						'alerts_channel_id' => array('type' => 'text', 'value' => $settings['alerts_channel_id'])
					)
				),
				array(
					'title' => 'alerts_expired_channel_id',
					'desc' => 'alerts_expired_channel_id_desc',
					'fields' => array(
						'alerts_expired_channel_id' => array('type' => 'text', 'value' => $settings['alerts_expired_channel_id'])
					)
				),
				array(
					'title' => 'field_id_alert_type',
					// 'desc' => 'field_id_alert_type_desc',
					'fields' => array(
						'field_id_alert_type' => array('type' => 'text', 'value' => $settings['field_id_alert_type'])
					)
				),
				array(
					'title' => 'field_id_alert_ext_id',
					// 'desc' => 'field_id_alert_ext_id_desc',
					'fields' => array(
						'field_id_alert_ext_id' => array('type' => 'text', 'value' => $settings['field_id_alert_ext_id'])
					)
				),
				array(
					'title' => 'field_id_alert_body',
					// 'desc' => 'field_id_alert_body_desc',
					'fields' => array(
						'field_id_alert_body' => array('type' => 'text', 'value' => $settings['field_id_alert_body'])
					)
				),
				array(
					'title' => 'alert_member_id',
					// 'desc' => 'alert_member_id_desc',
					'fields' => array(
						'alert_member_id' => array('type' => 'text', 'value' => $settings['alert_member_id'])
					)
				),
				array(
					'title' => 'cat_id_bus',
					// 'desc' => 'cat_id_bus_desc',
					'fields' => array(
						'cat_id_bus' => array('type' => 'text', 'value' => $settings['cat_id_bus'])
					)
				),
				array(
					'title' => 'cat_id_rail',
					// 'desc' => 'cat_id_rail_desc',
					'fields' => array(
						'cat_id_rail' => array('type' => 'text', 'value' => $settings['cat_id_rail'])
					)
				),
				array(
					'title' => 'cat_id_car',
					// 'desc' => 'cat_id_car_desc',
					'fields' => array(
						'cat_id_car' => array('type' => 'text', 'value' => $settings['cat_id_car'])
					)
				),
				array(
					'title' => 'cat_id_train_marc',
					// 'desc' => 'cat_id_train_marc_desc',
					'fields' => array(
						'cat_id_train_marc' => array('type' => 'text', 'value' => $settings['cat_id_train_marc'])
					)
				),
				array(
					'title' => 'cat_id_train_vavre',
					// 'desc' => 'cat_id_train_vavre_desc',
					'fields' => array(
						'cat_id_train_vavre' => array('type' => 'text', 'value' => $settings['cat_id_train_vavre'])
					)
				),
				array(
					'title' => 'cat_id_bus_art',
					// 'desc' => 'cat_id_bus_art_desc',
					'fields' => array(
						'cat_id_bus_art' => array('type' => 'text', 'value' => $settings['cat_id_bus_art'])
					)
				),
				array(
					'title' => 'cat_id_bus_montgomery_rideon',
					// 'desc' => 'cat_id_bus_art_desc',
					'fields' => array(
						'cat_id_bus_montgomery_rideon' => array('type' => 'text', 'value' => $settings['cat_id_bus_montgomery_rideon'])
					)
				),
				array(
					'title' => 'time_refresh',
					// 'desc' => 'time_refresh_desc',
					'fields' => array(
						'time_refresh' => array('type' => 'text', 'value' => $settings['time_refresh'])
					)
				),
				array(
					'title' => 'time_expired',
					// 'desc' => 'time_expired_desc',
					'fields' => array(
						'time_expired' => array('type' => 'text', 'value' => $settings['time_expired'])
					)
				),
				array(
					'title' => 'wmata_api_key',
					'desc' => 'wmata_api_key_desc',
					'fields' => array(
						'wmata_api_key' => array('type' => 'text', 'value' => $settings['wmata_api_key'])
					)
				),
				array(
					'title' => 'twitter_oauth_access_token',
					'desc' => 'twitter_oauth_access_token_desc',
					'fields' => array(
						'twitter_oauth_access_token' => array('type' => 'text', 'value' => $settings['twitter_oauth_access_token'])
					)
				),
				array(
					'title' => 'twitter_oauth_access_token_secret',
					'desc' => 'twitter_oauth_access_token_secret_desc',
					'fields' => array(
						'twitter_oauth_access_token_secret' => array('type' => 'text', 'value' => $settings['twitter_oauth_access_token_secret'])
					)
				),
				array(
					'title' => 'twitter_consumer_key',
					'desc' => 'twitter_consumer_key_desc',
					'fields' => array(
						'twitter_consumer_key' => array('type' => 'text', 'value' => $settings['twitter_consumer_key'])
					)
				),
				array(
					'title' => 'twitter_consumer_secret',
					'desc' => 'twitter_consumer_secret_desc',
					'fields' => array(
						'twitter_consumer_secret' => array('type' => 'text', 'value' => $settings['twitter_consumer_secret'])
					)
				),
				array(
					'title' => 'debug',
					'desc' => 'debug_desc',
					'fields' => array(
						'debug' => array('type' => 'inline_radio', 'choices' => array('yes' => 'yes', 'no' => 'no'), 'value' => $settings['debug'])
					)
				),
				array(
					'title' => '',
					'fields' => array(
						'action' => array('type' => 'hidden', 'value' => 'save_settings')
					)
				),
			)
		);
		
		if (ee()->input->post('action') == "save_settings")
		{
			$settings = array();
			$form_is_valid = TRUE;
			
			// Validation
			$validator = ee('Validation')->make();
			
			$validator->setRules(array(
			  
			));
			$result = $validator->validate($_POST);
			
			if ($result->isValid())
			{
				// Get back all values, store them in array and save them
				$fields = array();
				foreach ($vars['sections'] as $settings)
				{
					foreach ($settings as $setting)
					{
						foreach ($setting['fields'] as $field_name => $field)
						{
							$fields[$field_name] = ee()->input->post($field_name);
						}
					}
				}
				// We don't want to save that field, it's not a setting
				unset($fields['action']);
				//Save settings and redirect.
				HAF_settings_helper::save_settings($fields);
				
				ee('CP/Alert')->makeInline('shared-form')
						->asSuccess()
						->withTitle(lang('preferences_updated'))
						->addToBody(lang('preferences_updated_desc'))
						->defer();

				ee()->functions->redirect(ee('CP/URL', 'addons/settings/hop_alerts_fetch/settings')->compile());
			}
			else
			{
				$vars['errors'] = $result;
				ee('CP/Alert')->makeInline('shared-form')
					->asIssue()
					->withTitle(lang('settings_save_error'))
					->addToBody(lang('settings_save_error_desc'))
					->now();
				$vars["settings"] = $settings;
			}
			
		} // ENDIF save settings action
		
		// return ee()->load->view('settings', $vars, TRUE);
		return array(
			'heading'		=> lang('nav_settings'),
			'body'			=> ee('View')->make('hop_alerts_fetch:settings')->render($vars),
			'breadcrumb'	=> array(
			  ee('CP/URL', 'addons/settings/hop_alerts_fetch')->compile() => lang('hop_alerts_fetch_module_name')
			),
		);
	}
}
// END CLASS
