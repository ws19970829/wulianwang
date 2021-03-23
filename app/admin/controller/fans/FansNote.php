<?php

namespace app\admin\controller\fans;

use app\admin\controller\AuthController;
use app\admin\model\fans\FansNote as FansNoteModel;
use app\admin\model\fans\FansProduct;
use app\admin\model\order\StoreOrder;
use app\admin\model\store\StoreProduct;
use think\Request;
use think\facade\Route as Url;
use app\admin\model\store\StoreCategory as CategoryModel;
use crmeb\services\{FormBuilder as Form, JsonService as Json, UtilService as Util};

/**x
 * 产品分类控制器
 * Class StoreCategory
 * @package app\admin\controller\system
 */
class FansNote extends AuthController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //获取所有笔记的阅读总次数
        $view_total=FansNoteModel::where('tenant_id','=',session('tenant_id'))
            ->where('is_del','=',0)
            ->sum('view_num');
        $this->assign('view_total',$view_total);
        //获取通过笔记来下单支付的订单总金额
        $pay_money_total=StoreOrder::where('fans_note_id','>',0)
            ->where('tenant_id','=',session('tenant_id'))
            ->where('pay_time','>',0)
            ->sum('total_price');
        $this->assign('pay_money_total',$pay_money_total);

        return $this->fetch();
    }


    /*
     *  异步获取分类列表
     *  @return json
     */
    public function note_list()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['order', ''],
            ['title', '']
        ]);
        return Json::successlayui(FansNoteModel::getNoteList($where));
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
//        $news['author'] = '';
        $news['is_add_time'] = 1;
        $news['is_view'] = 1;
        $news['is_dianzan'] = 1;
        $news['is_into_shop'] = 1;
        $news['content'] = '';
        $news['synopsis'] = '';
        $product_list = [];
        if ($id) {
            $news = FansNoteModel::where('id', $id)->find();
            if (!$news) return $this->failed('数据不存在!');
            $news['content'] = htmlspecialchars_decode($news['content']);
            $product_list=(new FansProduct())->getProductListByNoteId($id);
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
            'image_input',
            'title',
            'content',
            'synopsis',
            ['goodsIdArr',[]],
            ['is_add_time', 1],
            ['is_view', 1],
            ['is_dianzan', 1],
            ['is_into_shop', 1],
        ], $request);


        if (!$data['title']) return Json::fail('请输入标题');
        if (!$data['image_input']) return Json::fail('请上传图片');
//        if (!$data['synopsis']) return Json::fail('请输入摘要');
        if (!$data['content']) return Json::fail('请输入内容');
//        if (!$data['goodsIdArr']) return Json::fail('请添加商品');
        $data['image']=$data['image_input'];
        unset($data['image_input']);
        $goodsIdArr=$data['goodsIdArr'];


        $data['add_time'] = time();
        $data['tenant_id'] = session('tenant_id');

        if($data['id']){
            $id=$data['id'];
            unset($data['id']);
            FansNoteModel::edit($data,$id,'id');
            //处理商品和笔记的关联数据-先清空已有数据
            (new FansProduct())->where('note_id','=',$id)->delete();
            $data=[];
            if($goodsIdArr){
                foreach($goodsIdArr as $val){
                    $tem['note_id']=$id;
                    $tem['product_id']=$val;
                    $data[]=$tem;
                }
                (new FansProduct())->saveAll($data);
            }

        }else{
            unset($data['id']);
            $res=FansNoteModel::create($data);
            if($res){
                $note_id=$res->getData('id');
                //处理商品和笔记的关联数据
                $data=[];
                if($goodsIdArr){
                    foreach($goodsIdArr as $val){
                        $tem['note_id']=$note_id;
                        $tem['product_id']=$val;
                        $data[]=$tem;
                    }
                    (new FansProduct())->saveAll($data);
                }

            }
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
        if (!FansNoteModel::where('id','=',$id)->update(['is_del'=>1]))
            return Json::fail(FansNoteModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }

    /**
     * 选择商品
     * @param int $id
     */
    public function select()
    {
        //处理已经选中的商品
        $ids=input('param.ids');
        $this->assign('ids',$ids);
        return $this->fetch();
    }


    /**
     * 选择商品提交并存入缓存
     */
    public function save_cache(){
        $ids=input('param.ids');
        $checked_ids=input('param.checked_ids');

        $cache_key='select_product_'.session('tenant_id');
        if($ids){
            $ids=implode(',',$ids);
            if($checked_ids){
                $ids.=','.$checked_ids;
            }
            $where=['ids'=>$ids,];
            $product_list=StoreProduct::ProductListBySelect($where);
        }else{
            $product_list=[];
        }
        cache($cache_key,null);
        cache($cache_key,$product_list);

        return Json::successful('提交成功!');
    }

    public function get_cache(){
        $cache_key='select_product_'.session('tenant_id');
        $res=cache($cache_key);
        if($res){
            cache($cache_key,null);
            return Json::successful('获取成功',$res);
        }else{
            return Json::fail('无数据');
        }
    }
}
