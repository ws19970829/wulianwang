<?php

namespace app\admin\controller\user;

use app\admin\controller\AuthController;
use app\admin\model\user\UserTagCity;
use app\admin\model\user\UserTagProduct;
use crmeb\basic\BaseModel;
use think\facade\Route as Url;
use crmeb\traits\CurdControllerTrait;
use app\admin\model\user\UserTag as UserTagModel;
use crmeb\services\{UtilService,JsonService,FormBuilder as Form};
use app\admin\model\system\SystemCity;


/**
 * 会员设置
 * Class UserTag
 * @package app\admin\controller\user
 */
class UserTag extends AuthController
{
    use CurdControllerTrait;

    /*
     * 等级展示
     * */
    public function index()
    {
        return $this->fetch();
    }

    /*
     * 创建form表单
     * */
    public function create($id = 0)
    {
        $id = $this->request->param('id');
        $news = [];
        $all = [];
        $news['id'] = '';
        $news['title'] = '';
        $news['is_auto'] = 0;
        $news['type'] = 1;
        //最后消费时间
        $news['is_last_pay_time'] = 0;
        $news['pay_time_type'] = 0;
        $news['last_day'] = '';
        $news['pay_start_time'] = '';
        $news['pay_end_time'] = '';
        //累积消费次数
        $news['is_pay_num_type'] = 0;
        $news['pay_num_lower'] = '';
        $news['pay_num_upper'] = '';
        //累积消费金额
        $news['is_pay_money_type'] = 0;
        $news['pay_money_lower'] = '';
        $news['pay_money_upper'] = '';
        //客单价
        $news['is_per_price'] = 0;
        $news['per_price_lower'] = '';
        $news['per_price_upper'] = '';
        $news['is_product_type'] = 0;
        $news['last_view_day'] = 0;
        $news['is_city_type'] = 0;
        $news['sex'] = 0;
        $news['sort'] = 0;
        $product_list=[];
        $city_list=[];
        $product_list_json='';
        $city_list_json='';

        if ($id) {
            $news = \app\admin\model\user\UserTag::where('id', $id)->find();
            if (!$news) return $this->failed('数据不存在!');
            $news=$news->toArray();

            $news['pay_start_time']=$news['pay_start_time']?date('Y-m-d H:i:s',$news['pay_start_time']):'';
            $news['pay_end_time']=$news['pay_end_time']?date('Y-m-d H:i:s',$news['pay_end_time']):'';


            //关联的商品-使用二维数组
            $product_list=(new UserTagProduct())->getProductListByTagId($id);
            $product_list_json=json_encode($product_list);



            //关联的地区
            $city_list=(new UserTagCity())->getCityListByTagId($id);
            $city_list_json=json_encode($city_list);


        }


        $this->assign('all', $all);
        $this->assign('news', $news);
        $this->assign('product_list', $product_list);
        $this->assign('product_list_json',$product_list_json);
        $this->assign('city_list', $city_list);
        $this->assign('city_list_json',$city_list_json);

        return $this->fetch();
    }

