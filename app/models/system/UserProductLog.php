<?php

namespace app\models\system;

use app\models\store\StoreCart;
use app\models\store\StoreOrder;
use app\models\user\User;
use app\models\user\UserBill;
use app\models\user\UserLevel;
use app\models\user\UserTaskFinish;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * TODO 设置等级任务Model
 * Class SystemUserTask
 * @package app\models\system
 */
class UserProductLog extends BaseModel
{
    use ModelTrait;

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

    /**
     * 添加记录表
     * @param int|array $product_ids  商品Id
     * @param string $uid    用户id
     * @param string $type  1：列表曝光 2：详情访问 3：加购物车
     * @return array|mixed|null|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function setCreateLog($product_ids,$uid,$type,$card_num=0)
    {
        if(!$uid || !$product_ids){
            return true;
        }

        $add_time = time();
        //将时间更改为当天的日期
        $date_time = date('Y-m-d',time());
        $ip = self::getip();

        //如果$product_ids为数组，则一定说明是列表的访问状态，这种情况只需要增加曝光率即可，所以做批量插入记录
        if(is_array($product_ids)){
            $data=[];
            for($i=0;$i<count($product_ids);$i++){
                if(!$product_ids[$i]){
                    continue;
                }
                $data[$i]=[
                    'product_id'=>$product_ids[$i],
                    'uid'=>$uid,
                    'type'=>$type,
                    'add_time'=>$add_time,
                    'ip'=>$ip,
                    'date_time'=>$date_time
                ];
            }
            (new UserProductLog())->saveAll($data);
        }else{
            //如果是单个文章的访问，要看访问类型。
            $product_id=$product_ids;
            //如果是曝光次数，则直接新增一次记录
            if($type==1 || $type==2){
                //如果是曝光人数和详情访问，则看插入记录
                self::create(compact('product_id','uid','type','add_time','ip','date_time'));
            }else if($type==3){
                //加购物车的人
                $where=[
                    'product_id'=>$product_ids,
                    'type'=>$type,
                    'uid'=>$uid,
                    'date_time'=>$date_time,
                ];
                $id=self::where($where)->value('id');
                if(!$id){
                    //若无访问记录，则创建访问次数
                    self::create(compact('product_id','uid','type','add_time','ip','date_time','card_num'));
                }else{
                    //如果是加购物车的人，在当天已经添加过购物车，则只增加数量
                    self::where('id','=',$id)->inc('card_num')->update();
                }
            }
        }

    }


     public static function getip() {

        static $ip = '';

        $ip = $_SERVER['REMOTE_ADDR'];

        if(isset($_SERVER['HTTP_CDN_SRC_IP'])) {

            $ip = $_SERVER['HTTP_CDN_SRC_IP'];

        } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {

            $ip = $_SERVER['HTTP_CLIENT_IP'];

        } elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {

            foreach ($matches[0] AS $xip) {

                if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {

                    $ip = $xip;

                    break;

                }

            }

        }

        return $ip;

    }


    /**
     * 获取商品的曝光次数
     * @param $product_id
     * @param $start_time
     * @param $end_time
     * @return int
     */
    public static function getViewNumByProductId($product_id,$start_time,$end_time){
        return self::where('product_id','=',$product_id)
            ->where('add_time','>=',$start_time)
            ->where('add_time','<=',$end_time)
            ->where('type','=',1)
            ->count();
    }

    /**
     * 获取商品的曝光人数数
     * @param $product_id
     * @param $start_time
     * @param $end_time
     * @return int
     */
    public static function getViewUserNumByProductId($product_id,$start_time,$end_time){
        return self::where('product_id','=',$product_id)
            ->where('add_time','>=',$start_time)
            ->where('add_time','<=',$end_time)
            ->where('type','=',1)
            ->group('uid')
            ->count();
    }


    /**
     * 获取商品的访客数(访问人数)
     * @param $product_id
     * @param $start_time
     * @param $end_time
     * @return int
     */
    public static function getVisitNumByProductId($product_id,$start_time,$end_time){
        return self::where('product_id','=',$product_id)
            ->where('add_time','>=',$start_time)
            ->where('add_time','<=',$end_time)
            ->where('type','=',2)
            ->count();
    }

    /**
     * 获取商品的访客数(访问人数)
     * @param $product_id
     * @param $start_time
     * @param $end_time
     * @return int
     */
    public static function getVisitUserNumByProductId($product_id,$start_time,$end_time){
        return self::where('product_id','=',$product_id)
            ->where('add_time','>=',$start_time)
            ->where('add_time','<=',$end_time)
            ->where('type','=',2)
            ->group('uid')
            ->count();
    }

    /**
     * 获取商品的访客数(访问IP数)
     * @param $product_id
     * @param $start_time
     * @param $end_time
     * @return int
     */
    public static function getVisitIpNumByProductId($product_id,$start_time,$end_time){
        return self::where('product_id','=',$product_id)
            ->where('add_time','>=',$start_time)
            ->where('add_time','<=',$end_time)
            ->where('type','=',2)
            ->group('ip')
            ->count();
    }


    /**
     * 获取商品的加购人数
     * @param $product_id
     * @param $start_time
     * @param $end_time
     * @return int
     */
    public static function getCardUserNumByProductId($product_id,$start_time,$end_time){
        return self::where('product_id','=',$product_id)
            ->where('add_time','>=',$start_time)
            ->where('add_time','<=',$end_time)
            ->where('type','=',3)
            ->count();
    }

    /**
     * 获取商品的加购件数
     * @param $product_id
     * @param $start_time
     * @param $end_time
     * @return int
     */
    public static function getCardNumByProductId($product_id,$start_time,$end_time){
        return self::where('product_id','=',$product_id)
            ->where('add_time','>=',$start_time)
            ->where('add_time','<=',$end_time)
            ->where('type','=',3)
            ->sum('card_num');
    }

    /**
     * 获取商品的加购件数
     * @param $product_id
     * @param $start_time
     * @param $end_time
     * @return int
     */
    public static function getPayUserNumByProductId($product_id,$start_time,$end_time){
        return StoreCart::where('product_id','=',$product_id)
            ->where('add_time','>=',$start_time)
            ->where('add_time','<=',$end_time)
            ->where('is_pay','=',1)
            ->group('uid')
            ->count();
    }

}