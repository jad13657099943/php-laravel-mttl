<?php


namespace Modules\User\Http\Controllers\api;


use App\Models\User;
use DB;

use Elliptic\EC;
use Ethereum\EcRecover;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Modules\Core\Events\Frontend\UserBeforeLogin;
use Modules\Core\Http\Requests\Frontend\Auth\LoginRequest;
use Modules\Core\Models\Frontend\UserVerify;
use Modules\Core\Services\Frontend\UserInvitationService;
use Modules\Core\Services\Frontend\UserRegisterService;
use Modules\Core\Services\Frontend\UserService;
use Modules\Core\Services\Frontend\UserVerifyService;
use Modules\Mttl\Services\NewService;
use Modules\User\Http\Requests\AccountRequest;
use Modules\User\Http\Requests\DappLoginRequest;
use Modules\User\Http\Requests\NoneRequest;
use Modules\User\Http\Requests\PasswordRequest;
use Modules\User\Http\Requests\RegisterRequest;
use Modules\User\Models\ProjectUser;
use Modules\User\Services\EcService;
use Modules\User\Services\ProjectUserService;

use Modules\Web3\Components\TronAPI\Support\Keccak;
use phpDocumentor\Reflection\Project;


/* 注册登录 */

class LoginController extends Controller
{

    use ThrottlesLogins;

    public function username()
    {
        return 'username';
    }

    /**
     * 校验是否注册
     * @param Request $request
     * @return bool[]
     * @throws Exception
     */
    public function checkRegistered(Request $request)
    {
        $address = $request->input("address", "");  // 用户钱包地址

        if (empty($address)) throw new Exception('缺少必要参数address!');

        $user = ProjectUser::query()->where('address', $address)->first();
        return ['regisged' => !!$user];
    }

