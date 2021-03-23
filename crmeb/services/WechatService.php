<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/23
 */

namespace crmeb\services;

use app\admin\model\collection\CollectionOrder;
use app\admin\model\fans\FansPlanUser;
use app\admin\model\system\SystemConfig;
use app\admin\model\wechat\WechatMessage;
use app\admin\model\wechat\WechatReply;
use app\api\controller\FansController;
use app\models\store\StoreOrder;
use app\models\user\User;
use app\models\user\WechatUser;
use crmeb\repositories\MessageRepositories;
use crmeb\repositories\PaymentRepositories;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Message\Article;
use EasyWeChat\Message\Image;
use EasyWeChat\Message\Material;
use EasyWeChat\Message\News;
use EasyWeChat\Message\Text;
use EasyWeChat\Message\Video;
use EasyWeChat\Message\Voice;
use EasyWeChat\Payment\Order;
use EasyWeChat\Payment\Payment;
use EasyWeChat\Server\Guard;
use think\Response;
use crmeb\utils\Hook;

class WechatService
{
    private static $instance = null;

    public static function options($tenant_id=0)
    {

        if(!$tenant_id){
            $tenant_id=session('tenant_id');
        }

        //更改后获取配置的方法，通过tenant_id来获取
        $wechat_config_list=(new SystemConfig())
            ->where('tenant_id','=',$tenant_id)
            ->where('menu_name','in','wechat_appid,wechat_appsecret,wechat_token,wechat_encodingaeskey,wechat_encode')
            ->field('menu_name,value,tenant_id')
            ->select()
            ->toArray();
        $wechat=[];
        if($wechat_config_list){
            foreach($wechat_config_list as $val){
                $tem=trim($val['value'],'\"');
                $wechat[$val['menu_name']]=trim($tem);
            }
        }else{
            $wechat = SystemConfigService::more(['wechat_appid', 'wechat_appsecret', 'wechat_token', 'wechat_encodingaeskey', 'wechat_encode'],false,$tenant_id);
        }

        //支付配置
        $payment_config_list=(new SystemConfig())
            ->where('tenant_id','=',$tenant_id)
            ->where('menu_name','in','pay_weixin_mchid,pay_weixin_client_cert,pay_weixin_client_key,pay_weixin_key,pay_weixin_open')
            ->field('menu_name,value,tenant_id')
            ->select()
            ->toArray();

        //处理退款用的支付证书，更改为绝对路径
        foreach($payment_config_list as $key=>$val){
            $payment_config_list[$key]['value']=$val['value']=trim(str_replace('\\/', '/', $val['value']),'"');
            if($val['menu_name']=='pay_weixin_client_cert' || $val['menu_name']=='pay_weixin_client_key'){
                $payment_config_list[$key]['value']=$_SERVER['DOCUMENT_ROOT'].$val['value'];
            }
        }

//        dump($payment_config_list);exit;
        $payment=[];
        if($payment_config_list){
            foreach($payment_config_list as $val){
                $tem=trim($val['value'],'\"');
                $payment[$val['menu_name']]=trim($tem);
            }
        }else{
            $payment = SystemConfigService::more(['wechat_appid', 'wechat_appsecret', 'wechat_token', 'wechat_encodingaeskey', 'wechat_encode'],false,$tenant_id);
        }


        $config = [
            'app_id' => isset($wechat['wechat_appid']) ? trim($wechat['wechat_appid']) : '',
            'secret' => isset($wechat['wechat_appsecret']) ? trim($wechat['wechat_appsecret']) : '',
            'token' => isset($wechat['wechat_token']) ? trim($wechat['wechat_token']) : '',
            'guzzle' => [
                'timeout' => 10.0, // 超时时间（秒）
                'verify' => false
            ],
        ];

        if (isset($wechat['wechat_encode']) && (int)$wechat['wechat_encode'] > 0 && isset($wechat['wechat_encodingaeskey']) && !empty($wechat['wechat_encodingaeskey']))
            $config['aes_key'] = $wechat['wechat_encodingaeskey'];
        if (isset($payment['pay_weixin_open']) && $payment['pay_weixin_open'] == 1) {
            $config['payment'] = [
                'merchant_id' => trim($payment['pay_weixin_mchid']),
                'key' => trim($payment['pay_weixin_key']),
                'cert_path' => realpath('.' . $payment['pay_weixin_client_cert']),
                'key_path' => realpath('.' . $payment['pay_weixin_client_key']),
                'notify_url' => config('site.default_site_url') . '/api/wechat/notify'
            ];
        }

//        mylog('支付配置new2',$config);
        return $config;
    }


