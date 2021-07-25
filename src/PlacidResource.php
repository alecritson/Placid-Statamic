<?php

namespace Ritson\Placid;

use Cache;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class PlacidResource extends AbstractResource
{
    /**
     * The host for the resource
     */
    protected $host;

    /**
     * The API path
     */
    protected $path = null;

    /**
     * The method
     */
    protected $method = 'GET';

    /**
     * The cache duration
     */
    protected $cache = null;

    /**
     * The query to send
     */
    protected $query = [];

    /**
     * The form params to send
     */
    protected $formParams = [];

    /**
     * Any segments to replace in the path
     */
    protected $segments = [];

    /**
     * Additional headers to send
     */
    protected $headers = [];

    /**
     * Whether to use auth for this request
     */
    protected $auth = null;

    /**
     * The full URL to use for the request
     */
    protected $url = null;

    /**
     * Set the value for access token
     * @param String $value
     */
    protected function setAccessToken($value)
    {
        $this->accessToken = config($value, null);
    }

    /**
     * Get the client
     */
    public function client()
    {
        return new Client([
            'base_uri' => $this->host,
            'query' => $this->query,
            'form_params' => $this->formParams,
            'headers' => $this->headers,
        ]);
    }

    /**
     * Prepare the path with segments
     */
    protected function preparePath()
    {
        if (is_string($this->segments)) {
            $this->segments = $this->getPartsFromString($this->segments);
        }
        foreach ($this->segments as $key => $value) {
            $this->path = str_replace(":{$key}", $value, $this->path);
        }
    }

    /**
     * Set the value for the query string
     * @param String|Array $value
     */
    protected function setQuery($value)
    {
        if (is_string($value)) {
            $queryParts = $this->getPartsFromString($value);
        } else {
            $queryParts = $value;
        }

        $this->query = collect($this->query)->merge($queryParts)->toArray();
    }


    /**
     * Set the value for the query string
     * @param String|Array $value
     */
    protected function setHeaders($value)
    {
        if (is_string($value)) {
            $headerParts = $this->getPartsFromString($value);
        } else {
            $headerParts = $value;
        }

        $this->headers = collect($this->headers)->merge($headerParts)->toArray();
    }

    /**
     * Prepare the resource for authentication
     */
    protected function prepareAuth()
    {
        if (!$this->auth) {
            return;
        }

        $authResource = (new PlacidRepository)->getByHandle($this->auth, 'auth');

        // Are we using headers?
        $authHeaders = collect($authResource->getHeaders());
        $authQuery = collect($authResource->getQuery());

        $this->headers = collect($this->headers)->merge($authHeaders)->toArray();
        $this->query = collect($this->query)->merge($authQuery)->toArray();
    }

    /**
     * Execute the resource
     */
    public function execute()
    {
        $this->preparePath();

        if ($this->cache && !$this->auth) {
            return Cache::remember($this->getCacheKey(), $this->cache, function () {
                $response = $this->client()->request($this->method, $this->url ?? $this->path);
                return (new PlacidResponse)->resolve($response);
            });
        }


        $this->prepareAuth();

        try {
            $response = $this->client()->request($this->method, $this->url ?? $this->path);
            return (new PlacidResponse)->resolve($response);
        } catch (RequestException $e) {
            return (new PlacidResponse)->resolve($e->getResponse() ?? $e);
        }
    }

    protected function getCacheKey()
    {
        return base64_encode(
            urlencode($this->url ?? ($this->host . $this->path . implode('&', $this->query)))
        );
    }
}
