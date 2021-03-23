<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\system;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use think\facade\Session;

/**
 * Class SystemAdmin
 * @package app\admin\model\system
 */
class MarginManagement extends BaseModel
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
    protected $name = 'margin_management';

    use ModelTrait;

    protected $insert = ['add_time'];

    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where)
    {
        $model = new self;
        $model = $model
            ->where('is_del', 0)
            ->order('id','desc');
        return self::page($model, function ($admin) {
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

    public static function getFindIsPay($id)
    {
        $find = self::where('admin_id',$id)->where('is_pay',1)->find();
        if($find){
            return 1;
        }else{
            return 0;
        }
    }
}