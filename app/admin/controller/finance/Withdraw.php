<?php

/**
 * Created by PhpStorm.
 * User: lianghuan
 * Date: 2018-03-03
 * Time: 16:37
 */

namespace app\admin\controller\finance;

use app\admin\controller\AuthController;
use app\admin\model\system\SystemConfig;
use think\facade\Route as Url;
use crmeb\services\JsonService;
use app\admin\model\user\UserExtract as UserExtractModel;
use app\admin\model\user\Withdraw as UserWithdraw;
use app\models\store\StoreOrder;
use app\models\user\SystemAdmin;
use crmeb\services\{UtilService as Util, FormBuilder as Form};
use think\Request;
use app\admin\model\user\Withdraw as WithdrawModel;

/**
 * 用户提现管理
 * Class UserExtract
 * @package app\admin\controller\finance
 */
class Withdraw extends AuthController
{
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 显示可提现订单列表
     */
    public function list()
    {
        $where = Util::getMore([
            ['start_time', ''],
            ['end_time', ''],
            ['order_id', ''],
            ['limit', 20],
            ['page', 1],
        ]);
        return JsonService::successlayui(UserWithdraw::getList($where));
    }

    public function edit($order_ids)
    {
        if (!$order_ids) return $this->failed('数据不存在');
        $total = StoreOrder::whereIn('id', $order_ids)->sum('give_store_cash');
        if ($total <= 0) return $this->failed('无需提现');
        $tenant_id = session('tenant_id');
        $commission = SystemConfig::getConfigValue('withdraw_rate');
        $rate = bcmul($total, $commission / 100, 2);
        $this->assign([
            'total' => $total,
            'tenant_id' => $tenant_id,
            'order_ids' => $order_ids,
            'rate' => $rate
        ]);
        return view();
        // $f = array();
        // $f[] = Form::number('extract_price', '提现金额', $total)->readonly(true)->precision(2);
        // $f[] = Form::radio('extract_type', '提现方式', 'bank')->options([['value' => 'bank', 'label' => '银行卡', 'checked' => true], ['value' => 'alipay', 'label' => '支付宝']]);
        // $f[] = Form::input('real_name', '姓名');
        // $f[] = Form::input('alipay_code', '支付宝账号', '');
        // $f[] = Form::input('bank_code', '银行卡号', '');
        // $f[] = Form::input('bank_name', '开户行', '');
        // $f[] = Form::input('mark', '备注', '')->type('textarea');
        // $f[] = Form::hidden('tenant_id', session('tenant_id'));
        // $form = Form::make_post_form('编辑', $f, Url::buildUrl('save', array('order_ids' => $order_ids)));
        // $this->assign(compact('form'));
        // return $this->fetch('public/form-builder');
    }

    public function save(Request $request)
    {
        $param = $request->param();
        $validate = \think\facade\Validate::rule([
            'order_ids|订单' => 'require',
            'extract_price|提现金额' => 'require|float|gt:0',
            'tenant_id' => 'require',
            'alipay_code|支付宝号' => 'requireIf:extract_type,alipay',
            'bank_code|银行卡号' => 'requireIf:extract_type,bank',
            'bank_name|开户行' => 'requireIf:extract_type,bank',
            'extract_type|提现方式' => 'require|in:alipay,bank,wx',
            'real_name|姓名' => 'require|max:30',
            'mark|备注' => 'max:255',
        ]);

        if (!$validate->check($param)) {
            return JsonService::fail($validate->getError(), []);
        }
        if (empty($param['order_ids'])) return JsonService::fail('参数缺失!');
        if (!WithdrawModel::apply($param)) {
            return JsonService::fail(UserExtractModel::getErrorInfo('申请失败'));
        }
        return JsonService::successful('申请成功!');
    }
}
