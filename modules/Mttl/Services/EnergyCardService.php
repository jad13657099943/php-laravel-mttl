<?php


namespace Modules\Mttl\Services;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Services\Traits\HasQuery;
use Modules\Mttl\Models\EnergyCard;
use Modules\User\Models\ProjectUser;

class EnergyCardService
{
    use HasQuery {
        one as first;
    }

    public function __construct(EnergyCard $model)
    {
        $this->model = $model;
    }

    /**
     * 获取能量卡
     * @param $user
     * @param $card_id
     * @param array $options
     * @return Builder|Model|object|null
     * @throws Exception
     */
    public function one($user, $card_id, $options = [])
    {
        $projectUser = ProjectUser::query()->where('user_id', with_user_id($user))->first();

        $card = EnergyCard::query()
            ->whereJsonContains('buyable_level', $projectUser->type)
            ->where('id', $card_id)
            ->first();
        if (!$card) {
            $exception = $options['exception'] ?? true;

            if ($exception) {
                throw is_callable($exception) ? $exception() : new Exception(trans('mttl::exception.未知的能量卡'));
            }
        }
        return $card;
    }

    /**
     * 返回可选择的能量卡类型
     * @param ProjectUser $user 会员实体
     * @return array
     */
    public function getOptionalTypesArray($user)
    {
        $user_id = with_user_id($user);
        $projectUser = ProjectUser::query()->where('user_id', $user_id)->first();
        $textArray = [];
        foreach (EnergyCard::$typeMap as $key => $item) {
            $count = $this->getActivatedList($key, $projectUser);
            if ($count) $textArray[$key] = trans($item);
        }
        return $textArray;
    }

    /**
     * 获取已启用的能量卡列表
     * @param $type integer 类型
     * @param $user Model 会员实体
     * @param false $isGet 是否get方法
     * @return mixed
     */
    public function getActivatedList($type, $user, $isGet = false)
    {
        $query = EnergyCard::query()
            ->where('types', $type)
            ->whereJsonContains('buyable_level', $user->type)
            ->orderBy('principal', 'ASC')
            ->activated();

        if ($isGet) return $query->get();
        else return $query->count();
    }
}
