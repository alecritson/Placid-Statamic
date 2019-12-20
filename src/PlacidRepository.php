<?php

namespace Ritson\Placid;

use File;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Symfony\Component\Yaml\Yaml;

class PlacidRepository
{
    public function getByHandle($handle, $path = 'requests')
    {
        $file = resource_path("placid/${path}/${handle}.yaml");

        try {
            $record = File::get($file);
        } catch (FileNotFoundException $e) {
            return null;
        }

        $record = Yaml::parse($record);

        if ($path == 'requests') {
            $resource = new PlacidResource;
        } else {
            $resource = new PlacidAuthResource;
        }
        
        $resource->createFromRecord($record);
        
        return $resource;
    }
}
