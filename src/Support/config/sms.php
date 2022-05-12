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
        ],
        'wechatOffiaccount' => [
            'appId' => env('WECHAT_OFFIACCOUNT_APPID', ''),
            'appSecret' => env('WECHAT_OFFIACCOUNT_APPSECRET', '')
        ]
    ],
    'wechatOffiaccount' => [
        'adminManagerOpenId' => env('WECHAT_OFFIACCOUNT_ADMIN_OPENID', ''),
        'SmsSendErrorTemplateId' => env('WECHAT_OFFIACCOUNT_REMIND_SMS_ERROR', ''),
        'queueName' => 'wechatOffiaccountSendSms'
    ]
];
