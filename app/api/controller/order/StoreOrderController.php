<?php

namespace app\api\controller\order;

use app\admin\model\activity\Activity;
use app\admin\model\activity\ActivityCouponIssue;
use app\api\controller\PublicController;
use app\admin\model\activity\ActivityProductGift;
use app\admin\model\order\OrderOutTradeNo;
use app\admin\model\system\{
    SystemAttachment,
    ShippingTemplates
};
use app\admin\model\user\User;
use app\models\routine\RoutineFormId;
use crmeb\repositories\OrderRepository;
use app\models\store\{
    StoreBargainUser,
    StoreCart,
    StoreCoupon,
    StoreCouponIssue,
    StoreCouponUser,
    StoreOrder,
    StoreOrderCartInfo,
    StoreOrderStatus,
    StorePink,
    StoreProductReply,
    StoreSeckill
};
use app\models\system\SystemStore;
use app\models\user\UserAddress;
use app\models\user\UserLevel;
use app\admin\model\system\SystemConfig;
use app\Request;
use think\facade\Cache;
use think\facade\Db;
use crmeb\services\{
    CacheService,
    ExpressService,
    SnowFlake,
    SystemConfigService,
    UtilService
};

/**
 * 订单类
 * Class StoreOrderController
 * @package app\api\controller\order
 */
class StoreOrderController
{
    /**
     * 订单确认
     * @param Request $request
     * @return mixed
     */
    public function confirm(Request $request)
    {
        list($tenant_id) = UtilService::getMore([
            ['tenant_id', ''],
        ], $request, true);
        $temp = ShippingTemplates::get(1);
        if (!$temp) return app('json')->fail('默认模板未配置，无法下单');
        list($cartId) = UtilService::postMore(['cartId'], $request, true);
        if (!is_string($cartId) || !$cartId) return app('json')->fail('请提交购买的商品');
        $uid = $request->uid();
        $user = $request->user()->toArray();
        $activity_id = StoreCart::where('id', '=', $cartId)->value('activity_id');

        $cartGroup = StoreCart::getUserProductCartList($uid, $cartId, 1, 0, $activity_id);

        if (count($cartGroup['invalid'])) return app('json')->fail($cartGroup['invalid'][0]['productInfo']['store_name'] . '已失效!');
        if (!$cartGroup['valid']) return app('json')->fail('进货单商品失效');


        $cartInfo = $cartGroup['valid'];

        $shop = array_filter(array_unique(array_column($cartInfo, 'shop_id'), SORT_REGULAR));

        foreach ($shop as &$v) {
            if (!isset($v['use_coupon'])) $v['use_coupon'] = 1;
            foreach ($cartInfo as $v1) {
                if ($v1['tenant_id'] == $v['id']) {
                    if (!empty($v1['combination_id']) || !empty($v1['seckill_id'])) {
                        $v['use_coupon'] = 0;
                    }
                    $v['list'][] = $v1;
                }
            }
        }
        $cartInfo = array_values($shop);

        $data['cartInfo'] = $cartInfo;

        $addr = UserAddress::where('uid', $uid)->where('is_default', 1)->find();
        $priceGroup = StoreOrder::getOrderPriceGroup($cartInfo, $addr, $user['tenant_id']);
        if ($priceGroup === false) {
            return app('json')->fail(StoreOrder::getErrorInfo('运费模板不存在'));
        }

        $publicController = new PublicController();
        $other = [
            'offlinePostage' => $publicController->getSysConfigValue('offline_postage', $tenant_id),
            'integralRatio' => $publicController->getSysConfigValue('integral_ratio', $tenant_id)
        ];

        // $usableCoupons = StoreCouponUser::getUsableCouponList($uid, $cartGroup, $priceGroup['totalPrice']);
        // $usableCoupon = isset($usableCoupons[0]) ? $usableCoupons[0] : null;
        // $cartIdA = explode(',', $cartId);
        // $seckill_id = 0;
        // $combination_id = 0;
        // $bargain_id = 0;
        // if (count($cartIdA) == 1) {
        //     $seckill_id = StoreCart::where('id', $cartId)->value('seckill_id');
        //     $combination_id = StoreCart::where('id', $cartId)->value('combination_id');
        //     $bargain_id = StoreCart::where('id', $cartId)->value('bargain_id');
        // }
        //$data['deduction'] = $seckill_id || $combination_id || $bargain_id;
        // $data['usableCoupon'] = $usableCoupon;
        $data['addressInfo'] = UserAddress::getUserDefaultAddress($uid);
        // $data['seckill_id'] = $seckill_id;
        // $data['combination_id'] = $combination_id;
        // $data['bargain_id'] = $bargain_id;


        $data['priceGroup'] = $priceGroup;
        $data['totalPrice'] = $priceGroup['totalPrice'];
        $data['totalPrice'] = $priceGroup['totalPrice'];
        //$data['storePostage'] = $priceGroup['storePostage'];
        $data['storePostage'] = 0;
        $data['orderKey'] = StoreOrder::cacheOrderInfo($uid, $cartInfo, $priceGroup, $other);
        // $data['offlinePostage'] = $other['offlinePostage'];
        //$vipId = UserLevel::getUserLevel($uid);
        $user = $request->user();
        if (isset($user['pwd'])) unset($user['pwd']);
        // $user['vip'] = $vipId !== false ? true : false;
        // if ($user['vip']) {
        //     $user['vip_id'] = $vipId;
        //     $user['discount'] = UserLevel::getUserLevelInfo($vipId, 'discount');
        // }
        $data['userInfo'] = $user;
        // $data['integralRatio'] = $other['integralRatio'];
        //$data['offline_pay_status'] = (int)sys_config('offline_pay_status') ?? (int)2;
        // $data['offline_pay_status'] = $publicController->getSysConfigValue('offline_pay_status', $tenant_id) ?? (int) 2;
        //        $data['store_self_mention'] = (int)sys_config('store_self_mention') ?? 0;//门店自提是否开启


        //是否显示优惠券
        // $data['can_use_coupon'] = 1;
        // $activity = [
        //     'type' => 0,
        //     'text' => '普通订单',
        // ];

        // if (!empty($seckill_id)) {
        //     $activity = [
        //         'type' => 1,
        //         'text' => '秒杀订单',
        //     ];
        // } elseif (!empty($combination_id)) {
        //     $activity = [
        //         'type' => 3,
        //         'text' => '拼团订单',
        //     ];
        // }
        // $data['activity'] = $activity;

        return app('json')->success('确认成功', $data);
    }




