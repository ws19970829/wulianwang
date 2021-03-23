<?php

/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/25
 */

namespace app\models\user;

use app\models\system\Express;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * TODO 用户收货地址
 * Class UserAddress
 * @package app\models\user
 */
class UserAddress extends BaseModel
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
    protected $name = 'user_address';

    use ModelTrait;

    protected $insert = ['add_time'];

    protected $hidden = ['add_time', 'is_del', 'uid'];

    protected function setAddTimeAttr()
    {
        return time();
    }

    public function express()
    {
        return $this->hasOne(Express::class, 'id', 'express_id');
    }

    /**
     * 设置默认收货地址
     * @param $id 地址id
     * @param $uid 用户uid
     * @return bool
     */
    public static function setDefaultAddress($id, $uid)
    {
        self::beginTrans();
        $res1 = self::where('uid', $uid)->update(['is_default' => 0]);
        $res2 = self::where('id', $id)->where('uid', $uid)->update(['is_default' => 1]);
        $res = $res1 !== false && $res2 !== false;
        self::checkTrans($res);
        return $res;
    }

    /**
     * 设置用户地址查询初始条件
     * @param null $model
     * @param string $prefix
     * @return \think\Model
     */
    public static function userValidAddressWhere($model = null, $prefix = '')
    {
        if ($prefix) $prefix .= '.';
        $model = self::getSelfModel($model);
        return $model->where("{$prefix}is_del", 0);
    }

    /**
     * 获取用户收货地址并分页
     * @param $uid 用户uid
     * @param int $page 页码
     * @param int $limit 展示条数
     * @param string $field 展示字段
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getUserValidAddressList($uid, $page = 1, $limit = 8, $field = '*')
    {
        if ($page) return self::userValidAddressWhere()->where('uid', $uid)->order('add_time DESC')->field($field)->page((int) $page, (int) $limit)->select()->append(['express_name'])->toArray() ?: [];
        else return self::userValidAddressWhere()->where('uid', $uid)->order('add_time DESC')->field($field)->select()->append(['express_name'])->toArray() ?: [];
    }

    public function getExpressNameAttr($v, $data)
    {
        return db('express')->where('id', $data['express_id'])->value('name') ?? '';
    }

    public function getExpressPhoneAttr($v,$data)
    {
        return db('express')->where('id', $data['express_id'])->value('phone') ?? '';
    }

    /**
     * 获取用户默认收货地址
     * @param $uid 用户uid
     * @param string $field 展示字段
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getUserDefaultAddress($uid, $field = '*')
    {
        $res = self::userValidAddressWhere()->where('uid', $uid)->where('is_default', 1)->field($field)->find();
        if (!empty($res)) {
            $res = $res->append(['express_name']);
        } else {
            $res = [];
        }
        return $res;
    }
}