    /**
     * dapp登录方法
     * 未注册用户自动注册，已注册用户则自动登录
     * @param DappLoginRequest $request
     * @return mixed
     * @throws ValidationException
     */
    public function dappLogin(DappLoginRequest $request)
    {
        $data = $request->validationData();
        $device = $request->input('device', '') ?: 'frontend';
        // 验证一下邀请码
        if (isset($data['invite_code'])) {
            $userInvitationService = resolve(UserInvitationService::class);
            $userInvitationService->getByToken($data['invite_code'], ['available' => true]);
        }
        try {
            return DB::transaction(function () use ($data, $device, $request) {
                $result = [];
                $projectUser = ProjectUser::query()->where("address", $data['address'])->first();
                if (empty($projectUser)) {
                    // 未找到匹配的合约地址用户，进行注册操作
                    $userRegiseterService = new UserRegisterService();

                    // 进行邀请码注册
                    $user = $userRegiseterService->register([
                        "username" => $data['address'],
                        "password" => $data['address'],
                        "invite_code" => $data['invite_code'] ?? null
                    ]);
                    $user = $user->refresh();

                    // 插入项目用户表
                    $show_userid=$this->generateUniqueId($user);
                    $projectUser = new ProjectUser();
                    $projectUser->show_userid = $show_userid;
                    $projectUser->user_id = $user->id;
                    $projectUser->parent_id = $user->inviter_id;
                    $projectUser->address = $data['address'];
                    $projectUser->save();
                    User::query()->where('username',$data['address'])->update(['password'=>Hash::make($show_userid)]);
                    // 生成一个邀请码，防止注册后直接邀请用户的情况
                    $this->invitation($request, $user);

                } else {
                    $service = resolve(ProjectUserService::class);
                    if (!$service->checkAuthority($projectUser, ProjectUser::AUTHORITY_LOGIN)) {
                        throw new Exception('此账号异常，请联系客服！');
                    }
                    // 已找到匹配的合约地址用户，进行登录操作
                    $user = User::query()->where("username", $data['address'])->first();
                }

                // 生成一个令牌，返回令牌的纯文本值
                $result['access_token'] = $user->createToken($device)->plainTextToken;
                $result['user'] = $user;
                $result['project_user'] = $projectUser;

                return $result;
            });
        } catch (\Throwable $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 地址注册
     * @param AccountRequest $request
     * @return mixed
     * @throws ValidationException
     */
    public function accountRegister(AccountRequest $request){
        $data = $request->validationData();
        // 验证一下邀请码
        if (isset($data['invite_code'])) {
            $userInvitationService = resolve(UserInvitationService::class);
            $userInvitationService->getByToken($data['invite_code'], ['available' => true]);
        }
        return DB::transaction(function () use ($data, $request) {

            $projectUser = ProjectUser::query()->where("address", $data['address'])->first();
            if (empty($projectUser)) {
                // 未找到匹配的合约地址用户，进行注册操作
                $userRegiseterService = new UserRegisterService();

                // 进行邀请码注册
                $user = $userRegiseterService->register([
                    "username" => $data['address'],
                    "password" => $data['password'],
                    "invite_code" => $data['invite_code'] ?? null
                ]);
                $user = $user->refresh();

                // 插入项目用户表
                $projectUser = new ProjectUser();
                $projectUser->show_userid = $this->generateUniqueId($user);
                $projectUser->user_id = $user->id;
                $projectUser->parent_id = $user->inviter_id;
                $projectUser->address = $data['address'];
                $projectUser->save();

                // 生成一个邀请码，防止注册后直接邀请用户的情况
                //   $this->invitation($request, $user);
                return ['msg'=>'注册成功'];

            }else{
                throw new Exception(trans('user::message.地址已注册'));
            }

        });
    }

    /**
     * 地址登录
     * @param AccountRequest $request
     * @param UserService $service
     * @return array
     * @throws ValidationException
     */
    public function accountLogin(AccountRequest $request,UserService $service){
        $data = $request->validationData();
        $user = $service->one(['username' => $data['address']], ['exception' => false]);
        if (empty($user))  throw new Exception(trans('user::message.地址不存在'));
        $service->checkPassword($user, $data['password']);
        $token = $user->createToken('frontend')->plainTextToken;
        return[
            'access_token' => $token
        ];
    }

    /**
     * 签名登录
     * @param NoneRequest $request
     * @param ProjectUserService $service
     * @return array
     * @throws Exception
     */
    public function noneLogin(Request $request,UserService $service){
        $data =$request->input(); /*$request->validationData()*/;
        $message = $data['signMessage']  ?? '';
        $signature = $data['sign'] ?? '';
        $address = $data['address'] ?? '';
        $result = Http::post('http://127.0.0.1:3000/' . 'tron', [
          'sign' => $signature,
           'signMessage' => $message,
          'address' => $address
        ]);
        $valid = $result->json()['verify'] ?? false;
        if (!$valid) throw new Exception('签名验证失败');

        // 验证一下邀请码
        if (isset($data['invite_code'])) {
            $userInvitationService = resolve(UserInvitationService::class);
            $userInvitationService->getByToken($data['invite_code'], ['available' => true]);
        }
        try {
            return DB::transaction(function () use ($data, $request) {
                $result = [];
                $projectUser = ProjectUser::query()->where("address", $data['address'])->first();
                if (empty($projectUser)) {
                    // 未找到匹配的合约地址用户，进行注册操作
                    $userRegiseterService = new UserRegisterService();

                    // 进行邀请码注册
                    $user = $userRegiseterService->register([
                        "username" => $data['address'],
                        "password" => $data['address'],
                        "invite_code" => $data['invite_code'] ?? null
                    ]);
                    $user = $user->refresh();

                    // 插入项目用户表
                    $show_userid=$this->generateUniqueId($user);
                    $projectUser = new ProjectUser();
                    $projectUser->show_userid = $show_userid;
                    $projectUser->user_id = $user->id;
                    $projectUser->parent_id = $user->inviter_id;
                    $projectUser->address = $data['address'];
                    $projectUser->save();
                    User::query()->where('username',$data['address'])->update(['password'=>Hash::make($show_userid)]);
                    // 生成一个邀请码，防止注册后直接邀请用户的情况
                    $this->invitation($request, $user);

                } else {
                    $service = resolve(ProjectUserService::class);
                    if (!$service->checkAuthority($projectUser, ProjectUser::AUTHORITY_LOGIN)) {
                        throw new Exception('此账号异常，请联系客服！');
                    }

                }
                // 已找到匹配的合约地址用户，进行登录操作
                $user = $service->one(['username' => $data['address']], ['exception' => false]);
                // 生成一个令牌，返回令牌的纯文本值
                $result['access_token'] = $user->createToken('frontend')->plainTextToken;
                $result['user'] = $user;
                $result['project_user'] = $projectUser;

                return $result;
            });
        } catch (\Throwable $e) {
            throw new Exception($e->getMessage());
        }

    }


    /**
     * 修改密码
     * @param PasswordRequest $request
     * @param NewService $service
     * @return string[]
     */
    public function setPassword(PasswordRequest $request,NewService $service){
        $uid=$request->user()['id'];
        $data=$request->validationData();
        return $service->setPassword($uid,$data['password']);
    }

    /**
     *获取签名
     */
    public function getNone(Request $request,NewService $service){
        $uid=$request->user()['id'];
        return $service->getNone($uid);
    }

    /**
     * 登录
     * @param LoginRequest $request
     * @param UserService $service
     * @return array|void
     * @throws ValidationException
     */
    public function login(LoginRequest $request, UserService $service)
    {
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            $this->sendLockoutResponse($request);
        }

        try {

            $username = $request->input('username');
            $password = $request->input('password');
            $device = $request->input('device');
            $where['username'] = $username;
            $user = $service->one($where, [
                'exception' => false
            ]);
            if (empty($user)) {
                throw new Exception('会员名不存在');
            }

            //验证码密码
            event(new UserBeforeLogin($user, User::LOGIN_TYPE_PASSWORD));
            $service->checkPassword($user, $password);

            $this->clearLoginAttempts($request);

            return [
                'access_token' => $user->createToken($device ?: 'frontend')->plainTextToken
            ];
        } catch (Exception $e) {
            $this->incrementLoginAttempts($request);

            throw $e;
        }
    }


    /**
     * 注册
     * @param RegisterRequest $request
     * @param UserRegisterService $userRegisterService
     * @return User
     */
    public function register(RegisterRequest $request, UserRegisterService $userRegisterService)
    {

        return \DB::transaction(function () use ($request, $userRegisterService) {

            $username = $request->input('username'); //mobile || email
            $password = $request->input('password');
            $mobilePre = $request->input('mobile_pre', 86); //手机号国际区号
            $inviteCode = $request->input('invite_code'); //推荐码
            $code = $request->input('code'); //验证码

            if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
                $userVerify = UserVerify::TYPE_EMAIL_REGISTER;
            } else {
                $userVerify = UserVerify::TYPE_MOBILE_REGISTER;
            }

            //验证短信验证码
            /** @var UserVerifyService $userService */
            $userService = resolve(UserVerifyService::class);
            $userService->getByKeyToken($username, $code, $userVerify, array_merge([
                'setExpired' => true, // 标记已使用
            ], $options['userVerifyOptions'] ?? []));


            $param = [
                'username' => $username,
                'password' => $password,
                'invite_code' => $inviteCode,
                'code' => $code
            ];


            //$user = $userRegisterService->register($request->validationData());
            //调用注册
            if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
                $param['email'] = $username;
                $user = $userRegisterService->registerByEmail($param);
            } else {
                //手机号
                $param['mobile'] = $username;
                $user = $userRegisterService->registerByMobile($param);
            }


            $userInfo = $user->refresh();
            $userInfo->save();

            //添加到扩展表
            $extendUser = [
                'user_id' => $userInfo->id,
                'parent_id' => $userInfo->inviter_id
            ];

            $projectUserModel = new ProjectUser($extendUser);
            $projectUserModel->save();
            return $userInfo;
        });
    }

    /**
     * 生成一个用户唯一id
     * @param Model $user
     * @param string|null $teamMark
     * @return int
     */
    private function generateUniqueId($user, $teamMark = null)
    {
        $service = resolve(ProjectUserService::class);

        if ($teamMark == null) {
            $pidAll = $service->getUserPidAll(with_user_id($user), false);
            if ($pidAll) {
                // 取离自己最近的用户的team_mark
                $teamMark = ProjectUser::query()
                    ->whereIn('user_id', $pidAll)
                    ->whereNotNull('team_mark')
                    ->orderByDesc('id')
                    ->value('team_mark');
            }
        }
        $teamMark = $teamMark ?? '';

        $random_id = $teamMark . mt_rand(10000, 99999);
        $record = ProjectUser::query()->where('show_userid', $random_id)->first();
        if (!$record) {
            return $random_id;
        } else {
            return $this->generateUniqueId($user, $teamMark);
        }
    }

    public function invitation(Request $request, $user = null)
    {
        if (!$user) $user = $request->user();
        $userInvitationService = resolve(UserInvitationService::class);
        $invitation = $userInvitationService->getUnusedToken($user, [
            'exception' => false
        ]);
        if (!$invitation) {
            $user_id = with_user_id($user);
            $data = ['user_id' => $user_id];
            $projectUser = ProjectUser::query()->where('user_id', $user_id)->first();
            // 一码多人默认99年有效期
            $expiredAt = config('core::user.invitation.any_expires', 86400 * 365 * 99);
            $expiredAt = date('Y-m-d H:i:s', (time() + $expiredAt));

            $invitation = $userInvitationService->queryCreate(array_merge($data, [
                'token' => $projectUser->show_userid,
                'expired_at' => $expiredAt,
            ]), []);
        }
        return $invitation;
    }
}
