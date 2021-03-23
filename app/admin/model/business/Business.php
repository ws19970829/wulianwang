<?php


namespace app\admin\model\business;
use app\admin\model\store\StoreProduct;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;


/**
 * 商家管理Model
 * Class StoreOrder
 * @package app\admin\model\store
 */
class Business extends BaseModel
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
    protected $name = 'business';

    use ModelTrait;

    protected function getAddTimeAttr($val){
        return $val?date('Y-m-d',$val):'';
    }

    public static function getBusinessList($where){
        $data = ($data = self::systemPage($where,true)->page((int)$where['page'], (int)$where['limit'])->select()) && count($data) ? $data->toArray() : [];
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

        if(isset($where['status'])){
            $model=$model->where('status','=',$where['status']);

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


    /**
     *
     * @param $id
     * @param int $is_admin_view 是否是总后台预览访问 0否
     * @return array|null|\think\Model
     */
    public function getOne($id,$is_admin_view=0){
        $info=$this
            ->where('id','=',$id)
            ->find();
        if($info){
            $info=$info->toArray();
            $info['product_list']=[];
            $product_ids_list=(new BusinessProduct())->where('business_id','=',$id)->select();
            if($product_ids_list){
                $product_ids_arr=$product_ids_list->column('product_id');
                $product_ids_str=implode($product_ids_arr,',');

                $product_list=StoreProduct::where('id','in',$product_ids_str)
                    ->where('tenant_id','=',$info['tenant_id'])
                    ->where('is_del','=',0)//未删除
                    ->where('is_show','=',1)//上架
                    ->select();
                if(count($product_list)){
                    //网站地址
                    $site_url=config('site.default_site_url');
                    foreach($product_list as $key=>$val){
                        $product_list[$key]['url']=$site_url.'/detail/'.$val['id'].'?business_id='.$id;
                    }
                    $info['product_list']=$product_list->toArray();
                }
            }

            //如果是总后台的预览，则不做阅读量处理
            if(!$is_admin_view){
                //本篇合作商家的阅读量增1
                $this->where('id','=',$id)->inc('view_num')->update();
            }

        }
        return $info;
    }


    /**
     * 产品分类隐藏显示
     * @param $id
     * @param $show
     * @return bool
     */
    public static function setShow($id, $show)
    {
        $res = self::where('id', $id)->update(['status' => $show]);
        return $res;
    }



}