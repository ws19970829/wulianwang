<?php


namespace app\admin\model\activity;
use crmeb\basic\BaseModel;
use app\admin\model\store\StoreProduct;

/**
 * 笔记商品关联白哦
 * Class StoreOrder
 * @package app\admin\model\store
 */
class ActivityCouponIssue extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'activity_coupon_issue';


    /**
     * 获取活动下的优惠券列表
     * @param $note_id
     * @return array
     */
    public function getCounponIssueListByActivityId($activity_id){
        $ids_arr=$this
            ->where('activity_id','=',$activity_id)
            ->with('withProduct')
            ->select();
        if(!count($ids_arr)){
            return [];
        }
        $ids_arr=$ids_arr->toArray();
        $data=[];
        $tem=[];
        foreach($ids_arr as $val){
            if(!isset($val['withProduct'])){
                continue;
            }
            $tem['id']=$val['withProduct']['id'];
            $tem['store_name']=$val['withProduct']['store_name'];
            $tem['image']=$val['withProduct']['image'];
            $tem['price']=$val['withProduct']['price'];
            $data[]=$tem;
        }
        return $data;
    }

    public function withCouponIssue(){
        return $this->belongsTo('app\admin\model\ump\StoreCouponIssue','coupon_issue_id','id');
    }


    public static function getCouponListByActivityIDtoAPI($activity_id){
        $data=self::where('activity_id','=',$activity_id)->with(['withCouponIssue','withCouponIssue.withCoupon'])->select();

        if(!count($data)){
            return [];
        }

        $data = $data->toArray();
        $return =[];
        foreach($data as $val){
            if(!$val['withCouponIssue']){
                continue;
            }

            if(!$val['withCouponIssue']['withCoupon']){
                continue;
            }

            $temp=[
                'title'=>$val['withCouponIssue']['withCoupon']['title'],
                'coupon_price'=>$val['withCouponIssue']['withCoupon']['coupon_price'],
                'use_min_price'=>$val['withCouponIssue']['withCoupon']['use_min_price'],
                'coupon_time'=>$val['withCouponIssue']['withCoupon']['coupon_time'],
                'coupon_issue_id'=>$val['coupon_issue_id'],
                'coupon_id'=>$val['withCouponIssue']['cid'],
            ];
            $return[]=$temp;
        }

        return $return;
    }
}