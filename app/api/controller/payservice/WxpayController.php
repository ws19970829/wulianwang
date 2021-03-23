<?php

/**
 * Created by PhpStorm.
 * User: lingyun
 * Date: 2020/6/18
 * Time: 13:48
 * Desc: 微信支付
 */

namespace app\api\controller\payservice;

use Yansongda\Pay\Pay as YanSongDaPay;
use crmeb\services\SystemConfigService;
use app\models\store\StoreOrder;
use app\models\store\StoreOrderRequest;
use app\admin\model\system\SystemAdmin;
use app\admin\model\system\MarginManagement as MarginManagementModel;
use app\Request;

use think\facade\Db;

class WxpayController
{
    protected $config = [
        'appid' => 'wx39b49fda875351c6', // APP APPID
        'app_id' => 'wx39b49fda875351c6', // 公众号 APPID
        'miniapp_id' => 'wx39b49fda875351c6', // 小程序 APPID
        'mch_id' => '1604276507',
        'key' => '35b7e90d547905ac71ac2b73425ef07e',
        'notify_url' => 'http://wujin.3todo.com/api/wechat/notify',
        'cert_client' => './apiclient_cert.pem', // optional，退款等情况时用到
        'cert_key' => './apiclient_key.pem', // optional，退款等情况时用到
        'log' => [ // optional
            'file' => '../runtime/log/weixin_notify.logg',
            'level' => 'debug'
        ],
        //        'mode' => 'optional', // optional, dev/hk;当为 `hk` 时，为香港 gateway。
    ];

    public function initialize()
    {
        // $where[] = ['menu_name', 'in', ['wechat_appid', 'pay_weixin_appsecret', 'pay_weixin_mchid', 'pay_weixin_key', 'pay_weixin_client_cert', 'pay_weixin_client_key']];
        // $pay_config = Db::name('system_config')->where('tenant_id', 1)->where($where)->column('value', 'menu_name');

        // $this->config['app_id'] = trim($pay_config['wechat_appid']);
        // $this->config['mch_id'] = trim($pay_config['pay_weixin_mchid']);
        // $this->config['key'] = trim($pay_config['pay_weixin_key']);
        // $this->config['cert_client'] = trim($pay_config['pay_weixin_client_cert']);
        // $this->config['cert_key'] = trim($pay_config['pay_weixin_client_key']);
    }


    /**
     * @Author  lingyun
     * @Desc    微信支付回调
     */
    public function notify()
    {
        $pay = YanSongDaPay::wechat($this->config);
        try {
            $data = $pay->verify(); // 是的，验签就这么简单！
            $info = $data->all();

            if ($info['result_code'] != 'SUCCESS' && $info['return_code'] != 'SUCCESS') {
                return false;
            }
            if (!StoreOrder::paySuccess($info['out_trade_no'])) {
                return false;
            }
            //            Log::debug('Pay notify', $data->all());
        } catch (\Exception $e) {
            // $e->getMessage();
          
            trace($e->getMessage().$e->getFile().$e->getLine(),'error');
        }
        return $pay->success()->send();
    }


 

    /**
     * @Author  lingyun
     * @Desc    查询订单详情
     * @param $order_sn
     * return \Yansongda\Supports\Collection
     */
    public function search_order($order_sn)
    {
        $result = YanSongDaPay::wechat($this->config)->find($order_sn);

        return $result;
    }

    /**
     * @Author  lingyun
     * @Desc    微信app支付
     * @param $order_id
     * @param $body
     * @param $pay_price
     * return mixed
     */
    public function wxapppay($order_id, $body, $pay_price)
    {
        $order = [
            'out_trade_no' => $order_id,
            'body' => $body,
            //'total_fee' => $pay_price * 100,
            'total_fee' => 0.01 * 100,
        ];

        $pay = YanSongDaPay::wechat($this->config)->app($order);
        return $pay->getContent();
    }

    /**退款 */
    public function refund($out_trade_no = '', $total_fee = 0, $refund_fee = 0)
    {
        $config = $this->config;
        $payment = SystemConfigService::more(['pay_weixin_client_cert', 'pay_weixin_client_key']);
        // $config['cert_client'] = '.' . trim($payment['pay_weixin_client_cert'], ' ');
        // $config['cert_key'] = '.' . trim($payment['pay_weixin_client_key'], ' ');
        $config['notify_url'] = request()->domain() . '/api/wxrefund_notify';
        // $total_fee = 0.01;
        // $refund_fee = 0.01;
        $order = [
            'out_trade_no' => $out_trade_no,
            'out_refund_no' => time(),
            'total_fee' => $total_fee * 100,
            'refund_fee' => $refund_fee * 100,
            'refund_desc' => '订单退款',
        ];

        $result = YanSongDaPay::wechat($config)->refund($order);
    }
}
