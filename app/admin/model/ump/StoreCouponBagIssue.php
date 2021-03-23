<?php


namespace app\admin\model\ump;
use app\admin\model\store\StoreCategory;
use app\admin\model\user\User;
use crmeb\basic\BaseModel;
use app\admin\model\store\StoreProduct;

/**
 * 优惠券礼包关联优惠券
 * Class StoreOrder
 * @package app\admin\model\store
 */
class StoreCouponBagIssue extends BaseModel
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
    protected $name = 'store_coupon_bag_issue';


    /**
     * 获取礼包下的优惠券列表
     * @param $bag_id
     * @return array
     */
    public function getCounponIssueListByBagId($bag_id){
        $ids_arr=$this
            ->where('coupon_bag_id','=',$bag_id)
            ->with('withCouponIssue')
            ->select();
        if(!count($ids_arr)){
            return [];
        }
        $ids_arr=$ids_arr->toArray();
        $data=[];
        $tem=[];
        foreach($ids_arr as $val){
            if(!isset($val['withCouponIssue'])){
                continue;
            }
            $tem['id']=$val['withCouponIssue']['id'];
            $tem['store_name']=$val['withCouponIssue']['store_name'];
            $tem['image']=$val['withCouponIssue']['image'];
            $tem['price']=$val['withCouponIssue']['price'];
            $data[]=$tem;
        }
        return $data;
    }

    public function withCouponIssue(){
        return $this->belongsTo('app\admin\model\store\StoreProduct','coupon_issue_id','id');
    }


    /**
     * 获取礼包下的优惠券列表
     * @param $uid
     * @param $limit
     * @param int $page
     * @param int $type
     * @param int $product_id
     * @param int $tenant_id
     * @return array
     */
    public static function getIssueCouponList($uid, $limit, $page = 0, $type = 0, $product_id = 0,$tenant_id=0 ,$coupon_bag_id=0)
    {
        if(!$tenant_id){
            $tenant_id=(new User())->where('uid','=',$uid)->value('tenant_id');
        }

        $issue_ids=StoreCouponBagIssue::where('coupon_bag_id','=',$coupon_bag_id)->field('coupon_issue_id')->select();
        if($issue_ids){
            $issue_ids=$issue_ids->column('coupon_issue_id');
            $issue_ids=implode(',',$issue_ids);
        }else{
            $issue_ids='';
        }


        $model1 = \app\models\store\StoreCouponIssue::validWhere('A')->alias('A')
            ->join('store_coupon B', 'A.cid = B.id')
//            ->where('A.tenant_id','=',$tenant_id)
//            ->where('B.tenant_id','=',$tenant_id)
            ->where('A.id','in',$issue_ids)
            ->field('A.*,B.type,B.coupon_price,B.use_min_price,B.title')
            ->order('B.sort DESC,A.id DESC');
        $model2 = \app\models\store\StoreCouponIssue::validWhere('A')->alias('A')
            ->join('store_coupon B', 'A.cid = B.id')
            ->where('A.id','in',$issue_ids)
//            ->where('A.tenant_id','=',$tenant_id)
//            ->where('B.tenant_id','=',$tenant_id)
            ->field('A.*,B.type,B.coupon_price,B.use_min_price,B.title')
            ->order('B.sort DESC,A.id DESC');
        $model3 = \app\models\store\StoreCouponIssue::validWhere('A')->alias('A')
            ->join('store_coupon B', 'A.cid = B.id')
            ->where('A.id','in',$issue_ids)
//            ->where('A.tenant_id','=',$tenant_id)
//            ->where('B.tenant_id','=',$tenant_id)
            ->field('A.*,B.type,B.coupon_price,B.use_min_price,B.title')
            ->order('B.sort DESC,A.id DESC');

        if ($uid) {
            $model1->with(['used' => function ($query) use ($uid) {
                $query->where('uid', $uid);
            }]);

            $model2->with(['used' => function ($query) use ($uid) {
                $query->where('uid', $uid);
            }]);

            $model3->with(['used' => function ($query) use ($uid) {
                $query->where('uid', $uid);
            }]);
        }

        $lst1 = $lst2 = $lst3 = [];
        if ($type) {
            if ($product_id) {
                //商品券
                $lst1 = $model1->where('B.type', 2)
                    ->where('is_give_subscribe', 0)
                    ->where('is_full_give', 0)
                    ->whereFindinSet('B.product_id', $product_id)
                    ->select()
                    ->hidden(['is_del', 'status'])
                    ->toArray();
                //品类券
                $cate_id = StoreProduct::where('id', $product_id)->value('cate_id');
                $category = explode(',', $cate_id);
                foreach ($category as $value) {
                    $temp[] = StoreCategory::where('id', $value)->value('pid');
                }
                $temp = array_unique($temp);
                $cate_id = $cate_id . ',' . implode(',', $temp);

                $lst2 = $model2->where('B.type', 1)
                    ->where('is_give_subscribe', 0)
                    ->where('is_full_give', 0)
                    ->where('B.category_id', 'in', $cate_id)
                    ->select()
                    ->hidden(['is_del', 'status'])
                    ->toArray();
            }
        } else {
            //平台券
            $lst3 = $model3
//                ->where('B.type', 0)
                ->where('is_give_subscribe', 0)
                ->where('is_full_give', 0)
                ->select()
                ->hidden(['is_del', 'status'])
                ->toArray();
        }
        $list = array_merge($lst1, $lst2, $lst3);
        $list = array_unique_fb($list);
        if ($page) $list = array_slice($list, ((int)$page - 1) * $limit, $limit);

        //        add_time: "2020/09/19"
//applicable_type: 0
//cid: 2
//coupon_price: "5.00"
//coupon_title: "平台券1"
//end_time: "2020/09/29"
//id: 47
//is_fail: 0
//status: 0
//tenant_id: 18
//type: "get"
//use_min_price: "10.00"
//use_time: 0
//_add_time: "2020/09/19"
//_end_time: "2020/09/29"
//_msg: "可使用"
//_type: 1


        //todo 礼包列表获取的数据 和普通列表返回数据内容不一样。先临时处理一下
        foreach ($list as $k => $v) {
            $v['is_use'] = $uid ? isset($v['used']) : false;
            if (!$v['end_time']) {
                $v['start_time'] = '';
                $v['end_time'] = '不限时';
            } else {
                $v['start_time'] = date('Y/m/d', $v['start_time']);
                $v['end_time'] = $v['end_time'] ? date('Y/m/d', $v['end_time']) : date('Y/m/d', time() + 86400);
            }


            if (!$v['end_time']) {
                $v['_add_time'] = '';
                $v['_end_time'] = '不限时';
            } else {
                $v['_add_time'] = $v['start_time'];
                $v['_end_time'] = $v['end_time'];
            }

            $v['coupon_title'] = $v['title'];
            $v['is_fail'] =0;
            $v['status'] =0;
            $v['use_time'] =0;
            $v['_msg'] ='可领取';
            $v['_type'] =1;




            $list[$k] = $v;



        }

        if ($list)
            return $list;
        else
            return [];
    }

}