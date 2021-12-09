<?php


namespace Modules\User\Http\Controllers\admin;


use Illuminate\Http\Request;
use Modules\User\Models\Article;
use Modules\User\Models\ArticleCate;
use Modules\User\Models\ArticleLabel;

class ArticleController
{
    public function index()
    {

        return view('user::admin.article.index', [
            'state' => Article::$stateMap
        ]);
    }

    public function create()
    {

        $cate = ArticleCate::query()->where('state', 1)->get();
        $label = ArticleLabel::query()->where('state', 1)->get();
        foreach ($label as $item) {
            $item->is_check = 0;
        }
        return view('user::admin.article.create', [
            'info' => Article::query()->newModelInstance(),
            'cate' => $cate,
            'label' => $label,
        ]);

    }

    public function editInfo(Request $request, Article $model)
    {
        $cate = ArticleCate::query()->where('state', 1)->get();
        $id = $request->input('id');
        $info = $model::query()->where('id', $id)->first();
        $label = ArticleLabel::query()->where('state', 1)->get();
        $infoLabel = $info->label;
        foreach ($label as $item) {
            if (in_array($item->id, $infoLabel)) {
                $item->is_check = 1;
            } else {
                $item->is_check = 0;
            }
        }

        return view('user::admin.article.create', [
            'info' => $info,
            'cate' => $cate,
            'label' => $label,
        ]);
    }
}
