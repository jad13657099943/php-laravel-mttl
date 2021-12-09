<?php


namespace Modules\Web3\Services;

use Modules\Web3\Components\TronAPI\Exception\TronException;
use Modules\Web3\Components\TronAPI\Provider\HttpProvider;
use Modules\Web3\Components\TronAPI\Tron;

class TronService
{
    protected $tron;

    public function __construct()
    {
        try {
            $fullNode = new HttpProvider(config('web3::tron.host'));
            $solidityNode = new HttpProvider(config('web3::tron.host'));
            $eventServer = new HttpProvider(config('web3::tron.host'));
            $this->tron = new Tron($fullNode, $solidityNode, $eventServer);
        } catch (TronException $e) {
            exit($e->getMessage());
        }
    }

    /**
     * 调用合约方法，获取数据
     * @param $abi
     * @param $address
     * @param $functionName
     * @param $params
     * @param $options
     * @return null
     * @throws
     */
    public function callContract($abi, $address, $functionName, $params, $options)
    {
        $abi = is_string($abi) ? \GuzzleHttp\json_decode($abi, true) : $abi;
        try {
            $result = $this->tron->getTransactionBuilder()
                ->triggerConstantContract(
                    $abi,
                    $address,
                    $functionName,
                    $params
                );
            if ($callback = $options['callBack'] ?? false) {
                $callback($result);
            } else {
                return $result;
            }
        } catch (TronException $e) {
            $exception = $options['exception'] ?? true;
            if ($exception) {
                throw is_callable($exception) ? $exception($e) : $e;
            }
        }
    }

    /**
     * 获取合约事件
     * @param $address
     * @param $event_name
     * @return array
     * @throws TronException
     */
    public function getContractEvents($address, $event_name)
    {
        $result = $this->tron->getEventResult($address, 0, $event_name);
        return $result;
    }
}
