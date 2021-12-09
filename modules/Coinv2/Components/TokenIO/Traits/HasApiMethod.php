<?php

namespace Modules\Coinv2\Components\TokenIO\Traits;

trait HasApiMethod
{


    //验证钱包地址正确性
    public function chainCheck($chain, $address)
    {
        $urn = ':8086/api/v2/chain/check';

        return $this->send($urn, compact('chain', 'address'), 'get');
    }

    //查询币种链上实时余额
    public function chainBalance($symbol, $address)
    {
        $urn = ':8083/api/v2/chain/balance';

        return $this->send($urn, compact('symbol', 'address'), 'get');
    }

    //请求提现转账
    public function newWithdraw($to, $value, $symbol, $trade_no, $memo = null, $from = null, $gas_price = null, $chain = null)
    {
        $urn = ':8083/api/v2/withdraw/new';

        return $this->send($urn, compact('to', 'value', 'symbol', 'trade_no', 'memo', 'from', 'gas_price', 'chain'), 'post');
    }

    //拉取提现记录信息
    public function withdrawInfo($trade_no, $chain)
    {
        $urn = ':8083/api/v2/withdraw/info';

        return $this->send($urn, compact('trade_no', 'chain'), 'get');
    }

    //查询钱包充值信息详情
    public function rechargeInfo($id = null, $txhash = null, $chain=null)
    {
        $urn = ':8083/api/v2/recharge/info';

        return $this->send($urn, compact('id', 'txhash', 'chain'), 'get');
    }

    //查询用户钱包地址
    public function newWallet($chain, $account,$index=null)
    {
        $urn = ':8086/api/v2/wallet/new';

        return $this->send($urn, compact('chain', 'account','index'), 'post');
    }

    //拉取币种信息
    public function symbolSet($symbol = 'all')
    {
        $urn = ':8083/api/v2/system/symbol';

        return $this->send($urn, compact('symbol'), 'get');
    }

    //地址监听数据
    public function token($chain,$address)
    {
        $urn = ":8088/api/v2/token/{$chain}/{$address}";

        return $this->send($urn, [], 'get');
    }

    //添加新app应用接口
    public function createApp($name,$noticeUrl)
    {
        $urn = ":8088/api/v2/createApp";

        return $this->send($urn, compact('name','noticeUrl'), 'post');
    }

    //初始化助记词
    public function mnemonicWords()
    {
        $urn = ":8086/api/v2/appMnemonic/mnemonicWords";

        return $this->send($urn, [], 'post');
    }

}
