<?php

namespace Modules\Coin\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Coin\Notifications\CheckCoinAssetBalanceTotal;
use Notification;
use Leonis\Notifications\EasySms\Channels\EasySmsChannel;
use Modules\Coin\Models\CoinAsset;
use Modules\Coin\Notifications\SystemWalletMaxBalance;
use Modules\Core\Services\Traits\HasQuery;

class AssetService
{
    use HasQuery {
        withQueryOptions as QueryWithQueryOptions;
    }

    /**
     * @var CoinAsset
     */
    protected $model;

    public function __construct(CoinAsset $model)
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
     * @param $userId
     * @param array $options
     *
     * @return mixed
     */
    public function getAllByUser($user, array $options = [])
    {
        $options['whereEnabled'] = false;
        $data = $this->all(['user_id' => with_user_id($user)], $options);
        if ($options['assetIsExist'] ?? false) {
            $data = $this->createWithUser($user, $data);
        }
        return $data->filter(function ($asset) {
            return $asset->isEnabled();
        })->values();
    }

    /**
     * 创建用户资产记录表
     */
    public function createWithUser($user, Collection $data = null, array $options = null)
    {
        $userId = with_user_id($user);
        $coins = resolve(CoinService::class)->all();
        if ($data === null) {
            $data = $this->getAllByUser($user, $options['allOptions'] ?? []);
        }

        $symbols = $data->pluck('symbol')->map(function ($symbol) {
            return strtoupper($symbol);
        })->toArray();

        foreach ($coins as $coin) {
            if (!in_array(strtoupper($coin->symbol), $symbols)) {
                $model = $this->create([
                    'user_id' => $userId,
                    'symbol' => $coin->symbol,
                    'balance' => 0,
                    'status' => $coin->status
                ]);
                $data->push($model);
            }
        }

        return $data;
    }


    /**
     * 定时检测币种总余额与上次检测余额的差
     * 超过设置的预警值则消息提示管理员
     * @throws \Exception
     */
    public function balanceTotalCheck()
    {

        //设置的管理员邮箱
        $notice = config('api::settings.admin_email');
        if (filter_var($notice, FILTER_VALIDATE_EMAIL)) { // 邮箱发送
            $route = 'mail';
        } else {
            return false;
        }

        $this->query([
            'with' => 'coin',
            'queryCallback' => function ($query) {
                $query->select('symbol', \DB::raw("SUM(balance) as balance"))
                    ->groupBy('symbol');
            },
        ])->chunk(50, function ($list) use ($route, $notice) {


            foreach ($list as $key => $item) {

                $cacheKey = $item->symbol . '_last_total_num';
                $coinCacheLastNum = cache($cacheKey) ?? 0;
                $diffNum = abs($item->balance - $coinCacheLastNum);
                $coinDiffNum = $item->coin->diff_num ?? 0;

                //本次查询的币种总余额与上次查询相差超过设置阈值，通知
                if ($coinDiffNum > 0 && $diffNum >= $coinDiffNum) {

                    $msg = $item->symbol . "余额总数变动超过阈值，上次余额" . $coinCacheLastNum . ",当前余额" . $item->balance . "，相差" . $diffNum;
                    Notification::route($route, $notice)
                        ->notify(new CheckCoinAssetBalanceTotal($msg));
                }
                //缓存本次查询的余额以供下次对比
                cache([$cacheKey => $item->balance]);
            }
        });
    }
}
