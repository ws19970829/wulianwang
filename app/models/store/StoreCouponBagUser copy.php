<?php
/**
 * StoreCouponBagUser.php
 * desc:
 * created on  2020/9/18 10:23 PM
 * Created by caogu
 */

namespace app\models\store;


use crmeb\basic\BaseModel;

class Activity extends BaseModel
{
    protected $name='activity';

    /**
     * 查询一条数据是否存在
     * @param $map
     * @param string $field
     * @return bool 是否存在
     */
    public static function be($map, $field = '')
    {
        $model = (new self);
        if (!is_array($map) && empty($field)) $field = $model->getPk();
        $map = !is_array($map) ? [$field => $map] : $map;
        return 0 < $model->where($map)->count();
    }
}