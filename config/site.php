<?php
return [
    'default_site_url'=>'http://xiaohuixiang.3todo.com',
    'fans_plan_url'=>'http://xiaohuixiang.3todo.com/share/index/note.html',//?id=2&is_admin_view=1
    'plan_upper'=>3,//商户能够创建粉丝营销计划数量的上线
//    'default_logo'=>'http://qiniu.xiaohuixiang.3todo.com/f2377202008131730033708.png',//默认头像logo
    'default_logo'=>'http://qiniu.xiaohuixiang.3todo.com/efc85202009251416037417.png',//默认头像logo
    'site_name'=>'多麦五金商城',//默认网站名称
    'different_store_ruoe'=>'http://pc.xiaohuixiang.3todo.com',//异业适配商城地址
    //后台配置的个人中心按钮
    'wechat_index_menu'=>[
        ['value'=>'classifyGoods?type=discount&title=限时折扣', 'label'=>'限时折扣', 'disabled'=>0],
        ['value'=>'classifyGoods?type=seckill&title=限时秒杀', 'label'=>'限时秒杀', 'disabled'=>0],
        ['value'=>'classifyGoods?type=gift&title=支付有礼', 'label'=>'支付有礼', 'disabled'=>0],
        ['value'=>'hot_new_goods/1', 'label'=>'精品推荐', 'disabled'=>0],
        ['value'=>'category', 'label'=>'商品分类', 'disabled'=>0],
        ['value'=>'cart', 'label'=>'购物车', 'disabled'=>0],
        ['value'=>'user', 'label'=>'个人中心', 'disabled'=>0],
        ['value'=>'user/user_coupon', 'label'=>'我的优惠券', 'disabled'=>0],
        ['value'=>'user/account', 'label'=>'我的钱包', 'disabled'=>0],
        ['value'=>'collection', 'label'=>'我的收藏', 'disabled'=>0],
        ['value'=>'user/add_manage', 'label'=>'地址管理', 'disabled'=>0],
        ['value'=>'order/list/', 'label'=>'我的订单', 'disabled'=>0],
    ],
];
