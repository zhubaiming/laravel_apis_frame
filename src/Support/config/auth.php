<?php

return [
    'guards' => [
        'admin' => [
            'driver' => 'redis',
            'provider' => 'admins',
        ],
        'wechatMiniprogram' => [

        ],
    ],

    'providers' => [
        'admins' => [
            'driver' => 'eloquent',
            'model' => 'App\Models\Admin\User',
        ]
    ],

    'passwords' => [

    ]
];
