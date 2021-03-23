<?php

/**
 * UserTagLog.php
 * desc:
 * created on  2020/9/25 12:04 AM
 * Created by caogu
 */

namespace app\admin\model\user;


use crmeb\basic\BaseModel;

class Invitation extends BaseModel
{

    protected $name = 'invitation';


    public static function getList($where)
    {
        $condition = [];
        if (!empty($where['phone'])) {
            $condition[] = ['phone', '=', $where['phone']];
        }
        if(empty($where['sys'])){
            $condition[] = ['tenant_id', '=', session('tenant_id')];
        }
        $data = self::page((int) $where['page'], (int) $where['limit'])->where($condition)->order('add_time', 'desc')->select();
        $data = $data->append(['is_submit']);
        $count = self::where($condition)->count();
        return compact('count', 'data');
    }

    public function getIsSubmitAttr($v, $data)
    {
        $res = db('user')
            ->where('account', $data['phone'])
            ->whereOr('phone', $data['phone'])
            ->count();
        if ($res) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getAddTimeAttr($v)
    {
        return date('Y-m-d H:i:s', $v);
    }
}
