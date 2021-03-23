<?php

namespace app\admin\controller\collection;

use app\admin\controller\AuthController;

use app\admin\model\store\StoreCategory as CategoryModel;
use crmeb\services\{
    FormBuilder as Form, JsonService as Json, UtilService as Util, WechatService
};

/**x
 * 合作商家
 * Class StoreCategory
 * @package app\admin\controller\system
 */
class CollectionOrder extends AuthController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //获取所有合作商家的阅读总次数
        $view_total=0;
        //获取通过合作商家来下单支付的订单总金额
        $pay_money_total=0;
        $this->assign('pay_money_total',$pay_money_total);

        return $this->fetch();
    }


    /*
     *  异步获取分类列表
     *  @return json
     */
    public function order_list()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['order', ''],
            ['title', ''],
            ['start_time', ''],
            ['end_time', '']
        ]);
        return Json::successlayui(\app\admin\model\collection\CollectionOrder::getOrderList($where));
    }




    /**
     * 设置产品分类上架|下架
     * @param string $is_show
     * @param string $id
     */
    public function set_show($is_show = '', $id = '')
    {
        ($is_show == '' || $id == '') && Json::fail('缺少参数');
        if (\app\admin\model\collection\Collection::setShow($id, (int)$is_show)) {
            return Json::successful($is_show == 1 ? '显示成功' : '隐藏成功');
        } else {
            return Json::fail(CategoryModel::getErrorInfo($is_show == 1 ? '显示失败' : '隐藏失败'));
        }
    }


    public function Refund(){
        $id=input('param.id');
        if(!$id){
            return Json::fail('参数错误，请稍后再试');
        }

        $info=\app\admin\model\collection\CollectionOrder::where('id','=',$id)->find();
        if(!$info){
            return Json::fail('该订单不存在，请稍后再试');
        }

        $info=$info->toArray();

        if(!$info['paid']){
            return Json::fail('该订单支付状态有误，无法退款');
        }

        $orderNo=$info['order_id'];

        $refund_data['pay_price'] = $info['pay_price'];
        $refund_data['refund_price'] = $info['pay_price'];
        $res=WechatService::payOrderRefund($orderNo, $refund_data);
        if($res){
            $data=[
                'is_refund'=>1,
                'refund_time'=>time(),
            ];
            \app\admin\model\collection\CollectionOrder::where('id','=',$id)->update($data);
            return Json::successful('退款成功');
        }else{
            return Json::fail('退款失败，请稍后再试');

        }

    }
}