    /*
     * 会员等级添加或者修改
     * @param $id 修改的等级id
     * @return json
     * */
    public function save($id = 0)
    {
        $data = UtilService::postMore([
            ['title', ''],
            ['is_auto', 0],
            ['type', 1],
            ['pay_time_type', 1],
            ['last_day', 0],
            ['pay_start_time', 0],
            ['pay_end_time', 0],
            ['is_pay_num_type', 0],
            ['pay_num_lower', 0],
            ['pay_num_upper', 0],
            ['is_pay_money_type', 0],
            ['pay_money_lower', 0],
            ['pay_money_upper', 0],
            ['is_per_price', 0],
            ['per_price_lower', 0],
            ['per_price_upper', 0],
            ['last_view_day', 0],
            ['is_product_type', 0],
            ['is_city_type', 0],
            ['cityIdArr', []],
            ['goodsIdArr', []],
            ['sex', 0],
            ['sort', 0],
        ]);

        if (!$data['title']) return JsonService::fail('请输入标签名称');
        if($data['pay_time_type']==1 && !$data['last_day']) return  JsonService::fail('请输入最近几天');
        if($data['pay_time_type']==2 && (!$data['pay_start_time'] || !$data['pay_end_time'])) return  JsonService::fail('请设定时间区间');
        if($data['is_pay_num_type']==1 && (!$data['pay_num_lower'] || !$data['pay_num_upper'])) return  JsonService::fail('请设定累积消费次数区间');
        if($data['is_pay_money_type']==1 && (!$data['pay_money_lower'] || !$data['pay_money_upper'])) return  JsonService::fail('请设定累积消费金额区间');
        if($data['is_per_price']==1 && (!$data['per_price_lower'] || !$data['per_price_upper'])) return  JsonService::fail('请设定客单价区间');
        if($data['is_product_type']==1 && !$data['goodsIdArr']) return  JsonService::fail('请选择商品');
        if($data['is_city_type']==1 && !$data['cityIdArr']) return  JsonService::fail('请选择地区');


        BaseModel::beginTrans();
        try {
            //修改
            if ($id) {
                unset($data['id']);
                $res=UserTagModel::edit($data,$id,'id');
                if ($data['is_product_type']) {
                    //插入标签商品关联的商品信息
                    $arr=[];
                    foreach($data['goodsIdArr'] as $val){
                        $tem['user_tag_id']=$id;
                        $tem['product_id']=$val;
                        $arr[]=$tem;
                    }
                    //先删除旧数据
                    (new UserTagProduct())->where('user_tag_id','=',$id)->delete();
                    $res=(new UserTagProduct())->saveAll($arr);
                }
                if(!$res){
                    BaseModel::rollbackTrans();
                    return JsonService::fail('更改失败，请稍后再试');
                }


                if($data['is_city_type']){
                    //插入地区关联信息
                    $arr=[];
                    foreach($data['cityIdArr'] as $val){
                        $tem['user_tag_id']=$id;
                        $tem['system_city_id']=$val;
                        $arr[]=$tem;
                    }
                    //先删除旧数据
                    (new UserTagCity())->where('user_tag_id','=',$id)->delete();
                    $res=(new UserTagCity())->saveAll($arr);
                }
                if(!$res){
                    BaseModel::rollbackTrans();
                    return JsonService::fail('添加失败');
                }



                BaseModel::commitTrans();
                return JsonService::successful('保存成功');
            } else {
                //新增-插入标签表
                $data['tenant_id'] = session('tenant_id');
                $res=UserTagModel::create($data);
                $user_tag_id=$res->getData('id');

                $res=$user_tag_id;

                if ($user_tag_id && $data['is_product_type']) {
                    //插入标签商品关联的商品信息
                    $arr=[];
                    foreach($data['goodsIdArr'] as $val){
                        $tem['user_tag_id']=$user_tag_id;
                        $tem['product_id']=$val;
                        $arr[]=$tem;
                    }
                    $res=(new UserTagProduct())->saveAll($arr);
                }
                if(!$res){
                    BaseModel::rollbackTrans();
                    return JsonService::fail('添加失败');
                }


                if($user_tag_id && $data['is_city_type']){
                    //插入地区关联信息
                    $arr=[];
                    foreach($data['cityIdArr'] as $val){
                        $tem['user_tag_id']=$user_tag_id;
                        $tem['system_city_id']=$val;
                        $arr[]=$tem;
                    }
                    $res=(new UserTagCity())->saveAll($arr);
                }
                if(!$res){
                    BaseModel::rollbackTrans();
                    return JsonService::fail('添加失败');
                }



                BaseModel::commitTrans();
                return JsonService::successful('保存成功');

            }
        } catch (\Exception $e) {
            BaseModel::rollbackTrans();
            return JsonService::fail($e->getMessage());
        }
    }

    /*
     * 获取系统设置的vip列表
     * @param int page
     * @param int limit
     * */
    public function get_list()
    {
        $where = UtilService::getMore([
            ['page', 0],
            ['limit', 10],
            ['title', ''],
            ['is_show', ''],
        ]);
        return JsonService::successlayui(UserTagModel::getSytemList($where));
    }

    public function set_status($status= '' ,$id=''){
        ($status == '' || $id == '') && JsonService::fail('缺少参数');
        $res = \app\admin\model\user\UserTag::where(['id' => $id])->update(['status' => (int)$status]);
        if ($res) {
            return JsonService::successful($status == 1 ? '开启成功' : '关闭成功');
        } else {
            return JsonService::fail($status == 1 ? '开启失败' : '关闭失败');
        }
    }


