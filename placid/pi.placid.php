<?php

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
			'cache_length'	=>	(int) $this->_getOption($request, 'refresh', $this->fetch('placid_defaults')['refresh'] ?: 3200),
			'method'		=>	$this->_getOption($request, 'method', 'GET'),
			'access_token'	=>	$this->_getOption($request, 'access_token'),
			'query'			=>	$this->_getOption($request, 'query'),
			'headers'		=>	$this->_getOption($request, 'headers', null)
		);

		// We only want to try and explode the query if it's been set as a parameter,
		// not when there is a record.
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

		// If an access token is set and there is no request we need to make sure the token exists in the config too.
		if($options['access_token'] && !$request)
		{
			// Try and get the token from the config
			try {
				$token = $this->fetch('placid_tokens')[$options['access_token']];
			} catch(Exception $e)
			{
				// Log needs to go here
				$token = null;
			}
			$options['access_token'] = $token;
		}
			
		// Get the request object from the tasks
		$request = $this->tasks->client()->request($options['method'], $url);

		// Grab the query from the request
		$query = $request->getQuery();

		// Only do this if the query is an array
		if($options['query'] && is_array($options['query']))
		{
			foreach ($options['query'] as $key => $value)
			{
				$query->set($key, $value);
			}
		}

		// Do headers exist and is it an array?
		if($options['headers'] && is_array($options['headers']))
		{
			foreach ($options['headers'] as $key => $value)
			{
				$request->setHeader($key, $value);
			}
		}

		// Do we have an access token we need to append?
		if($options['access_token'])
		{
			$query->set('access_token', $options['access_token']);
		}

		/**
		*	Try and get the response
		*
		*	TODO:
		*	- Create a log if something goes wrong, at the mo Log::warn($e->getMessage(), $meta['name'], $meta['version']) isn't working :(
		*
		**/
		try
		{
			$response = $this->tasks->client()->send($request);
			$result = $response->json();

		} catch(\Exception  $e)
		{
			// If an exception is thrown we set the result to null, this will help with the 'no_results' tag
			// The error log bit should go here
			$result = null;
		}

		// Do we need to cache the request?
		if($options['cache']) {	
			$cacheId = base64_encode(urlencode($url));
			$this->cache->putYAML($cacheId, $result);
		}

		// If there is no result, pass the `no_results` tag back
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
    * @return string   The url to request, null if empty
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