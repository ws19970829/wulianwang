<?php

/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/08
 */

namespace app\admin\model\store;

use crmeb\basic\BaseModel;
use crmeb\services\SystemConfigService;
use crmeb\services\workerman\ChannelService;
use crmeb\traits\ModelTrait;
use app\models\store\StoreProduct as StoreProductModel;

class StoreProductAttrValue extends BaseModel
{

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_product_attr_value';

    use ModelTrait;

    protected $insert = ['unique'];

    protected function setSukAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    protected function setUniqueAttr($value, $data)
    {

        if (is_array($data['suk'])) $data['suk'] = $this->setSukAttr($data['suk']);
        return $data['unique'] ?: self::uniqueId($data['product_id'] . $data['suk'] . uniqid(true));
    }

    /*
     * 减少销量增加库存
     * */
    public static function incProductAttrStock($productId, $unique, $num, $type = 0)
    {
        $activity_id = 0;
        if ($type) {
            $activity_id = $productId;
            if ($type == 1) {
                $productId = db('store_seckill')->where('id', $productId)->value('product_id');
            } else {
                $productId = db('store_combination')->where('id', $productId)->value('product_id');
            }
        }

        $productAttr = self::where('unique', $unique)
            ->where('product_id', $productId)
            ->where('type', $type)
            ->where('activity_id', $activity_id)
            ->field('stock,sales,quota')
            ->find();
        if (!$productAttr) return true;

        if (!StoreProductAttrValue::warehouse($productId, $unique, 0, $type, $num, 1)) return false;
        $productAttr->stock = bcadd($productAttr->stock, $num, 0);

        if ($productAttr->sales > 0) $productAttr->sales = bcsub($productAttr->sales, $num, 0);
        if ($productAttr->sales < 0) $productAttr->sales = 0;

        //活动商品有限量数
        if ($type > 0) {
            $productAttr->quota = bcadd($productAttr->quota, $num, 0);
        }
        return $productAttr->save();
    }

    public static function incProductAttrStock_bak($productId, $unique, $num, $type = 0)
    {
        $activity_id = 0;
        if ($type) {
            $activity_id = $productId;
            if ($type == 1) {
                $productId = db('store_seckill')->where('id', $productId)->value('product_id');
            } else {
                $productId = db('store_combination')->where('id', $productId)->value('product_id');
            }
        }

        $productAttr = self::where('unique', $unique)
            ->where('product_id', $productId)
            ->where('type', $type)
            ->where('activity_id', $activity_id)
            ->field('stock,sales,quota')
            ->find();
        if (!$productAttr) return true;

        if (!$type) {
            if (!Warehouse::pre_sale_switch($productId, $unique)) {
                //未开启无货预售按正常流程来
                if (!StoreProductAttrValue::warehouse($productId, $unique, 0, 0, $num, 1)) return false;
                $productAttr->stock = bcadd($productAttr->stock, $num, 0);
            }
        } else {
            $productAttr->stock = bcadd($productAttr->stock, $num, 0);
        }

        if ($productAttr->sales > 0) $productAttr->sales = bcsub($productAttr->sales, $num, 0);
        if ($productAttr->sales < 0) $productAttr->sales = 0;

        //活动商品有限量数
        if ($type > 0) {
            $productAttr->quota = bcadd($productAttr->quota, $num, 0);
        }
        return $productAttr->save();
    }

    public static function incProductAttrStock1($productId, $unique, $num, $type = 0)
    {
        $activity_id = 0;
        if ($type) {
            $activity_id = $productId;
            if ($type == 1) {
                $productId = db('store_seckill')->where('id', $productId)->value('product_id');
            } else {
                $productId = db('store_combination')->where('id', $productId)->value('product_id');
            }
        }
        $productAttr = self::where('unique', $unique)
            ->where('product_id', $productId)
            ->where('type', $type)
            ->where('activity_id', $activity_id)
            ->field('stock,sales,quota')
            ->find();
        if (!$productAttr) return true;
        if (!$type) {
            if (!StoreProductAttrValue::warehouse($productId, $unique, 0, 0, $num, 1)) return false;
        }
        $productAttr->stock = bcadd($productAttr->stock, $num, 0);
        if ($productAttr->sales > 0) $productAttr->sales = bcsub($productAttr->sales, $num, 0);
        if ($productAttr->sales < 0) $productAttr->sales = 0;

        //活动商品有限量数
        if ($type > 0) {
            $productAttr->quota = bcadd($productAttr->quota, $num, 0);
        }
        return $productAttr->save();
    }

