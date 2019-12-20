<?php

namespace Ritson\Placid;

class PlacidAuthResource extends AbstractResource
{
    protected $headers = [];

    protected $query = [];

    protected $token = null;

    public function setToken($value)
    {
        $this->token = config($value, null);
    }

    public function getHeaders()
    {
        // Replace any tokens
        foreach ($this->headers as $key => $value) {
            $this->headers[$key] = str_replace(':token', $this->token, $value);
        }
        return $this->headers;
    }

    public function getQuery()
    {
        // Replace any tokens
        foreach ($this->query as $key => $value) {
            $this->query[$key] = str_replace(':token', $this->token, $value);
        }
        return $this->query;
    }
}
