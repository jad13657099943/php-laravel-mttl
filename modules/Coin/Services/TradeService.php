<?php

namespace Modules\Coin\Services;

use Log;
use Exception;
use Modules\Coin\Models\Coin;
use Modules\Coin\Models\CoinConfig;
use Modules\Coin\Models\CoinTrade;
use Modules\Coin\Models\CoinRecharge;
use Modules\Coin\Models\CoinSystemWallet;
use Modules\Coin\Models\CoinWithdraw;
use Modules\Core\Services\Traits\HasQuery;
use Modules\Coin\Components\TokenIO\TokenIO;
use Modules\Coin\Exceptions\TradeNotFoundException;

class TradeService
{
    use HasQuery {
        one as queryOne;
    }

    /**
     * @var CoinTrade
     */
    protected $model;

    public function __construct(CoinTrade $model)
    {
        $this->model = $model;
    }

    /**
     * @param \Closure|array|null $where
     * @param array $options
     *
     * @return CoinTrade
     */
    public function one($where = null, array $options = [])
    {
        return $this->queryOne($where, array_merge([
            'exception' => function () {
                return new TradeNotFoundException(trans('coin::exception.交易数据未找到'));
            },
        ], $options));
    }

    /**
     * 充值 归集操作
     *
     * @param $address
     * @param $symbol
     * @param array $options
     *
     * @return array|bool
     * @throws \Modules\Coin\Exceptions\CoinNotFoundException
     */
    public function createWithRecharge(CoinRecharge $recharge, array $options = [])
    {
        $symbol = $recharge->symbol;
        $chain = $recharge->chain;
        //判断具体是否要归集，因为v2版本对应的充值记录不需要归集操作。
        //而监听是否要归集的操作又写在coin的providers里面。
        //所以只能在这判断是否需要归集
        $symbolVersion = CoinConfig::query()->where('symbol', $symbol)
            ->where('chain', $chain)
            ->value('tokenio_version');
        $symbolVersion = $symbolVersion ?? 2; //没设置默认为v2版本，兼容之前未设置的系统
        if ($symbolVersion == 2) {
            return false;
        }


        $address = $recharge->to;
        $no = $recharge->id;

        $module = $options['module'] ?? 'coin';
        $action = $options['action'] ?? CoinTrade::ACTION_POOLING_COLD;

        /** @var UserWalletService $userWalletService */
        $userWalletService = resolve(UserWalletService::class);
        $wallet = $userWalletService->getByAddress($address, [
            'exception' => false,
        ]);

        $userId = $wallet ? $wallet->user_id : 0;

        $trade = $this->one([
            'user_id' => $userId,
            'module' => $module,
            'action' => $action,
            'no' => $no
        ], array_merge([
            'exception' => false
        ], $options['oneOptions'] ?? []));

        if ($trade) { // 有记录
            $trade->saveIfFail(); // 状态不变(触发save事件)继续处理

            return $trade;
        }

        /** @var TokenIO $tokenIO */
        $tokenIO = resolve(TokenIO::class);
        /** @var CoinService $coinService */
        $coinService = resolve(CoinService::class);

        $coin = $coinService->getBySymbol($symbol);
        $balance = $tokenIO->chainBalance($symbol, $address);
        if (!$coin->canColdBalanceWith($balance['balance'], false)) {
            return false;
        }

        /** @var SystemWalletService $systemWalletService */
        $systemWalletService = resolve(SystemWalletService::class);
        $systemWallet = $systemWalletService->getColdWalletByChain($coin->chain,[
            'tokenio_version' => 1
        ]);



        return $this->create([
            'user_id' => $userId,
            'from' => $address,
            'to' => $systemWallet->address,
            'symbol' => $coin->symbol,
            'module' => $module,
            'action' => $action,
            'weight' => 10,
            'no' => $no,
            'num' => $balance['balance'],
            'state' => CoinTrade::STATE_PROCESSING,
            'chain' => $chain,
            'tokenio_version'=>1,
        ]);
    }

