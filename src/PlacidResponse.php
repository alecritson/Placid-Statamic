<?php

namespace Ritson\Placid;

use Exception;

class PlacidResponse
{
    protected $status = 200;

    protected $contents;

    public function resolve($response)
    {
        if ($response instanceof Exception) {
            $this->status = $response->getCode();
            $this->contents = $response->getMessage();
        } else {
            $this->contents = json_decode($response->getBody()->getContents(), true);
            $this->status = $response->getStatusCode();
        }
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getContents()
    {
        return $this->contents;
    }

    public function toArray()
    {
        return [
            'response' => [
                'status' => $this->status,
                'data' => $this->contents,
            ]
        ];
    }
}