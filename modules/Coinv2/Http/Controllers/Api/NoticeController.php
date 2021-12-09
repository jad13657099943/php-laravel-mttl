<?php

namespace Modules\Coinv2\Http\Controllers\Api;


use Illuminate\Routing\Controller;
use Modules\Coinv2\Services\TokenioNoticeService;
use Psr\Http\Message\ServerRequestInterface as Request;

class NoticeController extends Controller
{

    /**
     * @param Request $request
     * @param TokenioNoticeService $noticeService
     *
     * @return array
     * @throws \Modules\Coinv2\Components\TokenIO\Exceptions\SignatureVerifyException
     */
    public function tokenio(Request $request, TokenioNoticeService $noticeService)
    {
        $noticeService->receiveViaRequest($request);

        return json_encode([
            'code' => 0, //通知成功状态=0(Java那边要求)
        ]);
    }
}
