<?php

/**
 * 文件下载系统
 */

namespace ApisFrame\Facades;

use Illuminate\Support\Facades\Facade;

class Download extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Download';
    }
}
