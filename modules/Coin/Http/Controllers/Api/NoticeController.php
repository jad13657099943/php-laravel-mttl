<?php

namespace Modules\Coin\Http\Controllers\Api;

use Modules\Coin\Http\Controllers\Controller;
use Modules\Coin\Services\TokenioNoticeService;
use Psr\Http\Message\ServerRequestInterface as Request;

class NoticeController extends Controller
{

    /**
     * @param Request $request
     * @param TokenioNoticeService $noticeService
     *
     * @return array
     * @throws \Modules\Coin\Components\TokenIO\Exceptions\SignatureVerifyException
     */
    public function tokenio(Request $request, TokenioNoticeService $noticeService)
    {
        $noticeService->receiveViaRequest($request);

        return [
            'success' => true,
        ];
    }
}
