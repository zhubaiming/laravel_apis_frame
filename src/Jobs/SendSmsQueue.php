<?php

namespace ApisFrame\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSmsQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $response;

    /**
     * 创建一个新的任务实例
     *
     * @return void
     */
    public function __construct($syncCallBack)
    {
        $this->onQueue('sendSms');

        $this->onConnection('redis');

        $this->response = $syncCallBack();
//        $this->response = true;
    }

    /**
     * 运行任务
     *
     * @return void
     */
    public function handle()
    {
        Log::info('aaaaaaaaaa');
        if ($this->response) {
            Log::info('true');
        } else {
            Log::info('false');
        }
    }
}
