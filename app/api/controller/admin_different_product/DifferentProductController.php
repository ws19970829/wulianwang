<?php

namespace app\api\controller\admin_different_product;

use app\admin\model\store\StoreDescription;
use app\admin\model\store\StoreProductAttrValue;
use app\admin\model\system\SystemAttachment;
use app\admin\model\system\SystemConfig;
use app\admin\model\system\SystemGroup;
use app\models\store\StoreBargainUser;
use app\models\store\StoreCart;
use app\models\store\StoreCategory;
use app\models\store\StoreCouponIssue;
use app\models\store\StoreOrder;
use app\models\store\StorePink;
use app\models\store\StoreProduct;
use app\models\store\StoreProductRelation;
use app\models\store\StoreProductAttr;
use app\models\store\StoreService;
use app\models\store\StoreVisit;
use app\models\system\Express;
use app\models\system\SystemCity;
use app\models\system\SystemStore;
use app\models\system\SystemStoreStaff;
use app\models\store\StoreProductReply;
use app\models\user\SystemAdmin;
use app\models\user\User;
use app\models\user\UserBill;
use app\models\user\WechatUser;
use app\Request;
use crmeb\repositories\OrderRepository;
use crmeb\services\CacheService;
use crmeb\services\QrcodeService;
use crmeb\services\UtilService;
use crmeb\services\workerman\ChannelService;
use think\facade\Cache;
use crmeb\services\upload\Upload;

/**
 * 后台异业适配公共类
 * Class PublicController
 * @package app\api\controller
 */
class DifferentProductController
{
    /**
     * 后台异业商品列表页
     * @param $menu_name
     * @param $tenant_id
     * @return mixed|string
     */
    public function admin_index(Request $request)
    {
        list($price_order, $sale_order,$category_id, $second_category_id,$page, $limit) = UtilService::getMore([
            ['price_order', ''],
            ['sale_order', ''],
            ['category_id',''],
            ['second_category_id',''],
            ['page', 1],
            ['limit', 10]
        ], $request, true);
        //首页分类
        $category_list = StoreCategory::getPidCategoryList();
        foreach($category_list as $k => $v){
            $category_list[$k]['status'] = 0;
            if($v['id'] == $category_id){
                $category_list[$k]['status'] = 1;
            }
        }
        //商品分类以及二级分类
        $category_second_list = StoreCategory::getSecondCategoryList($category_id);
        foreach($category_second_list as $kk => $vv){
            $category_list[$kk]['status'] = 0;
            if($vv['id'] == $second_category_id){
                $category_list[$kk]['status'] = 1;
            }
        }
        //商品列表
        $store_product_list = StoreProduct::getDifferentStoreProductList($price_order,$sale_order,$page,$limit,$second_category_id);
        $data = array();
        $data['home_category_list'] = $category_list;
        $data['category_list'] = $category_second_list;
        $data['store_product_list'] = $store_product_list;

        return app('json')->successful($data);

    }


