<?php


namespace app\admin\model\collection;
use app\admin\model\store\StoreProduct;
use app\admin\model\wechat\WechatUser;
use app\api\controller\PublicController;
use app\admin\model\user;
use crmeb\basic\BaseModel;
use crmeb\services\WechatService;
use crmeb\traits\ModelTrait;
use org\QRcode;


/**
 * 商家管理Model
 * Class StoreOrder
 * @package app\admin\model\store
 */
class CollectionOrder extends BaseModel
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
    protected $name = 'collection_order';

    use ModelTrait;

    protected function getAddTimeAttr($val){
        return $val?date('Y-m-d',$val):'';
    }


    protected function getPayTimeAttr($val){
        return $val?date('Y-m-d H:i:s',$val):'';
    }

    /**
     * 扫码付款支付
     * @param $collection_order_id
     * @param $uid
     * @return bool|mixed
     */
    public static function jsPayPrice($collection_order_id,$uid)
    {
        $orderInfo = self::where('id', $collection_order_id)
            ->where('uid',$uid)
            ->find();
        if (!$orderInfo) return self::setErrorInfo('订单不存在!');
        if ($orderInfo['paid']) return self::setErrorInfo('该订单已支付!');
        $bodyContent = '快捷付款';
        $site_name = sys_config('site_name');
        $tenant_id=$orderInfo['tenant_id'];

        $openid=\app\models\user\WechatUser::where('uid','=',$uid)->value('openid');

        if (!$bodyContent && !$site_name) exception('支付参数缺少：请前往后台设置->系统设置-> 填写 网站名称');
        return WechatService::jsPay($openid, $orderInfo['order_id'], $orderInfo['pay_price'], 'collection', $site_name.'-'.$bodyContent, '', 'JSAPI',[],$tenant_id);
    }


    /**
     * TODO用户扫码付款成功后
     * @param $orderId
     * @return bool|static
     */
    public static function rechargeSuccess($orderId)
    {
        $order = self::where('order_id',$orderId)->where('paid',0)->find();
        if(!$order) return false;

        $res = self::where('order_id',$order['order_id'])->update(['paid'=>1,'pay_time'=>time()]);
        self::beginTrans();
        self::checkTrans($res);
        return $res;
    }


    public static function getOrderList($where){
        $data = ($data = self::systemPage($where,true)
            ->page((int)$where['page'], (int)$where['limit'])
            ->with(['withUser','withCollection'])
            ->select()) && count($data) ? $data->toArray() : [];


        $count = self::systemPage($where,true)->count();
        return compact('count', 'data');
    }


    public  function withUser(){
        return $this->belongsTo('UserModel','uid','uid');
    }

    public  function withCollection(){
        return $this->belongsTo('Collection','collection_id','id');
    }

    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where, $isAjax = false)
    {
        $model = new self;
        $model=$model->where('tenant_id','=',session('tenant_id'));
        $model=$model->where('paid','=',1);

        if($where['title']){
            $title=$where['title'];
            $model=$model->where('title','like',"%$title%");
        }

        if(isset($where['status'])){
            $model=$model->where('status','=',$where['status']);
        }

        if(isset($where['start_time']) && $where['start_time']){
            $model=$model->where('pay_time','>=',strtotime($where['start_time']));
        }

        if(isset($where['end_time']) && $where['end_time']){
            $model=$model->where('pay_time','<=',strtotime($where['end_time']));
        }


        if ($isAjax === true) {
            if (isset($where['order']) && $where['order'] != '') {
                $model = $model->order(self::setOrder($where['order']));
            } else {
                $model = $model->order('id desc');
            }
            return $model;
        }
        return self::page($model, function ($item) {

        }, $where);
    }


}