    /*
     * 删除
     * @param int $id
     * */
    public function delete($id = 0)
    {
        if (UserTagModel::edit(['is_del' => 1], $id))
            return JsonService::successful('删除成功');
        else
            return JsonService::fail('删除失败');
    }

    /**
     * 选择城市页面
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function city()
    {
        $data = UtilService::getMore([
            ['type', 0],
            ['isedit', 0]
        ]);
        $this->assign('is_layui', true);
        $this->assign($data);
        return $this->fetch();
    }

    //获取城市列表
    public function city_list()
    {
        $list = SystemCity::with('children')->where('parent_id', 0)->order('id asc')->select();
        return app('json')->success($list->toArray());
    }


    /**
     * 选择商品提交并存入缓存
     */
    public function save_cache_city(){
        $lists=input('param.lists');
        $checked_ids=input('param.checked_ids');
        $cache_key='select_city_'.session('tenant_id');
        if($lists){
            $data=[];
            $systemCityModel=(new SystemCity());
            foreach($lists as $val){
                foreach($val['children'] as $v){
                    $tem=$systemCityModel->where('city_id','=',$v['city_id'])->field('id,city_id,name,merger_name,area_code')->find();
                    if($tem){
                        $tem=$tem->toArray();
                    }
                    $tem['province_id']=$val['city_id'];
                    $tem['province_name']=$val['name'];
                    $data[]=$tem;
                }

            }
        }else{
            $data=[];
        }
        cache($cache_key,null);
        cache($cache_key,$data);

        return JsonService::successful('提交成功!');
    }

    public function get_cache_city(){
        $cache_key='select_city_'.session('tenant_id');
        $res=cache($cache_key);
        if($res){
            cache($cache_key,null);
            return JsonService::successful('获取成功',$res);
        }else{
            return JsonService::fail('无数据');
        }
    }



    /**
     * 设置单个产品上架|下架
     *
     * @return json
     */
    public function set_show($is_show = '', $id = '')
    {
        ($is_show == '' || $id == '') && Json::fail('缺少参数');
        $res = SystemUserTag::where(['id' => $id])->update(['is_show' => (int)$is_show]);
        if ($res) {
            return JsonService::successful($is_show == 1 ? '显示成功' : '隐藏成功');
        } else {
            return JsonService::fail($is_show == 1 ? '显示失败' : '隐藏失败');
        }
    }

    /**
     * 快速编辑
     *
     * @return json
     */
    public function set_value($field = '', $id = '', $value = '')
    {
        $field == '' || $id == '' || $value == '' && Json::fail('缺少参数');
        if (SystemUserTag::where(['id' => $id])->update([$field => $value]))
            return JsonService::successful('保存成功');
        else
            return JsonService::fail('保存失败');
    }


    /*
     * 等级任务列表
     * @param int $vip_id 等级id
     * @return json
     * */
    public function tash($tag_id = 0)
    {
        $this->assign('tag_id', $tag_id);
        return $this->fetch();
    }

    /**
     * 快速编辑
     *
     * @return json
     */
    public function set_tash_value($field = '', $id = '', $value = '')
    {
        $field == '' || $id == '' || $value == '' && Json::fail('缺少参数');
        if (SystemUserTask::where(['id' => $id])->update([$field => $value]))
            return JsonService::successful('保存成功');
        else
            return JsonService::fail('保存失败');
    }

    /**
     * 设置单个产品上架|下架
     *
     * @return json
     */
    public function set_tash_show($is_show = '', $id = '')
    {
        ($is_show == '' || $id == '') && Json::fail('缺少参数');
        $res = SystemUserTask::where(['id' => $id])->update(['is_show' => (int)$is_show]);
        if ($res) {
            return JsonService::successful($is_show == 1 ? '显示成功' : '隐藏成功');
        } else {
            return JsonService::fail($is_show == 1 ? '显示失败' : '隐藏失败');
        }
    }

    /**
     * 设置单个产品上架|下架
     *
     * @return json
     */
    public function set_tash_must($is_must = '', $id = '')
    {
        ($is_must == '' || $id == '') && Json::fail('缺少参数');
        $res = SystemUserTask::where(['id' => $id])->update(['is_must' => (int)$is_must]);
        if ($res) {
            return JsonService::successful('设置成功');
        } else {
            return JsonService::fail('设置失败');
        }
    }

