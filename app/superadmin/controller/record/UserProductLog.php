<?php
/**
 * Created by PhpStorm.
 * User: xurongyao <763569752@qq.com>
 * Date: 2018/6/14 下午5:25
 */

namespace app\superadmin\controller\record;

use app\superadmin\controller\AuthController;
use app\superadmin\model\store\{StoreProduct, StoreCouponUser};
use app\superadmin\model\order\StoreOrder;
use app\superadmin\model\ump\{StoreBargain, StoreSeckill, StoreCombination};
use app\superadmin\model\user\{User, UserBill, UserExtract,UserProductLog as UserProductLogModel};
use app\superadmin\model\system\{SystemRole, SystemAdmin as AdminModel};
use crmeb\services\{FormBuilder as Form, JsonService as Json, UtilService as Util};
/**
 * 微信充值记录
 * Class UserRecharge
 * @package app\admin\controller\user
 */
class UserProductLog extends AuthController
{
    /**
     * 显示操作记录
     */
    public function index()
    {
        return $this->fetch();
    }

    public function get_user_product_log_list()
    {
        $where = Util::getMore([
            ['store_name', ''],
            ['page', 1],
            ['limit', 20],
            ['order', ''],
            ['star_time',''],
            ['end_time','']
        ]);
        return Json::successlayui(UserProductLogModel::getProductList($where));
    }
}