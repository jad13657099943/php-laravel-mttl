<?php


namespace Modules\Mttl\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Exceptions\ModelSaveException;
use Modules\Core\Services\Traits\HasQuery;
use Modules\Mttl\Models\AutoLog;
use Modules\Mttl\Models\EnergyCardAutomatic;

class EnergyCardAutomaticService
{
    use HasQuery;

    public function __construct(EnergyCardAutomatic $model)
    {
        $this->model = $model;
    }

    /**
     * 变更自动购买设置
     * @param Model $user
     * @param array $data
     * @param boolean $automatic
     * @return bool|Model|mixed
     * @throws ModelSaveException
     */
    public function change($user, $data, $automatic)
    {
        $model = $this->one(['user_id' => with_user_id($user)], ['exception' => false]);
        if ($model) {
            if ($data == null) $data = $model->toArray();
            $model->types = $data['types'];
            $model->principal = $data['principal'];
            $model->total_days = $data['total_days'];
            $model->daily_rate = $data['daily_rate'];
            $model->automatic = $automatic;
            $model->save();
        } else {
            if ($data == null) throw new \Exception(trans('mttl::exception.未知的数据'));
            $model = $this->create([
                'user_id' => with_user_id($user),
                'types' => $data['types'],
                'principal' => $data['principal'],
                'total_days' => $data['total_days'],
                'daily_rate' => $data['daily_rate'],
                'automatic' => $automatic
            ]);
        }

        AutoLog::addLog(with_user_id($user),$automatic);
        return $model;
    }
}
