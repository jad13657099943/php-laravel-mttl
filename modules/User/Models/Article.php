<?php


namespace Modules\User\Models;


use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Article extends Model
{
    use HasTranslations;

    public $table = 'article';

    public $guarded = [];

    public $translatable = ['title', 'content', 'image', 'desc'];
    protected $casts = [
        'label' => 'array',
    ];

    public function toArray()
    {
        $attributes = parent::toArray();

        foreach ($this->getTranslatableAttributes() as $name) {
            $attributes[$name] = $this->$name;
        }

        return $attributes;
    }


    public static $stateMap = [
        1 => '上架',
        0 => '下架',
    ];

    public function getStateTextAttribute($value)
    {
        return self::$stateMap[$value] ?? '未定义state';
    }

    public function getCateTextAttribute($value)
    {
        $cateName = ArticleCate::query()->where('id', $value)->value('name');
        return $cateName ?? '未找到';
    }

    public function getLabelListAttribute($value)
    {

        if ($value) {
            $list = ArticleLabel::query()->whereIn('id', $value)->pluck('name');
        }

        return $list ?? [];
    }

}
