<?php

namespace Statamic\Addons\Placid;

use Statamic\Extend\API;
use \GuzzleHttp\Client as Client;

class PlacidAPI extends API
{
	public function request($options = array(),$path = null, $method = 'GET')
	{
		$client = new Client($options);
		if($path)
		{
			return $client->request($method, $path);
		}
		else
		{
			return $client->request($method);
		}
	}

	public function send($request, $options = null)
	{
		if($options)
		{
			return $this->client->get($request->getUrl(), $options);
		}
		return $this->client->send($request);
	}
}
