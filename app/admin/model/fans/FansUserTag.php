<?php


namespace app\admin\model\fans;
use crmeb\basic\BaseModel;
use app\admin\model\store\StoreProduct;

/**
 * 笔记商品关联白哦
 * Class StoreOrder
 * @package app\admin\model\store
 */
class FansUserTag extends BaseModel
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
    protected $name = 'fans_user_tag';


    /**
     * 获取笔记下的商品列表
     * @param $note_id
     * @return array
     */
    public function getCounponIssueListByNoteId($note_id){
        $ids_arr=$this
            ->where('note_id','=',$note_id)
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