<?php


namespace Modules\Coin\Http\Controllers\Admin;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Coin\Models\CoinSystemWallet;
use Modules\Coin\Services\CoinService;
use Modules\Coin\Services\SystemWalletService;

/* 系统钱包 */

class SystemWalletController extends Controller
{

    public function index(SystemWalletService $systemWalletService)
    {

        $list = $systemWalletService->all();
        $coinService = resolve(CoinService::class);
        foreach ($list as $item) {
            $item->address_link = $coinService->queryChainLink($item->chain, $item->address);
        }
        return view('coin::admin.system_wallet.index', [
            'list' => $list
        ]);
    }


    /**
     * 创建钱包
     * @param CoinSystemWallet $coinSystemWallet
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(CoinSystemWallet $coinSystemWallet)
    {

        $typeList = [
            $coinSystemWallet::TYPE_WITHDRAW => '支出热钱包',
            $coinSystemWallet::TYPE_GAS => '补gas钱包',
        ];
        $levelList = [
            $coinSystemWallet::LEVEL_HOT_WALLET => '热钱包'
        ];
        $chainList = ['ETH' => 'ETH', 'BTC' => 'BTC', 'FIL' => 'FIL','TRX'=>'TRX'];
        return view('coin::admin.system_wallet.create', [
            'type' => $typeList,
            'level' => $levelList,
            'chain' => $chainList
        ]);
    }


    public function editInfo(Request $request,SystemWalletService $service){

        $id = $request->input('id');
        $info = $service->getById($id);
        return view('coin::admin.system_wallet.edit', [
            'info'=>$info
        ]);
    }
}
