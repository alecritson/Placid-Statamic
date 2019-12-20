<?php

namespace Ritson\Placid;

use Exception;
use Ritson\Placid\PlacidRepository;
use Ritson\Placid\PlacidResource;
use Statamic\Tags\Tags;

class PlacidTag extends Tags
{
    protected static $handle = 'placid';

    public function __construct(PlacidRepository $repo)
    {
        $this->repo = $repo;
    }

    public function index()
    {
        return $this->request();
    }
    
    // protected function get

    /**
     * The {{ placid:example }} tag.
     *
     * @return string|array
     */
    public function request()
    {
        $handle = $this->getParam('handle', null);

        $options = [
            'host' => $this->getParam('host', null),
            'headers' => $this->getParam('headers', null),
            'path' => $this->getParam('path', null),
            'cache' => $this->getParam('cache', null),
            'method' => $this->getParam('method', null),
            'segments' => $this->getParam('segments', null),
            'url' => $this->getParam('url', null),
            'query' => $this->getParam('query', null),
            'auth' => $this->getParam('auth', null)
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
