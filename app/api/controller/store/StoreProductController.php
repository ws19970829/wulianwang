<?php

namespace app\api\controller\store;

use app\admin\model\activity\Activity;
use app\admin\model\activity\ActivityCouponIssue;
use app\admin\model\activity\ActivityProductGift;
use app\admin\model\store\StoreDescription;
use app\admin\model\system\SystemAttachment;
use app\api\controller\PublicController;
use app\models\store\StoreOrder;
use app\models\store\StoreVisit;
use app\models\system\SystemStore;
use app\models\store\StoreProduct;
use app\models\store\StoreProductAttr;
use app\admin\model\store\StoreProductAttrValue;
use app\admin\model\store\StoreProductAttrResult;
use app\models\store\StoreProductRelation;
use app\admin\model\store\StoreProductCate;
use app\admin\model\system\SystemConfig;
use app\admin\model\ump\StoreSeckillAttrResult;
use app\models\store\StoreCouponIssue;
use app\superadmin\model\system\SystemAdmin;
use app\models\store\StoreProductReply;
use app\models\store\StoreSeckill;
use app\models\system\UserProductLog;
use app\models\user\Footprint;
use app\models\user\User;
use app\models\user\UserCollect;
use app\Request;
use crmeb\services\GroupDataService;
use crmeb\services\QrcodeService;
use crmeb\services\SystemConfigService;
use crmeb\services\UtilService;
use crmeb\services\upload\Upload;
use app\models\store\StoreCombination;

/**
 * 商品类
 * Class StoreProductController
 * @package app\api\controller\store
 */
class StoreProductController
{
    /**
     * 商品列表
     * @param Request $request
     * @return mixed
     */
    public function lst(Request $request)
    {
        $data = UtilService::getMore([
            [['sid', 'd'], 0],
            [['cid', 'd'], 0],
            [['tid', 'd'], 0],
            ['keyword', ''],
            ['tenant_id', 0],
            ['isadmin', 0],
            ['priceOrder', ''],
            ['salesOrder', ''],
            ['timeOrder', ''],
            [['news', 'd'], ''],
            [['page', 'd'], 0],
            [['limit', 'd'], 0],
            ['goods_type', 'goods_normal']
        ], $request);

        switch ($data['goods_type']) {
            case 'goods_normal':
                return app('json')->successful(StoreProduct::getProductList($data, $request->uid()));
            case 'goods_group':
                $condition = [];
                if (!empty($data['tenant_id'])) {
                    $tenant_id = $data['tenant_id'];
                    $condition[] = ['c.tenant_id', '=', $tenant_id];
                }
                $combinationList = StoreCombination::getAll($data['page'], $data['limit'], $condition);
                if (!count($combinationList)) return app('json')->successful([]);
                $count = $combinationList->count();
                $list = $combinationList->append(['goods_url', 'goods_num_text', 'quota_text', 'price_text', 'ot_price_text','com_id'])->hidden(['info', 'images', 'mer_id', 'attr', 'sort', 'stock', 'sales', 'add_time', 'is_del', 'is_show', 'browse', 'cost', 'is_show',  'postage', 'is_postage', 'is_host', 'mer_use', 'combination'])->toArray();
                if (!empty($list)) {
                    foreach ($list as &$v) {
                        $v['start_time'] = date('Y-m-d H:i:s', $v['start_time']);
                        $v['stop_time'] = date('Y-m-d H:i:s', $v['stop_time']);
                    }
                }
                return app('json')->successful(compact('count', 'list'));
            case 'goods_spike':
                //秒杀
                $condition = [];
                if (!empty($data['tenant_id'])) {
                    $tenant_id = $data['tenant_id'];
                    $condition[] = ['a.tenant_id', '=', $tenant_id];
                }
                $list = StoreSeckill::alias('a')
                    ->join('store_product b', 'a.product_id = b.id')
                    ->where([
                        'a.is_del' => 0,
                        'a.is_show' => 1,
                    ])->where($condition)
                    ->whereBetweenTimeField('start_day', 'stop_day')
                    //->whereTime('start_time', '<=', time())
                    ->whereTime('stop_time', '>=', time())
                    ->field('a.id,a.product_id,a.title,a.image,a.info,a.quota,a.price,a.ot_price,start_time,stop_time,FROM_UNIXTIME( `start_day`,  "%Y-%m-%d" ) AS  start_day, FROM_UNIXTIME( `stop_day`,  "%Y-%m-%d" ) AS  stop_day')
                    ->order('a.sort', 'desc');
                $count = $list->count();
                $list = $list->page($data['page'], $data['limit'])
                    ->select();
                $list = $list->append(['goods_url', 'subscribe', 'start_timestamp','end_timestamp', 'ot_price_text', 'price_text', 'start_date', 'quota_text', 'status', 'status_text', 'seckill_id']);
                return app('json')->successful(compact('list', 'count'));
            default:
                return app('json')->successful(StoreProduct::getProductList($data, $request->uid()));
        }
    }

