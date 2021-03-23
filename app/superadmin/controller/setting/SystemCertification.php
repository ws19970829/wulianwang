<?php

namespace app\superadmin\controller\setting;

use app\superadmin\controller\AuthController;
use crmeb\basic\BaseModel;
use crmeb\services\{FormBuilder as Form, JsonService as Json, UtilService as Util};
use app\superadmin\model\system\{SystemRole, SystemAdmin as SystemAdminModel, UserEnter as UserEnterModel};
use think\facade\Route as Url;

/**
 * 管理员列表控制器
 * Class SystemAdmin
 * @package app\admin\controller\system
 */
class SystemCertification extends AuthController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $admin = $this->adminInfo;
        $where = Util::getMore([
            ['name', ''],
            ['roles', ''],
            ['level', bcadd($admin->level, 1, 0)],
            ['is_business_application', bcadd($admin->is_business_application, 1, 0)]
        ]);
        $this->assign('where', $where);
        $this->assign('role', SystemRole::getRole(bcadd($admin->level, 1, 0)));
        /*$list = UserEnterModel::systemPage($where);
        dump($list);exit;*/
        $this->assign(UserEnterModel::systemPage($where));
        return $this->fetch();
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id = 0)
    {
        if (!$id) return $this->failed('参数错误');

        $admin = UserEnterModel::get($id);
        $admin['addr'] = $admin['province'].''.$admin['city'].''.$admin['district'].''.$admin['address'];
        $f = array();
//        $f[] = Form::input('merchant_name', '商家名称', $admin->merchant_name);
        $f[] = Form::input('link_user', '联系人', $admin->link_user);
        $f[] = Form::input('link_tel', '联系电话', $admin->link_tel);
        $f[] = Form::input('addr', '通讯地址', $admin->addr);
        $f[] = Form::input('user_card_num', '身份证号码', $admin->user_card_num);

        $idcard_img=$admin->getData('idcard_img');
        if($idcard_img){
            $idcard_img=json_decode($idcard_img, 1);
        }else{
            $idcard_img=[];
        }

        $business_img=$admin->getData('business_img');
        if($business_img){
            $business_img=json_decode($business_img, 1);
        }else{
            $business_img=[];
        }


        $f[] = Form::frameImages('idcard_img', '身份证照片', Url::buildUrl('superadmin/widget.images/index', array('fodder' => 'idcard_img')),$idcard_img)->maxLength(5)->icon('images')->width('100%')->height('500px')->spin(0);

        $f[] = Form::frameImages('business_img', '营业执照', Url::buildUrl('superadmin/widget.images/index', array('fodder' => 'business_img')),$business_img)->maxLength(5)->icon('images')->width('100%')->height('500px')->spin(0);

        $f[] = Form::input('remark', '介绍说明', $admin->remark)->type('textarea');
        $f[] = Form::radio('status', '状态', $admin->status)->options([ ['label' => '审核中', 'value' => 0],['label' => '通过', 'value' => 1], ['label' => '不通过', 'value' => -1]]);
        $f[] = Form::input('no_remark', '不通过说明', $admin->no_remark)->type('textarea');
        $form = Form::make_post_form('查看认证信息', $f, Url::buildUrl('update', compact('id')));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }


    /**
     * 保存更新的资源
     *
     * @param \think\Request $request
     * @param int $id
     * @return \think\Response
     */
    public function update($id)
    {
        $data = Util::postMore([
            ['status', 0],
            'no_remark',
        ]);
        $data['apply_time'] = time();
        if($data['status'] == -1){
            if(empty($data['no_remark'])){
                return Json::fail('未填写不通过说明');
            }
        }
        if (!UserEnterModel::edit($data, $id)) return Json::fail('修改失败');
        return Json::successful('修改成功!');
    }
    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!$id)
            return Json::fail('删除失败!');
        if (UserEnterModel::edit(['is_del' => 1, 'status' => 0], $id, 'id'))
            return Json::successful('删除成功!');
        else
            return Json::fail('删除失败!');
    }

}
