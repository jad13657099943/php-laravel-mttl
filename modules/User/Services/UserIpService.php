<?php


namespace Modules\User\Services;


use App\Http\Controllers\Controller;
use Modules\User\Models\ProjectUser;
use Modules\User\Models\UserIp;

class UserIpService extends Controller
{

    /**
     * 获取用户ip
     * @param $uid
     * @return bool
     */
    public function userIp($uid)
    {
        $ip = $this->getIP();
        if (empty($ip)) return true;
        $show_userid = $this->getShowUserId($uid);
        $where[] = ['show_userid', '=', $show_userid];
        $where[] = ['user_id', '=', $uid];
        $where[] = ['ip', '=', $ip];
        $model = UserIp::query()->where($where)->first();
        $time = date('Y-m-d H:i:s');
        if (!empty($model)) {
            $model->updated_at = $time;
            $model->save();
        }
        if (empty($model)) {
            UserIp::query()->insert([
                'show_userid' => $show_userid,
                'user_id' => $uid,
                'ip' => $ip,
                'created_at' => $time
            ]);
        }
    }

    /**
     * 获取show_userid
     * @param $uid
     * @return mixed
     */
    public function getShowUserId($uid)
    {
        $where[] = ['user_id', '=', $uid];
        return ProjectUser::query()->where($where)->value('show_userid');
    }

    /**
     * 获取ip
     * @return array|false|string
     */
    public function getIP()
    {
        global $ip;
        if (getenv("HTTP_CLIENT_IP"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("REMOTE_ADDR"))
            $ip = getenv("REMOTE_ADDR");
        else
            $ip = '';
        return $ip;
    }
}
