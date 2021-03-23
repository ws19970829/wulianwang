<?php


namespace app\admin\model\business;
use crmeb\basic\BaseModel;

/**
 * 合作商家商品关联白哦
 * Class StoreOrder
 * @package app\admin\model\store
 */
class BusinessProduct extends BaseModel
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
    protected $name = 'business_product';


    /**
     * 获取合作商家下的商品列表
     * @param $business_id
     * @return array
     */
    public function getProductListByBusinessId($business_id){
        $ids_arr=$this
            ->where('business_id','=',$business_id)
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

    public function withProduct(){
        return $this->belongsTo('app\admin\model\store\StoreProduct','product_id','id');
    }
}