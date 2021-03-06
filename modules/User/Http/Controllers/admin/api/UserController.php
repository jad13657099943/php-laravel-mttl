<?php


namespace Modules\User\Http\Controllers\admin\api;


use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Models\Frontend\UserInvitationTree;
use Modules\Core\Services\Frontend\UserInvitationService;
use Modules\Core\Services\Frontend\UserService;
use Modules\Mttl\Models\UserDemotion;
use Modules\Mttl\Services\UserDemotionService;
use Modules\Mttl\Services\UserGradeRecordService;
use Modules\User\Models\Log;
use Modules\User\Models\ProjectUser;
use Modules\User\Services\ProjectUserService;

class UserController extends Controller
{

    public function getWhereParam($request)
    {

        $where = [];
        if (!empty($request->id)) {
            $where[] = ['show_userid', $request->id];
        }
        if (!empty($request->parent_id)) {
            $where[] = ['parent_id', $request->parent_id];
        }
        if (is_numeric($request->farm_grade)) {
            $where[] = ['grade', $request->farm_grade];
        }
        if (!empty($request->created_at)) {
            $time = explode('||', $request->created_at);
            $time[0] = date('Y-m-d H:i:s', strtotime($time[0]));
            $time[1] = date('Y-m-d H:i:s', strtotime($time[1]) + 86400);
            $where[] = ['created_at', '>=', [$time[0]]];
            $where[] = ['created_at', '<=', [$time[1]]];
        }
        $keyword = $request->keyword;
        if ($keyword) {
            $coreUserService = resolve(UserService::class);
            $userId = $coreUserService->query()
                ->where('username', 'like', '%' . $keyword . '%')
//                ->orWhere('username', 'like', '%' . $keyword . '%')
                ->value('id');
            if ($userId) {
                $where[] = ['user_id', '=', $userId];
            } else {
                $where[] = ['user_id', '=', 0];
            }
        }

        return $where;
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function index(Request $request)
    {

        $where = $this->getWhereParam($request);

        $service = resolve(ProjectUserService::class);
        $result = $service->paginate($where, [
            'orderBy' => ['id', 'desc'],
            'with' => ['user'],
            'queryCallback' => function (Builder $query) use ($request) {
                if (!empty($request->team_mark))
                    return $query->where('show_userid', 'like', $request->team_mark . '%');
            }
        ]);

        foreach ($result as $item) {
            $item->username = $item->user->username;
            $item->mobile = $item->user->mobile;
            $item->grade_text = $item->GradeText;

            //?????????????????????
            if ($item->parent_id > 0) {
                $parent = ProjectUser::query()->where('user_id', $item->parent_id)->first();
                $parent->grade_text = $parent->GradeText;
            } else {
                $parent = '';
            }
            $item->parent = $parent;

            // ????????????
            $team_count = UserInvitationTree::query()
                ->whereJsonContains('data', $item->user_id)
                ->pluck('user_id')
                ->count();
            $item->team_count = $team_count;

            // ????????????
            $son_count = ProjectUser::query()
                ->where('parent_id', $item->user_id)
                ->count();
            $item->son_count = $son_count;

        }
        return $result;
    }




    public function userEdit(Request $request)
    {


        $userId = $request->input('user_id');
        $data['grade'] = $request->input('grade', 0);
        if ($data['grade'] == 0) $data['type'] = 0;
        else $data['type'] = 1;
        $team_mark = $request->input('team_mark', '');
        if (!empty($team_mark) &&
            !(ord($team_mark) >= ord('A') && ord($team_mark) <= ord('Z'))
        ) {
            throw new \Exception('???????????????????????????????????????');
        }
        $show_userid = $request->input('show_userid', '');

        \DB::beginTransaction();
        try {

            $user = ProjectUser::query()->where('user_id', $userId)->first();
            $user->type = $data['type'];


            // ???????????????
            if ($data['grade'] != $user->grade) {
                $gradeService = resolve(UserGradeRecordService::class);
                $gradeService->add($user->user_id, $user->grade, $data['grade']);
            }

            $user->grade = $data['grade'];
            $user->team_mark = $team_mark;
            if (empty($team_mark) && !empty($show_userid)) {
                $user->show_userid = $show_userid;
            }
            $user->save();



            if ($user->wasChanged('team_mark')) {
                $user->show_userid = $user->team_mark . '00000';
                $user->save();

                // ???????????????????????????????????????????????????id??????????????????
                $service = resolve(ProjectUserService::class);
                $childUserids = $service->getTeamUserIds($userId);
                foreach (ProjectUser::query()->whereIn('user_id', $childUserids)->cursor() as $child) {
                    $show_userid = $child->show_userid;
                    if (!is_numeric(substr($show_userid, 0, 1))) {
                        $show_userid = substr($show_userid, 1);
                    }
                    $child->show_userid = $user->team_mark . $show_userid;
                    $child->save();
                }
            }

            //??????
            $user=$request->user();
            $admin=$user['id'];
            $type=1;
            $uid=$userId;
            $log='????????????,??????ID:'.$show_userid.',????????????:'.ProjectUser::$gradeMap[$data['grade']].',????????????:'. $team_mark;
            Log::addLog($admin,$uid,$type,$log);

        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception('?????????????????????????????????????????????');
        }
        \DB::commit();

//        $password = $request->input('password');
//        $payPassword = $request->input('pay_password');
//        if ($password || $payPassword) {
//
//            $coreUserService = resolve(UserService::class);
//            $coreUser = $coreUserService->getById($userId);
//
//            if ($password) {
//                if (strlen($password) < 6) {
//                    throw new \Exception('??????????????????6???');
//                }
//                $coreUser->password = $password;
//            }
//
//            if ($payPassword) {
//                if (strlen($payPassword) != 6 || !is_numeric($payPassword)) {
//                    throw new \Exception('????????????6?????????');
//                }
//                $coreUser->pay_password = $payPassword;
//            }
//
//            $coreUser->save();
//        }

        //????????????
        $cacheKey = 'user:' . $userId;
        \Cache::tags($cacheKey)->flush();
        return ['msg' => '????????????'];
    }

    public function authority(Request $request)
    {
        $userId = $request->input('user_id');
        $auth = $request->input('authority', []);
        $authority = [];
        foreach (ProjectUser::$authorityMap as $k => $a) {
            if (isset($auth[$k])) $authority[$k] = 1;
            else $authority[$k] = 0;
        }

        ProjectUser::query()->where('user_id', $userId)->update(['authority' => $authority]);
        //??????
        $state=[
            ''=>'??????',
            'on'=>'??????'
        ];
        $user=$request->user();
        $admin=$user['id'];
        $uid=$userId;
        $type=2;
        $log='????????????,????????????:'.$state[$auth['login']??'']
            .',??????????????????:'.$state[$auth['static']??'']
            .',??????????????????:'.$state[$auth['dynamic']??'']
            .',??????????????????:'.$state[$auth['transfer']??'']
            .',??????????????????????????????'.$state[$auth['empty']??'']
        ;
        Log::addLog($admin,$uid,$type,$log);

        return ['msg' => '????????????'];
    }


    public function tree(Request $request)
    {
        $userId = $request->input('user_id', 0);
        $uid = $request->input('uid', 0); // show_useruid
        if ($userId == 0 && $uid != 0) {
            $userId = ProjectUser::query()->where('show_userid', $uid)->value('user_id');
        }
        if (empty($userId)) {
            $userId = $request->input('uid') ?? "0";
        } else {
            $user = ProjectUser::query()->where('user_id', $userId)->first();
            $userId = $user->show_userid;
        }

        $parentInfo = '????????????';
        $user = ProjectUser::query()->where('show_userid', $userId)->first();
            if ($user && $user->parent_id > 0) {
            $parent = User::query()->where('id', $user->parent_id)->first();

            if ($parent) {
                $record = ProjectUser::query()->where('user_id', $parent->id)->first();
                $parentInfo = 'UID???' . $record->show_userid . '??????????????????' . $record->address . '?????????' . $record->GradeText;
            }
        }

        $userList = ProjectUser::query()->where('parent_id', $user ? $user->user_id : 0)
            ->with('user')
            ->orderBy('user_id', 'asc')
            ->get();
        $data = [];
        foreach ($userList as $item) {
            $msg = 'UID???' . $item->show_userid . "    ???????????????" . $item->address;

            $grade = $item->grade_text;
            $msg .= '    ?????????' . $grade;

            $sonsNum = ProjectUser::query()->where('parent_id', $userId)->count();
            if ($sonsNum) {
                $isParent = true;
            } else {
                $isParent = false;
            }
            $data[] = [
                'user_id' => $item->user_id,
                'name' => $msg,
                'isParent' => $isParent
            ];
        }

        return ['code' => 200, 'data' => $data, 'user_id' => $userId, 'parent_info' => $parentInfo];
    }


}
