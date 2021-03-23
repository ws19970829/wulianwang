<?php

/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\controller\order;

use app\admin\controller\AuthController;
use app\admin\model\order\StoreOrderCartInfo;
use app\admin\model\system\Express;
use crmeb\repositories\OrderRepository;
use crmeb\repositories\ShortLetterRepositories;
use crmeb\services\{
    ExpressService,
    JsonService,
    MiniProgramService,
    WechatService,
    FormBuilder as Form,
    CacheService,
    UtilService as Util,
    JsonService as Json
};
use app\admin\model\order\StoreOrderStatus;
use app\admin\model\ump\StorePink;
use app\admin\model\user\{
    User,
    UserBill
};
use crmeb\basic\BaseModel;
use think\facade\Route as Url;
use app\admin\model\order\StoreOrder as StoreOrderModel;
use app\admin\model\store\StoreProductAttrValue;
use app\admin\model\system\SystemConfig;
use crmeb\services\YLYService;
use think\facade\Log;
use app\models\store\StoreOrder as StoreOrderModels;
use app\models\store\StoreProduct;
use app\models\user\UserMessage;
use crmeb\services\PHPExcelService;
use think\facade\Db;
use think\Request;

/**
 * 订单管理控制器 同一个订单表放在一个控制器
 * Class StoreOrder
 * @package app\admin\controller\store
 */
class StoreOrder extends AuthController
{

    //————————————————————————————————————————————————————————//
    //—————————————————普通订单—————————————————————//
    //————————————————————————————————————————————————————————//
    /**
     * @return mixed
     */
    public function index()
    {
        $this->assign([
            'year' => get_month(),
            'real_name' => $this->request->get('real_name', ''),
            'status' => $this->request->param('status', ''),
            'orderCount' => StoreOrderModel::orderCount(),
            'payTypeCount' => StoreOrderModel::payTypeCount(),
        ]);
        return $this->fetch();
    }

    /**
     * 获取头部订单金额等信息
     * return json
     */
    public function getBadge()
    {
        $where = Util::postMore([
            ['status', ''],
            ['real_name', $this->request->param('real_name', '')],
            ['is_del', 0],
            ['data', ''],
            ['type', ''],
            ['pay_type', ''],
            ['order', ''],
            ['page', 1],
            ['limit', 20],
            ['excel', 0],
            ['ordertype', ''],
            ['activity_type', ''],
        ]);
        return Json::successful(StoreOrderModel::getBadge($where));
    }



    /**
     * 获取订单列表
     * return json
     */
    public function order_list()
    {
        $where = Util::getMore([
            ['status', ''],
            ['real_name', $this->request->param('real_name', '')],
            ['is_del', 0],
            ['data', ''],
            ['type', ''],
            ['pay_type', ''],
            ['order', ''],
            ['page', 1],
            ['limit', 20],
            ['excel', 0],
            ['ordertype', ''],
            ['activity_type', ''],
        ]);
        return Json::successlayui(StoreOrderModel::OrderList($where));
    }


    /**
     * 库存清单
     */
    public function inventory($id)
    {
        if (!$id || !($orderinfo = StoreOrderModel::where('id', $id)->append(['shop_name', 'express_name'])->find()))
            return $this->failed('订单不存在!');
        $userinfo = User::getUserInfos($orderinfo['uid']);
        $goodsinfo = StoreOrderCartInfo::where('oid', $id)->select();
        $goodsinfo = $goodsinfo->append(['cart_info_filter'])->toArray();
        $sys_phone = SystemConfig::getConfigValue('server_tel', $orderinfo['tenant_id']);
        $this->assign(compact('orderinfo', 'userinfo', 'goodsinfo', 'sys_phone'));
        return $this->fetch();
    }


    /**
     * 导入订单发货
     * @return string
     */
    public function uploader()
    {
        return $this->fetch();
    }



    public function order_print($id = '')
    {

        if (!$id) {
            return JsonService::fail('缺少参数');
        }
        $order = StoreOrderModel::get($id);
        if (!$order) {
            return JsonService::fail('订单没有查到,无法打印!');
        }
        try {
            $cartInfo = StoreOrderCartInfo::whereIn('cart_id', $order['cart_id'])->field('cart_info')->select();
            $cartInfo = count($cartInfo) ? $cartInfo->toArray() : [];
            $product = [];

            foreach ($cartInfo as $item) {
                $value = is_string($item['cart_info']) ? json_decode($item['cart_info'], true) : $item['cart_info'];

                $value['productInfo']['store_name'] = $value['productInfo']['store_name'] ?? "";
                $value['productInfo']['store_name'] = StoreOrderCartInfo::getSubstrUTf8($value['productInfo']['store_name'], 10, 'UTF-8', '');
                $product[] = $value;
            }
            if (!$product) {
                return JsonService::fail('订单商品获取失败,无法打印!');
            }
            $res = YLYService::instance()->setContent(sys_config('site_name'), is_object($order) ? $order->toArray() : $order, $product)->orderPrinting();
            if ($res) {
                return JsonService::successful('打印成功');
            } else {
                return JsonService::fail('打印失败');
            }
        } catch (\Exception $e) {
            Log::error('小票打印出现错误,错误原因：' . $e->getMessage());
            return JsonService::fail($e->getMessage());
        }
    }

