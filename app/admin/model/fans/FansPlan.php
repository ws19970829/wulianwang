<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\fans;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;


/**
 * 营销计划Model
 * Class StoreOrder
 * @package app\admin\model\store
 */
class FansPlan extends BaseModel
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
    protected $name = 'fans_plan';

    use ModelTrait;

    protected function getAddTimeAttr($val){
        return $val?date('Y-m-d',$val):'';
    }


    public function getStartTimeTextAttr($val,$data){
        if($data['start_time']){
            return date('Y-m-d',$data['start_time']);
        }
        return '';
    }

    public function getEndTimeTextAttr($val,$data){
        if($data['end_time']){
            return date('Y-m-d',$data['end_time']);
        }
        return '';
    }

    public function getIsLongTextAttr($val,$data){
        return $data['is_long']?'自动长期计划':'手动区间计划';
    }

    public function getTypeTextAttr($val,$data){
        switch ($data['type']){
            case 1:
                return '互动粉丝转化';
                break;
            case 2:
                return '自定义人群';
                break;
            case 3:
                return '未消费新增人群';
                break;
            default:
                return '未知计划类型';
        }
    }

    public function getStatusTextAttr($val,$data){
        return $data['status']?'进行中':'已终止';
    }


    public static function getPlanList($where){
        $data = self::systemPage($where,true)->page((int)$where['page'], (int)$where['limit'])->select();
        if(!count($data)){
            $data=[];
        }else{
            $data->append(['start_time_text','end_time_text','is_long_text','type_text','status_text']);
            $data=$data->toArray();
        }
//        $data = ($data = self::systemPage($where,true)->page((int)$where['page'], (int)$where['limit'])->select()) && count($data) ? $data->toArray() : [];
        $count = self::systemPage($where,true)->count();
        return compact('count', 'data');
    }

    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where, $isAjax = false)
    {
        $model = new self;
        $model=$model->where('tenant_id','=',session('tenant_id'));
        $model=$model->where('is_del','=',0);

        if($where['title']){
            $title=$where['title'];
            $model=$model->where('title','like',"%$title%");
        }

        if($where['type']){
            $model=$model->where('type','=',$where['type']);
        }

        if ($isAjax === true) {
            if (isset($where['order']) && $where['order'] != '') {
                $model = $model->order(self::setOrder($where['order']));
            } else {
                $model = $model->order('sort desc,id desc');
            }
            return $model;
        }
        return self::page($model, function ($item) {

        }, $where);
    }


    public function withNote(){
        return $this->belongsTo('FansNote','note_id','id');
    }



}