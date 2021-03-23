<?php

namespace app\api\controller\activity;

use app\admin\model\activity\Activity;
use app\admin\model\activity\ActivityProduct;
use app\admin\model\store\StoreDescription;
use app\admin\model\store\StoreProductAttrValue;
use app\models\store\StoreProduct;
use app\models\store\StoreProductAttr;
use app\models\store\StoreProductRelation;
use app\models\store\StoreProductReply;
use app\models\store\StoreSeckill;
use app\models\store\StoreVisit;
use app\models\system\UserProductLog;
use app\Request;
use crmeb\services\GroupDataService;
use crmeb\services\QrcodeService;
use crmeb\services\UtilService;

/**
 * 秒杀产品类
 * Class StoreSeckillController
 * @package app\api\controller\activity
 */
class StoreSeckillController
{
    /**
     * 活动首页
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {

        $type = input('param.type', 'discount');
        switch ($type) {
            case 'discount':
                $type = 4;
                break;
            case 'seckill':
                $type = 5;
                break;
            case 'gift':
                $type = 6;
                break;
            default:
                $type = 4;
        }


        $tenant_id = input('param.tenant_id', 18);
        $where = [
            'tenant_id' => $tenant_id,
            'type' => $type,
            'is_del' => 0,
            'status' => 1,
        ];
        $banner_list = Activity::where($where)
            ->field('id,title,image')
            ->order('end_time', 'desc')
            ->select();
        if (count($banner_list) > 0) {
            $banner_list = $banner_list->toArray();
        }

        //获取前几个推荐的活动商品
        $page = input('param.page', 1);
        $limit = input('param.limit', 5);
        $product_list = ActivityProduct::getProductListToIndex($tenant_id, $page, $limit, $type);

        $data = [
            'banner_list' => $banner_list,
            'product_list' => $product_list
        ];

        $uid = request()->uid();
        //列表访问，增加所有产品的曝光次数
        if ($uid != 0) {
            $prduct_ids = array_column($product_list, 'product_id');
            UserProductLog::setCreateLog($prduct_ids, $uid, 1); //曝光次数
        }
        return app('json')->successful($data);
    }


    /**
     * 获取活动下的商品列表
     * @return mixed
     */
    public function activity_list()
    {
        $activity_id = input('param.activity_id');
        if (!$activity_id) {
            return app('json')->fail('活动ID有误!');
        }

        $list = (new ActivityProduct())->getProductListByActivityIDtoAPI($activity_id);
        if (!count($list)) {
            return app('json')->fail('活动不存在或已下架!');
        }

        return app('json')->successful($list);
    }


