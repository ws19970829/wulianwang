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
use app\models\store\StoreOrder;
use app\models\store\StoreVisit;
use app\models\user\UserBill;
use app\Request;
use crmeb\services\{UtilService as Util, FormBuilder as Form};

/**
 * 用户提现管理
 * Class UserExtract
 * @package app\admin\controller\finance
 */
class UserExtract extends AuthController
{
    public function index()
    {
        $where = Util::getMore([
            ['status', ''],
            ['nickname', ''],
            ['extract_type', ''],
            ['nireid', ''],
            ['date', ''],
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
        $this->assign('where', $where);
        $this->assign('limitTimeList', $limitTimeList);
        $this->assign(UserExtractModel::extractStatistics());
        $this->assign(UserExtractModel::systemPage($where));
        return $this->fetch();
    }

    public function edit($id)
    {

        if (!$id) return $this->failed('数据不存在');
        $UserExtract = UserExtractModel::get($id);
        if (!$UserExtract) return JsonService::fail('数据不存在!');
        $f = array();
        $f[] = Form::number('extract_price', '提现金额', $UserExtract['extract_price'])->readonly(true)->precision(2);
        $f[] = Form::radio('extract_type', '提现方式', $UserExtract['extract_type'])->options([['value' => 'bank', 'label' => '银行卡', 'checked' => true], ['value' => 'alipay', 'label' => '支付宝']]);
        $f[] = Form::input('real_name', '姓名', $UserExtract['real_name']);
        $f[] = Form::input('alipay_code', '支付宝账号', $UserExtract['alipay_code']);
        $f[] = Form::input('bank_code', '银行卡号', $UserExtract['bank_code']);
        $f[] = Form::input('bank_name', '开户行', $UserExtract['bank_name']);
        $f[] = Form::input('mark', '备注', $UserExtract['mark'])->type('textarea');
        $f[] = Form::hidden('tenant_id', session('tenant_id'));
        $form = Form::make_post_form('编辑', $f, Url::buildUrl('update', array('id' => $id)));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    public function read($id)
    {

        if (!$id) return $this->failed('数据不存在');
        $UserExtract = UserExtractModel::get($id);
        if (!$UserExtract) return $this->failed('数据不存在');;
        $this->assign('vo', $UserExtract);
        return view();
    }

    public function update(Request $request)
    {
        $param = $request->param();
        $validate = \think\facade\Validate::rule([
            'id' => 'require|integer|gt:0',
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
        $UserExtract = UserExtractModel::get($param['id']);
        if (!$UserExtract) return JsonService::fail('数据不存在!');
        if ($UserExtract->status != -1)  return JsonService::fail('状态有误!');
        $extract_number = $UserExtract['extract_price'];
        try {
            $param['status'] = 0;
            $param['add_time'] = time();
            $res1 = UserExtractModel::edit($param, $param['id']);
            $res2 = StoreOrder::whereIn('id', $UserExtract->order_ids)->update(['withdraw' => 0]);
            if (!$res1 && !$res2) exception('申请失败');
            $bili = SystemConfig::getConfigValue('withdraw_rate');

            $rate = bcmul($extract_number, $bili / 100, 2);

            $give_shop = bcsub($extract_number, $rate, 2);
            //申请提现记录
            UserBill::expend('可用金额', 0, 'now_money', 'extract', $give_shop, 0, 0, '提现' . floatval($give_shop) . '元', 1, session('tenant_id'));
            // UserBill::expend('提现手续费', 0, 'now_money', 'rate', $rate, 0, 0, '提现手续费扣除' . floatval($rate) . '元', 1, session('tenant_id'));
        } catch (\Throwable $th) {
            return JsonService::fail('修改失败');
        }
        return JsonService::successful('修改成功!');
    }

    public function fail($id)
    {
        if (!UserExtractModel::be(['id' => $id, 'status' => 0])) return JsonService::fail('操作记录不存在或状态错误!');
        $fail_msg = request()->post();
        $extract = UserExtractModel::get($id);
        if (!$extract) return JsonService::fail('操作记录不存在!');
        if ($extract->status == 1) return JsonService::fail('已经提现,错误操作');
        if ($extract->status == -1) return JsonService::fail('您的提现申请已被拒绝,请勿重复操作!');
        $res = UserExtractModel::changeFail($id, $fail_msg['message']);
        if ($res) {
            return JsonService::successful('操作成功!');
        } else {
            return JsonService::fail('操作失败!');
        }
    }

    public function succ($id)
    {
        if (!UserExtractModel::be(['id' => $id, 'status' => 0]))
            return JsonService::fail('操作记录不存在或状态错误!');
        UserExtractModel::beginTrans();
        $extract = UserExtractModel::get($id);
        if (!$extract) return JsonService::fail('操作记录不存!');
        if ($extract->status == 1) return JsonService::fail('您已提现,请勿重复提现!');
        if ($extract->status == -1) return JsonService::fail('您的提现申请已被拒绝!');
        $res = UserExtractModel::changeSuccess($id);
        if ($res) {
            UserExtractModel::commitTrans();
            return JsonService::successful('操作成功!');
        } else {
            UserExtractModel::rollbackTrans();
            return JsonService::fail('操作失败!');
        }
    }
}
