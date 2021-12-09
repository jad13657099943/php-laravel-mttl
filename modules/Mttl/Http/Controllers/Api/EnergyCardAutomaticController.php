<?php

namespace Modules\Mttl\Http\Controllers\Api;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Exceptions\ModelSaveException;
use Modules\Mttl\Http\Requests\Api\EnergyCardAutomatic\SetupRequest;
use Modules\Mttl\Services\EnergyCardAutomaticService;
use Modules\Mttl\Services\EnergyCardService;

class EnergyCardAutomaticController extends Controller
{

    /**
     * 我的自动购买设置
     * @param Request $request
     * @param EnergyCardAutomaticService $service
     * @return Model|mixed
     */
    public function index(Request $request, EnergyCardAutomaticService $service)
    {
        return $service->one(
            ['user_id' => with_user_id($request->user())],
            ['exception' => false]
        );
    }

    /**
     * 设置自动购买
     * @param SetupRequest $request
     * @param EnergyCardAutomaticService $service
     * @return bool|Model|mixed
     * @throws ModelSaveException
     */
    public function setup(SetupRequest $request, EnergyCardAutomaticService $service)
    {
        $data = $request->validationData();

        if (isset($data['card_id'])) {
            $cardService = resolve(EnergyCardService::class);
            $card = $cardService->one($request->user(), $data['card_id']);

            $result = $service->change($request->user(), $card->toArray(), $data['automatic']);
        } else {
            $result = $service->change($request->user(), null, $data['automatic']);
        }

        return $result;
    }
}
