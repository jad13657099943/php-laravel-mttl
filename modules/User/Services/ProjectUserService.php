<?php


namespace Modules\User\Services;


use Modules\Core\Models\Frontend\UserInvitationTree;
use Modules\Core\Services\Frontend\UserInvitationService;
use Modules\User\Models\ProjectUser;
use Modules\Core\Services\Traits\HasQuery;

class ProjectUserService
{
    use HasQuery;

    public function __construct(ProjectUser $model)
    {
        $this->model = $model;
    }

    public function getByShowUserid($show_userid, $options)
    {
        return $this->one(['show_userid' => $show_userid], $options);
    }

    public function getByUserid($userid, $options)
    {
        return $this->one(['user_id' => $userid], $options);
    }

    /**
     * 检查权限
     *
     * @param $user
     * @param string $name
     *
     * @return boolean
     */
    public function checkAuthority($user, $name)
    {
        if (isset($user->authority[$name])) {
            return $user->authority[$name];
        } else {
            if ($name === ProjectUser::AUTHORITY_EMPTY) {
                return false;
            } else {
                return true;
            }
        }
    }

    /**
     * 获取所有的团队标识
     * @return array
     */
    public function getTeamMark()
    {
        return ProjectUser::query()
            ->whereNotNull('team_mark')
            ->pluck('team_mark')
            ->toArray();
    }


    /**
     * 会员团队列表.
     * @param $userId
     * @return mixed
     */
    public function userSons($userId)
    {


        $where[] = ['parent_id', '=', $userId];
        $list = ProjectUser::query()->where($where)
            ->with('user')
            ->orderBy('id', 'desc')
            ->paginate();
        foreach ($list as $item) {

            $username = $item->user->username;
            $item->user_yeji = 0;
            $item->username = $username;
            $item->total = 0;

        }
        return $list;
    }


    /**
     * 团队页面统计
     * @param $userId
     * @return array
     */
    public function userTeamTotal($userId)
    {


        $user = ProjectUser::query()->where('user_id', $userId)->first();
        $user->grade_text = $user->grade_text;

        //我的直推总人数
        $sonsNum = ProjectUser::query()->where('parent_id', $userId)->count();
        //团队总人数
        $teamUser = $this->getTeamUserIds($userId);
        //团队总业绩
        $teamYeji = 0;
        //新增人数
        $addTeamNum = UserInvitationTree::whereJsonContains('data', $userId)
            ->where('created_at', '>=', date('Y-m-d'))
            ->pluck('user_id')->toArray();
        //新增业绩
        $addYejiNum = 0;

        return [

            'sons_num' => $sonsNum,
            'team_num' => count($teamUser),
            'team_yeji' => floatval($teamYeji),
            'add_team_num' => count($addTeamNum),
            'add_team_yeji' => floatval($addYejiNum),
            'user' => $user,
        ];

    }


    /**
     * 获取会员下面所有团队id
     * @param $userId
     * @return array
     */
    public function getTeamUserIds($userId)
    {

        return UserInvitationTree::query()
            ->whereJsonContains('data', $userId)
            ->pluck('user_id')
            ->toArray();
    }

    /**
     * 获取所有直推会员id
     * @param $userId
     * @return array
     */
    public function getUserSonIds($userId)
    {

        return ProjectUser::query()
            ->where('parent_id', $userId)
            ->pluck('user_id')
            ->toArray();
    }

    /**
     * 获取会员所有上级ID
     * @param integer $userId
     * @param boolean $reverse
     * @return array
     */
    public function getUserPidAll($userId, $reverse = true)
    {

        $userInvitationService = resolve(UserInvitationService::class);
        $team = $userInvitationService->getInvitersByUser($userId);
        $userIds = [];
        if ($team) {
            foreach ($team as $item) {
                $userIds[] = $item->id;
            }
            //一定要倒序输出，因为数据表存的关系是相反的
            $userIds = $reverse ? array_reverse($userIds) : $userIds;
        }

        return $userIds;
    }

}
