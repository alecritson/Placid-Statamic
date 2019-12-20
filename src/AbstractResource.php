<?php

namespace Ritson\Placid;

abstract class AbstractResource
{

    public function set($key, $value)
    {
        if (method_exists($this, 'set' . ucfirst($key))) {
            $method = 'set' . ucfirst($key);
            $this->{$method}($value);
        } elseif (property_exists($this, $key)) {
            $this->{$key} = $value;
        }
        return $this;
    }

    /**
     * Create a resource from a record
     */
    public function createFromRecord(array $record)
    {
        foreach ($record as $key => $value) {
            $this->set($key, $value);
        }
        return $this;
    }

    protected function getPartsFromString($string)
    {
        $parts = explode('|', $string);

        return collect(array_map(function ($val) {
            return explode(':', $val);
        }, $parts))->mapWithKeys(function ($value) {
            return [$value[0] => $value[1]];
        })->toArray();
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getQuery()
    {
        return $this->query;
    }
}