    /**
     * 后台异业商品详情页
     * @param $menu_name
     * @param $tenant_id
     * @return mixed|string
     */
    public function different_product_detail(Request $request,$type = 0)
    {
        list($id) = UtilService::postMore([
            ['id', ''],
        ], $request, true);
        if (!$id || !($storeInfo = StoreProduct::getValidProduct($id))) return app('json')->fail('商品不存在或已下架');

        /* $tenant_id=input('param.tenant_id');
         $siteUrl = $this->getSysConfigValue('site_url',$tenant_id);
         $storeInfo['image'] = set_file_url($storeInfo['image'], $siteUrl);
         $storeInfo['image_base'] = set_file_url($storeInfo['image'], $siteUrl);
         $storeInfo['code_base'] = QrcodeService::getWechatQrcodePath($id . '_product_detail_wap.jpg', '/detail/' . $id);*/
        $siteUrl = sys_config('site_url');
        $storeInfo['image'] = set_file_url($storeInfo['image'], $siteUrl);
        $storeInfo['image_base'] = set_file_url($storeInfo['image'], $siteUrl);
        $storeInfo['code_base'] = QrcodeService::getWechatQrcodePath($id . '_product_detail_wap.jpg', '/detail/' . $id);
        $uid = $request->uid();
        $data['uid'] = $uid;
        $storeInfo['description'] = htmlspecialchars_decode(StoreDescription::getDescription($id));
        //替换windows服务器下正反斜杠问题导致图片无法显示
        $storeInfo['description'] = preg_replace_callback('#<img.*?src="([^"]*)"[^>]*>#i', function ($imagsSrc) {
            return isset($imagsSrc[1]) && isset($imagsSrc[0]) ? str_replace($imagsSrc[1], str_replace('\\', '/', $imagsSrc[1]), $imagsSrc[0]) : '';
        }, $storeInfo['description']);
        $storeInfo['userCollect'] = StoreProductRelation::isProductRelation($id, $uid, 'collect');
        $storeInfo['userLike'] = StoreProductRelation::isProductRelation($id, $uid, 'like');
        list($productAttr, $productValue) = StoreProductAttr::getProductAttrDetail($id, $uid, $type);
        $attrValue = $productValue;
        if (!$storeInfo['spec_type']) {
            $productAttr = [];
            $productValue = [];
        }
//        //对规格进行排序
//        $prices = array_column($productValue, 'price');
//        array_multisort($prices, SORT_ASC, SORT_NUMERIC, $productValue);
//        $keys = array_keys($productValue);
//        $productValue = array_combine($keys, $productValue);
        StoreVisit::setView($uid, $id, 'product',$storeInfo['cate_id'], 'viwe');
        $data['storeInfo'] = StoreProduct::setLevelPrice($storeInfo, $uid, true);
        $data['productAttr'] = $productAttr;
        $data['productValue'] = $productValue;
        $data['priceName'] = 0;
        if ($uid) {
            $user = $request->user();
            if (!$user->is_promoter) {
                $price = StoreOrder::where(['paid' => 1, 'refund_status' => 0, 'uid' => $uid])->sum('pay_price');
                $status = is_brokerage_statu($price);
                if ($status) {
                    User::where('uid', $uid)->update(['is_promoter' => 1]);
                    $user->is_promoter = 1;
                }
            }
            if ($user->is_promoter) {
                $data['priceName'] = StoreProduct::getPacketPrice($storeInfo, $attrValue);
            }
            if (!strlen(trim($data['priceName'])))
                $data['priceName'] = 0;
        }
        $data['reply'] = StoreProductReply::getRecProductReply($storeInfo['id']);
        $data['replyCount'] = StoreProductReply::productValidWhere()->where('product_id', $storeInfo['id'])->count();
        if ($data['replyCount']) {
            $goodReply = StoreProductReply::productValidWhere()->where('product_id', $storeInfo['id'])->where('product_score', 5)->count();
            $data['replyChance'] = $goodReply;
            if ($goodReply) {
                $data['replyChance'] = bcdiv($goodReply, $data['replyCount'], 2);
                $data['replyChance'] = bcmul($data['replyChance'], 100, 2);
            }
        } else $data['replyChance'] = 0;
        $data['mer_name'] = SystemAdmin::where('id',$storeInfo['mer_id'])->value('real_name');
        $data['system_store'] = ($res = SystemStore::getStoreDispose()) ? $res : [];
        $data['mapKey'] = sys_config('tengxun_map_key');
        $data['store_self_mention'] = (int)sys_config('store_self_mention') ?? 0;//门店自提是否开启
        $data['activity'] = StoreProduct::activity($data['storeInfo']['id'], false);
        return app('json')->successful($data);
    }