    /**
     * 计算订单金额
     * @param Request $request
     * @param $key
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function computedOrder(Request $request, $key)
    {
        $uid = $request->uid();
        $param = $request->param();
        $validate = \think\facade\Validate::rule([
            'shopinfo' => 'require',
            'order_type|订单类型' => 'require|in:1,2,3'
        ]);
        if (!$validate->check($param)) {
            return app('json')->fail($validate->getError(), []);
        }
        $shopinfo = json_decode($param['shopinfo'], true);
        if (is_null($shopinfo)) return app('json')->fail('json内容为空');
        $priceGroup = StoreOrder::computePrice($uid, $key, $shopinfo, $param['order_type']);

        if ($priceGroup)
            return app('json')->status('NONE', 'ok', $priceGroup);
        else
            return app('json')->fail(StoreOrder::getErrorInfo());
    }

    /**
     * @Author  lingyun
     * @Desc    计算运费
     * @param Request $request
     */
    public function computePostage(Request $request)
    {
        list($product_id, $addr_id, $cart_num, $product_attr, $isadmin) = UtilService::postMore([
            ['product_id', 0],
            ['addr_id', 0],
            ['cart_num', 1],
            ['product_attr', 0],
            ['isadmin', 0],
        ], $request, true);

        $user = $request->user()->toArray();

        if (empty($product_attr)) {
            $product_attr_value = Db::name('store_product_attr_value')->where('product_id', $product_id)->select();
            if (empty($product_attr_value)) {
                $product_attr = '';
            } else {
                $product_attr = $product_attr_value[0]['unique'];
            }
        }

        $cart_data[] = [
            'uid' => $user['uid'],
            'product_id' => $product_id,
            'cart_num' => $cart_num,
            'product_attr_unique' => $product_attr,
            'is_new' => 0,
            'type' => 'product',
            'combination_id' => 0,
            'add_time' => time(),
            'bargain_id' => 0,
            'seckill_id' => 0,
            'is_admin' => $isadmin,
            'is_different' => 1,
            'activity_id' => 0,
            'activity_product_id' => 0,
            'fans_note_id' => 0,
            'fans_plan_id' => 0,
            'tenant_id' => $user['tenant_id'],
            'activity_type' => 0,
        ];
        $cartGroup = StoreCart::getUserProductCartList($user['uid'], $cart_data, 1, 0, 0);
        if (count($cartGroup['invalid'])) return app('json')->fail($cartGroup['invalid'][0]['productInfo']['store_name'] . '已失效!');
        if (!$cartGroup['valid']) return app('json')->fail('请提交购买的商品');
        $cartInfo = $cartGroup['valid'];

        $addr = UserAddress::where('uid', $user['uid'])->where('is_default', 1)->find();
        $priceGroup = StoreOrder::getOrderPriceGroup($cartInfo, $addr, $user['tenant_id']);
        $payPostage = $priceGroup['storePostage'];

        return app('json')->successful(['$payPostage' => $payPostage]);
    }


