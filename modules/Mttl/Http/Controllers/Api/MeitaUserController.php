<?php

namespace Modules\Mttl\Http\Controllers\Api;

use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Coin\Models\CoinLog;
use Modules\Coin\Services\BalanceChangeService;
use Modules\Core\Exceptions\ModelSaveException;
use Modules\Core\Models\Frontend\UserInvitationTree;
use Modules\Core\Services\Frontend\UserInvitationService;
use Modules\Core\Translate\TranslateExpression;
use Modules\Mttl\Models\EnergyCardBuy;
use Modules\Mttl\Models\RewardLog;
use Modules\Mttl\Services\MeitaUserService;
use Modules\User\Models\ProjectUser;
use Modules\User\Services\ProjectUserService;

class MeitaUserController extends Controller
{

    /**
     * 质押升级
     * @param Request $request
     * @param MeitaUserService $service
     * @return mixed
     * @throws Exception
     */
    public function upgrade(Request $request, MeitaUserService $service)
    {
        return $service->upgrade($request->user());
    }

    /**
     * 取走质押
     * @param Request $request
     * @return array|bool
     * @throws ModelSaveException
     * @throws \Throwable
     */
    public function takeAway(Request $request)
    {
        $user_id = with_user_id($request->user());
        $service = resolve(ProjectUserService::class);
        $user = $service->getByUserid($user_id, []);

        if ($user->type == 0) {
            throw new \Exception('您还没有成为精神领袖，无法取走质押！');
        }

        // 当前社群奖励
        $amount = RewardLog::query()
            ->where('user_id', $user_id)
            ->whereIn('type', [
                RewardLog::TYPE_RECOMMEND,
                RewardLog::TYPE_ASSEMBLY,
                RewardLog::TYPE_LEVEL,
                RewardLog::TYPE_PEER,
            ])->sum('amount');

        if ($request->isMethod('GET')) {

            return ['community_rewards' => $amount];

        } else if ($request->isMethod('POST')) {

            // 检查是否已经取走质押
            $record = CoinLog::query()
                ->where('user_id', $user_id)
                ->where('action', 'take_away')
                ->first();

            if (!empty($record)) {
                throw new \Exception('不能重复取走质押哟！');
            }

            if (config('user::config.take_away', 90000) > $amount) {
                throw new \Exception('当前社群奖励不足以取走质押！');
            }

            // 取得质押金额
            $pledge_amount = CoinLog::query()
                ->where('user_id', $user_id)
                ->where('action', 'pledge_upgrade')
                ->value('num');

            $balanceService = resolve(BalanceChangeService::class);
            $balanceService->to($user_id)
                ->withNum($pledge_amount)
                ->withSymbol('USDT')
                ->withNo(0)
                ->withInfo(new TranslateExpression('mttl::message.取走质押'))
                ->withModule('mttl.take_away')
                ->change();

            return true;
        }
    }

    /**
     * 我的社群
     * @param Request $request
     * @param MeitaUserService $service
     * @return array
     */
    public function myCommunity(Request $request, MeitaUserService $service)
    {
        // 社群人数
        $team_ids = UserInvitationTree::query()
            ->whereJsonContains('data', with_user_id($request->user()))
            ->pluck('user_id')
            ->toArray();
        $team_count = count($team_ids);

        // 直推人数
        $son_count = ProjectUser::query()
            ->where('parent_id', with_user_id($request->user()))
            ->count();

        // 社群能量卡数
        $card_count = EnergyCardBuy::query()
            ->where('surplus_days', '>', 0)
            ->whereIn('user_id', $team_ids)
            ->count();

        return [
            'team_count' => $team_count,
            'son_count' => $son_count,
            'card_count' => $card_count
        ];
    }

    /**
     * 直推列表
     * @param Request $request
     * @param UserInvitationService $invitationService
     * @return array
     */
    public function sonList(Request $request, UserInvitationService $invitationService)
    {
        $sonList = $invitationService->getInviteesByUser(
            $request->user(),
            ['level' => $request->input('algebra', 1)]
        );

        $service = resolve(ProjectUserService::class);
        $list = [];
        foreach ($sonList as $user) {
            $userNew = $service->getByUserid($user->id, []);

            $parentUser = $service->getByUserid($userNew->parent_id, []);

            // 社群人数
            $team_ids = UserInvitationTree::query()
                ->whereJsonContains('data', $user->id)
                ->pluck('user_id')
                ->toArray();
            $team_count = count($team_ids);

            // 社群能量卡数
           $card_count = EnergyCardBuy::query()
               ->where('surplus_days', '>', 0)
              ->whereIn('user_id', $team_ids)
               ->count();

            $userNew['team_count'] = $team_count;
            $userNew['card_count'] = $card_count;
            $userNew['parent_show_userid'] = $parentUser->show_userid;
            $list[] = $userNew;
        }

        return $list;
    }

    /**
     * 社群明细（树结构）
     * @param Request $request
     * @param MeitaUserService $meitaUserService
     * @return array
     */
    public function communityDetail(Request $request, MeitaUserService $meitaUserService)
    {
        return $this->tree(with_user_id($request->user()), 1, ProjectUser::query()
            ->select(['grade', 'show_userid', 'user_id', 'type'])
            ->where('user_id', with_user_id($request->user()))
            ->first());
    }

    private function tree($pid = 0, $level = 1, $user = [])
    {
        $tree = array();
        $arr = ProjectUser::query()
            ->select(['grade', 'show_userid', 'user_id', 'type'])
            ->where('type', 1)
            ->where('parent_id', $pid)
            ->get()
            ->toArray();
        foreach ($arr as $v) {
            if ($level <= 19)
                $tree[] = $this->tree($v['user_id'], $level + 1, $v);
        }
        return [
            'name' => $user['show_userid'],
            'grade' => $user['grade'],
            'children' => $tree
        ];
    }
}
