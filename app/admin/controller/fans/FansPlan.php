<?php

namespace app\admin\controller\fans;

use app\admin\controller\AuthController;
use app\admin\model\fans\FansCouponIssue;
use app\admin\model\fans\FansPlan as FansPlanModel;
use app\admin\model\fans\FansUserTag;
use app\admin\model\order\StoreOrder;
use app\admin\model\ump\StoreCouponIssue;
use app\admin\model\user\User;
use app\admin\model\user\UserTag;
use crmeb\basic\BaseModel;
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
class FansPlan extends AuthController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //获取当前已经发布了的计划数量;
        $curr_plan_count=FansPlanModel::where('tenant_id','=',session('tenant_id'))
            ->where('is_del','=',0)
            ->count();
        $plan_upper=config('site.plan_upper');
        $can_add_plan=true;//是否还能创建新的计划
        if($plan_upper<=$curr_plan_count){
            $can_add_plan=false;
        }
        $this->assign('can_add_plan',$can_add_plan);
        return $this->fetch();
    }


    public function get_all_plan_count(){
        //获取当前已经发布了的计划数量;
        $curr_plan_count=FansPlanModel::where('tenant_id','=',session('tenant_id'))
            ->where('is_del','=',0)
            ->count();
        return Json::successful('营销计划最多不能超过3条',['count'=>$curr_plan_count]);
    }


    /*
     *  异步获取分类列表
     *  @return json
     */
    public function plan_list()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['order', ''],
            ['title', ''],
            ['type',0]
        ]);
        return Json::successlayui(FansPlanModel::getPlanList($where));
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
        $news['note_id'] = 0;
        $news['note_info'] = [];
        $news['note_info_json'] = '';
        $news['coupon_id'] = 0;
        $news['coupon_info'] = [];
        $news['coupon_info_json'] = '';
        $news['user_tag_list'] = [];
        $news['user_tag_json'] = '';

        if ($id) {
            $news = FansPlanModel::where('id', $id)->find();
            if (!$news) return $this->failed('数据不存在!');
            $news=$news->toArray();
            $news['start_time']=$news['start_time']?date('Y-m-d H:i:s',$news['start_time']):'';
            $news['end_time']=$news['end_time']?date('Y-m-d H:i:s',$news['end_time']):'';
            //关联的笔记详情-使用二维数组
            $note_info=(new \app\admin\model\fans\FansNote())->where('id','=',$news['note_id'])->select();
            $news['note_info']=$note_info?$note_info->toArray():[];
            $news['note_info_json']=json_encode($news['note_info']);

            //关联的优惠券
            $coupon_issue_list=(new FansCouponIssue())->where('fans_plan_id','=',$id)->select();
            if(count($coupon_issue_list)){
                $coupon_issue_list=$coupon_issue_list->column('coupon_issue_id');
                $coupon_issue_ids=implode($coupon_issue_list,',');
                $where=['ids'=>$coupon_issue_ids];
                $model=StoreCouponIssue::getModelToSelect($where);
                $coupon_issue_list=$model->select();
                if(count($coupon_issue_list)){
                    $coupon_issue_list=$coupon_issue_list->toArray();
                    foreach($coupon_issue_list as $key=>$val){
                        switch ($val['type']){
                            case 0:
                                $coupon_issue_list[$key]['type_text']='平台券';
                                break;
                            case 1:
                                $coupon_issue_list[$key]['type_text']='品类券';
                                break;
                            default:
                                $coupon_issue_list[$key]['type_text']='商品券';
                        }

                        if($val['start_time']>0){
                            $coupon_issue_list[$key]['time_text']=date('Y-m-d',$val['start_time']).'至'.date('Y-m-d',$val['end_time']);
                        }else{
                            $coupon_issue_list[$key]['time_text']='不限时';
                        }

                        if($val['is_permanent']){
                            $coupon_issue_list[$key]['count_text']='不限量';
                        }else{
                            $coupon_issue_list[$key]['count_text']='<b style="color: #0a6aa1">发布:'.$val["total_count"].'</b><br/><b style="color:#ff0000;">剩余:'.$val["remain_count"].'</b>';
                        }
                    }
                }else{
                    $coupon_issue_list=[];
                }
            }else{
                $coupon_issue_list=[];
            }
            $news['coupon_info']=$coupon_issue_list;
            $news['coupon_info_json']=json_encode($coupon_issue_list);



            //处理关联的用户标签
            $user_tag_list=(new FansUserTag())->where('fans_plan_id','=',$id)->select();
            if(count($user_tag_list)){
                $user_tag_list=$user_tag_list->column('user_tag_id');
                $user_tag_ids=implode($user_tag_list,',');
                $where=['ids'=>$user_tag_ids];
                $user_tags_list=UserTag::getSytemListToSelect($where);
            }else{
                $user_tags_list=[];
            }

            $news['user_tag_list']=$user_tags_list;
            $news['user_tag_json']=json_encode($user_tags_list);

        }


        $this->assign('all', $all);
        $this->assign('news', $news);

        //获取当前所有用户数量
        $all_user_count=User::where('tenant_id','=',session('tenant_id'))->count();
        $this->assign('all_user_count',$all_user_count);

        //获取所有已消费过的用户
        $payed_user_count=StoreOrder::where('tenant_id','=',session('tenant_id'))
            ->where('pay_time','>',0)
            ->group('uid')
            ->count();
        //未消费人群数量=总人数-已消费用户
        $not_pay_user_count=$all_user_count-$payed_user_count;
        $not_pay_user_count=$not_pay_user_count>0?$not_pay_user_count:0;
        $this->assign('not_pay_user_count',$not_pay_user_count);

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
            $news = \app\admin\model\fans\FansPlan::where('id', $id)->find();
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
            ['note_id',[]],
            ['is_long', 0],
            ['is_coupon', 0],
            ['coupon_ids', []],
            ['tag_ids', []],
            ['is_into_shop', 1],
        ], $request);



        if (!$data['title']) return Json::fail('请输入标题');
        if($data['type']==2 && !$data['tag_ids']) return Json::fail('请选择用户标签');
        if (!$data['note_id']) return Json::fail('请选择笔记');

        $data['add_time'] = time();
        $data['tenant_id'] = session('tenant_id');

        $data['note_id']=$data['note_id'][0];
        $couponIdsArr=$data['coupon_ids'];


        if(($data['is_long']==0 || !$data['is_long']) && (!$data['start_time'] || !$data['end_time'])){
            return Json::fail('请设置计划开始和结束时间');
        }

        $data['start_time']=$data['start_time']?strtotime($data['start_time']):0;
        $data['end_time']=$data['end_time']?strtotime($data['end_time']):0;

        if($data['is_coupon'] && !$data['coupon_ids']){
            return Json::fail('选择优惠券');
        }


        BaseModel::beginTrans();
        if($data['id']){
            $id=$data['id'];
            unset($data['id']);
            FansPlanModel::edit($data,$id,'id');

            if($data['is_coupon']){
                $coupon_data=[];
                //处理商品和笔记的关联数据-先清空已有数据
                (new FansCouponIssue())->where('fans_plan_id','=',$id)->delete();
                foreach($couponIdsArr as $val){
                    $tem['fans_plan_id']=$id;
                    $tem['coupon_issue_id']=$val;
                    $coupon_data[]=$tem;
                }
                $res=(new FansCouponIssue())->saveAll($coupon_data);
                if(!$res){
                    BaseModel::rollbackTrans();
                    return Json::fail('保存失败');
                }
            }

            if($data['type']==2){
                //存储用户标签
                $tag_data=[];
                //处理商品和笔记的关联数据-先清空已有数据
                (new FansUserTag())->where('fans_plan_id','=',$id)->delete();
                foreach($data['tag_ids'] as $val){
                    $tem['fans_plan_id']=$id;
                    $tem['user_tag_id']=$val;
                    $tag_data[]=$tem;
                }
                //存储优惠券
                $res=(new FansUserTag())->saveAll($tag_data);
                if(!$res){
                    BaseModel::rollbackTrans();
                    return Json::fail('添加失败');
                }
            }



            BaseModel::commitTrans();
            return Json::successful('保存成功');

        }else{

            //新增新数据
            unset($data['id']);



            $res=FansPlanModel::create($data);
            if(!$res){
                return Json::fail('添加失败');
            }


            $fans_id=$res->getData('id');
            if($data['is_coupon']){
                //处理商品和笔记的关联数据
                $coupon_data=[];
                foreach($couponIdsArr as $val){
                    $tem['fans_plan_id']=$fans_id;
                    $tem['coupon_issue_id']=$val;
                    $coupon_data[]=$tem;
                }
                //存储优惠券
                $res=(new FansCouponIssue())->saveAll($coupon_data);
                if(!$res){
                    BaseModel::rollbackTrans();
                    return Json::fail('添加失败');
                }
            }

            if($data['type']==2){
                //存储用户标签
                $tag_data=[];
                foreach($data['tag_ids'] as $val){
                    $tem['fans_plan_id']=$fans_id;
                    $tem['user_tag_id']=$val;
                    $tag_data[]=$tem;
                }
                //存储优惠券
                $res=(new FansUserTag())->saveAll($tag_data);
                if(!$res){
                    BaseModel::rollbackTrans();
                    return Json::fail('添加失败');
                }
            }

            BaseModel::commitTrans();
            return Json::successful('保存成功');
        }
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
        if (!FansPlanModel::where('id','=',$id)->update(['is_del'=>1]))
            return Json::fail(FansPlanModel::getErrorInfo('删除失败,请稍候再试!'));
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
        $res = \app\admin\model\fans\FansPlan::where(['id' => $id])->update(['status' => (int)$status]);
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


    /**
     * 选择笔记弹窗
     * @return string
     */
    public function select_note()
    {
        //处理已经选中的商品
        $ids=input('param.ids');
        $this->assign('ids',$ids);
        return $this->fetch();
    }




    public function save_cache_note(){
        $id=input('param.id');
        $image=input('param.image');
        $title=input('param.title');

        $data=[
            ['id'=>$id, 'image'=>$image, 'title'=>$title]
        ];
        $cache_key='select_note_'.session('tenant_id');
        cache($cache_key,null);
        cache($cache_key,$data);

        return Json::successful('提交成功!');
    }

    public function get_cache_note(){
        $cache_key='select_note_'.session('tenant_id');
        $res=cache($cache_key);
        if($res){
            cache($cache_key,null);
            return Json::successful('获取成功',$res);
        }else{
            return Json::fail('无数据');
        }
    }


    /**
     * 选择笔记弹窗
     * @return string
     */
    public function select_coupon()
    {
        //处理已经选中的商品
        $ids=input('param.ids');
        $this->assign('ids',$ids);
        return $this->fetch();
    }


    public function save_cache_coupon(){
        $ids=input('param.ids');
        $checked_ids=input('param.checked_ids');

        $cache_key='select_coupon_'.session('tenant_id');
        if($ids){
            $ids=implode(',',$ids);
            if($checked_ids){
                $ids.=','.$checked_ids;
            }
            $where=['ids'=>$ids,];
            $model=StoreCouponIssue::getModelToSelect($where);
            $product_list=$model->select();

            if(count($product_list)){
                $product_list=$product_list->toArray();
                foreach($product_list as $key=>$val){
                    switch ($val['type']){
                        case 0:
                            $product_list[$key]['type_text']='平台券';
                            break;
                        case 1:
                            $product_list[$key]['type_text']='品类券';
                            break;
                        default:
                            $product_list[$key]['type_text']='商品券';
                    }

                    if($val['start_time']>0){
                        $product_list[$key]['time_text']=date('Y-m-d',$val['start_time']).'至'.date('Y-m-d',$val['end_time']);
                    }else{
                        $product_list[$key]['time_text']='不限时';
                    }
                    if($val['is_permanent']){
                        $product_list[$key]['count_text']='不限量';
                    }else{
                        $product_list[$key]['count_text']='<b style="color: #0a6aa1">发布:'.$val["total_count"].'</b><br/><b style="color:#ff0000;">剩余:'.$val["remain_count"].'</b>';
                    }
                }
            }else{
                $product_list=[];
            }
        }else{
            $product_list=[];
        }

        cache($cache_key,null);
        cache($cache_key,$product_list);

        return Json::successful('提交成功!');
    }

    public function get_cache_coupon(){
        $cache_key='select_coupon_'.session('tenant_id');
        $res=cache($cache_key);
        if($res){
            cache($cache_key,null);
            return Json::successful('获取成功',$res);
        }else{
            return Json::fail('无数据');
        }
    }




    /**
     * 选择标签弹窗
     * @return string
     */
    public function select_tag()
    {
        //处理已经选中的商品
        $ids=input('param.ids');
        $this->assign('ids',$ids);
        return $this->fetch();
    }


    public function save_cache_tag(){
        $ids=input('param.ids');
        $checked_ids=input('param.checked_ids');



        $cache_key='select_tag_'.session('tenant_id');
        if($ids){
            $ids=implode(',',$ids);
            if($checked_ids){
                $ids.=','.$checked_ids;
            }
            $where=['ids'=>$ids,'page'=>1,'limit'=>100,'title'=>''];
            $tag_list=UserTag::getSytemListToSelect($where);
            if(count($tag_list)){
                foreach($tag_list as $key=>$val){
                    if($val['is_auto']){
                        $tag_list[$key]['is_auto_text']='自动标签';
                    }else{
                        $tag_list[$key]['is_auto_text']='手动标签';

                    }
                }
            }
        }else{
            $tag_list=[];
        }

        cache($cache_key,null);
        cache($cache_key,$tag_list);

        return Json::successful('提交成功!');
    }

    public function get_cache_tag(){
        $cache_key='select_tag_'.session('tenant_id');
        $res=cache($cache_key);
        if($res){
            cache($cache_key,null);
            return Json::successful('获取成功',$res);
        }else{
            return Json::fail('无数据');
        }
    }


}
