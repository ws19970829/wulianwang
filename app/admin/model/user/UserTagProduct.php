<?php

namespace app\admin\model\user;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;


/**
 * 用户标签关联商品 model
 * Class User
 * @package app\admin\model\user
 */
class UserTagProduct extends BaseModel
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
    protected $name = 'user_tag_product';


    /**
     * 获取笔记下的商品列表
     * @param $tag_id
     * @return array
     */
    public function getProductListByTagId($tag_id){
        $ids_arr=$this
            ->where('user_tag_id','=',$tag_id)
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