    public function create(Request $request)
    {
        $param = $request->param();
        $validate = \think\facade\Validate::rule([
            'key|订单信息' => 'require',
            'addressId|收货地址' => 'require|integer|gt:0',
            'shopinfo' => 'require',
            'order_type|订单类型' => 'require|in:1,2,3'
        ]);
        // 配送方式 1-全款支付 2欠款支付 3物流代收
        if (!$validate->check($param)) {
            return app('json')->fail($validate->getError(), []);
        }

        $uid = $request->uid();
        $shopinfo = json_decode($param['shopinfo'], true);
        if (is_null($shopinfo)) return app('json')->fail('json格式有误');
        $order = StoreOrder::cacheKeyCreateOrder($uid, $param['key'], $param['addressId'], $shopinfo, $param['order_type']);
        if (!$order)  return app('json')->fail(StoreOrder::getErrorInfo(), []);
        return app('json')->successful('创建成功', ['order_ids' => $order]);
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
        return app('json')->successful('提交成功', ['cartId' => implode(',', $cateId)]);
    }

    /**
     * 订单支付
     * @param Request $request
     * @return mixed
     */
    public function pay(Request $request)
    {
        list($ids, $paytype, $from) = UtilService::postMore([
            ['ids', ''],
            ['payType', 'zhifubao'],
            ['from', 'weixin'],
        ], $request, true);

        if (!$ids) return app('json')->fail('参数缺失!');
        if (is_int($ids)) $ids = (string) $ids;
        if (!$pay_price = StoreOrder::beforeCreateOrder($ids)) {
            return app('json')->fail(StoreOrder::getErrorInfo());
        }

        if ($paytype == 'balance' || $pay_price <= 0) {
            //修改订单状态
            StoreOrder::whereIn('id', $ids)->update(['paid' => 1]);
            return app('json')->success('支付成功', []);
        }

        if ($pay_price < 0) return app('json')->fail('支付金额有误');
        $out_trade_no = (string) SnowFlake::generateParticle();
        if (!OrderOutTradeNo::setRecord($ids, $out_trade_no)) {
            return app('json')->fail('支付失败');
        }
        switch ($paytype) {
            case 'weixin':
                try {
                    $jsConfig = OrderRepository::wxAppPay($out_trade_no, $ids, $pay_price);
                    return app('json')->successFul('wechat_pay', ['paySign' => json_decode($jsConfig, true)]);
                } catch (\Exception $e) {
                    return app('json')->fail($e->getMessage());
                }
                break;
            case 'zhifubao':
                try {
                    $jsConfig = OrderRepository::aliAppPay($out_trade_no, $ids, $pay_price);
                    return app('json')->successFul('ali_app_pay', ['paySign' => $jsConfig]);
                } catch (\Exception $e) {
                    return app('json')->fail($e->getMessage() . $e->getFile() . $e->getLine());
                }
                break;
        }
        return app('json')->fail('支付方式错误');
    }

    /**
     * 订单列表
     * @param Request $request
     * @return mixed
     */
    public function order_list(Request $request)
    {
        list($type, $page, $limit, $search) = UtilService::getMore([
            ['type', ''],
            ['page', 0],
            ['limit', ''],
            ['search', ''],
        ], $request, true);
        return app('json')->successful(StoreOrder::getUserOrderSearchList($request->uid(), $type, $page, $limit, $search));
    }

