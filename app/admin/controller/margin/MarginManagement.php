<?php
/**
 * Created by PhpStorm.
 * User: lingyun
 * Date: 2020/9/27
 * Time: 15:45
 * Desc: 保证金管理
 */
namespace app\admin\controller\margin;

use app\admin\model\system\MarginManagement as MarginManagementModel;
use Yansongda\Pay\Pay as YanSongDaPay;
use app\admin\model\system\BondPrice;
use app\api\controller\payservice\WxpayController;
use app\admin\controller\AuthController;
use think\facade\Session;
use crmeb\traits\CurdControllerTrait;
use app\admin\model\system\SystemConfig as SystemConfigModel;
use think\facade\Route as Url;
use app\admin\model\system\SystemAdmin;
use app\admin\model\system\{
    SystemAttachment, ShippingTemplates,RoutineCode as RoutineCodeModel
};
use crmeb\services\{
    JsonService, UtilService as Util, JsonService as Json, FormBuilder as Form
};
use Endroid\QrCode\QrCode;

/**
 * 保证金管理
 * Class StoreProduct
 * @package app\admin\controller\store
 */
class MarginManagement extends AuthController
{
    /**
     * @Author  lingyun
     * @Desc    生成订单号
     * return string
     */
    public function getNewOrderId($tenant_id='')
    {
        $yCode   = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));

        return $orderSn.sprintf("%06d",$tenant_id);
    }

    /**
     * @Author  lingyun
     * @Desc    支付
     */
    public function pay(){
        $margin_money = BondPrice::where(['id'=>1])->value('bond_price');
        $order_no = $this->getNewOrderId(session('tenant_id'));

        $this->assign('margin_money',$margin_money);
        $this->assign('order_no',$order_no);
        return $this->fetch();
    }

    /**
     * @Author  lingyun
     * @Desc    支付二维码
     */
    public function pay_order(){
        $data = Util::getMore([
            ['order_no', ''],
        ]);

//        $order_no = $this->getNewOrderId(session('tenant_id'));
        $order_no = $data['order_no'];
        $body = '缴纳保证金';
        $total_fee = BondPrice::where(['id'=>1])->value('bond_price');
        $total_fee = 0.01;
        $result = (new WxpayController())->merchant_pay_margin($order_no,$body,$total_fee);

        $data = [
            'tenant_id'=>session('tenant_id'),
            'admin_id'=>session('adminId'),
            'order_id'=>$order_no,
            'price'=>$total_fee,
            'type'=>1,      //支付方式，1-微信
            'is_pay'=>0,
            'add_time'=>time()
        ];

        (new MarginManagementModel())->create($data);

        $qrCode = new QrCode($result);

        header('Content-Type: '.$qrCode->getContentType());
        echo $qrCode->writeString();
        exit;
    }

    /**
     * @Author  lingyun
     * @Desc    查询订单支付状态
     */
    public function get_order_state(){
        $data = Util::getMore([
            ['order_no', ''],
        ]);

        $result = (new MarginManagementModel())->where('order_id',$data['order_no'])->find();

        if($result['is_pay'] == 1){
            return json(['code'=>200,'msg'=>'支付成功，等待后台审核']);
        }else{
            return json(['code'=>400,'msg'=>'支付失败']);
        }
        var_dump($result);
    }

}