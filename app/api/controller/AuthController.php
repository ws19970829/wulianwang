<?php


namespace app\api\controller;


use app\admin\model\sms\SmsRecord;
use app\admin\model\system\SystemConfig;
use app\http\validates\user\RegisterValidates;
use app\models\user\User;
use app\models\user\UserToken;
use app\models\user\WechatUser;
use app\Request;
use crmeb\jobs\TestJob;
use crmeb\repositories\ShortLetterRepositories;
use crmeb\services\CacheService;
use crmeb\services\UtilService;
use think\facade\Cache;
use think\exception\ValidateException;
use think\facade\Config;
use think\facade\Queue;
use think\facade\Session;

/**微信小程序授权类
 * Class AuthController
 * @package app\api\controller
 */
class AuthController
{
    /**
     * H5账号登陆
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login(Request $request)
    {
        $user = User::where('account', $request->param('account'))->find();
        if ($user) {
            if ($user->pwd !== md5($request->param('password')))
                return app('json')->fail('账号或密码错误');
            //            if ($user->pwd === md5(123456))
            //                return app('json')->fail('请修改您的初始密码，再尝试登陆！');
        } else {
            return app('json')->fail('账号或密码错误');
        }
        if (!$user['status']) {
            $phone = SystemConfig::getConfigValue('site_phone') ?? '';
            $data = [
                'tips' => '账号已被禁用，请联系平台客服',
                'customer_mobile' => $phone
            ];
            return json(['status' => 410000, 'msg' => '请登录', 'data' => $data]);
        }

        // 设置推广关系
        //User::setSpread(intval($request->param('spread')), $user->uid);

        $token = UserToken::createToken($user, 'user');
        $data =  ['token' => $token->token, 'expires_time' => $token->expires_time, 'admin_id' => $user['admin_account_id'], 'tenant_id' => $user['tenant_id']];
        $arr = array_merge($user->toArray(), $data);
        if ($token) {
            event('UserLogin', [$user, $token]);
            return app('json')->success('登录成功', $arr);
        } else
            return app('json')->fail('登录失败');
    }

    /**
     * 退出登录
     * @param Request $request
     */
    public function logout(Request $request)
    {
        $request->tokenData()->delete();
        return app('json')->success('成功');
    }

    public function verifyCode()
    {
        $unique = password_hash(uniqid(true), PASSWORD_BCRYPT);
        Cache::set('sms.key.' . $unique, 0, 300);

        return app('json')->success(['key' => $unique]);
    }

    public function captcha(Request $request)
    {
        ob_clean();
        $rep = captcha();
        $key = app('session')->get('captcha.key');
        $uni = $request->get('key');
        if ($uni)
            Cache::set('sms.key.cap.' . $uni, $key, 300);

        return $rep;
    }

