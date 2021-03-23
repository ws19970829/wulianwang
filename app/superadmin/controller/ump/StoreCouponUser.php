<?php

namespace app\superadmin\controller\ump;

use app\models\store\StoreCouponIssueUser;
use app\superadmin\controller\AuthController;
use app\superadmin\model\wechat\WechatUser;
use crmeb\services\UtilService as Util;
use crmeb\services\JsonService as Json;
use app\superadmin\model\ump\StoreCoupon as CouponModel;
use app\superadmin\model\ump\StoreCouponUser as CouponUserModel;
use app\superadmin\model\wechat\WechatUser as UserModel;

/**
 * 优惠券发放记录控制器
 * Class StoreCategory
 * @package app\admin\controller\system
 */
class StoreCouponUser extends AuthController
{
    /**
     * @return mixed
     */
    public function index()
    {
        $where = Util::getMore([
            ['status', ''],
            ['is_fail', ''],
            ['coupon_title', ''],
            ['account', ''],
        ], $this->request);
        $this->assign('where', $where);
        $model = new StoreCouponIssueUser();
        $model = $model->alias('a')
            ->join('store_coupon_issue b', 'a.issue_coupon_id=b.id')
            ->join('store_coupon c', 'b.cid=c.id')
            ->join('user d', 'a.uid=d.uid');

        if ($where['status'] != '') $model = $model->where('a.is_use', $where['status']);
        if ($where['coupon_title'] != '') $model = $model->where('c.title', 'LIKE', "%$where[coupon_title]%");
        if ($where['account'] != '') {
            $uid = UserModel::where('account', "$where[account]")->column('uid', 'uid');
            $model = $model->where('a.uid', 'IN', implode(',', $uid));
        };

        //$model = $model->where('b.tenant_id', '=', session('tenant_id'));

        //        $model = $model->where('is_del',0);
        $model = $model->order('a.add_time', 'desc');
        $model = $model->field('a.add_time,b.add_time as start_time,b.end_time,a.is_use,d.account,c.coupon_price,c.use_min_price,c.coupon_time,c.title');

        $this->assign(StoreCouponIssueUser::page($model, function ($item) {
            if (empty($item['start_time']) || empty($item['end_time'])) {
                $item['start_time'] = '';
                $item['end_time'] = '不限时';
            } else {
                $item['start_time'] = date('Y-m-d H:i:s', $item['start_time']);
                $item['end_time'] = date('Y-m-d H:i:s', $item['end_time']);
            }
        }));
        return $this->fetch();
    }

    /**
     * 给已关注的用户发放优惠券
     * @param $id
     */
    public function grant_subscribe($id)
    {
        if (!$id) return Json::fail('数据不存在!');
        $coupon = CouponModel::get($id)->toArray();
        if (!$coupon) return Json::fail('数据不存在!');
        $user = UserModel::getSubscribe('uid');
        if (!CouponUserModel::setCoupon($coupon, $user))
            return Json::fail(CouponUserModel::getErrorInfo('发放失败,请稍候再试!'));
        else
            return Json::successful('发放成功!');
    }

    /**
     * 给所有人发放优惠券
     * @param $id
     */
    public function grant_all($id)
    {
        if (!$id) return Json::fail('数据不存在!');
        $coupon = CouponModel::get($id)->toArray();
        if (!$coupon) return Json::fail('数据不存在!');
        $user = UserModel::getUserAll('uid');
        if (!CouponUserModel::setCoupon($coupon, $user))
            return Json::fail(CouponUserModel::getErrorInfo('发放失败,请稍候再试!'));
        else
            return Json::successful('发放成功!');
    }

    /**
     * 发放优惠券到指定个人
     * @param $id
     * @param $uid
     * @return \think\response\Json
     */
    public function grant($id, $uid)
    {
        if (!$id) return Json::fail('数据不存在!');
        $coupon = CouponModel::get($id)->toArray();
        if (!$coupon) return Json::fail('数据不存在!');
        $user = explode(',', $uid);
        if (!CouponUserModel::setCoupon($coupon, $user))
            return Json::fail(CouponUserModel::getErrorInfo('发放失败,请稍候再试!'));
        else
            return Json::successful('发放成功!');

    }

    public function grant_group($id)
    {
        $data = Util::postMore([
            ['group', 0]
        ]);
        if (!$id) return Json::fail('数据不存在!');
        $coupon = CouponModel::get($id)->toArray();
        if (!$coupon) return Json::fail('数据不存在!');
        $user = WechatUser::where('groupid', $data['group'])->column('uid', 'uid');
        if (!CouponUserModel::setCoupon($coupon, $user))
            return Json::fail(CouponUserModel::getErrorInfo('发放失败,请稍候再试!'));
        else
            return Json::successful('发放成功!');
    }

    public function grant_tag($id)
    {
        $data = Util::postMore([
            ['tag', 0]
        ]);
        if (!$id) return Json::fail('数据不存在!');
        $coupon = CouponModel::get($id)->toArray();
        if (!$coupon) return Json::fail('数据不存在!');
        $user = WechatUser::where("tagid_list", "LIKE", "%$data[tag]%")->column('uid', 'uid');
        if (!CouponUserModel::setCoupon($coupon, $user))
            return Json::fail(CouponUserModel::getErrorInfo('发放失败,请稍候再试!'));
        else
            return Json::successful('发放成功!');
    }

}
