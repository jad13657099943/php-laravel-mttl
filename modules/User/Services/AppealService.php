<?php


namespace Modules\User\Services;


use Modules\Core\Services\Traits\HasQuery;
use Modules\User\Models\UserAppeal;

class AppealService
{

    use HasQuery;

    protected $model;

    public function __construct(UserAppeal $model)
    {
        $this->model = $model;
    }

    public function addInfo($userId, $type, $message, $images = [])
    {

        $data = [
            'user_id' => $userId,
            'type' => $type,
            'message' => $message,
            'images' => $images
        ];

        return $this->create($data);
    }


    public function list($userId)
    {
        $list = $this->paginate(['user_id' => $userId], [
            'exception' => false,
            'orderBy' => ['id', 'desc']
        ]);

        foreach ($list as $item) {
            $item->type_text = $item->type_text;
            $item->state_text = $item->state_text;
        }
        return $list;
    }

}
