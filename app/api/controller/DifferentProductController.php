<?php

namespace app\api\controller;

use app\admin\model\store\StoreDescription;
use app\admin\model\system\SystemAttachment;
use app\admin\model\system\SystemConfig;
use app\admin\model\system\SystemGroup;
use app\models\store\StoreCategory;
use app\models\store\StoreCouponIssue;
use app\models\store\StoreOrder;
use app\models\store\StorePink;
use app\models\store\StoreProduct;
use app\models\store\StoreProductRelation;
use app\models\store\StoreProductAttr;
use app\models\store\StoreService;
use app\models\store\StoreVisit;
use app\models\system\Express;
use app\models\system\SystemCity;
use app\models\system\SystemStore;
use app\models\system\SystemStoreStaff;
use app\models\store\StoreProductReply;
use app\models\user\SystemAdmin;
use app\models\user\User;
use app\models\user\UserBill;
use app\models\user\WechatUser;
use app\Request;
use crmeb\services\CacheService;
use crmeb\services\QrcodeService;
use crmeb\services\UtilService;
use crmeb\services\workerman\ChannelService;
use think\facade\Cache;
use crmeb\services\upload\Upload;

/**
 * 后台异业适配公共类
 * Class PublicController
 * @package app\api\controller
 */
class DifferentProductController
{
    /**
     * 后台异业商品列表页
     * @param $menu_name
     * @param $tenant_id
     * @return mixed|string
     */
    public function admin_index(Request $request)
    {
        list($price_order, $sale_order,$category_id, $second_category_id,$page, $limit) = UtilService::getMore([
            ['price_order', ''],
            ['sale_order', ''],
            ['category_id',''],
            ['second_category_id',''],
            ['page', 1],
            ['limit', 10]
        ], $request, true);
        //首页分类
        $category_list = StoreCategory::getPidCategoryList();
        foreach($category_list as $k => $v){
            $category_list[$k]['status'] = 0;
            if($v['id'] == $category_id){
                $category_list[$k]['status'] = 1;
            }
        }
        //商品分类以及二级分类
        $category_second_list = StoreCategory::getSecondCategoryList($category_id);
        foreach($category_second_list as $kk => $vv){
            $category_list[$kk]['status'] = 0;
            if($vv['id'] == $second_category_id){
                $category_list[$kk]['status'] = 1;
            }
        }
        //商品列表
        $store_product_list = StoreProduct::getDifferentStoreProductList($price_order,$sale_order,$page,$limit,$second_category_id);
        $data = array();
        $data['home_category_list'] = $category_list;
        $data['category_list'] = $category_second_list;
        $data['store_product_list'] = $store_product_list;

        return app('json')->successful($data);

    }


    /**
     * 后台异业商品详情页
     * @param $menu_name
     * @param $tenant_id
     * @return mixed|string
     */
    public function different_product_detail(Request $request,$type = 0)
    {
        list($id) = UtilService::postMore([
            ['id', ''],
        ], $request, true);
        if (!$id || !($storeInfo = StoreProduct::getValidProduct($id))) return app('json')->fail('商品不存在或已下架');

        /* $tenant_id=input('param.tenant_id');
         $siteUrl = $this->getSysConfigValue('site_url',$tenant_id);
         $storeInfo['image'] = set_file_url($storeInfo['image'], $siteUrl);
         $storeInfo['image_base'] = set_file_url($storeInfo['image'], $siteUrl);
         $storeInfo['code_base'] = QrcodeService::getWechatQrcodePath($id . '_product_detail_wap.jpg', '/detail/' . $id);*/
        $siteUrl = sys_config('site_url');
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
        list($productAttr, $productValue) = StoreProductAttr::getProductAttrDetail($id, $uid, $type);
        $attrValue = $productValue;
        if (!$storeInfo['spec_type']) {
            $productAttr = [];
            $productValue = [];
        }
//        //对规格进行排序
//        $prices = array_column($productValue, 'price');
//        array_multisort($prices, SORT_ASC, SORT_NUMERIC, $productValue);
//        $keys = array_keys($productValue);
//        $productValue = array_combine($keys, $productValue);
        StoreVisit::setView($uid, $id, 'product',$storeInfo['cate_id'], 'viwe');
        $data['storeInfo'] = StoreProduct::setLevelPrice($storeInfo, $uid, true);
        $data['productAttr'] = $productAttr;
        $data['productValue'] = $productValue;
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
        $data['mer_name'] = SystemAdmin::where('id',$storeInfo['mer_id'])->value('real_name');
        $data['system_store'] = ($res = SystemStore::getStoreDispose()) ? $res : [];
        $data['mapKey'] = sys_config('tengxun_map_key');
        $data['store_self_mention'] = (int)sys_config('store_self_mention') ?? 0;//门店自提是否开启
        $data['activity'] = StoreProduct::activity($data['storeInfo']['id'], false);
        return app('json')->successful($data);
    }

    //
}