    /**
     * 核销码核销
     * @param string $verify_code
     * @return html
     */
    public function write_order($verify_code = '', $is_confirm = 0)
    {
        if ($this->request->isAjax()) {
            if (!$verify_code) return Json::fail('缺少核销码！');
            StoreOrderModel::beginTrans();
            $orderInfo = StoreOrderModel::where('verify_code', $verify_code)->where('paid', 1)->where('refund_status', 0)->find();
            if (!$orderInfo) return Json::fail('核销订单不存在！');
            if ($orderInfo->status > 0) return Json::fail('订单已核销！');
            if ($orderInfo->combination_id && $orderInfo->pink_id) {
                $res = StorePink::where('id', $orderInfo->pink_id)->where('status', '<>', 2)->count();
                if ($res) return Json::fail('拼团订单暂未成功无法核销！');
            }
            if ($is_confirm == 0) {
                $orderInfo['nickname'] = User::where(['uid' => $orderInfo['uid']])->value('nickname');
                return Json::successful($orderInfo);
            }
            $orderInfo->status = 2;
            if ($orderInfo->save()) {
                OrderRepository::storeProductOrderTakeDeliveryAdmin($orderInfo);
                StoreOrderStatus::setStatus($orderInfo->id, 'take_delivery', '已核销');
                //发送短信
                event('ShortMssageSend', [$orderInfo['order_id'], 'Receiving']);
                StoreOrderModel::commitTrans();
                return Json::successful('核销成功！');
            } else {
                StoreOrderModel::rollbackTrans();
                return Json::fail('核销失败');
            }
        } else
            $this->assign('is_layui', 1);
        return $this->fetch();
    }