    /**
     * 秒杀产品时间区间
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index_bak()
    {
        //秒杀时间段
        $seckillTime = sys_data('routine_seckill_time') ?? [];
        $seckillTimeIndex = 0;
        if (count($seckillTime)) {
            foreach ($seckillTime as $key => &$value) {
                $currentHour = date('H');
                $activityEndHour = bcadd((int) $value['time'], (int) $value['continued'], 0);
                if ($activityEndHour > 24) {
                    $value['time'] = strlen((int) $value['time']) == 2 ? (int) $value['time'] . ':00' : '0' . (int) $value['time'] . ':00';
                    $value['state'] = '即将开始';
                    $value['status'] = 2;
                    $value['stop'] = (int) bcadd(strtotime(date('Y-m-d')), bcmul($activityEndHour, 3600, 0));
                } else {
                    if ($currentHour >= (int) $value['time'] && $currentHour < $activityEndHour) {
                        $value['time'] = strlen((int) $value['time']) == 2 ? (int) $value['time'] . ':00' : '0' . (int) $value['time'] . ':00';
                        $value['state'] = '抢购中';
                        $value['stop'] = (int) bcadd(strtotime(date('Y-m-d')), bcmul($activityEndHour, 3600, 0));
                        $value['status'] = 1;
                        if (!$seckillTimeIndex) $seckillTimeIndex = $key;
                    } else if ($currentHour < (int) $value['time']) {
                        $value['time'] = strlen((int) $value['time']) == 2 ? (int) $value['time'] . ':00' : '0' . (int) $value['time'] . ':00';
                        $value['state'] = '即将开始';
                        $value['status'] = 2;
                        $value['stop'] = (int) bcadd(strtotime(date('Y-m-d')), bcmul($activityEndHour, 3600, 0));
                    } else if ($currentHour >= $activityEndHour) {
                        $value['time'] = strlen((int) $value['time']) == 2 ? (int) $value['time'] . ':00' : '0' . (int) $value['time'] . ':00';
                        $value['state'] = '已结束';
                        $value['status'] = 0;
                        $value['stop'] = (int) bcadd(strtotime(date('Y-m-d')), bcmul($activityEndHour, 3600, 0));
                    }
                }
            }
        }
        $data['lovely'] = sys_config('seckill_header_banner');
        if (strstr($data['lovely'], 'http') === false && strlen(trim($data['lovely']))) $data['lovely'] = sys_config('site_url') . $data['lovely'];
        $data['lovely'] = str_replace('\\', '/', $data['lovely']);
        $data['seckillTime'] = $seckillTime;
        $data['seckillTimeIndex'] = $seckillTimeIndex;
        $data['seckillCont'] = StoreSeckill::getSeckillContStatus();
        return app('json')->successful($data);
    }

    /**
     * 秒杀产品列表
     * @param Request $request
     * @param $time
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function lst_bak(Request $request, $time)
    {
        list($page, $limit) = UtilService::getMore([
            ['page', 0],
            ['limit', 0],
        ], $request, true);
        if (!$time) return app('json')->fail('参数错误');
        $seckillInfo = StoreSeckill::seckillList($time, $page, $limit);
        if (count($seckillInfo)) {
            foreach ($seckillInfo as $key => &$item) {
                if ($item['quota'] > 0) {
                    $quota = StoreProductAttrValue::where('product_id', $item['id'])->where('type', 1)->value('SUM(quota)');
                    $percent = (int) bcmul(bcdiv(bcsub($item['quota'], $quota), $item['quota'], 2), 100, 0);
                    $item['percent'] = $percent;
                    $item['stock'] = $quota;
                } else {
                    $item['percent'] = 100;
                    $item['stock'] = 0;
                }
                if ($item['percent'] < 0) {
                    $item['percent'] = 100;
                }
            }
        }
        return app('json')->successful($seckillInfo);
    }

    public function lst(Request $request)
    {
        list($page, $limit, $tenant_id) = UtilService::getMore([
            ['page', 0],
            ['limit', 0],
            ['tenant_id', 0],
        ], $request, true);
        //秒杀
        $condition = [];
        if (!empty($tenant_id)) {
            $condition[] = ['a.tenant_id', '=', $tenant_id];
        }
        $list = StoreSeckill::alias('a')
            ->join('store_product b', 'a.product_id = b.id')
            ->where([
                'a.is_del' => 0,
                'a.is_show' => 1,
            ])->where($condition)
            ->whereBetweenTimeField('start_day', 'stop_day')
            ->whereTime('start_time', '<=', time())
            ->whereTime('stop_time', '>=', time())
            ->field('a.id,a.product_id,a.title,a.image,a.info,a.stock,a.price,b.ot_price,start_time,stop_time,FROM_UNIXTIME( `start_day`,  "%Y-%m-%d" ) AS  start_day, FROM_UNIXTIME( `stop_day`,  "%Y-%m-%d" ) AS  stop_day')
            ->order('a.sort', 'desc');
        $count = $list->count();
        $list = $list->page($page, $limit)
            ->select();
        $list = $list->append(['goods_url','subscribe','start_timestamp','ot_price_text','price_text','start_date','quota_text']);
        return app('json')->successful(compact('list', 'count'));
    }

    /**
     * 秒杀产品详情
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function detail_new(Request $request, $id, $time = 0, $status = 1)
    {
        

        $activity_id = ActivityProduct::where('id', '=', $id)->value('activity_id');

        //        $storeInfo = StoreSeckill::getValidProduct($id);
        $storeInfo = ActivityProduct::getValidProduct($id);

        //        $siteUrl = sys_config('site_url');
        $siteUrl = config('site.default_site_url');

        if ($storeInfo)
            $storeInfo = $storeInfo->hidden(['cost', 'add_time', 'is_del'])->toArray();
        else
            $storeInfo = [];
        if (!$id || !$storeInfo) return app('json')->fail('商品不存在或已下架!');

        $id = $storeInfo['product_id'];

        $storeInfo['image'] = set_file_url($storeInfo['image'], $siteUrl);
        $storeInfo['image_base'] = set_file_url($storeInfo['image'], $siteUrl);
        $storeInfo['code_base'] = QrcodeService::getWechatQrcodePath($id . '_seckill_detail_wap.jpg', '/activity/seckill_detail/' . $id . '/' . $time . '/' . $status);
        $uid = $request->uid();
        $storeInfo['userLike'] = StoreProductRelation::isProductRelation($id, $uid, 'like', 'product_seckill');
        $storeInfo['like_num'] = StoreProductRelation::productRelationNum($id, 'like', 'product_seckill');
        $storeInfo['userCollect'] = StoreProductRelation::isProductRelation($storeInfo['product_id'], $uid, 'collect');
        $storeInfo['uid'] = $uid;
        $storeInfo['description'] = htmlspecialchars_decode(StoreDescription::getDescription($id, 0));
        $data['storeInfo'] = $storeInfo;
        StoreVisit::setView($uid, $id, 'seckill', $storeInfo['product_id'], 'viwe');
        $data['reply'] = StoreProductReply::getRecProductReply($storeInfo['product_id']);
        $data['replyCount'] = StoreProductReply::productValidWhere()->where('product_id', $storeInfo['product_id'])->count();
        if ($data['replyCount']) {
            $goodReply = StoreProductReply::productValidWhere()->where('product_id', $storeInfo['product_id'])->where('product_score', 5)->count();
            $data['replyChance'] = $goodReply;
            if ($goodReply) {
                $data['replyChance'] = bcdiv($goodReply, $data['replyCount'], 2);
                $data['replyChance'] = bcmul($data['replyChance'], 100, 3);
            }
        } else $data['replyChance'] = 0;
        //多商品规格
        if (StoreProduct::be(['spec_type' => 1, 'id' => $storeInfo['product_id']])) {
            list($productAttr, $productValue) = StoreProductAttr::getProductAttrDetailToActivity($id, $uid, 4, 1);
            foreach ($productValue as $k => $v) {
                $productValue[$k]['product_stock'] = StoreProductAttrValue::where('product_id', $storeInfo['product_id'])->where('suk', $v['suk'])->where('type', 0)->value('stock');
            }
            $data['productAttr'] = $productAttr;
            $data['productValue'] = $productValue;
        } else {
            //单商品规格
            $data['productAttr'] = [];
            $data['productValue'] = [];
            list($productAttr, $productValue) = StoreProductAttr::getProductAttrDetailToActivity($id, $uid, 4, 1);
            $value = $productValue['默认'] ?? [];
            $data['storeInfo']['product_stock'] = $value['stock'] ?? 0;
            $data['storeInfo']['quota'] = $value['quota'] ?? 0;
            $data['storeInfo']['quota_show'] = $value['quota_show'] ?? 0;
        }
        $data['isSeckillEnd'] = Activity::checkStatus($activity_id);
        return app('json')->successful($data);
    }


    /**
     * 秒杀产品详情-这个方法暂未用到，活动商品详情使用的是product控制器下的detail 而非本方法。
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function detail(Request $request, $id, $time = 0, $status = 1)
    {
        $storeInfo = StoreSeckill::getValidProduct($id);
        if ($storeInfo)
            $storeInfo = $storeInfo->hidden(['cost', 'add_time', 'is_del'])->toArray();
        else
            $storeInfo = [];
        if (!$id || !$storeInfo) return app('json')->fail('商品不存在或已下架!');

        $siteUrl = sys_config('site_url');
        $storeInfo['image'] = set_file_url($storeInfo['image'], $siteUrl);
        $storeInfo['image_base'] = set_file_url($storeInfo['image'], $siteUrl);
        $storeInfo['code_base'] = QrcodeService::getWechatQrcodePath($id . '_seckill_detail_wap.jpg', '/activity/seckill_detail/' . $id . '/' . $time . '/' . $status);
        $uid = $request->uid();
        $storeInfo['userLike'] = StoreProductRelation::isProductRelation($id, $uid, 'like', 'product_seckill');
        $storeInfo['like_num'] = StoreProductRelation::productRelationNum($id, 'like', 'product_seckill');
        $storeInfo['userCollect'] = StoreProductRelation::isProductRelation($storeInfo['product_id'], $uid, 'collect');
        $storeInfo['uid'] = $uid;
        $storeInfo['description'] = htmlspecialchars_decode(StoreDescription::getDescription($id, 1));
        $data['storeInfo'] = $storeInfo;
        StoreVisit::setView($uid, $id, 'seckill', $storeInfo['product_id'], 'viwe');
        $data['reply'] = StoreProductReply::getRecProductReply($storeInfo['product_id']);
        $data['replyCount'] = StoreProductReply::productValidWhere()->where('product_id', $storeInfo['product_id'])->count();
        if ($data['replyCount']) {
            $goodReply = StoreProductReply::productValidWhere()->where('product_id', $storeInfo['product_id'])->where('product_score', 5)->count();
            $data['replyChance'] = $goodReply;
            if ($goodReply) {
                $data['replyChance'] = bcdiv($goodReply, $data['replyCount'], 2);
                $data['replyChance'] = bcmul($data['replyChance'], 100, 3);
            }
        } else $data['replyChance'] = 0;
        if (StoreProduct::be(['spec_type' => 1, 'id' => $storeInfo['product_id']])) {
            list($productAttr, $productValue) = StoreProductAttr::getProductAttrDetail($id, $uid, 0, 1);
            foreach ($productValue as $k => $v) {
                $productValue[$k]['product_stock'] = StoreProductAttrValue::where('product_id', $storeInfo['product_id'])
                    ->where('suk', $v['suk'])
                    ->where('type', 0)
                    ->value('stock');
            }
            $data['productAttr'] = $productAttr;
            $data['productValue'] = $productValue;
        } else {
            $data['productAttr'] = [];
            $data['productValue'] = [];
            list($productAttr, $productValue) = StoreProductAttr::getProductAttrDetail($id, $uid, 0, 1);
            $value = $productValue['默认'] ?? [];
            $data['storeInfo']['product_stock'] = $value['stock'] ?? 0;
            $data['storeInfo']['quota'] = $value['quota'] ?? 0;
            $data['storeInfo']['quota_show'] = $value['quota_show'] ?? 0;
        }
        $data['isSeckillEnd'] = StoreSeckill::checkStatus($id);
        return app('json')->successful($data);
    }

    public function subscribe(Request $request)
    {
        $uid = $request->uid();

        $param = $request->param();
        $res = db('seckill_subscribe')
            ->where('uid', $uid)
            ->where('seckill_id', $param['id'])
            ->count();
        if ($res) {
            $text = '取消订阅成功';
            $type = 2;
            db('seckill_subscribe')
                ->where('uid', $uid)
                ->where('seckill_id', $param['id'])
                ->delete();
        } else {
            $text = '订阅成功';
            $type = 1;
            db('seckill_subscribe')->insert([
                'uid' => $uid,
                'seckill_id' => $param['id'],
            ]);
        }
        return app('json')->successful($text, compact('type'));
    }
}
