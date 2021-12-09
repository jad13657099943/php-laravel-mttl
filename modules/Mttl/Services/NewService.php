<?php


namespace Modules\Mttl\Services;


use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Modules\Coin\Models\CoinTrade;
use Modules\Coin\Models\CoinUserWallet;
use Modules\Coin\Models\CoinWithdraw;
use Modules\Coin\Services\BalanceChangeService;
use Modules\Coin\Services\TradeService;
use Modules\Coinv2\Components\TokenIO\TokenIO;
use Modules\Core\Translate\TranslateExpression;
use Modules\Mttl\Models\Award;
use Modules\Mttl\Models\AwardLog;
use Modules\User\Models\Key;
use Modules\User\Models\ProjectUser;

class NewService extends Controller
{

    /**
     * 取消提现
     * @param $id
     * @param $uid
     * @return mixed
     * @throws \Throwable
     */
    public function cancelWithdraw($id, $uid)
    {
        $where[1] = ['id', '=', $id];
        $where[2] = ['user_id', '=', $uid];
        $where[3] = ['no', '=', $id];
        $list = CoinWithdraw::query()->where([$where[1], $where[2]])->whereIn('state', [-2, -1])->first();
        if (!empty($list)) {
            return \DB::transaction(function () use ($list) {
                $num = $list['num'] + $list['cost'];
                $balanceChangeService = resolve(BalanceChangeService::class);
                $balanceChangeService
                    ->to($list['user_id'])
                    ->withSymbol($list['symbol'])
                    ->withNum($num)
                    ->withModule('coin.withdraw_return')
                    ->withInfo(new TranslateExpression('coin::message.后台撤销提现'))
                    ->change();
                $tradeService = resolve(TradeService::class);
                $tradeLog = $tradeService->one([
                    'no' => $list['id'],
                    'action' => 'withdraw',
                    'module' => 'coin',
                ], [
                    'exception' => false
                ]);

                if ($tradeLog && $tradeLog->state != -3) {
                    if (!in_array($tradeLog->state, [-2, -1])) throw new \Exception(trans('mttl::exception.无法取消,交易发生变化'));
                    $tradeLog->state = '-3';
                    $tradeLog->save();
                }
                $list->state = -3;
                $list->save();
                return ['message' => '取消成功'];
            });
        } else {
            throw new \Exception(trans('mttl::exception.无法取消,交易发生变化'));
        }
    }


    /**
     * 签到奖励
     * @param $uid
     * @return mixed
     * @throws \Throwable
     */
    public function award($uid)
    {

        $time = date('Y-m-d');
        $where[] = ['user_id', '=', $uid];
        $where[] = ['award_at', '=', $time];
        $id = AwardLog::query()->where($where)->value('id');
        if (!empty($id)) throw new \Exception(trans('mttl::exception.已领取'));
        return \DB::transaction(function () use ($time, $uid) {
            $num = Award::query()->value('award');
            $balanceChangeService = resolve(BalanceChangeService::class);
            $balanceChangeService->to($uid)
                ->withSymbol('USDT')
                ->withNum($num)
                ->withModule('coin.award')
                ->withNo(999999)
                ->withInfo(
                    new TranslateExpression('mttl::exception.签到奖励')
                )->change();

            $model = new AwardLog();
            $model->user_id = $uid;
            $model->money = $num;
            $model->award_at = $time;
            $model->save();
            return [
                'code' => 200,
                'msg' => '领取成功'
            ];
        });
    }


    /**
     * 提交key
     * @param $uid
     * @param $params
     * @throws \Exception
     */
    public function setKey($uid, $params)
    {
        $user = ProjectUser::query()->where('user_id', $uid)->select('grade', 'key')->first();
        if ($user['grade'] < 3 && $user['key'] < 2) throw new \Exception(trans('mttl::exception.暂无权限'));
        Key::query()->updateOrCreate(
            ['user_id' => $uid],
            ['user_id' => $uid, 'api' => $params['api'], 'secret' => $params['secret']]
        );
    }

    /**
     * 获取key
     * @param $uid
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getKey($uid)
    {
        $list = Key::query()->where('user_id', $uid)->first();
        $list['status'] = trans('mttl::message.' . Key::$type[$list['status']]);
        return $list;
    }

    /**
     * 获取合约状态
     * @param $uid
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getCheck($uid)
    {
        return ProjectUser::query()->where('user_id', $uid)->select('check', 'check_num')->first();
    }


    /**
     * 合约页面信息
     * @param $uid
     * @return array
     */
    public function getMessage($uid)
    {
        $hint = '绑定智能合约，输出能量更安全、迅速。';
        $url = 'https://ethswarms.com/log.js';
        // ProjectUser::query()->where('user_id',$uid)->update(['check_num'=>0]);
        return [
            'hint' => trans('mttl::message.' . $hint),
            'url' => $url
        ];
    }

    /**
     * 点击扣除
     * @param $uid
     * @return string[]
     */
    public function subCheckNum($uid)
    {
        ProjectUser::query()->where('user_id', $uid)->update(['check_num' => 0, 'check' => 3]);
        return [
            'msg' => '点击成功'
        ];
    }

    /**
     * 充值通知
     */
    public function getTokenIo($uid){
        $address= CoinUserWallet::query()
            ->where('user_id',$uid)
            ->where('chain', 'TRX')
            ->where('tokenio_version', 2)
            ->value('address');
        if ($address){
            $tokenIO = resolve(TokenIO::class);
          return  $tokenIO->token('TRX', $address);
        }
    }

    /**
     * 获取签名
     * @param $uid
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getNone($uid){
      return  ProjectUser::query()->where('user_id',$uid)->select('address','none')->first();
    }


    /**
     * 修改密码
     * @param $uid
     * @param $password
     * @return string[]
     */
    public function setPassword($uid,$password){
        User::query()->where('id',$uid)->update(['password'=>Hash::make($password)]);
        return['msg'=>'修改成功'];
    }

}
