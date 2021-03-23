<?php

namespace app\api\controller\store;

use app\models\store\StoreCategory;
use crmeb\services\{
    UtilService
};
use app\Request;

class CategoryController
{
    public function category_bak(Request $request)
    {
        list($isadmin) =  UtilService::getMore([
            ['isadmin', 0],
        ], $request, true);

        $where[] = ['is_show', '=', 1];
        $where[] = ['pid', '=', 0];
        if (!empty($isadmin)) {
            $where[] = ['tenant_id', '=', 0];
        } else {
            $where[] = ['tenant_id', '=', input('param.tenant_id', 18)];
        }

        $cateogry = StoreCategory::with('children')
            ->where($where)
            ->order('sort desc,id desc')
            ->select();
        return app('json')->success($cateogry->hidden(['add_time', 'is_show', 'sort', 'children.sort', 'children.add_time', 'children.pid', 'children.is_show'])->toArray());
    }

    public function category(Request $request)
    {
        $param = $request->param();

        $list = StoreCategory::where('level', !(bool) $param['level'] ? 1 : $param['level'])
            ->where('is_show', 1)
            ->order('sort', 'desc')
            ->page(!(bool) $param['page'] ? 1 : $param['page'], !(bool) $param['limit'] ? 10 : $param['limit'])
            ->select();
        $count = StoreCategory::where('level', !(bool) $param['level'] ? 1 : $param['level'])
            ->where('is_show', 1)
            ->count();
        return  app('json')->success(compact('list', 'count'));
    }

    public function subcate($cate_id)
    {

        $categorys = db('store_category')
            ->where('is_show', 1)
            ->field('id,cate_name,level,pic,tenant_id,pid')
            ->select()
            ->toArray() ?? [];
        $cateinfo = db('store_category')->getById($cate_id);
        if ($cateinfo['level'] == 1) {
            $childinfo = $this->getMenuTree($categorys, $cate_id);
            $childinfo[] = $cateinfo;

            $tree = list_to_tree($childinfo, $cateinfo['level'] - 1)[0]??[];
        } else {
            $childinfo = db('store_category')
                ->where('is_show', 1)
                ->where('pid', $cate_id)
                ->order('sort', 'desc')
                ->select();
            $cateinfo['children'] = $childinfo;
            $tree = $cateinfo;
        }

        return  app('json')->success('', $tree);
    }

    

    function getMenuTree($arrCat, $parent_id = 0, $level = 0)
    {
        static  $arrTree = array();
        if (empty($arrCat)) return FALSE;
        $level++;
        foreach ($arrCat as $key => $value) {
            if ($value['pid'] == $parent_id) {
                $value['level'] = $level;
                $arrTree[] = $value;
                unset($arrCat[$key]); //注销当前节点数据，减少已无用的遍历
                $this->getMenuTree($arrCat, $value['id'], $level);
            }
        }
        return $arrTree;
    }
}
