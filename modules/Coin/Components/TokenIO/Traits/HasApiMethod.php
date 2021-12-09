<?php

namespace Modules\Coin\Components\TokenIO\Traits;

trait HasApiMethod
{
    public function ping()
    {
        $urn = 'ping';

        return $this->send($urn);
    }

    //查询转账gas
    public function withdrawPre($operator, $to, $value, $trade_no, $symbol, $memo = '')
    {
        $urn = 'v1/withdraw/pre';

        return $this->send($urn, compact('operator', 'to', 'value', 'trade_no', 'symbol', 'memo'), 'post');
    }

    //请求转账二次发起（新增）
    public function recall($trade_no = null)
    {
        $urn = 'v1/withdraw/recall?trade_no=' . $trade_no;

        return $this->send($urn, [], 'post');
    }

    //查询链信息（新增）
    public function chainInfo($chain = 'ETH', $block_number = 1)
    {
        $urn = 'v1/chain';

        return $this->send($urn, compact('chain', 'block_number'), 'get');
    }


    public function chainSet($chain = 'all')
    {
        $urn = 'v1/system/chain';

        return $this->send($urn, compact('chain'), 'get');
    }

    public function symbolSet($symbol = 'all')
    {
        $urn = 'v1/system/symbol';

        return $this->send($urn, compact('symbol'), 'get');
    }

    public function systemWallet($chain = null, $level = null, $system_wallet_id = null)
    {
        $urn = 'v1/systemWallet/index';

        return $this->send($urn, compact('chain', 'level', 'system_wallet_id'), 'get');
    }

    public function newWallet($chain, $account)
    {
        $urn = 'v1/wallet/new';

        return $this->send($urn, compact('chain', 'account'), 'post');
    }

    public function chainBalance($symbol, $address)
    {
        $urn = 'v1/chain/balance';

        return $this->send($urn, compact('symbol', 'address'), 'get');
    }

    //验证钱包地址正确性
    public function chainCheck($symbol, $address)
    {
        $urn = 'v1/chain/check';

        return $this->send($urn, compact('symbol', 'address'), 'get');
    }

    /**
     * @param $symbol
     * @param $address
     *
     * @return mixed
     * @throws \Exception
     *
     *  获取区块交易记录
     */

    public function chainTrade($symbol, $block_start, $from = '', $to = '')
    {
        $urn = 'v1/chain/trade';

        return $this->send($urn, compact('symbol', 'block_start', 'from', 'to'), 'get');
    }

    //请求提现转账
    public function newWithdraw($to, $value, $symbol, $trade_no, $memo = null, $operator = null, $gas_price = null)
    {
        $urn = 'v1/withdraw/new';

        return $this->send($urn, compact('to', 'value', 'symbol', 'trade_no', 'memo', 'operator', 'gas_price'), 'post');
    }

    public function rechargeInfo($id = null, $txhash = null)
    {
        $urn = 'v1/recharge/info';

        return $this->send($urn, compact('id', 'txhash'), 'get');
    }

    public function withdrawInfo($trade_no)
    {
        $urn = 'v1/withdraw/info';

        return $this->send($urn, compact('trade_no'), 'get');
    }

    public function getLoginSign($params = [])
    {
        $urn = 'v1/sign/login';

        return $this->send($urn, $params);
    }

    public function postLoginSign(array $data)
    {
        $urn = 'v1/sign/login';

        return $this->send($urn, json_encode($data, JSON_UNESCAPED_UNICODE), 'raw');
    }

    public function askLoginSign($params = [])
    {
        $urn = 'v1/sign/login/ask';

        return $this->send($urn, $params);
    }

    public function addApp($name, $key = null, $ip = null, $notice_url = null, $is_enable = null)
    {
        $urn = 'v1/admin/app/new/' . $key;
        // if (!empty($key)) {
        //     $urn .= http_build_query(['key' => $key]);
        // }
        return $this->send($urn, compact('name', 'ip', 'notice_url', 'is_enable'), 'post');
    }

    public function initApp($key)
    {
        $urn = 'v1/admin/app/' . $key . '/init';

        // $urn .= http_build_query(['key' => $key]);
        return $this->send($urn, [], 'put');
    }

    public function deleteApp($key)
    {
        $urn = 'v1/admin/app/' . $key . '/delete';

        return $this->send($urn, [], 'delete');
    }

    public function recoverApp($key)
    {
        $urn = 'v1/admin/app/' . $key . '/recover';

        return $this->send($urn, [], 'put');
    }

    public function getSyncState()
    {
        $urn = 'v1/admin/queue/state';

        return $this->send($urn, [], 'get');
    }

    public function resetApp($key)
    {
        $urn = 'v1/manage/app/reset/' . $key;

        return $this->send($urn, [], 'put');
    }

    public function editApp($array, $key = null)
    {
        $urn = 'v1/manage/app/edit/' . $key;

        return $this->send($urn, $array, 'put');
    }

    public function withdrawList($where = [], $page = 1, $page_size = 10)
    {
        $urn = 'v1/manage/withdraw/list';

        return $this->send($urn, array_merge(compact('page', 'page_size'), (array)$where), 'get');
    }

    public function systemWalletDetail($id)
    {
        $urn = 'v1/manage/systemWallet/' . $id;

        return $this->send($urn);
    }

    public function newSystemWallet($params)
    {
        $urn = 'v1/manage/systemWallet/new';

        return $this->send($urn, $params, 'post');
    }

    public function editSystemWallet($systemWalletId, $params)
    {
        $urn = 'v1/manage/systemWallet/' . $systemWalletId . '/edit';

        return $this->send($urn, $params, 'put');
    }

    public function defaultSystemWallet($systemWalletId, $level)
    {
        $urn = 'v1/manage/systemWallet/' . $systemWalletId . '/default';

        return $this->send($urn, compact('level'), 'put');
    }

    public function delSystemWallet($systemWalletId)
    {
        $urn = 'v1/manage/systemWallet/' . $systemWalletId;

        return $this->send($urn, [], 'delete');
    }

    public function newHotSystemWallet($chain)
    {
        $urn = 'v1/manage/systemWallet/newHot';

        //return $this->send($urn, $params = [], 'post');
        return $this->send($urn, compact('chain'),'post');
    }

    public function fetchHotSystemWallet($chain)
    {
        $urn = 'v1/manage/systemWallet/fetchHot';
        return $this->send($urn, compact('chain'),'post');
    }

    public function symbolAppSet($symbol, $params)
    {
        $urn = 'v1/manage/symbolAppSet/' . $symbol;

        return $this->send($urn, $params, 'put');
    }

    public function getSymbolAppSet($symbol = null)
    {
        $urn = 'v1/manage/symbolAppSet/' . $symbol;

        return $this->send($urn, [], 'get');
    }

    public function cancelWithdraw($trade_no)
    {
        $urn = 'v1/withdraw/' . $trade_no;

        return $this->send($urn, ['type' => 0], 'put');
    }

    public function retryWithdraw($trade_no)
    {
        $urn = 'v1/withdraw/' . $trade_no;

        return $this->send($urn, ['type' => 1], 'put');
    }

    /**
     * 修改应用设置接口
     *
     * @param $key
     * @param $param
     *
     * @return mixed
     * @throws \Exception
     */
    public function editSetting($key, $param)
    {
        $urn = 'v1/manage/app/edit/' . $key;
        $notice_url = $param['notice_url'];

        return $this->send($urn, $notice_url, 'put');
    }
}
