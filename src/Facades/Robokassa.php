<?php

namespace Icekristal\RobokassaForLaravel\Facades;

use Illuminate\Support\Facades\Facade;

class Robokassa extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'ice.robokassa';
    }
}