    /**
     * @Author  lingyun
     * @Desc    订单列表
     * @param Request $request
     * return mixed
     */
    public function list_pc(Request $request)
    {
        list($type, $page, $limit, $search) = UtilService::getMore([
            ['type', ''],
            ['page', 0],
            ['limit', ''],
            ['search', ''],
        ], $request, true);
        return app('json')->successful(StoreOrder::getPcUserOrderSearchList($request->uid(), $type, $page, $limit, $search));
    }

    /**
     * 订单详情
     * @param Request $request
     * @param $uni
     * @return mixed
     */
    public function detail($id)
    {
        if (empty($id)) return app('json')->fail('参数错误');
        $order = StoreOrder::get($id);
        if (!$order) return app('json')->fail('订单不存在');
        $order = $order->append(['status_text'])->toArray();
        $coupon_price = $order['coupon_price'] ?? 0;
        $pay_postage = $order['pay_postage'] ?? 0;
        $order['discount_price_text'] = "优惠券-{$coupon_price}";
        //        $store_self_mention = sys_config('store_self_mention');
        //是否开启门店自提
        $publicController = new PublicController();
        $store_self_mention = $publicController->getSysConfigValue('integral_ratio', $order['tenant_id']);

        //关闭门店自提后 订单隐藏门店信息
        if ($store_self_mention == 0) $order['shipping_type'] = 1;
        if ($order['verify_code']) {
            $verify_code = $order['verify_code'];
            $verify[] = substr($verify_code, 0, 4);
            $verify[] = substr($verify_code, 4, 4);
            $verify[] = substr($verify_code, 8);
            $order['_verify_code'] = implode(' ', $verify);
        }
        $order['add_time_y'] = date('Y-m-d', $order['add_time']);
        $order['add_time_h'] = date('H:i:s', $order['add_time']);
        $order['system_store'] = SystemStore::getStoreDispose($order['store_id']);
        if ($order['shipping_type'] === 2 && $order['verify_code']) {
            $name = $order['verify_code'] . '.jpg';
            $imageInfo = SystemAttachment::getInfo($name, 'name');
            $siteUrl = sys_config('site_url');
            if (!$imageInfo) {
                $imageInfo = UtilService::getQRCodePath($order['verify_code'], $name);
                if (is_array($imageInfo)) {
                    SystemAttachment::attachmentAdd($imageInfo['name'], $imageInfo['size'], $imageInfo['type'], $imageInfo['dir'], $imageInfo['thumb_path'], 1, $imageInfo['image_type'], $imageInfo['time'], 2);
                    $url = $imageInfo['dir'];
                } else
                    $url = '';
            } else $url = $imageInfo['att_dir'];
            if (isset($imageInfo['image_type']) && $imageInfo['image_type'] == 1) $url = $siteUrl . $url;
            $order['code'] = $url;
        }
        $order['mapKey'] = sys_config('tengxun_map_key');



        return app('json')->successful('ok', StoreOrder::tidyOrder($order, true, true));
    }

    /**
     * 订单删除
     * @param Request $request
     * @return mixed
     */
    public function del(Request $request)
    {
        list($uni) = UtilService::postMore([
            ['uni', ''],
        ], $request, true);
        if (!$uni) return app('json')->fail('参数错误!');
        $res = StoreOrder::removeOrder($uni, $request->uid());
        if ($res)
            return app('json')->successful('删除成功');
        else
            return app('json')->fail(StoreOrder::getErrorInfo());
    }

    /**
     * 订单收货
     * @param Request $request
     * @return mixed
     */
    public function take(Request $request)
    {
        list($uni) = UtilService::postMore([
            ['uni', ''],
        ], $request, true);
        if (!$uni) return app('json')->fail('参数错误!');
        $res = StoreOrder::takeOrder($uni, $request->uid());
        if ($res) {
            $order_info = StoreOrder::where('order_id', $uni)->find();
            $gain_integral = intval($order_info['gain_integral']);

            $gain_coupon = StoreCouponIssue::alias('a')
                ->join('store_coupon b', 'a.cid = b.id')
                ->where('a.status', 1)
                ->where('a.is_full_give', 1)
                ->where('a.is_del', 0)
                ->where('a.full_reduction', '<=', $order_info['total_price'])
                ->sum('b.coupon_price');

            return app('json')->success('收货成功', ['gain_integral' => $gain_integral, 'gain_coupon' => $gain_coupon]);
        } else
            return app('json')->fail(StoreOrder::getErrorInfo());
    }


