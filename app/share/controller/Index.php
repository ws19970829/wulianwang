<?php
/**
 * Index.php
 * desc:
 * created on  2020/8/20 9:27 AM
 * Created by caogu
 */
namespace app\share\controller;

use app\admin\model\collection\Collection;
use app\admin\model\collection\CollectionOrder;
use app\admin\model\fans\FansNote;
use app\admin\model\fans\FansNoteReadLog;
use app\admin\model\fans\FansPlanUser;
use app\admin\model\system\SystemConfig;
use app\api\controller\PublicController;
use app\models\store\StoreOrder;
use app\models\system\Cache;
use app\models\user\WechatUser;
use crmeb\basic\BaseController;
use app\models\user\User;
use crmeb\services\WechatService;


class Index extends BaseController
{

    public function index(){
        dump(123123);
    }


    /**
     * 分享笔记页面
     * @return string
     */
    public function note(){
        $id=input('param.id');
        $is_admin_view=input('param.is_admin_view',0);
        if(!$id){
            echo '参数不正确';exit;
        }

        $info=(new FansNote())->getOne($id,$is_admin_view);
        if(!$info){
            echo '内容不存在';exit;
        }
        $this->assign('info',$info);


        //网站地址
        $site_url=config('site.default_site_url');
        $site_url.='?tenant_id='.$info['tenant_id'].'&note_id='.$id;
        $this->assign('site_url',$site_url);
        $this->assign('is_admin_view',$is_admin_view);

        //存入访问记录
        if(input('param.u')){
            //判断该文章，该用户是否已经访问过，如果访问过，则不再记录访问日志
            $uid=input('param.u');
            $fans_plan_id=FansPlanUser::where('uid','=',$uid)
                ->where('fans_note_id','=',$id)
                ->value('fans_plan_id');
            $where=[
                'uid'=>$uid,
                'fans_note_id'=>$id,
                'fans_plan_id'=>$fans_plan_id
            ];
            $res=(new FansNoteReadLog())->where($where)->find();
            if(!$res){
                $data=$where;
                $data['add_time']=time();
                $data['tenant_id']=User::getTenantIDbyUID($uid);
                (new FansNoteReadLog())->insert($data);
            }
        }

        return $this->fetch();
    }


    /**
     * 扫码付款页面
     * @return bool|string
     */
    public function collection(){

//        dump(session('user_uid'));
//        dump(session('user_openid'));
//        exit;

        $id=input('param.id');
        $collection_info=(new Collection())->where('id','=',$id)->find();
        $tenant_id=$collection_info['tenant_id'];
        $this->assign('collection_id',$id);
        //需要做微信授权登录
        if(!session('user_uid') || !session('user_openid')){
            $appid=(new SystemConfig())
                ->where('tenant_id','=',$tenant_id)
                ->where('menu_name','=','wechat_appid')
                ->value('value');
            $appid=trim($appid,'"');

            if(!input('param.code')){
                $notice_url=config('site.default_site_url').'/share/index/collection/id/'.$id;
                $url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$notice_url."&response_type=code&scope=snsapi_base&state=#wechat_redirect";
                $this->redirect($url);
            }else{
                $res=$this->auth_login($tenant_id);
                if($res!==true){
                    return $res;
                }
            }
        }

        //平台前台logo
        $logo=(new PublicController())->getSysConfigValue('routine_index_logo',$tenant_id);
        $logo = str_replace('\\/', '/', $logo);
        $this->assign('logo',$logo);
        $this->assign('info',$collection_info);
        return $this->fetch();
    }

