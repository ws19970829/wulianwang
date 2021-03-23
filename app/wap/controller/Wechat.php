<?php
/**
 * 首页控制类
 * @author 大梦
 * @DateTime 2019/07/06
 */

namespace app\wap\controller;

use think\Request;
use think\Validate;
use think\Controller;
use EasyWeChat\Factory;
use app\common\model\Member as MemberModel;
use app\common\model\Activity as ActivityModel;
use app\common\model\ActivitySignUp as ActivitySignUpModel;

class Wechat extends Controller
{
    public function wechat_login(Request $request)
    {

        $params = input();
        $uid = $params['uid'] ?? 0;
        $activity_id = $params['aid'] ?? 0;

        $config = [
            'app_id' => config('wechat.appid'),
            'secret' => config('wechat.secret'),
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',
            'oauth' => [
                'scopes' => ['snsapi_userinfo'],
                'callback' => 'index/Wechat/wechat_login/aid/'.$activity_id . '/uid/' . $uid,
            ],
        ];

        // 使用EasyWeChat
        $app = Factory::officialAccount($config);

        // 第一次没有code 跳转到微信服务器
        if (!isset($params['code'])) {
            // 跳转到网页授权地址
            $response = $app->oauth->scopes(['snsapi_userinfo'])->redirect();
            $response->send();
        } else {
            // 有code 获取微信用户信息

            // 获取 OAuth 授权结果用户信息

            $weixin_user_info = $app->oauth->user();

            $where_member[] = ['is_del', 'eq', 0];
            $where_member[] = ['open_id', 'eq', $weixin_user_info['id']];

            // 根据openid 查询会员
            $user_info = (new MemberModel())->where($where_member)->find();
            session('uid', $user_info['id']);
            session('user_info', $user_info);

            // 插入新会员
            if (empty($user_info)) {
                $data['open_id'] = $weixin_user_info['id'];
                $data['nickname'] = $weixin_user_info['nickname'];
                $data['username'] = $weixin_user_info['name'];
                $data['sex'] = $weixin_user_info['original']['sex'];
                $data['avatar'] = $weixin_user_info['avatar'];
                $data['token'] = hash_func($weixin_user_info['id']);
                $user_info = (new MemberModel())->create($data);
                session('uid', $user_info['id']);
                session('user_info', $user_info);

            }
            $this->redirect('index/index/detail',['aid'=>$activity_id, 'uid' => $user_info['id']]);
        }
    }

    public function wechat_login_back()
    {
        $params = input();
        return json(['code' => 1, 'msg' => '']);
    }
}
