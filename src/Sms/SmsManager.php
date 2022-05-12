<?php

namespace ApisFrame\Sms;

class SmsManager
{
    private $app;

    protected $guards = [];

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function guard($name = null)
    {
        $name = $name ?? $this->getDefaultDriver();

        return $this->guards[$name] ?? $this->guards[$name] = $this->resolve($name);
    }

    private function resolve($name = null)
    {
        $config = $this->getConfig($name);

        $driverMethod = 'create' . ucfirst($name) . 'Driver';

        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config);
        }

        // 原函数中抛出一个 InvalidArgumentException 类型的异常
    }

    private function getConfig($name)
    {
        return $this->app['config']["sms.connections.$name"];
    }

    private function getDefaultDriver()
    {
        // 获取 config/auth.php 中的对应参数，当前为【web】
        return $this->app['config']['sms.defaults.guard'];
    }

    private function createAliyunDriver($config)
    {
        if (is_null($config)) {
            throw new \Exception("短信无法下发，请联系管理员进行短信配置",20001);
        }

        return new AliyunGuard($config);
    }

    private function createWechatOffiaccountDriver($config)
    {
        return new WechatOffiaccountGuard($config);
    }

    public function __call($method, $parameters)
    {
        return $this->guard()->{$method}(...$parameters);
    }
}
