<?php

namespace Modules\Coin\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Coin\Events\TokenioNoticeReceived;
use Modules\Coin\Services\TokenioNoticeService;

class TokenioNoticeReceivedListener implements ShouldQueue
{
    /**
     * @var TokenioNoticeService
     */
    protected $noticeService;

    public function __construct(TokenioNoticeService $noticeService)
    {
        $this->noticeService = $noticeService;
    }

    /**
     * @param TokenioNoticeReceived $event
     */
    public function handle(TokenioNoticeReceived $event)
    {
        //不走监听形式，用轮询方式触发
        $this->noticeService->process($event->coinTokenioNotice);
    }
}