    public static function application($cache = false,$tenant_id=0)
    {
        (self::$instance === null || $cache === true) && (self::$instance = new Application(self::options($tenant_id)));
        return self::$instance;
    }

    public static function serve(): Response
    {

        $open_id=input('param.openid');
        $tenant_id=0;
        if($open_id){
            $tenant_id=User::getTenantIDbyOpenID($open_id);
        }
        $wechat = self::application(false,$tenant_id);
        $server = $wechat->server;
        self::hook($server);
        $response = $server->serve();
        return response($response->getContent());
    }

    /**
     * 监听行为
     * @param Guard $server
     */
    private static function hook($server)
    {

//        mylog('回调监听',input('param.'));
        $server->setMessageHandler(function ($message) {
            event('WechatMessageBefore', [$message]);
//            mylog('回调MsgType',$message->MsgType);

            switch ($message->MsgType) {
                case 'event':
                    //用户关注与取消关注事件
                    switch (strtolower($message->Event)) {
                        case 'subscribe':
                            //关注公众号事件
//                            mylog('关注事件','1111111111');

                            $response = WechatReply::reply('subscribe');
                            if (isset($message->EventKey)) {
                                if ($message->EventKey && ($qrInfo = QrcodeService::getQrcode($message->Ticket, 'ticket'))) {
                                    QrcodeService::scanQrcode($message->Ticket, 'ticket');
                                    if (strtolower($qrInfo['third_type']) == 'spread') {
                                        try {
                                            $spreadUid = $qrInfo['third_id'];
                                            $uid = WechatUser::openidToUid($message->FromUserName, 'openid');
                                            if ($spreadUid == $uid) return '自己不能推荐自己';
                                            $userInfo = User::getUserInfo($uid);
                                            if ($userInfo['spread_uid']) return '已有推荐人!';
                                            if (!User::setSpreadUid($userInfo['uid'], $spreadUid)) {
                                                $response = '绑定推荐人失败!';
                                            }
                                        } catch (\Exception $e) {
                                            $response = $e->getMessage();
                                        }
                                    }
                                }
                            }
                            break;
                        case 'unsubscribe':
                            //取消关注公众号事件
//                            mylog('取消关注','1111111111');
                            event('WechatEventUnsubscribeBefore', [$message]);
                            break;
                        case 'scan':
                            //已关注后，扫描带参数公众号关注的二维码事件
                            $response = WechatReply::reply('subscribe');
                            if ($message->EventKey && ($qrInfo = QrcodeService::getQrcode($message->Ticket, 'ticket'))) {
                                QrcodeService::scanQrcode($message->Ticket, 'ticket');
                                if (strtolower($qrInfo['third_type']) == 'spread') {
                                    try {
                                        $spreadUid = $qrInfo['third_id'];
                                        $uid = WechatUser::openidToUid($message->FromUserName, 'openid');
                                        if ($spreadUid == $uid) return '自己不能推荐自己';
                                        $userInfo = User::getUserInfo($uid);
                                        if ($userInfo['spread_uid']) return '已有推荐人!';
                                        if (User::setSpreadUid($userInfo['uid'], $spreadUid)) {
                                            $response = '绑定推荐人失败!';
                                        }
                                    } catch (\Exception $e) {
                                        $response = $e->getMessage();
                                    }
                                }
                            }
                            break;
                        case 'location':
                            $response = MessageRepositories::wechatEventLocation($message);
                            break;
                        case 'click':
                            //菜单点击事件拉取消息事件
//                            mylog('点击事件拉取消息','给用户发个消息');

                            $response = WechatReply::reply($message->EventKey);
                            break;
                        case 'view':
                            //菜单点击跳转页面事件
                            //测试
//                            mylog('跳转事件','给用户发个消息');
                            //给用户发个消息-测试用
//                            $response = MessageRepositories::wechatEventView($message);

//                            使用客服消息 发送营销计划
                            $response = self::sendFansPlanStaffNews($message->FromUserName);

                            break;
                    }
                    break;
                case 'text':
                    //回复文本消息
                    $uid = WechatUser::openidToUid($message->FromUserName, 'openid');
//                    mylog('跳转事件','给用户发个消息1');

                    $tenant_id=User::getTenantIDbyUID($uid);
                    $response = WechatReply::reply($message->Content,'',$tenant_id);
                    break;
                case 'image':
                    //回复图片消息
                    $response = MessageRepositories::wechatMessageImage($message);
                    break;
                case 'voice':
                    //回复语音消息
                    $response = MessageRepositories::wechatMessageVoice($message);
                    break;
                case 'video':
                    //回复视频消息
                    $response = MessageRepositories::wechatMessageVideo($message);
                    break;
                case 'location':
                    //上报地理位置消息
                    $response = MessageRepositories::wechatMessageLocation($message);
                    break;
                case 'link':
                    //链接消息
                    $response = MessageRepositories::wechatMessageLink($message);
                    break;
                // ... 其它消息
                default:
                    $response = MessageRepositories::wechatMessageOther($message);
                    break;
            }

            return $response ?? false;
        });
    }


