<?php

/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2018/01/22
 */

namespace app\models\store;


use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * TODO 优惠券Model
 * Class StoreCoupon
 * @package app\models\store
 */
class StoreCoupon extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_coupon';

    use ModelTrait;

    /**获取优惠券类型 */
    public static function type($id)
    {
        if (empty($id)) return -1;
        return self::where('id', $id)->value('type') ?? -1;
    }
}
