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
    protected $name = 'activity';

    public static function allow($type, $uid, $activity_id)
    {
        $res = db('activity_record')->where([
            'type' => $type,
            'uid' => $uid,
            'activity_id' => $activity_id
        ])->count();
        if ($res) {
            return 0;
        } else {
            return 1;
        }
    }
}