    public function create_order(){

        if(!request()->isAjax()){
            exit;
        }

        $id=input('param.collection_id');
        if(!$id){
            return app('json')->fail('参数有误');
        }

        $info=(new Collection())->find($id);
        if(!$info){
            return app('json')->fail('该二维码已失效');
        }

        $type=$info['type'];
        if($type==1){
            $money=$info['money'];
        }else{
            $money=input('param.money');
        }

        if(!$money || !is_numeric($money) || $money<=0){
            return app('json')->fail('支付金额有误，请确认支付金额');
        }

        $uid=session('user_uid');
        //创建订单
        $data=[
            'uid'=>$uid,
            'tenant_id'=>$info['tenant_id'],
            'collection_id'=>$info['id'],
            'paid'=>0,
            'pay_price'=>floatval($money),
            'order_id'=>StoreOrder::getNewOrderId(),
            'add_time'=>time()
        ];

//        dump($data);exit;

        $collection_order_id=(new CollectionOrder())->insertGetId($data);
        if($collection_order_id){
            //支付
            $payPriceStatus = CollectionOrder::jsPayPrice($collection_order_id, $uid);
            if ($payPriceStatus){
                $payPriceStatus['timeStamp']=$payPriceStatus['timestamp'];
//                dump($payPriceStatus);exit;
                return app('json')->successful($payPriceStatus);
            }
            else
                return app('json')->fail('下单失败，请稍后再试');

        }

        return app('json')->successful('提交成功');

    }


    //授权登录
    public function auth_login($tenant_id){
        //授权登录
        try {
            $wechatInfo = WechatService::oauthService($tenant_id)->user()->getOriginal();
        } catch (\Exception $e) {
            return app('json')->fail('授权失败', ['message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        if (!isset($wechatInfo['nickname'])) {
            try {
                $wechatInfo = WechatService::getUserInfo($wechatInfo['openid']);
            } catch (\Exception $e) {
                return app('json')->fail('获取信息失败', ['message' => $e->getMessage(), 'line' => $e->getLine()]);
            }
            if (!$wechatInfo['subscribe'] && !isset($wechatInfo['nickname']))
                //唤起微信授权登录
                exit(WechatService::oauthService()
                    ->scopes(['snsapi_userinfo'])
                    ->redirect($this->request->url(true))
                    ->send());
            if (isset($wechatInfo['tagid_list']))
                $wechatInfo['tagid_list'] = implode(',', $wechatInfo['tagid_list']);
        } else {
            if (isset($wechatInfo['privilege'])) unset($wechatInfo['privilege']);
            if (!WechatUser::be(['openid' => $wechatInfo['openid']]))
                $wechatInfo['subscribe'] = 0;
        }
        $openid = $wechatInfo['openid'];
        $user = User::where('uid', WechatUser::openidToUid($openid, 'openid'))->find();
        if (!$user)
            return app('json')->fail('获取用户信息失败');


        session('user_uid',$user['uid']);
        session('user_openid',$openid);
        return true;
    }

    /**
     * 付款成功页面
     * @return bool|string
     */
    public function pay_success(){

//        dump(session('user_uid'));
//        dump(session('user_openid'));
//        exit;

        $id=input('param.id');
        $collection_info=(new Collection())->where('id','=',$id)->find();
        $tenant_id=$collection_info['tenant_id'];
        $this->assign('collection_id',$id);
        //需要做微信授权登录
        if(!session('user_uid') || !session('user_openid')){
            $appid=(new SystemConfig())
                ->where('tenant_id','=',$tenant_id)
                ->where('menu_name','=','wechat_appid')
                ->value('value');
            $appid=trim($appid,'"');

            if(!input('param.code')){
                $notice_url=config('site.default_site_url').'/share/index/collection/id/'.$id;
                $url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$notice_url."&response_type=code&scope=snsapi_base&state=#wechat_redirect";
                $this->redirect($url);
            }else{
                $res=$this->auth_login($tenant_id);
                if($res!==true){
                    return $res;
                }
            }
        }

        //平台前台logo
        $logo=(new PublicController())->getSysConfigValue('routine_index_logo',$tenant_id);
        $logo = str_replace('\\/', '/', $logo);
        $this->assign('logo',$logo);
        $this->assign('info',$collection_info);
        return $this->fetch();
    }
}