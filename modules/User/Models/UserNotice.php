<?php


namespace Modules\User\Models;

use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class UserNotice extends Model
{
    use HasTranslations;

    protected $table = 'user_notice';

    protected $guarded = [];

    public $translatable = ['title'];

    public function toArray()
    {
        $attributes = parent::toArray();

        foreach ($this->getTranslatableAttributes() as $name) {
            $attributes[$name] = $this->$name;
        }

        return $attributes;
    }

    public static $stateMap = [
        0 => '未读',
        1 => '已读',
    ];

    public static $typeMap = [
        'user' => '会员消息',
    ];

    public function getStateTextAttribute($value)
    {
        return self::$stateMap[$value] ?? '未定义state';
    }

    public function getTypeTextAttribute($value)
    {
        return self::$typeMap[$value] ?? $value;
    }

}
