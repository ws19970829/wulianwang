<?php

namespace app\admin\controller\user;

use app\admin\controller\AuthController;
use app\admin\model\user\Invitation as UserInvitation;
use app\admin\model\user\UserGroup as GroupModel;
use app\models\user\User;
use app\Request;
use crmeb\services\JsonService;
use crmeb\services\UtilService;
use crmeb\services\FormBuilder as Form;
use think\Collection;
use think\facade\Route as Url;

/**
 * 用户通知
 * Class UserNotice
 * @package app\admin\controller\user
 */
class Invitation extends AuthController
{
    /**
     * 会员分组页面
     * @return string
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 分组列表
     */
    public function groupList()
    {
        $where = UtilService::getMore([
            ['page', 1],
            ['limit', 20],
            ['phone', '']
        ]);
        return JsonService::successlayui(UserInvitation::getList($where));
    }

    /**
     * 添加/修改分组页面
     * @param int $id
     * @return string
     */
    public function addGroup($id = 0)
    {
        $group = GroupModel::get($id);
        $f = array();
        do {
            $code = unique_code();
            $res = db('invitation')->where('code', $code)->count();
        } while ($res);
        if (!$group) {
            $f[] = Form::input('phone', '手机号', '');
            $f[] = Form::input('code', '邀请码', $code)->readonly(true);
        } else {
            $f[] = Form::input('group_name', '分组名称', $group->getData('group_name'));
        }
        $form = Form::make_post_form('用户邀请码', $f, Url::buildUrl('save', array('id' => $id)));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 添加/修改
     * @param int $id
     */
    public function save(Request $request)
    {

        $param = $request->param();
        $validate = \think\facade\Validate::rule([
            'phone|手机号' => 'require|mobile|unique:invitation',
            'code|邀请码' => 'requireWith:id|length:6|unique:invitation',
        ]);

        if (!$validate->check($param)) {
            return app('json')->fail($validate->getError(), []);
        }
        $id = $param['id'] ?? 0;
        if ($id) {
            if (db('invitation')->where('id', $id)->update($param)) {
                return JsonService::success('修改成功');
            } else {
                return JsonService::fail('修改失败或者您没有修改什么！');
            }
        } else {
            $param['add_time'] = time();
            $param['tenant_id'] = session('tenant_id');
            if ($id = db('invitation')->insertGetId($param)) {
                return JsonService::success('保存成功', ['id' => $id]);
            } else {
                return JsonService::fail('保存失败！');
            }
        }
    }

    /**
     * 删除
     * @param $id
     * @throws \Exception
     */
    public function delete($id)
    {
        if (!$id) return $this->failed('数据不存在');
        UserInvitation::destroy($id);
        return JsonService::success('删除成功');
    }

    public function send_message(Request $request)
    {
        $id = $request->param('id');
        $data = UserInvitation::find($id);
        if (!preg_match("/^1[3456789]{1}\d{9}$/", $data['phone'])) {
            return JsonService::fail('手机号码不正确');
        }

        $result = (new User())->send($data['phone'],  $data['code'], 'SMS_205884087');
        if ($result['code'] == 1) {
            return JsonService::success('发送成功');
        } else {
            return JsonService::fail('发送失败');
        }
    }
}
