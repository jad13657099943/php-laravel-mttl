<?php


namespace Modules\Coin\Http\Controllers\Admin\Api;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Coin\Components\TokenIO\TokenIO;
use Modules\Coin\Http\Requests\SystemWalletRequest;
use Modules\Coin\Models\CoinTokenioNotice;
use Modules\Coin\Services\CoinService;
use Modules\Coin\Services\SystemWalletService;

class SystemWalletApiController extends Controller
{

    /**
     * 查询系统钱包地址链上余额
     * @param Request $request
     * @param SystemWalletService $systemWalletService
     * @return string[]
     */
    public function balance(Request $request, SystemWalletService $systemWalletService)
    {

        $id = $request->input('id');
        $info = $systemWalletService->getById($id);

        $tokenIO = resolve(TokenIO::class);
        ['balance' => $balance] = $tokenIO->chainBalance($info->chain, $info->address);
        return [
            'balance' => $balance == '-1' ? '同步查询异常' : $balance
        ];
    }


    /**
     * 创建系统热钱包
     * @param SystemWalletRequest $request
     * @param SystemWalletService $service
     * @return string[]
     * @throws \Modules\Coin\Exceptions\AdminCommonException
     * @throws \Modules\Core\Exceptions\ModelSaveException
     */
    public function create(SystemWalletRequest $request, SystemWalletService $service)
    {

        $data = $request->all();

        //每种链的每个类型level类型钱包只能有一个
        $where[] = ['chain', '=', $data['chain']];
        $where[] = ['level', '=', $data['level']];
        $version = $data['tokenio_version'];
        if ($version == 1) {
            $service->createWallet($data);
        } else {
            $serviceV2 = resolve(\Modules\Coinv2\Services\SystemWalletService::class);
            $serviceV2->createWallet($data);
        }


        return ['msg' => '添加成功'];
    }


    public function editInfo(SystemWalletRequest $request,SystemWalletService $service){

        $id = $request->input('name');
        $data = [
            'address'=>$request->input('address'),
            'notice_max'=>$request->input('notice_max',0),
            'notice_min'=>$request->input('notice_min',0),
            'notice'=>$request->input('notice'),
        ];

        $info = $service->getById($id);
        //还是手动改吧，不做直接修改
        throw new \Exception('请联系技术人员修改');
    }

}
