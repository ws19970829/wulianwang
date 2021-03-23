<?php

namespace app\api\controller\store;


use app\admin\model\ump\StoreCouponBag;
use app\admin\model\ump\StoreCouponBagIssue;
use app\models\store\StoreCouponBagUser;
use app\models\store\StoreCouponIssue;
use app\models\store\StoreCouponIssueUser;
use app\models\user\User;
use app\Request;
use crmeb\services\UtilService;
use app\models\store\StoreCouponUser;

/**
 * 优惠券类
 * Class StoreCouponsController
 * @package app\api\controller\store
 */
class StoreCouponsController
{
    /**
     * 可领取优惠券列表
     * @param Request $request
     * @return mixed
     */
    public function lst(Request $request)
    {
        $data = UtilService::getMore([
            ['type', 0],
            ['page', 1],
            ['limit', 10],
            ['product_id', 0],
            ['tenant_id', 0],
            ['is_public', 1]
        ], $request);
        $return = StoreCouponIssue::getIssueCouponList($request->uid(), $data['limit'], $data['page'], $data['type'], $data['product_id'], $data['tenant_id'], $data['is_public']);
        return app('json')->successful($return);
    }

    /**
     * 领取优惠券
     *
     * @param Request $request
     * @return mixed
     */
    public function receive(Request $request)
    {
        list($couponId) = UtilService::getMore([['couponId', 0]], $request, true);
        if (!$couponId || !is_numeric($couponId)) return app('json')->fail('参数错误!');
        if (StoreCouponIssue::issueUserCoupon($couponId, $request->uid())) {
            return app('json')->successful('领取成功');
        } else {
            return app('json')->fail(StoreCouponIssue::getErrorInfo('领取失败!'));
        }
    }


    /**
     * 用户优惠券
     * @param Request $request
     * @param $types
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function user(Request $request)
    {

        $param = $request->param();

        $list = array();
        $data = UtilService::getMore([
            ['type', 0],
            ['page', 1],
            ['limit', 10],
            ['product_id', 0],
            ['tenant_id', 0],
            ['is_public', 1],
            ['is_bag', 0]
        ], $request);

        $couponList = StoreCouponIssue::getIssueCouponList1($request->uid(), $data['limit'], $data['page'], $data['type'], $data['product_id'], $data['tenant_id'], $data['is_public'], $data['is_bag'], 2);
        $list[] = [
            'title' => '可领取的优惠券',
            'data' => $couponList,
            'is_mine' => 0,
        ];
        $couponList = StoreCouponIssue::getIssueCouponList($request->uid(), $data['limit'], $data['page'], $data['type'], $data['product_id'], $data['tenant_id'], $data['is_public'], $data['is_bag'], 1);
        $list[] = [
            'title' => '已领取的优惠券',
            'data' => $couponList,
            'is_mine' => 1,
        ];
        return app('json')->successful(compact('list'));
    }



    /**
     * 批量领取优惠券
     * @param Request $request
     * @return mixed
     */
    public function receive_batch(Request $request)
    {
        list($couponIds) = UtilService::postMore([
            ['couponId', []],
        ], $request, true);
        if (!count($couponIds)) return app('json')->fail('参数错误');
        $couponIdsError = [];
        $count = 0;
        $msg = '';
        foreach ($couponIds as $key => &$item) {
            if (!StoreCouponIssue::issueUserCoupon($item, $request->uid())) {
                $couponIdsError[$count]['id'] = $item;
                $couponIdsError[$count]['msg'] = StoreCouponIssue::getErrorInfo('领取失败');
            } else {
                $couponIdsError[$count]['id'] = $item;
                $couponIdsError[$count]['msg'] = '领取成功';
            }
            $count++;
        }
        foreach ($couponIdsError as $key => &$value) {
            $msg = $msg . StoreCouponIssue::getIssueCouponTitle($value['id']) . ',' . $value['msg'];
        }
        return app('json')->fail($msg);
    }

    /**
     * 优惠券 订单获取
     * @param Request $request
     * @param $price
     * @return mixed
     */
    public function order(Request $request, $cartId, $price)
    {
        return app('json')->successful(StoreCouponUser::beUsableCouponList($request->uid(), $cartId, $price));
    }
}
