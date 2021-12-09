<?php

namespace Modules\Coin\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Coin\Models\CoinLog;
use Modules\Coin\Services\BalanceChangeService;

class UserCoinBalanceChanged
{
    use SerializesModels;

    /**
     * @var BalanceChangeService
     */
    protected $balanceChanger;
    /**
     * @var CoinLog
     */
    protected $changedResult;

    public function __construct(BalanceChangeService $balanceChanger, array $changedResult)
    {
        $this->balanceChanger = $balanceChanger;
        $this->changedResult = $changedResult;
    }
}
