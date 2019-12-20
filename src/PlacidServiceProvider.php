<?php

namespace Ritson\Placid;

use Statamic\Providers\AddonServiceProvider;
use Ritson\Placid\PlacidTag;

class PlacidServiceProvider extends AddonServiceProvider
{
    protected $tags = [
        PlacidTag::class,
    ];
}
