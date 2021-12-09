<?php

namespace Modules\Coinv2\Events;

use App\Models\User;
use Illuminate\Queue\SerializesModels;

/**
 * 用户转账日志坏账事件
 *
 * @package Modules\Coinv2\Events
 */
class UserCoinLogBadDebt
{
    use SerializesModels;

    /**
     * @var User
     */
    public $user;
    /**
     * @var string
     */
    public $symbol;
    /**
     * @var string 当前币种日志总额
     */
    public $sum;
    /**
     * @var string 改变额度
     */
    public $value;

    public function __construct(User $user, $symbol, $sum, $value)
    {
        $this->user = $user;
        $this->symbol = $symbol;
        $this->sum = $sum;
        $this->value = $value;
    }
}