    /**
     * 购物车 添加
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function add(Request $request)
    {
        list($productId, $cartNum, $uniqueId, $combinationId, $secKillId, $bargainId, $new) = UtilService::postMore([
            ['productId',0],//普通产品编号
            ['cartNum',1], //购物车数量
            ['uniqueId',''],//属性唯一值
            ['combinationId',0],//拼团产品编号
            ['secKillId',0],//秒杀产品编号
            ['bargainId',0],//砍价产品编号
            ['new',1], // 1 加入购物车直接购买  0 加入购物车
        ], $request, true);
        $is_admin = 1;
        $is_different = 1;
        if (!$productId || !is_numeric($productId)) return app('json')->fail('参数错误');
        if ($bargainId && StoreBargainUserHelp::getSurplusPrice($bargainId, $request->uid())) return app('json')->fail('请先砍价');
        $res = StoreCart::setCart($request->uid(), $productId, $cartNum, $uniqueId, 'product', $new, $combinationId, $secKillId, $bargainId,$is_admin,$is_different);
        if (!$res) return app('json')->fail(StoreCart::getErrorInfo());
        else  return app('json')->successful('ok', ['cartId' => $res->id]);
    }

    /**
     * 购物车 删除产品
     * @param Request $request
     * @return mixed
     */
    public function del(Request $request)
    {
        list($ids) = UtilService::postMore([
            ['ids',[]],//购物车编号
        ], $request, true);
        if (!count($ids))
            return app('json')->fail('参数错误!');
        if(StoreCart::removeUserCart($request->uid(), $ids))
            return app('json')->successful();
        return app('json')->fail('清除失败！');
    }

    /**
     * 购物车 修改产品数量
     * @param Request $request
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function num(Request $request)
    {
        list($id, $number) = UtilService::postMore([
            ['id',0],//购物车编号
            ['number',0],//购物车编号
        ], $request, true);
        if (!$id || !$number || !is_numeric($id) || !is_numeric($number)) return app('json')->fail('参数错误!');
        $res = StoreCart::changeUserCartNum($id, $number, $request->uid());
        if ($res)  return app('json')->successful();
        else return app('json')->fail(StoreCart::getErrorInfo('修改失败'));
    }

    /**
     * 购物车 获取数量
     * @param Request $request
     * @return mixed
     */
    public function count(Request $request)
    {
        list($numType) = UtilService::postMore([
            ['numType',true],//购物车编号
        ], $request, true);
        if(!(int)$numType) $numType = false;
        return  app('json')->success('ok', ['count'=>StoreCart::getUserCartNum($request->uid(), 'product', $numType)]);
    }

