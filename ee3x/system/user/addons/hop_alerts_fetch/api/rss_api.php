<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class RSS_api
{
	public function __construct()
	{

	}

	public function get_marc_train_updates()
	{
		ee()->load->library('logger');
		$url = 'http://mtamarylandalerts.com/rss.aspx?ma';

		$rss_feed = $this->get_rss_content($url);

		if ($rss_feed == '')
		{
			if (HAF_settings_helper::get_debug()) {
				ee()->logger->developer('HAF: RSS Feed of MARC train empty');
			}
			return null;
		}

		// Parse that thing to retrieve meaningful content
		$rss = new DOMDocument();
		$result = $rss->loadXML($rss_feed);

		if ($result === FALSE)
		{
			if (HAF_settings_helper::get_debug()) {
				ee()->logger->developer('HAF: Error parsing RSS Feed of MARC train');
			}
			return null;
		}

		$items = array();
		foreach ($rss->getElementsByTagName('item') as $node) {
			$item = array ( 
				'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
				'desc' => $node->getElementsByTagName('description')->item(0)->nodeValue,
				'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
				'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
				'guid' => $node->getElementsByTagName('guid')->item(0)->nodeValue,
			);
			$items[] = $item;
		}

		return $items;
	}

	public function get_vavre_train_updates()
	{
		$url = 'https://public.govdelivery.com/accounts/VAVRE/feed.rss';

		$rss_feed = $this->get_rss_content($url);

		if ($rss_feed == '')
		{
			if (HAF_settings_helper::get_debug()) {
				ee()->logger->developer('HAF: RSS Feed of VRE train empty');
			}
			return null;
		}

		// Parse that thing to retrieve meaningful content
		$rss = new DOMDocument();
		$result = $rss->loadXML($rss_feed);

		if ($result === FALSE)
		{
			if (HAF_settings_helper::get_debug()) {
				ee()->logger->developer('HAF: Error parsing RSS Feed of VRE train');
			}
			return null;
		}

		$items = array();
		foreach ($rss->getElementsByTagName('item') as $node) {
			$item = array ( 
				'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
				'desc' => $node->getElementsByTagName('description')->item(0)->nodeValue,
				'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
				'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
				'guid' => $node->getElementsByTagName('guid')->item(0)->nodeValue,
			);
			$items[] = $item;
		}

		return $items;
	}

	private function get_rss_content($url, $parameters = array())
	{

		if (!empty($parameters))
		{
			$query = http_build_query($parameters);
			$url .= '?' . $query;
		}

		$ch = curl_init();

		// curl_setopt($ch, CURLOPT_HTTPHEADER, array('api_key: ' . $this->api_key));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);

		/* Execute cURL, Return Data */
		$data = curl_exec($ch);

		/* Check HTTP Code */
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		// print_r($data);

		if ($status == 200)
		{
			//We got our data, YAY !
			return $data;
		}
		else
		{
			//Error
			// echo '<h2>Error WMATA API</h2>';
			// print_r($data);
			return $data;
		}
	}
}