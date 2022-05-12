<?php

namespace ApisFrame\Sms;

use ApisFrame\Jobs\WechatOffiaccount\SendSmsQueue;
use GuzzleHttp\Client;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Redis;

class WechatOffiaccountGuard implements Connector
{
    private string $accessToken;

    public function __construct(array $config)
    {
//        if (is_null($config)) {
//            throw new \Exception("短信无法下发，请联系管理员进行短信配置",20001);
//        }

        $redis = Redis::connection('thirdParties');

        if ($redis->exists('wechat_offiaccount_access_token')) {
            $this->accessToken = $redis->get('wechat_offiaccount_access_token');
        } else {
            $res = $this->getAccessToken($config['appId'], $config['appSecret']);
            $redis->set('wechat_offiaccount_access_token', $res['access_token']);
            $redis->expire('wechat_offiaccount_access_token', $res['expires_in']);
            $this->accessToken = $res['access_token'];
        }
    }

    private function getAccessToken($appId, $appSecret)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appId}&secret={$appSecret}";

        try {
            $client = new Client();

            $response = $client->request('GET', $url);

            $body = json_decode($response->getBody()->getContents(), true);

            return $body;
        } catch (RequestException $e) {
        }
    }

    public function sendSms(array $parameters, string $queueName)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$this->accessToken}";

        return $this->buildQueue($url, 'POST', $parameters, $queueName);
    }

    private function buildQueue($url, $method, $parameters, $queueName)
    {
        SendSmsQueue::dispatch($url, $method, $queueName, $parameters);

        return true;
    }

    public function throwException(string $message, int $code)
    {
        // TODO: Implement throwException() method.
    }
}
