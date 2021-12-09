<?php

namespace Modules\Coinv2\Services;

use Modules\Coin\Models\CoinChainTrade;
use Modules\Core\Services\Traits\HasQuery;

class ChainTradeService
{
    use HasQuery {
        one as queryOne;
    }

    /**
     * @var CoinChainTrade
     */
    protected $model;

    public function __construct(CoinChainTrade $model)
    {
        $this->model = $model;
    }
}
