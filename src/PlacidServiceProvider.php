<?php

namespace Ritson\Placid;

use Statamic\Providers\AddonServiceProvider;
use Ritson\Placid\Placid;

class PlacidServiceProvider extends AddonServiceProvider
{
    protected $tags = [
        Placid::class,
    ];
}
