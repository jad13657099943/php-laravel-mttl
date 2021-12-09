<?php


namespace Modules\Coin\Http\Controllers\Api;


use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Modules\Coin\Models\CoinTrade;
use Modules\Coin\Services\CoinService;
use Modules\Coin\Services\TokenioNoticeService;
use Modules\Coin\Services\TradeService;

class TestController extends Controller
{

    public function test(){

        /*$app = resolve(Container::class);
        $s = $app->config->get('coin::tokenioV2');
        print_r($s);*/
        //echo $this->config->get('coin::use_tokenio_version');

        //echo hash_hmac('sha256', '123456', '1', false);

        /*$s = resolve(CoinService::class);
        $res = $s->syncPrice();
        print_r($res);*/

        /*$s = resolve(TokenioNoticeService::class);
        $info = $s->getById(1);
        $s->processRecharge($info);*/

        /** @var TradeService $tradeService */
        /*$tradeService = resolve(TradeService::class);
        $tradeService->query()
            ->where('tokenio_version',1)
            ->whereIn('state', [CoinTrade::STATE_NOT_SUFFICIENT_FUNDS, CoinTrade::STATE_BROADCASTED])
            ->orderBy('id', 'asc') // 先进先出
            ->chunk(100, function (Collection $trades) use ($tradeService) {
                $trades->each(function (CoinTrade $trade) use ($tradeService) {
                    $tradeService->syncTradeState($trade);
                });
            });*/


        exit();

    }

}