    /**
     * 订单创建
     * @param Request $request
     * @param $key
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function create(Request $request, $key)
    {
        if (!$key) return app('json')->fail('参数错误!');
        $uid = $request->uid();
        if (StoreOrder::be(['order_id|unique' => $key, 'uid' => $uid, 'is_del' => 0]))
            return app('json')->status('extend_order', '订单已生成', ['orderId' => $key, 'key' => $key]);

        $userInfo = $request->user()->toArray();
        //检查额度
        if($userInfo['is_real_name'] == 0){
//            return api_error(40200,'请先进行实名认证');
            return app('json')->status('40200', '请先进行实名认证');
        }

        list($addressId, $couponId, $payType, $useIntegral, $mark, $combinationId, $pinkId, $seckill_id, $formId, $bargainId, $from, $shipping_type, $real_name, $phone, $storeId) = UtilService::postMore([
            'addressId', 'couponId', 'payType', ['useIntegral', 0], 'mark', ['combinationId', 0], ['pinkId', 0], ['seckill_id', 0], ['formId', ''], ['bargainId', ''], ['from', 'weixin'],
            ['shipping_type', 1], ['real_name', ''], ['phone', ''], ['store_id', 0]
        ], $request, true);


        $payType = strtolower($payType);
        if ($bargainId) {
            $bargainUserTableId = StoreBargainUser::getBargainUserTableId($bargainId, $uid);//TODO 获取用户参与砍价表编号
            if (!$bargainUserTableId)
                return app('json')->fail('砍价失败');
            $status = StoreBargainUser::getBargainUserStatusEnd($bargainUserTableId);
            if ($status == 3)
                return app('json')->fail('砍价已支付');
            StoreBargainUser::setBargainUserStatus($bargainId, $uid); //修改砍价状态
        }
        if ($pinkId) {
            $cache_pink = Cache::get(md5('store_pink_'.$pinkId));
            if($cache_pink && bcsub($cache_pink['people'], $cache_pink['now_people'], 0) <= 0){
                return app('json')->status('ORDER_EXIST', '订单生成失败，该团人员已满', ['orderId' => StoreOrder::getStoreIdPink($pinkId, $request->uid())]);
            }
            if (StorePink::getIsPinkUid($pinkId, $request->uid()))
                return app('json')->status('ORDER_EXIST', '订单生成失败，你已经在该团内不能再参加了', ['orderId' => StoreOrder::getStoreIdPink($pinkId, $request->uid())]);
            if (StoreOrder::getIsOrderPink($pinkId, $request->uid()))
                return app('json')->status('ORDER_EXIST', '订单生成失败，你已经参加该团了，请先支付订单', ['orderId' => StoreOrder::getStoreIdPink($pinkId, $request->uid())]);
        }

        //小程序-routine 微信H5：weixinh5 微信公众号：weixin APP：fromapp PC网站：frompc
        $isChannel = 1;
        if ($from == 'weixin')      //微信公众号
            $isChannel = 0;
        elseif ($from == 'weixinh5')    //微信h5
            $isChannel = 2;
        elseif($from == 'routine')    //小程序
            $isChannel = 1;
        elseif($from == 'frompc')       //PC网站
            $isChannel = 4;
        elseif($from == 'fromapp')       //app支付
            $isChannel = 5;

        $order = StoreOrder::cacheKeyCreateOrder($request->uid(), $key, $addressId, $payType, (int)$useIntegral, $couponId, $mark, $combinationId, $pinkId, $seckill_id, $bargainId, false, $isChannel, $shipping_type, $real_name, $phone, $storeId);

        if(isset($order['codes'])) return app('json')->status($order['codes'], $order['msg'],$order['data']);

        if ($order === false) return app('json')->fail(StoreOrder::getErrorInfo('订单生成失败'));
        $orderId = $order['order_id'];
        $info = compact('orderId', 'key');

        //请求报文 todo
        $order_request_data = [
            'addressId'=>$addressId,
            'couponId'=>$couponId,
            'payType'=>$payType,
            'useIntegral'=>$useIntegral,
            'mark'=>$mark,
            'combinationId'=>$combinationId,
            'pinkId'=>$pinkId,
            'seckill_id'=>$seckill_id,
            'formId'=>$formId,
            'bargainId'=>$bargainId,
            'from'=>$from,
            'shipping_type'=>$shipping_type,
            'real_name'=>$real_name,
            'phone'=>$phone,
            'storeId'=>$storeId,
        ];


        if ($orderId) {
            event('OrderCreated', [$order]); //订单创建成功事件
//            event('ShortMssageSend', [$orderId, 'AdminPlaceAnOrder']);//发送管理员通知
            switch ($payType) {
                case "weixin":
                    $orderInfo = StoreOrder::where('order_id', $orderId)->find();
                    if (!$orderInfo || !isset($orderInfo['paid'])) return app('json')->fail('支付订单不存在!');
                    $orderInfo = $orderInfo->toArray();
                    if ($orderInfo['paid']) return app('json')->fail('支付已支付!','');

                    //保存请求报文 todo
                    (new StoreOrderRequest())->saveRequestData($orderInfo['id'],$orderInfo['order_id'],$order_request_data);

                    //支付金额为0
                    if (bcsub((float)$orderInfo['pay_price'], 0, 2) <= 0) {     //0元

                        //创建订单jspay支付
                        $payPriceStatus = StoreOrder::jsPayPrice($orderId, $uid, $formId);
                        if ($payPriceStatus)//0元支付成功
                            return app('json')->status('success', '微信支付成功', $info);
                        else
                            return app('json')->status('pay_error', StoreOrder::getErrorInfo());
                    } else {
                        try {
                            if ($from == 'routine') {
                                $jsConfig = OrderRepository::jsPay($orderId); //创建订单jspay
                            } else if ($from == 'weixinh5') {
                                $jsConfig = OrderRepository::h5Pay($orderId);
//                                $jsConfig = OrderRepository::wxH5Pay($orderId);
                            }else if($from == 'fromapp'){
                                $jsConfig = OrderRepository::wxAppPay($orderId);
                                $jsConfig = json_decode($jsConfig,true);
                            }else if($from == 'frompc'){
                                $jsConfig = OrderRepository::wxPcPay((string)$orderId);
                            }else {
                                $jsConfig = OrderRepository::wxPay($orderId);
                            }
                        } catch (\Exception $e) {
                            return app('json')->status('pay_error', $e->getMessage(), $info);
                        }
                        $info['jsConfig'] = $jsConfig;
                        if ($from == 'weixinh5') {
                            return app('json')->status('wechat_h5_pay', '订单创建成功', $info);
                        }else if($from == 'fromapp'){
                            return app('json')->status('wechat_app_pay', '订单创建成功', $info);
                        }else if($from == 'frompc'){
                            return app('json')->status('wechat_pc_pay', '订单创建成功', $info);
                        } else {
                            return app('json')->status('wechat_pay', '订单创建成功', $info);
                        }
                    }
                    break;
                case 'yue':
                    if (StoreOrder::yuePay($orderId, $request->uid(), $formId))
                        return app('json')->status('success', '余额支付成功', $info);
                    else {
                        $errorinfo = StoreOrder::getErrorInfo();
                        if (is_array($errorinfo))
                            return app('json')->status($errorinfo['status'], $errorinfo['msg'], $info);
                        else
                            return app('json')->status('pay_error', $errorinfo);
                    }
                    break;
                case 'offline':
                    return app('json')->status('success', '订单创建成功', $info);
                    break;
                case 'zhifubao':        //支付宝支付
                    $orderInfo = StoreOrder::where('order_id', $orderId)->find();
                    if (!$orderInfo || !isset($orderInfo['paid'])) return app('json')->fail('支付订单不存在!');
                    $orderInfo = $orderInfo->toArray();
                    if ($orderInfo['paid']) return app('json')->fail('支付已支付!');

                    //保存请求报文 todo
                    (new StoreOrderRequest())->saveRequestData($orderInfo['id'],$orderInfo['order_id'],$order_request_data);

                    //支付金额为0
                    if (bcsub((float)$orderInfo['pay_price'], 0, 2) <= 0) {     //0元
                        //创建订单jspay支付
                        $payPriceStatus = StoreOrder::zfbPayPrice($orderId, $uid, $formId);
                        if ($payPriceStatus)//0元支付成功
                            return app('json')->status('success', '支付宝支付成功', $info);
                        else
                            return app('json')->status('pay_error', StoreOrder::getErrorInfo());
                    } else {
                        try {
                            if ($from == 'weixinh5') {      //支付宝H5支付
                                $jsConfig = OrderRepository::aliwappay($orderId);
                            }else if($from == 'fromapp'){       //支付宝app支付
                                $jsConfig = OrderRepository::aliAppPay($orderId);
                            }else if($from == 'frompc'){        //电脑网站支付
                                $jsConfig = OrderRepository::aliPcPay($orderId);
                            }
                        } catch (\Exception $e) {
                            return app('json')->status('pay_error', $e->getMessage(), $info);
                        }
                        $info['jsConfig'] = $jsConfig;
                        if ($from == 'weixinh5') {
                            return app('json')->status('ali_h5_pay', '订单创建成功', $info);
                        }else if($from == 'fromapp'){
                            return app('json')->status('ali_app_pay', '订单创建成功', $info);
                        }else if($from == 'frompc'){
                            return app('json')->status('ali_pc_pay', '订单创建成功', $info);
                        } else {
                            return app('json')->status('ali_pay', '订单创建成功', $info);
                        }
                    }
                    break;

            }
        } else return app('json')->fail(StoreOrder::getErrorInfo('订单生成失败!'));
    }

    /**
     * 订单 再次下单
     * @param Request $request
     * @return mixed
     */
    public function again(Request $request)
    {
        list($uni) = UtilService::postMore([
            ['uni', ''],
        ], $request, true);
        if (!$uni) return app('json')->fail('参数错误!');
        $order = StoreOrder::getUserOrderDetail($request->uid(), $uni);
        if (!$order) return app('json')->fail('订单不存在!');
        $order = StoreOrder::tidyOrder($order, true);
        $res = [];
        foreach ($order['cartInfo'] as $v) {
            if ($v['combination_id']) return app('json')->fail('拼团产品不能再来一单，请在拼团产品内自行下单!');
            else if ($v['bargain_id']) return app('json')->fail('砍价产品不能再来一单，请在砍价产品内自行下单!');
            else if ($v['seckill_id']) return app('json')->ail('秒杀产品不能再来一单，请在秒杀产品内自行下单!');
            else $res[] = StoreCart::setCart($request->uid(), $v['product_id'], $v['cart_num'], isset($v['productInfo']['attrInfo']['unique']) ? $v['productInfo']['attrInfo']['unique'] : '', 'product', 0, 0);
        }
        $cateId = [];
        foreach ($res as $v) {
            if (!$v) return app('json')->fail('再来一单失败，请重新下单!');
            $cateId[] = $v['id'];
        }
        event('OrderCreateAgain', implode(',', $cateId));
        return app('json')->successful('ok', ['cateId' => implode(',', $cateId)]);
    }


