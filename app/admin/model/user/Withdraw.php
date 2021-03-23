<?php

namespace app\admin\model\user;

use app\admin\model\system\SystemConfig;
use app\models\store\StoreOrder;
use app\models\user\UserBill;
use app\models\user\UserExtract;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;



class Withdraw extends BaseModel
{

    public static function getList($where)
    {
        $order = (new StoreOrder());
        $order = $order->whereNotIn('withdraw', '0,1')
            ->where('is_del', 0)
            ->where('status', 2)
            ->where('give_store_cash', '<>', 0)
            ->where('tenant_id', session('tenant_id'))
            ->order('add_time', 'desc')
            ->where('withdraw', '-1')
            ->field('id,order_id,give_store_cash,add_time');
        if ($where['order_id']) {
            $order = $order->where('order_id', $where['order_id']);
        }


        $data = ($data = $order->page((int) $where['page'], (int) $where['limit'])->select()) && count($data) ? $data->toArray() : [];
        foreach ($data as &$v) $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
        $count = $order->count();
        return compact('data', 'count');
    }

    public static function change_status($ids, $status)
    {
        return StoreOrder::whereIn('id', $ids)->update(['withdraw' => $status]);
    }

    public static function apply($param)
    {
        self::beginTrans();
        if (!self::change_status($param['order_ids'], 0)) {
            return self::setErrorInfo('申请失败', true);
        }

        $param['add_time'] = time();
        if (!UserExtract::create($param)) {
            return self::setErrorInfo('申请失败', true);
        }
        $total = StoreOrder::whereIn('id', $param['order_ids'])->sum('give_store_cash');

        $bili = SystemConfig::getConfigValue('withdraw_rate');


        $rate = bcmul($total, $bili / 100, 2);

        $give_shop = bcsub($total, $rate, 2);

        // //申请提现记录
        // UserBill::expend('提现申请', 0, 'now_money', 'extract', $give_shop, 0, 0, '提现' . floatval($give_shop) . '元', 1, session('tenant_id'));
        // UserBill::expend('提现手续费', 0, 'now_money', 'rate', $rate, 0, 0, '提现手续费扣除' . floatval($rate) . '元', 1, session('tenant_id'));
        // UserBill::income('提现手续费', 0, 'now_money', 'rate', $rate, 0, 0, '提现手续费收入' . floatval($rate) . '元', 1, 0);
        $content = '提现' . floatval($total) . '元,扣除手续费' . $rate . '元';
        UserBill::expend('可用金额', 0, 'now_money', 'extract', $give_shop, 0, 0, $content, 1, session('tenant_id'));
        self::commitTrans();
        return true;
    }
}
