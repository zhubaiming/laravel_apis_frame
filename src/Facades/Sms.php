<?php

/**
 * 消息发送系统
 */

namespace ApisFrame\Facades;

use Illuminate\Support\Facades\Facade;

class Sms extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Sms';
    }
}
