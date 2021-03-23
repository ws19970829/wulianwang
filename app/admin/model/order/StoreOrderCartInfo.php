<?php

/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/26
 */

namespace app\admin\model\order;


use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class StoreOrderCartInfo extends BaseModel
{

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_order_cart_info';

    use ModelTrait;

    /** 获取订单产品列表
     * @param $oid
     * @return array
     */
    public static function getProductNameList($oid)
    {
        $cartInfo = self::where('oid', $oid)->select();
        $goodsName = [];
        foreach ($cartInfo as $cart) {
            if (isset($cart['cart_info']['productInfo'])) {
                $suk = isset($cart['cart_info']['productInfo']['attrInfo']) ? '(' . $cart['cart_info']['productInfo']['attrInfo']['suk'] . ')' : '';
                $goodsName[] = $cart['cart_info']['productInfo']['store_name'] . $suk;
            } else {
                $goodsName[] = '';
            }
        }
        return $goodsName;
    }

    public function getCartInfoFilterAttr($v, $data)
    {
        $data =  json_decode($data['cart_info'], true);
        if (!$data) return [];
        if (!$data['type']) {
            if ($data['cart_num'] >= $data['discount']) {
                $data['unit_price'] = $data['discount_gt'];
            } else {
                $data['unit_price'] = $data['discount_lt'];
            }
        } else {
            $data['unit_price'] = $data['productInfo']['price'];
        }
        $data['unit_name'] = db('store_product')->where('id', $data['product_id'])->value('unit_name') ?? '件';
        $data['total'] = bcmul($data['unit_price'], $data['cart_num'], 2);
        return $data;
    }
}
