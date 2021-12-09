<?php


namespace Modules\User\Services;


use Modules\User\Models\UserNotice;

class UserNoticeService
{

    /**
     * 创建通知消息
     * @param $userId
     * @param array $title
     * @param string $type
     * @param int $state
     * @return UserNotice
     */
    public function createNotice($userId, $title = [], $type = 'user', $state = 0)
    {

        $data = [
            'user_id' => $userId,
            'state' => $state,
            'type' => $type,
            'title' => $title, //注意这里是多语言内容
        ];
        $model = new UserNotice($data);
        $model->save();
        return $model;
    }

}
