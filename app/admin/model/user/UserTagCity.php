<?php

namespace app\admin\model\user;

use app\admin\model\system\SystemCity;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;


/**
 * 用户标签关联地区 model
 * Class User
 * @package app\admin\model\user
 */
class UserTagCity extends BaseModel
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
    protected $name = 'user_tag_city';


    /**
     * 获取笔记下的商品列表
     * @param $tag_id
     * @return array
     */
    public function getCityListByTagId($tag_id){
        $ids_arr=$this
            ->where('user_tag_id','=',$tag_id)
            ->with('withSystemCity')
            ->select();
        if(!count($ids_arr)){
            return [];
        }
        $ids_arr=$ids_arr->toArray();
        $data=[];
        $tem=[];
        $systemCityModel=(new SystemCity());

        foreach($ids_arr as $val){
            if(!isset($val['withSystemCity'])){
                continue;
            }
            $tem['id']=$val['withSystemCity']['id'];
            $tem['province_name']=$systemCityModel->where('city_id','=',$val['withSystemCity']['parent_id'])->value('name');
            $tem['province_id']=$val['withSystemCity']['parent_id'];
            $tem['name']=$val['withSystemCity']['name'];
            $tem['merger_name']=$val['withSystemCity']['merger_name'];
            $tem['area_code']=$val['withSystemCity']['area_code'];
            $tem['parent_id']=$val['withSystemCity']['parent_id'];
            $data[]=$tem;
        }

        return $data;
    }

    public function withSystemCity(){
        return $this->belongsTo('app\admin\model\system\SystemCity','system_city_id','id');
    }

}