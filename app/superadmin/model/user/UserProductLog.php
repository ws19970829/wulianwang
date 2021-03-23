<?php
/**
 *
 * @author: wuhaotian<442384644@qq.com>
 * @day: 2019/12/07
 */

namespace app\superadmin\model\user;

use app\superadmin\model\store\StoreProduct;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * Class UserGroup
 * @package app\admin\model\user
 */
class UserProductLog extends BaseModel
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
    protected $name = 'user_product_log';

    use ModelTrait;

    public static function getProductList($where)
    {
        $star_time ="";
        $end_time ="";
        if($where['star_time'] != "" && $where['end_time'] != ""){
            $star_time = $where['star_time'].' 00:00:00';
            $end_time = $where['end_time'].' 00:00:00';
        }
        $data =StoreProduct::where('is_show',1)->where('is_del',0)->page((int)$where['page'], (int)$where['limit'])->field('id,price,image,store_name')->select()->toArray();
        foreach($data as $k => $v){
            //曝光次数
            $data[$k]['exposure_times'] = self::getExposureTimes($v['id'],1,$star_time,$end_time);
            //曝光人数
            $data[$k]['exposure_number'] = self::getExposureNumber($v['id'],2,$star_time,$end_time);
            //访客数
            $data[$k]['visitor_number'] = self::getVisitorNumber($v['id'],3,$star_time,$end_time);
            //浏览量
            $data[$k]['views_number'] = self::getViewsNumber($v['id'],4,$star_time,$end_time);
            //加购人数
            $data[$k]['cart_user_num'] = self::getCartUserNum($v['id'],5,$star_time,$end_time);
            //加购件数
            $data[$k]['cart_num'] = self::getCartNum($v['id'],5,$star_time,$end_time);
            //支付人数
            $data[$k]['pay_number'] = self::getPayNum($v['id'],6,$star_time,$end_time);
            //转化率
            $data[$k]['conversion_rate'] = '0.00%';
            if($data[$k]['visitor_number'] != 0){
                $data[$k]['conversion_rate'] =bcdiv($data[$k]['pay_number'],$data[$k]['visitor_number'],2);
                $data[$k]['conversion_rate'] =sprintf("%01.2f", $data[$k]['conversion_rate']*100).'%';
            }

        }
        /*if(){

        }*/
        $count = StoreProduct::where('is_show',1)->where('is_del',0)->count();
        return compact('count', 'data');
    }

    /**
     * 曝光次数 为不限人数，不限时间
     * @var string
     */
    public static function getExposureTimes($product_id,$type,$star_time = "",$end_time = "")
    {
        $model = new self;
        if($star_time != "" && $end_time != "") $model->where('add_time','>=',$star_time)->where('add_time','<=',$end_time);
        return $model->where('product_id',$product_id)->where('type',$type)->count();
    }

    /**
     * 曝光人数 不限时间，只限人数
     * @var string
     */
    public static function getExposureNumber($product_id,$type,$star_time = "",$end_time = "")
    {
        $model = new self;
        if($star_time != "" && $end_time != "") $model->where('add_time','>=',$star_time)->where('add_time','<=',$end_time);
        $list = $model->where('product_id',$product_id)->where('type',$type)->field('uid')->select()->toArray();
        $list = array_unique($list);
        return count($list);

    }


    /**
     * 访客数 走详情 看ip 一个人一天只看一次
     * @var string
     */
    public static function getVisitorNumber($product_id,$type,$star_time = "",$end_time = "")
    {
        $model = new self;
        if($star_time != "" && $end_time != "") $model->where('add_time','>=',$star_time)->where('add_time','<=',$end_time);
        $list = $model->where('product_id',$product_id)->where('type',$type)->field('ip,date_time')->select()->toArray();

        $list = self::array_unique_fb($list);

        return count($list);
    }


    /**
     * 浏览量 一天一个用户算一次
     * @var string
     */
    public static function getViewsNumber($product_id,$type,$star_time = "",$end_time = "")
    {
        $model = new self;
        if($star_time != "" && $end_time != "") $model->where('add_time','>=',$star_time)->where('add_time','<=',$end_time);
        $list = $model->where('product_id',$product_id)->where('type',$type)->field('uid,date_time')->select()->toArray();
        $list = self::array_unique_fb($list);

        return count($list);
    }

    /**
     * 加入购物车的人数
     * @var string
     */
    public static function getCartUserNum($product_id,$type,$star_time = "",$end_time = "")
    {
        $model = new self;
        if($star_time != "" && $end_time != "") $model->where('add_time','>=',$star_time)->where('add_time','<=',$end_time);
        $list = $model->where('product_id',$product_id)->where('type',$type)->field('uid')->select()->toArray();

        $list = self::array_unique_fb($list);
        return count($list);
    }

    /**
     * 加入购物车的次数
     * @var string
     */
    public static function getCartNum($product_id,$type,$star_time = "",$end_time = "")
    {
        $model = new self;
        if($star_time != "" && $end_time != "") $model->where('add_time','>=',$star_time)->where('add_time','<=',$end_time);
        $list = $model->where('product_id',$product_id)->where('type',$type)->field('uid')->select()->toArray();

        $list = self::array_unique_fb($list);
        return count($list);
    }


    /**
     * 支付人数
     * @var string
     */
    public static function getPayNum($product_id,$type,$star_time = "",$end_time = "")
    {
        $model = new self;
        if($star_time != "" && $end_time != "") $model->where('add_time','>=',$star_time)->where('add_time','<=',$end_time);
        $list = $model->where('product_id',$product_id)->where('type',$type)->field('uid')->select()->toArray();

        $list = self::array_unique_fb($list);
        return count($list);
    }




    public static function array_unique_fb($array2D) {

        $temp = array();
        foreach ($array2D as $v) {

            $v = join(",", $v); //降维,也可以用implode,将一维数组转换为用逗号连接的字符串

            $temp[] = $v;

        }

        $temp = array_unique($temp);//去掉重复的字符串,也就是重复的一维数组

        foreach ($temp as $k => $v) {

            $temp[$k] = explode(",", $v);//再将拆开的数组重新组装

        }

        return $temp;

    }

}