<?php


namespace Modules\Web3\Services;

use Modules\Web3\Components\Web3\Contract;

class Web3Service
{
    protected $host;

    public function __construct()
    {
        $this->host = config('web3::eth.host');
    }

    /**
     * 调用合约方法，获取数据
     * @param $abi
     * @param $address
     * @param $functionName
     * @param $params
     * @param $options
     * @return null
     */
    public function callContract($abi, $address, $functionName, $params, $options)
    {
        $contract = new Contract($this->host, $abi);
        $returnData = null;
        $contract->at($address)->call($functionName, $params, function ($err, $data) use ($options, &$returnData) {
            if ($err) {
                $exception = $options['exception'] ?? true;
                if ($exception) {
                    throw is_callable($exception) ? $exception($err) : $err;
                }
            }
            if ($callback = $options['callBack'] ?? false) {
                $callback($data);
            } else {
                $returnData = $data;
            }
        });
        return $returnData;
    }
}
