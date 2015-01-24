<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;

class Tasks_placid extends Tasks
{

	protected $client;
	protected $request;

	/**
	*
	* Create a new client
	*
	* @return GuzzleHttp\Client Object
	**/
	
	public function client()
	{
		$this->client = new Client();
		
		return $this;
	}
	/**
	*
	* Create a request with the client
	*
	* @param $method - The method to use default is GET
	* @param $url - The URL to create the request for, must be a full valid url
	*
	* @return GuzzleHttp\Message\Request Object
	**/
	
	public function request($url, $method = 'GET')
	{
		$this->request = $this->client->createRequest($method, $url);

		return $this->request;
	}

	/**
	*
	* Send the request with the client
	* @param $request - the request object to send
	*
	* @return GuzzleHttp\Message\Response Object
	**/
	
	public function send($request, $options = null)
	{
		if($options)
		{
			return $this->client->get($request->getUrl(), $options);
		}
		return $this->client->send($request);
	}

	// public function 
}