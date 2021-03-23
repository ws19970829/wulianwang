<?php

namespace app\admin\controller\fans;

use app\admin\controller\AuthController;
use app\admin\model\fans\FansData as FansDataModel;
use app\admin\model\fans\FansNoteReadLog;
use app\admin\model\fans\FansPlanUser;
use app\admin\model\order\StoreOrder;
use think\Request;
use think\facade\Route as Url;
use app\admin\model\store\StoreCategory as CategoryModel;
use crmeb\services\{
    FormBuilder as Form, JsonService as Json, JsonService, UtilService as Util
};

/**
 * 产品分类控制器
 * Class StoreCategory
 * @package app\admin\controller\system
 */
class FansData extends AuthController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //获取已经通知人数
        $where=[
            'tenant_id'=>session('tenant_id'),
        ];
        if(input('param.id')){
            $where['fans_plan_id']=input('param.id');
        }
        //考虑到各个表的字段统一性，公共where条件，只保留tenant_id和fans_plan_id两个字段查询条件。
        //其他的查询条件，根据各个数据获取方式和表结构的不同而单独处理。

        $plan_id=input('param.plan_id');
        if($plan_id){
            $plan_name=\app\admin\model\fans\FansPlan::where('id','=',$plan_id)->value('title');
        }else{
            $plan_name='全部计划';
        }
        $this->assign('plan_name',$plan_name);

        //通知到的人数
        $notice_user_num=FansPlanUser::where($where)->group('uid');
        if($plan_id){
            $notice_user_num=$notice_user_num->where('fans_plan_id','=',$plan_id);
        }
        $notice_user_num=$notice_user_num->count();
        $this->assign('notice_user_num',$notice_user_num);

        //访客数
        $read_user_num=FansNoteReadLog::where($where)->group('uid');
        if($plan_id){
            $read_user_num=$read_user_num->where('fans_plan_id','=',$plan_id);
        }
        $read_user_num=$read_user_num->count();
        $this->assign('read_user_num',$read_user_num);

        //付款人数
        $pay_user_num=StoreOrder::where($where)
            ->where('fans_note_id','>',0)
            ->where('pay_time','>',0)
            ->group('uid');
         if($plan_id){
             $pay_user_num=$pay_user_num->where('fans_plan_id','=',$plan_id);
         }
        $pay_user_num=$pay_user_num->count();
        $this->assign('pay_user_num',$pay_user_num);


        //付款订单数
        $pay_order_num=StoreOrder::where($where)
            ->where('fans_note_id','>',0)
            ->where('pay_time','>',0);
        if($plan_id){
            $pay_order_num=$pay_order_num->where('fans_plan_id','=',$plan_id);
        }
        $pay_order_num=$pay_order_num->count();
        $this->assign('pay_order_num',$pay_order_num);

        //付款金额
        $pay_money=StoreOrder::where($where)
            ->where('fans_note_id','>',0)
            ->where('pay_time','>',0);
        if($plan_id){
            $pay_money=$pay_money->where('fans_plan_id','=',$plan_id);
        }
        $pay_money=$pay_money->sum('total_price');
        $this->assign('pay_money',number_format($pay_money,2));


        //通知-访问转换率   访客数/通知人数
        if($notice_user_num>0){
            $notify_read_rate=round($read_user_num/$notice_user_num*100,2);
        }else{
            $notify_read_rate=0;
        }
        $this->assign('notify_read_rate',$notify_read_rate);


        //通知-付款转换率   付款人数/通知人数
        if($notice_user_num>0){
            $notify_pay_rate=round($pay_user_num/$notice_user_num*100,2);
        }else{
            $notify_pay_rate=0;
        }
        $this->assign('notify_pay_rate',$notify_pay_rate);


        //访客-付款转换率   付款人数/访客数
        if($read_user_num>0){
            $read_pay_rate=round($pay_user_num/$read_user_num*100,2);
        }else{
            $read_pay_rate=0;
        }
        $this->assign('read_pay_rate',$read_pay_rate);

        return $this->fetch();
    }



    /*
     *  异步获取分类列表
     *  @return json
     */
    public function data_list()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['order', ''],
            ['title', ''],
            ['start_time', ''],
            ['end_time', ''],
            ['type',0]
        ]);

//        return Json::successlayui(FansDataModel::getDataList($where));
        //通过推送表记录表来获取列表数据(目的是所有的数据都建立在已推送的记录上，所以推送记录表是基础表。)
        return Json::successlayui(FansPlanUser::getDataList($where));
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
        $news['title'] = '';
        $news['type'] = 1;
        $news['is_long'] = 1;
        $news['start_time'] = '';
        $news['end_time'] = '';
        $news['is_coupon'] = 0;
        $news['note_id'] = 1;

        if ($id) {
            $news = FansDataModel::where('id', $id)->find();
            if (!$news) return $this->failed('数据不存在!');
            $news=$news->toArray();
            $news['start_time']=$news['start_time']?date('Y-m-d H:i:s',$news['start_time']):'';
            $news['end_time']=$news['end_time']?date('Y-m-d H:i:s',$news['end_time']):'';
        }


        $this->assign('all', $all);
        $this->assign('news', $news);
        return $this->fetch();
    }

    /**
     * TODO 文件添加和修改
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function create_bak()
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
        if ($id) {
            $news = \app\admin\model\fans\FansData::where('id', $id)->find();
            if (!$news) return $this->failed('数据不存在!');
            $news['content'] = htmlspecialchars_decode($news['content']);
        }

        $this->assign('all', $all);
        $this->assign('news', $news);
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
            'start_time',
            'end_time',
            ['type', 1],
            ['note_id',0],
            ['is_long', 0],
            ['is_coupon', 0],
            ['coupon_ids', ''],
            ['is_into_shop', 1],
        ], $request);

        if (!$data['title']) return Json::fail('请输入标题');
//        if (!$data['note_id']) return Json::fail('请选择笔记');

        $data['add_time'] = time();
        $data['tenant_id'] = session('tenant_id');

        if(($data['is_long']==0 || !$data['is_long']) && (!$data['start_time'] || !$data['end_time'])){
            return Json::fail('请设置计划开始和结束时间');
        }

        if($data['is_coupon'] && !$data['coupon_ids']){
            return Json::fail('选择优惠券');
        }


        if($data['id']){
            $id=$data['id'];
            unset($data['id']);
            FansDataModel::edit($data,$id,'id');
        }else{
            unset($data['id']);
            FansDataModel::create($data);
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
        if (!FansDataModel::where('id','=',$id)->update(['is_del'=>1]))
            return Json::fail(FansDataModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }

    /**
     * 选择商品弹窗
     * @return string
     */
    public function select()
    {
        return $this->fetch();
    }

    public function set_status($status= '' ,$id=''){
        ($status == '' || $id == '') && JsonService::fail('缺少参数');
        $res = \app\admin\model\fans\FansData::where(['id' => $id])->update(['status' => (int)$status]);
        if ($res) {
            return JsonService::successful($status == 1 ? '开启成功' : '关闭成功');
        } else {
            return JsonService::fail($status == 1 ? '开启失败' : '关闭失败');
        }
    }

    /**
     * 设置单个店员是否开启
     * @param string $is_show
     * @param string $id
     * @return json
     */
    public function set_show($is_show = '', $id = '')
    {

    }
}
