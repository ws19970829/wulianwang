<?php

/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\store;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;



class Warehouse extends BaseModel
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
    protected $name = 'warehouse';

    use ModelTrait;

    public function getPmTextAttr($v, $data)
    {
        return $data['pm'] ? '入库' : '出库';
    }

    public function getAddTimeAttr($v, $data)
    {
        return date('Y-m-d H:i', $data['add_time']);
    }

    public function getTypeTextAttr($v, $data)
    {
        switch ($data['type']) {
            case '0':
                return '普通订单';
            case '1':
                return '秒杀订单';
            case '2':
                return '后台修改';
            case '3':
                return '拼团订单';
        }
    }

    public static function record($where)
    {
        $model = new self;
        if (!empty($where['product_id'])) {
            $model =  $model->where('product_id', $where['product_id']);
        }
        if (!empty($where['unique'])) {
            $model =  $model->where('unique', $where['unique']);
        }
        $data = $model
            ->page($where['page'], $where['limit'])
            ->order('add_time', 'desc')
            ->select();
        $data = $data->append(['add_time', 'pm_text', 'type_text'])->toArray();

        $count = $model->count();
        return compact('data', 'count');
    }

    /**是否开启无货预售 */
    public static function pre_sale_switch($product_id, $unique, $field = 'unique')
    {
        return (bool) StoreProductAttrValue::where($field, $unique)
            ->where('product_id', $product_id)
            ->where('type', 0)
            ->where('advance_sale',1)
            ->where('activity_id', 0)
            ->count();
    }
}
