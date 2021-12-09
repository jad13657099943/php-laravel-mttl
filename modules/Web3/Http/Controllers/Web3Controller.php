<?php

namespace Modules\Web3\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Web3\Services\TronService;
use Modules\Web3\Services\Web3Service;

class Web3Controller extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $service = new Web3Service();
        $abi = '[{"inputs":[{"internalType":"contract IERC20","name":"_usdt","type":"address"}],"stateMutability":"nonpayable","type":"constructor"},{"inputs":[{"internalType":"uint256","name":"_amount","type":"uint256"}],"name":"deposit","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"payable","type":"function"},{"inputs":[],"name":"getBalance","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"_address","type":"address"}],"name":"getUserRecord","outputs":[{"internalType":"uint32[]","name":"","type":"uint32[]"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"investmentId","outputs":[{"internalType":"uint32","name":"","type":"uint32"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint32","name":"","type":"uint32"}],"name":"investments","outputs":[{"internalType":"address","name":"owner","type":"address"},{"internalType":"uint256","name":"amount","type":"uint256"},{"internalType":"uint256","name":"created_at","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"_address","type":"address"},{"internalType":"uint256","name":"_amount","type":"uint256"}],"name":"technology","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"payable","type":"function"},{"inputs":[{"internalType":"address","name":"","type":"address"},{"internalType":"uint256","name":"","type":"uint256"}],"name":"userInvestments","outputs":[{"internalType":"uint32","name":"","type":"uint32"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"_address","type":"address"},{"internalType":"uint256","name":"_amount","type":"uint256"}],"name":"withdraw","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"payable","type":"function"}]';
        $data2 = null;
        $data = $service->callContract(
            $abi,
            '0xef3DCCd7c6261bB7865FbA1c532a372bd669e3DF',
            'investments',
            array('1'),
            [
                'exception' => function ($err) {
                    return new \Exception('出错了！');
                }
            ]
        );
        if (isset($data[0]) && is_array($data[0])) {
            print_r($data[0][0]->value);
        } else {
            print_r($data[0]->value);
        }
    }

    public function tron()
    {
        $service = new TronService();
        $abi = '[{"inputs":[{"name":"_golden","type":"address"},{"name":"_password","type":"uint256"}],"stateMutability":"Nonpayable","type":"Constructor"},{"outputs":[{"type":"address"}],"constant":true,"inputs":[{"type":"uint256"}],"name":"appUser","stateMutability":"View","type":"Function"},{"outputs":[{"type":"address[]"}],"constant":true,"name":"getAppUser","stateMutability":"View","type":"Function"},{"outputs":[{"type":"address[]"}],"constant":true,"inputs":[{"name":"_owner","type":"address"}],"name":"getAppUserStraight","stateMutability":"View","type":"Function"},{"outputs":[{"type":"uint256"}],"constant":true,"name":"getPassword","stateMutability":"View","type":"Function"},{"outputs":[{"type":"address"}],"constant":true,"inputs":[{"name":"_owner","type":"address"}],"name":"getRecommend","stateMutability":"View","type":"Function"},{"outputs":[{"type":"uint256"},{"type":"uint256"},{"type":"uint256"},{"type":"uint256"}],"constant":true,"inputs":[{"name":"_owner","type":"address"}],"name":"getStraightTeam","stateMutability":"View","type":"Function"},{"outputs":[{"type":"uint256"},{"type":"uint256"}],"constant":true,"inputs":[{"name":"_owner","type":"address"}],"name":"getSum","stateMutability":"View","type":"Function"},{"outputs":[{"type":"uint256"},{"type":"uint256"}],"constant":true,"inputs":[{"name":"_owner","type":"address"}],"name":"getTeamDetai","stateMutability":"View","type":"Function"},{"outputs":[{"type":"uint256"}],"constant":true,"inputs":[{"name":"_owner","type":"address"}],"name":"getTeamEarnings","stateMutability":"View","type":"Function"},{"outputs":[{"type":"uint256"},{"type":"uint256"},{"type":"uint256"}],"constant":true,"name":"getTronDetail","stateMutability":"View","type":"Function"},{"outputs":[{"type":"address[]"},{"type":"address[]"}],"constant":true,"name":"getUserList","stateMutability":"View","type":"Function"},{"outputs":[{"type":"address"}],"constant":true,"name":"golden","stateMutability":"View","type":"Function"},{"outputs":[{"type":"address"}],"constant":true,"name":"my","stateMutability":"View","type":"Function"},{"outputs":[{"type":"address"}],"constant":true,"inputs":[{"type":"address"},{"type":"uint256"}],"name":"myNoStraight","stateMutability":"View","type":"Function"},{"outputs":[{"type":"uint256"}],"constant":true,"name":"releaseTime","stateMutability":"View","type":"Function"},{"inputs":[{"name":"_value","type":"uint256"},{"name":"_password","type":"uint256"}],"name":"run","stateMutability":"Nonpayable","type":"Function"},{"inputs":[{"name":"_owner","type":"address"}],"name":"setAddress","stateMutability":"Nonpayable","type":"Function"},{"outputs":[{"type":"bool"}],"payable":true,"inputs":[{"name":"_superior","type":"address"}],"name":"setTRX","stateMutability":"Payable","type":"Function"},{"outputs":[{"type":"bool"}],"payable":true,"inputs":[{"name":"_value","type":"uint256"}],"name":"setWithdraw","stateMutability":"Payable","type":"Function"},{"outputs":[{"type":"address"}],"constant":true,"inputs":[{"type":"address"}],"name":"superior","stateMutability":"View","type":"Function"},{"outputs":[{"type":"uint256"}],"constant":true,"inputs":[{"type":"address"}],"name":"userEarnings","stateMutability":"View","type":"Function"},{"outputs":[{"type":"uint256"}],"constant":true,"inputs":[{"type":"address"}],"name":"userFirst","stateMutability":"View","type":"Function"},{"outputs":[{"type":"uint256"}],"constant":true,"inputs":[{"type":"address"}],"name":"userJoinState","stateMutability":"View","type":"Function"},{"outputs":[{"type":"uint256"}],"constant":true,"inputs":[{"type":"address"}],"name":"userMoney","stateMutability":"View","type":"Function"},{"outputs":[{"type":"uint256"}],"constant":true,"inputs":[{"type":"address"}],"name":"userOneWithdraw","stateMutability":"View","type":"Function"},{"outputs":[{"type":"uint256"}],"constant":true,"inputs":[{"type":"address"}],"name":"userOutTime","stateMutability":"View","type":"Function"},{"outputs":[{"type":"uint256"}],"constant":true,"inputs":[{"type":"address"}],"name":"userPool","stateMutability":"View","type":"Function"},{"outputs":[{"type":"uint256"}],"constant":true,"inputs":[{"type":"address"}],"name":"userStraight","stateMutability":"View","type":"Function"},{"outputs":[{"type":"uint256"}],"constant":true,"inputs":[{"type":"address"}],"name":"userTeam","stateMutability":"View","type":"Function"},{"outputs":[{"type":"uint256"}],"constant":true,"inputs":[{"type":"address"}],"name":"userTeamSize","stateMutability":"View","type":"Function"},{"outputs":[{"type":"uint256"}],"constant":true,"inputs":[{"type":"address"}],"name":"userTime","stateMutability":"View","type":"Function"},{"outputs":[{"type":"uint256"}],"constant":true,"inputs":[{"type":"address"}],"name":"userTotal","stateMutability":"View","type":"Function"},{"outputs":[{"type":"uint256"}],"constant":true,"inputs":[{"type":"address"}],"name":"userTotalWithdraw","stateMutability":"View","type":"Function"},{"outputs":[{"type":"uint256"}],"constant":true,"name":"userWithdraw","stateMutability":"View","type":"Function"}]';
        $data = $service->callContract(
            $abi,
            'THc1n5Kom2PHG1pikMRjfQnDBXsY6M1TYD',
            'getAppUser',
            array(),
            [
                'exception' => function ($err) {
                    return new \Exception('出错了！');
                }
            ]
        );
        print_r($data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('web3::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('web3::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('web3::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
