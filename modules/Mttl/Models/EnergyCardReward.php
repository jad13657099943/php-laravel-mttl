<?php

namespace Modules\Mttl\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Models\ProjectUser;

class EnergyCardReward extends Model
{
    protected $table = 'mttl_energy_card_reward';

    protected $guarded = [];

    public function user()
    {
        return $this->hasOne(ProjectUser::class, 'user_id', 'user_id');
    }

    public function buyRecord()
    {
        return $this->hasOne(EnergyCardBuy::class, 'id', 'buy_record_id');
    }
}
