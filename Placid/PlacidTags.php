<?php

namespace Statamic\Addons\Placid;

use Statamic\Extend\Tags;

class PlacidTags extends Tags
{
    /**
     * The {{ placid:example }} tag.
     *
     * @return string|array
     */
    public function request($something = null)
    {
        $options = [];
        $handle = $this->getParam('handle', null);
        $request = $this->getConfig($handle);
        $method = $this->getConfig('method');
        $cacheDuration = $this->getParam('duration', isset($request['duration']) ? $request['duration'] : $this->getConfig('cacheDuration', 1440));

        // Request options
        $options['client']['base_uri'] = $this->getParam('url', isset($request['url']) ? $request['url'] : null);
        $options['client']['headers'] = isset($request['headers']) ? $request['headers'] : null;
        $options['access_token'] = $this->getParam('access_token', isset($request['access_token']) ? $request['access_token'] : null);
        $options['cache'] = $this->getParamBool('cache', isset($request['cache']) ? $request['cache'] : true);
        $options['path'] = $this->getParam('path', null);
        $options['query'] = $this->getParam('query', isset($request['query']) ? $request['query'] : null);
        // Set up the cached_id
        $cacheId = base64_encode(urlencode($options['client']['base_uri'].(is_array($options['query']) ? implode(' ', $options['query']) : $options['query'])));

        /*
            Has a query been set in the template.
         */
        if ($options['query'] && !is_array($options['query'])) {

            // Get the query parameter as a string and explode it.
            $queries = explode(',', $options['query']);

            // Make sure this is a clean array.
            $options['client']['query'] = [];

            // Map each query from the exploded array into a variable
            // Then add them to the query array
            foreach ($queries as $query) {
                list($key, $value) = explode(':', $query);
                if ($method != 'GET') {
                    $options['client']['form_params'][$key] = $value;
                } else {
                    $options['client']['query'][$key] = $value;
                }
            }
        }

        // If an access token is set, lets set it to our client
        if ($options['access_token'] && !$request) {
            // Try and get the token from the config
            try {
                $token = $this->fetch('tokens')[$options['access_token']];
            } catch (Exception $e) {
                // Log needs to go here
                $token = null;
            }
            $options['access_token'] = $token;
        }

        // Do the cache thing
        // ---------------------------------------------------------
        if ($options['cache']) {
            // Try and get a cached response
            $cached_response = $this->cache->get($cacheId);
            if ($cached_response) {
                if (count($cached_response) == count($cached_response, COUNT_RECURSIVE)) {
                    return $cached_response;
                }
                return ['response' => $cached_response];
            }
        }

        // Do we have an access token we need to append?
        if ($options['access_token']) {
            $options['client']['query']['access_token'] = $options['access_token'];
        }

        $options['client']['query'] = $options['query'];

        try {
            $response = $this->api('Placid')->request($options['client'], $options['path'], $method);
            $response = json_decode($response->getBody(), true);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = null;
        }

        // Do we need to cache the request?
        if ($options['cache']) {
            $this->cache->put($cacheId, $response, $cacheDuration);
        }
        // If there is no result, pass the `no_results` tag back
        if (!$response) {
            return ['no_results' => true];
        }

        if (count($response) == count($response, COUNT_RECURSIVE)) {
            return $response;
        }

        return ['response' => $response];
    }
}