    /**
     * @param CoinWithdraw $withdraw
     * @param array $options
     *
     * @return CoinTrade
     */
    public function createWithWithdraw(CoinWithdraw $withdraw, array $options = [])
    {
        /** @var TradeService $tradeService */
        $tradeService = resolve(TradeService::class);

        $trade = $tradeService->one([
            'module' => $options['module'] ?? 'coin',
            'action' => $options['action'] ?? CoinTrade::ACTION_WITHDRAW,
            'no' => $withdraw->id,
//            'to'    => $withdraw->to,
//            'state' => [CoinTrade::STATE_PROCESSING, CoinTrade::STATE_NOT_SUFFICIENT_FUNDS],
        ], [
            'exception' => false,
        ]);

        if (!$trade) {
            /** @var SystemWalletService $systemWalletService */
            $systemWalletService = resolve(SystemWalletService::class);
            $systemWallet = $systemWalletService->getWithdrawWalletByChain($withdraw->chain,1);

            $state = CoinTrade::STATE_PROCESSING;
            if ($withdraw->needManually()) {
                $state = CoinTrade::STATE_HUMAN_TRANSFER;
            }

            $chain = $withdraw->chain;
            $trade = $tradeService->create([
                'user_id' => $withdraw->user_id,
                'from' => $systemWallet->address,
                'to' => $withdraw->to,
                'symbol' => $withdraw->symbol,
                'module' => $options['module'] ?? 'coin',
                'action' => $options['action'] ?? CoinTrade::ACTION_WITHDRAW,
                'weight' => 100,
                'no' => $withdraw->id,
                'num' => $withdraw->num,
                'state' => $state,
                'chain' => $chain,
                'tokenio_version'=>1,
            ]);

            $withdraw->state = CoinWithdraw::STATE_LOGGED;
            $withdraw->save();
        }

        return $trade;
    }

    /**
     * 计算补gas交易
     *
     * @param CoinTrade $trade
     *
     * @return array
     */
    protected function calcGas(CoinTrade $trade)
    {
        $tokenIO = resolve(TokenIO::class);
        $result = $tokenIO->withdrawPre($trade->from, $trade->to, $trade->num, $trade->id, $trade->symbol);

        $gas = $result['tx']['recommend']['ether'];
        $gasPriceInGwei = $result['tx']['recommend']['gasPriceInGwei'];

        Log::info('Coin trade:' . $trade->id . ' gas:' . $gas . ' GWei:' . $gasPriceInGwei, $result);

        $coinGasPrice = $trade->coin->gas_price != 0 ? $trade->coin->gas_price : 20; // 默认加20个 保证快速转账

        $finalGas = $gas * (($gasPriceInGwei + $coinGasPrice) / $gasPriceInGwei);
        $finalGasPriceInGwei = $gasPriceInGwei + $coinGasPrice;

        return [$gas, $gasPriceInGwei, $finalGas, $finalGasPriceInGwei];
    }

    /**
     * tokenio机制 不能有state=0的from同时处理 需要等0过后处理
     *
     * @param CoinTrade $trade
     *
     * @return boolean
     */
    protected function hasProcessing(CoinTrade $trade)
    {
        return $this->has([
            'from' => $trade->from,
            'state' => CoinTrade::STATE_BROADCASTED
        ]);
    }

    /**
     * 处理数据
     */
    public function process(CoinTrade $trade, array $options = [])
    {

        if ($trade->tokenio_version != 1) {
            return false;
        }

        // 不需要处理
        if ($trade->needProcess() || ($options['force'] ?? false)) {
            try {
                if (!$this->hasProcessing($trade)) {
                    if ($trade->isGas()) {
                        $this->processingGas($trade);
                    } elseif ($trade->isPoolingCold()) {
                        $this->processingPoolingCold($trade);
                    } else {
                        $this->processing($trade);
                    }
                    if ($trade->isDirty()) {
                        $trade->saveIfFail();
                    }
                }
            } catch (Exception $e) {
                Log::alert(trans('Trade处理异常'), ['e' => $e, 'trade' => $trade]);

                throw  $e;
            }
        }
    }

    /**
     * @param CoinTrade $trade
     */
    protected function processingGas(CoinTrade $trade)
    {
        $tokenIO = resolve(TokenIO::class);
        $result = $tokenIO->newWithdraw($trade->to, $trade->num, $trade->symbol, $trade->id, $trade->memo, $trade->from, $trade->gas_price);
        $trade->state = CoinTrade::STATE_BROADCASTED;
    }

