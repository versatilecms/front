<?php

namespace Versatile\Front\Facades;

use Illuminate\Support\Facades\Facade;

class VersatileFrontend extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'versatile-frontend';
    }
}