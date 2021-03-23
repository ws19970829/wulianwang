<?php

/**
 * StoreCouponBagUser.php
 * desc:
 * created on  2020/9/18 10:23 PM
 * Created by caogu
 */

namespace app\models\store;

use app\models\user\User;
use app\models\user\UserMessage;
use crmeb\basic\BaseModel;

class SeckillSubscribe extends BaseModel
{
    protected $name = 'seckill_subscribe';

    /**获取未推送短信通知的用户 */
    public static function no_send($activity_id)
    {
        return self::where([
            'seckill_id' => $activity_id,
            'is_push' => 0
        ])->column('uid') ?? [];
    }

    /**发送活动短信 */
    public static function send($activity_id, $ids)
    {
        foreach ($ids as $v) {
            $user = (new User());
            $mobile = $user->where('uid', $v)->value('account');
            if (empty($mobile)) continue;
            $result = (new User())->send($mobile,  '', 'SMS_205397069');
            if ($result['code'] == 1) {
                //发送成功
                self::where('uid', $v)->where('seckill_id', $activity_id)->update(['is_push' => 1]);
            }
            (new UserMessage)->create_message($v, '提示', '您关注的秒杀活动即将开始！');
        }
    }
}
