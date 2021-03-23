<?php

namespace crmeb\repositories;

use app\models\store\StoreOrder;
use app\models\user\User;
use app\models\user\UserBill;
use app\models\user\WechatUser;
use app\admin\model\order\StoreOrder as AdminStoreOrder;
use app\api\controller\payservice\AlipayController;
use crmeb\services\MiniProgramService;
use app\api\controller\payservice\WxpayController;
use crmeb\services\WechatService;

/**
 * Class OrderRepository
 * @package crmeb\repositories
 */
class OrderRepository
{

    /**
     * TODO 小程序JS支付
     * @param $orderId
     * @param string $field
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function jsPay($orderId, $field = 'order_id')
    {
        $oid = explode('_', $orderId)[1];
        if (is_string($oid))
            $orderInfo = StoreOrder::where($field, $oid)->find();
        else
            $orderInfo = $orderId;

        if (!$orderInfo || !isset($orderInfo['paid'])) exception('支付订单不存在!');
        if ($orderInfo['paid']) exception('支付已支付!');
        if ($orderInfo['pay_price'] <= 0) exception('该支付无需支付!');
        $openid = WechatUser::getOpenId($orderInfo['uid']);

        $bodyContent = StoreOrder::getProductTitle($orderInfo['cart_id']);
        $site_name = sys_config('site_name');
        if (!$bodyContent && !$site_name) exception('支付参数缺少：请前往后台设置->系统设置-> 填写 网站名称');

        (new StoreOrder())->where('id', $orderInfo['id'])->update(['pay_type' => 'weixin', 'is_channel' => 1]);

        return MiniProgramService::jsPay($openid, $orderId, $orderInfo['pay_price'], 'product', StoreOrder::getSubstrUTf8($site_name . ' - ' . $bodyContent, 30));
    }

    /**
     * 微信公众号JS支付
     * @param $orderId
     * @param string $field
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function wxPay($orderId, $field = 'order_id', $tenant_id = 0)
    {
        if (is_string($orderId))
            $orderInfo = StoreOrder::where($field, $orderId)->find();
        else
            $orderInfo = $orderId;
        if (!$orderInfo || !isset($orderInfo['paid'])) exception('支付订单不存在!');
        if ($orderInfo['paid']) exception('支付已支付!');
        if ($orderInfo['pay_price'] <= 0) exception('该支付无需支付!');
        $openid = WechatUser::uidToOpenid($orderInfo['uid'], 'openid');
        $bodyContent = StoreOrder::getProductTitle($orderInfo['cart_id']);
        $site_name = sys_config('site_name');
        if (!$bodyContent && !$site_name) exception('支付参数缺少：请前往后台设置->系统设置-> 填写 网站名称');
        return WechatService::jsPay($openid, $orderInfo['order_id'], $orderInfo['pay_price'], 'product', StoreOrder::getSubstrUTf8($site_name . ' - ' . $bodyContent, 30), '', 'JSAPI', [], $tenant_id);
        //        return WechatService::jsPay($openid, $orderInfo['order_id'], 0.01, 'product', StoreOrder::getSubstrUTf8($site_name . ' - ' . $bodyContent, 30),'','JSAPI',[],$tenant_id);
    }

    /**
     * 微信h5支付
     * @param $orderId
     * @param string $field
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function h5Pay($orderId, $field = 'order_id', $tenant_id = 0)
    {
        if (is_string($orderId))
            $orderInfo = StoreOrder::where($field, $orderId)->find();
        else
            $orderInfo = $orderId;

        //        mylog('tenant_id',$tenant_id);
        if (!$orderInfo || !isset($orderInfo['paid'])) exception('支付订单不存在!');
        if ($orderInfo['paid']) exception('支付已支付!');
        if ($orderInfo['pay_price'] <= 0) exception('该支付无需支付!');
        $bodyContent = StoreOrder::getProductTitle($orderInfo['cart_id']);
        $site_name = sys_config('site_name');
        if (!$bodyContent && !$site_name) exception('支付参数缺少：请前往后台设置->系统设置-> 填写 网站名称');
        return WechatService::paymentPrepare(null, $orderInfo['order_id'], $orderInfo['pay_price'], 'product', StoreOrder::getSubstrUTf8($site_name . ' - ' . $bodyContent, 30), '', 'MWEB', [], $tenant_id);
        //        return WechatService::paymentPrepare(null, $orderInfo['order_id'], 0.01, 'product', StoreOrder::getSubstrUTf8($site_name . ' - ' . $bodyContent, 30), '', 'MWEB',[],$tenant_id);
    }

    /**
     * 用户确认收货
     * @param $order
     * @param $uid
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function storeProductOrderUserTakeDelivery($order, $uid)
    {
        $res1 = StoreOrder::gainUserIntegral($order);
        $res2 = User::backOrderBrokerage($order);
        StoreOrder::orderTakeAfter($order);
        //满赠优惠券
        WechatUser::userTakeOrderGiveCoupon($uid, $order['pay_price']);
        if (!($res1 && $res2)) exception('收货失败!');
    }

    /**
     * 修改状态 为已收货  admin模块
     * @param $order
     * @throws \Exception
     */
    public static function storeProductOrderTakeDeliveryAdmin($order)
    {
        $res1 = AdminStoreOrder::gainUserIntegral($order);
        $res2 = User::backOrderBrokerage($order);
        AdminStoreOrder::orderTakeAfter($order);
        //满赠优惠券
        WechatUser::userTakeOrderGiveCoupon($order['uid'], $order['pay_price']);
        UserBill::where('uid', $order['uid'])->where('link_id', $order['id'])->where('type', 'pay_money')->update(['take' => 1]);
        if (!($res1 && $res2)) exception('收货失败!');
    }

