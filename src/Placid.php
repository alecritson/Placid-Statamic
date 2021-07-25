<?php

namespace Ritson\Placid;

use Ritson\Placid\PlacidRepository;
use Ritson\Placid\PlacidResource;
use Statamic\Tags\Tags;

class Placid extends Tags
{
    public function __construct(PlacidRepository $repo)
    {
        $this->repo = $repo;
    }

    public function index()
    {
        return $this->request();
    }

    /**
     * The {{ placid:example }} tag.
     *
     * @return string|array
     */
    public function request()
    {
        $handle = $this->params->get('handle', null);

        $options = [
            'host' => $this->params->get('host', null),
            'headers' => $this->params->get('headers', null),
            'path' => $this->params->get('path', null),
            'cache' => $this->params->get('cache', null),
            'method' => $this->params->get('method', null),
            'segments' => $this->params->get('segments', null),
            'url' => $this->params->get('url', null),
            'query' => $this->params->get('query', null),
            'auth' => $this->params->get('auth', null)
        ];

        if ($handle) {
            $resource = $this->repo->getByHandle($handle);
        } else {
            $resource = new PlacidResource;
        }

        foreach ($options as $key => $value) {
            if ($value) {
                $resource->set($key, $value);
            }
        }

        $response = $resource->execute();
  
        return $response->toArray();
    }
}
