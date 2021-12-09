<?php

namespace Modules\Coinv2\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Notification;
use Leonis\Notifications\EasySms\Channels\EasySmsChannel;
use Modules\Coin\Models\CoinAsset;
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



}
