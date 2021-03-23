<?php

namespace app\admin\controller;

use app\models\store\SeckillSubscribe;
use app\models\store\StoreSeckill;
use app\models\user\User;
use crmeb\utils\Redis;

class Test
{
    /**活动短信通知 */
    public function send_message()
    {

        $seckill_id = db('store_seckill')
            ->whereTime('start_day', '<=', time())
            ->whereTime('stop_day', '>=', time())
            ->where('status', 1)
            ->where('is_del', 0)
            ->whereBetweenTime('start_time', date('H:i:s', time() - 600), date('H:i:s', time()))
            ->column('id') ?? [];

        foreach ($seckill_id as $v) {
            $ids = SeckillSubscribe::no_send($v);
            if (empty($ids)) continue;
            SeckillSubscribe::send($v, $ids);
        }
        echo 'success';
    }
}
