<?php

/**
 * 导出系统
 */

namespace ApisFrame\Facades;

use Illuminate\Support\Facades\Facade;

class Batch extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Batch';
    }
}
