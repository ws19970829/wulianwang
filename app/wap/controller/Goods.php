<?php

/**
 * 首页控制类
 * @author 大梦
 * @DateTime 2019/07/06
 */

namespace app\wap\controller;

use app\admin\model\store\StoreProductAttrValue;
use app\models\store\StoreCouponIssue;
use app\models\store\StoreProduct;
use think\Request;
use think\Validate;
use think\facade\View;

class Goods extends Common
{

    /**
     * [index description]
     * @author Meng
     * @dateTime 2020-09-23
     * @return   [type]     [description]
     */
    public function index()
    {
        $params = input();
        $data = [
            'info' => '',
        ];
        $this->assign($data);
        return view();
    }

    /**
     * [商品详情]
     * @author Meng
     * @dateTime 2020-09-23
     * @return   [type]     [description]
     */
    public function detail(Request $request)
    {
        $params = $request->param();
        $uid = $request->uid();

        $data = StoreProduct::with(['shop'])->find($params['id'])->append(['content', 'shop_collect']);
        $data->shop->logo_img = json_decode($data->shop->logo_img, true)[0] ?? '';
        $type = 0;
        $activity_id = 0;
        if (!empty($params['seckill_id'])) {
            $type = 1;
            $activity_id = $params['seckill_id'];
        } elseif (!empty($params['combination_id'])) {
            $type = 3;
            $activity_id = $params['combination_id'];
        }
        $attr_value = StoreProductAttrValue::where('type', $type)
            ->where('activity_id', $activity_id)
            ->where('is_del', 0)
            ->where('product_id', $params['id'])
            ->select()
            ->toArray();
        if (!empty($activity_id)) {
            foreach ($attr_value as $k => $v) {
                $res =  StoreProductAttrValue::where('suk', $v['suk'])
                    ->where('product_id', $params['id'])
                    ->where('type', 0)
                    ->count();
                if (!$res) {
                    unset($attr_value[$k]);
                    continue;
                }
            }
        }

        //店铺优惠券
        $coupon = StoreCouponIssue::getIssueCouponList($uid, 0, 0, 1, 0, $data->shop->id, 1, 0, 2);

        $empty_html = "<div>无</div>";

        $token = db('user_token')->where('uid', $uid)->order('id', 'desc')->value('token');
        if (empty($token)) {
            $uid = 0;
        } else {
            $token = 'Bearer ' . $token;
        }
        $json = [];
        foreach ($attr_value as $v) {
            $json[$v['unique']] = ['discount' => $v['discount'], 'discount_gt' => $v['discount_gt'], 'discount_lt' => $v['discount_lt'], 'moq' => $v['moq']];
        }
        $json = json_encode($json, JSON_UNESCAPED_UNICODE);
        $this->assign(compact('data', 'coupon', 'empty_html', 'uid', 'token', 'attr_value', 'json'));
        return view();
    }
}
