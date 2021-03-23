<?php

/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/09
 */

namespace app\admin\model\ump;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class StoreSeckillAttrResult extends BaseModel
{
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_seckill_attr_result';

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

    public static function setResult($result, $product_id, $activity_id)
    {
        $result = self::setResultAttr($result);
        $change_time = self::setChangeTimeAttr(0);
        return self::insert(compact('product_id', 'result', 'change_time', 'activity_id'), true);
    }

    public static function getResult($productId)
    {
        return json_decode(self::where('product_id', $productId)->value('result'), true) ?: [];
    }

    public static function clearResult($productId, $activity_id)
    {
        return self::where('type', 1)
            ->where('product_id', $productId)
            ->where('activity_id', $activity_id)
            ->delete();
    }
}
