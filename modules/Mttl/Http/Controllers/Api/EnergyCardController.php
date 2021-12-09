<?php

namespace Modules\Mttl\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Mttl\Http\Requests\Api\EnergyCard\ListRequest;
use Modules\Mttl\Models\EnergyCard;
use Modules\Mttl\Services\EnergyCardService;
use Modules\User\Models\ProjectUser;

class EnergyCardController extends Controller
{

    /**
     * 返回能量卡类型
     * @param Request $request
     * @param EnergyCardService $service
     * @return string[]
     */
    public function types(Request $request, EnergyCardService $service)
    {
        $user = $request->user();
        return $service->getOptionalTypesArray($user);
    }

    /**
     * 返回可购买的能量卡列表
     * @param ListRequest $request
     * @param EnergyCardService $service
     * @return array
     */
    public function list(ListRequest $request, EnergyCardService $service)
    {
        $data = $request->validationData();
        $user_id = with_user_id($request->user());
        $projectUser = ProjectUser::query()->where('user_id', $user_id)->first();

        if (isset($data['type'])) {
            return $service->getActivatedList($data['type'], $projectUser, true);
        } else {
            $list = [];
            foreach (array_keys(EnergyCard::$typeMap) as $key) {
                $result = $service->getActivatedList($key, $projectUser, true);
                if (count($result))
                    $list[$key] = $result;
            }
            return $list;
        }
    }
}
