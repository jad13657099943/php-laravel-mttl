<?php

namespace Modules\Otc\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Otc\Models\OtcTradeAppeal;
use Modules\Otc\Services\OtcTradeAppealService;

class OtcTradeAppealController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(OtcTradeAppeal $otcTradeAppeal)
    {

        $userType = $otcTradeAppeal::$userTypeMap;
        $type = $otcTradeAppeal::$typeMap;
        $status = $otcTradeAppeal::$statusMap;

        return view('otc::admin.otc_appeal.index', [
            'type' => $type,
            'status' => $status,
            'userType' => $userType,
        ]);
    }

    /**
     * 编辑
     * @param Request $request
     * @param OtcTradeAppealService $otcTradeAppealService
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Request $request, OtcTradeAppealService $otcTradeAppealService)
    {

        $id = $request->input('id');
        $info = $otcTradeAppealService->getById($id, [
            'with' => 'user'
        ]);

        $info->type_text = $info->type_text;
        $info->status_text = $info->status_text;
        $info->user_type_text = $info->user_type_text;

        return view('otc::admin.otc_appeal.edit', [
            'info' => $info,
        ]);
    }
}
