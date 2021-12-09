<?php

namespace Modules\Coinv2\Services;

use Illuminate\Database\Eloquent\Builder;
use Modules\Coin\Models\CoinExchangeSettings;
use Modules\Core\Services\Traits\HasQuery;

class ExchangeSettingsService
{
    use HasQuery {
        withQueryOptions as QueryWithQueryOptions;
    }

    protected $model;

    public function __construct(CoinExchangeSettings $model)
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
    public function getAll(array $options = [])
    {
        $data = $this->all(null, $options);

        return $data;
    }
}
