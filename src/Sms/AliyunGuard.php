<?php

namespace ApisFrame\Sms;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use Darabonba\OpenApi\Models\Config;

class AliyunGuard
{
    private $client;

    private string $signName;

    public function __construct(array $config)
    {
        $_config = new Config([
            'accessKeyId' => $config['accessKeyId'],
            'accessKeySecret' => $config['accessKeySecret']
        ]);

        // 访问的域名
        $_config->endpoint = '';
        $this->client = new Dysmsapi($_config);
        $this->signName = $config['sginName'];
    }


    public function sendSms($phoneNumbers, $templateCode)
    {
        return $phoneNumbers;
        $request = new SendSmsRequest();

        $request->phoneNumbers = '1';


        return 'send';
    }
}