    /**
     * @Author  lingyun
     * @Desc    精品推荐商品 - 代销商品
     * @param Request $request
     */
    public function boutique_list(Request $request)
    {
        $data = UtilService::getMore([
            [['sid', 'd'], 0],
            [['cid', 'd'], 0],
            ['keyword', ''],
            ['tenant_id', 0],
            ['isadmin', 0],
            ['is_boutique', 1],
            ['priceOrder', ''],
            ['salesOrder', ''],
            [['news', 'd'], 0],
            [['page', 'd'], 0],
            [['limit', 'd'], 0],
            [['type', 0], 0]
        ], $request);

        return app('json')->successful(StoreProduct::getProductList($data, $request->uid()));
    }

    /**
     * 产品分享二维码 推广员
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function code(Request $request, $id)
    {
        if (!$id || !($storeInfo = StoreProduct::getValidProduct($id, 'id'))) return app('json')->fail('商品不存在或已下架');
        $userType = $request->get('user_type', 'wechat');
        $user = $request->user();
        try {
            switch ($userType) {
                case 'wechat':
                    //公众号
                    $name = $id . '_product_detail_' . $user['uid'] . '_is_promoter_' . $user['is_promoter'] . '_wap.jpg';
                    $url = QrcodeService::getWechatQrcodePath($name, '/detail/' . $id . '?spread=' . $user['uid']);
                    if ($url === false)
                        return app('json')->fail('二维码生成失败');
                    else
                        return app('json')->successful(['code' => image_to_base64($url)]);
                    break;
                case 'routine':
                    //小程序
                    $name = $id . '_' . $user['uid'] . '_' . $user['is_promoter'] . '_product.jpg';
                    $imageInfo = SystemAttachment::getInfo($name, 'name');
                    $siteUrl = sys_config('site_url');
                    if (!$imageInfo) {
                        $data = 'id=' . $id;
                        if ($user['is_promoter'] || sys_config('store_brokerage_statu') == 2) $data .= '&pid=' . $user['uid'];
                        $res = \app\models\routine\RoutineCode::getPageCode('pages/goods_details/index', $data, 280);
                        if (!$res) return app('json')->fail('二维码生成失败');
                        $uploadType = (int) sys_config('upload_type', 1);
                        $upload = new Upload($uploadType, [
                            'accessKey' => sys_config('accessKey'),
                            'secretKey' => sys_config('secretKey'),
                            'uploadUrl' => sys_config('uploadUrl'),
                            'storageName' => sys_config('storage_name'),
                            'storageRegion' => sys_config('storage_region'),
                        ]);
                        $upload->delete($name);
                        $res = $upload->to('routine/product')->validate()->stream($res, $name);
                        if ($res === false) {
                            return app('json')->fail($upload->getError());
                        }
                        $imageInfo = $upload->getUploadInfo();
                        $imageInfo['image_type'] = $uploadType;
                        if ($imageInfo['image_type'] == 1) $remoteImage = UtilService::remoteImage($siteUrl . $imageInfo['dir']);
                        else $remoteImage = UtilService::remoteImage($imageInfo['dir']);
                        if (!$remoteImage['status']) return app('json')->fail('小程序二维码未能生成');
                        SystemAttachment::attachmentAdd($imageInfo['name'], $imageInfo['size'], $imageInfo['type'], $imageInfo['dir'], $imageInfo['thumb_path'], 1, $imageInfo['image_type'], $imageInfo['time'], 2);
                        $url = $imageInfo['dir'];
                    } else $url = $imageInfo['att_dir'];
                    if ($imageInfo['image_type'] == 1) $url = $siteUrl . $url;
                    return app('json')->successful(['code' => $url]);
            }
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage(), [
                'code' => $e->getCode(),
                'line' => $e->getLine(),
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * 产品详情
     * @param Request $request
     * @param $id
     * @param int $type
     * @return mixed
     */
    public function detail(Request $request, $id, $type = 0)
    {
        if (!$id || !($storeInfo = StoreProduct::getValidProduct($id))) return app('json')->fail('商品不存在或已下架');

        $product_id = $id;

        $tenant_id = input('param.tenant_id');
        $publicController = new PublicController();
        $siteUrl = $publicController->getSysConfigValue('site_url', $tenant_id);

        $storeInfo['image'] = set_file_url($storeInfo['image'], $siteUrl);
        $storeInfo['image_base'] = set_file_url($storeInfo['image'], $siteUrl);
        $storeInfo['code_base'] = QrcodeService::getWechatQrcodePath($id . '_product_detail_wap.jpg', '/detail/' . $id);
        $uid = $request->uid();
        $data['uid'] = $uid;
        $storeInfo['description'] = htmlspecialchars_decode(StoreDescription::getDescription($id));
        //替换windows服务器下正反斜杠问题导致图片无法显示
        $storeInfo['description'] = preg_replace_callback('#<img.*?src="([^"]*)"[^>]*>#i', function ($imagsSrc) {
            return isset($imagsSrc[1]) && isset($imagsSrc[0]) ? str_replace($imagsSrc[1], str_replace('\\', '/', $imagsSrc[1]), $imagsSrc[0]) : '';
        }, $storeInfo['description']);

        $storeInfo['userCollect'] = StoreProductRelation::isProductRelation($id, $uid, 'collect');
        $storeInfo['userLike'] = StoreProductRelation::isProductRelation($id, $uid, 'like');

        //还需要处理显示在详情页面的主价格。另外，需要处理对优惠券领取和使用的限制。-另外 下单以后的订单详情价格显示不正确
        $activity_id = input('param.activity_id');
        $activity_type = 0;
        if ($activity_id) {
            $activity_type = Activity::where('id', '=', $activity_id)->value('type');
        }

        $storeInfo['product_collect'] = (new UserCollect)->is_collect(1, $id);
        $storeInfo['shop_collect'] = (new UserCollect)->is_collect(2, $storeInfo['tenant_id']);

        //处理活动的剩余时间、库存显示、价格等
        $activity_info = [];
        $gift_list = [];
        $gift_coupon_list = [];
        list($productAttr, $productValue) = StoreProductAttr::getProductAttrDetail($id, $uid, $type, 0);
        
        //        dump($productValue);exit;
        $attrValue = $productValue;
        // if (!$storeInfo['spec_type']) {
        //     $productAttr = [];
        //     $productValue = [];
        // }


        //        //对规格进行排序
        //        $prices = array_column($productValue, 'price');
        //        array_multisort($prices, SORT_ASC, SORT_NUMERIC, $productValue);
        //        $keys = array_keys($productValue);
        //        $productValue = array_combine($keys, $productValue);
        StoreVisit::setView($uid, $id, 'product', $storeInfo['cate_id'], 'viwe');
        $data['storeInfo'] = StoreProduct::setLevelPrice($storeInfo, $uid, true);
        $data['similarity'] = StoreProduct::cateIdBySimilarityProduct($storeInfo['cate_id'], 'id,store_name,image,price,sales,ficti', 4);
        $data['productAttr'] = $productAttr;
        $data['productValue'] = $productValue;
        $data['activity_info'] = $activity_info;
        $data['gift_list'] = $gift_list;
        $data['gift_coupon_list'] = $gift_coupon_list;
        $data['priceName'] = 0;
     
        if ($uid) {
            $user = $request->user();
            if (!$user->is_promoter) {
                $price = StoreOrder::where(['paid' => 1, 'refund_status' => 0, 'uid' => $uid])->sum('pay_price');
                $status = is_brokerage_statu($price);
                if ($status) {
                    User::where('uid', $uid)->update(['is_promoter' => 1]);
                    $user->is_promoter = 1;
                }
            }
            if ($user->is_promoter) {
                $data['priceName'] = StoreProduct::getPacketPrice($storeInfo, $attrValue);
            }
            if (!strlen(trim($data['priceName'])))
                $data['priceName'] = 0;
        }
        $data['reply'] = StoreProductReply::getRecProductReply($storeInfo['id']);
        $data['replyCount'] = StoreProductReply::productValidWhere()->where('product_id', $storeInfo['id'])->count();
        if ($data['replyCount']) {
            $goodReply = StoreProductReply::productValidWhere()->where('product_id', $storeInfo['id'])->where('product_score', 5)->count();
            $data['replyChance'] = $goodReply;
            if ($goodReply) {
                $data['replyChance'] = bcdiv($goodReply, $data['replyCount'], 2);
                $data['replyChance'] = bcmul($data['replyChance'], 100, 2);
            }
        } else $data['replyChance'] = 0;
        $data['mer_id'] = $storeInfo['mer_id'];
        $data['system_store'] = ($res = SystemStore::getStoreDispose()) ? $res : [];
        $data['good_list'] = StoreProduct::getGoodList(18, 'image,store_name,price,id,ot_price', $tenant_id);
        //TODO:腾讯地图秘钥，应该考虑使用平台方统一的
        $data['mapKey'] = sys_config('tengxun_map_key');
        //        $data['store_self_mention'] = (int)sys_config('store_self_mention') ?? 0;//门店自提是否开启
        $store_self_mention = (new PublicController())->getSysConfigValue('store_self_mention', $tenant_id);
        $data['store_self_mention'] = (int) $store_self_mention ?? 0; //门店自提是否开启
        $data['activity'] = StoreProduct::activity($data['storeInfo']['id'], false);

        //详情访问，增加产品的访问次数
        if ($uid != 0) {
            UserProductLog::setCreateLog($product_id, $uid, 2); //访问次数

            //添加足迹
            Footprint::footprint($uid, $id);
        }

        $data['shop'] = SystemAdmin::where('id', $storeInfo['tenant_id'])
            ->field('id,real_name,logo_img,remark')
            ->find() ?? [];
        if (!empty($data['shop'])) {
            $data['shop'] = $data['shop']->append(['logo_img_filter']);
            $data['shop']['coupon'] = StoreCouponIssue::getIssueCouponList($uid, 0, 0, 1, 0, $storeInfo['tenant_id']);
            $data['shop']['customer_mobile'] = SystemConfig::getConfigValue('server_tel', $data['shop']['id']) ?? '';
        }

        $data['activity_info'] = [];
        $data['activity_productAttr'] = [];
        $data['activity_productValue'] = [];

        if ($request->has('seckill_id')) {
            // 秒杀信息
            $seckill_id = $request->param('seckill_id');
            $data['activity_info'] =  StoreSeckill::alias('a')
                ->join('store_product b', 'a.product_id = b.id')
                ->where([
                    'a.is_del' => 0,
                    'a.is_show' => 1,
                ])
                ->field('a.id,a.product_id,a.title,a.image,a.info,a.quota,a.price,a.ot_price,start_time,stop_time,FROM_UNIXTIME( `start_day`,  "%Y-%m-%d" ) AS  start_day, FROM_UNIXTIME( `stop_day`,  "%Y-%m-%d" ) AS  stop_day,num')
                ->append(['timestamp', 'subscribe', 'price_text', 'ot_price_text', 'start_date', 'quota_text', 'start_timestamp','stop_timestamp', 'end_timestamp', 'status', 'status_text', 'notice_title', 'notice_context'])
                ->find($request->param('seckill_id')) ?? [];
            list($seckill_productAttr, $seckill_productValue) = StoreProductAttr::getProductAttrDetail($id, $uid, 1, $seckill_id);
            $data['activity_productAttr'] = $seckill_productAttr;
            $data['activity_productValue'] = $seckill_productValue;
        }

        if ($request->has('com_id')) {
            // 拼团信息
            $com_id = $request->param('com_id');
            $data['activity_info'] = StoreCombination::alias('a')
                ->join('store_product b', 'a.product_id=b.id')
                ->where('a.is_show', 1)
                ->where('a.is_host', 1)
                ->where('a.is_del', 0)
                ->limit(5)
                ->order('a.sort')
                ->field('a.id,a.product_id,a.image,a.title,a.info,a.stock,quota,goods_num,a.price,a.ot_price,FROM_UNIXTIME( `start_time`,  "%Y-%m-%d %H:%i:%S" ) AS  start_time , FROM_UNIXTIME( `stop_time`,  "%Y-%m-%d %H:%i:%S" ) AS  stop_time,num')
                ->append(['price_text', 'ot_price_text', 'goods_num_text', 'quota_text', 'notice_title', 'notice_context'])
                ->find($com_id) ?? [];
            list($com_productAttr, $com_productValue) = StoreProductAttr::getProductAttrDetail($id, $uid, 3, $com_id);
            $data['activity_productAttr'] = $com_productAttr;
            $data['activity_productValue'] = $com_productValue;
        }
        return app('json')->successful($data);
    }