    protected function createGas(CoinTrade $trade)
    {
        if ($trade->coin->isChainETH()) { // ETH需要计算gas
            $tokenIO = resolve(TokenIO::class);
            [$gas, $gasPriceInGwei, $finalGas, $finalGasPriceInGwei] = $this->calcGas($trade);
            $balance = $tokenIO->chainBalance('ETH', $trade->from); // 获取链上gas余额

            $gasTrade = $this->one([
                'no' => $trade->id,
                'action' => CoinTrade::ACTION_GAS,
            ], ['exception' => false]);

            if ($gasTrade) { // 有补gas记录
                // 最终需要的ETH额度
                $finalETH = $trade->symbol == 'ETH' ? $gasTrade->gas + $trade['num'] : $finalGas;

                if ($finalETH > $balance['balance'] || !$gasTrade->hasFinished()) {
                    return $gasTrade;
                }

                $trade->gas = $gasTrade->gas;
                $trade->gas_price = $gasTrade->gas_price; // 直接按照补gas的记录发送
            } else { // 没有补gas记录, 创建补gas记录
                // 最终需要的ETH额度
                $finalETH = $trade->symbol == 'ETH' ? $finalGas + $trade['num'] : $finalGas;

                if ($finalETH > $balance['balance']) { // 余额不足够gas 才创建补gas操作
                    $supplementGas = $finalETH - $balance['balance']; // 剩下的gas补充

                    /** @var SystemWalletService $systemWalletService */
                    $systemWalletService = resolve(SystemWalletService::class);
                    $systemWallet = $systemWalletService->one([
                        'chain' => 'ETH',
                        'type' => CoinSystemWallet::TYPE_GAS
                    ]);

                    $info = '当前ETH余额: ' . $balance['balance'] . "\n";
                    $info .= '需要补充ETH: ' . $supplementGas . "\n";
                    $info .= '查询gas:' . $gas . ' gasPrice:' . $gasPriceInGwei . "\n";
                    $info .= '最终归集gas:' . $finalGas . ' gasPrice:' . $finalGasPriceInGwei . "\n";

                    $chain = $trade->chain;
                    return $this->create([
                        'user_id' => $trade->user_id,
                        'from' => $systemWallet->address,
                        'to' => $trade->from,
                        'symbol' => 'ETH',
                        'module' => $trade->module,
                        'action' => CoinTrade::ACTION_GAS,
                        'weight' => 15,
                        'no' => $trade->id,
                        'num' => $supplementGas,
                        'state' => CoinTrade::STATE_PROCESSING,
                        'gas' => $gas,
                        'gas_price' => $gasPriceInGwei,
                        'state_info' => $info,
                        'chain' => $chain,
                        'tokenio_version'=>1,
                    ]);
                }

                $trade->gas = $gas;
                $trade->gas_price = $gasPriceInGwei; // 直接按照补gas的记录发送
            }
        }

        return false;
    }

    /**
     * 处理归集操作
     *
     * @param CoinTrade $trade
     */
    public function processingPoolingCold(CoinTrade $trade)
    {
        $tokenIO = resolve(TokenIO::class);
        $symbolBalance = $tokenIO->chainBalance($trade->symbol, $trade->from);

        if ($trade->canPoolingColdWith($symbolBalance['balance'], false)) { // 是否需要归集操作
            if ($gasTrade = $this->createGas($trade)) {
                return;
            }

            if ($trade->symbol == 'FIL') {
                $filGasNum = CoinTrade::FIL_GAS_NUM;
                $symbolBalance['balance'] = bcsub($symbolBalance['balance'], $filGasNum, 8);
                if ($symbolBalance['balance'] < 0) {
                    //归集数量不够gas数量
                    $trade->state = CoinTrade::STATE_CANCELED;
                    return;
                }
                $trade->gas = $filGasNum;
            } elseif ($trade->symbol == 'ETH') {

                //eth本身的归集的数量，要减掉补gas这部分增加的这部分
                $gasNum = CoinTrade::query()->where('no', $trade->id)
                        ->where('action', 'gas')
                        ->where('state', 2)
                        ->orderBy('id', 'desc')
                        ->value('num') ?? 0;
                $symbolBalance['balance'] = bcsub($symbolBalance['balance'], $gasNum, 8);

            }

            // 提交到tokenio
            $result = $tokenIO->newWithdraw($trade->to, $symbolBalance['balance'], $trade->symbol, $trade->id, $trade->memo, $trade->from, $trade->gas_price);
            $trade->state = CoinTrade::STATE_BROADCASTED;
            $trade->num = $symbolBalance['balance']; //更新实际归集数量
        } else {
            $trade->state = CoinTrade::STATE_CANCELED;
        }
    }