    /**
     * 订单支付
     * @param Request $request
     * @return mixed
     */
    public function pay(Request $request)
    {
        list($uni, $paytype, $from) = UtilService::postMore([
            ['uni', ''],
            ['paytype', 'weixin'],
            ['from', 'weixin']
        ], $request, true);

        if (!$uni) return app('json')->fail('参数错误!');
        $order = StoreOrder::getUserOrderDetail($request->uid(), $uni);

        if (!$order)
            return app('json')->fail('订单不存在!');
        if ($order['paid'])
            return app('json')->fail('该订单已支付!');
        if ($order['pink_id']){
            $cache_pink = Cache::get(md5('store_pink_'.$order['pink_id']));
            if(StorePink::isPinkStatus($order['pink_id'])  || ($cache_pink && bcsub($cache_pink['people'], $cache_pink['now_people'], 0) <= 0)){
                return app('json')->fail('该订单已失效!');
            }
        }

        if ($from == 'weixin') {//0
            if (in_array($order->is_channel, [1, 2,4,5]))
                $order['order_id'] = mt_rand(100, 999) . '_' . $order['order_id'];
        }
        if ($from == 'weixinh5') {//2
            if (in_array($order->is_channel, [0, 1,4,5]))
                $order['order_id'] = mt_rand(100, 999) . '_' . $order['order_id'];
        }
        if ($from == 'routine') {//1
            if (in_array($order->is_channel, [0, 2,4,5]))
                $order['order_id'] = mt_rand(100, 999) . '_' . $order['order_id'];
        }

        if ($from == 'frompc') {//4
            if (in_array($order->is_channel, [0,1,2,5]))
                $order['order_id'] = mt_rand(100, 999) . '_' . $order['order_id'];
        }

        if ($from == 'fromapp') {//5
            if (in_array($order->is_channel, [0,1,2,4]))
                $order['order_id'] = mt_rand(100, 999) . '_' . $order['order_id'];
        }
        $order['pay_type'] = $paytype; //重新支付选择支付方式
        switch ($order['pay_type']) {
            case 'weixin':
                try {
                    if ($from == 'routine') {
                        $jsConfig = OrderRepository::jsPay($order); //订单列表发起支付
                    } else if ($from == 'weixinh5') {
                        $jsConfig = OrderRepository::h5Pay($order);
//                        $jsConfig = OrderRepository::wxH5Pay($order);
                    }else if($from == 'fromapp'){
                        $jsConfig = OrderRepository::wxAppPay($order);
                        $jsConfig = json_decode($jsConfig,true);
                    }else if($from == 'frompc'){
                        $jsConfig = OrderRepository::wxPcPay($order);
                    } else {
                        $jsConfig = OrderRepository::wxPay($order);
                    }
                } catch (\Exception $e) {
                    return app('json')->fail($e->getMessage());
                }

                if ($from == 'weixinh5') {
                    return app('json')->status('wechat_h5_pay', ['jsConfig' => $jsConfig, 'order_id' => $order['order_id']]);
                }else if($from == 'fromapp'){
                    return app('json')->status('wechat_app_pay', ['jsConfig' => $jsConfig, 'order_id' => $order['order_id']]);
                }else if($from == 'frompc'){
                    return app('json')->status('wechat_pc_pay', ['jsConfig' => $jsConfig, 'order_id' => $order['order_id']]);
                } else {
                    return app('json')->status('wechat_pay', ['jsConfig' => $jsConfig, 'order_id' => $order['order_id']]);
                }

                break;
            case 'yue':
                if (StoreOrder::yuePay($order['order_id'], $request->uid()))
                    return app('json')->status('success', '余额支付成功');
                else {
                    $error = StoreOrder::getErrorInfo();
                    return app('json')->fail(is_array($error) && isset($error['msg']) ? $error['msg'] : $error);
                }
                break;
            case 'offline':
                StoreOrder::createOrderTemplate($order);
                if (StoreOrder::setOrderTypePayOffline($order['order_id']))
                    return app('json')->status('success', '订单创建成功');
                else
                    return app('json')->status('success', '支付失败');
                break;
            case 'zhifubao':
                try {
                    if ($from == 'weixinh5') {
                        $jsConfig = OrderRepository::aliwappay($order);
                    }else if($from == 'fromapp'){
                        $jsConfig = OrderRepository::aliAppPay($order);
                    }else if($from == 'frompc'){
                        $jsConfig = OrderRepository::aliPcPay($order);
                    }

                } catch (\Exception $e) {
                    return app('json')->fail($e->getMessage());
                }
//                if ($from == 'weixinh5') {
//                    return app('json')->status('ali_h5_pay', ['jsConfig' => $jsConfig, 'order_id' => $order['order_id']]);
//                } else {
//                    return app('json')->status('ali_pay', ['jsConfig' => $jsConfig, 'order_id' => $order['order_id']]);
//                }


                if ($from == 'weixinh5') {
                    return app('json')->status('ali_h5_pay', ['jsConfig' => $jsConfig, 'order_id' => $order['order_id']]);
                }else if($from == 'fromapp'){
                    return app('json')->status('ali_app_pay', ['jsConfig' => $jsConfig, 'order_id' => $order['order_id']]);
                }else if($from == 'frompc'){
                    return app('json')->status('ali_pc_pay', ['jsConfig' => $jsConfig, 'order_id' => $order['order_id']]);
                } else {
                    return app('json')->status('ali_pay', ['jsConfig' => $jsConfig, 'order_id' => $order['order_id']]);
                }
                break;
        }
        return app('json')->fail('支付方式错误');
    }