    /**
     * 订单 查看物流
     * @param Request $request
     * @param $uni
     * @return mixed
     */
    public function express(Request $request, $uni)
    {
        if (!$uni || !($order = StoreOrder::getUserOrderDetail($request->uid(), $uni))) return app('json')->fail('查询订单不存在!');
        if ($order['delivery_type'] != 'express' || !$order['delivery_id']) return app('json')->fail('该订单不存在快递单号!');
        $cacheName = $uni . $order['delivery_id'];
        $result = CacheService::get($cacheName, null);
        if ($result === NULL) {
            $result = ExpressService::query($order['delivery_id']);
            if (
                is_array($result) &&
                isset($result['result']) &&
                isset($result['result']['deliverystatus']) &&
                $result['result']['deliverystatus'] >= 3
            )
                $cacheTime = 0;
            else
                $cacheTime = 1800;
            CacheService::set($cacheName, $result, $cacheTime);
        }
        $orderInfo = [];
        $cartInfo = StoreOrderCartInfo::where('oid', $order['id'])->column('cart_info', 'unique') ?? [];
        $info = [];
        $cartNew = [];
        foreach ($cartInfo as $k => $cart) {
            $cart = json_decode($cart, true);
            $cartNew['cart_num'] = $cart['cart_num'];
            $cartNew['truePrice'] = $cart['truePrice'];
            $cartNew['productInfo']['image'] = $cart['productInfo']['image'];
            $cartNew['productInfo']['store_name'] = $cart['productInfo']['store_name'];
            $cartNew['productInfo']['unit_name'] = $cart['productInfo']['unit_name'] ?? '';
            array_push($info, $cartNew);
            unset($cart);
        }
        $orderInfo['delivery_id'] = $order['delivery_id'];
        $orderInfo['delivery_name'] = $order['delivery_name'];
        $orderInfo['delivery_type'] = $order['delivery_type'];
        $orderInfo['cartInfo'] = $info;
        return app('json')->successful(['order' => $orderInfo, 'express' => $result ? $result : []]);
    }

    /**
     * 订单评价
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function comment(Request $request)
    {
        $group = UtilService::postMore([
            ['unique', ''], ['comment', ''], ['pics', ''], ['product_score', 5], ['service_score', 5]
        ], $request);
        $unique = $group['unique'];
        unset($group['unique']);
        if (!$unique) return app('json')->fail('参数错误!');
        $cartInfo = StoreOrderCartInfo::where('unique', $unique)->find();
        $uid = $request->uid();
        $user_info = User::get($uid);
        $group['nickname'] = $user_info['nickname'];
        $group['avatar'] = $user_info['avatar'];
        if (!$cartInfo) return app('json')->fail('评价产品不存在!');
        //        $orderUid = StoreOrder::getOrderInfo($cartInfo['oid'], 'uid')['uid'];

        //订单信息
        $orderInfo = StoreOrder::getOrderInfo($cartInfo['oid'], 'uid,order_type,is_consignment,tenant_id,org_tenant_id');
        $orderUid = $orderInfo['uid'];

        if ($uid != $orderUid) return app('json')->fail('评价产品不存在!');
        if (StoreProductReply::be(['oid' => $cartInfo['oid'], 'unique' => $unique]))
            return app('json')->fail('该产品已评价!');
        $group['comment'] = htmlspecialchars(trim($group['comment']));
        if ($group['product_score'] < 1) return app('json')->fail('请为产品评分');
        else if ($group['service_score'] < 1) return app('json')->fail('请为商家服务评分');
        if ($cartInfo['cart_info']['combination_id']) $productId = $cartInfo['cart_info']['product_id'];
        else if ($cartInfo['cart_info']['seckill_id']) $productId = $cartInfo['cart_info']['product_id'];
        else if ($cartInfo['cart_info']['bargain_id']) $productId = $cartInfo['cart_info']['product_id'];
        else $productId = $cartInfo['product_id'];
        if ($group['pics']) $group['pics'] = json_encode(is_array($group['pics']) ? $group['pics'] : explode(',', $group['pics']));

        $tenant_id = \app\models\user\User::getTenantIDbyUID($uid);

        if ($orderInfo['order_type'] == 2) {      //异业订单
            $tenant_id = $orderInfo['org_tenant_id'];       //原商家tenant_id
        }

        $group = array_merge($group, [
            'uid' => $uid,
            'oid' => $cartInfo['oid'],
            'unique' => $unique,
            'product_id' => $productId,
            'add_time' => time(),
            'reply_type' => 'product',
            'tenant_id' => $tenant_id
        ]);
        StoreProductReply::beginTrans();
        $res = StoreProductReply::reply($group, 'product');
        if (!$res) {
            StoreProductReply::rollbackTrans();
            return app('json')->fail('评价失败!');
        }
        try {
            StoreOrder::checkOrderOver($cartInfo['oid']);
        } catch (\Exception $e) {
            StoreProductReply::rollbackTrans();
            return app('json')->fail($e->getMessage());
        }
        StoreProductReply::commitTrans();
        event('UserCommented', $res);
        event('AdminNewPush');
        return app('json')->successful();
    }

    /**
     * 订单统计数据
     * @param Request $request
     * @return mixed
     */
    public function data(Request $request)
    {
        return app('json')->successful(StoreOrder::getOrderData($request->uid()));
    }

