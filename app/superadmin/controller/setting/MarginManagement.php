<?php

namespace app\superadmin\controller\setting;

use app\admin\model\system\BondPrice;
use app\superadmin\controller\AuthController;
use crmeb\basic\BaseModel;
use crmeb\services\{FormBuilder as Form, JsonService as Json, UtilService as Util};
use app\superadmin\model\system\{SystemRole, SystemAdmin as SystemAdminModel, MarginManagement as MarginManagementModel , SystemConfig as ConfigModel};
use think\facade\Route as Url;

/**
 * 管理员列表控制器
 * Class SystemAdmin
 * @package app\admin\controller\system
 */
class MarginManagement extends AuthController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
//        $admin = $this->adminInfo;
//        $where = Util::getMore([
//            ['name', ''],
//            ['roles', '']
//        ]);
//        $this->assign('where', $where);
//        /*$list = UserEnterModel::systemPage($where);
//        dump($list);exit;*/
//        $this->assign('role', SystemRole::getRole(bcadd($admin->level, 1, 0)));
//        $this->assign(MarginManagementModel::systemPage($where));
        return $this->fetch();
    }

    /**
     * @Author  lingyun
     * @Desc    保证金订单详情
     */
    public function order_list(){
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['start_time', ''],
            ['end_time', ''],
            ['type', 1],
        ]);

        if(strtotime($where['start_time']) > strtotime($where['end_time'])){
            $data = [];
            $count = [];
            return Json::successlayui(compact('count', 'data'));
        }

        $map[] = ['is_pay','=',1];
        if(!empty($where['start_time'])){
            $map[] = ['add_time','between',[strtotime($where['start_time']),strtotime($where['end_time'])]];
        }

        if(!empty($where['type'])){
            $map[] = ['type','=',$where['type']];
        }

        $data = MarginManagementModel::where($map)->page($where['page'],$where['limit'])->select();

        $data->append(['admin_name']);
        $count = MarginManagementModel::where($map)->count();

        return Json::successlayui(compact('count', 'data'));
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function create()
    {
        //dump(config('base.bond_price'));
        $bond_price = BondPrice::getBondPrice(1);
        $f = array();
        $f[] = Form::input('bond_price', '保证金',$bond_price);
        $form = Form::make_post_form('保证金管理', $f, Url::buildUrl('save'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }


    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save()
    {
        $data = Util::postMore([
            ['bond_price', 0],
        ]);

        if (!$data['bond_price']) return Json::fail('保证金不能为空');
        BondPrice::edit($data,1);
        return Json::successful('修改保证金成功!');
    }

    /**
     * @Author  lingyun
     * @Desc    审核订单
     * return string
     */
    public function check_order(){
        $data = Util::postMore([
            ['id', 0],
        ]);

        $this->assign('id',$data['id']);
        return $this->fetch();
    }

    /**
     * @Author  lingyun
     * @Desc    审核订单
     * return string
     */
    public function save_check_order(){
        $data = Util::postMore([
            ['id', ''],
            ['status', 2],
            ['remark', ''],
        ]);

        $model = new MarginManagementModel();
        $order = $model->get(['id'=>$data['id']]);

        $model->where('id',$data['id'])->update(['status'=>$data['status'],'remark'=>$data['remark']]);
        (new SystemAdminModel())->where('id',$order['admin_id'])->update(['is_margin'=>2]);

        return json(['code'=>200,'msg'=>'审核成功']);
    }


}