    public function orderchart()
    {
        $where = Util::getMore([
            ['status', ''],
            ['real_name', ''],
            ['is_del', 0],
            ['data', ''],
            ['combination_id', ''],
            ['export', 0],
            ['order', 'id desc']
        ], $this->request);
        $limitTimeList = [
            'today' => implode(' - ', [date('Y/m/d'), date('Y/m/d', strtotime('+1 day'))]),
            'week' => implode(' - ', [
                date('Y/m/d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600)),
                date('Y-m-d', (time() + (7 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600))
            ]),
            'month' => implode(' - ', [date('Y/m') . '/01', date('Y/m') . '/' . date('t')]),
            'quarter' => implode(' - ', [
                date('Y') . '/' . (ceil((date('n')) / 3) * 3 - 3 + 1) . '/01',
                date('Y') . '/' . (ceil((date('n')) / 3) * 3) . '/' . date('t', mktime(0, 0, 0, (ceil((date('n')) / 3) * 3), 1, date('Y')))
            ]),
            'year' => implode(' - ', [
                date('Y') . '/01/01', date('Y/m/d', strtotime(date('Y') . '/01/01 + 1year -1 day'))
            ])
        ];
        if ($where['data'] == '') $where['data'] = $limitTimeList['today'];
        $orderCount = [
            urlencode('未支付') => StoreOrderModel::getOrderWhere($where, StoreOrderModel::statusByWhere(0))->count(),
            urlencode('未发货') => StoreOrderModel::getOrderWhere($where, StoreOrderModel::statusByWhere(1))->count(),
            urlencode('待收货') => StoreOrderModel::getOrderWhere($where, StoreOrderModel::statusByWhere(2))->count(),
            urlencode('待评价') => StoreOrderModel::getOrderWhere($where, StoreOrderModel::statusByWhere(3))->count(),
            urlencode('交易完成') => StoreOrderModel::getOrderWhere($where, StoreOrderModel::statusByWhere(4))->count(),
            urlencode('退款中') => StoreOrderModel::getOrderWhere($where, StoreOrderModel::statusByWhere(-1))->count(),
            urlencode('已退款') => StoreOrderModel::getOrderWhere($where, StoreOrderModel::statusByWhere(-2))->count()
        ];
        $model = StoreOrderModel::getOrderWhere($where, new StoreOrderModel())->field('sum(total_num) total_num,count(*) count,sum(total_price) total_price,sum(refund_price) refund_price,from_unixtime(add_time,\'%Y-%m-%d\') add_time')
            ->group('from_unixtime(add_time,\'%Y-%m-%d\')');
        $orderPrice = $model->select()->toArray();
        $orderDays = [];
        $orderCategory = [
            ['name' => '商品数', 'type' => 'line', 'data' => []],
            ['name' => '订单数', 'type' => 'line', 'data' => []],
            ['name' => '订单金额', 'type' => 'line', 'data' => []],
            ['name' => '退款金额', 'type' => 'line', 'data' => []]
        ];
        foreach ($orderPrice as $price) {
            $orderDays[] = $price['add_time'];
            $orderCategory[0]['data'][] = $price['total_num'];
            $orderCategory[1]['data'][] = $price['count'];
            $orderCategory[2]['data'][] = $price['total_price'];
            $orderCategory[3]['data'][] = $price['refund_price'];
        }
        $this->assign(StoreOrderModel::systemPage($where, $this->adminId));
        $this->assign('price', StoreOrderModel::getOrderPrice($where));
        $this->assign(compact('limitTimeList', 'where', 'orderCount', 'orderPrice', 'orderDays', 'orderCategory'));
        return $this->fetch();
    }

    /**
     * 修改支付金额等
     * @param $id
     * @return mixed|\think\response\Json|void
     */
    public function edit($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        $f = [];
        $f[] = Form::input('order_id', '订单编号', $product->getData('order_id'))->disabled(1);
        $f[] = Form::number('total_price', '商品总价', $product->getData('total_price'))->min(0);
        $f[] = Form::number('total_postage', '原始邮费', $product->getData('total_postage'))->min(0);
        $f[] = Form::number('pay_price', '实际支付金额', $product->getData('pay_price'))->min(0);
        $f[] = Form::number('pay_postage', '实际支付邮费', $product->getData('pay_postage'));
        //        $f[] = Form::number('gain_integral', '赠送积分', $product->getData('gain_integral'));
        //        $f[] = Form::radio('status','状态',$product->getData('status'))->options([['label'=>'开启','value'=>1],['label'=>'关闭','value'=>0]]);
        $form = Form::make_post_form('修改订单', $f, Url::buildUrl('update', array('id' => $id)));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 修改订单提交更新
     * @param $id
     */
    public function update($id)
    {
        $data = Util::postMore([
            'order_id',
            'total_price',
            'total_postage',
            'pay_price',
            'pay_postage',
            'gain_integral',
        ]);
        if ($data['total_price'] <= 0) return Json::fail('请输入商品总价');
        if ($data['pay_price'] <= 0) return Json::fail('请输入实际支付金额');
        $orderInfo = StoreOrderModel::get($id);
        if (!$orderInfo) {
            return Json::fail('订单不存在');
        }
        $orderInfo->order_id = StoreOrderModel::changeOrderId($data['order_id']);
        $pay_price = $orderInfo->pay_price;
        $orderInfo->pay_price = $data['pay_price'];
        $orderInfo->total_price = $data['total_price'];
        $orderInfo->total_postage = $data['total_postage'];
        $orderInfo->pay_postage = $data['pay_postage'];
        $orderInfo->gain_integral = $data['gain_integral'];
        if ($orderInfo->save()) {
            //改价短信提醒
            if ($data['pay_price'] != $pay_price) {
                $switch = sys_config('price_revision_switch') ? true : false;
                ShortLetterRepositories::send($switch, $orderInfo->user_phone, ['order_id' => $orderInfo->order_id, 'pay_price' => $orderInfo->pay_price], 'PRICE_REVISION_CODE');
            }
            event('StoreProductOrderEditAfter', [$data, $id]);
            StoreOrderStatus::setStatus($id, 'order_edit', '修改商品总价为：' . $data['total_price'] . ' 实际支付金额' . $data['pay_price']);
            return Json::successful('修改成功!');
        } else {
            return Json::fail('订单修改失败');
        }
    }

    /*
     * 发送货
     * @param int $id
     * @return html
     * */
    public function order_goods($id = 0)
    {
        $orderinfo = StoreOrderModel::get($id);
        $list = Express::where('is_show', 1)
            ->where('uid', 0)
            ->whereOr('uid', $orderinfo['uid'])
            ->order('sort desc')
            ->field('name,id')
            ->select();
        $daishou = $orderinfo['order_type'] == 3 ? 1 : 0;
        $express_id = $orderinfo['express_id'];
        $this->assign([
            'list' => $list,
            'id' => $id,
            'daishou' => $daishou,
            'express_id' => $express_id
        ]);
        return $this->fetch();
    }

    /*
     * 删除订单
     * */
    public function del_order()
    {
        $ids = Util::postMore(['ids'])['ids'];
        if (!count($ids)) return Json::fail('请选择需要删除的订单');
        if (StoreOrderModel::where('is_del', 0)->where('id', 'in', $ids)->count())
            return Json::fail('您选择的的订单存在用户未删除的订单，无法删除用户未删除的订单');
        $res = StoreOrderModel::where('id', 'in', $ids)->update(['is_system_del' => 1]);
        if ($res)
            return Json::successful('删除成功');
        else
            return Json::fail('删除失败');
    }


    public function server_phone()
    {
        $phone = get_service_config('site_service_phone');
        return Json::successful(compact('phone'));
    }

    /**
     * TODO 送货信息提交
     * @param Request $request
     * @param $id
     */
    public function update_delivery($id = 0)
    {
        $data = Util::postMore([
            ['type', 1],
            ['delivery_name', ''],
            ['delivery_id', ''],
            ['sh_delivery_name', ''],
            ['sh_delivery_id', ''],
        ], $this->request);

        $orderinfo = StoreOrderModel::get($id);
        switch ((int) $data['type']) {
            case 1:
                //发货
                $data['delivery_type'] = 'express';
                if (!$data['delivery_name']) return Json::fail('请选择快递公司');
                if ($orderinfo['order_type'] != 3 && !$data['delivery_id']) return Json::fail('请输入快递单号');

                $data['express_id'] = $data['delivery_name'];
                $data['delivery_name'] = db('express')
                    ->where('id', $data['delivery_name'])
                    ->value('name');
                $data['status'] = 1;
                StoreOrderModel::edit($data, $id);
                $goods = StoreOrderCartInfo::where('oid', $id)->select()->toArray();
                if (empty($goods))  return Json::fail('订单商品信息丢失');
                $order = StoreOrderModel::where('id', $id)->find();
                UserMessage::create([
                    'uid' => $order['uid'],
                    'add_time' => time(),
                    'title' => '提示',
                    'content' => '您的订单:' . $order['order_id'] . '已发货',
                    'is_read' => 0
                ]);
                event('StoreProductOrderDeliveryGoodsAfter', [$data, $id]);
                StoreOrderStatus::setStatus($id, 'delivery_goods', '已发货 快递公司：' . $data['delivery_name'] . ' 快递单号：' . $data['delivery_id']);

                break;
            case 2:
                //送货
                $data['delivery_type'] = 'send';
                $data['delivery_name'] = $data['sh_delivery_name'];
                $data['delivery_id'] = $data['sh_delivery_id'];
                unset($data['sh_delivery_name'], $data['sh_delivery_id']);
                if (!$data['delivery_name']) return Json::fail('请输入送货人姓名');
                if (!(int) $data['delivery_id']) return Json::fail('请输入送货人电话号码');
                else if (!preg_match("/^1[3456789]{1}\d{9}$/", $data['delivery_id'])) return Json::fail('请输入正确的送货人电话号码');
                $data['status'] = 1;
                StoreOrderModel::edit($data, $id);
                event('StoreProductOrderDeliveryAfter', [$data, $id]);
                StoreOrderStatus::setStatus($id, 'delivery', '已配送 发货人：' . $data['delivery_name'] . ' 发货人电话：' . $data['delivery_id']);
                break;
            case 3:
                //虚拟发货
                $data['delivery_type'] = 'fictitious';
                $data['status'] = 1;
                StoreOrderModel::edit($data, $id);
                event('StoreProductOrderDeliveryAfter', [$data, $id]);
                StoreOrderStatus::setStatus($id, 'delivery_fictitious', '已虚拟发货');
                break;
            default:
                return Json::fail('暂时不支持其他发货类型');
                break;
        }
        //短信发送
        // event('ShortMssageSend', [StoreOrderModel::where('id', $id)->value('order_id'), 'Deliver']);
        return Json::successful('修改成功!');
    }


    public function update_delivery_bak($id = 0)
    {
        $data = Util::postMore([
            ['type', 1],
            ['delivery_name', ''],
            ['delivery_id', ''],
            ['sh_delivery_name', ''],
            ['sh_delivery_id', ''],
        ], $this->request);

        $orderinfo = StoreOrderModel::get($id);
        switch ((int) $data['type']) {
            case 1:
                //发货
                $data['delivery_type'] = 'express';
                if (!$data['delivery_name']) return Json::fail('请选择快递公司');
                if ($orderinfo['order_type'] != 3 && !$data['delivery_id']) return Json::fail('请输入快递单号');

                $data['express_id'] = $data['delivery_name'];
                $data['delivery_name'] = db('express')
                    ->where('id', $data['delivery_name'])
                    ->value('name');
                $data['status'] = 1;
                StoreOrderModel::edit($data, $id);
                $goods = StoreOrderCartInfo::where('oid', $id)->select()->toArray();
                if (empty($goods))  return Json::fail('订单商品信息丢失');
                foreach ($goods as  $v) {
                    $cartinfo = json_decode($v['cart_info'], true);
                    if (empty($cartinfo)) return Json::fail('商品异常');

                    if (empty($cartinfo['productInfo']['attrInfo']['advance_sale'])) continue;
                    //代表下单时该产品开启了无货预售，需扣减库存
                    try {
                        Db::startTrans();
                        $attr = $cartinfo['productInfo']['attrInfo'];
                        if ($attr['type']) {
                            $res = StoreProductAttrValue::where('suk', $attr['suk'])->where('product_id', $cartinfo['product_id'])->where('type', $attr['type'])
                                ->where('activity_id', $attr['activity_id'])
                                ->dec('stock', $cartinfo['cart_num'])
                                ->update();
                            $res = $res && StoreProductAttrValue::where('suk', $attr['suk'])->where('product_id', $cartinfo['product_id'])->where('type', 0)
                                ->dec('stock', $cartinfo['cart_num'])
                                ->update();
                            $res1 = StoreProductAttrValue::warehouse($cartinfo['product_id'], $attr['suk'], 0, $attr['type'], $cartinfo['cart_num'], 0);
                        } else {
                            $res = StoreProductAttrValue::where('unique', $cartinfo['product_attr_unique'])->where('product_id', $cartinfo['product_id'])->where('type', 0)->dec('stock', $cartinfo['cart_num'])->update();
                            $res1 = StoreProductAttrValue::warehouse($cartinfo['product_id'], $attr['unique'], 0, $attr['type'], $cartinfo['cart_num'], 0);
                        }

                        $res2 = StoreProduct::where('id', $cartinfo['product_id'])->dec('stock', $cartinfo['cart_num'])->update();

                        if (!$res || !$res1 || !$res2) exception('扣减库存失败');

                        Db::commit();
                    } catch (\Throwable $th) {
                        Db::rollback();
                        return Json::fail('扣减库存失败');
                    }
                }
                $order = StoreOrderModel::where('id', $id)->find();
                UserMessage::create([
                    'uid' => $order['uid'],
                    'add_time' => time(),
                    'title' => '提示',
                    'content' => '您的订单:' . $order['order_id'] . '已发货',
                    'is_read' => 0
                ]);
                event('StoreProductOrderDeliveryGoodsAfter', [$data, $id]);
                StoreOrderStatus::setStatus($id, 'delivery_goods', '已发货 快递公司：' . $data['delivery_name'] . ' 快递单号：' . $data['delivery_id']);

                break;
            case 2:
                //送货
                $data['delivery_type'] = 'send';
                $data['delivery_name'] = $data['sh_delivery_name'];
                $data['delivery_id'] = $data['sh_delivery_id'];
                unset($data['sh_delivery_name'], $data['sh_delivery_id']);
                if (!$data['delivery_name']) return Json::fail('请输入送货人姓名');
                if (!(int) $data['delivery_id']) return Json::fail('请输入送货人电话号码');
                else if (!preg_match("/^1[3456789]{1}\d{9}$/", $data['delivery_id'])) return Json::fail('请输入正确的送货人电话号码');
                $data['status'] = 1;
                StoreOrderModel::edit($data, $id);
                event('StoreProductOrderDeliveryAfter', [$data, $id]);
                StoreOrderStatus::setStatus($id, 'delivery', '已配送 发货人：' . $data['delivery_name'] . ' 发货人电话：' . $data['delivery_id']);
                break;
            case 3:
                //虚拟发货
                $data['delivery_type'] = 'fictitious';
                $data['status'] = 1;
                StoreOrderModel::edit($data, $id);
                event('StoreProductOrderDeliveryAfter', [$data, $id]);
                StoreOrderStatus::setStatus($id, 'delivery_fictitious', '已虚拟发货');
                break;
            default:
                return Json::fail('暂时不支持其他发货类型');
                break;
        }
        //短信发送
        // event('ShortMssageSend', [StoreOrderModel::where('id', $id)->value('order_id'), 'Deliver']);
        return Json::successful('修改成功!');
    }

    /**
     * TODO 填写送货信息
     * @param $id
     * @return mixed|void
     * @throws \think\exception\DbException
     */
    public function delivery($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        if ($product['paid'] == 1 && $product['status'] == 0) {
            $f = [];
            $f[] = Form::input('delivery_name', '送货人姓名')->required('送货人姓名不能为空', 'required:true;');
            $f[] = Form::input('delivery_id', '送货人电话')->required('请输入正确电话号码', 'telephone');
            $form = Form::make_post_form('修改订单', $f, Url::buildUrl('updateDelivery', array('id' => $id)), 7);
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');
        } else $this->failedNotice('订单状态错误');
    }

    /**
     * TODO 送货信息提交
     * @param $id
     */
    public function updateDelivery($id)
    {
        $data = Util::postMore([
            'delivery_name',
            'delivery_id',
        ]);
        $data['delivery_type'] = 'send';
        if (!$data['delivery_name']) return Json::fail('请输入送货人姓名');
        if (!(int) $data['delivery_id']) return Json::fail('请输入送货人电话号码');
        else if (!preg_match("/^1[3456789]{1}\d{9}$/", $data['delivery_id'])) return Json::fail('请输入正确的送货人电话号码');
        $data['status'] = 1;
        StoreOrderModel::edit($data, $id);
        event('StoreProductOrderDeliveryAfter', [$data, $id]);
        StoreOrderStatus::setStatus($id, 'delivery', '已配送 发货人：' . $data['delivery_name'] . ' 发货人电话：' . $data['delivery_id']);
        return Json::successful('修改成功!');
    }

    /**
     * TODO 填写发货信息
     * @param $id
     * @return mixed|void
     * @throws \think\exception\DbException
     */
    public function deliver_goods($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        if ($product['paid'] == 1 && $product['status'] == 0) {
            $f = [];
            $f[] = Form::select('delivery_name', '快递公司')->setOptions(function () {
                $list = Express::where('is_show', 1)->order('sort DESC')->column('name', 'id');
                $menus = [];
                foreach ($list as $k => $v) {
                    $menus[] = ['value' => $v, 'label' => $v];
                }
                return $menus;
            })->filterable(1);
            $f[] = Form::input('delivery_id', '快递单号');
            $form = Form::make_post_form('修改订单', $f, Url::buildUrl('updateDeliveryGoods', array('id' => $id)), 7);
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');
        } else return $this->failedNotice('订单状态错误');
    }

    /**
     * TODO 发货信息提交
     * @param $id
     */
    public function updateDeliveryGoods($id)
    {
        $data = Util::postMore([
            'delivery_name',
            'delivery_id',
        ]);
        $data['delivery_type'] = 'express';
        if (!$data['delivery_name']) return Json::fail('请选择快递公司');
        if (!$data['delivery_id']) return Json::fail('请输入快递单号');
        $data['status'] = 1;
        StoreOrderModel::edit($data, $id);
        event('StoreProductOrderDeliveryGoodsAfter', [$data, $id]);
        StoreOrderStatus::setStatus($id, 'delivery_goods', '已发货 快递公司：' . $data['delivery_name'] . ' 快递单号：' . $data['delivery_id']);
        return Json::successful('修改成功!');
    }

    /**
     * 修改状态为已收货
     * @param $id
     * @return \think\response\Json|void
     */
    public function take_delivery($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $order = StoreOrderModel::get($id);
        if (!$order) return Json::fail('数据不存在!');
        if ($order['status'] == 2) return Json::fail('不能重复收货!');
        if ($order['paid'] == 1 && $order['status'] == 1) $data['status'] = 2;
        else if ($order['pay_type'] == 'offline') $data['status'] = 2;
        else return Json::fail('请先发货或者送货!');
        StoreOrderModel::beginTrans();
        try {
            if (!StoreOrderModel::edit($data, $id)) {
                StoreOrderModel::rollbackTrans();
                return Json::fail(StoreOrderModel::getErrorInfo('收货失败,请稍候再试!'));
            } else {
                OrderRepository::storeProductOrderTakeDeliveryAdmin($order, $id);
                StoreOrderStatus::setStatus($id, 'take_delivery', '已收货');
                StoreOrderModel::commitTrans();
                //发送短信
                event('ShortMssageSend', [$order['order_id'], 'Receiving']);
                return Json::successful('收货成功!');
            }
        } catch (\Exception $e) {
            StoreOrderModel::rollbackTrans();
            return Json::fail($e->getMessage());
        }
    }

    /**
     * 修改退款状态
     * @param $id
     * @return \think\response\Json|void
     */
    public function refund_y($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);

        if (!$product) return Json::fail('数据不存在!');
        $f = [];
        $f[] = Form::radio('refund_status', '状态', 1)->options([['label' => '同意', 'value' => 1], ['label' => '拒绝', 'value' => 0]]);
        $f[] = Form::input('order_id', '退款单号', $product->getData('order_id'))->disabled(1);
        $f[] = Form::number('refund_price', '退款金额', $product->getData('pay_price'))->precision(2)->min(0.01)->readonly(true);
        $f[] = Form::textarea('refund_reason_wap', '退款原因', $product['refund_reason_wap'])->readonly(true);
        $f[] = Form::textarea('refund_reason_wap_explain', '用户备注', $product['refund_reason_wap_explain'])->readonly(true)->placeholder('');
        // $f[] = Form::frameImages('slider_image', '产品轮播图(640*640px)', Url::buildUrl('admin/widget.images/index', array('fodder' => 'slider_image')), json_decode($product['refund_reason_wap_img'], 1) ?: [])->maxLength(5)->icon('images')->width('100%')->height('500px');
        $form = Form::make_post_form('退款处理', $f, Url::buildUrl('updateRefundY', array('id' => $id)), 7);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 退款处理
     * @param $id
     */
    public function updateRefundY(Request $request)
    {
        $param = $request->param();
        $order = StoreOrderModel::find($param['id']);
        $order->refund_status = $param['refund_status'];
        $content = '退款失败，商家驳回';
        if ($param['refund_status']) {
            $order->plat_verify = 1;
            $order->refund_status = 1;
            $content = '商家审核成功，请等待平台审核';
        }
        $order->save();
        (new UserMessage())->create_message($order['uid'], '通知', '订单号:' . $order['order_id'] . $content);
        return Json::successful('审核成功!');
    }


    public function order_info($oid = '')
    {
        if (!$oid || !($orderInfo = StoreOrderModel::where('id', $oid)->append(['order_type_text', 'express_name'])->find()))
            return $this->failed('订单不存在!');
        $userInfo = User::getUserInfos($orderInfo['uid']);
        if ($userInfo['spread_uid']) {
            $spread = User::where('uid', $userInfo['spread_uid'])->value('nickname');
        } else {
            $spread = '';
        }
        $cartinfo = StoreOrderCartInfo::where('oid', $oid)
            ->field('cart_info')
            ->select();
        if($cartinfo->isEmpty()){
            $cartinfo = [];
        }else{
            $cartinfo = $cartinfo->append(['cart_info_filter'])->toArray();
            $cartinfo = array_column($cartinfo,'cart_info_filter');
        }
 
        $this->assign(compact('orderInfo', 'userInfo', 'spread','cartinfo'));
        return $this->fetch();
    }

    public function express($oid = '')
    {
        if (!$oid || !($order = StoreOrderModel::get($oid)))
            return $this->failed('订单不存在!');
        if ($order['delivery_type'] != 'express' || !$order['delivery_id']) return $this->failed('该订单不存在快递单号!');
        $cacheName = $order['order_id'] . $order['delivery_id'];
        $result = CacheService::get($cacheName, null);
        if ($result === null) {
            $result = ExpressService::query($order['delivery_id']);
            if (
                is_array($result) &&
                isset($result['result']) &&
                isset($result['result']['deliverystatus']) &&
                $result['result']['deliverystatus'] >= 3
            )
                $cacheTime = 0;
            else
                $cacheTime = 1800;
            CacheService::set($cacheName, $result, $cacheTime);
        }
        $this->assign([
            'order' => $order,
            'express' => $result
        ]);
        return $this->fetch();
    }

    /**
     * 修改配送信息
     * @param $id
     * @return mixed|\think\response\Json|void
     */
    public function distribution($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        $f = [];
        $f[] = Form::input('order_id', '订单号', $product->getData('order_id'))->disabled(1);
        if ($product['delivery_type'] == 'send') {
            $f[] = Form::input('delivery_name', '送货人姓名', $product->getData('delivery_name'));
            $f[] = Form::input('delivery_id', '送货人电话', $product->getData('delivery_id'));
        } else if ($product['delivery_type'] == 'express') {
            $f[] = Form::select('delivery_name', '快递公司', $product->getData('delivery_name'))->setOptions(function () {
                $list = Express::where('is_show', 1)->column('name', 'id');
                $menus = [];
                foreach ($list as $k => $v) {
                    $menus[] = ['value' => $v, 'label' => $v];
                }
                return $menus;
            });
            $f[] = Form::input('delivery_id', '快递单号', $product->getData('delivery_id'));
        }
        $form = Form::make_post_form('配送信息', $f, Url::buildUrl('updateDistribution', array('id' => $id)), 7);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 修改配送信息
     * @param $id
     */
    public function updateDistribution($id)
    {
        $data = Util::postMore([
            'delivery_name',
            'delivery_id',
        ]);
        if (!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        if ($product['delivery_type'] == 'send') {
            if (!$data['delivery_name']) return Json::fail('请输入送货人姓名');
            if (!(int) $data['delivery_id']) return Json::fail('请输入送货人电话号码');
            else if (!preg_match("/^1[3456789]{1}\d{9}$/", $data['delivery_id'])) return Json::fail('请输入正确的送货人电话号码');
        } else if ($product['delivery_type'] == 'express') {
            if (!$data['delivery_name']) return Json::fail('请选择快递公司');
            if (!$data['delivery_id']) return Json::fail('请输入快递单号');
        }
        StoreOrderModel::edit($data, $id);
        event('StoreProductOrderDistributionAfter', [$data, $id]);
        StoreOrderStatus::setStatus($id, 'distribution', '修改发货信息为' . $data['delivery_name'] . '号' . $data['delivery_id']);
        return Json::successful('修改成功!');
    }

    /**
     * 修改退款状态
     * @param $id
     * @return mixed|\think\response\Json|void
     */
    public function refund_n($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        $f[] = Form::input('order_id', '订单号', $product->getData('order_id'))->disabled(1);
        $f[] = Form::input('refund_reason', '退款原因')->type('textarea');
        $form = Form::make_post_form('退款', $f, Url::buildUrl('updateRefundN', array('id' => $id)));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 不退款原因
     * @param $id
     */
    public function updateRefundN($id)
    {
        $data = Util::postMore([
            'refund_reason',
        ]);
        if (!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        if (!$data['refund_reason']) return Json::fail('请输入退款原因');
        $data['refund_status'] = 0;
        StoreOrderModel::edit($data, $id);
        event('StoreProductOrderRefundNAfter', [$data['refund_reason'], $id]);
        StoreOrderStatus::setStatus($id, 'refund_n', '不退款原因:' . $data['refund_reason']);
        return Json::successful('修改成功!');
    }

    /**
     * 立即支付
     * @param $id
     */
    public function offline($id)
    {
        $res = StoreOrderModel::updateOffline($id);
        if ($res) {
            event('StoreProductOrderOffline', [$id]);
            StoreOrderStatus::setStatus($id, 'offline', '线下付款');
            return Json::successful('修改成功!');
        } else {
            return Json::fail(StoreOrderModel::getErrorInfo('修改失败!'));
        }
    }

    /**
     * 修改积分和金额
     * @param $id
     * @return mixed|\think\response\Json|void
     */
    public function integral_back($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        if ($product['paid'] == 1) {
            $f[] = Form::input('order_id', '退款单号', $product->getData('order_id'))->disabled(1);
            $f[] = Form::number('use_integral', '使用的积分', $product->getData('use_integral'))->min(0)->disabled(1);
            $f[] = Form::number('use_integrals', '已退积分', $product->getData('back_integral'))->min(0)->disabled(1);
            $f[] = Form::number('back_integral', '可退积分', bcsub($product->getData('use_integral'), $product->getData('use_integral')))->min(0);
            $form = Form::make_post_form('退积分', $f, Url::buildUrl('updateIntegralBack', array('id' => $id)), 7);
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');
        } else {
            return Json::fail('参数错误!');
        }
        return $this->fetch('public/form-builder');
    }

    /**
     * 退积分保存
     * @param $id
     */
    public function updateIntegralBack($id)
    {
        $data = Util::postMore([
            'back_integral',
        ]);
        if (!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        if ($data['back_integral'] <= 0) return Json::fail('请输入积分');
        if ($product['use_integral'] == $product['back_integral']) return Json::fail('已退完积分!不能再积分了');
        $back_integral = $data['back_integral'];
        $data['back_integral'] = bcadd($data['back_integral'], $product['back_integral'], 2);
        $bj = bccomp((float) $product['use_integral'], (float) $data['back_integral'], 2);
        if ($bj < 0) return Json::fail('退积分大于支付积分，请修改退积分');
        BaseModel::beginTrans();
        $integral = User::where('uid', $product['uid'])->value('integral');
        $res1 = User::bcInc($product['uid'], 'integral', $back_integral, 'uid');
        $res2 = UserBill::income('商品退积分', $product['uid'], 'integral', 'pay_product_integral_back', $back_integral, $product['id'], bcadd($integral, $back_integral, 2), '订单退积分' . floatval($back_integral) . '积分到用户积分');
        event('StoreOrderIntegralBack', [$product, $back_integral]);
        try {
            OrderRepository::storeOrderIntegralBack($product, $back_integral);
        } catch (\Exception $e) {
            BaseModel::rollbackTrans();
            return Json::fail($e->getMessage());
        }
        $res = $res1 && $res2;
        BaseModel::checkTrans($res);
        if (!$res) return Json::fail('退积分失败!');
        if ($product['pay_price'] == 0 && $bj == 0) {
            $data['refund_status'] = 2;
        }
        StoreOrderModel::edit($data, $id);
        StoreOrderStatus::setStatus($id, 'integral_back', '商品退积分：' . $data['back_integral']);
        return Json::successful('退积分成功!');
    }

    public function remark()
    {
        $data = Util::postMore(['id', 'remark']);
        if (!$data['id']) return Json::fail('参数错误!');
        if ($data['remark'] == '') return Json::fail('请输入要备注的内容!');
        $id = $data['id'];
        unset($data['id']);
        StoreOrderModel::edit($data, $id);
        return Json::successful('备注成功!');
    }

    public function order_status($oid)
    {
        if (!$oid) return $this->failed('数据不存在');
        $this->assign(StoreOrderStatus::systemPage($oid));
        return $this->fetch();
    }

    /*
     * 订单列表推荐人详细
     */
    public function order_spread_user($uid)
    {
        $spread = User::where('uid', $uid)->find();
        $this->assign('spread', $spread);
        return $this->fetch();
    }

    /**
     * 立即核销
     * @param $id
     */
    public function verify($id)
    {
        StoreOrderModel::beginTrans();
        $orderInfo = StoreOrderModel::where('id', $id)->find();
        if (!$orderInfo) return Json::fail('核销订单不存在！');
        if ($orderInfo->status > 0) return Json::fail('订单已核销！');
        if ($orderInfo->combination_id && $orderInfo->pink_id) {
            $res = StorePink::where('id', $orderInfo->pink_id)->where('status', '<>', 2)->count();
            if ($res) return Json::fail('拼团订单暂未成功无法核销！');
        }

        $orderInfo->status = 2;
        if ($orderInfo->save()) {
            OrderRepository::storeProductOrderTakeDeliveryAdmin($orderInfo);
            StoreOrderStatus::setStatus($orderInfo->id, 'take_delivery', '已核销');
            //发送短信
            event('ShortMssageSend', [$orderInfo['order_id'], 'Receiving']);
            StoreOrderModel::commitTrans();
            return Json::successful('核销成功！');
        } else {
            StoreOrderModel::rollbackTrans();
            return Json::fail('核销失败');
        }
    }


    /**
     * 导入excel发货的处理方法
     */
    public function post_file()
    {

        $param = input('param.');
        $success_num = 0;
        $fail_name = '';
        if (isset($param['file'])) {
            $orderModel = new \app\models\store\StoreOrder();
            //获取快递公司的数据
            foreach ($param['file'] as $val) {
                //获取文件名(去除后缀名)
                //                $file_name=$this->retrieve($val);
                //根据文件名来更新凭证
                $data = '/uploads/excel/' . $val;
                $res = PHPExcelService::readExcel($data);


                foreach ($res as $k => $v) {
                    if ($k == 0) {
                        continue;
                    }

                    $order_id = $v[0];
                    if (!$order_id) {
                        continue;
                    }
                    $order_id = trim($order_id);
                    $order_info = $orderModel
                        ->where('order_id', '=', $order_id)
                        ->field('id,pay_time,status')
                        ->find();
                    if ($order_info) {
                        $order_info = $order_info->toArray();
                    } else {
                        $fail_name .= $v[1] . ',';
                        continue;
                    }



                    //未支付的订单不处理
                    if (!$order_info['pay_time']) {
                        $fail_name .= $v[1] . ',';
                        continue;
                    }

                    //状态不是未发货和已发货的 不处理
                    $status_arr = [0, 1, 4];
                    if (!in_array($order_info['status'], $status_arr)) {
                        $fail_name .= $v[1] . ',';
                        continue;
                    }

                    if (!$v[1] || !$v[2]) {
                        $fail_name .= $v[0] . ',';
                        continue;
                    }

                    $success_num++;
                    $data = [
                        'delivery_name' => $v[1],
                        'delivery_type' => 'express',
                        'delivery_id' => $v[2],
                        'status' => 1,
                        'delivery_time' => time()
                    ];


                    $orderModel->where('id', '=', $order_info['id'])->update($data);
                }
                //                dump($res);exit;
                //读取excel内容
            }

            $msg = '成功导入了' . $success_num . '条，未成功单号：' . ($fail_name);
            return Json::successful($msg);
        } else {
            return Json::successful('导入失败，请检查模板格式是否正确!');
        }
    }

    function retrieve($filename)
    {
        $filename = str_replace(strrchr($filename, '.'), '', $filename);
        return $filename;
    }



    //多图上传方法
    public function fileupload()
    {

        // Make sure file is not cached (as it happens for example on iOS devices)
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        // Support CORS
        // header("Access-Control-Allow-Origin: *");
        // other CORS headers if any...
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit; // finish preflight CORS requests here
        }


        if (!empty($_REQUEST['debug'])) {
            $random = rand(0, intval($_REQUEST['debug']));
            if ($random === 0) {
                header("HTTP/1.0 500 Internal Server Error");
                exit;
            }
        }

        // header("HTTP/1.0 500 Internal Server Error");
        // exit;


        // 5 minutes execution time
        @set_time_limit(5 * 60);

        // Uncomment this one to fake upload time
        // usleep(5000);

        // Settings
        // $targetDir = ini_get("upload_tmp_dir") . DIRECTORY_SEPARATOR . "plupload";
        $targetDir = 'upload_tmp';
        $uploadDir = 'uploads/excel/';

        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds


        // Create target dir
        if (!file_exists($targetDir)) {
            @mkdir($targetDir);
        }

        // Create target dir
        if (!file_exists($uploadDir)) {
            @mkdir($uploadDir);
        }

        // Get a file name
        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid("file_");
        }

        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
        $uploadPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

        // Chunking might be enabled
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;


        // Remove old temp files
        if ($cleanupTargetDir) {
            if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
            }

            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

                // If temp file is current file proceed to the next
                if ($tmpfilePath == "{$filePath}_{$chunk}.part" || $tmpfilePath == "{$filePath}_{$chunk}.parttmp") {
                    continue;
                }

                // Remove temp file if it is older than the max age and is not the current file
                if (preg_match('/\.(part|parttmp)$/', $file) && (@filemtime($tmpfilePath) < time() - $maxFileAge)) {
                    @unlink($tmpfilePath);
                }
            }
            closedir($dir);
        }


        // Open temp file
        if (!$out = @fopen("{$filePath}_{$chunk}.parttmp", "wb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }

        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
            }

            // Read binary input stream and append it to temp file
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        } else {
            if (!$in = @fopen("php://input", "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        }

        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }

        @fclose($out);
        @fclose($in);

        rename("{$filePath}_{$chunk}.parttmp", "{$filePath}_{$chunk}.part");

        $index = 0;
        $done = true;
        for ($index = 0; $index < $chunks; $index++) {
            if (!file_exists("{$filePath}_{$index}.part")) {
                $done = false;
                break;
            }
        }
        if ($done) {
            if (!$out = @fopen($uploadPath, "wb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
            }

            if (flock($out, LOCK_EX)) {
                for ($index = 0; $index < $chunks; $index++) {
                    if (!$in = @fopen("{$filePath}_{$index}.part", "rb")) {
                        break;
                    }

                    while ($buff = fread($in, 4096)) {
                        fwrite($out, $buff);
                    }

                    @fclose($in);
                    @unlink("{$filePath}_{$index}.part");
                }

                flock($out, LOCK_UN);
            }
            @fclose($out);
        }

        // Return Success JSON-RPC response
        die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
    }
}
