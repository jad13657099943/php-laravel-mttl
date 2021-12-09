<?php


namespace Modules\Coinv2\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Coinv2\Services\TokenioNoticeService;
use Modules\Coinv2\Services\TradeService;

class TokenioNoticeProcessJobV2 implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array
     */
    public $options;

    /**
     * 创建一个新的任务实例
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * 执行任务
     * 处理tokenio未完成的任务
     * 只处理tokenio V2版本的通知
     * @return void
     */
    public function handle()
    {
        $service = resolve(TokenioNoticeService::class);
        $list = $service->all([
            'state' => 0,
            'version' => 2,
        ], [
            'limit' => 10,
            'orderBy' => ['id', 'asc']
        ]);

        if ($list->isNotEmpty()) {

            foreach ($list as $item) {
                $service->process($item);
            }
        }
    }

}
