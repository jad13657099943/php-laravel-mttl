<?php

namespace Modules\Coin\Services;

use Cache;
use Carbon\Carbon;
use Http;
use Illuminate\Database\Eloquent\Builder;
use Modules\Coin\Components\Exchange\Manager as ExchangeManager;
use Modules\Coin\Components\TokenIO\TokenIO;
use Modules\Coin\Events\CoinPriceSynced;
use Modules\Coin\Exceptions\CoinNotFoundException;
use Modules\Coin\Exceptions\CoinPriceSyncException;
use Modules\Coin\Models\Coin;
use Modules\Core\Services\Frontend\LabelService;
use Modules\Core\Services\Traits\HasQuery;


class CoinService
{
    use HasQuery {
        one as queryOne;
        withQueryOptions as QueryWithQueryOptions;
    }

    /**
     * @var Coin
     */
    protected $model;

    public function __construct(Coin $model)
    {
        $this->model = $model;
    }

    /**
     * @param Builder $query
     * @param array $options
     *
     * @return Builder
     */
    public function withQueryOptions(Builder $query, array $options)
    {
        if ($options['whereEnabled'] ?? true) { // 默认查询启用状态的币种
            $query->whereEnabled();
        }

        return $this->queryWithQueryOptions($query, $options);
    }

    /**
     * @param \Closure|array|null $where
     * @param array $options
     *
     * @return mixed
     */
    public function one($where = null, array $options = [])
    {
        return $this->queryOne($where, array_merge([
            'exception' => function () {
                return new CoinNotFoundException(trans('coin::exception.代币数据未找到'));
            },
        ], $options));
    }

    /**
     * @param $symbol
     * @param array $options
     *
     * @return Coin
     */
    public function getBySymbol($symbol, array $options = [])
    {
        $key = 'coin:' . $symbol;

        return Cache::tags([$key])
            ->rememberForever($key, function () use ($symbol, $options) {
                return $this->queryOne(['symbol' => $symbol], array_merge([
                    'exception' => function () use ($symbol) {
                        return CoinNotFoundException::withSymbol($symbol);
                    },
                ], $options));
            });
    }

    /**
     * @param $symbol
     * @param array $options
     *
     * @return mixed
     * @throws CoinPriceSyncException
     */
    public function getPriceBySymbol($symbol, array $options = [])
    {
        $data = $this->getAllPrice($options['allPriceOptions'] ?? []);
        $symbolUpper = strtoupper($symbol);
        if (!$data->has($symbolUpper)) {
            if ($options['exception'] ?? false) {
                throw new \UnexpectedValueException(trans('coin::exception.获取价格失败', ['symbol' => $symbol]));
            }

            return $this->defaultPriceData();
        }

        return $data[$symbolUpper];
    }

    const COIN_PRICE_CACHE_KEY = 'coin_price';

    /**
     * 获取所有设定币种价格数据
     * @TODO 队列自动处理价格
     *
     * @param array $options
     *
     * @return array|\Illuminate\Support\Collection
     * @throws CoinPriceSyncException
     */
    public function getAllPrice(array $options = [])
    {
        return $this->cacheSyncPrice($options);
    }

    /**
     * @param array $options
     *
     * @return array|\Illuminate\Cache\CacheManager|mixed
     * @throws CoinPriceSyncException
     */
    public function cacheSyncPrice(array $options = [])
    {
        $cacheKey = 'coin_price';
        $data = cache($cacheKey);
        $emptyData = empty($data);
        if (
            $emptyData ||
            Carbon::createFromTimestamp($data['timestamp'])->addSeconds($options['ttl'] ?? 600)->isPast() || // 默认10分钟拉一次
            ($options['force'] ?? false) // 强制刷新
        ) {
            $data = array_merge([
                'price' => [],
                'errorTimes' => 0,
            ], $data ?: [], [
                'timestamp' => time(),
            ]);

            try {
                $data['price'] = $this->syncPrice();
                $data['errorTimes'] = 0;
                cache([$cacheKey => $data]);
            } catch (\Exception $e) {
                if ($emptyData) {
                    throw $e;
                } elseif (($data['errorTimes'] > ($options['maxErrorTimes'] ?? 99999))) { // 拉取新价格错误 则直接返回旧数据, 默认可以错误1天的数据
                    throw new CoinPriceSyncException(trans('coin::exception.价格数据获失败, 超出获取界限值'));
                } else {
                    $data['errorTimes']++;
                }
            }
        }

        return $data['price'];
    }

