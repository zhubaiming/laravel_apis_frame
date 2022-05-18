<?php

namespace ApisFrame\Support\enums;

enum ApisError
{
    const JWT = [
        10001 => 'token 配置错误',
        10002 => 'token 验证失败',
        10003 => 'token 验证错误',
        10004 => 'token 加密失败',
        10005 => 'token 非法签发者',
        10006 => 'token 非法使用者',
        10007 => 'token 非法接收者',
        10008 => 'token 非法签发时间',
        10009 => 'token 尚未生效',
        10010 => 'token 已过期',
        10011 => 'token 解析错误',
        10012 => 'token 信息错误',
        10013 => 'token 加密方式不支持',
    ];

    public static function getError($name, $code): array
    {
        $name = strtolower($name);

        return match ($name) {
            'jwt' => [self::JWT[$code], $code],
            default => ['未定义', 99999]
        };
    }
}
