<?php

/**
 * 文件上传系统
 */

namespace ApisFrame\Facades;

use Illuminate\Support\Facades\Facade;

class Upload extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Upload';
    }
}