    /**
     * 同步交易所价格
     *
     * @return array|\Illuminate\Support\Collection
     * @throws CoinPriceSyncException
     */
    public function syncPrice()
    {
        /** @var ExchangeManager $manager */
        $manager = resolve(ExchangeManager::class);
        $exchange = $manager->priceSyncExchange();
        $tickers = $exchange->fetchTickers();

        $coins = $this->all(null, [
            'whereEnabled' => false,
        ]); // 所有币种都查询价格

        $usdtPrice = $this->getUsdtPrice();

        $data = $coins
            //->keyBy('symbol')
            ->keyBy('coin')
            ->map(function ($coin) use ($usdtPrice, $tickers) {
                /** @var Coin $coin */

                //$symbol = $coin->symbol;
                $symbol = $coin->coin;

                $priceData = $this->defaultPriceData();

                foreach ($priceData as $priceKey => $price) {
                    $tickerKey = $symbol . '/' . $priceKey;
                    if ($symbol == $priceKey) { // 同名币种 为1
                        $priceData[$priceKey] = '1';
                    } elseif (array_key_exists($tickerKey, $tickers)) { // 获取交易所币种对价格
                        $priceData[$priceKey] = number_format($tickers[$tickerKey]['last'], 6, '.', '');
                    } elseif ($priceKey == 'CNY') { // CNY 但对计算USDT价格
                        $_price = $priceData['USDT'] * $usdtPrice['CNY'];
                        $priceData[$priceKey] = $_price > 0 ? number_format($_price, 6, '.', '') : '0';
                    } else {
                        $priceData[$priceKey] = 0;
                    }
                }

                return $priceData;
            })->toArray();

        $data = collect($data)->map(function ($priceData, $symbol) use ($data) { // 交易对之间的价格换算, 比如 BTC <=> ETH 价格
            foreach ($priceData as $priceKey => $price) {
                if (
                    !($price > 0) &&
                    array_key_exists($priceKey, $data) &&
                    ($data[$priceKey][$symbol] ?? 0) > 0
                ) {
                    $priceData[$priceKey] = number_format(1 / $data[$priceKey][$symbol], 6, '.', '');
                }
            }

            return $priceData;
        });

        event(new CoinPriceSynced($data));

        return $data;
    }

    /**
     * USDT基本价格 用来换算其他价格
     *
     * @param array $options
     *
     * @return mixed
     * @throws CoinPriceSyncException
     */
    public function getUsdtPrice(array $options = [])
    {
        $cacheKey = 'usdt_price';
        $data = cache($cacheKey);

        $emptyData = empty($data);
        if (
            $emptyData ||
            Carbon::createFromTimestamp($data['timestamp'])->addSeconds($options['ttl'] ?? 3600)->isPast() || // 默认1个小时重新拉取
            ($options['force'] ?? false) // 强制刷新
        ) {
            $url = 'https://otc-api.eiijo.cn/v1/data/trade-market?coinId=2&currency=1&tradeType=sell&currPage=1&payMethod=0&country=37&blockType=general&online=1&range=0&amount=';
            $response = Http::timeout(10)->get($url);

            if ($response->successful()) {
                $data = array_merge([
                    'price' => [],
                    'errorTimes' => 0,
                ], $data ?: [], [
                    'timestamp' => time(),
                ]);

                foreach ($response['data'] as $_data) {
                    if ($_data['coinId'] == 2) {
                        if ($_data['price'] > 20 || $_data['price'] < 1) { // USDT 价格不会大于20吧?
                            throw new CoinPriceSyncException(trans('coin::exception.价格数据获取异常'));
                        }
                        $data['price']['CNY'] = $_data['price'];

                        break;
                    }
                }
            } elseif ($emptyData) {
                throw new CoinPriceSyncException(trans('coin.exception.价格数据获失败'));
            } elseif (($data['errorTimes'] > ($options['maxErrorTimes'] ?? 24))) { // 拉取新价格错误 则直接返回旧数据, 默认可以错误1天的数据
                throw new CoinPriceSyncException(trans('coin.exception.价格数据获失败, 超出获取界限值'));
            } else {
                $data['errorTimes']++;
            }

            cache([$cacheKey => $data]);
        }

        return $data['price'];
    }

