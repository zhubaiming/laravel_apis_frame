<?php

namespace ApisFrame\Sms;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use ApisFrame\Jobs\SendSmsQueue;
use ApisFrame\Support\enums\AliyunSmsError;
use Darabonba\OpenApi\Models\Config;
use Illuminate\Support\Facades\Log;

class AliyunGuard implements Connector
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
        $_config->endpoint = 'dysmsapi.aliyuncs.com';
        $this->client = new Dysmsapi($_config);
        $this->signName = $config['sginName'];
    }

    public function sendSms(string $phoneNumbers, string $templateCode, array $templateParam = [])
    {
        $request = new SendSmsRequest();

        $request->signName = $this->signName;
        $request->phoneNumbers = $phoneNumbers;
        $request->templateCode = $templateCode;
        if (!empty($templateParam)) $request->templateParam = json_encode($templateCode, 320);
//        $request->templateParam = $templateCode;
        $request->outId = rand(100000, 999999);

        return $this->buildQueue('sendSms', $request);
    }

    public function processResponse(array $response)
    {
        if ('OK' === $response['Code']) {
            Log::info(json_encode($response, 320));
            return $response;
        } else {
            Log::error(AliyunSmsError::APIERROR[$response['Code']]);
//            return AliyunSmsError::APIERROR['APIERROR'];
            return false;
        }
    }

    public function record($phoneNumbers, $bizId)
    {
        if (is_array($phoneNumbers)) {

        }
    }

    private function buildQueue(string $method, $request)
    {
        if (!method_exists($this->client, $method)) {
            // TODO：格式错误 // throw new
        } else {
            $syncCallBack = function () use ($method, $request) {
                $response = $this->client->{$method}($request)->body->toMap();

                return $this->processResponse($response);
            };

            dispatch(new SendSmsQueue($syncCallBack));
        }


        return 'OK';
    }
}
