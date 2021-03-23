<?php

namespace app\admin\controller\collection;

use app\admin\controller\AuthController;
use app\admin\model\collection\Collection as CollectionModel;
use app\admin\model\collection\CollectionProduct;
use app\admin\model\order\StoreOrder;
use app\admin\model\store\StoreProduct;
use think\Request;
use think\facade\Route as Url;
use app\admin\model\store\StoreCategory as CategoryModel;
use crmeb\services\{FormBuilder as Form, JsonService as Json, UtilService as Util};

/**x
 * 合作商家
 * Class StoreCategory
 * @package app\admin\controller\system
 */
class Collection extends AuthController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //获取所有合作商家的阅读总次数
        $view_total=0;
        //获取通过合作商家来下单支付的订单总金额
        $pay_money_total=0;
        $this->assign('pay_money_total',$pay_money_total);

        return $this->fetch();
    }


    /*
     *  异步获取分类列表
     *  @return json
     */
    public function collection_list()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['order', ''],
            ['title', '']
        ]);
        return Json::successlayui(CollectionModel::getCollectionList($where));
    }


    /**
     * TODO 文件添加和修改
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function create()
    {
        $id = $this->request->param('id');
        $news = [];
        $all = [];
        $news['id'] = '';
        $news['image_input'] = '';
        $news['image'] = '';
        $news['title'] = '';
        $news['money'] = 0;
        $news['content'] = 0;
//        $news['author'] = '';
        $news['type'] = 0;
        $product_list = [];
        if ($id) {
            $news = CollectionModel::where('id', $id)->find();
        }

//        $product_list_json=json_encode($product_list);
        $product_list_json=json_encode($product_list);
        $this->assign('all', $all);
        $this->assign('news', $news);
        $this->assign('product_list', $product_list);
        $this->assign('product_list_json',$product_list_json);
        return $this->fetch();
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = Util::postMore([
            'id',
            'title',
            ['type', 0],
            ['money', 0],
        ], $request);


        if (!$data['title']) return Json::fail('请输入商家名称');
        if($data['type']&&!$data['money']) return Json::fail('请设置指定收款金额');
        $data['add_time'] = time();
        $data['tenant_id'] = session('tenant_id');

        if($data['id']){
            $id=$data['id'];
            unset($data['id']);
            CollectionModel::edit($data,$id,'id');
        }else{
            unset($data['id']);
            $res=CollectionModel::create($data);

        }
        return Json::successful('保存成功!');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $c = CategoryModel::get($id);
        if (!$c) return Json::fail('数据不存在!');
        $field = [
            Form::select('pid', '父级', (string)$c->getData('pid'))->setOptions(function () use ($id) {
                $list = CategoryModel::getTierList(CategoryModel::where('id', '<>', $id), 0);
//                $list = (sort_list_tier((CategoryModel::where('id','<>',$id)->select()->toArray(),'顶级','pid','cate_name'));
                $menus = [['value' => 0, 'label' => '顶级菜单']];
                foreach ($list as $menu) {
                    $menus[] = ['value' => $menu['id'], 'label' => $menu['html'] . $menu['cate_name']];
                }
                return $menus;
            })->filterable(1),
            Form::input('cate_name', '分类名称', $c->getData('cate_name')),
            Form::frameImageOne('pic', '分类图标', Url::buildUrl('admin/widget.images/index', array('fodder' => 'pic')), $c->getData('pic'))->icon('image')->width('100%')->height('500px'),
            Form::number('sort', '排序', $c->getData('sort')),
            Form::radio('is_show', '状态', $c->getData('is_show'))->options([['label' => '显示', 'value' => 1], ['label' => '隐藏', 'value' => 0]])
        ];
        $form = Form::make_post_form('编辑分类', $field, Url::buildUrl('update', array('id' => $id)), 2);

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
    public function update(Request $request, $id)
    {
        $data = Util::postMore([
            'pid',
            'cate_name',
            ['pic', []],
            'sort',
            ['is_show', 0]
        ], $request);
        if ($data['pid'] == '') return Json::fail('请选择父类');
        if (!$data['cate_name']) return Json::fail('请输入分类名称');
        if (count($data['pic']) < 1) return Json::fail('请上传分类图标');
        if ($data['sort'] < 0) $data['sort'] = 0;
        $data['pic'] = $data['pic'][0];
        CategoryModel::edit($data, $id);
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
        if (!CollectionModel::where('id','=',$id)->update(['is_del'=>1]))
            return Json::fail(CollectionModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }



    /**
     * 设置产品分类上架|下架
     * @param string $is_show
     * @param string $id
     */
    public function set_show($is_show = '', $id = '')
    {
        ($is_show == '' || $id == '') && Json::fail('缺少参数');
        if (\app\admin\model\collection\Collection::setShow($id, (int)$is_show)) {
            return Json::successful($is_show == 1 ? '显示成功' : '隐藏成功');
        } else {
            return Json::fail(CategoryModel::getErrorInfo($is_show == 1 ? '显示失败' : '隐藏失败'));
        }
    }
}
