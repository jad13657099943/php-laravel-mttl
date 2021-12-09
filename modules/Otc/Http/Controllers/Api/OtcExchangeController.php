<?php

namespace Modules\Otc\Http\Controllers\Api;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Controller;
use Modules\Core\Models\Frontend\UserBank;
use Modules\Otc\Exceptions\OtcExchangeException;
use Modules\Otc\Http\Requests\Api\OtcExchangeRequest;
use Modules\Otc\Http\Requests\Api\OtcExchangeStoreRequest;
use Modules\Otc\Models\OtcExchange;
use Modules\Otc\Services\OtcExchangeService;

class OtcExchangeController extends Controller
{

    /**
     * 全部挂单列表
     *
     * @param  OtcExchangeRequest  $request
     * @param  OtcExchangeService  $otcExchangeService
     * @return LengthAwarePaginator|Collection
     */
    public function index(OtcExchangeRequest $request, OtcExchangeService $otcExchangeService)
    {
        list($where, $options) = $this->parseWhereAndOptions($request);
        $options['whereIn']       = [
            'status' =>
                [
                    OtcExchange::STATUS_NORMAL,
                    OtcExchange::STATUS_SELLING,
                ],
        ];
        $options['queryCallback'] = function ($query) {
            $query->where('surplus', '>', 0);
        };

        $result = $otcExchangeService->all($where, $options);
        $result->getCollection()->map(function ($item) {
            $item->load([
                'user.enableBanks' => function ($query) use ($item) {

                    if ($item->type === OtcExchange::TYPE_BUY) {
                        $query->whereIn('bank', $item->bank_list);
                    }
                },
            ]);
            //$item->load(['user.userInfo']);
            $item->user->userInfo->makeHidden(['real_name']);
            $item->makeHidden("bank_list");
            $item->user->makeHidden(['mobile', 'pay_password_updated_at', 'pay_password_set', 'inviter_id', 'email']);

            $item->user->enableBanks->map(function ($bank) use ($item) {
                $bank->makeHidden(['value']);

                return $bank;
            });

            return $item;
        });

        return $result;
    }

    /**
     * 用户发布的挂单
     *
     * @param  OtcExchangeRequest  $request
     * @param  OtcExchangeService  $otcExchangeService
     * @return LengthAwarePaginator|Collection
     */
    public function userExchange(OtcExchangeRequest $request, OtcExchangeService $otcExchangeService)
    {
        list($where, $options) = $this->parseWhereAndOptions($request);
        $where['user_id'] = $request->user()->id;

        $result = $otcExchangeService->all($where, $options);
//        //返回用户昵称
//        $result->getCollection()->map(function ($item) {
//            $item->load(['user.userInfo']);
//            $item->user->userInfo->makeHidden(['real_name']);
//            return $item;
//        });

        return $result;
    }

    /**
     * @param  OtcExchangeStoreRequest  $request
     * @param  OtcExchangeService  $otcExchangeService
     * @return bool|Model
     */
    public function store(OtcExchangeStoreRequest $request, OtcExchangeService $otcExchangeService)
    {
        $user   = $request->user();
        $result = $otcExchangeService->create($user, $request->validationData());
        $result->relationLoaded('otcUser');
        $result->relationLoaded('user.lastActiveAccessToken');
        $result->status_text = $result->statusText;
        $result->type_text   = $result->typeText;

        return $result;
        //return $this->transform($result);
    }

    /**
     * @param  OtcExchangeRequest  $request
     * @param  OtcExchangeService  $otcExchangeService
     * @return mixed
     * @throws OtcExchangeException
     */
    public function cancel(OtcExchangeRequest $request, OtcExchangeService $otcExchangeService)
    {
        return $otcExchangeService->cancel($request->user(), $request->id);
    }

    /**
     * @param  OtcExchangeRequest  $request
     * @return array[]
     */
    protected function parseWhereAndOptions(OtcExchangeRequest $request)
    {
        $where   = [];
        $options = [];
        //币种
        if ( !empty($request->coin)) {
            $where['coin'] = strtolower($request->coin);
        }

        if ( !empty($request->status)) {
            $where['status'] = $request->status;
        }

        if ( !empty($request->type)) {
            $where['type'] = $request->type;
        }

        //排序方式
        $sort = isset($params['sort']) ? $params['sort'] : 'asc';
        if ( !empty($params['orderBy'])) {
            $options['orderBy'] = [$params['orderBy'], $sort];
        }

        $options['paginate'] = true;
        $options['with']     = [
            'otcUser',
            'coin',
            'user.lastActiveAccessToken',
            'user.userInfo',
        ];

        return [$where, $options];
    }
}
