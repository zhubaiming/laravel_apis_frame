<?php

namespace ApisFrame\Sms;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use ApisFrame\Jobs\Aliyun\SendSmsQueue;
use Darabonba\OpenApi\Models\Config;
use Illuminate\Support\Facades\Log;

class AliyunGuard implements Connector
{
    private $client;

    private string $signName;

    private string $logChannel = 'smsAliyun';

    public function __construct(array $config)
    {
        $_config = new Config([
            'accessKeyId' => $config['accessKeyId'],
            'accessKeySecret' => $config['accessKeySecret']
        ]);

        // 访问的域名
        $_config->endpoint = 'dysmsapi.aliyuncs.com';
        $this->client = new Dysmsapi($_config);
//        $this->signName = $config['sginName'];
        $this->signName = '阿里云短信测试';
    }

    public function sendSms(string $phoneNumbers, string $templateCode, array $templateParam = [], string $queueName = null)
    {
        $request = new SendSmsRequest();

        $request->signName = $this->signName;
        $request->phoneNumbers = $phoneNumbers;
        $request->templateCode = $templateCode;
        if (!empty($templateParam)) $request->templateParam = json_encode($templateParam, 320);

        $this->buildQueue([$phoneNumbers], $request, 'sendSms', $queueName);
    }

    private function buildQueue(array $phoneNumbers, $request, string $method, string $queueName)
    {
        SendSmsQueue::dispatch($this->client, $phoneNumbers, $request, $method, $queueName);
    }

    public function throwException(string $message, int $code)
    {
        throw new \Exception("阿里云短信【{$message}】失败，具体原因请联系管理员进行查看", $code);
    }
}
