<?php

namespace app\api\controller\store;

use app\models\store\StoreBargainUserHelp;
use app\models\store\StoreCart;
use app\models\store\StoreProduct;
use app\models\system\UserProductLog;
use app\Request;
use crmeb\services\UtilService;

/**
 * 购物车类
 * Class StoreCartController
 * @package app\api\controller\store
 */
class StoreCartController
{

    /**
     * 购物车 列表
     * @param Request $request
     * @return mixed
     */
    public function lst(Request $request)
    {
        $cart = StoreCart::getUserProductCartList($request->uid());

        try {
            $shop = array_filter(array_unique(array_column($cart['valid'], 'shop_id'), SORT_REGULAR));

            $cart_select = db('cart_select')->where('uid', $request->uid())->column('cart_id') ?? [];
            foreach ($shop as &$v) {
                foreach ($cart['valid'] as &$v1) {
                    if (in_array($v1['id'], $cart_select)) {
                        $v1['is_select'] = 1;
                    } else {
                        $v1['is_select'] = 0;
                    }

                    if ($v1['tenant_id'] == $v['id']) {
                        $v['list'][] = $v1;
                    }
                }
            }

            foreach ($shop as &$va) {
                $select = 1;
                foreach ($va['list'] as &$val) {
                    $val['moq'] = $val['productInfo']['attrInfo']['moq'];
                    $select = $select ? $val['is_select'] : $select;
                }
                $va['is_select'] = $select;
            }
            $cart['valid'] = array_values($shop);
        } catch (\Throwable $th) {
            $cart['valid'] = [];
            $cart['invalid'] = [];
        }

        return app('json')->successful($cart);
    }

    /**
     * 购物车 添加  废弃
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function add(Request $request)
    {
        
        list($productId, $cartNum, $uniqueId, $combinationId, $secKillId, $bargainId, $new, $activity_id, $activity_product_id, $note_id, $plan_id, $spread) = UtilService::postMore([
            ['productId', 0], //普通产品编号
            ['cartNum', 1], //购物车数量
            ['uniqueId', ''], //属性唯一值
            ['com_id', 0], //拼团产品编号
            ['seckill_id', 0], //秒杀产品编号
            ['bargainId', 0], //砍价产品编号
            ['new', 1], // 1 加入购物车直接购买  0 加入购物车
            ['activity_id', 0], // 活动id
            ['activity_product_id', 0], // 活动id
            ['note_id', 0], // 营销笔记id
            ['plan_id', 0], // 营销计划id
            ['spread', 0], // 推荐人的id
        ], $request, true);
        if (!$productId || !is_numeric($productId)) return app('json')->fail('参数错误');
        // if ($bargainId && StoreBargainUserHelp::getSurplusPrice($bargainId, $request->uid())) return app('json')->fail('请先砍价');
        $uid = $request->uid();

        $res = StoreCart::setCart($uid, $productId, $cartNum, $uniqueId, 'product', $new, $combinationId, $secKillId, $bargainId, 0, 0, $activity_id, $activity_product_id, $note_id, $plan_id, $spread);
        if (!$res) {
            return app('json')->fail(StoreCart::getErrorInfo());
        } else {
            //记录增加购物车的次数
            if (!$new) {
                UserProductLog::setCreateLog($productId, $uid, 3, $cartNum); //曝光次数
            }
            return app('json')->successful('ok', ['cartId' => $res->id]);
        }
    }

    /**批量添加 */
    public function saveall(Request $request)
    {
        $param = $request->param();
        $validate = \think\facade\Validate::rule([
            'cartInfo|购物车信息' => 'require',
            'new' => 'require|in:0,1'
        ]);
        if (!$validate->check($param)) {
            return app('json')->fail($validate->getError(), []);
        }

        if (!$cartInfo = json_decode($param['cartInfo'], true)) {
            return app('json')->fail('json解析失败');
        }
        $uid = $request->uid();
        $ids = [];
        foreach ($cartInfo as $v) {
            $seckill_id = 0;
            $combinationId = 0;
            if ($v['type'] == 1) {
                $seckill_id = $v['activity_id'];
            }
            if ($v['type'] == 3) {
                $combinationId = $v['activity_id'];
            }

            $res = StoreCart::setCart($uid, $v['productId'], $v['cartNum'], $v['uniqueId'], 'product', $param['new'], $combinationId, $seckill_id, 0, 0, 0, 0, 0, 0, 0, 0);
            if (!$res) {
                return app('json')->fail(StoreCart::getErrorInfo());
            } else {
                // //记录增加购物车的次数
                // if (!$param['new']) {
                //     UserProductLog::setCreateLog($v, $uid, 3, $v['cartNum']); //曝光次数
                // }
                //array_push($ids, $res->id);
            }
        }
        return app('json')->successful('ok', ['cartId' => $ids]);
    }

    /**
     * 购物车 删除产品
     * @param Request $request
     * @return mixed
     */
    public function del(Request $request)
    {
        list($ids) = UtilService::postMore([
            ['ids', ''], //购物车编号
        ], $request, true);
        if (StoreCart::removeUserCart($request->uid(), $ids))
            return app('json')->successful('清除成功');
        return app('json')->fail('清除失败！');
    }

    /**
     * 购物车 修改产品数量
     * @param Request $request
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function num(Request $request)
    {
        
        list($id, $number) = UtilService::postMore([
            ['id', 0], //购物车编号
            ['number', 0], //购物车编号
        ], $request, true);

        if (!$id || !is_numeric($id) || !is_numeric($number)) return app('json')->fail('参数错误!');
        if ($number == 0) {
            //传0清除购物车
            StoreCart::removeUserCart($request->uid(), "$id");
            return app('json')->fail('修改成功');
        }
        $res = StoreCart::changeUserCartNum($id, $number, $request->uid());
        if ($res)  return app('json')->successful();
        else return app('json')->fail(StoreCart::getErrorInfo('修改失败'));
    }

    /**
     * 购物车 获取数量
     * @param Request $request
     * @return mixed
     */
    public function count(Request $request)
    {
        list($numType) = UtilService::postMore([
            ['numType', true], //购物车编号
        ], $request, true);
        if (!(int) $numType) $numType = false;
        return  app('json')->success('ok', ['count' => StoreCart::getUserCartNum($request->uid(), 'product', $numType)]);
    }

    /**
     * 修改选中状态
     *
     * @param Request $request
     */
    public function select(Request $request)
    {

        $param = $request->param();
        extract($param);
        $uid = $request->uid();
        //0取消选中 1选中
        $arr = compact('cart_id', 'uid');
        db('cart_select')
            ->whereIn('cart_id', $param['cart_id'])
            ->where('uid', $uid)
            ->delete();
        if ($type) {
            $insert = [];
            $arr = explode(',', $param['cart_id']);
            foreach ($arr as $v) {
                $product_id = StoreCart::where('id', $v)->value('product_id');
                if (empty($product_id)) continue;
                $shop_id = StoreProduct::where('id', $product_id)->value('tenant_id');
                if (empty($shop_id)) continue;
                $insert[] = [
                    'product_id' => $product_id,
                    'shop_id' => $shop_id,
                    'uid' => $uid,
                    'cart_id' => $v
                ];
            }
            db('cart_select')->insertAll($insert);
        }
        return app('json')->successFul('操作成功');
    }
}