    /**
     * 多客服消息转发
     * @param string $account
     * @return \EasyWeChat\Message\Transfer
     */
    public static function transfer($account = '')
    {
        $transfer = new \EasyWeChat\Message\Transfer();
        return empty($account) ? $transfer : $transfer->to($account);
    }


    /**
     * 上传永久素材接口
     * @return \EasyWeChat\Material\Material
     */
    public static function materialService()
    {
        return self::application()->material;
    }

    /**
     * 上传临时素材接口
     * @return \EasyWeChat\Material\Temporary
     */
    public static function materialTemporaryService()
    {
        return self::application()->material_temporary;
    }

    /**
     * 用户接口
     * @return \EasyWeChat\User\User
     */
    public static function userService()
    {
        return self::application()->user;
    }


    /**
     * 客服消息接口
     * @param null $to
     * @param null $message
     */
    public static function staffService()
    {
        return self::application()->staff;
    }

    /**
     * 微信公众号菜单接口
     * @return \EasyWeChat\Menu\Menu
     */
    public static function menuService($tenant_id=0)
    {
        return self::application(false,$tenant_id)->menu;
    }

    /**
     * 微信二维码生成接口
     * @return \EasyWeChat\QRCode\QRCode
     */
    public static function qrcodeService()
    {
        return self::application()->qrcode;
    }

    /**
     * 微信永久二维码生成接口 小于10万个
     * @return \EasyWeChat\QRCode\QRCode
     */
    public static function qrcodeForeverService($sceneValue)
    {
        return self::application()->qrcode->forever($sceneValue);
    }

    /**
     * 微信临时二维码生成接口 30天有效期
     * @return \EasyWeChat\QRCode\QRCode
     */
    public static function qrcodeTempService($sceneValue, $expireSeconds = 2592000)
    {
        return self::application()->qrcode->temporary($sceneValue, $expireSeconds);

    }

    /**
     * 短链接生成接口
     * @return \EasyWeChat\Url\Url
     */
    public static function urlService()
    {
        return self::application()->url;
    }

