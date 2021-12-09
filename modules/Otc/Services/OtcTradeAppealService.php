<?php


namespace Modules\Otc\Services;

use Modules\Core\Services\Traits\HasQuery;
use Modules\Otc\Events\TradeAppealCreated;
use Modules\Otc\Exceptions\OtcTradeAppealException;
use Modules\Otc\Exceptions\OtcTradeException;
use Modules\Otc\Models\OtcTrade;
use Modules\Otc\Models\OtcTradeAppeal;
use App\Models\User;

class OtcTradeAppealService
{
    use HasQuery {
        create as queryCreate;
    }

    /**
     * @var OtcTradeAppeal
     */
    protected $model;

    public function __construct(OtcTradeAppeal $model)
    {
        $this->model = $model;
    }


    /**
     * @param $user
     * @param  array  $data
     * @param  array  $options
     * @throws OtcTradeException
     */
    public function create($user, array $data, array $options = [])
    {
        $otcTradeService = resolve(OtcTradeService::class);
        /** @var OtcTrade $otcTrade */
        $otcTrade = $otcTradeService->one(['id' => $data['otc_trade_id']]);
        //用户在撮合订单中身份，是买家还是卖家
        $userIdentify = $otcTrade->userIdentity($user, false);

        //用户是否提交过申诉资料
        if ($this->hasTradeAppealByUser($otcTrade, $user)) {
            throw new OtcTradeAppealException(trans("otc::exception.您已经提交过申诉资料"));
        }

        $data['type']      = $otcTrade->type;
        $data['user_type'] = $userIdentify;
        $data['user_id']   = $user->id;
        $data['status']    = OtcTradeAppeal::STATUS_WAITING;
        $data['no']        = generate_otc_no(OtcTradeAppeal::class);
        $otcTradeAppeal    = \DB::transaction(function () use ($data, $otcTrade, $userIdentify) {
            //标记OtcTrade状态
            if ($userIdentify == OtcTradeAppeal::TYPE_BUY) {
                $otcTrade->setBuyerAppeal()->saveIfFail();
            } else {
                $otcTrade->setSellerAppeal()->saveIfFail();
            }
            /** @var OtcTradeAppeal $otcTradeAppeal */
            $otcTradeAppeal = $this->queryCreate($data);

            return $otcTradeAppeal;
        });
        event(new TradeAppealCreated($otcTradeAppeal));

        return $otcTradeAppeal;
    }

    /**
     * 用户是否有申诉记录
     *
     * @param  OtcTrade  $otcTrade
     * @param  User  $user
     * @return int
     */
    public function hasTradeAppealByUser(OtcTrade $otcTrade, User $user)
    {
        return $this->has([
            'user_id'      => $user->id,
            'otc_trade_id' => $otcTrade->id,
        ]);
    }


    /**
     *
     * @param $user
     * @param $otcTradeId
     * @return \Illuminate\Database\Eloquent\Model|mixed
     */
    public function getByUserAndOtcTrade($user, $otcTradeId)
    {
        $user     = with_user($user);
        $otcTrade = with_otc_trade($otcTradeId);
        //买家或者卖家都可以查看申诉单
        if ($otcTrade->buyer_id == $user->id || $otcTrade->seller_id == $user->id) {
            return $this->one([
                'otc_trade_id' => $otcTradeId,
            ]);
        }
        throw new OtcTradeAppealException(trans('otc::exception.未找到申诉记录'));


    }
}
