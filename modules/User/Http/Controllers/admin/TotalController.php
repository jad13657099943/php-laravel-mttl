<?php


namespace Modules\User\Http\Controllers\admin;


use App\Models\User;
use Illuminate\Routing\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Coin\Models\Coin;
use Modules\Coin\Models\CoinAsset;
use Modules\Coin\Models\CoinLog;
use Modules\Coin\Models\CoinRecharge;
use Modules\Coin\Models\CoinWithdraw;
use Modules\User\Models\ProjectUser;

class TotalController extends Controller
{

    /**
     * 用户统计数据
     */
    public function total(Request $request)
    {

        $mark = $request->input('mark', null);
        $show=$request->input('user',null);
        $whereMark = '1=1';
        if ($mark) {
            $mark = strtoupper($mark);
            $whereMark = "user_id in (select user_id from ti_project_user WHERE show_userid LIKE '$mark%')";
        }
        if ($show){
            $uid= ProjectUser::query()->where('show_userid',$show)->value('user_id');
            $whereMark = "user_id in (select user_id from ti_user_invitation_tree WHERE JSON_CONTAINS(data,'$uid'))";
        }

        $dayStart = Carbon::now()->startOfDay();
        $dayEnd = Carbon::now()->endOfDay();
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        $yearStart = Carbon::now()->startOfYear();
        $yearEnd = Carbon::now()->endOfYear();

        //币种资产充提统计
        $coin = Coin::query()->where('status', 1)->pluck('symbol');
        $coinTotal = [];
        foreach ($coin as $item) {

            //剩余总数
            $balance = CoinAsset::query()->where('symbol', $item)
                ->sum('balance');
            //充值
            $recharge['total'] = CoinRecharge::query()->where('symbol', $item)
                ->select(\DB::raw('sum(value) as total'), \DB::raw('count(*) as num'))
                ->whereRaw($whereMark)
                ->first();
            $recharge['total']=empty($recharge['total'])?['total'=>0,'num'=>0]:$recharge['total']->toArray();
            $recharge['day'] = CoinRecharge::query()->where('symbol', $item)
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->select(\DB::raw('sum(value) as total'), \DB::raw('count(*) as num'))
                ->whereRaw($whereMark)
                ->first();
            $recharge['day']=empty($recharge['day'])?['total'=>0,'num'=>0]:$recharge['day']->toArray();
            $recharge['week'] = CoinRecharge::query()->where('symbol', $item)
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->select(\DB::raw('sum(value) as total'), \DB::raw('count(*) as num'))
                ->whereRaw($whereMark)
                ->first();
            $recharge['week']=empty($recharge['week'])?['total'=>0,'num'=>0]:$recharge['week']->toArray();
            $recharge['month'] = CoinRecharge::query()->where('symbol', $item)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->select(\DB::raw('sum(value) as total'), \DB::raw('count(*) as num'))
                ->whereRaw($whereMark)
                ->first();
            $recharge['month']=empty($recharge['month'])?['total'=>0,'num'=>0]:$recharge['month']->toArray();
            $recharge['year'] = CoinRecharge::query()->where('symbol', $item)
                ->whereBetween('created_at', [$yearStart, $yearEnd])
                ->select(\DB::raw('sum(value) as total'), \DB::raw('count(*) as num'))
                ->whereRaw($whereMark)
                ->first();
            $recharge['year']=empty($recharge['year'])?['total'=>0,'num'=>0]:$recharge['year']->toArray();
            //提现
            $withdraw['total'] = CoinWithdraw::query()->where('symbol', $item)
                ->where('state', 2)
                ->select(\DB::raw('sum(num) as total'), \DB::raw('count(*) as num'))
                ->whereRaw($whereMark)
                ->first();
            $withdraw['total']=empty($withdraw['total'])?['total'=>0,'num'=>0]:$withdraw['total']->toArray();
            $withdraw['day'] = CoinWithdraw::query()->where('symbol', $item)
                ->where('state', 2)
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->select(\DB::raw('sum(num) as total'), \DB::raw('count(*) as num'))
                ->whereRaw($whereMark)
                ->first();
            $withdraw['day']=empty($withdraw['day'])?['total'=>0,'num'=>0]:$withdraw['day']->toArray();
            $withdraw['week'] = CoinWithdraw::query()->where('symbol', $item)
                ->where('state', 2)
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->select(\DB::raw('sum(num) as total'), \DB::raw('count(*) as num'))
                ->whereRaw($whereMark)
                ->first();
            $withdraw['week']=empty($withdraw['week'])?['total'=>0,'num'=>0]:$withdraw['week']->toArray();
            $withdraw['month'] = CoinWithdraw::query()->where('symbol', $item)
                ->where('state', 2)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->select(\DB::raw('sum(num) as total'), \DB::raw('count(*) as num'))
                ->whereRaw($whereMark)
                ->first();
            $withdraw['month']=empty($withdraw['month'])?['total'=>0,'num'=>0]:$withdraw['month']->toArray();
            $withdraw['year'] = CoinWithdraw::query()->where('symbol', $item)
                ->where('state', 2)
                ->whereBetween('created_at', [$yearStart, $yearEnd])
                ->select(\DB::raw('sum(num) as total'), \DB::raw('count(*) as num'))
                ->whereRaw($whereMark)
                ->first();
            $withdraw['year']=empty($withdraw['year'])?['total'=>0,'num'=>0]:$withdraw['year']->toArray();
            //后台拨币总金额
            $adminAddNum = CoinLog::query()->where('action', 'admin_change')
                ->where('symbol', $item)
                ->where('num', '>', 0)
                ->whereRaw($whereMark)
                ->sum('num');
            $adminDecNum = CoinLog::query()->where('action', 'admin_change')
                ->where('symbol', $item)
                ->where('num', '<', 0)
                ->whereRaw($whereMark)
                ->sum('num');
            $coinTotal[$item] = [
                'balance' => floatval($balance),
                'admin_add_num' => floatval($adminAddNum),
                'admin_dec_num' => floatval($adminDecNum),
                'recharge' => $recharge,
                'withdraw' => $withdraw,
            ];
        }


        ////////////////// 具体业务 ////////////////////////

        //注册人数
        $user['total'] = ProjectUser::query()->whereRaw($whereMark )->count();
        $user['day'] = ProjectUser::query()->whereRaw($whereMark )
            ->whereBetween('created_at', [$dayStart, $dayEnd])->count();
        $user['week'] = ProjectUser::query()->whereRaw($whereMark )
            ->whereBetween('created_at', [$weekStart, $weekEnd])->count();
        $user['month'] = ProjectUser::query()->whereRaw($whereMark )
            ->whereBetween('created_at', [$monthStart, $monthEnd])->count();
        $user['year'] = ProjectUser::query()->whereRaw($whereMark )
            ->whereBetween('created_at', [$yearStart, $yearEnd])->count();

        //会员等级人数统计（具体业务具体分析）
        $gradeSet = ProjectUser::$gradeMap;
        $grade = ProjectUser::query()
            ->whereRaw($whereMark)
            ->groupBy('grade')
            ->select('grade', \DB::raw('count(*) as num'))
            ->pluck('num', 'grade');
        $grade=empty($grade)?['grade'=>0,'num'=>0]:$grade->toArray();
        $gradeTotal = [];
        for ($i = 0; $i < count($gradeSet); $i++) {
            $gradeTotal[$i] = [
                'name' => $gradeSet[$i],
                'num' => $grade[$i] ?? 0
            ];
        }

        return view('user::admin.total.total', [
            'user' => $user,
            'grade' => $gradeTotal ?? [],
            'coin' => $coinTotal,
            'param' => ['mark' => $mark],
            'show'=>$show
        ]);
    }


    /**
     * 手动调整资产
     */
    public function asset()
    {

        $coin = Coin::query()->where('status', 1)->pluck('symbol');
        return view('user::admin.total.asset', [
            'coin' => $coin,
        ]);
    }

}
