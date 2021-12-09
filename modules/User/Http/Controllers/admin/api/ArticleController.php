<?php


namespace Modules\User\Http\Controllers\admin\api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\User\Http\Requests\ArticleRequest;
use Modules\User\Models\Article;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param Request $request
     * @param GoodsService $goodsService
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request, Article $article)
    {

        $where = [];
        $param = $request->all();
        if (isset($param['state']) && is_numeric($param['state'])) {
            $where[] = ['state', '=', $param['state']];
        }
        $result = $article::query()->where($where)->orderBy('id', 'desc')->paginate();

        foreach ($result as $item) {
            $item->state_text = $item->state;
            $item->cate_text = $item->cate_id;
            $item->label_list = $item->label;
            $labelStr = '';
            foreach ($item->label_list as $vo) {
                $labelStr .= $vo . '、';
            }
            $item->label_str = $labelStr;
        }

        return $result;
    }


    /**
     * 添加产品
     * @param GoodsRequest $request
     * @param GoodsService $goodsService
     * @return string[]
     * @throws \Modules\Core\Exceptions\ModelSaveException
     */
    public function create(Request $request, Article $model)
    {

        $param = [
            'title' => $request->title,
            'content' => $request->input('content'),
            'desc' => $request->desc,
            'image' => $request->image,
            'state' => $request->state,
            'sort' => $request->sort,
            'label' => $request->label,
            'cate_id' => $request->cate_id,
        ];

        $model::query()->create($param);
        return response()->redirectTo(route('m.user.admin.article.index'));
    }

    public function editInfo(Request $request, Article $model)
    {

        $id = $request->input('id');
        $param = [
            'title' => $request->title,
            'content' => $request->input('content'),
            'desc' => $request->desc,
            'image' => $request->image,
            'state' => $request->state,
            'sort' => $request->sort,
            'label' => $request->label,
            'cate_id' => $request->cate_id,
        ];


        $model::query()->where('id', $id)->update($param);
        return response()->redirectTo(route('m.user.admin.article.index'));
    }


    public function del(Request $request, Article $model)
    {

        $id = $request->input('id');
        $model::query()->where('id', $id)->delete();
        return ['msg' => '删除成功'];
    }
}
