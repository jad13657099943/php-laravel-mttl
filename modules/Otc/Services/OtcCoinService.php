<?php


namespace Modules\Otc\Services;


use Modules\Core\Services\Traits\HasQuery;
use Modules\Otc\Models\OtcCoin;

class OtcCoinService
{
    use HasQuery;

    /**
     * @var OtcCoin
     */
    protected $model;

    public function __construct(OtcCoin $model)
    {
        $this->model = $model;
    }
}
