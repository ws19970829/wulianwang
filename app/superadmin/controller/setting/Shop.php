<?php

namespace app\superadmin\controller\setting;

use app\admin\model\system\ShippingTemplatesRegion;
use app\models\article\Article;
use app\models\store\StoreProductRule;
use app\superadmin\controller\AuthController;
use crmeb\basic\BaseModel;
use crmeb\services\{FormBuilder as Form, JsonService as Json, UtilService as Util};
use app\superadmin\model\system\{SystemRole, SystemAdmin as AdminModel, SystemAdmin};
use think\facade\Route as Url;
use crmeb\services\JsonService;
use think\Request;

/**
 * 管理员列表控制器
 * Class SystemAdmin
 * @package app\admin\controller\system
 */
class Shop extends AuthController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function list()
    {
        $where = Util::getMore([
            ['name', ''],
            ['limit', 20],
            ['page', 1],
        ]);
        return JsonService::successlayui(SystemAdmin::systemPage1($where));
    }

    public function index()
    {
        return view();
    }

    public function cate_usable(Request $request)
    {
        if ($request->post()) {
            $param = $request->param();
            $param['cate_id'] = empty($param['cate_id']) ? '' : implode(',', $param['cate_id']);
            db('system_admin')
                ->where('id', $param['tenant_id'])
                ->update(['usable_cate' => $param['cate_id']]);
            return Json::successful('设置成功');
        } else {
            $tenant_id = input('tenant_id');
            $catelist = db('store_category')->where([
                'is_show' => 1,
            ])->field('id,cate_name as title,pid')
                ->select()->toArray();

            if (!empty($catelist)) {
                $select = db('system_admin')->where('id', $tenant_id)->value('usable_cate') ?? '';
                $select = explode(',', $select);
                foreach ($catelist as &$v) {
                    if (in_array($v['id'], $select)) {
                        $v['checked'] = true;
                    } else {
                        $v['checked'] = false;
                    }
                }
                $catelist = list_to_tree($catelist);
            }

            $this->assign('catelist', json_encode($catelist, JSON_UNESCAPED_UNICODE));
            $this->assign('tenant_id', $tenant_id);
            return view();
        }
    }

    public function rec(Request $request)
    {
        if ($request->isGet()) {
            $this->assign('ids', $request->param('ids'));
            return view();
        } else {
            $range = $request->param('rec_time');
            $ids = $request->param('ids');
            if (empty($range) || empty($ids)) return Json::fail('参数缺失');

            list($rec_start, $rec_end) = explode(' - ', $range);
            $rec_start = strtotime($rec_start);
            $rec_end = strtotime($rec_end);
            $save = [];
            foreach (explode(',', $ids) as $v) {
                $save[] = ['id' => $v, 'rec_start' => $rec_start, 'rec_end' => $rec_end, 'is_rec' => 1];
            }
            (new AdminModel())->saveAll($save);
            return Json::successful('推荐成功');
        }
    }

    public function unrec(Request $request)
    {
        $ids = $request->param('ids');
        if (empty($ids)) return Json::fail('参数缺失');
        (new AdminModel())->whereIn('id', $ids)->update(['is_rec' => 0]);
        return Json::successful('取消推荐成功');
    }

    public function sort(Request $request)
    {
        AdminModel::where('id', $request->param('id'))->update(['sort' => $request->param('sort')]);
        return Json::successful('保存成功');
    }
}