    /**
     * 用户授权
     * @return \Overtrue\Socialite\Providers\WeChatProvider
     */
    public static function oauthService($tenant_id=0)
    {
        return self::application(false,$tenant_id)->oauth;
    }

    /**
     * 模板消息接口
     * @return \EasyWeChat\Notice\Notice
     */
    public static function noticeService()
    {
        return self::application()->notice;
    }

    public static function sendTemplate($openid, $templateId, array $data, $url = null, $defaultColor = null)
    {
        $notice = self::noticeService()->to($openid)->template($templateId)->andData($data);
        if ($url !== null) $notice->url($url);
        if ($defaultColor !== null) $notice->defaultColor($defaultColor);
        return $notice->send();
    }


    /**
     * 支付
     * @return \EasyWeChat\Payment\Payment
     */
    public static function paymentService($tenant_id=0)
    {
        return self::application(false,$tenant_id)->payment;
    }

    public static function downloadBill($day, $type = 'ALL')
    {
//        $payment = self::paymentService();
//        $merchant = $payment->getMerchant();
//        $params = [
//            'appid' => $merchant->app_id,
//            'bill_date'=>$day,
//            'bill_type'=>strtoupper($type),
//            'mch_id'=> $merchant->merchant_id,
//            'nonce_str' => uniqid()
//        ];
//        $params['sign'] = \EasyWeChat\Payment\generate_sign($params, $merchant->key, 'md5');
//        $xml = XML::build($params);
//        dump(self::paymentService()->downloadBill($day)->getContents());
//        dump($payment->getHttp()->request('https://api.mch.weixin.qq.com/pay/downloadbill','POST',[
//            'body' => $xml,
//            'stream'=>true
//        ])->getBody()->getContents());
    }

    public static function userTagService()
    {
        return self::application()->user_tag;
    }

    public static function userGroupService()
    {
        return self::application()->user_group;
    }

    /**
     * 生成支付订单对象
     * @param $openid
     * @param $out_trade_no
     * @param $total_fee
     * @param $attach
     * @param $body
     * @param string $detail
     * @param string $trade_type
     * @param array $options
     * @return Order
     */
    protected static function paymentOrder($openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $trade_type = 'JSAPI', $options = [])
    {
        $total_fee = bcmul($total_fee, 100, 0);
        $order = array_merge(compact('out_trade_no', 'total_fee', 'attach', 'body', 'detail', 'trade_type'), $options);
        if (!is_null($openid)) $order['openid'] = $openid;
        if ($order['detail'] == '') unset($order['detail']);
        return new Order($order);
    }

