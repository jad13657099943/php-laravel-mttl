<?php


namespace Modules\User\Http\Controllers\api;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\User\Models\Article;
use Modules\User\Models\ArticleCate;

class ArticleController extends Controller
{

    public function cateList(Request $request)
    {

        $list = ArticleCate::query()->where('state', 1)
            ->orderBy('state', 'desc')
            ->get();

        return $list;
    }


    public function articleList(Request $request)
    {

        $cateId = $request->input('cate_id', 0);
        $where[] = ['cate_id', '=', $cateId];
        $where[] = ['state', '=', 1];
        $list = Article::query()->where($where)
            ->orderBy('sort', 'desc')
            ->select('id','cate_id','state','label','title','image','desc','created_at')
            ->paginate($request->limit);
        foreach ($list as $item) {
            $item->label_list = $item->label;
        }

        return $list;
    }

    public function articleInfo(Request $request)
    {

        $id = $request->input('id');
        $info = Article::query()->where('id', $id)
            ->where('state', 1)
            ->first();
        if($info){
            $info->label_list = $info->label;
        }
        return $info;
    }

}
