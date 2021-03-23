<?php


namespace app\admin\model\activity;
use app\admin\model\store\StoreProductAttr;
use app\admin\model\store\StoreProductAttrResult;
use crmeb\basic\BaseModel;
use app\admin\model\store\StoreProduct;

/**
 * 活动关联商品表
 * Class StoreOrder
 * @package app\admin\model\store
 */
class ActivityProductGift extends BaseModel
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
    protected $name = 'activity_product_gift';


    protected function getImagesAttr($value)
    {
        return json_decode($value, true) ?: [];
    }

    public function getDescriptionAttr($value)
    {
        return htmlspecialchars_decode($value);
    }

    public function getStopTimeTextAttr($val,$data){
        return $data['stop_time']>time()?date('Y-m-d H:i:s',$data['stop_time']):'已结束';
    }



    /**
     * 获取活动中关联的商品列表
     * @param $activity_id
     * @return array
     */
    public function getProductListByActivityId($activity_id){
        $ids_arr=$this
            ->where('activity_id','=',$activity_id)
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
            //商品基础数据
            $tem['id']=$product_id=$val['withProduct']['id'];
            $tem['store_name']=$val['withProduct']['store_name'];
            $tem['image']=$val['withProduct']['image'];
            $tem['price']=$val['withProduct']['price'];
            $tem['stock']=$val['withProduct']['stock'];


            $data[]=$tem;
        }
        return $data;
    }



    /**
     * 获取活动中关联的商品列表
     * @param $activity_id int 活动id
     * @param $type int 4限时折扣，5限时秒杀，6支付有礼
     * @return array
     */
    public function getProductListAndAttrByActivityId($activity_id,$type=0){

        $ids_arr=$this
            ->where('activity_id','=',$activity_id)
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
            //商品基础数据
            $tem['id']=$product_id=$val['withProduct']['id'];
            $tem['store_name']=$val['withProduct']['store_name'];
            $tem['image']=$val['withProduct']['image'];
            $tem['price']=$val['withProduct']['price'];
            $tem['stock']=$val['withProduct']['stock'];
            $tem['spec_type']=$val['withProduct']['spec_type'];

            //多规格商品
            $result = StoreProductAttrResult::getResult($product_id, $type,$activity_id);
            foreach ($result['value'] as $k => $v) {
                $num = 1;
                foreach ($v['detail'] as $dv) {
                    $result['value'][$k]['value' . $num] = $dv;
                    $num++;
                }
            }
            $tem['items'] = $result['attr'];
            $tem['attrs'] = $result['value'];
            $tem['attr'] = ['pic' => '', 'price' => 0, 'cost' => 0, 'ot_price' => 0, 'stock' => 0, 'bar_code' => '', 'weight' => 0, 'volume' => 0, 'brokerage' => 0, 'brokerage_two' => 0];

            $data[]=$tem;
        }
        return $data;
    }

    public function withProduct(){
        return $this->belongsTo('app\admin\model\store\StoreProduct','product_id','id');
    }

    /**
     * 获取一条秒杀产品
     * @param $id
     * @param string $field
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public static function getValidProduct($id, $field = '*')
    {
        $time = time();
        $info = self::alias('n')
            ->join('store_product c', 'c.id=n.product_id')
            ->where('n.id', $id)
            ->where('c.is_show', 1)
            ->where('c.is_del', 0)
            ->where('n.is_del', 0)
            ->where('n.status', 1)
//            ->where('n.start_time', '<', $time)
//            ->where('n.stop_time', '>', $time - 86400)
            ->field('n.*,SUM(c.sales+c.ficti) as total')
            ->find();
        if ($info['id']) {
            return $info;
        } else {
            return [];
        }
    }


    /**
     * 获取活动首页的商品列表
     * @param int $tenant_id
     * @param int $page
     * @param int $limit
     * @return array
     */
    public static function getProductListToIndex($tenant_id=18,$page=1,$limit=5,$type=4){
        $data=self::alias('n')
            ->field('n.id,n.activity_id,n.product_id,n.image,n.title,n.price,n.ot_price,n.stop_time')
            ->join('store_product c', 'c.id=n.product_id')
            ->where('n.type',$type)
            ->where('c.tenant_id',$tenant_id)
            ->where('c.is_show', 1)
            ->where('c.is_del', 0)
            ->where('n.is_del', 0)
            ->where('n.status', 1)
            ->page($page,$limit)
            ->order('n.stop_time', 'desc')
            ->select();
        if(!count($data)){
            return [];
        }

        $data=$data->append(['stop_time_text']);

        return $data->toArray();
    }

    /**
     * 获取活动首页的商品列表
     * @param $activity_id
     * @param int $page
     * @param int $limit
     * @return array
     */
    public static function getProductListByActivityIDtoAPI($activity_id){
        $data=self::where('activity_id','=',$activity_id)->with(['withProduct'])->select();

        if(!count($data)){
            return [];
        }

        $data = $data->toArray();

        $return =[];
        foreach($data as $val){
            if(!$val['withProduct']){
                continue;
            }

            $temp=[
                'product_id'=>$val['product_id'],
                'store_name'=>$val['withProduct']['store_name'],
                'store_info'=>$val['withProduct']['store_info'],
                'image'=>$val['withProduct']['image'],
                'price'=>$val['withProduct']['price'],
                'ot_price'=>$val['withProduct']['ot_price'],
                'unit_name'=>$val['withProduct']['unit_name'],
            ];
            $return[]=$temp;
        }

        return $return;
    }







}