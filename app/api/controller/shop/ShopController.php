<?php

namespace app\api\controller\shop;

use app\admin\model\system\SystemConfig;
use app\models\store\StoreCombination;
use app\models\store\StoreCouponIssue;
use app\models\store\StoreProduct;
use app\models\store\StoreSeckill;
use crmeb\services\{
    UtilService
};
use app\Request;
use app\models\system\SystemAdmin;


class ShopController
{
    /**
     * 店铺列表
     * @author Meng
     * @dateTime 2020-11-24
     * @param    Request    $request [description]
     * @return   [type]              [description]
     */
    public function list(Request $request)
    {
        $data = UtilService::getMore([
            [['page', 'd'], 0],
            [['limit', 'd'], 0],
            ['keyword','']
        ], $request);

        return app('json')->successful(SystemAdmin::getList($data, $request->uid()));
    }

    /**
     * 店铺详情
     * @author 金橙
     * @dateTime 2020-11-24
     * @param    Request    $request [description]
     * @return   [type]              [description]
     */
    public function read(Request $request)
    {
        $uid = $request->uid();
        // $param = $request->param();
        // trace($param,'error');
        $tenant_id = $request->param('tenant_id');

        //轮播
        $banner = db('shop_banner')
            ->where('tenant_id', $tenant_id)
            ->field('url,img as pic')
            ->order('sort', 'desc')
            ->select() ?? [];
        if ($banner->isEmpty()) {
            $banner[] = ['url' => '', 'pic' => 'http://qiniu-wujin.3todo.com/9dcc7202012011521466110.png'];
        }
        $shop = SystemAdmin::get($tenant_id) ?? [];
        //店铺信息
        if (!empty($shop)) {
            $shop = $shop->append(['logo_img_filter'])->toArray();
            $shop['is_collect'] = 0;
            $shop['customer_mobile'] = SystemConfig::getConfigValue('server_tel',$tenant_id)??'';
            if ($uid) {
                $res = db('user_collect')
                    ->where('uid', $uid)
                    ->where('type', 2)
                    ->where('collect_id', $tenant_id)
                    ->count();
                if ($res) $shop['is_collect'] = 1;
            }
        }

        //主营分类
        $ids = db('system_admin')
            ->where('id', $tenant_id)
            ->value('usable_cate') ?? "";
        $ids = explode(',', $ids);
        $category = db('store_category')
            ->where('id', 'in', $ids)
            // ->where('level', 3)
            // ->where('is_show', 1)
           // ->where('tenant_id', $tenant_id)
            ->order('sort', 'desc')
            ->select();
        //店铺优惠券
        $coupon = StoreCouponIssue::getIssueCouponList($uid, 0, 0, 1, 0, $tenant_id, 1, 0, 2);
        //店铺商品
        $goods = StoreProduct::where([
            'tenant_id' => $tenant_id,
            'is_del' => 0,
            'is_show' => 1
        ])->field('id,image,store_name,price,ot_price,sales,tenant_id')
            ->order('sort', 'desc')
            ->page(input('page', 1), input('limit', 10))
            ->select();
        $goods = $goods->append(['collect', 'goods_url', 'shop_name'])->toArray();

        $goods_count = StoreProduct::where([
            'tenant_id' => $tenant_id,
            'is_del' => 0,
            'is_show' => 1
        ])->count();

        //团购
        $combination = StoreCombination::alias('a')
            ->join('store_product b', 'a.product_id=b.id')
            ->where('a.tenant_id', $tenant_id)
            ->where('a.is_show', 1)
            ->where('a.is_host', 1)
            ->where('a.is_del', 0)
            ->limit(5)
            ->order('a.sort')
            ->field('a.id,a.product_id,a.image,a.title,a.info,a.stock,quota,a.goods_num,a.price,b.price as ot_price,FROM_UNIXTIME( `start_time`,  "%Y-%m-%d" ) AS  start_time , FROM_UNIXTIME( `stop_time`,  "%Y-%m-%d" ) AS  stop_time')
            ->select();
        $combination = $combination->append(['is_valid', 'goods_url', 'quota_text', 'goods_num_text', 'price_text', 'ot_price_text', 'com_id'])->toArray();

        //置顶秒杀
        $seckill = StoreSeckill::alias('a')
            ->join('store_product b', 'a.product_id = b.id')
            ->where('a.tenant_id', $tenant_id)
            ->where([
                'a.is_del' => 0,
                'a.is_show' => 1,
                'a.is_hot' => 1
            ])->whereTime('stop_time', '>', time())
            ->field('a.id,a.product_id,a.title,a.image,a.info,a.stock,quota,a.price,b.price as ot_price,start_time,stop_time,FROM_UNIXTIME( `start_day`,  "%Y-%m-%d" ) AS  start_day , FROM_UNIXTIME( `stop_day`,  "%Y-%m-%d") AS  stop_day')
            ->limit(5)
            ->order('a.sort')
            ->select();
        $seckill = $seckill->append(['goods_url', 'start_timestamp', 'subscribe', 'price_text', 'ot_price_text', 'start_date', 'quota_text', 'status', 'status_text', 'seckill_id'])->toArray();

        $rec = SystemAdmin::where('is_rec', 1)
            ->where('is_del', 0)
            ->where('id', '<>', $tenant_id)
            ->field('id,real_name,remark,logo_img')
            ->whereBetweenTimeField('rec_start', 'rec_end')
            ->order('sort', 'desc')
            ->select();
        $rec = $rec->append(['logo_img_filter'])->toArray();
        return app('json')->successful('', compact('banner', 'goods', 'goods_count', 'combination', 'seckill', 'coupon', 'category', 'rec', 'shop'));
    }
}
