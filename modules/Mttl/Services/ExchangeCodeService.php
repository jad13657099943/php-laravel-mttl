<?php


namespace Modules\Mttl\Services;


use Illuminate\Database\Eloquent\Model;
use Modules\Coin\Services\BalanceChangeService;
use Modules\Core\Models\Frontend\BaseUser;
use Modules\Core\Services\Traits\HasQuery;
use Modules\Core\Translate\TranslateExpression;
use Modules\Mttl\Models\ExchangeCode;

class ExchangeCodeService
{
    use HasQuery;

    public function __construct(ExchangeCode $model)
    {
        $this->model = $model;
    }

    /**
     * 序号兑换
     * @param ExchangeCode|Model $model
     * @param BaseUser $user
     * @param integer $user_count
     * @return array
     * @throws
     */
    public function exchange($model, $user, $user_count)
    {
        return \DB::transaction(function () use ($model, $user, $user_count) {

            $balanceService = resolve(BalanceChangeService::class);
            $balanceService->to($user->id)
                ->withNum($model->amount)
                ->withSymbol('USDT')
                ->withNo($model->id)
                ->withInfo(new TranslateExpression('mttl::message.序号兑换'))
                ->withModule('mttl.number_exchange')
                ->change();

            \DB::table('mttl_exchange_code')->where('id', $model->id)
                ->increment('received_count', 1);
            if ($user_count == 0) {
                \DB::table('mttl_exchange_code')->where('id', $model->id)
                    ->increment('received_users', 1);
            }

            return ['res' => true];
        });
    }
}
