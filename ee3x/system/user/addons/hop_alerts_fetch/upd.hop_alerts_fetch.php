<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD.'hop_alerts_fetch/helper_settings.php';

class Hop_alerts_fetch_upd {

	var $version = HOP_ALERTS_FETCH_VERSION;

	function install()
	{
		ee()->load->dbforge();

		$data = array(
			'module_name' =>  ucfirst('hop_alerts_fetch') ,
			'module_version' => $this->version,
			'has_cp_backend' => 'y',
			'has_publish_fields' => 'n'
		);

		ee()->db->insert('modules', $data);

		//Create our tables

		$fields = array(
			'setting_name'   => array('type' => 'VARCHAR', 'constraint' => '100'),
			'setting_value'  => array('type' => 'TEXT')
		);

		ee()->dbforge->add_field($fields);
		ee()->dbforge->add_key('setting_name', TRUE);

		ee()->dbforge->create_table('hop_alerts_fetch_settings');

		HAF_settings_helper::save_settings();
		
		unset($fields);

		return TRUE;
	}

	function uninstall()
	{
		ee()->load->dbforge();

		ee()->db->select('module_id');
		$query = ee()->db->get_where('modules', array('module_name' =>  ucfirst('hop_alerts_fetch')));

		ee()->db->where('module_id', $query->row('module_id'));
		ee()->db->delete('module_member_groups');

		ee()->db->where('module_name', 'hop_alerts_fetch');
		ee()->db->delete('modules');

		ee()->db->where('class', 'hop_alerts_fetch');
		ee()->db->delete('actions');

		//Uninstall our tables here
		ee()->dbforge->drop_table('hop_alerts_fetch_settings');

		return TRUE;
	}

	function update($current = '')
	{
		ee()->load->dbforge();

		if (version_compare($current, '0.0.4', '='))
		{
			return FALSE;
		}

		/*
		if (version_compare($current, '2.0', '<'))
		{
			// Do your update code here
		}
		*/

		return TRUE;
	}

}