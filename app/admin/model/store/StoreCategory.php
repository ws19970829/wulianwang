<?php

/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\store;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * Class StoreCategory
 * @package app\admin\model\store
 */
class StoreCategory extends BaseModel
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
    protected $name = 'store_category';

    use ModelTrait;

    /**
     * 异步获取分类列表
     * @param $where
     * @return array
     */
    public static function CategoryList($where)
    {
        $data = ($data = self::systemPage($where, true)->page((int) $where['page'], (int) $where['limit'])->select()) && count($data) ? $data->toArray() : [];
        foreach ($data as &$item) {
            if ($item['pid']) {
                $item['pid_name'] = self::where('id', $item['pid'])->value('cate_name');
            } else {
                $item['pid_name'] = '顶级';
            }
        }
        $count = self::systemPage($where, true)->count();
        return compact('count', 'data');
    }

    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where, $isAjax = false)
    {
        $model = new self;
        $model = $model->where('tenant_id', '=', session('tenant_id'));

        if ($where['pid'] != '') $model = $model->where('pid', $where['pid']);
        else if ($where['pid'] == '' && $where['cate_name'] == '') $model = $model->where('pid', 0);
        if ($where['is_show'] != '') $model = $model->where('is_show', $where['is_show']);
        if ($where['cate_name'] != '') $model = $model->where('cate_name', 'LIKE', "%$where[cate_name]%");
        if ($isAjax === true) {
            if (isset($where['order']) && $where['order'] != '') {
                $model = $model->order(self::setOrder($where['order']));
            } else {
                $model = $model->order('sort desc,id desc');
            }
            return $model;
        }
        return self::page($model, function ($item) {
            if ($item['pid']) {
                $item['pid_name'] = self::where('id', $item['pid'])->value('cate_name');
            } else {
                $item['pid_name'] = '顶级';
            }
        }, $where);
    }

    /**
     * 获取顶级分类
     * @return array
     */
    public static function getCategory()
    {
        return self::where('is_show', 1)->column('cate_name', 'id');
    }

    /**
     * 分级排序列表
     * @param null $model
     * @param int $type
     * @param int $sign     1-查询本商户分类，2-查询总后台维护分类
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getTierList($model = null, $type = 0, $sign = 1)
    {
        if ($model === null) $model = new self();
        // if($sign == 1)
        //     $model=$model->where('tenant_id','=',session('tenant_id'));
        // else
        //     $model=$model->where('tenant_id','=',0);

        if (!$type) return sort_list_tier($model->order('sort desc,id desc')->where('level', '<', 3)->select()->toArray());
        $ids = db('system_admin')->where('id', session('tenant_id'))->value('usable_cate');
        $ids = empty($ids) ? [] : explode(',', $ids);
        $category = db('store_category')->where('is_show', 1)->where('level', '<', 3)->field('id,pid')->select()->toArray() ?? [];

        foreach ($ids as $v) {
            $cate = db('store_category')->where('id', $v)->find();
            $ids = array_merge($ids, getPid($category, $cate['pid']));
        }

        $data =  $model->where('id', 'in', $ids)
            ->order('sort desc,id desc')
            //->where('level', '<=',3)
            ->select()
            ->toArray();
        foreach ($data as &$v) {
            if ($v['level'] < 3) $v['disabled'] = true;
        }
        //dump(sort_list_tier($data));die;
        return sort_list_tier($data);
        // return $data;
        //   return sort_list_tier($model->order('sort desc,id desc')->where('level',3)->select()->toArray());
    }

    public static function getTierList1($id)
    {
        $model = new self();
        $ids = db('system_admin')->where('id', session('tenant_id'))->value('usable_cate');
        $ids = empty($ids) ? [] : explode(',', $ids);
        $category = db('store_category')->where('is_show', 1)->where('level', '<', 3)->field('id,pid')->select()->toArray() ?? [];

        foreach ($ids as $v) {
            $cate = db('store_category')->where('id', $v)->find();
            $ids = array_merge($ids, getPid($category, $cate['pid']));
        }

        $data =  $model->where('id', 'in', $ids)
            ->order('sort desc,id desc')
            //->where('level', '<=',3)
            ->field('id,cate_name as title,pid,cate_name,level')
            ->select()
            ->toArray();
        $select = [];
        if (!empty($id)) {
            $arr = db('store_product')->where('id', $id)->value('cate_id') ?? '';
            $select = explode(',', $arr);
        }

        foreach ($data as &$v) {
            if (in_array($v['id'], $select)) $v['checked'] = true;
        }
        return list_to_tree($data);
    }

    public static function delCategory($id)
    {
        $count = self::where('pid', $id)->count();
        if ($count)
            return self::setErrorInfo('请先删除下级子分类');
        else {
            return self::del($id);
        }
    }

    /**
     * 产品分类隐藏显示
     * @param $id
     * @param $show
     * @return bool
     */
    public static function setCategoryShow($id, $show)
    {
        $count = self::where('id', $id)->count();
        if (!$count) return self::setErrorInfo('参数错误');
        $count = self::where('id', $id)->where('is_show', $show)->count();
        if ($count) return true;
        $pid = self::where('id', $id)->value('pid');
        self::beginTrans();
        $res1 = true;
        $res2 = self::where('id', $id)->update(['is_show' => $show]);
        if (!$pid) { //一级分类隐藏
            $count = self::where('pid', $id)->count();
            if ($count) {
                $count      = self::where('pid', $id)->where('is_show', $show)->count();
                $countWhole = self::where('pid', $id)->count();
                if (!$count || $countWhole > $count) {
                    $res1 = self::where('pid', $id)->update(['is_show' => $show]);
                }
            }
        }
        $res = $res1 && $res2;
        self::checkTrans($res);
        return $res;
    }
}
