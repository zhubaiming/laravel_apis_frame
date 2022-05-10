<?php

namespace ApisFrame;

use ApisFrame\Auth\AuthManager;
use ApisFrame\Batch\BatchManager;
use ApisFrame\Download\DownloadManager;
use ApisFrame\Jwt\JwtConnector;
use ApisFrame\Pay\PayManager;
use ApisFrame\Sms\SmsManager;
use ApisFrame\Upload\UploadManager;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;

class ApisFrameServiceProvider extends ServiceProvider
{
    private $paths = [];

    /**
     * 注册任何应用服务
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Auth', function ($app) {
            return new AuthManager($app);
        });
        $this->app->singleton('Batch', function ($app) {
            return new BatchManager($app);
        });
        $this->app->singleton('Download', function ($app) {
            return new DownloadManager($app);
        });
        $this->app->singleton('Jwt', function ($app) {
            return new JwtConnector($app);
        });
        $this->app->singleton('Pay', function ($app) {
            return new PayManager($app);
        });
        $this->app->singleton('Sms', function ($app) {
            return new SmsManager($app);
        });
        $this->app->singleton('Upload', function ($app) {
            return new UploadManager($app);
        });

        // 检查当前配置文件目录下所有文件
        $configFiles = array_diff(scandir(__DIR__ . '/Support/config/'), array('..', '.'));

        foreach ($configFiles as $configFile) {
            // 如果原配置文件目录中含有当前配置文件
            if (file_exists(config_path($configFile))) {
                $this->mergeConfigFrom(__DIR__ . '/Support/config/' . $configFile, preg_replace('/\.(.*)/', '', 'cors.php'));
            } else {
                $this->paths[__DIR__ . '/Support/config/' . $configFile] = config_path($configFile);
            }
        }
    }

    /**
     * 启动任何应用服务
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes($this->paths);

        Artisan::call('vendor:publish', ['--provider' => 'App\Providers\AppServiceProvider']);
    }
}
