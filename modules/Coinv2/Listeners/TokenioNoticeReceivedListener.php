<?php

namespace Modules\Coinv2\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Coin\Events\TokenioNoticeReceived;
use Modules\Coinv2\Services\TokenioNoticeService;

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
        $this->noticeService->process($event->coinTokenioNotice);
    }
}