    /**
     * 订单退款理由
     * @return mixed
     */
    public function refund_reason(Request $request)
    {
        list($tenant_id) = UtilService::getMore([
            ['tenant_id', ''],
        ], $request, true);

        $reason = SystemConfig::where('menu_name', 'stor_reason')->where('tenant_id', $tenant_id)->value('value');
        $reason = json_decode($reason, true);

        $reason = str_replace("\r\n", "\n", $reason); //防止不兼容
        $reason = explode("\n", $reason);

        return app('json')->successful($reason);
    }

    /**
     * 订单退款审核
     * @param Request $request
     * @return mixed
     */
    public function refund_verify(Request $request)
    {
        $data = UtilService::postMore([
            ['text', ''],
            ['refund_reason_wap_img', ''],
            ['refund_reason_wap_explain', ''],
            ['uni', '']
        ], $request);
        $uni = $data['uni'];
        unset($data['uni']);
        if (!empty($data['refund_reason_wap_img'])) $data['refund_reason_wap_img'] = explode(',', $data['refund_reason_wap_img']);
        if (empty($uni)) return app('json')->fail('参数错误!');
        $res = StoreOrder::orderApplyRefund($uni, $request->uid(), $data['text'], $data['refund_reason_wap_explain'], $data['refund_reason_wap_img']);
        if ($res)
            return app('json')->successful('提交申请成功');
        else
            return app('json')->fail(StoreOrder::getErrorInfo());
    }


