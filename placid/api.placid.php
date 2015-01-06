<?php

class API_placid extends API
{
	public function request($url, $method = 'GET')
	{
		return $this->tasks->client()->request($url, $method);
	}
	public function send($request)
	{
		return $this->tasks->client()->send($request);
	}
}