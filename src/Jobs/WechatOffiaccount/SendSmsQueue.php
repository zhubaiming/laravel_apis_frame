<?php

namespace ApisFrame\Jobs\WechatOffiaccount;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendSmsQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $url;

    public string $method;

    public string $queueName;

    public array $parameters;

    /**
     * 该任务的最大失败次数
     *
     * @var int
     */
    public $tries = 3;

    /**
     * 该任务允许运行的最大时长（单位：秒）
     *
     * @var int
     */
    public $timeout = 30;

    /**
     * 延迟重试任务时长（单位：秒）
     *
     * @var int
     */
    public $backoff = [60, 120, 300];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        string $url,
        string $method,
        string $queueName,
        array  $parameters = []
    )
    {
        $this->onQueue($queueName);

        $this->onConnection('redis');

        $this->url = $url;

        $this->method = $method;

        $this->queueName = $queueName;

        $this->parameters = $parameters;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $method = strtolower($this->method) . 'Http';

        $this->{$method}();
    }

    public function failed(\Throwable $exception)
    {
        Log::info('========================================================================================================================================================================');
        Log::info($exception);
    }

    private function postHttp()
    {
        $client = new Client();

        $response = $client->post($this->url, [
            'body' => json_encode($this->parameters, 320),
            'header' => [
                'Content-Type' => 'application/json'
            ]
        ]);

        Log::info($response->getBody()->getContents());

        return json_decode($response->getBody()->getContents(), true);
    }
}