    /**
     * 提现等其他类型处理
     *
     * @param CoinTrade $trade
     */
    protected function processing(CoinTrade $trade)
    {
        $tokenIO = resolve(TokenIO::class);
        $symbolBalance = $tokenIO->chainBalance($trade->symbol, $trade->from);
        if ($symbolBalance['balance'] >= $trade->num) { // 是否有余额转账
            // TODO 通知不足以转账

            if ($trade->coin->isChainETH()) { // ETH需要计算gas
                [$gas, $gasPriceInGwei, $finalGas, $finalGasPriceInGwei] = $this->calcGas($trade);
                $balance = $tokenIO->chainBalance('ETH', $trade->from); // 获取链上gas余额
                if ($finalGas > $balance['balance']) { // 余额不足够gas 不处理
                    // TODO 通知不足以转账
                    return;
                }
                $trade->gas_price = $finalGasPriceInGwei;
            }

            //判断fil币的操作
            if ($trade->symbol == 'FIL') {
                //fil币固定gas数量
                $gasNum = CoinTrade::FIL_GAS_NUM;
                //链上余额不足转账金额和预留手续费，则无法转账
                if ($symbolBalance['balance'] < ($trade->num + $gasNum)) {
                    $trade->state = CoinTrade::STATE_CANCELED;
                    return '';
                }
            }

            $tokenIO = resolve(TokenIO::class);
            $result = $tokenIO->newWithdraw($trade->to, $trade->num, $trade->symbol, $trade->id, $trade->memo,
                $trade->from, $trade->gas_price);
            $trade->state = CoinTrade::STATE_BROADCASTED;
        }
    }

    /**
     * 主动同步tokenio状态
     *
     * @param CoinTrade $trade
     */
    public function syncTradeState(CoinTrade $trade)
    {
        if($trade->tokenio_version!=1){
            return false;
        }

        if (!in_array($trade->state, [CoinTrade::STATE_NOT_SUFFICIENT_FUNDS, CoinTrade::STATE_BROADCASTED])) {
            return;
        }
        $tokenIO = resolve(TokenIO::class);
        $result = $tokenIO->withdrawInfo($trade->id);
        //未成功获取到
        if (!empty($result['success'])) {
            return;
        }

        //状态只能从低往高改变
        if ($result['state'] > $trade->state) {
            $trade->hash = $result['hash'];
            $trade->state = $result['state'];

            $trade->save();
        }

        //提现记录同时更新提现状态
        if ($trade->action == 'withdraw' && $trade->no > 0) {
            $withdrawInfo = CoinWithdraw::query()->where('id', $trade->no)->first();
            if ($withdrawInfo && $withdrawInfo->state < $result['state']) {
                $withdrawInfo->state = $result['state'];
                $withdrawInfo->save();
            }
        }
    }

    //向tokenio发起二次转账请求
    public function recallToTokenio($tradeId)
    {

        $info = $this->getById($tradeId);
        //再次发起请求转账的状态条件
        if (in_array($info->state, array(0, -1, -4))) {


            $tokenio = resolve(TokenIO::class);
            $res = $tokenio->recall($info->id);

            if ($res) {

                //2秒后(还要不要这个?)，再次请求接口获取最新的hash值并更新
                sleep(2);
                $newHash = $tokenio->withdrawInfo($info->id);

                if (!empty($newHash['success'])) { //未成功获取到
                    throw new Exception('已请求再次转账，但未成功：' . $newHash['message']);
                }


                $info->hash = $newHash['hash'];
                $info->state = $newHash['hash'];
                $info->withdraw_at = date('Y-m-d H:i:s');
                $info->save();

            } else {
                throw new Exception('请求二次转账失败');
            }
        }

        return $info;
    }
}
