<?php

namespace Modules\Mttl\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Models\ProjectUser;

class EnergyCardAutomatic extends Model
{
    protected $table = 'mttl_energy_card_automatic';

    /**
     * 追加到模型数组表单的访问器。
     *
     * @var array
     */
    protected $appends = ['type_text', 'rate_of_return'];

    protected $guarded = [];

    /**
     * 应进行类型转换的属性
     *
     * @var array
     */
    protected $casts = [
        'principal' => 'float'
    ];

    public function getTypeTextAttribute()
    {
        $key = EnergyCard::$typeMap[$this->types];
        if ($key)
            return trans($key);
        else
            return trans('mttl::message.未知');
    }

    public function getRateOfReturnAttribute()
    {
        return floatval(bcmul($this->daily_rate, 100, 4));
    }

    public function user()
    {
        return $this->hasOne(ProjectUser::class, 'user_id', 'user_id');
    }
}
