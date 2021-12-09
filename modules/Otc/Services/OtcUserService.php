<?php

namespace Modules\Otc\Services;

//use Carbon\CarbonInterval;
use Modules\Core\Services\Traits\HasQuery;
use Modules\Otc\Models\OtcTrade;
use Modules\Otc\Models\OtcUser;

class OtcUserService
{
    use HasQuery;

    /**
     * @var OtcUser
     */
    protected $model;

    public function __construct(OtcUser $model)
    {
        $this->model = $model;
    }

    /**
     * 创建OTC用户
     *
     * @param $data
     * @return bool|\Illuminate\Database\Eloquent\Model|mixed
     * @throws \Modules\Core\Exceptions\ModelSaveException
     */
    public function firstOrCreate($data)
    {
        $otcUser = $this->one($data, ['exception' => false]);
        if (empty($otcUser)) {
            $data['enable_buy'] = OtcUser::BUY_ENABLE;
            $data['enable_sell'] = OtcUser::SELL_ENABLE;
            return $this->create($data);
        }
        return $otcUser;
    }

    public function updateTradeInfo($id)
    {
        /** @var OtcUser $user */
        $user = with_otc_user($id);
        $otcTradeService = resolve(OtcTradeService::class);
        //买单成功笔数
        $sellCount = $otcTradeService->count([
            'seller_id' => $user->id
        ]);

        //卖单成功笔数
        $buyCount = $otcTradeService->count([
            'buyer_id' => $user->id
        ]);
        //todo 付款跟确认时间比对，计算出平均交易时间
        //买家付款，卖家确认
        $tradeList = $otcTradeService->all([
            'seller_id'=>$id,
            'status'=>OtcTrade::STATUS_SUCCESS
            ]);
        if($tradeList->count() > 0)
        {

            $time = 0;
            foreach($tradeList as $trade)
            {
                $time += strtotime($trade->confirmed_at) - strtotime($trade->paid_at);
            }
            $time = intval($time / $tradeList->count());
            //$time = CarbonInterval::seconds($time)->cascade()->forHumans();
            $user->setAverageTime($time);
        }


        $user->setSuccessSell($sellCount);
        $user->setSuccessBuy($buyCount);
        $user->save();
        return true;
    }
}
