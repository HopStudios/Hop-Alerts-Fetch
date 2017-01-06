<?php

/**
 * Hop Alerts Fetch - Config
 *
 * NSM Addon Updater config file.
 *
 * @package		Hop Studios:Hop Alerts Fetch
 * @author		Hop Studios, Inc.
 * @copyright	Copyright (c) 2014, Hop Studios, Inc.
 * @link		http://www.hopstudios.com/software/
 * @version		0.0.1
 */

$config['name']='Hop Alerts Fetch';
$config['version']='0.0.1';
// $config['nsm_addon_updater']['versions_xml']='http://www.hopstudios.com/software/versions/';

// Version constant
if (!defined("HOP_ALERTS_FETCH_VERSION")) {
	define('HOP_ALERTS_FETCH_VERSION', $config['version']);
}
