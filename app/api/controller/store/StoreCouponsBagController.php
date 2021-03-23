<?php

namespace app\api\controller\store;


use app\admin\model\ump\StoreCouponBag;
use app\admin\model\ump\StoreCouponBagIssue;
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
class StoreCouponsBagController
{

    public function index(Request $request){
        $data = UtilService::getMore([
            ['type',''],
            ['page', 0],
            ['is_public', 1],
            ['limit', 0],
            ['status',1],//前台只看有效的
            ['title','']
        ], $request);
        $tenant_id=input('param.tenant_id',18);
        $uid=$request->uid();

        $return = StoreCouponBag::systemPageToApi($data,$tenant_id,$data['page'],$data['limit'],$uid);
//        $return = StoreCouponIssue::getIssueCouponList($request->uid(), $data['limit'], $data['page'],$data['type'],$data['product_id'],$data['tenant_id']);
        return app('json')->successful($return);
    }


    //用户优惠券礼包列表
    public function lst(Request $request){

        //可领取
        //先获取用户已经领取的
        $user_issue_ids=StoreCouponIssueUser::where('uid','=',$request->uid())->select();
        if($user_issue_ids){
            $user_issue_ids=$user_issue_ids->column('issue_coupon_id');
        }else{
            $user_issue_ids=[];
        }
//            $list = StoreCouponUser::getUserAllCoupon($request->uid());
        $data = UtilService::getMore([
            ['type',0],
            ['page', 0],
            ['limit', 0],
            ['product_id',0],
            ['tenant_id',0],
            ['is_public',1],
            ['bag_id',0],
        ], $request);
        $data['uid']=$request->uid();
//            $list = StoreCouponIssue::getIssueCouponList($request->uid(), $data['limit'], $data['page'],$data['type'],$data['product_id'],$data['tenant_id'],$data['is_public']);
        $list = StoreCouponBagIssue::getIssueCouponList($data['uid'], $data['limit'], $data['page'],$data['type'],$data['product_id'],$data['tenant_id'],$data['bag_id']);

        if($list){
            $tenant_id=User::getTenantIDbyUID($request->uid());
            //从所有优惠券中，去除用户已领取的
            $return=[];
            if($user_issue_ids){
                foreach($list as $key=>$value){
                    if(in_array($value['id'],$user_issue_ids)){
                        continue;
                    }
                    $temp=[
                        'id'=>$value['id'],
                        'cid'=>$value['cid'],
                        'coupon_title'=>$value['title'],
                        'coupon_price'=>$value['coupon_price'],
                        'use_min_price'=>$value['use_min_price'],
                        'add_time'=>'',
                        'end_time'=>'',
                        'use_time'=>0,
                        'type'=>'get',
                        'status'=>0,
                        'is_fail'=>0,
                        'tenant_id'=>$tenant_id,
                        '_add_time'=>'',
                        '_end_time'=>'',
                        '_type'=>$value['type'],
                        '_msg'=>'可领取',
                        'applicable_type'=>0,
                    ];

                    $return[]=$temp;
                }
            }

            $list=$return;
        }else{
            $list=[];
        }

        foreach ($list as &$v) {
            $v['add_time'] = $v['add_time']?date('Y/m/d', $v['add_time']):'';
            $v['end_time'] = $v['end_time']?date('Y/m/d', $v['end_time']):'';
        }
        return app('json')->successful($list);
    }

    /**
     * 可领取优惠券列表
     * @param Request $request
     * @return mixed
     */
    public function lst_bak(Request $request)
    {
        $data = UtilService::getMore([
            ['type',0],
            ['page', 0],
            ['limit', 0],
            ['product_id',0],
            ['bag_id',0],
            ['tenant_id',0],
            ['bag_id',0],//临时
            ['uid',0]
        ], $request);

        $return = StoreCouponBagIssue::getIssueCouponList($data['uid'], $data['limit'], $data['page'],$data['type'],$data['product_id'],$data['tenant_id'],$data['bag_id']);
//        $return = StoreCouponIssue::getIssueCouponList($request->uid(), $data['limit'], $data['page'],$data['type'],$data['product_id'],$data['tenant_id']);
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
     * 用户已领取优惠券
     * @param Request $request
     * @param $types
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function user(Request $request, $types)
    {
        switch ($types) {
            case 0:
            case '':
                $list = StoreCouponUser::getUserAllCoupon($request->uid());
                break;
            case 1:
                $list = StoreCouponUser::getUserValidCoupon($request->uid());
                break;
            case 2:
                $list = StoreCouponUser::getUserAlreadyUsedCoupon($request->uid());
                break;
            default:
                $list = StoreCouponUser::getUserBeOverdueCoupon($request->uid());
                break;
        }
        foreach ($list as &$v) {
            $v['add_time'] = date('Y/m/d', $v['add_time']);
            $v['end_time'] = date('Y/m/d', $v['end_time']);
        }
        return app('json')->successful($list);
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