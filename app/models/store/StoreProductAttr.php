<?php

/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/13
 */

namespace app\models\store;


use crmeb\basic\BaseModel;
use think\facade\Db;
use crmeb\traits\ModelTrait;

/**
 * TODO  产品属性Model
 * Class StoreProductAttr
 * @package app\models\store
 */
class StoreProductAttr extends BaseModel
{
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_product_attr';

    use ModelTrait;

    protected function getAttrValuesAttr($value)
    {
        return explode(',', $value);
    }

    public static function storeProductAttrValueDb()
    {
        return Db::name('StoreProductAttrValue');
    }


    /**
     * 获取商品属性数据-给活动详情改装的方法
     * @param $productId
     * @return array
     */
    public static function getProductAttrDetailToActivity($productId, $uid = 0, $type = 0, $type_id = 0)
    {

        $attrDetail = self::where('product_id', $productId)
            ->where('type', 0)
            ->order('attr_values asc')
            ->select()
            ->toArray() ?: [];
        $_values = self::storeProductAttrValueDb()
            ->where('product_id', $productId)
            ->where('type', $type)
            ->select();
        $values = [];
        foreach ($_values as $value) {
            if ($type) {
                if ($uid)
                    $value['cart_num'] = StoreCart::where('product_attr_unique', $value['unique'])
                        ->where('is_pay', 0)
                        ->where('is_del', 0)
                        ->where('is_new', 0)
                        ->where('type', 'product')
                        ->where('product_id', $productId)
                        ->where('uid', $uid)
                        ->value('cart_num');
                else
                    $value['cart_num'] = 0;
                if (is_null($value['cart_num'])) $value['cart_num'] = 0;
            }
            unset($value['cost']);
            $values[$value['suk']] = $value;
        }
        foreach ($attrDetail as $k => $v) {
            $attr = $v['attr_values'];
            //            unset($productAttr[$k]['attr_values']);
            foreach ($attr as $kk => $vv) {
                $attrDetail[$k]['attr_value'][$kk]['attr'] = $vv;
                $attrDetail[$k]['attr_value'][$kk]['check'] = false;
            }
        }
        return [$attrDetail, $values];
    }



    /**
     * 获取商品属性数据-给活动详情接口改装的方法
     * @param $productId
     * @param int $uid
     * @param int $type 活动类型
     * @param int $type_id
     * @param int $activity_id 活动id
     * @return array
     */
    public static function getProductAttrDetailToActivityToApi($productId, $uid = 0, $type = 0, $type_id = 0, $activity_id = 0)
    {

        $attrDetail = self::where('product_id', $productId)
            ->where('type', 0)
            ->order('attr_values asc')
            ->select()
            ->toArray() ?: [];
        $_values = self::storeProductAttrValueDb()
            ->where('product_id', $productId)
            ->where('type', $type)
            ->where('activity_id', '=', $activity_id)
            ->select();
        $values = [];
        foreach ($_values as $value) {
            if ($type) {
                if ($uid)
                    $value['cart_num'] = StoreCart::where('product_attr_unique', $value['unique'])
                        ->where('is_pay', 0)
                        ->where('is_del', 0)
                        ->where('is_new', 0)
                        ->where('type', 'product')
                        ->where('product_id', $productId)
                        ->where('uid', $uid)
                        ->value('cart_num');
                else
                    $value['cart_num'] = 0;
                if (is_null($value['cart_num'])) $value['cart_num'] = 0;
            }
            $value['price'] = $value['activity_price'];
            $value['stock'] = $value['activity_stock'] > 0 ? $value['activity_stock'] : 0;
            unset($value['cost']);
            $values[$value['suk']] = $value;
        }
        foreach ($attrDetail as $k => $v) {
            $attr = $v['attr_values'];
            //            unset($productAttr[$k]['attr_values']);
            foreach ($attr as $kk => $vv) {
                $attrDetail[$k]['attr_value'][$kk]['attr'] = $vv;
                $attrDetail[$k]['attr_value'][$kk]['check'] = false;
            }
        }
        return [$attrDetail, $values];
    }


