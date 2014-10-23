<?php

class Plugin_placid extends Plugin {

	var $meta = array(
		'name' => 'Placid',
		'version' => '0.5.5',
		'author' => 'Alec Ritson',
		'author_url' => 'http://www.alecritson.co.uk'
	);
	var $options = array();

	public function index()
	{	
		// Get the record and url
		// -------------------------------------------------------

		$handle = $this->fetchParam('handle', NULL, NULL, FALSE, FALSE);
		$cache = $this->fetchParam('cache', TRUE, NULL, TRUE, TRUE);
		$request = $this->fetch($handle) ?: null;
		$query = isset($request['query']) ? $request['query'] : null;
		$cache_length = $this->fetchParam('refresh', 7200);

		// Set our options
		// ---------------------------------------------------------
		$options['cache'] = $cache;


		$url = $this->_getUrl($request);


		// If there is no URL
		if( !$url ) {
			return 'Invalid or missing URL';
		}

		// If there is a query string in the config, get it and add it to the url
		if($query) {
			$queryString = $this->_buildQueryString($query);
			$url .= '?' . $queryString;
		} 

		// Do the cache thing
		// ---------------------------------------------------------

		// First, check if there is already a cache of the url
		$cached_id = base64_encode(urlencode($url));
		$cached_response = $this->cache->getYAML($cached_id);

		if($cached_response)
		{
			// If the cache is older than we want, delete it.
			if($this->cache->getAge($cached_id) >= $cache_length)
			{
				$this->cache->delete($cached_id);
			}
			else {
				return $cached_response;
			}
		}
			
		

		$result = $this->_get($url, $options);

		if( $result ) {
			return $result;
		} else {
			return Parse::template($this->content, array('no_results' => true));
		}
		
	}

	/**
    * Get the url from the tag/record
    *                                   
    * @param array|null      $record     The record array from config
    *
    * @return string   The url to request, null is empty
    */
	private function _getUrl($record) {

		if($record) {
			// Does the request have a url?
			if( $record['url'] ) {
				$url = $record['url'];
			} else {
				$url = null;
			}
		} else {
			$url = $this->fetchParam('url') ?: null;
		}

		return $url;
	}

	/**
    * Make the request
    *                                   
    * @param array|null      $record     The record array from config
    *
    * @return string   The url to request, null is empty
    */
	private function _get($url, $options = null, $method = 'get', $headers = null, $postFields = null)
	{
		
		$config = array(
			CURLOPT_URL => $url,
			CURLOPT_HEADER => false,
			CURLOPT_RETURNTRANSFER => true,
		);
		
		// Initialise the curl request
		$data = curl_init();
		curl_setopt_array($data, $config);
		$json = curl_exec($data);
		curl_close($data);

		// Print the result
		$response = json_decode($json, true);

		if($options['cache']) {	
			$cacheId = base64_encode(urlencode($url));
			$this->cache->putYAML($cacheId, $response);
		}

		return $response;
	}

	private function _buildQueryString($array)
	{
		// Lets build the query
		$query = http_build_query($array, '', '&');
		return $query;
	}
}