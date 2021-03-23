<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\ump;

use app\models\store\StoreCouponBagUser;
use crmeb\services\FormBuilder as Form;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use think\facade\Route as Url;
use app\admin\model\store\StoreCategory as CategoryModel;


/**
 * Class StoreCategory
 * @package app\admin\model\store
 */
class StoreCouponBag extends BaseModel
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
    protected $name = 'store_coupon_bag';

    use ModelTrait;

    public function getCouponIssueCountAttr($val,$data){
        return StoreCouponBagIssue::where('coupon_bag_id','=',$data['id'])->count();
    }


    /**
     * 获取优惠券礼包列表
     * @param $where
     * @param int $tenant_id
     * @return array
     */
    public static function systemPage($where,$tenant_id=0)
    {
        $model = new self;
        if ($where['status'] != '') $model = $model->where('status', $where['status']);
        if ($where['type'] != '') $model = $model->where('type', $where['type']);
        if ($where['title'] != '') $model = $model->where('title', 'LIKE', "%$where[title]%");
//        if($where['is_del'] != '')  $model = $model->where('is_del',$where['is_del']);
        $tenant_id=$tenant_id?$tenant_id:session('tenant_id');
        $tenant_id=$tenant_id?$tenant_id:0;
        $model=$model->where('tenant_id','=',$tenant_id);
        $model = $model->where('is_del', 0);
        $model = $model->order('id desc');
        $model = $model->append(['coupon_issue_count']);
        return self::page($model, $where);
    }


    /**
     * 获取优惠券礼包列表
     * @param $where
     * @param int $tenant_id
     * @param int $page
     * @param int $limit
     * @param int $uid
     * @return array
     */
    public static function systemPageToApi($where,$tenant_id=0,$page=1,$limit=20,$uid=0)
    {
        $model = new self;
        if ($where['status'] != '') $model = $model->where('status', $where['status']);
        if ($where['type'] != '') $model = $model->where('type', $where['type']);
        if ($where['is_public'] != '') $model = $model->where('is_public', $where['is_public']);
        if ($where['title'] != '') $model = $model->where('title', 'LIKE', "%$where[title]%");


//        if($where['is_del'] != '')  $model = $model->where('is_del',$where['is_del']);
        $tenant_id=$tenant_id?$tenant_id:session('tenant_id');
        $model=$model
            ->field('id,title,type,total_count,remain_count,is_public')
            ->where('tenant_id','=',$tenant_id)
            ->where('is_del', 0)
            ->order('id desc');

        $count=$model->count();
        if($count){
            $data = $model
                ->page($page,$limit)
                ->append(['coupon_issue_count','is_received'])
                ->select()
                ->toArray();
        }else{
            $data=[];
        }

        if($uid){
            $bag_ids=StoreCouponBagUser::where('uid','=',$uid)->select();
            if($bag_ids){
                $bag_ids=$bag_ids->column('coupon_bag_id');
                foreach($data as $key=>$val){
                    if(in_array($val['id'],$bag_ids)){
                        $data[$key]['is_received']=1;
                    }
                }
            }
        }
        //获取用户领取过的是

        return $data;

    }

    /**
     * @param $where
     * @return array
     */
    public static function systemPageCoupon($where)
    {
        $model = new self;
        if ($where['status'] != '') $model = $model->where('status', $where['status']);
        if ($where['title'] != '') $model = $model->where('title', 'LIKE', "%$where[title]%");
//        if($where['is_del'] != '')  $model = $model->where('is_del',$where['is_del']);
        $model = $model->where('is_del', 0);
        $model = $model->where('status', 1);
        $model = $model->order('sort desc,id desc');
        return self::page($model, $where);
    }

    public static function editIsDel($id)
    {
        $data['status'] = 0;
        self::beginTrans();
        $res1 = self::edit($data, $id);
        $res2 = false !== StoreCouponUser::where('cid', $id)->update(['is_fail' => 1]);
        $res3 = false !== StoreCouponIssue::where('cid', $id)->update(['status' => -1]);
        $res = $res1 && $res2 && $res3;
        self::checkTrans($res);
        return $res;

    }

    /**
     * 品类券
     * @param $tab_id
     * @return array
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public static function createClassRule($tab_id)
    {
        $formbuider = [];
        $formbuider[] = Form::select('category_id', '选择品类')->setOptions(function () {
            $list = CategoryModel::getTierList(null, 1);
            $menus = [];
            foreach ($list as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['html'] . $menu['cate_name']];
            }
            return $menus;
        })->filterable(1)->col(12);
        return $formbuider;
    }

    /**
     * 商品券
     * @param $tab_id
     * @return array
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public static function createProductRule($tab_id)
    {
        $formbuider = [];
        $formbuider[] = Form::frameImages('image', '商品', Url::buildUrl('admin/ump.StoreCoupon/select', array('fodder' => 'image')))->icon('plus')->width('100%')->height('500px');
        $formbuider[] = Form::hidden('product_id', 0);
        return $formbuider;
    }
}