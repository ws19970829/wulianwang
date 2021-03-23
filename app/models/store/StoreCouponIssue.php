<?php

/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2018/01/18
 */

namespace app\models\store;


use app\models\user\User;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\db\Where;

/**
 * TODO 发布优惠券Model
 * Class StoreCouponIssue
 * @package app\models\store
 */
class StoreCouponIssue extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_coupon_issue';

    use ModelTrait;

    public function used()
    {
        return $this->hasOne(StoreCouponIssueUser::class, 'issue_coupon_id', 'id')->field('issue_coupon_id');
    }

    protected function getCouponPriceAttr($val)
    {
        return floatval($val);
    }

    protected function getUseMinPriceAttr($val)
    {
        return floatval($val);
    }

    protected function getCateNameAttr($v, $data)
    {
        return  db('store_category')->where('id', '=', function ($query) use ($data) {
            $query->name('store_coupon')->where('id', $data['cid'])->field('category_id');
        })->value('cate_name') ?? '无';
    }



    public static function getIssueCouponList1($uid, $limit, $page = 0, $type = 0, $product_id = 0, $tenant_id = 0, $is_public = 1, $is_bag = 0, $query = 1)
    {
        //1已领取 2未领取 3全部
        $model1 = self::validWhere('A')->alias('A')
            ->join('store_coupon B', 'A.cid = B.id')
            ->where('A.tenant_id', '=', $tenant_id)
            ->where('A.status', 1)
            ->where('B.tenant_id', '=', $tenant_id)
            ->where('A.is_public', '=', $is_public)
            ->where('A.is_bag', '=', $is_bag)
            ->field('A.*,B.type,B.coupon_price,B.use_min_price,B.title,B.des')
            ->order('B.sort DESC,A.id DESC');
        $model2 = self::validWhere('A')->alias('A')
            ->join('store_coupon B', 'A.cid = B.id')
            ->where('A.tenant_id', '=', $tenant_id)
            ->where('A.status', 1)
            ->where('B.tenant_id', '=', $tenant_id)
            ->where('A.is_public', '=', $is_public)
            ->where('A.is_bag', '=', $is_bag)
            ->field('A.*,B.type,B.coupon_price,B.use_min_price,B.title,B.des')
            ->order('B.sort DESC,A.id DESC');
        $model3 = self::validWhere('A')->alias('A')
            ->join('store_coupon B', 'A.cid = B.id')
            ->where('A.status', 1)
            ->where('A.tenant_id', '=', 0)
            ->where('B.tenant_id', '=', 0)
            ->where('A.is_public', '=', $is_public)
            ->where('A.is_bag', '=', $is_bag)
            ->field('A.*,B.type,B.coupon_price,B.use_min_price,B.title,B.des')
            ->order('B.sort DESC,A.id DESC');

        if ($uid) {
            if ($query == 1) {
                $ids = db('store_coupon_issue_user')
                    ->where('uid', $uid)
                    ->where('is_use', 0)
                    ->whereTime('fail_time', '>', time())
                    ->column('issue_coupon_id') ?? [];
                //已领取未使用
                $model1->whereIn('A.id', $ids);
                $model2->whereIn('A.id', $ids);
                $model3->whereIn('A.id', $ids);
            } elseif ($query == 2) {
                //未领取
                $ids = db('store_coupon_issue_user')
                    ->where('uid', $uid)
                    ->column('issue_coupon_id') ?? [];
                $model1->whereNotIn('A.id', $ids);
                $model2->whereNotIn('A.id', $ids);
                $model3->whereNotIn('A.id', $ids);
            }
            $model1->with(['used' => function ($query) use ($uid) {
                $query->where('uid', $uid);
            }]);

            $model2->with(['used' => function ($query) use ($uid) {
                $query->where('uid', $uid);
            }]);

            $model3->with(['used' => function ($query) use ($uid) {
                $query->where('uid', $uid);
            }]);
        }

        $lst1 = $lst2 = $lst3 = [];
        $lst3 = $model3->where('B.type', 0)
            ->where('is_give_subscribe', 0)
            ->where('is_full_give', 0)
            ->select()
            ->hidden(['is_del', 'status'])
            ->toArray();

        if (!empty($tenant_id)) {
            //商家券
            if (!empty($product_id)) {
                //如果设置了可使用类别，不合法的不展示
                $cate = db('store_product')->where('id', $product_id)->value('cate_id') ?? '';
                $cate = explode(',', $cate);
                array_push($cate, 0);

                $model2->where('category_id', 'in', $cate);
                //获取商品券
                $lst1 = $model1->where('B.type', 2)
                    ->where('is_give_subscribe', 0)
                    ->where('is_full_give', 0)
                    ->whereFindinSet('B.product_id', $product_id)
                    ->select()
                    ->hidden(['is_del', 'status'])
                    ->toArray();
            }

            $lst2 = $model2->where('B.type', 1)
                ->where('is_give_subscribe', 0)
                ->where('is_full_give', 0)
                ->select()
                ->hidden(['is_del', 'status']);
            $lst2 = $lst2->append(['cate_name'])->toArray();
        }
        if (!empty($product_id)) {
            //商品券
            $lst1 = $model1->where('B.type', 2)
                ->where('is_give_subscribe', 0)
                ->where('is_full_give', 0)
                ->whereFindinSet('B.product_id', $product_id)
                ->select()
                ->hidden(['is_del', 'status'])
                ->toArray();
        }

        $list = array_merge($lst1, $lst2, $lst3);

        $list = array_unique_fb($list);

        if ($page) $list = array_slice($list, ((int) $page - 1) * $limit, $limit);

        foreach ($list as $k => $v) {
            $v['is_use'] = $uid ? isset($v['used']) : false;
            if (!$v['end_time']) {
                $v['start_time'] = '';
                $v['end_time'] = '不限时';
            } else {
                $v['start_time'] = date('Y/m/d', $v['start_time']);
                $v['end_time'] = $v['end_time'] ? date('Y/m/d', $v['end_time']) : date('Y/m/d', time() + 86400);
            }
            $v['coupon_price'] = $v['coupon_price'];
            $v['error_tips'] = '';
            $v['shop_name'] = '';
            if (!empty($v['tenant_id'])) {
                $v['shop_name'] = db('system_admin')->where('id', $v['tenant_id'])->value('real_name');
                $v['error_tips'] = '该优惠券仅可用于' . $v['shop_name'];
            }
            $list[$k] = $v;
        }
        if ($list)
            return $list;
        else
            return [];
    }


    public static function getIssueCouponList($uid, $limit, $page = 0, $type = 0, $product_id = 0, $tenant_id = 0, $is_public = 1, $is_bag = 0, $query = 1)
    {
        //1已领取 2未领取 3全部
        $model1 = self::validWhere('A')->alias('A')
            ->join('store_coupon B', 'A.cid = B.id')
            ->where('A.tenant_id', '=', $tenant_id)
            ->where('A.status', 1)
            ->where('B.tenant_id', '=', $tenant_id)
            ->where('A.is_public', '=', $is_public)
            ->where('A.is_bag', '=', $is_bag)
            ->field('A.*,B.type,B.coupon_price,B.use_min_price,B.title,B.des')
            ->order('B.sort DESC,A.id DESC');
        $model2 = self::validWhere('A')->alias('A')
            ->join('store_coupon B', 'A.cid = B.id')
            ->where('A.tenant_id', '=', $tenant_id)
            ->where('A.status', 1)
            ->where('B.tenant_id', '=', $tenant_id)
            ->where('A.is_public', '=', $is_public)
            ->where('A.is_bag', '=', $is_bag)
            ->field('A.*,B.type,B.coupon_price,B.use_min_price,B.title,B.des')
            ->order('B.sort DESC,A.id DESC');
        $model3 = self::validWhere('A')->alias('A')
            ->join('store_coupon B', 'A.cid = B.id')
            ->where('A.status', 1)
            ->where('A.tenant_id', '=', $tenant_id)
            ->where('B.tenant_id', '=', $tenant_id)
            ->where('A.is_public', '=', $is_public)
            ->where('A.is_bag', '=', $is_bag)
            ->field('A.*,B.type,B.coupon_price,B.use_min_price,B.title,B.des')
            ->order('B.sort DESC,A.id DESC');

        if ($uid) {
            if ($query == 1) {
                $ids = db('store_coupon_issue_user')
                    ->where('uid', $uid)
                    ->where('is_use', 0)
                    ->whereTime('fail_time', '>', time())
                    ->column('issue_coupon_id') ?? [];
                //已领取未使用
                $model1->whereIn('A.id', $ids);
                $model2->whereIn('A.id', $ids);
                $model3->whereIn('A.id', $ids);
            } elseif ($query == 2) {
                //未领取
                $ids = db('store_coupon_issue_user')
                    ->where('uid', $uid)
                    ->column('issue_coupon_id') ?? [];
                $model1->whereNotIn('A.id', $ids);
                $model2->whereNotIn('A.id', $ids);
                $model3->whereNotIn('A.id', $ids);
            }
            $model1->with(['used' => function ($query) use ($uid) {
                $query->where('uid', $uid);
            }]);

            $model2->with(['used' => function ($query) use ($uid) {
                $query->where('uid', $uid);
            }]);

            $model3->with(['used' => function ($query) use ($uid) {
                $query->where('uid', $uid);
            }]);
        }

        $lst1 = $lst2 = $lst3 = [];

        if (!empty($tenant_id)) {
            //商家券
            if (!empty($product_id)) {
                //如果设置了可使用类别，不合法的不展示
                $cate = db('store_product')->where('id', $product_id)->value('cate_id') ?? '';
                $cate = explode(',', $cate);
                array_push($cate, 0);

                $model2->where('category_id', 'in', $cate);
                //获取商品券
                $lst1 = $model1->where('B.type', 2)
                    ->where('is_give_subscribe', 0)
                    ->where('is_full_give', 0)
                    ->whereFindinSet('B.product_id', $product_id)
                    ->select()
                    ->hidden(['is_del', 'status'])
                    ->toArray();
            }

            $lst2 = $model2->where('B.type', 1)
                ->where('is_give_subscribe', 0)
                ->where('is_full_give', 0)
                ->select()
                ->hidden(['is_del', 'status']);
            $lst2 = $lst2->append(['cate_name'])->toArray();
        } elseif (!empty($product_id) && empty($tenant_id)) {
            //商品券
            $lst1 = $model1->where('B.type', 2)
                ->where('is_give_subscribe', 0)
                ->where('is_full_give', 0)
                ->whereFindinSet('B.product_id', $product_id)
                ->select()
                ->hidden(['is_del', 'status'])
                ->toArray();
        } else {
            //平台券
            $lst3 = $model3->where('B.type', 0)
                ->where('is_give_subscribe', 0)
                ->where('is_full_give', 0)
                ->select()
                ->hidden(['is_del', 'status'])
                ->toArray();
        }

        $list = array_merge($lst1, $lst2, $lst3);

        $list = array_unique_fb($list);

        if ($page) $list = array_slice($list, ((int) $page - 1) * $limit, $limit);

        foreach ($list as $k => $v) {
            $v['is_use'] = $uid ? isset($v['used']) : false;
            if (!$v['end_time']) {
                $v['start_time'] = '';
                $v['end_time'] = '不限时';
            } else {
                $v['start_time'] = date('Y/m/d', $v['start_time']);
                $v['end_time'] = $v['end_time'] ? date('Y/m/d', $v['end_time']) : date('Y/m/d', time() + 86400);
            }
            $v['coupon_price'] = $v['coupon_price'];
            $v['error_tips'] = '';
            $v['shop_name'] = '';
            if (!empty($v['tenant_id'])) {
                $v['shop_name'] = db('system_admin')->where('id', $v['tenant_id'])->value('real_name');
                $v['error_tips'] = '该优惠券仅可用于' . $v['shop_name'];
            }
            $list[$k] = $v;
        }
        if ($list)
            return $list;
        else
            return [];
    }

    /**
     * @param string $prefix
     * @return $this
     */
    public static function validWhere($prefix = '')
    {
        $model = new self;
        if ($prefix) {
            $model->alias($prefix);
            $prefix .= '.';
        }
        $newTime = time();
        return $model->where("{$prefix}status", 1)
            ->where(function ($query) use ($newTime, $prefix) {
                $query->where(function ($query) use ($newTime, $prefix) {
                    $query->where("{$prefix}start_time", '<', $newTime)->where("{$prefix}end_time", '>', $newTime);
                })->whereOr(function ($query) use ($prefix) {
                    $query->where("{$prefix}start_time", 0)->where("{$prefix}end_time", 0);
                });
            })->where("{$prefix}is_del", 0)->where("{$prefix}remain_count > 0 OR {$prefix}is_permanent = 1");
    }


    public static function issueUserCoupon($id, $uid)
    {
        $issueCouponInfo = self::validWhere()->where('id', $id)->find();
        if (!$issueCouponInfo) return self::setErrorInfo('领取的优惠劵已领完或已过期!');
        if (StoreCouponIssueUser::be(['uid' => $uid, 'issue_coupon_id' => $id]))
            return self::setErrorInfo('已领取过该优惠劵!');
        if ($issueCouponInfo['remain_count'] <= 0 && !$issueCouponInfo['is_permanent'])
            return self::setErrorInfo('抱歉优惠卷已经领取完了！');

        self::beginTrans();
        $res1 = false != StoreCouponUser::addUserCoupon($uid, $issueCouponInfo['cid']);
        $res2 = false != StoreCouponIssueUser::addUserIssue($uid, $id, $issueCouponInfo['cid']);
        $res3 = true;
        if ($issueCouponInfo['total_count'] > 0) {
            $issueCouponInfo['remain_count'] -= 1;
            $res3 = false !== $issueCouponInfo->save();
        }
        $res = $res1 && $res2 && $res3;
        self::checkTrans($res);
        return $res;
    }



    /**
     * 优惠券名称
     * @param $id
     * @return mixed
     */
    public static function getIssueCouponTitle($id)
    {
        $cid = self::where('id', $id)->value('cid');
        return StoreCoupon::where('id', $cid)->value('title');
    }

    /**检测优惠券是否合法 */
    public static function beforeCreateOrder($uid, $issue_id)
    {
        $data = self::alias('a')
            ->join('store_coupon_issue_user b', 'a.id=b.issue_coupon_id')
            ->where('b.uid', $uid)
            ->where('issue_coupon_id', $issue_id)
            ->find();
        if (empty($data)) return self::setErrorInfo('请先领取优惠券', true);
        if ($data['is_use']) return self::setErrorInfo('优惠券已使用', true);
        if (!$data['is_permanent'] && $data['remain_count'] < 1) return self::setErrorInfo('优惠券数量不足', true);
        if (!empty($data['start_time']) || !empty($data['end_time'])) {
            if ($data['start_time'] > time()) return self::setErrorInfo('优惠券未到领取时间', true);
            if ($data['end_time'] < time()) return self::setErrorInfo('优惠券已过期', true);
        }

        if ($data['fail_time'] <= time()) return self::setErrorInfo('优惠券已过期', true);
        return $data['cid'];
    }
}