    /**
     * 修改状态 为已收货  定时任务使用
     * @param $order
     * @throws \Exception
     */
    public static function storeProductOrderTakeDeliveryTimer($order)
    {
        $res1 = AdminStoreOrder::gainUserIntegral($order, false);
        $res2 = User::backOrderBrokerage($order, false);
        AdminStoreOrder::orderTakeAfter($order);
        UserBill::where('uid', $order['uid'])->where('link_id', $order['id'])->where('type', 'pay_money')->update(['take' => 1]);
        if (!($res1 && $res2)) exception('收货失败!');
    }


    /**
     * 修改状态为  已退款  admin模块
     * @param $data
     * @param $oid
     * @return bool|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function storeProductOrderRefundY($data, $oid)
    {
        $order = AdminStoreOrder::where('id', $oid)->find();
        if ($order['is_channel'] == 1)
            return AdminStoreOrder::refundRoutineTemplate($oid); //TODO 小程序余额退款模板消息
        else
            return AdminStoreOrder::refundTemplate($data, $oid); //TODO 公众号余额退款模板消息
    }


    /**
     * TODO  后台余额退款
     * @param $product
     * @param $refund_data
     * @throws \Exception
     */
    public static function storeOrderYueRefund($product, $refund_data)
    {
        $res = AdminStoreOrder::integralBack($product['id']);
        if (!$res) exception('退积分失败!');
    }

    /**
     * @Author  lingyun
     * @Desc    微信PC端扫码支付
     * @param $order_id
     * @param $uid
     * @param string $formId
     */
    public static function wxPcPay($orderId, $field = 'order_id')
    {
        if (is_string($orderId)) {
            $orderInfo = StoreOrder::where($field, $orderId)->find();
            $order_id = $orderId;
        } else {
            $orderInfo = $orderId;
            $order_id = $orderInfo['order_id'];
        }

        if (!$orderInfo || !isset($orderInfo['paid'])) exception('支付订单不存在!');
        if ($orderInfo['paid']) exception('支付已支付!');
        if ($orderInfo['pay_price'] <= 0) exception('该支付无需支付!');
        $bodyContent = StoreOrder::getProductTitle($orderInfo['cart_id']);
        $site_name = sys_config('site_name');

        if (!$bodyContent && !$site_name) exception('支付参数缺少：请前往后台设置->系统设置-> 填写 网站名称');

        (new StoreOrder())->where('id', $orderInfo['id'])->update(['pay_type' => 'weixin', 'is_channel' => 4]);

        return (new WxpayController())->wxpcpay($order_id, StoreOrder::getSubstrUTf8($site_name . ' - ' . $bodyContent, 30), $orderInfo['pay_price']);
        //        return (new WxpayController())->wxpcpay($order_id,StoreOrder::getSubstrUTf8($site_name . ' - ' . $bodyContent, 30),0.01);
    }


    /**
     * @Author  lingyun
     * @Desc    微信app支付
     * @param $order_id
     * @param $uid
     * @param string $formId
     */
    public static function wxAppPay($out_trade_no, $orderId, $pay_price)
    {

        // $bodyContent = StoreOrder::getProductTitle($orderInfo['cart_id']);
        // $site_name = sys_config('site_name');
        // if (!$bodyContent && !$site_name) exception('支付参数缺少：请前往后台设置->系统设置-> 填写 网站名称');

        (new StoreOrder())->whereIn('id', $orderId)->update(['pay_type' => 'weixin', 'is_channel' => 5]);

        return (new WxpayController())->wxapppay($out_trade_no, '五金商城订单', $pay_price);
    }


    /**
     * @Author  lingyun
     * @Desc    支付宝App支付
     */
    public static function aliAppPay($out_trade_no, $orderId, $pay_price)
    {
        // $site_name = sys_config('site_name');
        // if (!$bodyContent && !$site_name) exception('支付参数缺少：请前往后台设置->系统设置-> 填写 网站名称');
   
        (new StoreOrder())->whereIn('id', $orderId)->update(['pay_type' => 'zhifubao', 'is_channel' => 5]);
        return (new AlipayController())->aliapppay($out_trade_no, '五金商城订单', $pay_price);
    }
}