    /**
     * 获得下单ID
     * @param $openid
     * @param $out_trade_no
     * @param $total_fee
     * @param $attach
     * @param $body
     * @param string $detail
     * @param string $trade_type
     * @param array $options
     * @return mixed
     */
    public static function paymentPrepare($openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $trade_type = 'JSAPI', $options = [],$tenant_id=0)
    {
        $order = self::paymentOrder($openid, $out_trade_no, $total_fee, $attach, $body, $detail, $trade_type, $options);
//        mylog('支付参数',$order);
        $result = self::paymentService($tenant_id)->prepare($order);
//        mylog('支付结果',$result);

        if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS') {
            try {
                PaymentRepositories::wechatPaymentPrepare($order, $result->prepay_id);
            } catch (\Exception $e) {
            }
            return $result;
        } else {
            if ($result->return_code == 'FAIL') {
                exception('微信支付错误返回：' . $result->return_msg);
            } else if (isset($result->err_code)) {
                exception('微信支付错误返回：' . $result->err_code_des);
            } else {
                exception('没有获取微信支付的预支付ID，请重新发起支付!');
            }
            exit;
        }

    }

    /**
     * 获得jsSdk支付参数
     * @param $openid
     * @param $out_trade_no
     * @param $total_fee
     * @param $attach
     * @param $body
     * @param string $detail
     * @param string $trade_type
     * @param array $options
     * @return array|string
     */
    public static function jsPay($openid, $out_trade_no, $total_fee, $attach, $body, $detail = '', $trade_type = 'JSAPI', $options = [],$tenant_id=0)
    {
        $paymentPrepare = self::paymentPrepare($openid, $out_trade_no, $total_fee, $attach, $body, $detail, $trade_type, $options,$tenant_id);
        return self::paymentService($tenant_id)->configForJSSDKPayment($paymentPrepare->prepay_id);
    }

    /**
     * 使用商户订单号退款
     * @param $orderNo
     * @param $refundNo
     * @param $totalFee
     * @param null $refundFee
     * @param null $opUserId
     * @param string $refundReason
     * @param string $type
     * @param string $refundAccount
     */
    public static function refund($orderNo, $refundNo, $totalFee, $refundFee = null, $opUserId = null, $refundReason = '', $type = 'out_trade_no', $refundAccount = 'REFUND_SOURCE_UNSETTLED_FUNDS')
    {
        $tenant_id=(new StoreOrder())
            ->where('order_id','=',$orderNo)
            ->value('tenant_id');
        $totalFee = floatval($totalFee);
        $refundFee = floatval($refundFee);
        return self::paymentService($tenant_id)->refund($orderNo, $refundNo, $totalFee, $refundFee, $opUserId, $type, $refundAccount, $refundReason);
    }

    public static function payOrderRefund($orderNo, array $opt)
    {
        if (!isset($opt['pay_price'])) exception('缺少pay_price');
        $totalFee = floatval(bcmul($opt['pay_price'], 100, 0));
        $refundFee = isset($opt['refund_price']) ? floatval(bcmul($opt['refund_price'], 100, 0)) : null;
        $refundReason = isset($opt['desc']) ? $opt['desc'] : '';
        $refundNo = isset($opt['refund_id']) ? $opt['refund_id'] : $orderNo;
        $opUserId = isset($opt['op_user_id']) ? $opt['op_user_id'] : null;
        $type = isset($opt['type']) ? $opt['type'] : 'out_trade_no';
        /*仅针对老资金流商户使用
        REFUND_SOURCE_UNSETTLED_FUNDS---未结算资金退款（默认使用未结算资金退款）
        REFUND_SOURCE_RECHARGE_FUNDS---可用余额退款*/
        $refundAccount = isset($opt['refund_account']) ? $opt['refund_account'] : 'REFUND_SOURCE_UNSETTLED_FUNDS';
        try {
            $res = (self::refund($orderNo, $refundNo, $totalFee, $refundFee, $opUserId, $refundReason, $type, $refundAccount));
            if ($res->return_code == 'FAIL') exception('退款失败:' . $res->return_msg);
            if (isset($res->err_code)) exception('退款失败:' . $res->err_code_des);
        } catch (\Exception $e) {
            exception($e->getMessage());
        }
        return true;
    }

    /**
     * 微信支付成功回调接口
     */
    public static function handleNotify()
    {
        $postArr = file_get_contents("php://input");
//        mylog('回调的handleNotify方法xml',$postArr);
        $postObj = simplexml_load_string($postArr,'SimpleXMLElement', LIBXML_NOCDATA);//将xml格式的内容转换成为对象
//        mylog('回调的openid',$postObj->openid);

        $openid=$postObj->openid;
        $tenant_id=0;
        if($openid){
            $tenant_id=User::getTenantIDbyOpenID($openid);
        }
        self::paymentService($tenant_id)->handleNotify(function ($notify, $successful) {
            if ($successful && isset($notify->out_trade_no)) {
                if (isset($notify->attach) && $notify->attach) {
                    //TODO  用户扫码付款回调
                    if(strtolower($notify->attach) == 'collection'){
                        try{
                            if(CollectionOrder::be(['order_id'=>$notify->out_trade_no,'paid'=>1])) return true;
                            return CollectionOrder::rechargeSuccess($notify->out_trade_no);
                        }catch (\Exception $e){
                            return false;
                        }
                    }else{
                        //正常支付订单回调
                        if (($count = strpos($notify->out_trade_no, '_')) !== false) {
                            $notify->out_trade_no = substr($notify->out_trade_no, $count + 1);
                        }
                        return (new Hook(PaymentRepositories::class, 'wechat'))
                            ->listen($notify->attach, $notify->out_trade_no);
                    }
                }
                WechatMessage::setOnceMessage($notify, $notify->openid, 'payment_success', $notify->out_trade_no);
                return false;
            }
        });
    }


    /**
     * jsSdk
     * @param int $tenant_id
     * @return \EasyWeChat\Js\Js
     */
    public static function jsService($tenant_id=0)
    {
        return self::application(false,$tenant_id)->js;
    }

    public static function jsSdk($url = '',$tenant_id=0)
    {
        $apiList = ['editAddress', 'openAddress', 'updateTimelineShareData', 'updateAppMessageShareData', 'onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ', 'onMenuShareWeibo', 'onMenuShareQZone', 'startRecord', 'stopRecord', 'onVoiceRecordEnd', 'playVoice', 'pauseVoice', 'stopVoice', 'onVoicePlayEnd', 'uploadVoice', 'downloadVoice', 'chooseImage', 'previewImage', 'uploadImage', 'downloadImage', 'translateVoice', 'getNetworkType', 'openLocation', 'getLocation', 'hideOptionMenu', 'showOptionMenu', 'hideMenuItems', 'showMenuItems', 'hideAllNonBaseMenuItem', 'showAllNonBaseMenuItem', 'closeWindow', 'scanQRCode', 'chooseWXPay', 'openProductSpecificView', 'addCard', 'chooseCard', 'openCard'];
        $jsService = self::jsService($tenant_id);
//        dump('jsSdk------');
//        dump($jsService);
        if ($url) $jsService->setUrl($url);
        try {
            return $jsService->config($apiList);
        } catch (\Exception $e) {
            return '{}';
        }

    }


    /**
     * 回复文本消息
     * @param string $content 文本内容
     * @return Text
     */
    public static function textMessage($content)
    {
        return new Text(compact('content'));
    }

    /**
     * 回复图片消息
     * @param string $media_id 媒体资源 ID
     * @return Image
     */
    public static function imageMessage($media_id)
    {
        return new Image(compact('media_id'));
    }

    /**
     * 回复视频消息
     * @param string $media_id 媒体资源 ID
     * @param string $title 标题
     * @param string $description 描述
     * @param null $thumb_media_id 封面资源 ID
     * @return Video
     */
    public static function videoMessage($media_id, $title = '', $description = '...', $thumb_media_id = null)
    {
        return new Video(compact('media_id', 'title', 'description', 'thumb_media_id'));
    }

    /**
     * 回复声音消息
     * @param string $media_id 媒体资源 ID
     * @return Voice
     */
    public static function voiceMessage($media_id)
    {
        return new Voice(compact('media_id'));
    }

    /**
     * 回复图文消息
     * @param string|array $title 标题
     * @param string $description 描述
     * @param string $url URL
     * @param string $image 图片链接
     */
    public static function newsMessage($title, $description = '...', $url = '', $image = '')
    {
        if (is_array($title)) {
            if (isset($title[0]) && is_array($title[0])) {
                $newsList = [];
                foreach ($title as $news) {
                    $newsList[] = self::newsMessage($news);
                }
                return $newsList;
            } else {
                $data = $title;
            }
        } else {
            $data = compact('title', 'description', 'url', 'image');
        }
//        mylog('news_message',$data);
        return new News($data);
    }

    /**
     * 回复文章消息
     * @param string|array $title 标题
     * @param string $thumb_media_id 图文消息的封面图片素材id（必须是永久 media_ID）
     * @param string $source_url 图文消息的原文地址，即点击“阅读原文”后的URL
     * @param string $content 图文消息的具体内容，支持HTML标签，必须少于2万字符，小于1M，且此处会去除JS
     * @param string $author 作者
     * @param string $digest 图文消息的摘要，仅有单图文消息才有摘要，多图文此处为空
     * @param int $show_cover_pic 是否显示封面，0为false，即不显示，1为true，即显示
     * @param int $need_open_comment 是否打开评论，0不打开，1打开
     * @param int $only_fans_can_comment 是否粉丝才可评论，0所有人可评论，1粉丝才可评论
     * @return Article
     */
    public static function articleMessage($title, $thumb_media_id, $source_url, $content = '', $author = '', $digest = '', $show_cover_pic = 0, $need_open_comment = 0, $only_fans_can_comment = 1)
    {
        $data = is_array($title) ? $title : compact('title', 'thumb_media_id', 'source_url', 'content', 'author', 'digest', 'show_cover_pic', 'need_open_comment', 'only_fans_can_comment');
        return new Article($data);
    }

    /**
     * 回复素材消息
     * @param string $type [mpnews、 mpvideo、voice、image]
     * @param string $media_id 素材 ID
     * @return Material
     */
    public static function materialMessage($type, $media_id)
    {
        return new Material($type, $media_id);
    }

    /**
     * 作为客服消息发送
     * @param $to
     * @param $message
     * @return bool
     */
    public static function staffTo($to, $message)
    {
        $staff = self::staffService();
        $staff = is_callable($message) ? $staff->message($message()) : $staff->message($message);
//        mylog('staff',$staff);
        $res = $staff->to($to)->send();
        return $res;
    }

    /**
     * 作为客服消息发送-图文消息-草谷新增方法
     * @param $to
     * @param $message
     * @param string $title
     * @param string $description
     * @param string $url
     * @param string $image
     * @return bool
     */
    public static function staffToNews($to, $message,$title='',$description='',$url='',$image='')
    {
        $staff = self::staffService();
//        $message = new News([
//            'content' => $message,
//            'title'=>$message,
//            'description'=>$message,
//            'url'=>'http://www.3todo.com',
//            'image'=>'http://qiniu.xiaohuixiang.3todo.com/f2377202008131730033708.png',
//        ]);

        $message = new News([
            'content' => $message,
            'title'=>$title,
            'description'=>$description,
            'url'=>$url,
            'image'=>$image,
        ]);
        $staff = is_callable($message) ? $staff->message($message()) : $staff->message($message);


        $res = $staff->to($to)->send();
        return $res;
    }


    /**
     * 使用客服信息接口发送粉丝营销计划的文章
     * @param $openid
     * @return bool
     */
    public static function sendFansPlanStaffNews($openid){

//        mylog('营销内容openid',$openid);//日志在根目录tlogs下

        $res=(new FansController())->get_staff_plan_info($openid);
//        mylog('营销内容1',$res);//日志在根目录tlogs下
        if(!is_array($res) || $res['code']!=1){
            //未获取成功
            exit;
        }
        foreach($res['data'] as $data){
            $title=$data['title'];
            $description=$data['description'];
            $url=$data['url'];
            $image=$data['image'];
            $res=self::staffToNews($openid,$title,$title,$description,$url,$image);
            if($res['errcode']==0){
                $log=[
                    'uid'=>$data['uid'],
                    'fans_plan_id'=>$data['fans_plan_id'],
                    'fans_note_id'=>$data['fans_note_id'],
                    'tenant_id'=>$data['tenant_id'],
                    'add_time'=>time(),
                    'send_date'=>$data['send_date'],
                ];
                (new FansPlanUser())->create($log);
//                mylog('推送结果',$res);
                //TODO：推送完毕后，更新推送记录
            }

        }

    }

    /**
     * 获得用户信息
     * @param array|string $openid
     * @return \EasyWeChat\Support\Collection
     */
    public static function getUserInfo($openid)
    {
        $userService = self::userService();
        $userInfo = is_array($openid) ? $userService->batchGet($openid) : $userService->get($openid);
        return $userInfo;
    }


}