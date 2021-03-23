<?php

namespace app\api\controller\business;

use app\admin\model\activity\Activity;
use app\admin\model\activity\ActivityProduct;
use app\admin\model\business\Business;
use app\admin\model\business\BusinessProduct;
use app\admin\model\store\StoreDescription;
use app\admin\model\store\StoreProductAttrValue;
use app\models\store\StoreProduct;
use app\models\store\StoreProductAttr;
use app\models\store\StoreProductRelation;
use app\models\store\StoreProductReply;
use app\models\store\StoreSeckill;
use app\models\store\StoreVisit;
use app\models\system\UserProductLog;
use app\Request;
use crmeb\services\GroupDataService;
use crmeb\services\QrcodeService;
use crmeb\services\UtilService;

/**
 * 商家
 * Class StoreSeckillController
 * @package app\api\controller\activity
 */
class BusinessController
{

    /**
     * 获取活动下的商品列表
     * @return mixed
     */
    public function business_list(){
        $activity_id=input('param.activity_id');
        if(!$activity_id){
            return app('json')->fail('活动ID有误!');
        }

        $list=(new ActivityProduct())->getProductListByActivityIDtoAPI($activity_id);
        if(!count($list)){
            return app('json')->fail('活动不存在或已下架!');
        }

        return app('json')->successful($list);
    }


    /**
     * 商家详情
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function detail(Request $request, $id)
    {

        if (!$id) return app('json')->fail('商家不存在或已下架!');

        $business_info=Business::where('id','=',$id)->find();

        if (!$business_info) return app('json')->fail('商家不存在或已下架!');

        $business_info=$business_info->toArray();
        $product_list=(new BusinessProduct())->getProductListByBusinessId($id);
        $business_info['product_list']=$product_list;

        return app('json')->successful($business_info);
    }
}