<?php

namespace Modules\Mttl\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnergyCard extends Model
{
    use SoftDeletes;

    /**
     * 追加到模型数组表单的访问器。
     *
     * @var array
     */
    protected $appends = ['type_text', 'rate_of_return', 'total_revenue'];

    protected $table = 'mttl_energy_card';

    protected $guarded = [];

    /**
     * 应进行类型转换的属性
     *
     * @var array
     */
    protected $casts = [
        'buyable_level' => 'array',
        'principal' => 'float'
    ];

    const TYPE_1 = "mttl::message.初级";
    const TYPE_2 = "mttl::message.基础";
    const TYPE_3 = "mttl::message.高级";
    const TYPE_4 = "mttl::message.终极";
    public static $typeMap = [
        1 => self::TYPE_1,
        2 => self::TYPE_2,
        3 => self::TYPE_3,
        4 => self::TYPE_4,
    ];

    public function getTypeTextAttribute()
    {
        $key = self::$typeMap[$this->types];
        if ($key)
            return trans($key);
        else
            return trans('mttl::message.未知');
    }

    public function getRateOfReturnAttribute()
    {
        return floatval(bcmul($this->daily_rate, 100, 4));
    }

    public function getTotalRevenueAttribute()
    {
        return floatval(
            bcsub(
                bcmul(
                    bcmul($this->principal, $this->daily_rate, 4),
                    $this->total_days,
                    4
                ),
                $this->principal,
                4
            )
        );
    }

    /**
     * 只查询已启用的能量卡的作用域
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActivated($query)
    {
        return $query->where('enable', '=', 1);
    }
}
