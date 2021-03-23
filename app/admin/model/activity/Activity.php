<?php


namespace app\admin\model\activity;
use app\admin\model\store\StoreProduct;
use app\api\controller\PublicController;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;


/**
 * 活动管理Model
 * Class StoreOrder
 * @package app\admin\model\store
 */
class Activity extends BaseModel
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
    protected $name = 'activity';

    use ModelTrait;

    protected function getAddTimeAttr($val){
        return $val?date('Y-m-d',$val):'';
    }

    protected function getStartTimeTextAttr($val,$data){
        return $data['start_time']?date('Y-m-d',$data['start_time']):'';
    }

    protected function getEndTimeTextAttr($val,$data){
        return $data['end_time']?date('Y-m-d',$data['end_time']):'';
    }


    /**
     * 获取资源列表
     * @param $where
     * @return array
     */
    public static function getList($where){
        $data=$data = self::systemPage($where,true)
            ->page((int)$where['page'], (int)$where['limit'])
            ->select();
        if(($data) && count($data)){
            $data=$data->append(['start_time_text','end_time_text']);
            $data=$data->toArray();
        }else{
            $data=[];
        }
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
            $type=$where['type'];
            $model=$model->where('type','=',$type);
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
            $product_ids_list=(new FansProduct())->where('note_id','=',$id)->select();
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
                        $product_list[$key]['url']=$site_url.'/detail/'.$val['id'].'?note_id='.$id;
                    }
                    $info['product_list']=$product_list->toArray();
                }
            }

            //如果是总后台的预览，则不做阅读量处理
            if(!$is_admin_view){
                //本篇笔记的阅读量增1
                $this->where('id','=',$id)->inc('view_num')->update();
            }

        }
        return $info;
    }


    /**
     * 检查活动状态
     * @param $activity_id
     * @return array
     */
    public static function checkStatus($activity_id)
    {

        $activity_info=self::where('id','=',$activity_id)->field('start_time,end_time,type')->find();
        $value=[];
        $value['time'] = date('m/d H:i:s',$activity_info['start_time']);
        $value['stop'] = $activity_info['end_time'];
        $value['type'] = $activity_info['type'];
        $value['id'] = 120;
        $value['continued'] = ceil(($activity_info['end_time']-$activity_info['start_time'])/3600);

        if(time()<$activity_info['start_time']){
            $value['state'] = '即将开始';
            $value['status'] = 2;//2即将开始，1抢购中，0已结束
        }



        if($activity_info['start_time']<=time() && time()<=$activity_info['end_time']){
            $value['state'] = '抢购中';
            $value['status'] = 1;//2即将开始，1抢购中，0已结束
        }


        if(time()>$activity_info['end_time']){
            $value['state'] = '已结束';
            $value['status'] = 0;//2即将开始，1抢购中，0已结束
        }

        return $value;
    }


    /**
     * 获取活动商品的限购次数
     * @param $activity_id
     * @return int|mixed
     */
    public static function getBuyLimitByActivityID($activity_id){
        $activity_info=Activity::where('id','=',$activity_id)->field('id,limit_goods_num,limit_buy_type')->find();
        if(!$activity_info){
            return 0;
        }


        if($activity_info['limit_buy_type']==1){
            //限购
            return $activity_info['limit_goods_num'];
        }

        return 0;
    }



    public static function getIndexSeckill($tenant_id=0,$type=5,$page=1,$limit=3){
        $where=[
            'tenant_id'=>$tenant_id,
            'is_del'=>0,
            'status'=>1,
            'type'=>$type,
        ];
        $activity_info=self::where($where)
            ->field('id,title,image,type,sec_title,end_time')
            ->where('end_time','>',time())
            ->order('end_time','asc')
            ->find();

        if(!$activity_info){
            return [];
        }

        $activity_info=$activity_info->toArray();


        $product_list=ActivityProduct::alias('n')
            ->field('n.id,n.activity_id,n.product_id,n.image,n.title,n.price,n.ot_price,n.stop_time')
            ->join('store_product c', 'c.id=n.product_id')
            ->join('activity a', 'a.id=n.activity_id')
            ->where('n.type',$type)
            ->where('c.tenant_id',$tenant_id)
            ->where('c.is_show', 1)
            ->where('c.is_del', 0)
            ->where('n.is_del', 0)
            ->where('a.is_del', 0)
            ->where('n.status', 1)
            ->page($page,$limit)
            ->order('n.stop_time', 'desc')
            ->select();

        if($product_list){
            $product_list=$product_list->append(['stop_time_text']);
            $product_list=$product_list->toArray();
            foreach($product_list as $key => $val){

                list($productAttr, $productValue) = \app\models\store\StoreProductAttr::getProductAttrDetailToActivityToApi($val['product_id'], 0, $activity_info['type'],0, $activity_info['id']);
                $first_attr=current($productValue);
                $product_list[$key]['price']=$first_attr['activity_price']>0?$first_attr['activity_price']:0;
            }

            $activity_info['product_list']=$product_list;

            return $activity_info;
        }else{
            return [];
        }

    }
}