    /**
     * 购物车列表
     * @param Request $request
     * @param $key
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cart_list(Request $request)
    {
        list($admin_id) = UtilService::postMore([
            ['admin_id', '']
        ], $request, true);
        $is_admin = 1;
        return app('json')->successful(StoreCart::getUserProductCartList($admin_id,'','',$is_admin));
    }
    
    /**
     * 申请代销
     * @param Request $request
     * @param $key
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function apply_consignment(Request $request)
    {
        list($product_id) = UtilService::postMore([
            ['product_id', '']
        ], $request, true);

        //获取代销的商品、图片、价格以及商家名称
        $store_product = StoreProduct::where('id',$product_id)->field('id,image,store_name,store_info,price,tenant_id')->find();
        $store_product['admin_name'] = SystemAdmin::where('id',$store_product['tenant_id'])->value('real_name');

        //规格列表
        $suk_list = StoreProductAttrValue::where('product_id',$store_product['id'])->field('suk,stock,price')->select();
        return app('json')->successful(compact('store_product','suk_list'));
    }


    /**
     * 申请代销保存
     * @param Request $request
     * @param $key
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function save_apply_consignment(Request $request)
    {
        list($product_id,$attrs) = UtilService::postMore([
            ['product_id', ''],
            ['attrs',[]]
        ], $request, true);
        $attrs = array();
        $attrs[0]['suk'] = "默认";
        $attrs[0]['price'] = 100;
        $attrs[0]['new_price'] = 100;
        $attrs[1]['suk'] = "默认";
        $attrs[1]['price'] = 100;
        $attrs[1]['new_price'] = 100;
        foreach($attrs as $k => $v){
            if($v['price'] > $v['new_price']){
                return app('json')->fail('设置的价格不能比原本价格低');
            }
        }


        //将数据更改成为自己的数据
        $store_product = StoreProduct::where('id',$product_id)->field('id',true)->find()->toArray();
        dump($store_product);exit;
        $admin_id = 18;
        $store_product['mer_id'] = $admin_id;
        //新增数据
        $result = StoreProduct::create($store_product);
        dump($result);exit;
        $list = StoreProductAttrValue::where('product_id',$product_id)->select();
        foreach($attrs as $k => $v){
            $list[$k]['mer_price'] = $v['price'];
            $list[$k]['price'] = $v['new_price'];
        }
        StoreProductAttrValue::create($list);


        return app('json')->successful('申请异业适配成功');
    }

}