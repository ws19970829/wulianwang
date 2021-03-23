<?php

/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\order;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * 支付订单转系统订单
 * Class StoreOrderStatus
 * @package app\admin\model\store
 */
class OrderOutTradeNo extends BaseModel
{

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'order_out_trade_no';

    use ModelTrait;

    public static function setRecord($ids, $out_trade_no)
    {
        self::beginTrans();
        $insert = [];
        try {
            $ids = explode(',', $ids);
            foreach ($ids as $v) {
                $insert[] = [
                    'order_id' => $v,
                    'out_trade_no' => $out_trade_no
                ];
            }
            (new self)->saveAll($insert);
            self::commitTrans();
            return true;
        } catch (\Exception $e) {
            self::rollbackTrans();
            return false;
        }
    }

    public static function getOrderId($out_trade_no)
    {
        return self::where('out_trade_no', $out_trade_no)->column('order_id') ?? [];
    }
}
