<?php

namespace ApisFrame\Jwt;

use ApisFrame\Exceptions\Jwt\JwtException;
use ApisFrame\Support\enums\ApisError;
use Illuminate\Support\Facades\Log;

class JwtConnector
{
    protected object $app;

    private int $time;

    private array $modality;

    private string $logChannel = 'jwt';

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
     * @throws JwtException
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
     * @return string
     */
    private function getDefaultDriver(): string
    {
        return $this->app['config']['jwt.default'];
    }

    /**
     * 获取默认配置
     *
     * @param $modality
     * @return void
     * @throws JwtException
     */
    private function getDefaultModality($modality)
    {
        $_modality = $this->app['config']["jwt.modality.$modality"];

        if (is_null($_modality)) {
            Log::channel($this->logChannel)->error("获取 token 配置失败(10001)：当前调用的配置【 $modality 】不存在");
            throw new JwtException(...ApisError::getError('jwt', 10001));
        }

        $_modality['key'] = $this->getDefaultKey($_modality['key']);

        $this->modality = $_modality;
    }

    /**
     * 格式化密钥
     *
     * @param $key
     * @return string
     */
    private function getDefaultKey($key): string
    {
        return trim(preg_replace('/(.*):/', '', $key));
    }

    /**
     * 生成 token
     *
     * @param array $user
     * @return string
     * @throws JwtException
     */
    private function sign(array $user): string
    {
        $header = $this->setHeader();

        $payload = $this->setPayload($user);

        $signature = $this->setSignature($this->modality['alg'], "$header.$payload");

        return "$header.$payload.$signature";
    }

    /**
     * 验证 token
     *
     * @param string $token
     * @return string
     * @throws JwtException
     */
    private function check(string $token): string
    {
        $jwt = explode('.', $token);

        if (3 != count($jwt)) throw new JwtException(...ApisError::getError('jwt', 10002));

        list($header, $payload, $signature) = $jwt;

        $alg = $this->getHeader($header)['alg'];

        $signatureNew = $this->setSignature($alg, "$header.$payload");

        if ($signature === $signatureNew) {
            return $this->checkPayload($payload);
        } else {
            throw new JwtException(...ApisError::getError('jwt', 10003));
        }
    }

    /**
     * 刷新 token
     *
     * @return void
     */
    private function refresh(): string
    {
        // TODO: Implement refresh() method.
    }

    /**
     * 设置头信息
     *
     * @return string
     */
    private function setHeader(): string
    {
        $header = ['typ' => 'JWT', 'alg' => $this->modality['alg']];

        return $this->urlSafeBase64Encode(json_encode($header, 320));
    }

    /**
     * 获取头信息
     *
     * @param string $header
     * @return array
     */
    private function getHeader(string $header): array
    {
        return json_decode($this->urlSafeBase64Decode($header), true);
    }

    /**
     * 设置荷载信息
     *
     * @param array $userInfo
     * @return string
     */
    private function setPayload(array $userInfo): string
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
     * @param string $payload
     * @return array
     */
    private function getPayload(string $payload): array
    {
        return json_decode($this->urlSafeBase64Decode($payload), true);
    }

    /**
     * 设置签名信息
     *
     * @param string $alg
     * @param string $str
     * @return string
     * @throws JwtException
     */
    private function setSignature(string $alg, string $str): string
    {
        if (empty($this->supportedAlgs[$alg])) throw new JwtException(...ApisError::getError('jwt', 10004));

        list($function, $algorithm) = $this->supportedAlgs[$alg];

        $signature = match ($function) {
            'openssl' => '',
            'hash_hmac' => hash_hmac($algorithm, $str, $this->modality['key'], true),
            'sodium_crypto' => '',
            default => throw new JwtException(...ApisError::getError('jwt', 10013)),
        };

        return $this->urlSafeBase64Encode($signature);
    }

    /**
     * base64 url 安全加密
     *
     * @param string $str
     * @return string
     */
    private function urlSafeBase64Encode(string $str): string
    {
        return str_replace('=', '', strtr(base64_encode($str), '+/', '-_'));
    }

    /**
     * base64 url 安全解密
     *
     * @param string $str
     * @return string
     */
    private function urlSafeBase64Decode(string $str): string
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
     * @return string
     * @throws JwtException
     */
    private function checkPayload($payload): string
    {
        $payload = $this->getPayload($payload);

        if ($payload['iss'] != $this->modality['iss']) throw new JwtException(...ApisError::getError('jwt', 10005));
        if ($payload['sub'] != $this->modality['sub']) throw new JwtException(...ApisError::getError('jwt', 10006));
        if ($payload['aud'] != $this->modality['aud']) throw new JwtException(...ApisError::getError('jwt', 10007));

        if ($payload['iat'] > $this->time) throw new JwtException(...ApisError::getError('jwt', 10008));
        if ($payload['nbf'] > $this->time) throw new JwtException(...ApisError::getError('jwt', 10009));
        if ($payload['exp'] <= $this->time) throw new JwtException(...ApisError::getError('jwt', 10010));

        if (!empty($payload['user_info'])) {
            if (!empty($payload['user_info']['user_id'])) {
                return $payload['user_info']['user_id'];
            } else {
                throw new JwtException(...ApisError::getError('jwt', 10012));
            }
        } else {
            throw new JwtException(...ApisError::getError('jwt', 10011));
        }
    }

    public function __call($method, $parameters)
    {
        return $this->guard()->{$method}(...$parameters);
    }
}
