<?php


namespace Modules\Coin\Http\Controllers\Admin;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Coin\Services\CoinService;

class CoinController extends Controller
{
    public function index()
    {
        return view('coin::admin.coin.index');
    }

    public function edit(Request $request, CoinService $coinService)
    {

        $id = $request->input('id');
        $info = $coinService->getById($id, ['whereEnabled' => false]);

        return view('coin::admin.coin.edit', [
            'info' => $info
        ]);
    }


}