    /**
     * 为你推荐
     *
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function product_hot(Request $request)
    {
        list($page, $limit) = UtilService::getMore([
            [['page', 'd'], 0],
            [['limit', 'd'], 0]
        ], $request, true);
        $uid = $request->uid();
        if (!$limit) return app('json')->successful([]);

        $tenant_id = User::getTenantIDbyUID($uid);
        $productHot = StoreProduct::getHotProductLoading('id,image,store_name,cate_id,price,unit_name,ot_price', (int) $page, (int) $limit, $uid, $tenant_id);
        if (!empty($productHot)) {
            foreach ($productHot as $k => $v) {
                $productHot[$k]['activity'] = StoreProduct::activity($v['id']);
            }
        }
        return app('json')->successful($productHot);
    }

    /**
     * 获取首页推荐不同类型产品的轮播图和产品
     * @param Request $request
     * @param $type
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function groom_list(Request $request, $type)
    {
        list($page, $limit) = UtilService::getMore([
            [['page', 'd'], 0],
            [['limit', 'd'], 0]
        ], $request, true);
        $info['banner'] = [];
        $info['list'] = [];
        if ($type == 1) { //TODO 精品推荐
            $info['banner'] = sys_data('routine_home_bast_banner') ?: []; //TODO 首页精品推荐图片
            $info['list'] = StoreProduct::getBestProduct('id,image,store_name,cate_id,price,ot_price,IFNULL(sales,0) + IFNULL(ficti,0) as sales,unit_name,sort', 0, 0, true, $page, $limit); //TODO 精品推荐个数
        } else if ($type == 2) { //TODO  热门榜单
            $info['banner'] = sys_data('routine_home_hot_banner') ?: []; //TODO 热门榜单 猜你喜欢推荐图片
            $info['list'] = StoreProduct::getHotProduct('id,image,store_name,cate_id,price,ot_price,unit_name,sort,IFNULL(sales,0) + IFNULL(ficti,0) as sales', 0, $request->uid(), $page, $limit); //TODO 热门榜单 猜你喜欢
        } else if ($type == 3) { //TODO 首发新品
            $info['banner'] = sys_data('routine_home_new_banner') ?: []; //TODO 首发新品推荐图片
            $info['list'] = StoreProduct::getNewProduct('id,image,store_name,cate_id,price,ot_price,unit_name,sort,IFNULL(sales,0) + IFNULL(ficti,0) as sales', 0, $request->uid(), true, $page, $limit); //TODO 首发新品
        } else if ($type == 4) { //TODO 促销单品
            $info['banner'] = sys_data('routine_home_benefit_banner') ?: []; //TODO 促销单品推荐图片
            $info['list'] = StoreProduct::getBenefitProduct('id,image,store_name,cate_id,price,ot_price,stock,unit_name,sort', 0, $page, $limit); //TODO 促销单品
        }
        return app('json')->successful($info);
    }

    /**
     * 产品评价数量和好评度
     * @param $id
     * @return mixed
     */
    public function reply_config($id)
    {
        if (!$id || !is_numeric($id)) return app('json')->fail('参数错误!');
        return app('json')->successful(StoreProductReply::productReplyCount($id));
    }

