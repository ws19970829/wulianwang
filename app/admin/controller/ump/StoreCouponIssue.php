<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2018/01/17
 */

namespace app\admin\controller\ump;

use app\admin\controller\AuthController;
use think\facade\Route as Url;
use crmeb\traits\CurdControllerTrait;
use crmeb\services\{JsonService, FormBuilder as Form, UtilService as Util};
use app\admin\model\ump\{StoreCouponIssue as CouponIssueModel, StoreCouponIssueUser};

class StoreCouponIssue extends AuthController
{
    use CurdControllerTrait;

    protected $bindModel = CouponIssueModel::class;

    //普通优惠券
    public function index()
    {
        $where = Util::getMore([
            ['status', ''],
            ['coupon_title', ''],
            ['type',''],
            ['is_bag',0]
        ]);

        $this->assign(CouponIssueModel::stsypage($where));
        $this->assign('where', $where);
        return $this->fetch();
    }

    //礼包优惠券
    public function index_bag()
    {
        $where = Util::getMore([
            ['status', ''],
            ['coupon_title', ''],
            ['type',''],
            ['is_bag',1]
        ]);
        $this->assign(CouponIssueModel::stsypage($where));
        $this->assign('where', $where);
        return $this->fetch();
    }

    //获取已发布的优惠券
    public function get_list(){
        $where = Util::getMore([
            ['status', 1],
            ['coupon_title', ''],
            ['type',''],
            ['is_bag',0]
        ]);
        $model=CouponIssueModel::getModelToSelect($where);
        $count=$model->count();
        $data=$model->select();
        if($count>0){
            $data=$data->toArray();
        }
        $return = compact('count', 'data');

        return JsonService::successlayui($return);
    }

    public function delete($id = '')
    {
        if (!$id) return JsonService::fail('参数有误!');
        if (CouponIssueModel::edit(['is_del' => 1], $id, 'id'))
            return JsonService::successful('删除成功!');
        else
            return JsonService::fail('删除失败!');
    }

    public function edit($id = '')
    {
        if (!$id) return JsonService::fail('参数有误!');
        $issueInfo = CouponIssueModel::get($id);
        if (-1 == $issueInfo['status'] || 1 == $issueInfo['is_del']) return $this->failed('状态错误,无法修改');
        $f = [Form::radio('status', '是否开启', $issueInfo['status'])->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]])];
        $form = Form::make_post_form('状态修改', $f, Url::buildUrl('change_field', array('id' => $id, 'field' => 'status')));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    public function issue_log($id = '')
    {
        if (!$id) return JsonService::fail('参数有误!');
        $this->assign(StoreCouponIssueUser::systemCouponIssuePage($id));
        return $this->fetch();
    }
}