<?php

class Plugin_placid extends Plugin {

	var $meta = array(
		'name' => 'Placid',
		'version' => '1.0.0',
		'author' => 'Alec Ritson',
		'author_url' => 'http://www.alecritson.co.uk'
	);
	var $params = array(
		'cache',
		'refresh',
		'curl',	
		'access_token',
		'query',
		'path',
		'headers',
		'url',	
	);

	var $method = 'GET';
	var $curlOpts = null;
	var $url = null;


	public function index()
	{			
		// Get the request
		// -------------------------------------------------------

		$handle = $this->fetchParam('handle', null, null, false, false);
		$request = $this->fetch($handle, null, null, false, false);

		// Get the config from placid.yaml
		$options = $this->fetch($handle, [], null, false, false);
		$this->params = $this->fetchParams($this->params);		

		$options = array_merge($options,array_filter($this->params));

		if(array_key_exists('url', $options))
		{
			$this->url = $options['url'];
		}

		if(!array_key_exists('refresh', $options))
		{
			$options['refresh'] = 500;
		}

		// We only want to try and explode the query if it's been set as a parameter,
		// not when there is a record.
		if(array_key_exists('query', $options) && !$request)
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

		// If an access token is set and there is no request we need to make sure the token exists in the config too.
		if(array_key_exists('access_token', $options) && !$request)
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


		if(array_key_exists('curl', $options))
		{
			// list($key, $value) = $options['curl'];
			foreach($options['curl'] as $key => $value) {
				$this->curlOpts[$key] = $value;
			}
		}

		if(array_key_exists('method', $options))
		{
			$this->method = $options['method'];
		}


		// Get the request object from the tasks
		$request = $this->tasks->client()->request($this->url, $this->method);

		if(array_key_exists('path', $options))
		{
			$request->setPath($options['path']);
		}

		
		// Do the cache thing
		// ---------------------------------------------------------
		if(array_key_exists('cache', $options) && $options['cache'])
		{

			// Set up the cached_id
			$cached_id = base64_encode(urlencode($request->getUrl()));

			// Try and get a cached response
			$cached_response = $this->cache->getYAML($cached_id);

			if($cached_response)
			{	
				// If the cache is older than we want, delete it.
				if($this->cache->getAge($cached_id) >= $options['refresh'])
				{
					$this->cache->delete($cached_id);
				}
				else
				{
					return $cached_response;
				}
			}
		}
		
		// Grab the query from the request
		$query = $request->getQuery();


		// Only do this if the query is an array
		if(array_key_exists('query', $options)&& is_array($options['query']))
		{
			foreach ($options['query'] as $key => $value)
			{
				$query->set($key, $value);
			}
		}

		
		// Do headers exist and is it an array?
		if(array_key_exists('headers', $options) && is_array($options['headers']))
		{
			foreach ($options['headers'] as $key => $value)
			{
				$request->setHeader($key, $value);
			}
		}

		// Do we have an access token we need to append?
		if(array_key_exists('access_token', $options))
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

		try {

			$response = $this->tasks->client()->send($request, $this->curlOpts);
			$result = $response->json();

		} catch(\Exception  $e)
		{
			$this->log->warn($e->getMessage());
			$result = null;
		}

		// Do we need to cache the request?
		if(array_key_exists('cache', $options)) {	
			$cacheId = base64_encode(urlencode($request->getUrl()));
			$this->cache->putYAML($cacheId, $result);
		}

		// If there is no result, pass the `no_results` tag back
		if(!$result) {
			return Parse::template($this->content, array('no_results' => true));
		}
		

		return $result;
	}

	public function fetchParams($args)
	{
		foreach($args as $arg)
		{
			
			$options[$arg] = $this->fetchParam($arg, NULL, NULL, FALSE, FALSE);
		}
		return array_filter($options);
	}
}