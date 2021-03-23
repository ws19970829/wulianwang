<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 状态码相关配置
// +----------------------------------------------------------------------

return [

    // 全部
    'order_all' => '',
    // 待支付
    'order_un_pay' => 'un_pay',
    // 待发货
    'order_un_deliver' => 'un_deliver',
    // 待收货
    'order_un_receive' => 'un_receive',
    // 已完成
    'order_completed' => 'completed',
    // 已取消
    'order_cancel' => 'cancel',
];