<?php


namespace Modules\Otc\Http\Controllers\Admin\Api;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Otc\Services\OtcCoinService;

class OtcCoinController extends Controller
{

    public function index(Request $request, OtcCoinService $otcCoinService)
    {

        $list = $otcCoinService->paginate(null, [
            'exception' => false,
            'orderBy' => ['sort', 'desc']
        ]);

        foreach ($list as &$item) {
            $item->buy_text = $item->buy == 1 ? '是' : '否';
            $item->sell_text = $item->sell == 1 ? '是' : '否';
            $item->min = floatval($item->min);
            $item->max = floatval($item->max);
            $item->price_cny = $item->price_cny == 0 ? '取接口实时价' : floatval($item->price_cny);
            $item->status_text = $item->is_enable == 1 ? '开启' : '关闭';
        }
        return $list;
    }

}
