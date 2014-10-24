<?php

class Plugin_placid extends Plugin {

	var $meta = array(
		'name' => 'Placid',
		'version' => '0.5.7',
		'author' => 'Alec Ritson',
		'author_url' => 'http://www.alecritson.co.uk'
		);
	var $options = array();

	public function index()
	{	
		// Get the request
		// -------------------------------------------------------

		$handle = $this->fetchParam('handle', null, null, false, false);
		$request = $this->fetch($handle) ?: null;
		
		// Set our options
		// ---------------------------------------------------------
		$options = array(
			'cache' => (bool) $this->_getOption( $request, 'cache', true, null, true, true),
			'cache_length' => $this->_getOption( $request, 'refresh', 3200)
			);

		// If there is no url specified, return (figure out why throw exception wasnt working...)
		if( ! $url = $this->_getUrl($request) ) {
			return 'Invalid or missing URL';
		}

		// If there is a query string in the request, get it and add it to the url
		$query = isset($request['query']) ? $request['query'] : null;

		if($query) {
			$queryString = $this->_buildQueryString($query);
			$url .= '?' . $queryString;
		} 

		// Do the cache thing
		// ---------------------------------------------------------

		if($options['cache'])
		{

			// Set up the cached_id
			$cached_id = base64_encode(urlencode($url));

			// Try and get a cached response
			$cached_response = $this->cache->getYAML($cached_id);

			if($cached_response)
			{	
				// If the cache is older than we want, delete it.
				if($this->cache->getAge($cached_id) >= $options['cache_length'])
				{
					$this->cache->delete($cached_id);
				}
				else {
					return $cached_response;
				}
			}
		}

		// Make the call
		// -----------------------------------------------------------
		// Needs improving:
		// 	- Be able to send headers?
		//	- Content type?
		//	- Authorisation?
		//	- What happens if it's not json?
		// -----------------------------------------------------------
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
		$response = curl_exec($data);
		$status = curl_getinfo($data);
		curl_close($data);

		switch ($status['content_type']) {
			default:
			$content = json_decode($response, true);
			break;
		}

		if($options['cache']) {	
			$cacheId = base64_encode(urlencode($url));
			$this->cache->putYAML($cacheId, $content);
		}

		return $content;
	}


	private function _getOption($request, $id, $default=NULL, $validity_check=NULL, $is_boolean=FALSE, $force_lower=TRUE)
	{
		return isset($request[$id]) ? $request[$id] : $this->fetchParam($id, $default, $validity_check, $is_boolean, $force_lower);
	}
	private function _buildQueryString($array)
	{
		// Lets build the query
		$query = http_build_query($array, '', '&');
		return $query;
	}
}