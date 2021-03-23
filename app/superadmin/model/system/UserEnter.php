<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\superadmin\model\system;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use think\facade\Session;

/**
 * Class SystemAdmin
 * @package app\admin\model\system
 */
class UserEnter extends BaseModel
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
    protected $name = 'user_enter';

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

    public static function getIdcardImgAttr($val){
        return json_decode($val,true);
    }

    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where)
    {
        $model = new self;
        /*if ($where['name'] != '') $model = $model->where('account|real_name', 'LIKE', "%$where[name]%");
        if ($where['roles'] != '') $model = $model->where("CONCAT(',',roles,',')  LIKE '%,$where[roles],%'");*/
        $model = $model
            ->where('is_del', 0)
            ->where('is_lock', 0)
            ->order('id','desc');
        return self::page($model, function ($admin) {
            $admin->addr = $admin->province.''.$admin->city.''.$admin->district.''.$admin->address;
            if($admin->status == 0){
                $admin->status_name = "审核中";
            }
            if($admin->status == 1){
                $admin->status_name = "审核通过";
            }
            if($admin->status == -1){
                $admin->status_name = "审核不通过";
            }
        }, $where);
    }
}