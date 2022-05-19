<?php

namespace ApisFrame;

use Illuminate\Contracts\Foundation\CachesConfiguration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;

class ApisFrameServiceProvider extends ServiceProvider
{
    /**
     * 注册任何应用服务
     *
     * @return void
     */
    public function register()
    {
        $singletons = [
            ['abstract' => 'Auth', 'class' => 'AuthManager'],
            ['abstract' => 'Batch', 'class' => 'BatchManager'],
            ['abstract' => 'Download', 'class' => 'DownloadManager'],
            ['abstract' => 'Jwt', 'class' => 'JwtConnector'],
            ['abstract' => 'Pay', 'class' => 'PayManager'],
            ['abstract' => 'Sms', 'class' => 'SmsManager'],
            ['abstract' => 'Upload', 'class' => 'UploadManager'],
        ];

        foreach ($singletons as $singleton) {
            $className = 'ApisFrame\\' . $singleton['abstract'] . '\\' . $singleton['class'];
            $this->app->singleton($singleton['abstract'], function ($app) use ($className) {
                return new $className($app);
            });
        }
    }

    /**
     * 启动任何应用服务
     *
     * @return void
     */
    public function boot()
    {
        /**
         * 资源
         */

        /*
         * 配置
         */
        $configFilePath = __DIR__ . '/Support/config/';
        $configPublishes = [];
        // 检查当前配置文件目录下所有文件
        $configFiles = array_diff(scandir($configFilePath), array('..', '.'));

        foreach ($configFiles as $configFile) {
            if (file_exists(config_path($configFile))) {
                $this->mergeConfigFrom($configFilePath . $configFile, preg_replace('/\.(.*)/', '', $configFile));
            } else {
                $configPublishes[$configFilePath . $configFile] = config_path($configFile);
            }
        }

        // 1、将配置文件复制到指定的发布位置
        $this->publishes($configPublishes);
        // 2、将配置文件与应用程序已发布的副本合并(参数1：配置文件路径，参数2：应用程序的配置文件副本的名称)
//        $this->mergeConfigFrom();

        /*
         * 路由(方法将自动确定应用程序的路由是否被缓存，如果路由已被缓存，则不会加载您的路由文件)
         */
//        $this->loadRoutesFrom();

        /*
         * 迁移
         */
//        $this->loadMigrationsFrom();

        /*
         * 翻译
         */
//        $this->loadTranslationsFrom();

        /*
         * 视图
         */
        // 1、视图
//        $this->loadViewsFrom();
        // 2、视图组件
//        $this->loadViewComponentsAs();

        /*
         * 命令
         */
//        $this->commands();

        Artisan::call('vendor:publish', ['--provider' => __CLASS__]);
    }


    public function mergeConfigFrom($path, $key)
    {
        if (!($this->app instanceof CachesConfiguration && $this->app->configurationIsCached())) {
            $config = $this->app->make('config');

            $config->set($key, array_merge_recursive(
                require $path, $config->get($key, [])
            ));
        }
    }
}