    protected function defaultPriceData()
    {
        return [
            'USDT' => '0',
            'CNY' => '0',
            'ETH' => '0',
            'BTC' => '0',
        ];
    }


    /**
     * 返回已启用的币种币种列表
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection
     */
    public function enabledCoinList()
    {

        return $this->all([], [
            'queryCallback' => function ($query) {
                $query->select('id', 'chain', 'symbol', 'real_symbol');
            }
        ]);
    }

    public function gas($operator,
                        $to,
                        $value,
                        $trade_no,
                        $symbol,
                        $memo = '')
    {
        $tokenIO = resolve(TokenIO::class);
        $result = $tokenIO->withdrawPre();

        //调用tokenio查询该笔转账所需要的gas费用
        try {
            $tokenio = new Tokenio(env('tokenio.apiKey'), env('tokenio.apiSecret'));
            $result = $tokenio->withdrawPre($operator, $to, $value, $trade_no, $symbol, $memo);

            if (isset($result['tx'])) {
                //$gas = $result['tx']['ether'];
                $coinInfo = Coins::get(['symbol' => $symbol]);
                $coinGasPrice = empty($coinInfo->gas_price) ? 0 : $coinInfo->gas_price;
                $gasPriceInGwei = $result['tx']['recommend']['gasPriceInGwei'];

                if ($coinGasPrice == 0) {
                    $gas = $result['tx']['recommend']['ether'];
                } else {
                    $gas = $result['tx']['recommend']['ether'] * (($gasPriceInGwei + $coinGasPrice) / $gasPriceInGwei);
                    //重新计算gas_price价格
                    $gasPriceInGwei = intval($gasPriceInGwei) + $coinGasPrice;
                }

                $result['tx']['recommend']['gasPriceInGwei'] = $gasPriceInGwei;
                if (is_numeric($gas)) { //必须保证是一个数字
                    return ['status' => 1, 'gas' => $gas, 'tokenioRes' => $result];
                } else {
                    return ['status' => 0, 'msg' => '获取gas失败', 'error' => $gas];
                }

            } else {
                return ['status' => 0, 'msg' => '查询gas失败：' . $result['message']];
            }
        } catch (\Exception $e) {

            return ['status' => 0, 'msg' => '查询gas失败：' . $e->getMessage()];
        }

    }


    /**
     * 跳转到外网查询该链地址的详情
     * @param $chain
     * @param string $address
     * @param string $hash
     * @return bool|string[]
     */
    public function queryChainLink($chain, $address = '', $hash = '')
    {


        if (empty($address) && empty($hash)) {
            return false;
        }

        $url = '';
        $labelService = resolve(LabelService::class);
        //获取根据设置的不同链的查询地址
        if (!empty($address)) {
            switch ($chain) {
                case 'BTC':
                    $url = 'https://btc.com/' . $address;
                    break;
                case 'ETH':
                    $url = 'https://cn.etherscan.com/address/' . $address;
                    break; //可以尝试用eth.btc.com
                case 'FIL':
                    $url = 'https://filfox.info/zh/address/' . $address;
                    break;
                case 'TRX':
                    $url = 'https://tronscan.org/#/address/' . $address;
                    break;
                default:
                    $url = '';
                    break;
            }
        } else {
            switch ($chain) {
                case 'BTC':
                    $url = 'https://btc.com/' . $hash;
                    break;
                case 'ETH':
                    $url = 'https://cn.etherscan.com/tx/' . $hash;
                    break;
                case 'FIL':
                    $url = 'https://filfox.info/zh/message/' . $hash;
                    break;
                case 'TRX':
                    $url = 'https://tronscan.org/#/transaction/' . $hash;
                    break;
                default:
                    $url = '';
                    break;
            }
        }

        return $url;
    }
}
