<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;

class Plugin_placid extends Plugin {

	var $meta = array(
		'name' => 'Placid',
		'version' => '0.8.9',
		'author' => 'Alec Ritson',
		'author_url' => 'http://www.alecritson.co.uk'
	);
	var $options = array();

	public function index()
	{	
		// Get the request
		// -------------------------------------------------------

		$handle = $this->fetchParam('handle', null, null, false, false);
		$request = $this->fetch($handle) ? $this->fetch($handle) : null;

		// Set our options
		// ---------------------------------------------------------
		$options = array(
			'cache'			=>	(bool) $this->_getOption($request, 'cache', true, null, true, true),
			'cache_length'	=>	(int) $this->_getOption($request, 'refresh', 3200),
			'method'		=>	$this->_getOption($request, 'method', 'GET'),
			'access_token'	=>	$this->_getOption($request, 'access_token'),
			'query'			=>	$this->_getOption($request, 'query'),
			'headers'		=>	$this->_getOption($request, 'headers', null)
		);

		if($options['query'] && !$request)
		{
			// Get the query parameter as a string and explode it.
			$queries = explode(',', $this->fetchParam('query'));

			// Make sure this is a clean array.
			$options['query'] = array();

			// Map each query from the exploded array into a variable
			// Then add them to the query array
			foreach($queries as $query)
			{
				list($key, $value) = explode(':', $query);
				$options['query'][$key] = $value;
			}
		}

		// If there is no url specified, return (figure out why throw exception wasnt working...)
		if( ! $url = $this->_getUrl($request) ) {
			return 'Invalid or missing URL';
		}

		// If there is a query string in the request, get it and add it to the url

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
				else
				{
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

		$client = new Client();

		$request = $client->createRequest($options['method'], $url);

		$query = $request->getQuery();

		if($options['query'])
		{
			foreach ($options['query'] as $key => $value)
			{
				$query->set($key, $value);
			}
		}

		if($options['headers'])
		{
			foreach ($options['headers'] as $key => $value)
			{
				$request->setHeader($key, $value);
			}
		}

		if($options['access_token'])
		{
			$query->set('access_token', $options['access_token']);
		}
	
		$response = $client->send($request);
		$result = $response->json();

		if($options['cache']) {	
			$cacheId = base64_encode(urlencode($url));
			$this->cache->putYAML($cacheId, $result);
		}

		if(!$result) {
			return Parse::template($this->content, array('no_results' => true));
		}

		return $result;
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
			if( array_key_exists('url', $record) ) {
				$url = $record['url'];
			} else {
				$url = null;
			}
		} else {
			$url = $this->fetchParam('url') ?: null;
		}

		return $url;
	}

	private function _getOption($request, $id, $default=NULL, $validity_check=NULL, $is_boolean=FALSE, $force_lower=TRUE)
	{
		return isset($request[$id]) ? $request[$id] : $this->fetchParam($id, $default, $validity_check, $is_boolean, $force_lower);
	}
}