    /**
     * 获取商品属性数据
     * @param $productId
     * @return array
     */
    public static function getProductAttrDetail($productId, $uid = 0, $type = 0, $activity_id = 0)
    {
        $unit = StoreProduct::where('id', $productId)->value('unit_name') ?? '件';
        $attrDetail = self::where('product_id', $productId)
            ->where('type', $type)
            ->where('activity_id', $activity_id)
            ->order('attr_values asc')
            ->select()
            ->toArray() ?: [];

        $_values = self::storeProductAttrValueDb()
            ->where('product_id', $productId)
            ->where('is_del', 0)
            ->where('activity_id', $activity_id)
            ->where('type', $type)
            ->order('sort', 'desc')
            ->select();

        $values = [];
        foreach ($_values as $key => $value) {
            if ($type) {
                $res =  self::storeProductAttrValueDb()
                    ->where('suk', $value['suk'])
                    ->where('product_id', $productId)
                    ->where('type', 0)
                    ->count();
                if (!$res) {
                    unset($_values[$key]);
                    continue;
                }
                if ($uid)
                    $value['cart_num'] = StoreCart::where('product_attr_unique', $value['unique'])
                        ->where('is_pay', 0)
                        ->where('is_del', 0)
                        ->where('is_new', 0)
                        ->where('type', 'product')
                        ->where('product_id', $productId)
                        ->where('uid', $uid)
                        ->value('cart_num');
                else
                    $value['cart_num'] = 0;
                if (is_null($value['cart_num'])) $value['cart_num'] = 0;
                if ($value['stock'] > 0) {
                    $value['stock_text'] = '库存：' . $value['stock'];
                } else {
                    $value['stock_text'] = '无货';
                }
            } else {
                if ($value['stock'] >= $value['moq']) {
                    if ($value['advance_sale']) {
                        $value['stock_text'] = '无货预售，预计下单后' . $value['advance_time'] . '天发货';
                    } else {
                        $value['stock_text'] = '库存：' . $value['stock'];
                    }
                } else {
                    $value['stock_text'] = '无货';
                }
            }
            $value['discount_text'] = ">={$value['discount']}{$unit} ￥{$value['discount_gt']}/{$unit}， <{$value['discount']}{$unit} ￥{$value['discount_lt']}/{$unit}";
            unset($value['cost']);
            if ($value['type'] > 0) $value['stock'] = $value['quota'];
            $values[$value['suk']] = $value;
        }


        foreach ($attrDetail as $k => $v) {
            $attr = $v['attr_values'];
            //            unset($productAttr[$k]['attr_values']);
            foreach ($attr as $kk => $vv) {

                $attrDetail[$k]['attr_value'][$kk]['attr'] = $vv;
                $attrDetail[$k]['attr_value'][$kk]['check'] = false;
            }
        }

        return [$attrDetail, $values];
    }




    public static function uniqueByStock($unique)
    {
        return self::storeProductAttrValueDb()->where('unique', $unique)->value('stock') ?: 0;
    }

    public static function uniqueByAttrInfo($unique, $field = '*', $type, $activity_id = 0)
    {
        $model = self::storeProductAttrValueDb()->field($field)->where('unique', $unique);
        if ($activity_id) {
            $model = $model->where('activity_id', '=', $activity_id)->where('type', $type)->find();
            $model['stock'] = $model['quota'];
            return $model;
        } else {
            $model = $model->where('activity_id', '=', 0)->where('type', 0)->find();
            return $model;
        }
    }

    public static function issetProductUnique($productId, $unique)
    {
        //        $res = self::be(['product_id'=>$productId]);

        // $res = self::where('product_id', $productId)->where('type', 0)->find();
        $res = true;
        if ($unique) {
            return $res && self::storeProductAttrValueDb()->where('product_id', $productId)->where('unique', $unique)->where('type', 0)->count() > 0;
        } else {
            return !$res;
        }
    }
}
