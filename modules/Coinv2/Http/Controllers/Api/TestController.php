<?php


namespace Modules\Coinv2\Http\Controllers\Api;


use Illuminate\Container\Container;
use Illuminate\Routing\Controller;
use Modules\Coinv2\Services\CoinService;
use Modules\Coinv2\Services\TokenioNoticeService;
use Modules\Coinv2\Services\TradeService;
use Modules\Coinv2\Services\UserWalletService;

class TestController extends Controller
{

    public function test(){

        $s = resolve(UserWalletService::class);
        //$s->createWithChain(1,'TRX');

        /*$s = resolve(CoinService::class);
        $s->checkAddress('TRC20.USDT','TL91p5qVopHLvPG3Nv1gqRo5jbxNjhCkuS');*/

        $s = resolve(UserWalletService::class);
        $s->createWithChain(30,'TRX');

        /*$s = resolve(TradeService::class);
        $info = $s->getById(3);
        $s->processing($info);*/
        exit();

        $service = resolve(TokenioNoticeService::class);
        $item = $service->getById(290);
        $service->process($item);

    }

}
