<?php

namespace Modules\Coin\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\Traits\DynamicRelationship;
use Modules\Core\Models\Traits\HasFail;
use Modules\Core\Models\Traits\HasTableName;
use Spatie\Translatable\HasTranslations;

class CoinLogModules extends Model
{
    use HasFail,
        HasTableName,
        DynamicRelationship,
        HasTranslations;

    //需要翻译处理的的字段
    public $translatable = ['title'];

    const MODULE_ENABLE = 1;
    const MODULE_DISABLE = 0;

    protected $fillable = [
        'module',
        'action',
        'title',
        'remark',
        'enable',
    ];


    /**
     * 应进行类型转换的属性
     * array转换成字段类型
     * @var array
     */
    protected $casts = [
        'title' => 'array',
    ];

    public function coinLog()
    {
        return $this->hasMany(CoinLog::class, 'module', 'module');
    }


    /*public function toArray()
    {

//        $lang = request()->header('Content-Language') ?? 'zh_CN';
//        app()->setLocale($lang);
        $attributes = parent::toArray();
        foreach ($this->getTranslatableAttributes() as $name) {
            $attributes[$name] = $this->$name;
            //$attributes[$name] = $this->getTranslation($name, $lang);
        }
        return $attributes;
    }*/
}
