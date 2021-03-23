<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/12
 */

namespace app\models\store;

use crmeb\basic\BaseModel;
use think\facade\Cache;

/**
 * TODO 产品分类Model
 * Class StoreCategory
 * @package app\models\store
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

    public static function pidByCategory($pid, $field = '*', $limit = 0)
    {
        $model = self::where('pid', $pid)->where('is_show', 1)->order('sort desc,id desc')->field($field);
        if ($limit) $model->limit($limit);
        return $model->select();
    }

    public static function pidBySidList($pid)
    {
        return self::where('pid', $pid)->field('id,cate_name,pid')->select();
    }

    public static function cateIdByPid($cateId)
    {
        return self::where('id', $cateId)->value('pid');
    }

    /*
     * 获取一级和二级分类
     * @return array
     * */
    public static function getProductCategory($expire = 800)
    {
        if (Cache::has('parent_category')) {
            return Cache::get('parent_category');
        } else {
            $parentCategory = self::pidByCategory(0, 'id,cate_name')->toArray();
            foreach ($parentCategory as $k => $category) {
                $category['child'] = self::pidByCategory($category['id'], 'id,cate_name,pic')->toArray();
                $parentCategory[$k] = $category;
            }
            Cache::set('parent_category', $parentCategory, $expire);
            return $parentCategory;
        }
    }

    /**
     * TODO  获取首页展示的二级分类  排序默认降序
     * @param string $field
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function byIndexList($limit = 4,bool $bool = true, $field = 'id,cate_name,pid,pic',$tenant_id=0)
    {
        if(!$limit && !$bool) return [];
        return self::where('pid', '>', 0)
            ->where('is_show', 1)
            ->where('tenant_id','=',$tenant_id)
            ->field($field)
            ->order('sort DESC')
            ->limit($limit)
            ->select();
    }

    /**
     * 获取子集分类查询条件
     * @return \think\model\relation\HasMany
     */
    public function children()
    {
        return $this->hasMany(self::class, 'pid','id')
            ->where('is_show',1)
            ->order('sort DESC,id DESC');
    }

    /**
     * 获取首页第一级分类列表
     * @return \think\model\relation\HasMany
     */
    public static function getPidCategoryList()
    {
        $list = self::where('pid',0)->where('tenant_id',0)->field('cate_name,id')->select()->toArray();
        return $list;
    }

    /**
     * 获取首页第一级分类列表
     * @return \think\model\relation\HasMany
     */
    public static function getSecondCategoryList($category_id = "")
    {
        if($category_id == ""){
            $list = self::where('pid',0)->where('tenant_id',0)->field('cate_name,id')->select()->toArray();
        }else{
            $list = self::where('pid',0)->where('tenant_id',0)->field('cate_name,id')->select()->toArray();
        }


        foreach($list as $k => $v){
            $list[$k]['second_category_list'] = self::where('pid',$v['id'])->where('tenant_id',0)->field('cate_name,id')->select()->toArray();
        }
        return $list;
    }

    /**
     * 获取分类下面的第二级分类列表
     * @return \think\model\relation\HasMany
     */
    public static function getPidSecondCategoryList($pid = 0)
    {
        return self::where('pid',$pid)->where('tenant_id',0)->field('cate_name,id')->select()->toArray();
    }


}