    /**
     * 获取产品评论
     * @param Request $request
     * @param $id
     * @param $type
     * @return mixed
     */
    public function reply_list(Request $request, $id)
    {
        list($page, $limit, $type) = UtilService::getMore([
            [['page', 'd'], 0], [['limit', 'd'], 0], [['type', 'd'], 0]
        ], $request, true);
        if (!$id || !is_numeric($id)) return app('json')->fail('参数错误!');
        $list = StoreProductReply::getProductReplyList($id, (int) $type, $page, $limit);
        return app('json')->successful($list);
    }

    /**
     * @Author  lingyun
     * @Desc    代销商品
     * @param Request $request
     */
    public function consignment_product(Request $request)
    {
        list($product_id, $product_unique, $product_price) = UtilService::postMore([
            ['product_id', ''],
            ['product_unique', ''],
            ['product_price', ''],
        ], $request, true);

        if (empty($product_unique)) {
            $res = StoreProductAttrValue::where('product_id', $product_id)->select()->toArray();
            $product_unique = array_column($res, 'unique');
        }

        $user = $request->user()->toArray();
        if (empty($product_id)) return app('json')->fail('商品参数有误');

        //        $product_unique = ['98dd9e4f','ff4f4aeb','69759ab3','886c099a','ac2eba2d','d411c455','031f3e51','c554ed33','3b32844d','a08fd92c','4a40a48c','aaa794ec','0609dd63','335d0328','5872428b','75eb1ca4','c5064a45','1328f749','15510bc9','5076756e','6626bf3e','4d8901b9','808865c8','9f9ad7fa','4c1f2ee8','39fda772','617353a0','6766b57f','9dd7841f','d0c3f545','52426e54','bc73f4d7','af02c9ae','1265764a','9cf390f7','fcdf595b','7fa3e465','02164063','cd5963a2','16929439','45e69d0b','705de6c0','8dc243d6','7e11ec43','a36b8527','4abb3fd6','749bf547','4bd85962','b054230d','34d127e9','0bfe3c4b','ae89ca1d','34296077','f37a8bc2','84b7d321','d585c266','baa37005','08fbe497','72d43f6c','7ded8605','4da7baa3','fa207e34','062aa71c','5482f5b8','34264142','5af47faf','9fbf6c10','3d725435','563c000e','44fed9af','58bb7459','311ef963','391ca77e','b02062ca','a10d1ece'];
        //        $product_price = ['200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200','200'];

        $result = (new StoreProduct())->consignmentProduct($product_id, $user, $product_price, $product_unique);

        if ($result !== true) {
            return app('json')->fail(StoreProduct::getErrorInfo());
        }

        return app('json')->successful('代销成功');
    }
}
