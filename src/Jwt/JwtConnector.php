<?php

namespace ApisFrame\Jwt;

class JwtConnector
{
    protected $app;

    private int $time;

    private array $modality;

    private array $supportedAlgs = [
        'ES384' => ['openssl', 'SHA384'],
        'ES256' => ['openssl', 'SHA256'],
        'HS256' => ['hash_hmac', 'SHA256'],
        'HS384' => ['hash_hmac', 'SHA384'],
        'HS512' => ['hash_hmac', 'SHA512'],
        'RS256' => ['openssl', 'SHA256'],
        'RS384' => ['openssl', 'SHA384'],
        'RS512' => ['openssl', 'SHA512'],
        'EdDSA' => ['sodium_crypto', 'EdDSA']
    ];

    public function __construct($app)
    {
        $this->app = $app;

        $this->time = time();
    }

    /**
     * 守卫：获取配置
     *
     * @param $modality
     * @return $this
     */
    public function guard($modality = null)
    {
        $modality = $modality ?? $this->getDefaultDriver();

        $this->getDefaultModality($modality);

        return $this;
    }

    /**
     * 获取默认配置名称
     *
     * @param $modality
     * @return mixed
     */
    private function getDefaultDriver()
    {
        return $this->app['config']['jwt.default'];
    }

    /**
     * 获取默认配置
     *
     * @param $modality
     * @return void
     */
    private function getDefaultModality($modality)
    {
        $_modality = $this->app['config']["jwt.modality.{$modality}"];

        $_modality['key'] = $this->getDefaultKey($_modality['key']);

        $this->modality = $_modality;
    }

    /**
     * 格式化密钥
     *
     * @return void
     */
    private function getDefaultKey($key)
    {
        return trim(preg_replace('/(.*)\:/', '', $key));
    }

    /**
     * 生成 token
     *
     * @param $parameters
     * @return string
     */
    private function sign($user)
    {
        $header = $this->setHeader();

        $payload = $this->setPayload($user);

        $signature = $this->setSignature($this->modality['alg'], "{$header}.{$payload}");

        return "{$header}.{$payload}.{$signature}";
    }

    /**
     * 验证 token
     *
     * @param $parameters
     * @return false|mixed
     */
    private function check(string $token)
    {
        $jwt = explode('.', $token);

        if (3 != count($jwt)) return false; // TODO：格式错误 // throw new

        list($header, $payload, $signature) = $jwt;

        $alg = $this->getHeader($header)['alg'];

        $signatureNew = $this->setSignature($alg, "{$header}.{$payload}");

        if ($signature === $signatureNew) {
            return $this->checkPayload($payload);
        } else {
            // TODO：格式错误
            // throw new
            return false;
        }
    }

    /**
     * 刷新 token
     *
     * @return void
     */
    private function refresh()
    {
        // TODO: Implement refresh() method.
    }

    /**
     * 设置头信息
     *
     * @return array|string|string[]
     */
    private function setHeader()
    {
        $header = ['typ' => 'JWT', 'alg' => $this->modality['alg']];

        return $this->urlSafeBase64Encode(json_encode($header, 320));
    }

    /**
     * 获取头信息
     *
     * @param $header
     * @return mixed
     */
    private function getHeader($header)
    {
        return json_decode($this->urlSafeBase64Decode($header), true);
    }

    /**
     * 设置荷载信息
     *
     * @param $userInfo
     * @param int $effectiveTime
     * @return array|string|string[]
     */
    private function setPayload($userInfo)
    {
        $payload = [
            'iss' => $this->modality['iss'],                                                                 // 签发者
            'sub' => $this->modality['sub'],                                                                 // 所面向的用户
            'aud' => $this->modality['aud'],                                                                 // 接收的一方
            'exp' => (int)bcadd($this->time, ($this->modality['exp'] + $this->modality['nbf']), 0),    // 过期时间，这个过期时间必须要大于签发时间
            'nbf' => (int)bcadd($this->time, $this->modality['nbf'], 0),                               // 生效时间，定义在什么时间之后生效
            'iat' => $this->time                                                                             // 签发时间
        ];

        foreach ($userInfo as $key => $value) {
            $payload[$key] = $value;
        }

        return $this->urlSafeBase64Encode(json_encode($payload, 320));
    }

    /**
     * 获取荷载信息
     *
     * @param $payload
     * @return mixed
     */
    private function getPayload($payload)
    {
        return json_decode($this->urlSafeBase64Decode($payload), true);
    }

    /**
     * 设置签名信息
     *
     * @param $alg
     * @param $str
     * @return array|false|string|string[]
     */
    private function setSignature($alg, $str)
    {
        if (empty($this->supportedAlgs[$alg])) return false; // TODO：格式错误 // throw new

        list($function, $algorithm) = $this->supportedAlgs[$alg];

        switch ($function) {
            case 'openssl':
                $signature = '';
                break;
            case 'hash_hmac':
                $signature = hash_hmac($algorithm, $str, $this->modality['key'], true);
                break;
            case 'sodium_crypto':
                $signature = '';
                break;
            default:
                $signature = '';
                break;
        }

        return $this->urlSafeBase64Encode($signature);
    }

    /**
     * base64 url 安全加密
     *
     * @param string $str
     * @return array|string|string[]
     */
    private function urlSafeBase64Encode(string $str)
    {
        return str_replace('=', '', strtr(base64_encode($str), '+/', '-_'));
    }

    /**
     * base64 url 安全解密
     *
     * @param string $str
     * @return false|string
     */
    private function urlSafeBase64Decode(string $str)
    {
        $remainder = strlen($str) % 4;
        if ($remainder) {
            $addlen = 4 - $remainder;
            $str .= str_repeat('=', $addlen);
        }
        return base64_decode(strtr($str, '-_', '+/'));
    }

    /**
     * 检查荷载是否匹配
     *
     * @param $payload
     * @return false|mixed
     */
    private function checkPayload($payload)
    {
        $payload = $this->getPayload($payload);

        if ($payload['iss'] != $this->modality['iss']) return false; // TODO：格式错误 // throw new 签发者
        if ($payload['sub'] != $this->modality['sub']) return false; // TODO：格式错误 // throw new 所面向的用户
        if ($payload['aud'] != $this->modality['aud']) return false; // TODO：格式错误 // throw new 接收的一方

        if ($payload['iat'] > $this->time) return false; // TODO：格式错误 // throw new 签发时间
        if ($payload['nbf'] > $this->time) return false; // TODO：格式错误 // throw new 生效时间
        if ($payload['exp'] <= $this->time) return false; // TODO：格式错误 // throw new 过期时间

        if (!empty($payload['user_info'])) {
            return $payload['user_info']['user_id'];
        } else {
            // TODO：格式错误
            // throw new
            return false;
        }
    }

    public function __call($method, $parameters)
    {
        return $this->guard()->{$method}(...$parameters);
    }
}