    /*
     * 生成任务表单
     * @param int $id 任务id
     * @param int $vip_id 会员id
     * @return html
     * */
    public function create_tash($id = 0, $tag_id = 0)
    {
        if ($id) $tash = SystemUserTask::get($id);
        $field[] = Form::select('task_type', '任务类型', isset($tash) ? $tash->task_type : '')->setOptions(function () {
            $list = SystemUserTask::getTaskTypeAll();
            $menus = [];
            foreach ($list as $menu) {
                $menus[] = ['value' => $menu['type'], 'label' => $menu['name'] . '----单位[' . $menu['unit'] . ']'];
            }
            return $menus;
        })->filterable(1);
        $field[] = Form::number('number', '限定数量', isset($tash) ? $tash->number : 0)->min(0)->col(24);
        $field[] = Form::number('sort', '排序', isset($tash) ? $tash->sort : 0)->min(0)->col(24);
        $field[] = Form::radio('is_show', '是否显示', isset($tash) ? $tash->is_show : 1)->options([['label' => '显示', 'value' => 1], ['label' => '隐藏', 'value' => 0]])->col(24);
        $field[] = Form::radio('is_must', '是否务必达成', isset($tash) ? $tash->is_must : 1)->options([['label' => '务必达成', 'value' => 1], ['label' => '完成其一', 'value' => 0]])->col(24);
        $field[] = Form::textarea('illustrate', '任务说明', isset($tash) ? $tash->illustrate : '');
        $form = Form::make_post_form('添加任务', $field, Url::buildUrl('save_tash', ['id' => $id, 'tag_id' => $tag_id]), 2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }


    /*
     * 保存或者修改任务
     * @param int $id 任务id
     * @param int $vip_id 会员id
     * */
    public function save_tash($id = 0, $tag_id = 0)
    {
        if (!$tag_id) return JsonService::fail('缺少参数');
        $data = UtilService::postMore([
            ['task_type', ''],
            ['number', 0],
            ['is_show', 0],
            ['sort', 0],
            ['is_must', 0],
            ['illustrate', ''],
        ]);
        if (!$data['task_type']) return JsonService::fail('请选择任务类型');
        if ($data['number'] < 1) return JsonService::fail('请输入限定数量,数量不能小于1');
        $tash = SystemUserTask::getTaskType($data['task_type']);
        if ($tash['max_number'] != 0 && $data['number'] > $tash['max_number']) return JsonService::fail('您设置的限定数量超出最大限制,最大限制为:' . $tash['max_number']);
        $data['name'] = SystemUserTask::setTaskName($data['task_type'], $data['number']);
        try {
            if ($id) {
                SystemUserTask::edit($data, $id);
                return JsonService::successful('修改成功');
            } else {
                $data['tag_id'] = $tag_id;
                $data['add_time'] = time();
                $data['real_name'] = $tash['real_name'];
                if (SystemUserTask::create($data))
                    return JsonService::successful('添加成功');
                else
                    return JsonService::fail('添加失败');
            }
        } catch (\Exception $e) {
            return JsonService::fail($e->getMessage());
        }
    }

    /*
     * 异步获取等级任务列表
     * @param int $vip_id 会员id
     * @param int $page 分页
     * @param int $limit 显示条数
     * @return json
     * */
    public function get_tash_list($tag_id = 0)
    {
        list($page, $limit) = UtilService::getMore([
            ['page', 1],
            ['limit', 10],
        ], $this->request, true);
        return JsonService::successlayui(SystemUserTask::getTashList($tag_id, (int)$page, (int)$limit));
    }

    /*
     * 删除任务
     * @param int 任务id
     * */
    public function delete_tash($id = 0)
    {
        if (!$id) return JsonService::fail('缺少差参数');
        if (SystemUserTask::del($id))
            return JsonService::successful('删除成功');
        else
            return JsonService::fail('删除失败');
    }

    /*
     * 会员等级展示
     *
     * */
    public function user_tag_list()
    {
        $this->assign('tag', SystemUserTag::where('is_del', 0)->where('is_show', 1)->order('grade asc')->field(['id', 'name'])->select());
        return $this->fetch();
    }

    public function get_user_vip_list()
    {
        $where = UtilService::getMore([
            ['page', 1],
            ['limit', 10],
            ['nickname', ''],
            ['tag_id', ''],
        ]);
        return JsonService::successlayui(UserTagModel::getUserVipList($where));
    }

}