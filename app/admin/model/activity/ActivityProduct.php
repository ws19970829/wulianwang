<?php


namespace app\admin\model\activity;
use app\admin\model\store\StoreProductAttr;
use app\admin\model\store\StoreProductAttrResult;
use app\admin\model\store\StoreProductAttrValue;
use crmeb\basic\BaseModel;
use app\admin\model\store\StoreProduct;

/**
 * 活动关联商品表
 * Class StoreOrder
 * @package app\admin\model\store
 */
class ActivityProduct extends BaseModel
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
    protected $name = 'activity_product';


    protected function getImagesAttr($value)
    {
        return json_decode($value, true) ?: [];
    }

    public function getDescriptionAttr($value)
    {
        return htmlspecialchars_decode($value);
    }

    public function getStopTimeTextAttr($val,$data){
        return $data['stop_time']>time()?date('Y-m-d',$data['stop_time']):'已结束';
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
     * 通过商品id和活动id获取一条秒杀产品
     * @param $activity_id
     * @param $product_id
     * @param string $field
     * @return array
     */
    public static function getValidProductByProductyIDandActivityID($activity_id,$product_id, $field = '*')
    {
        $time = time();
        $info = self::alias('n')
            ->join('store_product c', 'c.id=n.product_id')
            ->where('n.product_id', $product_id)
            ->where('n.activity_id', $activity_id)
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
        if(!count($data)){
            return [];
        }

        $data=$data->append(['stop_time_text'])->toArray();


        foreach($data as $key => $val){
            $activity_type=Activity::where('id','=',$val['activity_id'])->value('type');
            list($productAttr, $productValue) = \app\models\store\StoreProductAttr::getProductAttrDetailToActivityToApi($val['product_id'], 0, $activity_type,0, $val['activity_id']);
            $first_attr=current($productValue);
            $data[$key]['price']=$first_attr['activity_price']>0?$first_attr['activity_price']:0;
        }




        return $data;
    }


    /**
     * 获取活动首页的商品列表
     * @param $activity_id
     * @param int $page
     * @param int $limit
     * @return array
     */
    public static function getProductListByActivityIDtoAPI($activity_id,$page=1,$limit=5){

//        $activity_info=Activity::where('id','=',$activity_id)
//            ->field('id,title,status,image,start_time,end_time,tenant_id,type,gift_type')
//            ->find();

        $activity_type=Activity::where('id','=',$activity_id)->value('type');


        $data=self::alias('n')
            ->field('n.id,n.activity_id,n.product_id,n.image,n.title,n.price,n.ot_price,n.stop_time')
            ->join('store_product c', 'c.id=n.product_id')
            ->where('n.activity_id','=',$activity_id)
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


        $data=$data->append(['stop_time_text'])->toArray();
        foreach($data as $key => $val){

            list($productAttr, $productValue) = \app\models\store\StoreProductAttr::getProductAttrDetailToActivityToApi($val['product_id'], 0, $activity_type,0,$activity_id);
            $first_attr=current($productValue);
            $data[$key]['price']=$first_attr['activity_price']>0?$first_attr['activity_price']:0;
        }

        return $data;
    }


    /**通过 $activity_id  和  $product_id 获取  activity_product表的id
     * @param $activity_id
     * @param $product_id
     * @return mixed
     */
    public static function getActivityProductIDbyActivityIDandProductID($activity_id,$product_id){
        return self::where('activity_id','=',$activity_id)
            ->where('product_id','=',$product_id)
            ->value('id');
    }


    /**
     * 修改活动的库存
     * @param int $num
     * @param int $activity_id
     * @return bool
     */
    public static function decStock($num = 0, $activity_id = 0, $unique = '')
    {


//        $product_id = self::where('id', $activity_id)->value('product_id');
        if ($unique) {
            $product_id=StoreProductAttrValue::where('unique', $unique)
                ->where('activity_id',$activity_id)
                ->value('product_id');

            $type=Activity::where('id','=',$activity_id)->value('type');
            $res1 = StoreProductAttrValue::decProductAttrStockByActivtiyAndUnique($activity_id, $unique, $num, $type);


            $res2 = self::where('activity_id', $activity_id)
                ->where('product_id','=',$product_id)
                ->dec('stock', $num)
                ->inc('sales', $num)
                ->update();




            $res3 = StoreProductAttrValue::where('unique', $unique)
                    ->where('activity_id',$activity_id)
                    ->where('product_id','=',$product_id)
                    ->dec('activity_stock', $num)
//                    ->dec('stock', $num)//经测试，会有一些商品在存入表的时候 stock会出现0的情况，而活动的库存主要是使用activity_stock，所以这个注掉，不再处理了
                    ->inc('sales', $num)
                    ->update();


            StoreProduct::where('id', $product_id)->dec('stock', $num)->inc('sales', $num)->update();

            return $res1&&$res2&&$res3;

        } else {
//            $res = false !== self::where('id', $activity_id)->dec('stock', $num)->inc('sales', $num)->update();
        }
//        $res = $res && StoreProduct::where('id', $product_id)->dec('stock', $num)->inc('sales', $num)->update();
        return true;
    }



}