    public static function decProductAttrStock($productId, $unique, $num, $type = 0)
    {
        if ($type == 0) {
            $res = self::where('product_id', $productId)->where('unique', $unique)->where('type', $type)
                ->dec('stock', $num)->inc('sales', $num)->update();
            if (!StoreProductAttrValue::warehouse($productId, $unique, 0, 0, $num, 0)) return false;
        } else {
            $activity_id = $productId;
            $table = $type == 1 ? 'store_seckill' : 'store_combination';
            $productId =  db($table)
                ->where('id', $activity_id)
                ->value('product_id');
            $res = self::where('product_id', $productId)
                ->where('unique', $unique)
                ->where('activity_id', $activity_id)
                ->where('type', $type)
                ->dec('stock', $num)
                ->dec('quota', $num)
                ->inc('sales', $num)
                ->update();
        }

        if ($res) {
            $stock = self::where('product_id', $productId)->where('unique', $unique)->where('type', $type)->value('stock');
            $replenishment_num = sys_config('store_stock') ?? 0; //库存预警界限
            if ($replenishment_num >= $stock) {
                try {
                    ChannelService::instance()->send('STORE_STOCK', ['id' => $productId]);
                } catch (\Exception $e) {
                }
            }
        }
        return $res;
    }

    public static function decProductAttrStock1($productId, $unique, $num, $type = 0)
    {
        if ($type == 0) {
            $res = self::where('product_id', $productId)->where('unique', $unique)->where('type', $type)
                ->dec('stock', $num)->inc('sales', $num)->update();
            if (!StoreProductAttrValue::warehouse($productId, $unique, 0, 0, $num, 0)) return false;
        } else {
            $activity_id = $productId;
            $table = $type == 1 ? 'store_seckill' : 'store_combination';
            $productId =  db($table)
                ->where('id', $activity_id)
                ->value('product_id');
            $res = self::where('product_id', $productId)
                ->where('unique', $unique)
                ->where('activity_id', $activity_id)
                ->where('type', $type)
                //->dec('stock', $num)
                ->dec('quota', $num)
                ->inc('sales', $num)
                ->update();
        }

        if ($res) {
            $stock = self::where('product_id', $productId)->where('unique', $unique)->where('type', $type)->value('stock');
            $replenishment_num = sys_config('store_stock') ?? 0; //库存预警界限
            if ($replenishment_num >= $stock) {
                try {
                    ChannelService::instance()->send('STORE_STOCK', ['id' => $productId]);
                } catch (\Exception $e) {
                }
            }
        }
        return $res;
    }


    public static function decProductAttrStockByActivtiyAndUnique($activity_id, $unique, $num, $type = 0)
    {
        if ($type == 0) {
            $res = self::where('activity_id', $activity_id)
                ->where('unique', $unique)
                ->where('type', $type)
                ->dec('activity_stock', $num)
                ->inc('sales', $num)
                ->update();
        } else {
            $res = self::where('activity_id', $activity_id)
                ->where('unique', $unique)
                ->where('type', $type)
                ->dec('activity_stock', $num)
                //                ->dec('quota', $num)
                ->inc('sales', $num)
                ->update();
        }

        //        if ($res) {
        //            $stock = self::where('activity_id', $activity_id)
        //                ->where('unique', $unique)
        //                ->where('type', $type)
        //                ->value('stock');
        //            $replenishment_num = sys_config('store_stock') ?? 0;//库存预警界限
        //            if ($replenishment_num >= $stock) {
        //                try {
        //                    ChannelService::instance()->send('STORE_STOCK', ['id' => $productId]);
        //                } catch (\Exception $e) {
        //                }
        //            }
        //        }
        return $res;
    }

