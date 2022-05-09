<?php

/**
 * 线上支付系统
 */

namespace ApisFrame\Facades;

use Illuminate\Support\Facades\Facade;

class Pay extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Pay';
    }
}
