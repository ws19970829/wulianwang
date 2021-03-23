<?php

namespace app\admin\controller\setting;

use app\admin\controller\AuthController;
use app\admin\model\system\{SystemAdmin, SystemNotice as NoticeModel};
use crmeb\services\{JsonService, UtilService, FormBuilder as Form};
use think\Request;
use think\facade\Route as Url;

/**
 * 店铺轮播图
 * Class SystemNotice
 * @package app\admin\controller\system
 */
class ShopBanner extends AuthController
{
    public function index()
    {
        $list = db('shop_banner')
            ->where('tenant_id', session('tenant_id'))
            ->where('is_del', 0)
            ->select();
        $this->assign('list', $list);
        return $this->fetch();
    }

    public function create()
    {
        $f = array();
        $f[] = Form::input('url', '链接');
        $f[] = Form::frameImageOne('img', '图片', Url::buildUrl('admin/widget.images/index', array('fodder' => 'img', 'big' => 1)))->icon('image')->width('100%')->height('500px');
        $f[] = Form::number('sort', '排序', 0);

        $form = Form::make_post_form('新增轮播图', $f, Url::buildUrl('save'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    public function save(Request $request)
    {
        $param = $request->param();
        $validate = \think\facade\Validate::rule([
            'url|链接' => 'require|url',
            'img|图片' => 'require',
            'sort|排序' => 'require|egt:0',
        ]);
        if (!$validate->check($param)) {
            return app('json')->fail($validate->getError(), []);
        }
        $param['add_time'] = time();
        $param['tenant_id'] = session('tenant_id');

        db('shop_banner')->insert($param);
        return $this->successful('添加成功');
    }

    /**编辑通知模板
     * @param $id
     * @return mixed|void
     */
    public function edit($id)
    {
        $data =  db('shop_banner')->getById($id);
        if (!$data) return JsonService::fail('数据不存在!');
        $f = array();
        $f[] = Form::input('url', '链接', $data['url']);
        $f[] = Form::frameImageOne('img', '图片', Url::buildUrl('admin/widget.images/index', array('fodder' => 'img', 'big' => 1)), $data['img'])->icon('image')->width('100%')->height('500px');
        $f[] = Form::number('sort', '排序', $data['sort']);
        $form = Form::make_post_form('编辑轮播图', $f, Url::buildUrl('update', array('id' => $id)));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    public function update(Request $request)
    {
        $param = $request->param();
        $validate = \think\facade\Validate::rule([
            'id' => 'require|gt:0|integer',
            'url|链接' => 'require|url',
            'img|图片' => 'require',
            'sort|排序' => 'require|egt:0',
        ]);
        if (!$validate->check($param)) {
            return app('json')->fail($validate->getError(), []);
        }

        db('shop_banner')->where('id', $param['id'])->update($param);
        return $this->successful('修改成功!');
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        db('shop_banner')->where('id', $id)->update(['is_del' => 1]);
        return $this->successful('删除成功!');
    }
}
