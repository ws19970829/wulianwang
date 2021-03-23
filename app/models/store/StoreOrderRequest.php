<?php
/**
 * Created by PhpStorm.
 * User: lingyun
 * Date: 2020/8/10
 * Time: 22:07
 * Desc: 订单请求 - 响应报文
 */
namespace app\models\store;

use crmeb\basic\BaseModel;
use think\facade\Cache;
use app\models\user\{
    User, UserAddress, UserBill, WechatUser
};
use crmeb\services\{
    SystemConfigService, WechatTemplateService, workerman\ChannelService
};
use crmeb\repositories\{
    GoodsRepository, PaymentRepositories, OrderRepository, ShortLetterRepositories, UserRepository
};
use think\facade\Db;

/**
 * TODO 订单Model
 * Class StoreOrder
 * @package app\models\store
 */
class StoreOrderRequest extends BaseModel
{
    /**
     * @Author  lingyun
     * @Desc    保存请求报文
     * @param string $order_id
     * @param string $order_sn
     * @param $data
     */
    public function saveRequestData($order_id='',$order_sn='',$data){
        $request_data = '';

        foreach($data as $k => $v){
            $request_data .= $k.'='.$v.'&';
        }

        $update_data = [
            'order_id'=>$order_id,
            'order_sn'=>$order_sn,
            'request_data'=>trim($request_data,'&'),
            'update_time'=>time(),
            'create_time'=>time()
        ];

        $this->create($update_data);
    }

    /**
     * @Author  lingyun
     * @Desc    响应报文
     * @param string $order_id
     * @param string $order_sn
     * @param $data
     */
    public function saveResponseData($order_sn='',$data){
        $response_data = '';

        foreach($data as $k => $v){
            $response_data .= $k.'='.$v.'&';
        }

        $update_data = [
            'response_data'=>trim($response_data,'&'),
            'update_time'=>time(),
        ];

        $this->where('order_sn',$order_sn)->update($update_data);
    }

    /**
     * @Author  lingyun
     * @Desc    保存公众号，小程序支付支付响应报文
     * @param $order_sn
     * @param $data
     */
    public function saveWxResponseData($order_sn,$data){
        $data = json_decode($data,true);

        $response_data = '';

        foreach($data as $k => $v){
            $response_data .= $k.'='.$v.'&';
        }

        $update_data = [
            'response_data'=>trim($response_data,'&'),
            'update_time'=>time(),
        ];

        $this->where('order_sn',$order_sn)->update($update_data);
    }

}