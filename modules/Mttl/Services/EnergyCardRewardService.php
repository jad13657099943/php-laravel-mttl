<?php


namespace Modules\Mttl\Services;

use Modules\Core\Services\Traits\HasQuery;
use Modules\Mttl\Models\EnergyCardReward;

class EnergyCardRewardService
{
    use HasQuery;

    public function __construct(EnergyCardReward $model)
    {
        $this->model = $model;
    }
}
