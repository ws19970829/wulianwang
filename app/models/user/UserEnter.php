<?php


namespace app\models\user;

use app\models\store\StoreOrder;
use app\models\store\StoreProduct;
use crmeb\services\SystemConfigService;
use think\facade\Session;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use crmeb\traits\JwtAuthModelTrait;

/**
 * TODO 用户Model
 * Class User
 * @package app\models\user
 */
class UserEnter extends BaseModel
{
    use JwtAuthModelTrait;
    use ModelTrait;

    protected $pk = 'id';

    protected $name = 'user_enter';

    public static function getFind($uid = 0)
    {
        $data = self::where('uid',$uid)->find();
        $data['status_name'] = '';
        if($data['status'] == 0){
            $data['status_name'] = '审核中';
        }
        if($data['status'] == 1){
            $data['status_name'] = '审核成功';
        }
        if($data['status'] == -1){
            $data['status_name'] = '审核失败';
        }

        $data['idcard_img'] = json_decode($data['idcard_img'],true);
        $data['business_img'] = json_decode($data['business_img'],true);

        return $data;
    }

    public static function setCreate($data,$uid)
    {
        $merchant_name = $data['merchant_name'];
        $link_user = $data['link_user'];
        $link_tel = $data['link_tel'];
        $province = $data['province'];
        $city = $data['city'];
        $district = $data['district'];
        $address = $data['address'];
        $user_card_num = $data['user_card_num'];
        $idcard_img = $data['idcard_img'];
        $business_img = $data['business_img'];
        $remark = $data['remark'];
        $add_time = time();
        return self::create(compact('uid','merchant_name','link_user','link_tel','province','city','district','address','user_card_num','idcard_img','business_img','remark','add_time'));
    }

}