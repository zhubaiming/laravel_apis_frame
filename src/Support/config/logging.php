<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [
    'channels' => [
        'jwt' => [
            'driver' => 'single',
            'path' => storage_path('logs/errors/jwt-' . date('Y-m-d') . '.log'),
            'level' => env('LOG_LEVEL', 'debug')
        ],

        'smsAliyun' => [
            'driver' => 'single',
            'path' => storage_path('logs/sms_aliyun-' . date('Y-m-d') . '.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ]
    ],

];
