<?php


namespace Modules\User\Http\Controllers\admin\api;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\User\Models\Key;
use Modules\User\Models\ProjectUser;

class KeyController extends Controller
{
    public function list(Request $request)
    {
        $show_userid = $request->user;
        $type = $request->type;
        $where = [];
        if ($show_userid) {
            $where[] = ['show_userid', '=', $show_userid];
        }

        $sql = ProjectUser::query()->where($where)->with('key')->orderBy('key', 'desc');
        if ($type) {
            $id = Key::query()->where('status', $type)->pluck('user_id');
            $sql->whereIn('user_id', $id);
        }
        $list = $sql->paginate($request->limit ?? 10);
        foreach ($list->items() as $item) {
            $item->api = $item->key['api'];
            $item->secret = $item->key['secret'];
            $item->status_text = Key::$type[$item->key['status']];
            $item->key_text = key::$status[$item->key];
            $item->created_at = $item->key['created_at'];
        }
        return $list;
    }

    public function key(Request $request)
    {
        $uid = $request->id;
        $status = $request->status;
        if ($uid && $status) {
            ProjectUser::query()->whereIn('user_id', $uid)->update(['key' => $status]);
        }
        return ['code' => 200];
    }

    public function status(Request $request)
    {
        $uid = $request->id;
        $status = $request->status;
        if ($uid && $status) {
            Key::query()->whereIn('user_id', $uid)->update(['status' => $status]);
        }
        return ['code' => 200];
    }


}
