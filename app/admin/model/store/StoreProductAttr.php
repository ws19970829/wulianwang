<?php

/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/08
 */

namespace app\admin\model\store;

use app\models\store\StoreProduct;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class StoreProductAttr extends BaseModel
{

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_product_attr';

    use ModelTrait;

    protected function setAttrValuesAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    protected function getAttrValuesAttr($value)
    {
        return explode(',', $value);
    }


    public static function createProductAttr($valueList, $productId, $op = 'add', $type = 0, $activity_id = 0)
    {

        $product = StoreProduct::find($productId);

        foreach ($valueList as $value) {
            if (empty($value['suk'])) {
                return self::setErrorInfo('属性不能为空');
            }

            if ($op != 'activity') {
                $num = $op == 'edit' ? 1 : 0;
                $count = StoreProductAttrValue::where([
                    'suk' => $value['suk'],
                    'product_id' => $productId,
                    'type' => $type,
                    'activity_id' => $activity_id
                ])->count();
                if ($count > $num) return self::setErrorInfo('商品中含有重复的属性名称');
            }
            // if (empty($value['discount']) && $op != 'activity') return self::setErrorInfo('优惠量填写有误');
            // if (empty($value['discount_gt']) && $op != 'activity') return self::setErrorInfo('大于优惠量的售价填写有误');
            // if (empty($value['discount_lt']) && $op != 'activity') return self::setErrorInfo('小于优惠量的售价填写有误');
            // if (empty($value['moq'])) return self::setErrorInfo('起订量填写有误');
            // if (!is_numeric($value['stock']) || $value['stock'] < 0) {
            //     return self::setErrorInfo('请填写正确的商品库存');
            // }

            $unique = StoreProductAttrValue::getunique($productId, $value['suk'], $type, $activity_id);
            $valueGroup[$value['suk']] = [
                'product_id' => $productId,
                'suk' => $value['suk'],
                'price' => $op == 'activity' ? $value['price'] : $product['price'],
                'cost' => $product['cost'],
                'ot_price' => $product['ot_price'],
                'stock' => $value['stock'],
                'unique' => $unique,
                'image' => '',
                'bar_code' => $value['bar_code'] ?? '',
                'weight' => $value['weight'] ?? 0,
                'volume' => $value['volume'] ?? 0,
                'brokerage' => $value['brokerage'] ?? 0,
                'brokerage_two' => $value['brokerage_two'] ?? 0,
                'type' => $type,
                'quota' => $value['quota'] ?? 0,
                'quota_show' => $value['quota'] ?? 0,
                'discount' => $value['discount'] ?? 0,
                'discount_gt' => $value['discount_gt'] ?? 0,
                'discount_lt' => $value['discount_lt'] ?? 0,
                'activity_id' => $activity_id,
                'advance_sale' => $value['advance_sale'] ?? 0,
                'advance_day' => $value['advance_day'] ?? 0,
                'advance_time' => $value['advance_time'] ?? '',
                'moq' => $value['moq'] ?? 0,
                'sort' => $value['sort'] ?? 0,
            ];

            if ($type == 0 && !$activity_id) {
                if (!StoreProductAttrValue::warehouse($productId, $unique, $value['stock'], 2)) {
                    return self::setErrorInfo('更新出入库记录失败!');
                }
                StoreProductAttrValue::where('suk', $value['suk'])
                    ->where('type', '<>', 0)
                    ->where('product_id', $productId)
                    ->update(['stock' => $value['stock']]);
            }
        }

        if (!count($valueGroup)) return self::setErrorInfo('请设置至少一个属性!');
        $attrValueModel = new StoreProductAttrValue;
        if (!self::clearProductAttr($productId, $type, $activity_id)) {
            return self::setErrorInfo('系统异常!');
        }

        if ($attrValueModel->saveAll($valueGroup) !== false)
            return true;
        else
            return self::setErrorInfo('编辑商品属性失败!');
    }

    public static function createProductAttr1($attrList, $valueList, $productId, $type = 0, $activity_id = 0)
    {
        $result = ['attr' => $attrList, 'value' => $valueList];
        $attrValueList = [];
        $attrNameList = [];
        foreach ($attrList as $index => $attr) {
            if (!isset($attr['value'])) return self::setErrorInfo('请输入规则名称!');
            $attr['value'] = trim($attr['value']);
            if (!isset($attr['value'])) return self::setErrorInfo('请输入规则名称!!');
            if (!isset($attr['detail']) || !count($attr['detail'])) return self::setErrorInfo('请输入属性名称!');
            foreach ($attr['detail'] as $k => $attrValue) {
                $attrValue = trim($attrValue);
                if (empty($attrValue)) return self::setErrorInfo('请输入正确的属性');
                $attr['detail'][$k] = $attrValue;
                $attrValueList[] = $attrValue;
                $attr['detail'][$k] = $attrValue;
            }
            $attrNameList[] = $attr['value'];
            $attrList[$index] = $attr;
        }
        $attrCount = count($attrList);

        foreach ($valueList as $index => $value) {
            if (!isset($value['detail']) || count($value['detail']) != $attrCount) return self::setErrorInfo('请填写正确的商品信息');
            if (!isset($value['price']) || !is_numeric($value['price']) || floatval($value['price']) != $value['price'])
                return self::setErrorInfo('请填写正确的商品价格');
            if (!isset($value['stock']) || !is_numeric($value['stock']) || intval($value['stock']) != $value['stock'])
                return self::setErrorInfo('请填写正确的商品库存');
            if (!isset($value['cost']) || !is_numeric($value['cost']) || floatval($value['cost']) != $value['cost'])
                return self::setErrorInfo('请填写正确的商品成本价格');
            if (!isset($value['pic']) || empty($value['pic']))
                return self::setErrorInfo('请上传商品图片');
            foreach ($value['detail'] as $attrName => $attrValue) {
                $attrName = trim($attrName);
                $attrValue = trim($attrValue);
                if (!in_array($attrName, $attrNameList, true)) return self::setErrorInfo($attrName . '规则不存在');
                if (!in_array($attrValue, $attrValueList, true)) return self::setErrorInfo($attrName . '属性不存在');
                if (empty($attrName)) return self::setErrorInfo('请输入正确的属性');
                $value['detail'][$attrName] = $attrValue;
            }
            $valueList[$index] = $value;
        }
        $attrGroup = [];
        $valueGroup = [];
        foreach ($attrList as $k => $value) {
            $attrGroup[] = [
                'product_id' => $productId,
                'attr_name' => $value['value'],
                'attr_values' => $value['detail'],
                'type' => $type,
                'activity_id' => $activity_id
            ];
        }

        foreach ($valueList as $k => $value) {
            sort($value['detail'], SORT_STRING);
            $suk = implode(',', $value['detail']);
            $unique = StoreProductAttrValue::getunique($productId, $suk, $type, $activity_id);
            $valueGroup[$suk] = [
                'product_id' => $productId,
                'suk' => $suk,
                'price' => $value['price'],
                'cost' => $value['cost'],
                'ot_price' => $value['ot_price'],
                'stock' => $value['stock'],
                'unique' => $unique,
                'image' => $value['pic'],
                'bar_code' => $value['bar_code'] ?? '',
                'weight' => $value['weight'] ?? 0,
                'volume' => $value['volume'] ?? 0,
                'brokerage' => $value['brokerage'] ?? 0,
                'brokerage_two' => $value['brokerage_two'] ?? 0,
                'type' => $type,
                'quota' => $value['quota'] ?? 0,
                'quota_show' => $value['quota'] ?? 0,
                'discount' => $value['discount'] ?? 0,
                'discount_gt' => $value['discount_gt'] ?? 0,
                'discount_lt' => $value['discount_lt'] ?? 0,
                'activity_id' => $activity_id,
                'advance_sale' => $value['advance_sale'],
                'advance_day' => $value['advance_day'],
                'advance_time' => $value['advance_time'],
                'sort' => $value['sort'],
            ];
            if ($type == 0 && !$activity_id) {
                if (!StoreProductAttrValue::warehouse($productId, $unique, $value['stock'], 2)) {
                    return self::setErrorInfo('更新出入库记录失败!');
                }
            }
        }
        if (!count($attrGroup) || !count($valueGroup)) return self::setErrorInfo('请设置至少一个属性!');
        $attrModel = new self;
        $attrValueModel = new StoreProductAttrValue;

        if (!self::clearProductAttr($productId, $type, $activity_id)) return false;

        $res = false !== $attrModel->saveAll($attrGroup)
            && false !== $attrValueModel->saveAll($valueGroup)
            && false !== StoreProductAttrResult::setResult($result, $productId, $type, $activity_id);
        if ($res)
            return true;
        else
            return self::setErrorInfo('编辑商品属性失败!');
    }

    /**
     * @Author  lingyun
     * @Desc    设置异业商品规格
     */
    public function setDifferentProductAttr($product_id = '', $type = 0, $attrGroup, $valueGroup, $result)
    {
        $product_model = new StoreProduct();
        $product = $product_model::where('id', $product_id)->find();
        $product = $product->toArray();

        if (!empty($product)) {
            $product_attr_list = StoreProductAttrValue::where('product_id', $product['id'])->field('unique,price')->select()->toArray();

            $product_list = $product_model::where('product_id', $product['id'])->select()->toArray();

            $org_product_id = $product['id'];
            $product_unique = [];
            $product_price = [];

            foreach ($product_attr_list as $k1 => $v1) {
                $price = $v1['price'] + $product['consignment_price'];
                array_push($product_unique, $v1['unique']);
                array_push($product_price, $price);
            }

            if (!empty($product_list)) {
                foreach ($product_list as $k => $v) {
                    $id = $v['id'];
                    $product['mer_id'] = $v['mer_id'];
                    $product['tenant_id'] = $v['tenant_id'];
                    $product['mer_use'] = 0;        //不可代理
                    $product['mer_type'] = 1;        //代销通过
                    $product['product_id'] = $org_product_id;        //异业商品id
                    $product['is_different'] = 0;        //非异业商品
                    //                    $product['slider_image'] = json_encode($product['slider_image']);
                    unset($product['id']);

                    $product_model->update($product, ['id' => $v['id']]);

                    //创建代销商品规格属性
                    $res = $product_model->consignmentProductAttr($id, $org_product_id, $product_price, $product_unique, 1);
                }
            }
        }

        return true;
    }

    public static function clearProductAttr($productId, $type = 0, $activity_id = 0)
    {
        if (empty($productId) && $productId != 0) return self::setErrorInfo('商品不存在!');
        $res = false !== self::where('product_id', $productId)
            ->where('type', $type)
            ->where('activity_id', $activity_id)
            ->delete()
            && false !== StoreProductAttrValue::clearProductAttrValue($productId, $type, $activity_id);
        if (!$res)
            return self::setErrorInfo('编辑属性失败,清除旧属性失败!');
        else
            return true;
    }

    /**
     * 获取产品属性
     * @param $productId
     * @return array|bool|null|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getProductAttr($productId)
    {
        if (empty($productId) && $productId != 0) return self::setErrorInfo('商品不存在!');
        $count = self::where('product_id', $productId)->count();
        if (!$count) return self::setErrorInfo('商品不存在!');
        return self::where('product_id', $productId)->select()->toArray();
    }
}
