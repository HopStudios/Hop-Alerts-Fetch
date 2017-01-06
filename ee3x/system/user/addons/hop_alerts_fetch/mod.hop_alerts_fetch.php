<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD.'hop_alerts_fetch/helper.php';

class Hop_alerts_fetch
{
	/**
	 * The tag {exp:hop_alerts_fetch:update_all} is processing the current URL as a 404 one
	 * This tag is to be placed into the 404 template
	 **/

	function update_all()
	{
		// Update Rail and Bus
		
		$wmata_h = new HAF_Wmata_helper();
		$wmata_h->update_alerts();

		$twitter_h = new HAF_Twitter_helper();
		$twitter_h->update_alerts();

		$rss_h = new HAF_RSS_helper();
		$rss_h->update_alerts();
	}
}