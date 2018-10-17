<?php

namespace Mrluke\Privileges\Facades;

use Illuminate\Support\Facades\Facade;

class Detector extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'mrluke-privileges';
    }
}
