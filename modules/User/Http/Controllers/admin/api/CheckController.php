<?php


namespace Modules\User\Http\Controllers\admin\api;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\User\Models\ProjectUser;

class CheckController extends Controller
{
    public function getWhere($param)
    {
        $where = [];
        if (!empty($param['show'])) {
            $where[] = ['show_userid', '=', $param['show']];
        }
        if (!empty($param['check'])) {
            $where[] = ['check', '=', $param['check']];
        }

        return $where;
    }

    public function list(Request $request)
    {
        $param=$request->input();
        $where = $this->getWhere($param);
        $sql = ProjectUser::query()
            ->where($where)
            ->select('show_userid', 'user_id', 'check')
            ->orderBy('id', 'desc');
        if (!empty($param['team_mark'])) {
            $uid = ProjectUser::query()->where('show_userid', 'like', $param['team_mark'] . '%')->distinct()->pluck('user_id');
            $sql->whereIn('user_id', $uid);
        }
        $list = $sql->paginate($request->limit ?? 10);
        foreach ($list as $item) {
            $item->check_text = ProjectUser::$type[$item->check];
        }
        return $list;
    }

    public function setCheck(Request $request)
    {
        $uid = $request->id;
        $check = $request->check;
        $data = ['check' => $check];
        if ($check == 2) {
            $data = ['check' => $check, 'check_num' => 1];
        }
        ProjectUser::query()->whereIn('user_id', $uid)->update($data);
        return ['msg' => '设置成功'];
    }
}
