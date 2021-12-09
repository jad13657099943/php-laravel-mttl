<?php

namespace Modules\Coinv2\Services;

use Modules\Coin\Exceptions\CoinException;
use Modules\Coin\Models\CoinLog;
use Modules\Core\Exceptions\ModelSaveException;
use Modules\Core\Services\Traits\HasQuery;
use Modules\Core\Translate\TranslateExpression;

class CoinLogService
{
    use HasQuery {
     create as QueryCreate;
    }

    /**
     * @var CoinLog
     */
    protected $model;

    public function __construct(CoinLog $model)
    {
        $this->model = $model;
    }

    /**
     * @param $userId
     * @param array $options
     *
     * @return mixed
     */
    public function getAllBySymbol($user, $symbol, array $where = [], array $options = [])
    {
        return $this->all(array_merge([
            'user_id' => with_user_id($user),
            'symbol' => $symbol,
        ], $where), $options);
    }

    /**
     * @param $data
     * @param array $options
     *
     * @return bool|\Illuminate\Database\Eloquent\Model
     * @throws ModelSaveException
     */
    public function create($data, array $options = [])
    {
        $coinLogModuleService = resolve(CoinLogModuleService::class);
        if (!isset($data['module'])) {
            throw new ModelSaveException(trans('coin::exception.模块未定义'));
        }
        $coinLogModuleService->hasModule($data['module']);
        /** @var CoinLog $result */
        $result = $this->queryCreate($data, $options);



        return $result;
    }

    /**
     * 获取用户日志总额
     *
     * @param $user
     * @param array $options
     *
     * @return mixed
     */
    public function getSymbolSumByUser($user, $symbol, array $options = [])
    {
        return $this->sum('num', [
            'user_id' => with_user_id($user),
            'symbol' => $symbol
        ], $options);
    }
}