    /**
     * 验证验证码是否正确
     *
     * @param $uni
     * @param string $code
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function checkCaptcha($uni, string $code): bool
    {
        $cacheName = 'sms.key.cap.' . $uni;
        if (!Cache::has($cacheName)) {
            return false;
        }

        $key = Cache::get($cacheName);

        $code = mb_strtolower($code, 'UTF-8');

        $res = password_verify($code, $key);

        if ($res) {
            Cache::delete($cacheName);
        }

        return $res;
    }

    // 发送短信

    public function verify(Request $request)
    {
        $param = $request->param();
        $reg = '/^1[3456789]\d{9}$/';
        if (!preg_match($reg, $param['mobile'])) {
            return app('json')->fail('手机号填写错误，请重新填写');
        }

        if (session('msg_code_send_time') + 60 > time()) {
            return app('json')->fail('短信请求过于频繁，请稍后再试');
        }


        $code = rand('100000', '999999');
        //$code = 100000;
        $tpl_data = json_encode(array('code' => $code));
        $result = (new User())->send($param['mobile'],  $tpl_data);

        if ($result['code'] == 1) {

            session('msg_code_send_time', time());
            $key = 'sms_verification_' . $param['mobile'];
            cache($key, $code, 300);

            return app('json')->success('发送成功');
        } else {
            return app('json')->fail('短信平台验证码发送失败');
        }
    }


    /**
     * H5注册新用户
     * @param Request $request
     * @return mixed
     */
    public function register(Request $request)
    {
        $param = $request->param();
        $validate = \think\facade\Validate::rule([
            'account|账号' => 'require|mobile|unique:user',
            'password|密码' => 'require|min:6',
            're_password|确认密码' => 'require',
            //'captcha|验证码' => '',
            'invite_code|邀请码' => 'require'
        ]);

        if (!$validate->check($param)) {
            return app('json')->fail($validate->getError(), []);
        }
        if ($param['password'] != $param['re_password']) {
            return app('json')->fail('两次密码输入不一致', []);
        }
        $invitation = db('invitation')
            ->where('phone', $param['account'])
            ->where('code', $param['invite_code'])
            ->find();
        if (empty($invitation))  return app('json')->fail('邀请码填写错误', []);
        if (!empty($param['captcha'])) {
            //验证验证码
            $res = (new User)->validate_sms($param['account'], $param['captcha']);
            if (!$res) return app('json')->fail('验证码错误');
        }

        if (strlen(trim($param['password'])) < 6 || strlen(trim($param['password'])) > 16) {
            return app('json')->fail('密码必须是在6到16位之间');
        }

        $registerStatus = User::register($param['account'], $param['password'], '');
        if ($registerStatus) {
            return app('json')->success('注册成功');
        }
        return app('json')->fail(User::getErrorInfo('注册失败'));
    }

    /**
     * 密码修改
     * @param Request $request
     * @return mixed
     */
    public function reset(Request $request)
    {
        list($account, $captcha, $password, $re_password) = UtilService::postMore([['account', ''], ['captcha', ''], ['password', ''], 're_password'], $request, true);
        if (!empty($captcha)) {
            //验证验证码
            $res = (new User)->validate_sms($account, $captcha);
            if (!$res) return app('json')->fail('验证码错误');
        }
        if ($password != $re_password) {
            return app('json')->fail('两次密码输入不一致', []);
        }
        if (strlen(trim($password)) < 6 || strlen(trim($password)) > 16)
            return app('json')->fail('密码必须是在6到16位之间');
        $resetStatus = User::reset($account, $password);
        if ($resetStatus) return app('json')->success('修改成功');
        return app('json')->fail(User::getErrorInfo('修改失败'));
    }

