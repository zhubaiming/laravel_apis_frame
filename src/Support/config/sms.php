<?php

return [
    'defaults' => [
        'guard' => env('SMS_CONNECTION', ''),
    ],

    'connections' => [
        'aliyun' => [
            'accessKeyId' => env('SMS_ALIYUN_ACCESSKEYID', ''),
            'accessKeySecret' => env('SMS_ALIYUN_ACCESSKEYSECRET', ''),
            'sginName' => env('SMS_SIGN_NAME', '')
        ]
    ]
];
