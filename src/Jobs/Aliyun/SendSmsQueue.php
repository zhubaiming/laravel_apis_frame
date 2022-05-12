<?php

namespace ApisFrame\Jobs\Aliyun;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use ApisFrame\Facades\Sms;
use ApisFrame\Support\enums\AliyunSmsError;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendSmsQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $logChannel = 'smsAliyun';

    public Dysmsapi $client;

    public array $phoneNumbers;

    public $request;

    public string $method;

    public string $queueName;

    /**
     * 该任务的最大失败次数
     *
     * @var int
     */
    public $tries = 1;

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
    public $backoff = 120;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        Dysmsapi $client,
        array    $phoneNumbers,
                 $request,
        string   $method,
        string   $queueName
    )
    {
        $this->onQueue($queueName);

        $this->onConnection('redis');

        $this->client = $client;

        $this->phoneNumbers = $phoneNumbers;

        $this->request = $request;

        $this->method = $method;

        $this->queueName = $queueName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->existsMethod()) {
            $this->createData();

            $response = $this->client->{$this->method}($this->request)->body->toMap();

            $this->processResponse($response);
        } else {
            $this->fail();
        }
    }

    public function failed(string $reason = null)
    {
        // 发送公众号消息给管理员
        $this->sendWechatRemind($reason);
    }

    private function existsMethod()
    {
        if (!method_exists($this->client, $this->method)) {
            Log::channel($this->logChannel)->error("短信下发失败(20002)：(当前队列：{$this->queueName}) SDK 中方法【{$this->method}】不存在");
            return false;
        } else {
            return true;
        }
    }

    private function processResponse(array $response)
    {
        if ('OK' === $response['Code']) {
            $this->updateSuccessData();
            Log::info(json_encode($response, 320));
            return $response;
        } else {
            $reason = AliyunSmsError::APIERROR[$response['Code']];
            Log::channel($this->logChannel)->error("短信下发失败(20003)：(当前队列：{$this->queueName}) SDK Api 调用失败，失败原因（{$reason}）");
//            $this->updateErrorData($phoneNumbers);
            $this->fail($reason);
        }
    }

    private function createData()
    {
//        DB::beginTransaction();
//        try {
//            DB::table()->insert();
//            DB::commit();
//        } catch (QueryException $e) {
//            DB::rollBack();
//        }
    }

    private function updateSuccessData()
    {

    }

    private function updateErrorData()
    {

    }

    private function sendWechatRemind(string $reason)
    {
        $_wechatConfig = config('sms.wechatOffiaccount') ?? false;
        if ($_wechatConfig) {
            $parameters = [
                'touser' => $_wechatConfig['adminManagerOpenId'],
                'template_id' => $_wechatConfig['SmsSendErrorTemplateId'],
                'data' => [
                    'datetime' => [
                        'value' => date('Y-m-d H:i:s'),
                        'color' => '#795548'
                    ],
                    'server' => [
                        'value' => '阿里云',
                        'color' => '#03A9F4'
                    ],
                    'reason' => [
                        'value' => $reason,
                        'color' => '#F44336'
                    ]
                ]
            ];

            Sms::guard('wechatOffiaccount')->sendSms($parameters, 'sendWechatOffiaccountRemind');
        } else {

        }
    }
}
