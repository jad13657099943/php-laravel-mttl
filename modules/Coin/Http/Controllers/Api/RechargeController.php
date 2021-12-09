<?php


namespace Modules\Coin\Http\Controllers\Api;


use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Modules\Coin\Models\CoinUserWallet;
use Modules\Coin\Services\RechargeService;
use Modules\Coinv2\Components\TokenIO\TokenIO;

class RechargeController
{
    /**
     * 充值记录
     * @param Request $request
     * @param RechargeService $rechargeService
     * @return LengthAwarePaginator
     */
    public function list(Request $request, RechargeService $rechargeService)
    {
        $user_id = with_user_id($request->user());
        $wallet = CoinUserWallet::query()
            ->where('chain', 'TRX')
            ->where('user_id', $user_id)
            ->where('tokenio_version', 2)
            ->first();
        if ($wallet) {
            $tokenIO = resolve(TokenIO::class);
            $tokenIO->token('TRX', $wallet->address);
        }
        return $rechargeService->paginate([
            ['user_id', '=', with_user_id($request->user())]
        ],[
            'orderBy'=>['created_at','desc']
        ]);
    }
}
