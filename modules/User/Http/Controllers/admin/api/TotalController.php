<?php


namespace Modules\User\Http\Controllers\admin\api;


use App\Models\User;
use ccxt\therock;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Coin\Services\BalanceChangeService;
use Modules\Core\Translate\TranslateExpression;
use Modules\User\Models\Log;
use Modules\User\Models\ProjectUser;

class TotalController extends Controller
{

    /**
     * 手动调整资产
     * @param Request $request
     * @return string[]
     * @throws \Modules\Core\Exceptions\ModelSaveException
     * @throws \Throwable
     */
    public function asset(Request $request)
    {

        // throw new \Exception('该功能暂未开通'); //需要手动打开
        $coin = $request->input('coin');
        $num = $request->input('number');
        $state = $request->input('state', 0);
        $userId = $request->input('user_id');
        $userId = ProjectUser::query()->where('show_userid', $userId)->value('user_id');
        if (empty($userId)) {
            throw new \Exception('该会员ID不存在');
        }
        $service = resolve(BalanceChangeService::class);
        if ($state == 1) {
            $obj = $service->to($userId);
        } else {
            $obj = $service->from($userId);
        }

        $obj->withSymbol($coin)
            ->withNum($num)
            ->withModule('coin.admin_change')
            ->withNo(0)
            ->withInfo(
                new TranslateExpression(
                    'coin::message.后台操作'
                )
            )
            ->change(['transaction' => true]);

        //日志
        $states=[
            1=>'增加',
            0=>'减少'
        ];
        $user=$request->user();
        $admin=$user['id'];
        $uid=$userId;
        $type=3;
        $log='变更资产,'.$states[$state].$num.$coin;
        Log::addLog($admin,$uid,$type,$log);

        return ['msg' => '操作成功'];
    }

}
