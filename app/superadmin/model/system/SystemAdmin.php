<?php

/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\superadmin\model\system;

use app\api\controller\PublicController;
use app\superadmin\model\order\StoreOrder;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use think\facade\Session;

/**
 * Class SystemAdmin
 * @package app\admin\model\system
 */
class SystemAdmin extends BaseModel
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
    protected $name = 'system_admin';

    use ModelTrait;

    protected $insert = ['add_time'];

    public static function setAddTimeAttr($value)
    {
        return time();
    }

    public static function setRolesAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    public static function getIdcardImgAttr($val)
    {
        return json_decode($val, true);
    }

    public function getLogoImgFilterAttr($v, $data)
    {
        $arr = json_decode($data['logo_img'], true);
        if (empty($arr)) return '';
        return $arr[0];
    }


    /**
     * 用户登陆
     * @param $account
     * @param $pwd
     * @return bool
     */
    public static function login($account, $pwd)
    {
        $adminInfo = self::get(compact('account'));
        if (!$adminInfo) return self::setErrorInfo('登陆的账号不存在!');
        if ($adminInfo['pwd'] != md5($pwd)) return self::setErrorInfo('账号或密码错误，请重新输入');
        if (!$adminInfo['status']) return self::setErrorInfo('该账号已被关闭!');
        if (!$adminInfo['is_superadmin_create']) return self::setErrorInfo('该账号权限不足，请通过商家后台登录。');
        if ($adminInfo['roles'] == 2) return self::setErrorInfo('该账号权限不足，请通过商家后台登录');
        self::setLoginInfo($adminInfo);
        event('SystemAdminLoginAfter', [$adminInfo]);
        return true;
    }

    /**
     *  保存当前登陆用户信息
     */
    public static function setLoginInfo($adminInfo)
    {
        Session::set('superadminId', $adminInfo['id']);
        Session::set('superadminInfo', $adminInfo->toArray());
        Session::save();
    }

    /**
     * 清空当前登陆用户信息
     */
    public static function clearLoginInfo()
    {
        Session::delete('superadminInfo');
        Session::delete('superadminId');
        Session::save();
    }

    /**
     * 检查用户登陆状态
     * @return bool
     */
    public static function hasActiveAdmin()
    {

        return Session::has('superadminId') && Session::has('superadminInfo');
    }

    /**
     * 获得登陆用户信息
     * @return mixed
     * @throws \Exception
     */
    public static function activeAdminInfoOrFail()
    {
        $adminInfo = Session::get('superadminInfo');
        if (!$adminInfo) exception('请登陆');
        if (!$adminInfo['status']) exception('该账号已被关闭!');
        return $adminInfo;
    }

    /**
     * 获得登陆用户Id 如果没有直接抛出错误
     * @return mixed
     * @throws \Exception
     */
    public static function activeAdminIdOrFail()
    {
        $adminId = Session::get('superadminId');
        if (!$adminId) exception('访问用户为登陆登陆!');
        return $adminId;
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public static function activeAdminAuthOrFail()
    {
        $adminInfo = self::activeAdminInfoOrFail();
        if (is_object($adminInfo)) $adminInfo = $adminInfo->toArray();
        return $adminInfo['level'] === 0 ? SystemRole::getAllAuth() : SystemRole::rolesByAuth($adminInfo['roles']);
    }

    /**
     * 获得有效管理员信息
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public static function getValidAdminInfoOrFail($id)
    {
        $adminInfo = self::get($id);
        if (!$adminInfo) exception('用户不能存在!');
        if (!$adminInfo['status']) exception('该账号已被关闭!');
        return $adminInfo;
    }

    /**
     * @param string $field
     * @param int $level
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getOrdAdmin($field = 'real_name,id', $level = 0)
    {
        return self::where('level', '>=', $level)->field($field)->select();
    }

    public static function getTopAdmin($field = 'real_name,id')
    {
        return self::where('level', 0)->field($field)->select();
    }

    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where)
    {
        $model = new self;
        if ($where['name'] != '') $model = $model->where('account|real_name', 'LIKE', "%$where[name]%");
        if ($where['roles'] != '') $model = $model->where("CONCAT(',',roles,',')  LIKE '%,$where[roles],%'");
        $model = $model
            ->where('tenant_id', '=', 0)
            ->where('level', $where['level'])
            ->where('is_del', 0)
            ->order('id', 'desc');
        return self::page($model, function ($admin) {
            $admin->order_num = db('store_order')
                ->where('is_del', 0)
                ->where('tenant_id', $admin->id)
                ->count();
            $admin->goods_num = db('store_product')
                ->where('is_del', 0)
                ->where('tenant_id', $admin->id)
                ->count();

            $admin->roles = SystemRole::where('id', 'IN', $admin->roles)->column('role_name', 'id');
        }, $where);
    }


    /**
     * @param $where
     * @return array
     */
    public static function systemPage1($where)
    {
        $model = new self;
        if ($where['name'] != '') $model = $model->where('account|real_name', 'LIKE', "%$where[name]%");
        if (!empty($where['roles'])) $model = $model->where("CONCAT(',',roles,',')  LIKE '%,$where[roles],%'");

        $model = $model
            ->where('is_del', 0)
            ->where('id','<>',1)
            ->order('id', 'desc');
        $data = $model->page($where['page'], $where['limit'])->select()->toArray();
        foreach ($data as &$v) {
            $v['order_num'] = db('store_order')
                ->where('is_del', 0)
                ->where('tenant_id', $v['id'])
                ->sum('pay_price');
            $v['goods_num'] = db('store_product')
                ->where('is_del', 0)
                ->where('tenant_id', $v['id'])
                ->count();
                if(!$v['is_rec']||!$v['rec_start']){
                    $v['rec_start'] = '无';
                }else{
                    $v['rec_start'] = date('Y-m-d H:i:s',$v['rec_start']);
                }
                if(!$v['is_rec']||!$v['rec_end']){
                    $v['rec_end'] = '无';
                }else{
                    $v['rec_end'] = date('Y-m-d H:i:s',$v['rec_end']);
                }
            
        }
        $count = $model->count();
        return compact('data', 'count');
    }

    public static function get_settle_list($where = [])
    {

        $model = new self;
        if ($where['nickname'] != '') $model = $model->where('account|real_name', 'LIKE', "%$where[nickname]%");
        //        if ($where['roles'] != '') $model = $model->where("CONCAT(',',roles,',')  LIKE '%,$where[roles],%'");
        $model = $model
            ->where('tenant_id', '=', 0)
            ->where('level', '>', 0)
            ->where('is_del', 0)
            ->order('id', 'desc');

        $count = $model->count();
        $list = $model->select()->toArray();

        //平台服务费
        $business_rate = (new PublicController())->getSysConfigValue('business_rate', 1);
        $business_rate = floatval($business_rate);
        foreach ($list as $key => $val) {
            //计算该商家总计的营收情况
            $list[$key]['income'] = StoreOrder::getIncomeByTenantID($val['id']);
            $list[$key]['income_text'] = number_format($list[$key]['income'], 2);
            $list[$key]['refund_price'] = StoreOrder::getRefundPriceByTenantID($val['id']);
            $list[$key]['refund_price_text'] = number_format($list[$key]['refund_price'], 2);

            //毛利润
            $list[$key]['maolirun'] = $list[$key]['income'] - $list[$key]['refund_price'];
            $list[$key]['maolirun_text'] = number_format($list[$key]['income'] - $list[$key]['refund_price'], 2);


            //平台服务费率
            $list[$key]['business_rate'] = $business_rate;

            //应结算总额=毛利率*1-费率
            $list[$key]['yingjiesuan'] = $list[$key]['maolirun'] * (1 - $business_rate);
            $list[$key]['yingjiesuan_text'] = number_format($list[$key]['yingjiesuan'], 2);

            //已结算总额-应当从结算记录中获取
            $list[$key]['yijiesuan'] = 0;
            $list[$key]['yijiesuan_text'] = number_format($list[$key]['yijiesuan'], 2);


            //未结算总额=应结算-已结算
            $list[$key]['wait_settle_money'] = $list[$key]['yingjiesuan'] - $list[$key]['yijiesuan'];
            $list[$key]['wait_settle_money_text'] = number_format($list[$key]['wait_settle_money'], 2);
        }

        return ['list' => $list, 'count' => $count];
    }
}
