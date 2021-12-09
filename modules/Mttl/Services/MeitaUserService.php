<?php


namespace Modules\Mttl\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Modules\Coin\Services\BalanceChangeService;
use Modules\Core\Services\Traits\HasQuery;
use Modules\Core\Translate\TranslateExpression;
use Modules\Mttl\Models\UserDemotion;
use Modules\Mttl\Models\UserGradeRecord;
use Modules\User\Models\ProjectUser;

class MeitaUserService
{
    use HasQuery;

    public function __construct(ProjectUser $model)
    {
        $this->model = $model;
    }

    /**
     * 质押升级
     * @param $user
     * @return mixed
     * @throws Exception
     */
    public function upgrade($user)
    {
        $user_id = with_user_id($user);
        $mtUser = $this->one(['user_id' => $user_id], ['exception' => function () {
            throw new Exception(trans('mttl::exception.会员数据未找到'));
        }]);
        if ($mtUser->type != 0) throw new Exception(trans('mttl::exception.操作失败'));
        return \DB::transaction(function () use ($mtUser, $user) {
            $grade = config('user::config.pledge_grade', 1);
            $amount = config('user::config.pledge_amount', 3000);

            // 变更余额
            $service = resolve(BalanceChangeService::class);
            $service->from($mtUser->user_id)
                ->withNum($amount)
                ->withSymbol('USDT')
                ->withNo(0)
                ->withInfo(new TranslateExpression('mttl::message.质押升级'))
                ->withModule('mttl.pledge_upgrade')
                ->change();

            $mtUser->type = 1;
            $mtUser->grade = $grade;
            $mtUser->save();

            // 发放推荐收益
            $rewardService = resolve(RewardLogService::class);
            $rewardService->referralReward($mtUser);

            return $mtUser;
        });
    }

    /**
     * 进化
     * @param ProjectUser|Model $user
     */
    public function evolution($user)
    {
        $evolutionArray = [
            ['level' => 5, 'grade' => 4, 'count' => 3],
            ['level' => 4, 'grade' => 3, 'count' => 3],
            ['level' => 3, 'grade' => 2, 'count' => 3],
            ['level' => 2, 'grade' => 1, 'count' => 3]
        ];
        $maxLevel = 0;
        $groupList = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        $user_id = $user->user_id;

        //直推列表
        $son_list = ProjectUser::query()->where('parent_id', $user_id)->pluck('user_id');

        // 降级记录
        $down_list = UserGradeRecord::query()->where('user_id', $user_id)
            ->where('operation', 2)
            ->where('new', $user->grade)
            ->orderByDesc('created_at')
            ->value('exclude_userid');

        $down = '0';
        if ($down_list) {
            $down = implode(',', $down_list);
        }

        foreach ($son_list as $son) {
            $childGradeGroup = \DB::select("
                SELECT
                    max(grade) as grade
                FROM
                    ti_project_user
                WHERE
                    (user_id IN (
                        SELECT
                            user_id
                        FROM
                            ti_user_invitation_tree
                        WHERE
                        JSON_CONTAINS( DATA, '{$son}' )
                    )
                    OR user_id = {$son})
                    AND grade > 0
                    AND user_id NOT IN ({$down})
            ");
            if (isset($childGradeGroup[0]) && $childGradeGroup[0]->grade)
                $groupList[$childGradeGroup[0]->grade] += 1;
        }

        foreach ($evolutionArray as $item) {
            if (!isset($groupList[$item['grade']])) continue;

            // 高等级也可以当低等级用，所以要循环判断累加
            $count = 0;
            foreach ($groupList as $key => $group) {
                if ($key >= $item['grade']) {
                    $count += $group;
                }
            }

            if ($item['grade'] && $count && $count >= $item['count']) {
                $maxLevel = $item['level'];
                break;
            }
        }
        if ($maxLevel > $user->grade) {

            // 增加记录
            $record = new UserGradeRecord();
            $record->user_id = $user->user_id;
            $record->old = $user->grade;
            $record->new = $maxLevel;
            $record->exclude_userid = array();
            $record->save();

            // 修改等级
            $user->grade = $maxLevel;
            $user->save();
        }
    }
}