    /**
     * 获取属性参数
     * @param $productId
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getStoreProductAttrResult($productId)
    {
        $productAttr = StoreProductAttr::getProductAttr($productId);
        if (!$productAttr) return [];
        $attr = [];
        foreach ($productAttr as $key => $value) {
            $attr[$key]['value'] = $value['attr_name'];
            $attr[$key]['detailValue'] = '';
            $attr[$key]['attrHidden'] = true;
            $attr[$key]['detail'] = $value['attr_values'];
        }
        $value = attr_format($attr)[1];
        $valueNew = [];
        $count = 0;
        foreach ($value as $key => $item) {
            $detail = $item['detail'];
            sort($item['detail'], SORT_STRING);
            $suk = implode(',', $item['detail']);
            $sukValue = self::where('product_id', $productId)->where('type', 0)->where('suk', $suk)->column('bar_code,cost,price,stock as sales,image as pic', 'suk');
            if (!count($sukValue)) {
                unset($value[$key]);
            } else {
                $valueNew[$count]['detail'] = $detail;
                $valueNew[$count]['cost'] = $sukValue[$suk]['cost'];
                $valueNew[$count]['price'] = $sukValue[$suk]['price'];
                $valueNew[$count]['sales'] = $sukValue[$suk]['sales'];
                $valueNew[$count]['pic'] = $sukValue[$suk]['pic'];
                $valueNew[$count]['bar_code'] = $sukValue[$suk]['bar_code'] ?? '';
                $valueNew[$count]['check'] = false;
                $count++;
            }
        }
        return ['attr' => $attr, 'value' => $valueNew];
    }


    public static function uniqueId($key)
    {
        return substr(md5($key), 12, 8);
    }

    public static function clearProductAttrValue($productId, $type = 0, $activity_id = 0)
    {
        return self::where('product_id', $productId)
            ->where('type', $type)
            ->where('activity_id', $activity_id)
            ->delete();
    }

    /**添加出入库记录 
     * 商品id
     * 型号unique
     * 库存
     * 类型0普通商品1秒杀商品2系统修改3拼团商品
     * setnum修改数量
     * pm '0 = 出库 1 = 入库'
     * uid 
     */
    public static function warehouse($product_id, $unique, $stock, $type, $setnum = 0, $pm = 0, $uid = 0)
    {
        $field = $type == 2 || $type == 0 ? 'unique' : 'suk';
        $attr = self::where([
            'product_id' => $product_id,
            'type' => 0,
            $field => $unique
        ])->find();
        if (empty($attr)) {
            $pm = 0;
            $setnum = $stock;
        } else {
            if ($type == 2) {
                if ($attr->stock > $stock) {
                    $pm = 0;
                    $setnum = $attr->stock - $stock;
                } elseif ($attr->stock < $stock) {
                    $pm = 1;
                    $setnum = $stock - $attr->stock;
                } else {
                    return true;
                }
            }
            if ($type == 1 || $type == 3) {
                $unique = $attr['unique'];
            }
        }
        Warehouse::create([
            'uid' => $uid,
            'product_id' => $product_id,
            'unique' => $unique,
            'number' => $setnum,
            'pm' => $pm,
            'type' => $type,
            'add_time' => time(),
            'title' => ''
        ]);
        return true;
    }

    public static function getunique($product_id, $suk, $type, $activity_id)
    {
        return self::where(['product_id' => $product_id, 'suk' => $suk, 'type' => $type, 'activity_id' => $activity_id])->value('unique') ?: '';
    }

    public function getTrueStockAttr($v, $data)
    {
        if (!$data['type']) return $data['stock'];
        return self::where('product_id', $data['product_id'])
            ->where('suk', $data['suk'])
            ->where('type', 0)
            ->value('stock');
    }
}
