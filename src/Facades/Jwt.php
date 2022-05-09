<?php

/**
 * JWT 发放、验证系统
 */

namespace ApisFrame\Facades;

use Illuminate\Support\Facades\Facade;

class Jwt extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Jwt';
    }
}
