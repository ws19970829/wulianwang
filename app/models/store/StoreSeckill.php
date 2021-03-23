<?php

/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/18
 */

namespace app\models\store;

use app\admin\model\store\StoreProductAttrValue as StoreProductAttrValueModel;
use crmeb\basic\BaseModel;
use app\admin\model\store\StoreProductAttrValue;
use app\admin\model\store\Warehouse;
use think\facade\Request;

/**
 * TODO 秒杀产品Model
 * Class StoreSeckill
 * @package app\models\store
 */
class StoreSeckill extends BaseModel
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
    protected $name = 'store_seckill';

    protected function getImagesAttr($value)
    {
        return json_decode($value, true) ?: [];
    }

    public function getDescriptionAttr($value)
    {
        return htmlspecialchars_decode($value);
    }

    protected function getGoodsUrlAttr($v, $data)
    {
        $uid = Request::uid();
        return request()->domain() . '/wap/goods/detail?id=' . $data['product_id'] . '&uid=' . $uid . '&seckill_id=' . $data['id'];
    }


    public function getOtPriceTextAttr($v, $data)
    {
        return '原价:￥' . $data['ot_price'] . '/件';
    }

    public function getPriceTextAttr($v, $data)
    {
        return '秒杀价:￥' . $data['price'] . '/件';
    }

    public function getStartTimestampAttr($v, $data)
    {
        if (is_numeric($data['start_day'])) {
            if ($data['start_day'] < time()) {
                $data['start_day'] = time();
            }
            return strtotime(date('Y-m-d', $data['start_day']) . ' ' . $data['start_time']);
        } else {
            $day = strtotime($data['start_day']);
            if ($day < time()) {
                $data['start_day'] = date('Y-m-d');
            }
            return strtotime($data['start_day'] . ' ' . $data['start_time']);
        }
    }

    public function getQuotaTextAttr($v, $data)
    {
        return '剩余' . $data['quota'] . '件';
    }

    public function getSubscribeAttr($v, $data)
    {
        $uid = Request::uid();
        $res = SeckillSubscribe::where([
            'seckill_id' => $data['id'],
            'uid' => $uid
        ])->count();
        if ($res) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getStatusTextAttr($v, $data)
    {
        $data['start_day'] = is_numeric($data['start_day']) ? $data['start_day'] : strtotime($data['start_day']);
        $data['stop_day'] = is_numeric($data['stop_day']) ? $data['stop_day'] : strtotime($data['stop_day']);
        if (time() < $data['start_day']) return '未开始';
        if (time() > $data['stop_day']) return '已结束';
        $start = strtotime(date('Y-m-d', $data['start_day']) . ' ' . $data['start_time']);
        $end = strtotime(date('Y-m-d', $data['stop_day']) . ' ' . $data['stop_time']);
        if (time() < $start) {
            return '未开始';
        }
        if (time() >= $end) {
            return '已结束';
        }
        if (strtotime($data['start_time']) <= time() && strtotime($data['stop_time']) > time()) {
            return '进行中';
        } else {
            if (strtotime($data['stop_time']) < time()) {
                return '已结束';
            } else {
                return '未开始';
            }
        }
        return '已结束';
    }

    public function getStatusAttr($v, $data)
    {
        $data['start_day'] = strtotime($data['start_day']);
        $data['stop_day'] = strtotime($data['stop_day']);
        if (time() < $data['start_day']) return 0;
        if (time() > $data['stop_day']) return 2;
        $start = strtotime(date('Y-m-d', $data['start_day']) . ' ' . $data['start_time']);
        $end = strtotime(date('Y-m-d', $data['stop_day']) . ' ' . $data['stop_time']);
        if ($start <= time() && $end > time()) {
            return 1;
        } else {
            return 0;
        }
        return 2;
    }

    public function getStartDateAttr($v, $data)
    {
        if (is_numeric($data['start_day'])) {
            $data['start_day'] = date('Y-m-d', $data['start_day']);
        }
        return $data['start_day'] . ' ' . $data['start_time'];
    }

    public function getEndTimestampAttr($v, $data)
    {
        return strtotime(date('Y-m-d') . ' ' . $data['stop_time']);
    }

    public function getNoticeTitleAttr($v, $data)
    {
        return '秒杀须知';
    }

    public function getNoticeContextAttr($v, $data)
    {
        return '本次秒杀仅限平台用户参加，在秒杀截止时间前，达到秒杀预订数量，即秒杀成功。否则平台将进行退款，退款将在xx个工作日内完成。';
    }


    public function getMoqAttr($v, $data)
    {
        return db('store_product')->where('id', $data['product_id'])->value('moq');
    }

    public function getSeckillIdAttr($v, $data)
    {
        return $data['id'];
    }


    // public function getQuotaAttr($v, $data)
    // {
    //     return db('store_product_attr_value')
    //         ->where('type', 1)
    //         ->where('product_id', $data['product_id'])
    //         ->sum('quota') ?? 0;
    // }

    public static function getSeckillCount()
    {
        $seckillTime = sys_data('routine_seckill_time') ?: []; //秒杀时间段
        $timeInfo = ['time' => 0, 'continued' => 0];
        foreach ($seckillTime as $key => $value) {
            $currentHour = date('H');
            $activityEndHour = bcadd((int) $value['time'], (int) $value['continued'], 0);
            if ($currentHour >= (int) $value['time'] && $currentHour < $activityEndHour && $activityEndHour < 24) {
                $timeInfo = $value;
                break;
            }
        }
        if ($timeInfo['time'] == 0) return 0;
        $activityEndHour = bcadd((int) $timeInfo['time'], (int) $timeInfo['continued'], 0);
        $startTime = bcadd(strtotime(date('Y-m-d')), bcmul($timeInfo['time'], 3600, 0));
        $stopTime = bcadd(strtotime(date('Y-m-d')), bcmul($activityEndHour, 3600, 0));
        return self::where('is_del', 0)->where('status', 1)->where('start_time', '<=', $startTime)->where('stop_time', '>=', $stopTime)->count();
    }

    /**
     * 获取秒杀列表
     * @param $time
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function seckillList($time, $page = 0, $limit = 20)
    {
        if ($page) $list = StoreSeckill::alias('n')->join('store_product c', 'c.id=n.product_id')->where('c.is_show', 1)->where('c.is_del', 0)->where('n.is_del', 0)->where('n.status', 1)->where('n.start_time', '<=', time())->where('n.stop_time', '>=', time() - 86400)->where('n.time_id', $time)->field('n.*')->order('n.sort desc')->page($page, $limit)->select();
        else $list = StoreSeckill::alias('n')->join('store_product c', 'c.id=n.product_id')->where('c.is_show', 1)->where('c.is_del', 0)->where('n.is_del', 0)->where('n.status', 1)->where('n.start_time', '<=', time())->where('n.stop_time', '>=', time() - 86400)->where('n.time_id', $time)->field('n.*')->order('sort desc')->select();
        if ($list) return $list->hidden(['cost', 'add_time', 'is_del'])->toArray();
        return [];
    }

    /**
     * 获取所有秒杀产品
     * @param string $field
     * @return array
     */
    public static function getListAll($offset = 0, $limit = 10, $field = 'id,product_id,image,title,price,ot_price,start_time,stop_time,stock,sales')
    {
        $time = time();
        $model = self::where('is_del', 0)->where('status', 1)->where('stock', '>', 0)->field($field)
            ->where('start_time', '<', $time)->where('stop_time', '>', $time)->order('sort DESC,add_time DESC');
        $model = $model->limit($offset, $limit);
        $list = $model->select();
        if ($list) return $list->toArray();
        else return [];
    }

    /**
     * 获取热门推荐的秒杀产品
     * @param int $limit
     * @param string $field
     * @return array
     */
    public static function getHotList($limit = 0, $field = 'id,product_id,image,title,price,ot_price,start_time,stop_time,stock')
    {
        $time = time();
        $model = self::where('is_hot', 1)->where('is_del', 0)->where('status', 1)->where('stock', '>', 0)->field($field)
            ->where('start_time', '<', $time)->where('stop_time', '>', $time)->order('sort DESC,add_time DESC');
        if ($limit) $model->limit($limit);
        $list = $model->select();
        if ($list) return $list->toArray();
        else return [];
    }

    /**
     * 获取一条秒杀产品
     * @param $id
     * @param string $field
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public static function getValidProduct($id, $field = '*')
    {

        $time = time();
        $info = self::alias('n')
            ->join('store_product c', 'c.id=n.product_id')
            ->where('n.id', $id)
            ->where('c.is_show', 1)
            ->where('c.is_del', 0)
            ->where('n.is_del', 0)
            ->where('n.is_show', 1)
            // ->where('n.start_time', '<', $time)
            // ->where('n.stop_time', '>', $time - 86400)
            ->field('n.*,SUM(c.sales+c.ficti) as total')
            ->find();

        if ($info['id']) {
            return $info;
        } else {
            return [];
        }
    }

    /**
     * 获取秒杀是否有开启
     * @return bool
     */
    public static function getSeckillContStatus()
    {
        $time = time();
        $count = self::where('is_del', 0)->where('status', 1)->where('start_time', '<', $time)->where('stop_time', '>', $time)->count();
        return $count ? true : false;
    }

    public static function initFailSeckill()
    {
        self::where('is_hot', 1)->where('is_del', 0)->where('status', '<>', 1)->where('stop_time', '<', time())->update(['status' => '-1']);
    }

    public static function idBySimilaritySeckill($id, $limit = 4, $field = '*')
    {
        $time = time();
        $list = [];
        $productId = self::where('id', $id)->value('product_id');
        if ($productId) {
            $list = array_merge($list, self::where('product_id', $productId)->where('id', '<>', $id)
                ->where('is_del', 0)->where('status', 1)->where('stock', '>', 0)
                ->field($field)->where('start_time', '<', $time)->where('stop_time', '>', $time)
                ->order('sort DESC,add_time DESC')->limit($limit)->select()->toArray());
        }
        $limit = $limit - count($list);
        if ($limit) {
            $list = array_merge($list, self::getHotList($limit, $field));
        }

        return $list;
    }

    /** 获取秒杀产品库存
     * @param $id
     * @return mixed
     */
    public static function getProductStock($id)
    {
        return self::where('id', $id)->value('stock');
    }

    /**
     * 获取字段值
     * @param $id
     * @param string $field
     * @return mixed
     */
    public static function getProductField($id, $field = 'title')
    {
        return self::where('id', $id)->value($field);
    }

    /**
     * 修改秒杀库存
     * @param int $num
     * @param int $seckillId
     * @return bool
     */
    public static function decSeckillStock($num = 0, $seckillId = 0, $unique = '')
    {
        $product_id = self::where('id', $seckillId)->value('product_id');
        if ($unique) {
            $sku = StoreProductAttrValue::where('product_id', $product_id)
                ->where('unique', $unique)
                ->where('activity_id', $seckillId)
                ->where('type', 1)
                ->value('suk');

            $res = false !== StoreProductAttrValue::decProductAttrStock($seckillId, $unique, $num, 1);
            $res = $res && self::where('id', $seckillId)
                ->dec('stock', $num)
                ->dec('quota', $num)
                ->inc('sales', $num)
                ->update();
            if (!StoreProductAttrValue::warehouse($product_id, $sku, 0, 1, $num, 0)) return false;
            $res = $res && StoreProductAttrValue::where('product_id', $product_id)->where('suk', $sku)->where('type', 0)->dec('stock', $num)->inc('sales', $num)->update();
            $res = $res && StoreProduct::where('id', $product_id)->dec('stock', $num)->inc('sales', $num)->update();
        } else {
            $res = false !== self::where('id', $seckillId)->dec('stock', $num)->inc('sales', $num)->update();
            $res = $res && StoreProduct::where('id', $product_id)->dec('stock', $num)->inc('sales', $num)->update();
        }

        return $res;
    }


    public static function decSeckillStock_bak($num = 0, $seckillId = 0, $unique = '')
    {
        $product_id = self::where('id', $seckillId)->value('product_id');
        if ($unique) {
            $sku = StoreProductAttrValue::where('product_id', $product_id)
                ->where('unique', $unique)
                ->where('activity_id', $seckillId)
                ->where('type', 1)
                ->value('suk');

            if (Warehouse::pre_sale_switch($product_id, $sku, 'suk')) {
                $res = false !== StoreProductAttrValue::decProductAttrStock1($seckillId, $unique, $num, 1);
                $res = $res && self::where('id', $seckillId)
                    // ->dec('stock', $num)
                    ->dec('quota', $num)
                    ->inc('sales', $num)
                    ->update();
                //开启了无货预售只加销量不扣库存
                $res = $res && StoreProductAttrValue::where('product_id', $product_id)->where('suk', $sku)->where('type', 0)->inc('sales', $num)->update();
                $res = $res && StoreProduct::where('id', $product_id)->inc('sales', $num)->update();
            } else {
                //未开启无货预售按正常流程来
                $res = false !== StoreProductAttrValue::decProductAttrStock($seckillId, $unique, $num, 1);
                $res = $res && self::where('id', $seckillId)
                    ->dec('stock', $num)
                    ->dec('quota', $num)
                    ->inc('sales', $num)
                    ->update();
                if (!StoreProductAttrValue::warehouse($product_id, $sku, 0, 1, $num, 0)) return false;
                $res = $res && StoreProductAttrValue::where('product_id', $product_id)->where('suk', $sku)->where('type', 0)->dec('stock', $num)->inc('sales', $num)->update();
                $res = $res && StoreProduct::where('id', $product_id)->dec('stock', $num)->inc('sales', $num)->update();
            }
        } else {
            $res = false !== self::where('id', $seckillId)->dec('stock', $num)->inc('sales', $num)->update();
            $res = $res && StoreProduct::where('id', $product_id)->dec('stock', $num)->inc('sales', $num)->update();
        }

        return $res;
    }

    /**
     * 增加库存较少销量
     * @param int $num
     * @param int $seckillId
     * @return bool
     */
    public static function incSeckillStock($num = 0, $seckillId = 0, $unique = '')
    {
        $seckill = self::where('id', $seckillId)->field(['product_id', 'stock', 'sales', 'quota'])->find();
        if (!$seckill) return true;
        if ($seckill->sales > 0) $seckill->sales = bcsub($seckill->sales, $num, 0);
        if ($seckill->sales < 0) $seckill->sales = 0;
        $res = true;
        if ($unique) {
            $sku = StoreProductAttrValue::where('product_id', $seckill['product_id'])
                ->where('unique', $unique)
                ->where('activity_id', $seckillId)
                ->where('type', 1)
                ->value('suk');
            $res = false !== StoreProductAttrValueModel::incProductAttrStock($seckillId, $unique, $num, 1);
            $attr = StoreProductAttrValue::where('product_id', $seckill['product_id'])
                ->where('suk', $sku)
                ->where('type', 0)
                ->find();
            $attr->stock = $attr->stock + $num;
            if ($attr->sales >= $num) {
                $attr->sales = $attr->sales - $num;
            }
            $res = $res && $attr->save();
            $res = $res && StoreProductAttrValue::warehouse($seckill['product_id'], $sku, 0, 1, $num, 1);
            $store = StoreProduct::where('id', $seckill['product_id'])->find();
            if ($store->sales >= $num) {
                $store->sales = $store->sales - $num;
            }
            $store->stock = $store->stock + $num;
            $res = $res && $store->save();
        } else {
            $store = StoreProduct::where('id', $seckill['product_id'])->find();
            if ($store->sales >= $num) {
                $store->sales = $store->sales - $num;
            }
            $store->stock = $store->stock + $num;
            $res = $res && $store->save();
            return $res;
        }
        $seckill->stock = bcadd($seckill->stock, $num, 0);
        $seckill->quota = bcadd($seckill->quota, $num, 0);
        $res = $res && $seckill->save();
        return $res;
    }


    /**
     * 获取秒杀是否已结束
     * @param $seckill_id
     * @return bool
     */
    public static function isSeckillEnd($seckill_id)
    {
        $seckill = self::find($seckill_id);
        if ($seckill->isEmpty()) return false;
        if ($seckill->start_day > time()) return false;
        if ($seckill->stop_day < time()) return false;
        if (strtotime($seckill->start_time) > time()) return false;
        if (strtotime($seckill->stop_time < time())) return false;
        return true;
    }

    /**
     * 检查秒杀活动状态
     * @param $seckill_id
     * @return bool
     */
    public static function checkStatus($seckill_id)
    {
        $time_id = self::where('id', $seckill_id)->value('time_id');
        //秒杀时间段
        $seckillTime = sys_data('routine_seckill_time') ?? [];
        $seckillTimeIndex = 0;
        $activityTime = [];
        if (count($seckillTime)) {
            foreach ($seckillTime as $key => &$value) {
                $currentHour = date('H');
                $activityEndHour = bcadd((int) $value['time'], (int) $value['continued'], 0);
                if ($activityEndHour > 24) {
                    $value['time'] = strlen((int) $value['time']) == 2 ? (int) $value['time'] . ':00' : '0' . (int) $value['time'] . ':00';
                    $value['state'] = '即将开始';
                    $value['status'] = 2;
                    $value['stop'] = (int) bcadd(strtotime(date('Y-m-d')), bcmul($activityEndHour, 3600, 0));
                } else {
                    if ($currentHour >= (int) $value['time'] && $currentHour < $activityEndHour) {
                        $value['time'] = strlen((int) $value['time']) == 2 ? (int) $value['time'] . ':00' : '0' . (int) $value['time'] . ':00';
                        $value['state'] = '抢购中';
                        $value['stop'] = (int) bcadd(strtotime(date('Y-m-d')), bcmul($activityEndHour, 3600, 0));
                        $value['status'] = 1;
                        if (!$seckillTimeIndex) $seckillTimeIndex = $key;
                    } else if ($currentHour < (int) $value['time']) {
                        $value['time'] = strlen((int) $value['time']) == 2 ? (int) $value['time'] . ':00' : '0' . (int) $value['time'] . ':00';
                        $value['state'] = '即将开始';
                        $value['status'] = 2;
                        $value['stop'] = (int) bcadd(strtotime(date('Y-m-d')), bcmul($activityEndHour, 3600, 0));
                    } else if ($currentHour >= $activityEndHour) {
                        $value['time'] = strlen((int) $value['time']) == 2 ? (int) $value['time'] . ':00' : '0' . (int) $value['time'] . ':00';
                        $value['state'] = '已结束';
                        $value['status'] = 0;
                        $value['stop'] = (int) bcadd(strtotime(date('Y-m-d')), bcmul($activityEndHour, 3600, 0));
                    }
                }

                if ($value['id'] == $time_id) {
                    $activityTime = $value;
                    break;
                }
            }
        }
        return $activityTime;
    }

    public static function forbidden($id)
    {
        $res = self::find($id);
        if ($res->isEmpty()) return false;
        if ($res->is_del) return false;
        if (!$res->is_show) return false;
        return true;
    }
}