    /**
     * 订单取消   未支付的订单回退积分,回退优惠券,回退库存
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cancel(Request $request)
    {
        
        list($id) = UtilService::postMore([['uni', 0]], $request, true);
        if (!$id) return app('json')->fail('参数错误');
        if (StoreOrder::cancelOrder($id, $request->uid()))
            return app('json')->successful('取消订单成功');
        return app('json')->fail(StoreOrder::getErrorInfo('取消订单失败'));
    }

    public function pay_type()
    {
        $data = [
            'paymentType' => 'weixin',
            'paymentArray' => [
                [
                    'title' => '余额',
                    'payment_type' => 'balance'
                ],
                [
                    'title' => '微信',
                    'payment_type' => 'weixin'
                ],
                [
                    'title' => '支付宝',
                    'payment_type' => 'zhifubao'
                ],
            ],
        ];
        return app('json')->successful($data);
    }

    /**
     * 订单产品信息
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function product(Request $request)
    {
        list($unique) = UtilService::postMore([['unique', '']], $request, true);
        if (!$unique || !StoreOrderCartInfo::be(['unique' => $unique]) || !($cartInfo = StoreOrderCartInfo::where('unique', $unique)->find())) return app('json')->fail('评价产品不存在!');
        $cartInfo = $cartInfo->toArray();
        $cartProduct = [];
        $cartProduct['cart_num'] = $cartInfo['cart_info']['cart_num'];
        $cartProduct['productInfo']['image'] = isset($cartInfo['cart_info']['productInfo']['image']) ? $cartInfo['cart_info']['productInfo']['image'] : '';
        $cartProduct['productInfo']['price'] = isset($cartInfo['cart_info']['productInfo']['price']) ? $cartInfo['cart_info']['productInfo']['price'] : 0;
        $cartProduct['productInfo']['store_name'] = isset($cartInfo['cart_info']['productInfo']['store_name']) ? $cartInfo['cart_info']['productInfo']['store_name'] : '';
        if (isset($cartInfo['cart_info']['productInfo']['attrInfo'])) {
            $cartProduct['productInfo']['attrInfo']['product_id'] = isset($cartInfo['cart_info']['productInfo']['attrInfo']['product_id']) ? $cartInfo['cart_info']['productInfo']['attrInfo']['product_id'] : '';
            $cartProduct['productInfo']['attrInfo']['suk'] = isset($cartInfo['cart_info']['productInfo']['attrInfo']['suk']) ? $cartInfo['cart_info']['productInfo']['attrInfo']['suk'] : '';
            $cartProduct['productInfo']['attrInfo']['price'] = isset($cartInfo['cart_info']['productInfo']['attrInfo']['price']) ? $cartInfo['cart_info']['productInfo']['attrInfo']['price'] : '';
            $cartProduct['productInfo']['attrInfo']['image'] = isset($cartInfo['cart_info']['productInfo']['attrInfo']['image']) ? $cartInfo['cart_info']['productInfo']['attrInfo']['image'] : '';
        }
        $cartProduct['product_id'] = isset($cartInfo['cart_info']['product_id']) ? $cartInfo['cart_info']['product_id'] : 0;
        $cartProduct['combination_id'] = isset($cartInfo['cart_info']['combination_id']) ? $cartInfo['cart_info']['combination_id'] : 0;
        $cartProduct['seckill_id'] = isset($cartInfo['cart_info']['seckill_id']) ? $cartInfo['cart_info']['seckill_id'] : 0;
        $cartProduct['bargain_id'] = isset($cartInfo['cart_info']['bargain_id']) ? $cartInfo['cart_info']['bargain_id'] : 0;
        $cartProduct['order_id'] = StoreOrder::where('id', $cartInfo['oid'])->value('order_id');
        return app('json')->successful($cartProduct);
    }

    /**
     * 首页获取未支付订单
     */
    public function get_noPay(Request $request)
    {
        return app('json')->successful(StoreOrder::getUserOrderSearchList($request->uid(), 0, 0, 0, ''));
    }

    /**
     * @Author  lingyun
     * @Desc    查询订单状态
     * @param Request $request
     */
    public function order_pay_state(Request $request, $uni)
    {
        $order_no = $uni;
        $orderno = explode('_', $uni);
        if (isset($orderno[0]) && !is_numeric($orderno[0])) {
            $order_no = $orderno[0];
        } else {
            if (isset($orderno[1]) && !is_numeric($orderno[1])) {
                $order_no = $orderno[1];
            }
        }

        $order = StoreOrder::where('order_id', 'like', '%' . $order_no . '%')->find();
        $order = $order->toArray();
        return app('json')->successful('查询成功', $order);
    }

    /**手续费率 */

    public function business_rate()
    {
        $business_rate = trim(db('system_config')->where('menu_name', 'business_rate')->value('value'), '""');
        $business_message = '欠款支付与物流支付需在线支付' . $business_rate . '%的手续费';
        $business_rate = $business_rate / 100;
        $payment_type_array = [
            ['title'=> '全款支付', 'payment_type'=> 1],
            // ['title'=> '欠款支付', 'payment_type'=> 2],
            // ['title'=> '物流支付', 'payment_type'=> 3],
        ];
        return app('json')->successful(compact('business_rate', 'business_message','payment_type_array'));
    }

    /**支付页面 */
    public function pay_amount(Request $request)
    {
        $ids = $request->param('ids');
        if (empty($ids)) return app('json')->fail('参数缺失');
        $total = StoreOrder::whereIn('id', $ids)->sum('pay_price') ?? 0;
        return app('json')->successful(compact('total'));
    }
}