    /**
     * 手机号登录
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function mobile(Request $request)
    {
        list($phone, $captcha, $spread) = UtilService::postMore([['phone', ''], ['captcha', ''], ['spread', 0]], $request, true);

        //验证手机号
        try {
            validate(RegisterValidates::class)->scene('code')->check(['phone' => $phone]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }

        //验证验证码
        $verifyCode = CacheService::get('code_' . $phone);
        if (!$verifyCode)
            return app('json')->fail('请先获取验证码');
        $verifyCode = substr($verifyCode, 0, 6);
        if ($verifyCode != $captcha)
            return app('json')->fail('验证码错误');

        //数据库查询
        $user = User::where('account', $phone)->find();
        if (!$user)
            return app('json')->fail('用户不存在');
        if (!$user->status)
            return app('json')->fail('已被禁止，请联系管理员');

        // 设置推广关系
        User::setSpread($spread, $user->uid);

        $token = UserToken::createToken($user, 'user');

        if ($token) {
            event('UserLogin', [$user, $token]);
            return app('json')->success('登录成功', ['token' => $token->token, 'expires_time' => $token->expires_time]);
        } else
            return app('json')->fail('登录失败');
    }

    /**
     * H5切换登陆
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function switch_h5(Request $request)
    {
        $from = $request->post('from', 'wechat');
        $user = $request->user();
        if ($from === 'h5') {
            $user = User::where('phone', $user['phone'])->where('user_type', '<>', 'h5')->find();
            $user->login_type = 'wechat';
            $user->save();
        } else {
            //数据库查询
            $user = User::where('account|phone', $user['phone'])->where('user_type', 'h5')->find();
            if (!$user)
                return app('json')->fail('H5用户不存在,无法切换');

            if (!$user->status) return app('json')->fail('已被禁止，请联系管理员');

            $wechatUserInfo = WechatUser::where('uid', $request->uid())->find(); //当前登陆用户信息
            $wechatH5UserInfo = WechatUser::where('uid', $user->uid)->find(); //H5登陆切换用户信息

            if ($wechatH5UserInfo->unionid && $wechatUserInfo->unionid != $wechatH5UserInfo->unionid)
                return app('json')->fail('您的账号已绑定特定用户无法切换到此用户上');
            if ($wechatH5UserInfo->openid && $wechatUserInfo->openid != $wechatH5UserInfo->openid)
                return app('json')->fail('您的账号已绑定特定用户无法切换到此用户上');
            if ($wechatH5UserInfo->routine_openid && $wechatUserInfo->routine_openid != $wechatH5UserInfo->routine_openid)
                return app('json')->fail('您的账号已绑定特定用户无法切换到此用户上');

            switch ($from) {
                case 'wechat':
                    if (!$wechatH5UserInfo->openid)
                        $wechatH5UserInfo->openid = $wechatUserInfo->openid;
                    if (!$wechatH5UserInfo->unionid && $wechatUserInfo->unionid)
                        $wechatH5UserInfo->unionid = $wechatUserInfo->unionid;
                    break;
                case 'routine':
                    if (!$wechatH5UserInfo->routine_openid)
                        $wechatH5UserInfo->routine_openid = $wechatUserInfo->routine_openid;
                    if (!$wechatH5UserInfo->unionid && $wechatUserInfo->unionid)
                        $wechatH5UserInfo->unionid = $wechatUserInfo->unionid;
                    break;
            }
            $wechatH5UserInfo->save();
            User::where('uid', $request->uid())->update(['login_type' => 'h5']);
        }
        $token = UserToken::createToken($user, 'user');
        if ($token) {
            event('UserLogin', [$user, $token]);
            //退出上一个账号
            $request->tokenData()->delete();
            return app('json')->success('登录成功', ['userInfo' => $user, 'token' => $token->token, 'expires_time' => $token->expires_time, 'time' => strtotime($token->expires_time)]);
        } else
            return app('json')->fail('登录失败');
    }

    /**
     * 绑定手机号
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function binding_phone(Request $request)
    {
        list($phone, $captcha, $step) = UtilService::postMore([
            ['phone', ''],
            ['captcha', ''],
            ['step', 0]
        ], $request, true);

        //验证手机号
        try {
            validate(RegisterValidates::class)->scene('code')->check(['phone' => $phone]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }

        //验证验证码
        $verifyCode = CacheService::get('code_' . $phone);
        if (!$verifyCode)
            return app('json')->fail('请先获取验证码');
        $verifyCode = substr($verifyCode, 0, 6);
        if ($verifyCode != $captcha)
            return app('json')->fail('验证码错误');

        $userInfo = User::where('uid', $request->uid())->find();
        $userPhone = $userInfo->phone;
        if (!$userInfo) return app('json')->fail('用户不存在');
        if ($userInfo->phone) return app('json')->fail('您的账号已经绑定过手机号码！');
        if (User::where('phone', $phone)->where('user_type', '<>', 'h5')->count())
            return app('json')->fail('此手机已经绑定，无法多次绑定！');
        if (User::where('account', $phone)->where('phone', $phone)->where('user_type', 'h5')->find()) {
            if (!$step) return app('json')->success('H5已有账号是否绑定此账号上', ['is_bind' => 1]);
            $userInfo->phone = $phone;
        } else {
            $userInfo->account = $phone;
            $userInfo->phone = $phone;
        }
        if ($userInfo->save() || $userPhone == $phone)
            return app('json')->success('绑定成功');
        else
            return app('json')->fail('绑定失败');
    }
}
