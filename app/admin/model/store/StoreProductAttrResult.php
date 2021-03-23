<?php

/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/09
 */

namespace app\admin\model\store;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class StoreProductAttrResult extends BaseModel
{

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_product_attr_result';

    use ModelTrait;

    protected $insert = ['change_time'];

    protected static function setChangeTimeAttr($value)
    {
        return time();
    }

    protected static function setResultAttr($value)
    {
        return is_array($value) ? json_encode($value) : $value;
    }

    public static function setResult($result, $product_id, $type = 0, $activity_id = 0)
    {
        $result = self::setResultAttr($result);
        $change_time = self::setChangeTimeAttr(0);
        $count = self::where('product_id', $product_id)
            ->where('activity_id', $activity_id)
            ->where('type', $type)
            ->count();
        $res = true;
        if ($count) $res = self::where('product_id', $product_id)
            ->where('activity_id', $activity_id)
            ->where('type', $type)
            ->delete();
        if ($res) return self::insert(compact('product_id', 'result', 'change_time', 'type', 'activity_id'), true);
        return $res;
    }


    /**
     * @param $productId
     * @param int $type //0普通商品，123都是系统默认的（秒杀团购砍价），4限时折扣，5限时秒杀，6支付有礼
     * @return array
     */
    public static function getResult(int $productId, int $type = 0, int $activity_id = 0)
    {
        return json_decode(self::where('product_id', $productId)
            ->where('activity_id', '=', $activity_id)
            ->where('type', '=', $type)
            ->value('result'), true) ?: ['value' => []];
    }

    public static function clearResult($productId)
    {
        return self::where('product_id', $productId)->delete();
    }
}
