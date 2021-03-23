<?php

namespace app\superadmin\controller\setting;

use app\admin\model\system\ShippingTemplatesRegion;
use app\models\article\Article;
use app\models\store\StoreProductRule;
use app\superadmin\controller\AuthController;
use crmeb\basic\BaseModel;
use crmeb\services\{FormBuilder as Form, JsonService as Json, UtilService as Util};
use app\superadmin\model\system\{SystemRole, SystemAdmin as AdminModel};
use think\facade\Route as Url;
use think\Request;

/**
 * 管理员列表控制器
 * Class SystemAdmin
 * @package app\admin\controller\system
 */
class SystemTenant extends AuthController
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
            ['roles', 2],
            ['level', bcadd($admin->level, 1, 0)],
        ]);
        $this->assign('where', $where);
        $this->assign('role', SystemRole::getRole(bcadd($admin->level, 1, 0)));
        $list=AdminModel::systemPage($where);
        $this->assign($list);
        return $this->fetch();
    }


    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function settle()
    {
        $product_id = input('product_id');
        if (!$product_id) $product_id = 0;
        $this->assign('is_layui', true);
        $this->assign('product_id', (int)$product_id);
        return $this->fetch();
    }

    /**
     * 商家结算情况
     */
    public function get_settle_list()
    {
        $where = Util::getMore([
            ['limit', 10],
            ['title', ''],
            ['is_reply', ''],
            ['message_page', 1],
            ['producr_id', 0],
            ['order_id', ''],
            ['nickname', ''],
            ['score_type', ''],
        ]);
        return Json::successful(AdminModel::get_settle_list($where));
    }


    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $admin = $this->adminInfo;
        $f = array();
        $f[] = Form::input('account', '商家账号');
        $f[] = Form::input('pwd', '登录密码')->type('password');
        $f[] = Form::input('conf_pwd', '确认密码')->type('password');
        $f[] = Form::input('real_name', '商家姓名');
        $f[] = Form::input('mobile', '联系电话');
        $f[] = Form::input('addr', '联系地址');
        $f[] = Form::input('idcard_num', '身份证号码');
    
        $f[] = Form::frameImages('idcard_img', '身份证照片', Url::buildUrl('superadmin/widget.images/index', array('fodder' => 'idcard_img')))->maxLength(5)->icon('images')->width('100%')->height('500px')->spin(0);

        $f[] = Form::input('company_name', '公司名称');
        $f[] = Form::input('company_tel', '公司联系电话');
        $f[] = Form::frameImages('business_img', '营业执照', Url::buildUrl('superadmin/widget.images/index', array('fodder' => 'business_img')))->maxLength(5)->icon('images')->width('100%')->height('500px')->spin(0);

        $f[] = Form::frameImages('logo_img', '商家logo', Url::buildUrl('superadmin/widget.images/index', array('fodder' => 'logo_img')))->maxLength(1)->icon('images')->width('100%')->height('500px')->spin(0);


        $f[] = Form::input('remark', '介绍说明')->type('textarea');

//        $f[] = Form::select('roles', '管理员身份')->setOptions(function () use ($admin) {
//            $list = SystemRole::getRole(bcadd($admin->level, 1, 0));
//            $options = [];
//            foreach ($list as $id => $roleName) {
//                $options[] = ['label' => $roleName, 'value' => $id];
//            }
//            return $options;
//        })->multiple(1);
        $f[] = Form::radio('status', '状态', 1)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        $f[] = Form::number('sort', '排序',0);
        // $f[] = Form::radio('is_rec', '首页推荐', 0)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]])->col(12);
        $form = Form::make_post_form('添加管理员', $f, Url::buildUrl('save'));
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
            'account',
            'conf_pwd',
            'pwd',
            'real_name',
            'mobile',
            'addr',
            'idcard_num',
            'company_name',
            'company_tel',
            'remark',
            ['status', 0],
            ['sort', 0],
            // ['is_rec', 0],
            ['idcard_img', []],
            ['business_img', []],
            ['logo_img', []],
        ]);


        if (!$data['account']) return Json::fail('请输入管理员账号');
        if (!$data['pwd']) return Json::fail('请输入管理员登陆密码');
        if ($data['pwd'] != $data['conf_pwd']) return Json::fail('两次输入密码不想同');
        if (AdminModel::be($data['account'], 'account')) return Json::fail('管理员账号已存在');
        $data['pwd'] = md5($data['pwd']);
        $data['add_time'] = time();
        $data['is_superadmin_create']=1;//是否是超级管理员后台创建的用户
        unset($data['conf_pwd']);
        $data['level'] = $this->adminInfo['level'] + 1;
        $data['add_time'] = time();

        $data['idcard_img'] = json_encode($data['idcard_img']);
        $data['business_img'] = json_encode($data['business_img']);
        $data['logo_img'] = json_encode($data['logo_img']);
        $data['roles'] = 2;//商家管理只能创建商家身份


//        $res=AdminModel::create($data);
//        if($res){
//            $tenant_id=$res->getData('id');
//            $this->create_init_data($tenant_id);
//            return Json::successful('添加商家成功!');
//        }else{
//            return Json::fail('添加商家失败');
//        }

        BaseModel::beginTrans();
        try {
            $res=AdminModel::create($data);
            if($res){
                $tenant_id=$res->getData('id');
                $res=$this->create_init_data($tenant_id);
            }else{
                BaseModel::rollbackTrans();
                return Json::fail('添加商家失败:数据添加失败');
            }
        } catch (\Exception $e) {
            BaseModel::rollbackTrans();
            return Json::fail('添加商家失败:'.$e->getMessage());
        }

        BaseModel::checkTrans($res);
        return Json::successful('添加商家成功!');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        if (!$id) return $this->failed('参数错误');
        $admin = AdminModel::get($id);
        if (!$admin) return Json::fail('数据不存在!');
        $f = array();

        $f[] = Form::input('account', '商家账号', $admin->account);
        $f[] = Form::input('pwd', '登录密码')->type('password');
        $f[] = Form::input('conf_pwd', '确认密码')->type('password');
        $f[] = Form::input('real_name', '商家姓名', $admin->real_name);
        $f[] = Form::input('mobile', '联系电话', $admin->mobile);
        $f[] = Form::input('addr', '联系地址', $admin->addr);
        $f[] = Form::input('idcard_num', '身份证号码', $admin->idcard_num);
        $f[] = Form::frameImages('idcard_img', '身份证照片', Url::buildUrl('superadmin/widget.images/index', array('fodder' => 'idcard_img')),json_decode($admin->getData('idcard_img'), 1))->maxLength(5)->icon('images')->width('100%')->height('500px')->spin(0);

        $f[] = Form::input('company_name', '公司名称', $admin->company_name);
        $f[] = Form::input('company_tel', '公司联系电话', $admin->company_tel);
        $f[] = Form::frameImages('business_img', '营业执照', Url::buildUrl('superadmin/widget.images/index', array('fodder' => 'business_img')),json_decode($admin->getData('business_img'), 1))->maxLength(5)->icon('images')->width('100%')->height('500px')->spin(0);
       
        $f[] = Form::frameImages('logo_img', '商铺logo', Url::buildUrl('superadmin/widget.images/index', array('fodder' => 'logo_img')),json_decode($admin->getData('logo_img'), 1))->maxLength(1)->icon('images')->width('100%')->height('500px')->spin(0);
        $f[] = Form::input('remark', '介绍说明', $admin->remark)->type('textarea');
        $f[] = Form::radio('status', '状态', $admin->status)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        $f[] = Form::number('sort', '排序',$admin->sort);
        // $f[] = Form::radio('is_rec', '首页推荐', $admin->is_rec)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]])->col(12);
        $form = Form::make_post_form('编辑管理员', $f, Url::buildUrl('update', compact('id')));
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
            'account',
            'conf_pwd',
            'pwd',
            'real_name',
            'mobile',
            'addr',
            'idcard_num',
            'company_name',
            'company_tel',
            'remark',
            ['status', 0],
            ['sort', 0],
            // ['is_rec', 0],
            ['idcard_img', []],
            ['business_img', []],
            ['logo_img', []],
        ]);
        if (!$data['account']) return Json::fail('请输入管理员账号');
        if (!$data['pwd'])
            unset($data['pwd']);
        else {
            if (isset($data['pwd']) && $data['pwd'] != $data['conf_pwd']) return Json::fail('两次输入密码不想同');
            $data['pwd'] = md5($data['pwd']);
        }

        if (AdminModel::where('account', $data['account'])->where('id', '<>', $id)->count()) return Json::fail('管理员账号已存在');
        unset($data['conf_pwd']);

        $data['idcard_img'] = json_encode($data['idcard_img']);
        $data['business_img'] = json_encode($data['business_img']);
        $data['logo_img'] = json_encode($data['logo_img']);

        if (!AdminModel::edit($data, $id)) return Json::fail('修改失败');
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
        if (AdminModel::edit(['is_del' => 1, 'status' => 0], $id, 'id'))
            return Json::successful('删除成功!');
        else
            return Json::fail('删除失败!');
    }

    /**
     * 个人资料 展示
     * @return string
     */
    public function admin_info()
    {
        $adminInfo = $this->adminInfo;//获取当前登录的管理员
        $this->assign('adminInfo', $adminInfo);
        return $this->fetch();
    }

    /**
     * 保存信息
     */
    public function setAdminInfo()
    {
        $adminInfo = $this->adminInfo;//获取当前登录的管理员
        if ($this->request->isPost()) {
            $data = Util::postMore([
                ['new_pwd', ''],
                ['new_pwd_ok', ''],
                ['pwd', ''],
                'real_name',
            ]);
            if ($data['pwd'] != '') {
                $pwd = md5($data['pwd']);
                if ($adminInfo['pwd'] != $pwd) return Json::fail('原始密码错误');
            }
            if ($data['new_pwd'] != '') {
                if (!$data['new_pwd_ok']) return Json::fail('请输入确认新密码');
                if ($data['new_pwd'] != $data['new_pwd_ok']) return Json::fail('俩次密码不一样');
            }
            if ($data['pwd'] != '' && $data['new_pwd'] != '') {
                $data['pwd'] = md5($data['new_pwd']);
            } else {
                unset($data['pwd']);
            }
            unset($data['new_pwd']);
            unset($data['new_pwd_ok']);
            if (!AdminModel::edit($data, $adminInfo['id'])) return Json::fail('修改失败');
            return Json::successful('修改成功!,请重新登录');
        }
    }


    /**
     * 生成平台方用户时创建初始配置项数据
     * @param $tenant_id
     * @return bool
     */
    public function create_init_data($tenant_id){
        $data=[
            //基础配置
            ["menu_name" => "site_name", "type" => "text", "input_type" => "input", "config_tab_id" => 1, "parameter" => "", "upload_type" => 0, "required" => "required:true", "width" => 100, "high" => 0, "value" => "\"\"", "info" => "网站名称", "desc" => "网站名称", "sort" => 0, "status" => 1, "tenant_id" => $tenant_id],
            ["menu_name" => "site_url", "type" => "text", "input_type" => "input", "config_tab_id" => 1, "parameter" => "", "upload_type" => 0, "required" => "required:true,url:true", "width" => 100, "high" => 0, "value" => "\"\"", "info" => "网站地址", "desc" => "网站地址", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "site_logo", "type" => "upload", "input_type" => '', "config_tab_id" => 1, "parameter" => '', "upload_type" => 1, "required" => '', "width" => 0, "high" => 0, "value" => "\"\"", "info" => "后台LOGO", "desc" => "左上角logo,建议尺寸[170*50]", "sort" => 0, "status" => 1, "tenant_id" => $tenant_id],
            ["menu_name" => "seo_title", "type" => "text", "input_type" => "input", "config_tab_id" => 1, "parameter" => "", "upload_type" => 0, "required" => "required:true", "width" => 100, "high" => 0, "value" => "\"\"", "info" => "SEO标题", "desc" => "SEO标题", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "news_slides_limit", "type" => "text", "input_type" => "number", "config_tab_id" => 1, "parameter" => '', "upload_type" => '', "required" => "required:true,digits:true,min:1", "width" => 100, "high" => '', "value" => "\"5\"", "info" => "新闻幻灯片限制数量", "desc" => "新闻幻灯片限制数量", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "cache_config", "type" => "text", "input_type" => "input", "config_tab_id" => 1, "parameter" => '', "upload_type" => '', "required" => "", "width" => 100, "high" => '', "value" => "\"86400\"", "info" => "网站缓存时间", "desc" => "配置全局缓存时间（秒），默认留空为永久缓存", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "filing_info", "type" => "text", "input_type" => "input", "config_tab_id" => 1, "parameter" => "", "upload_type" => '', "required" => "", "width" => 100, "high" => '', "value" => "", "info" => "备案信息", "desc" => "网站备案信息", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "server_tel", "type" => "text", "input_type" => "input", "config_tab_id" => 1, "parameter" => "", "upload_type" => '', "required" => "", "width" => 100, "high" => '', "value" => "", "info" => "客服电话", "desc" => "客服联系电话", "sort" => 0, "status" => 1, "tenant_id" => $tenant_id],
            ["menu_name" => "server_qrcode_img", "type" => "upload", "input_type" => '', "config_tab_id" => 1, "parameter" => '', "upload_type" => 1, "required" => '', "width" => 0, "high" => 0, "value" => "\"\"", "info" => "客服联系二维码", "desc" => "显示在用户中心", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],

            //商城配置
            ["menu_name" => "store_user_min_recharge", "type" => "text", "input_type" => "input", "config_tab_id" => 5, "parameter" => "", "upload_type" => 0, "required" => "required:true,number:true,min:0", "width" => 100, "high" => 0, "value" => "\"0.01\"", "info" => "用户最低充值金额", "desc" => "用户单次最低充值金额", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "replenishment_num", "type" => "text", "input_type" => "input", "config_tab_id" => 5, "parameter" => "", "upload_type" => 0, "required" => "required:true,number:true,min:0", "width" => 100, "high" => 0, "value" => "\"20\"", "info" => "待补货数量", "desc" => "商品待补货数量低于多少时，提示补货", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "store_stock", "type" => "text", "input_type" => "input", "config_tab_id" => 5, "parameter" => "", "upload_type" => 0, "required" => "", "width" => 100, "high" => 0, "value" => "\"2\"", "info" => "警戒库存", "desc" => "警戒库存提醒值", "sort" => 0, "status" => 1, "tenant_id" => $tenant_id],
            ["menu_name" => "stor_reason", "type" => "textarea", "input_type" => "input", "config_tab_id" => 5, "parameter" => "", "upload_type" => 0, "required" => "", "width" => 100, "high" => 8, "value" => "\"\u6536\u8d27\u5730\u5740\u586b\u9519\u4e86\r\n\u4e0e\u63cf\u8ff0\u4e0d\u7b26\r\n\u4fe1\u606f\u586b\u9519\u4e86\uff0c\u91cd\u65b0\u62cd\r\n\u6536\u5230\u5546\u54c1\u635f\u574f\u4e86\r\n\u672a\u6309\u9884\u5b9a\u65f6\u95f4\u53d1\u8d27\r\n\u5176\u5b83\u539f\u56e0\"", "info" => "退货理由", "desc" => "配置退货理由，一行一个理由", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "routine_index_logo", "type" => "upload", "input_type" => '', "config_tab_id" => 5, "parameter" => '', "upload_type" => 1, "required" => '', "width" => '', "high" => '', "value" => "\"\"", "info" => "首页顶部logo图标", "desc" => "主页logo图标尺寸(127*45)", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id,],
            ["menu_name" => "order_cancel_time", "type" => "text", "input_type" => "input", "config_tab_id" => 5, "parameter" => '', "upload_type" => '', "required" => "", "width" => 100, "high" => '', "value" => "\"0.1\"", "info" => "普通商品未支付取消订单时间", "desc" => "普通商品未支付取消订单时间，单位（小时）", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "order_activity_time", "type" => "text", "input_type" => "input", "config_tab_id" => 5, "parameter" => '', "upload_type" => '', "required" => "", "width" => 100, "high" => '', "value" => "\"2\"", "info" => "活动商品未支付取消订单时间", "desc" => "活动商品未支付取消订单时间，单位（小时）", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "order_bargain_time", "type" => "text", "input_type" => "input", "config_tab_id" => 5, "parameter" => '', "upload_type" => '', "required" => '', "width" => 100, "high" => '', "value" => "\"\"", "info" => "砍价未支付取消订单时间", "desc" => "砍价未支付默认取消订单时间，单位（小时），如果为0将使用默认活动取消时间，优先使用单独活动配置", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "order_seckill_time", "type" => "text", "input_type" => "input", "config_tab_id" => 5, "parameter" => '', "upload_type" => '', "required" => '', "width" => 100, "high" => '', "value" => "\"\"", "info" => "秒杀未支付订单取消时间", "desc" => "秒杀未支付订单取消时间，单位（小时），如果为0将使用默认活动取消时间，优先使用单独活动配置", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "order_pink_time", "type" => "text", "input_type" => "input", "config_tab_id" => 5, "parameter" => '', "upload_type" => '', "required" => '', "width" => 100, "high" => '', "value" => "\"\"", "info" => "拼团未支付取消订单时间", "desc" => "拼团未支付取消订单时间,单位（小时），如果为0将使用默认活动取消时间，优先使用单独活动配置", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "vip_open", "type" => "radio", "input_type" => "input", "config_tab_id" => 5, "parameter" => "0=>关闭\n1=>开启", "upload_type" => '', "required" => '', "width" => '', "high" => '', "value" => "\"1\"", "info" => "会员功能是否开启", "desc" => "会员功能是否开启", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "new_order_audio_link", "type" => "upload", "input_type" => "input", "config_tab_id" => 5, "parameter" => '', "upload_type" => 3, "required" => '', "width" => '', "high" => '', "value" => "\"\/public\/uploads\/config\/file\/5cedd83eedba2.mp3\"", "info" => "新订单语音提示", "desc" => "新订单语音提示", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "system_delivery_time", "type" => "text", "input_type" => "input", "config_tab_id" => 5, "parameter" => '', "upload_type" => '', "required" => "required:true,digits:true,min:0", "width" => 100, "high" => '', "value" => "\"1\"", "info" => "自动收货时间", "desc" => "系统自动收货时间,单位(天),0为不设置自动收货", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "offline_pay_status", "type" => "radio", "input_type" => '', "config_tab_id" => 5, "parameter" => "1=>开启\n2=>关闭", "upload_type" => '', "required" => '', "width" => '', "high" => '', "value" => "\"2\"", "info" => "线下支付状态", "desc" => "线下支付状态", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "recharge_switch", "type" => "radio", "input_type" => "input", "config_tab_id" => 5, "parameter" => "1=>开启\n0=>关闭", "upload_type" => '', "required" => '', "width" => '', "high" => '', "value" => "\"1\"", "info" => "小程序充值开关", "desc" => "小程序充值开关", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "new_goods_bananr", "type" => "upload", "input_type" => '', "config_tab_id" => 5, "parameter" => '', "upload_type" => 1, "required" => '', "width" => '', "high" => '', "value" => "", "info" => "首发新品广告图（414*99）", "desc" => "首发新品广告图", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "recharge_attention", "type" => "textarea", "input_type" => '', "config_tab_id" => 5, "parameter" => '', "upload_type" => '', "required" => '', "width" => 100, "high" => 5, "value" => "\"\u5145\u503c\u540e\u5e10\u6237\u7684\u91d1\u989d\u4e0d\u80fd\u63d0\u73b0\uff0c\u53ef\u7528\u4e8e\u5546\u57ce\u6d88\u8d39\u4f7f\u7528\n\u4f63\u91d1\u5bfc\u5165\u8d26\u6237\u4e4b\u540e\u4e0d\u80fd\u518d\u6b21\u5bfc\u51fa\u3001\u4e0d\u53ef\u63d0\u73b0\n\u8d26\u6237\u5145\u503c\u51fa\u73b0\u95ee\u9898\u53ef\u8054\u7cfb\u5546\u57ce\u5ba2\u670d\uff0c\u4e5f\u53ef\u62e8\u6253\u5546\u57ce\u5ba2\u670d\u70ed\u7ebf\uff1a4008888888\"", "info" => "充值注意事项", "desc" => "充值注意事项", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],

            //应用配置-公众号配置
            ["menu_name" => "wechat_appid", "type" => "text", "input_type" => "input", "config_tab_id" => 2, "parameter" => "", "upload_type" => 0, "required" => "required:true", "width" => 100, "high" => 0, "value" => "\"\"", "info" => "AppID", "desc" => "AppID", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "wechat_appsecret", "type" => "text", "input_type" => "input", "config_tab_id" => 2, "parameter" => "", "upload_type" => 0, "required" => "required:true", "width" => 100, "high" => 0, "value" => "\"\"", "info" => "AppSecret", "desc" => "AppSecret", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "wechat_token", "type" => "text", "input_type" => "input", "config_tab_id" => 2, "parameter" => "", "upload_type" => 0, "required" => "required:true", "width" => 100, "high" => 0, "value" => "\"\"", "info" => "微信验证TOKEN", "desc" => "微信验证TOKEN", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "wechat_encode", "type" => "radio", "input_type" => "input", "config_tab_id" => 2, "parameter" => "0=>明文模式\n1=>兼容模式\n2=>安全模式", "upload_type" => 0, "required" => "", "width" => 0, "high" => 0, "value" => "\"0\"", "info" => "消息加解密方式", "desc" => "如需使用安全模式请在管理中心修改，仅限服务号和认证订阅号", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "wechat_encodingaeskey", "type" => "text", "input_type" => "input", "config_tab_id" => 2, "parameter" => "", "upload_type" => 0, "required" => "required:true", "width" => 100, "high" => 0, "value" => "\"\"", "info" => "EncodingAESKey", "desc" => "公众号消息加解密Key,在使用安全模式情况下要填写该值，请先在管理中心修改，然后填写该值，仅限服务号和认证订阅号", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "wechat_share_img", "type" => "upload", "input_type" => '', "config_tab_id" => 2, "parameter" => '', "upload_type" => 1, "required" => '', "width" => 0, "high" => 0, "value" => "\"\"", "info" => "微信分享图片", "desc" => "若填写此图片地址，则分享网页出去时会分享此图片。可有效防止分享图片变形", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "wechat_qrcode", "type" => "upload", "input_type" => "input", "config_tab_id" => 2, "parameter" => "", "upload_type" => 1, "required" => "", "width" => 0, "high" => 0, "value" => "\"\"", "info" => "公众号关注二维码", "desc" => "公众号关注二维码", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "wechat_type", "type" => "radio", "input_type" => "input", "config_tab_id" => 2, "parameter" => "0=>服务号\n1=>订阅号", "upload_type" => 0, "required" => "", "width" => 0, "high" => 0, "value" => "\"0\"", "info" => "公众号类型", "desc" => "公众号的类型", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "wechat_share_title", "type" => "text", "input_type" => "input", "config_tab_id" => 2, "parameter" => '', "upload_type" => '', "required" => "required:true", "width" => 100, "high" => 0, "value" => "\"\u5546\u57ce\u540d\u79f0\"", "info" => "微信分享标题", "desc" => "微信分享标题", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "wechat_share_synopsis", "type" => "textarea", "input_type" => '', "config_tab_id" => 2, "parameter" => '', "upload_type" => '', "required" => '', "width" => 100, "high" => 5, "value" => "\"\u5546\u57ce\u7b80\u4ecb\"", "info" => "微信分享简介", "desc" => "微信分享简介", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "api", "type" => "text", "input_type" => "input", "config_tab_id" => 2, "parameter" => "", "upload_type" => 0, "required" => "", "width" => 100, "high" => 0, "value" => "\"\/api\/wechat\/serve\"", "info" => "接口地址", "desc" => "微信接口例如：http://www.abc.com/api/wechat/serve", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "wechat_avatar", "type" => "upload", "input_type" => "input", "config_tab_id" => 2, "parameter" => "", "upload_type" => 1, "required" => "", "width" => 0, "high" => 0, "value" => "\"\"", "info" => "H5登录logo", "desc" => "H5登录logo", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "h5_avatar", "type" => "upload", "input_type" => '', "config_tab_id" => 2, "parameter" => '', "upload_type" => 1, "required" => '', "width" => 0, "high" => 0, "value" => "\"http:\/\/kaifa.crmeb.net\/uploads\/attach\/2019\/08\/20190807\/723adbdd4e49a0f9394dfc700ab5dba3.png\"", "info" => "用户H5默认头像", "desc" => "用户H5默认头像尺寸(80*80)", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            //应用配置-小程序配置
            ["menu_name" => "routine_appId", "type" => "text", "input_type" => "input", "config_tab_id" => 7, "parameter" => "", "upload_type" => 0, "required" => "", "width" => 100, "high" => 0, "value" => "\"\"", "info" => "appId", "desc" => "小程序appID", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "routine_appsecret", "type" => "text", "input_type" => "input", "config_tab_id" => 7, "parameter" => "", "upload_type" => 0, "required" => "", "width" => 100, "high" => 0, "value" => "\"\"", "info" => "AppSecret", "desc" => "小程序AppSecret", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "routine_logo", "type" => "upload", "input_type" => '', "config_tab_id" => 7, "parameter" => '', "upload_type" => 1, "required" => '', "width" => 0, "high" => 0, "value" => "\"\"", "info" => "小程序授权logo", "desc" => "小程序授权logo", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "routine_name", "type" => "text", "input_type" => "input", "config_tab_id" => 7, "parameter" => "", "upload_type" => 0, "required" => "", "width" => 100, "high" => 0, "value" => "\"\u5546\u57ce\u540d\u79f0\"", "info" => "小程序名称", "desc" => "小程序名称", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],

            //支付配置-公众号配置
            ["menu_name" => "pay_weixin_appid", "type" => "text", "input_type" => "input", "config_tab_id" => 4, "parameter" => "", "upload_type" => 0, "required" => "", "width" => 100, "high" => 0, "value" => "\"\"", "info" => "Appid", "desc" => "微信公众号身份的唯一标识。审核通过后，在微信发送的邮件中查看。", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "pay_weixin_appsecret", "type" => "text", "input_type" => "input", "config_tab_id" => 4, "parameter" => "", "upload_type" => 0, "required" => "", "width" => 100, "high" => 0, "value" => "\"\"", "info" => "Appsecret", "desc" => "JSAPI接口中获取openid，审核后在公众平台开启开发模式后可查看。", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "pay_weixin_mchid", "type" => "text", "input_type" => "input", "config_tab_id" => 4, "parameter" => "", "upload_type" => 0, "required" => "", "width" => 100, "high" => 0, "value" => "\"\"", "info" => "Mchid", "desc" => "受理商ID，身份标识", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "pay_weixin_client_cert", "type" => "upload", "input_type" => "input", "config_tab_id" => 4, "parameter" => "", "upload_type" => 3, "required" => "", "width" => 0, "high" => 0, "value" => "\"\"", "info" => "微信支付证书", "desc" => "微信支付证书，在微信商家平台中可以下载！文件名一般为apiclient_cert.pem", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "pay_weixin_client_key", "type" => "upload", "input_type" => "input", "config_tab_id" => 4, "parameter" => "", "upload_type" => 3, "required" => "", "width" => 0, "high" => 0, "value" => "\"\"", "info" => "微信支付证书密钥", "desc" => "微信支付证书密钥，在微信商家平台中可以下载！文件名一般为apiclient_key.pem", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "pay_weixin_key", "type" => "text", "input_type" => "input", "config_tab_id" => 4, "parameter" => "", "upload_type" => 0, "required" => "", "width" => 100, "high" => 0, "value" => "\"\"", "info" => "Key", "desc" => "商户支付密钥Key。审核通过后，在微信发送的邮件中查看。", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "pay_weixin_open", "type" => "radio", "input_type" => "input", "config_tab_id" => 4, "parameter" => "1=>开启\n0=>关闭", "upload_type" => 0, "required" => "", "width" => 0, "high" => 0, "value" => "\"1\"", "info" => "开启", "desc" => "是否启用微信支付", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "paydir", "type" => "textarea", "input_type" => "input", "config_tab_id" => 4, "parameter" => "", "upload_type" => 0, "required" => "", "width" => 100, "high" => 5, "value" => "\"\"", "info" => "配置目录", "desc" => "支付目录配置系统不调用提示作用", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],

            //支付配置-小程序配置
            ["menu_name" => "pay_routine_appid", "type" => "text", "input_type" => "input", "config_tab_id" => 14, "parameter" => "", "upload_type" => 0, "required" => "required:true", "width" => 100, "high" => 0, "value" => "\"\"", "info" => "Appid", "desc" => "小程序Appid", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "pay_routine_appsecret", "type" => "text", "input_type" => "input", "config_tab_id" => 14, "parameter" => "", "upload_type" => 0, "required" => "required:true", "width" => 100, "high" => 0, "value" => "\"\"", "info" => "Appsecret", "desc" => "小程序Appsecret", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "pay_routine_mchid", "type" => "text", "input_type" => "input", "config_tab_id" => 14, "parameter" => "", "upload_type" => 0, "required" => "required:true", "width" => 100, "high" => 0, "value" => "\"\"", "info" => "Mchid", "desc" => "商户号", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "pay_routine_key", "type" => "text", "input_type" => "input", "config_tab_id" => 14, "parameter" => "", "upload_type" => 0, "required" => "required:true", "width" => 100, "high" => 0, "value" => "\"\"", "info" => "Key", "desc" => "商户key", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "pay_routine_client_cert", "type" => "upload", "input_type" => "input", "config_tab_id" => 14, "parameter" => "", "upload_type" => 3, "required" => "", "width" => 0, "high" => 0, "value" => "\"\"", "info" => "小程序支付证书", "desc" => "小程序支付证书", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "pay_routine_client_key", "type" => "upload", "input_type" => "input", "config_tab_id" => 14, "parameter" => "", "upload_type" => 3, "required" => "", "width" => 0, "high" => 0, "value" => "\"\"", "info" => "小程序支付证书密钥", "desc" => "小程序支付证书密钥", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],

            //首页配置
            ["menu_name" => "fast_number", "type" => "text", "input_type" => "input", "config_tab_id" => 16, "parameter" => '', "upload_type" => '', "required" => "required:true,digits:true,min:1", "width" => 100, "high" => '', "value" => "\"10\"", "info" => "快速选择分类个数", "desc" => "首页配置快速选择分类个数", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "bast_number", "type" => "text", "input_type" => "input", "config_tab_id" => 16, "parameter" => '', "upload_type" => '', "required" => "required:true,digits:true,min:1", "width" => 100, "high" => '', "value" => "\"10\"", "info" => "精品推荐个数", "desc" => "首页配置精品推荐个数", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "first_number", "type" => "text", "input_type" => "input", "config_tab_id" => 16, "parameter" => '', "upload_type" => '', "required" => "required:true,digits:true,min:1", "width" => 100, "high" => '', "value" => "\"10\"", "info" => "首发新品个数", "desc" => "首页配置首发新品个数", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "promotion_number", "type" => "text", "input_type" => "input", "config_tab_id" => 16, "parameter" => "", "upload_type" => '', "required" => "required:true,digits:true,min:1", "width" => 100, "high" => '', "value" => "3", "info" => "促销单品个数", "desc" => "小程序首页配置促销单品个数", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],

            //文件上传配置
            ["menu_name" => "upload_type", "type" => "radio", "input_type" => "input", "config_tab_id" => 17, "parameter" => "1=>本地存储\n2=>七牛云存储\n3=>阿里云OSS\n4=>腾讯COS", "upload_type" => '', "required" => '', "width" => '', "high" => '', "value" => "\"2\"", "info" => "上传类型", "desc" => "文件上传的类型", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "uploadUrl", "type" => "text", "input_type" => "input", "config_tab_id" => 17, "parameter" => '', "upload_type" => '', "required" => "url:true", "width" => 100, "high" => '', "value" => "\"qiniu.xiaohuixiang.3todo.com\"", "info" => "空间域名 Domain", "desc" => "空间域名 Domain", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "accessKey", "type" => "text", "input_type" => "input", "config_tab_id" => 17, "parameter" => '', "upload_type" => '', "required" => "", "width" => 100, "high" => '', "value" => "\"aKGxVmufdTu61hhxVCInypo_kWOoEGuY2x1F9bJI\"", "info" => "accessKey", "desc" => "accessKey", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "secretKey", "type" => "text", "input_type" => "input", "config_tab_id" => 17, "parameter" => '', "upload_type" => '', "required" => "", "width" => 100, "high" => '', "value" => "\"4CopuVyZOhcEjx2hKRnd9QmUE8U7P9G68Rgn1KV6\"", "info" => "secretKey", "desc" => "secretKey", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "storage_name", "type" => "text", "input_type" => "input", "config_tab_id" => 17, "parameter" => '', "upload_type" => '', "required" => "", "width" => 100, "high" => '', "value" => "\"xiaohuixiang\"", "info" => "存储空间名称", "desc" => "存储空间名称", "sort" => 0, "status" => 0,"tenant_id" => $tenant_id],
            ["menu_name" => "storage_region", "type" => "text", "input_type" => "input", "config_tab_id" => 17, "parameter" => '', "upload_type" => '', "required" => "", "width" => 100, "high" => '', "value" => "\"\u534e\u4e1c\"", "info" => "所属地域", "desc" => "所属地域", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],


            //短信提醒开关
            ["menu_name" => "lower_order_switch", "type" => "radio", "input_type" => '', "config_tab_id" => 20, "parameter" => "0=>关闭\n1=>开启", "upload_type" => '', "required" => '', "width" => '', "high" => '', "value" => "\"0\"", "info" => "支付成功提醒开关", "desc" => "支付成功提醒开关", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "deliver_goods_switch", "type" => "radio", "input_type" => '', "config_tab_id" => 20, "parameter" => "0=>关闭\n1=>开启", "upload_type" => '', "required" => '', "width" => '', "high" => '', "value" => "\"0\"", "info" => "发货提醒开关", "desc" => "发货提醒开关", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "confirm_take_over_switch", "type" => "radio", "input_type" => '', "config_tab_id" => 20, "parameter" => "0=>关闭\n1=>开启", "upload_type" => '', "required" => '', "width" => '', "high" => '', "value" => "\"0\"", "info" => "确认收货提醒开关", "desc" => "确认收货提醒开关", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "admin_lower_order_switch", "type" => "radio", "input_type" => '', "config_tab_id" => 20, "parameter" => "0=>关闭\n1=>开启", "upload_type" => '', "required" => '', "width" => '', "high" => '', "value" => "\"0\"", "info" => "用户下单管理员提醒开关", "desc" => "用户下单管理员提醒开关", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "admin_pay_success_switch", "type" => "radio", "input_type" => '', "config_tab_id" => 20, "parameter" => "0=>关闭\n1=>开启", "upload_type" => '', "required" => '', "width" => '', "high" => '', "value" => "\"0\"", "info" => "用户支付成功管理员提醒开关", "desc" => "用户支付成功管理员提醒开关", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "admin_refund_switch", "type" => "radio", "input_type" => '', "config_tab_id" => 20, "parameter" => "0=>关闭\n1=>开启", "upload_type" => '', "required" => '', "width" => '', "high" => '', "value" => "\"0\"", "info" => "用户退款管理员提醒开关", "desc" => "用户退款管理员提醒开关", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "admin_confirm_take_over_switch", "type" => "radio", "input_type" => '', "config_tab_id" => 20, "parameter" => "0=>关闭\n1=>开启", "upload_type" => '', "required" => '', "width" => '', "high" => '', "value" => "\"0\"", "info" => "用户确认收货管理员短信提醒", "desc" => "用户确认收货管理员短信提醒", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "price_revision_switch", "type" => "radio", "input_type" => "input", "config_tab_id" => 20, "parameter" => "0=>关闭\n1=>开启", "upload_type" => '', "required" => '', "width" => '', "high" => '', "value" => "\"0\"", "info" => "改价短信提醒开关", "desc" => "改价短信提醒开关", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "unpid_order_switch", "type" => "radio", "input_type" => "input", "config_tab_id" => 20, "parameter" => "0=>关闭\n1=>开启", "upload_type" => '', "required" => '', "width" => '', "high" => '', "value" => "\"0\"", "info" => "未支付订单用户短信提醒", "desc" => "未支付订单用户短信提醒", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],

            //小票打印设置
            ["menu_name" => "pay_success_printing_switch", "type" => "radio", "input_type" => '', "config_tab_id" => 21, "parameter" => "0=>关闭\n1=>开启", "upload_type" => '', "required" => '', "width" => '', "high" => '', "value" => "\"1\"", "info" => "支付成功订单打印开关", "desc" => "支付成功订单打印开关", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "develop_id", "type" => "text", "input_type" => "input", "config_tab_id" => 21, "parameter" => '', "upload_type" => '', "required" => "", "width" => 100, "high" => '', "value" => "\"\"", "info" => "开发者ID", "desc" => "易联云开发者ID", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "printing_api_key", "type" => "text", "input_type" => "input", "config_tab_id" => 21, "parameter" => '', "upload_type" => '', "required" => "", "width" => 100, "high" => '', "value" => "\"\"", "info" => "应用密钥", "desc" => "易联应用密钥", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "printing_client_id", "type" => "text", "input_type" => "input", "config_tab_id" => 21, "parameter" => '', "upload_type" => '', "required" => "", "width" => 100, "high" => '', "value" => "\"\"", "info" => "应用ID", "desc" => "易联应用ID", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "terminal_number", "type" => "text", "input_type" => "input", "config_tab_id" => 21, "parameter" => '', "upload_type" => '', "required" => "", "width" => 100, "high" => '', "value" => "\"\"", "info" => "终端号", "desc" => "易联云打印机终端号", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],

            //物流配置
            ["menu_name" => "store_postage", "type" => "text", "input_type" => "input", "config_tab_id" => 10, "parameter" => "", "upload_type" => 0, "required" => "number:true,min:0", "width" => 100, "high" => 0, "value" => "\"0\"", "info" => "邮费基础价", "desc" => "商品邮费基础价格,最终金额为(基础价 + 商品1邮费 + 商品2邮费)", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "store_free_postage", "type" => "text", "input_type" => "input", "config_tab_id" => 10, "parameter" => "", "upload_type" => 0, "required" => "number:true,min:-1", "width" => 100, "high" => 0, "value" => "\"1000\"", "info" => "满额包邮", "desc" => "商城商品满多少金额即可包邮", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "offline_postage", "type" => "radio", "input_type" => "input", "config_tab_id" => 10, "parameter" => "0=>不包邮\n1=>包邮", "upload_type" => 0, "required" => "", "width" => 0, "high" => 0, "value" => "\"0\"", "info" => "线下支付是否包邮", "desc" => "用户选择线下支付时是否包邮", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "system_express_app_code", "type" => "text", "input_type" => "input", "config_tab_id" => 10, "parameter" => "", "upload_type" => 0, "required" => "", "width" => 100, "high" => 0, "value" => "\"\"", "info" => "快递查询密钥", "desc" => "阿里云快递查询接口密钥购买地址：https://market.aliyun.com/products/56928004/cmapi021863.html", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "tengxun_map_key", "type" => "text", "input_type" => "input", "config_tab_id" => 10, "parameter" => '', "upload_type" => '', "required" => "", "width" => 100, "high" => '', "value" => "", "info" => "腾讯地图KEY", "desc" => "腾讯地图KEY", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "store_self_mention", "type" => "radio", "input_type" => '', "config_tab_id" => 10, "parameter" => "0=>关闭\n1=>开启", "upload_type" => '', "required" => '', "width" => '', "high" => '', "value" => "\"1\"", "info" => "是否开启门店自提", "desc" => "是否开启门店自提", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],

            //分销配置
            ["menu_name" => "store_brokerage_statu", "type" => "radio", "input_type" => "input", "config_tab_id" => 9, "parameter" => "1=>指定分销\n2=>人人分销", "upload_type" => 0, "required" => "", "width" => 0, "high" => 0, "value" => "\"2\"", "info" => "分销模式", "desc" => "人人分销默认每个人都可以分销，制定人分销后台制定人开启分销", "sort" => 10, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "store_brokerage_ratio", "type" => "text", "input_type" => "input", "config_tab_id" => 9, "parameter" => "", "upload_type" => 0, "required" => "required:true,min:0,max:100,number:true", "width" => 100, "high" => 0, "value" => "\"80\"", "info" => "一级返佣比例", "desc" => "订单交易成功后给上级返佣的比例0 - 100,例:5 = 返订单金额的5%", "sort" => 5, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "store_brokerage_two", "type" => "text", "input_type" => "input", "config_tab_id" => 9, "parameter" => "", "upload_type" => 0, "required" => "required:true,min:0,max:100,number:true", "width" => 100, "high" => 0, "value" => "\"60\"", "info" => "二级返佣比例", "desc" => "订单交易成功后给上级返佣的比例0 - 100,例:5 = 返订单金额的5%", "sort" => 4, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "user_extract_min_price", "type" => "text", "input_type" => "input", "config_tab_id" => 9, "parameter" => "", "upload_type" => 0, "required" => "required:true,number:true,min:0", "width" => 100, "high" => 0, "value" => "\"100\"", "info" => "提现最低金额", "desc" => "用户提现最低金额", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "user_extract_bank", "type" => "textarea", "input_type" => "input", "config_tab_id" => 9, "parameter" => "", "upload_type" => 0, "required" => "", "width" => 100, "high" => 5, "value" => "\"\u4e2d\u56fd\u519c\u884c\r\n\u4e2d\u56fd\u5efa\u8bbe\u94f6\u884c\r\n\u5de5\u5546\u94f6\u884c\"", "info" => "提现银行卡", "desc" => "提现银行卡，每个银行换行", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "extract_time", "type" => "text", "input_type" => "input", "config_tab_id" => 9, "parameter" => '', "upload_type" => '', "required" => "", "width" => 100, "high" => '', "value" => "30", "info" => "冻结时间", "desc" => "佣金冻结时间(天)", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "store_brokerage_price", "type" => "text", "input_type" => "input", "config_tab_id" => 9, "parameter" => '', "upload_type" => '', "required" => "", "width" => 100, "high" => '', "value" => "\"1\"", "info" => "人人分销满足金额", "desc" => "人人分销满足金额开通分销权限", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],

            //积分配置
            ["menu_name" => "integral_ratio", "type" => "text", "input_type" => "input", "config_tab_id" => 11, "parameter" => "", "upload_type" => 0, "required" => "number:true", "width" => 100, "high" => 0, "value" => "\"1\"", "info" => "积分抵用比例", "desc" => "积分抵用比例(1积分抵多少金额)", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],

            //短信配置
            ["menu_name" => "sms_account", "type" => "text", "input_type" => "input", "config_tab_id" => 18, "parameter" => '', "upload_type" => '', "required" => "", "width" => 100, "high" => '', "value" => "\"\"", "info" => "账号", "desc" => "短信后台的登录账号", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],
            ["menu_name" => "sms_token", "type" => "text", "input_type" => "input", "config_tab_id" => 18, "parameter" => '', "upload_type" => '', "required" => "", "width" => 100, "high" => '', "value" => "\"\"", "info" => "token/密码", "desc" => "token(注册时候的密码)", "sort" => 0, "status" => 0, "tenant_id" => $tenant_id],

        ];

//        $tenant_id='';
        (new \app\superadmin\model\system\SystemConfig())->saveAll($data);

        //商品规格模板初始数据
        $product_rules=[
            ["rule_name" => "鞋类", "rule_value" => '[{"value":"\u989c\u8272","detailValue":"","attrHidden":"","detail":["\u9ed1\u8272","\u767d\u8272"]},{"value":"\u5c3a\u7801","detailValue":"","attrHidden":"","detail":["37","38","39","40","41","42","43","44","45"]}]', "tenant_id" => $tenant_id],
            ["rule_name" => "衣服", "rule_value" => '[{"value":"\u989c\u8272","detailValue":"","attrHidden":"","detail":["\u7ea2\u8272","\u84dd\u8272","\u767d\u8272"]},{"value":"\u5c3a\u7801","detailValue":"","attrHidden":"","detail":["XL","S","M","XXL","XXXL"]},{"value":"\u5e74\u9f84","detailValue":"","attrHidden":"","detail":["3","5","7","9","3-4"]}]', "tenant_id" => $tenant_id],
            ["rule_name" => "衣服模板", "rule_value" => '[{"value":"\u989c\u8272","detailValue":"","attrHidden":"","detail":["\u9ed1\u8272","\u767d\u8272","\u7eff\u8272","\u7ea2\u8272","\u6a58\u8272"]},{"value":"\u5c3a\u7801","detailValue":"","attrHidden":"","detail":["L","XL","XXL","XXXL"]}]', "tenant_id" => $tenant_id],
            ["rule_name" => "色彩模板", "rule_value" => '[{"value":"\u989c\u8272","detailValue":"","attrHidden":"","detail":["\u767d\u8272","\u9ed1\u8272","\u7ea2\u8272"]}]', "tenant_id" => $tenant_id],
        ];
        (new StoreProductRule())->saveAll($product_rules);


        //运费模板初始数据-注意是单条数据
        $shipping_templates=[
            'name'=>'通用模板',
            'type'=>1,
            'appoint'=>0,
            'sort'=>0,
            'add_time'=>time(),
            'tenant_id'=>$tenant_id
        ];
        $temp_id=(new \app\admin\model\system\ShippingTemplates())->insertGetId($shipping_templates);
        if($temp_id){
            $temp_region=[
                'province_id'=>0,
                'temp_id'=>$temp_id,
                'city_id'=>0,
                'first'=>10,
                'first_price'=>1,
                'continue'=>20,
                'continue_price'=>1,
                'type'=>1,
                'uniqid'=>uniqid(true) . rand(1000, 9999),
                'tenant_id'=>$tenant_id
            ];
            (new ShippingTemplatesRegion())->insertGetId($temp_region);
        }

        //----------------------------------
        //              数据配置项
        //----------------------------------
        $systemGroupModel=(new \app\superadmin\model\system\SystemGroup());
        $systemGroupDateModel=(new \app\superadmin\model\system\SystemGroupData());

//        //团购、折扣、有礼顶部banner图
        $system_group= ['name'=>'活动页banner', 'info'=>'团购、折扣、有礼顶部banner图', 'config_name'=>'routine_lovely', 'fields'=>'[{"name":"\u56fe\u7247","title":"img","type":"upload","param":""},{"name":"\u63cf\u8ff0","title":"comment","type":"input","param":""}]', 'tenant_id'=>$tenant_id];
        $gid=$systemGroupModel->insertGetId($system_group);
        $system_data=[
            ['gid'=>$gid, 'value'=>'{"img":{"type":"upload","value":"http:\/\/qiniu.xiaohuixiang.3todo.com\/78e9f202008132215053684.jpg"},"comment":{"type":"input","value":"\u79d2\u6740\u5217\u8868\u9876\u90e8baaner"}}', 'add_time'=>time(), 'sort'=>1, "status" => 0,'tenant_id'=>$tenant_id],
            ['gid'=>$gid, 'value'=>'{"img":{"type":"upload","value":"http:\/\/qiniu.xiaohuixiang.3todo.com\/78e9f202008132215053684.jpg"},"comment":{"type":"input","value":"\u79d2\u6740\u5217\u8868\u9876\u90e8baaner"}}', 'add_time'=>time(), 'sort'=>1, "status" => 0,'tenant_id'=>$tenant_id],
            ['gid'=>$gid, 'value'=>'{"img":{"type":"upload","value":"http:\/\/qiniu.xiaohuixiang.3todo.com\/78e9f202008132215053684.jpg"},"comment":{"type":"input","value":"\u780d\u4ef7\u5217\u8868\u9876\u90e8baaner"}}', 'add_time'=>time(), 'sort'=>1, "status" => 0,'tenant_id'=>$tenant_id],
        ];
        $systemGroupDateModel->saveAll($system_data);


        //首页分类图标
        $system_group= ['name'=>'首页分类图标', 'info'=>'首页分类图标', 'config_name'=>'routine_home_menus', 'fields'=>'[{"name":"\u5206\u7c7b\u540d\u79f0","title":"name","type":"input","param":""},{"name":"\u5206\u7c7b\u56fe\u6807(90*90)","title":"pic","type":"upload","param":""},{"name":"\u5c0f\u7a0b\u5e8f\u8df3\u8f6c\u8def\u5f84","title":"url","type":"select","param":"\/pages\/index\/index=>\u5546\u57ce\u9996\u9875\n\/pages\/user_spread_user\/index=>\u4e2a\u4eba\u63a8\u5e7f\n\/pages\/user_sgin\/index=>\u6211\u8981\u7b7e\u5230\n\/pages\/user_get_coupon\/index=>\u4f18\u60e0\u5238\n\/pages\/user\/user=>\u4e2a\u4eba\u4e2d\u5fc3\n\/pages\/activity\/goods_seckill\/index=>\u79d2\u6740\u5217\u8868\n\/pages\/activity\/goods_combination\/index=>\u62fc\u56e2\u5217\u8868\u9875\n\/pages\/activity\/goods_bargain\/index=>\u780d\u4ef7\u5217\u8868\n\/pages\/goods_cate\/goods_cate=>\u5206\u7c7b\u9875\u9762\n\/pages\/user_address_list\/index=>\u5730\u5740\u5217\u8868\n\/pages\/user_cash\/index=>\u63d0\u73b0\u9875\u9762\n\/pages\/promoter-list\/index=>\u63a8\u5e7f\u7edf\u8ba1\n\/pages\/user_money\/index=>\u8d26\u6237\u91d1\u989d\n\/pages\/user_goods_collection\/index=>\u6211\u7684\u6536\u85cf\n\/pages\/promotion-card\/promotion-card=>\u63a8\u5e7f\u4e8c\u7ef4\u7801\u9875\u9762\n\/pages\/order_addcart\/order_addcart=>\u8d2d\u7269\u8f66\u9875\u9762\n\/pages\/order_list\/index=>\u8ba2\u5355\u5217\u8868\u9875\u9762\n\/pages\/news_list\/index=>\u6587\u7ae0\u5217\u8868\u9875"},{"name":"\u5e95\u90e8\u83dc\u5355","title":"show","type":"radio","param":"1=>\u662f\n2=>\u5426"},{"name":"\u516c\u4f17\u53f7\u8df3\u8f6c\u8def\u5f84","title":"wap_url","type":"select","param":"\/=>\u5546\u57ce\u9996\u9875\n\/user\/user_promotion=>\u4e2a\u4eba\u63a8\u5e7f\n\/user\/sign=>\u6211\u8981\u7b7e\u5230\n\/user\/get_coupon=>\u4f18\u60e0\u5238\n\/user=>\u4e2a\u4eba\u4e2d\u5fc3\n\/activity\/goods_seckill=>\u79d2\u6740\u5217\u8868\n\/activity\/group=>\u62fc\u56e2\u5217\u8868\u9875\n\/activity\/bargain=>\u780d\u4ef7\u5217\u8868\n\/category=>\u5206\u7c7b\u9875\u9762\n\/user\/add_manage=>\u5730\u5740\u5217\u8868\n\/user\/user_cash=>\u63d0\u73b0\u9875\u9762\n\/user\/promoter_list=>\u63a8\u5e7f\u7edf\u8ba1\n\/user\/account=>\u8d26\u6237\u91d1\u989d\n\/collection=>\u6211\u7684\u6536\u85cf\n\/user\/poster=>\u63a8\u5e7f\u4e8c\u7ef4\u7801\u9875\u9762\n\/cart=>\u8d2d\u7269\u8f66\u9875\u9762\n\/order\/list\/=>\u8ba2\u5355\u5217\u8868\u9875\u9762\n\/news_list=>\u6587\u7ae0\u5217\u8868\u9875"}]', 'tenant_id'=>$tenant_id];
        $gid=$systemGroupModel->insertGetId($system_group);
        $system_data=[
            ['gid'=>$gid, 'value'=>'{"name":{"type":"input","value":"\u5546\u54c1\u5206\u7c7b"},"pic":{"type":"upload","value":"http:\/\/datong.crmeb.net\/public\/uploads\/attach\/2019\/03\/29\/5c9ddc9f34bfd.png"},"url":{"type":"select","value":"\/pages\/goods_cate\/goods_cate"},"show":{"type":"radio","value":"1"},"wap_url":{"type":"select","value":"\/category"}}', 'add_time'=>time(), 'sort'=>1, "status" => 0,'tenant_id'=>$tenant_id],
            ['gid'=>$gid, 'value'=>'{"name":{"type":"input","value":"\u9886\u4f18\u60e0\u5238"},"pic":{"type":"upload","value":"http:\/\/datong.crmeb.net\/public\/uploads\/attach\/2019\/03\/29\/5c9ddccecb7f3.png"},"url":{"type":"select","value":"\/pages\/user_get_coupon\/index"},"show":{"type":"radio","value":"2"},"wap_url":{"type":"select","value":"\/user\/get_coupon"}}', 'add_time'=>time(), 'sort'=>1, "status" => 0,'tenant_id'=>$tenant_id],
            ['gid'=>$gid, 'value'=>'{"name":{"type":"input","value":"\u884c\u4e1a\u8d44\u8baf"},"pic":{"type":"upload","value":"http:\/\/datong.crmeb.net\/public\/uploads\/attach\/2019\/03\/29\/5c9ddcec57a80.png"},"url":{"type":"select","value":"\/pages\/news_list\/index"},"show":{"type":"radio","value":"2"},"wap_url":{"type":"select","value":"\/news_list"}}', 'add_time'=>time(), 'sort'=>1, "status" => 0,'tenant_id'=>$tenant_id],
            ['gid'=>$gid, 'value'=>'{"name":{"type":"input","value":"\u6211\u8981\u7b7e\u5230"},"pic":{"type":"upload","value":"http:\/\/datong.crmeb.net\/public\/uploads\/attach\/2019\/03\/29\/5c9ddd570b8b3.png"},"url":{"type":"select","value":"\/pages\/user_sgin\/index"},"show":{"type":"radio","value":"2"},"wap_url":{"type":"select","value":"\/user\/sign"}}', 'add_time'=>time(), 'sort'=>1, "status" => 0,'tenant_id'=>$tenant_id],
            ['gid'=>$gid, 'value'=>'{"name":{"type":"input","value":"\u6211\u7684\u6536\u85cf"},"pic":{"type":"upload","value":"http:\/\/datong.crmeb.net\/public\/uploads\/attach\/2019\/03\/29\/5c9dddce0eac9.png"},"url":{"type":"select","value":"\/pages\/user_goods_collection\/index"},"show":{"type":"radio","value":"2"},"wap_url":{"type":"select","value":"\/collection"}}', 'add_time'=>time(), 'sort'=>1, "status" => 0,'tenant_id'=>$tenant_id],
            ['gid'=>$gid, 'value'=>'{"name":{"type":"input","value":"\u62fc\u56e2\u6d3b\u52a8"},"pic":{"type":"upload","value":"http:\/\/datong.crmeb.net\/public\/uploads\/attach\/2019\/03\/29\/5c9dde013f63c.png"},"url":{"type":"select","value":"\/pages\/activity\/goods_combination\/index"},"show":{"type":"radio","value":"2"},"wap_url":{"type":"select","value":"\/activity\/group"}}', 'add_time'=>time(), 'sort'=>1, "status" => 0,'tenant_id'=>$tenant_id],
            ['gid'=>$gid, 'value'=>'{"name":{"type":"input","value":"\u79d2\u6740\u6d3b\u52a8"},"pic":{"type":"upload","value":"http:\/\/datong.crmeb.net\/public\/uploads\/attach\/2019\/03\/29\/5c9dde246ad96.png"},"url":{"type":"select","value":"\/pages\/activity\/goods_seckill\/index"},"show":{"type":"radio","value":"2"},"wap_url":{"type":"select","value":"\/activity\/goods_seckill"}}', 'add_time'=>time(), 'sort'=>1, "status" => 0,'tenant_id'=>$tenant_id],
            ['gid'=>$gid, 'value'=>'{"name":{"type":"input","value":"\u780d\u4ef7\u6d3b\u52a8"},"pic":{"type":"upload","value":"http:\/\/datong.crmeb.net\/public\/uploads\/attach\/2019\/03\/29\/5c9ddedbed782.png"},"url":{"type":"select","value":"\/pages\/activity\/goods_bargain\/index"},"show":{"type":"radio","value":"2"},"wap_url":{"type":"select","value":"\/activity\/bargain"}}', 'add_time'=>time(), 'sort'=>1, "status" => 0,'tenant_id'=>$tenant_id],
        ];
        $systemGroupDateModel->saveAll($system_data);


        //首页banner滚动图
        $system_group= ['name'=>'首页banner', 'info'=>'首页banner滚动图', 'config_name'=>'routine_home_banner', 'fields'=>'[{"name":"\u6807\u9898","title":"name","type":"input","param":""},{"name":"\u5c0f\u7a0b\u5e8f\u94fe\u63a5","title":"url","type":"input","param":""},{"name":"\u56fe\u7247(750*375)","title":"pic","type":"upload","param":""},{"name":"\u516c\u4f17\u53f7\u94fe\u63a5","title":"wap_url","type":"input","param":""}]', 'tenant_id'=>$tenant_id];
        $gid=$systemGroupModel->insertGetId($system_group);
        $system_data=[
            ['gid'=>$gid, 'value'=>'{"name":{"type":"input","value":"banenr2"},"url":{"type":"input","value":""},"pic":{"type":"upload","value":"http:\/\/qiniu.xiaohuixiang.3todo.com\/78e9f202008132215053684.jpg"}}', 'add_time'=>time(), 'sort'=>1, "status" => 0,'tenant_id'=>$tenant_id],
         ];
        $systemGroupDateModel->saveAll($system_data);


        //首页活动区域图片
        $system_group= ['name'=>'首页活动图', 'info'=>'首页活动区域图片', 'config_name'=>'routine_home_activity', 'fields'=>'[{"name":"\u56fe\u7247(260*260\/416*214)","title":"pic","type":"upload","param":""},{"name":"\u6807\u9898","title":"title","type":"input","param":""},{"name":"\u7b80\u4ecb","title":"info","type":"input","param":""},{"name":"\u5c0f\u7a0b\u5e8f\u94fe\u63a5","title":"link","type":"select","param":"\/pages\/activity\/goods_seckill\/index=>\u79d2\u6740\u5217\u8868\n\/pages\/activity\/goods_bargain\/index=>\u780d\u4ef7\u5217\u8868\n\/pages\/activity\/goods_combination\/index=>\u62fc\u56e2\u5217\u8868"},{"name":"\u516c\u4f17\u53f7\u94fe\u63a5","title":"wap_link","type":"select","param":"\/activity\/goods_seckill=>\u79d2\u6740\u5217\u8868\n\/activity\/bargain=>\u780d\u4ef7\u5217\u8868\n\/activity\/group=>\u62fc\u56e2\u5217\u8868"}]', 'tenant_id'=>$tenant_id];
        $gid=$systemGroupModel->insertGetId($system_group);
        $system_data=[
            ['gid'=>$gid, 'value'=>'{"pic":{"type":"upload","value":"http:\/\/qiniu.xiaohuixiang.3todo.com\/90223202010220945215117.png"},"title":{"type":"input","value":"\u65b0\u54c1\u9996\u53d1"},"info":{"type":"input","value":"\u9996\u9875\u65b0\u54c1\u9996\u53d1"},"wap_link":{"type":"select","value":"\/activity\/group"}}', 'add_time'=>time(), 'sort'=>1, "status" => 0,'tenant_id'=>$tenant_id],
            ['gid'=>$gid, 'value'=>'{"pic":{"type":"upload","value":"http:\/\/qiniu.xiaohuixiang.3todo.com\/d0680202010220945437577.png"},"title":{"type":"input","value":"\u63a8\u8350\u5546\u54c1"},"info":{"type":"input","value":"\u9996\u9875\u63a8\u8350\u5546\u54c1\u56fe\u7247"},"wap_link":{"type":"select","value":"\/activity\/goods_seckill"}}', 'add_time'=>time(), 'sort'=>1, "status" => 0,'tenant_id'=>$tenant_id],
            ['gid'=>$gid, 'value'=>'{"pic":{"type":"upload","value":"http:\/\/qiniu.xiaohuixiang.3todo.com\/45e17202010220946012208.png"},"title":{"type":"input","value":"\u7cbe\u54c1\u63a8\u8350"},"info":{"type":"input","value":"\u9996\u9875\u7cbe\u54c1\u63a8\u8350\u56fe\u7247"},"wap_link":{"type":"select","value":"\/activity\/bargain"}}', 'add_time'=>time(), 'sort'=>1, "status" => 0,'tenant_id'=>$tenant_id],
            ['gid'=>$gid, 'value'=>'{"pic":{"type":"upload","value":"http:\/\/qiniu.xiaohuixiang.3todo.com\/906f8202010211155248412.jpg"},"title":{"type":"input","value":"\u652f\u4ed8\u6709\u793c"},"info":{"type":"input","value":"\u652f\u4ed8\u6709\u793c"},"link":{"type":"select","value":"\/pages\/activity\/goods_seckill\/index"},"wap_link":{"type":"select","value":"\/activity\/goods_seckill"}}', 'add_time'=>time(), 'sort'=>1, "status" => 0,'tenant_id'=>$tenant_id],
            ['gid'=>$gid, 'value'=>'{"pic":{"type":"upload","value":"http:\/\/qiniu.xiaohuixiang.3todo.com\/085b4202010211155248312.jpg"},"title":{"type":"input","value":"\u9650\u65f6\u6298\u6263"},"info":{"type":"input","value":"\u9650\u65f6\u6298\u6263"},"link":{"type":"select","value":"\/pages\/activity\/goods_seckill\/index"},"wap_link":{"type":"select","value":"\/activity\/goods_seckill"}}', 'add_time'=>time(), 'sort'=>1, "status" => 0,'tenant_id'=>$tenant_id],
        ];
        $systemGroupDateModel->saveAll($system_data);


        //首页精品推荐benner图
        $system_group= ['name'=>'首页精品推荐', 'info'=>'首页精品推荐benner图', 'config_name'=>'routine_home_bast_banner', 'fields'=>'[{"name":"\u56fe\u7247","title":"img","type":"upload","param":""},{"name":"\u63cf\u8ff0","title":"comment","type":"input","param":""},{"name":"\u5c0f\u7a0b\u5e8f\u8df3\u8f6c\u94fe\u63a5","title":"link","type":"input","param":""},{"name":"\u516c\u4f17\u53f7\u8df3\u8f6c\u94fe\u63a5","title":"wap_link","type":"input","param":""}]', 'tenant_id'=>$tenant_id];
        $gid=$systemGroupModel->insertGetId($system_group);
        $system_data=[
            ['gid'=>$gid, 'value'=>'{"img":{"type":"upload","value":"http:\/\/qiniu.xiaohuixiang.3todo.com\/78e9f202008132215053684.jpg"},"comment":{"type":"input","value":"\u7cbe\u54c1\u63a8\u8350750*282"},"link":{"type":"input","value":"\/pages\/first-new-product\/index"},"wap_link":{"type":"input","value":"\/hot_new_goods\/1"}}', 'add_time'=>time(), 'sort'=>1, "status" => 0,'tenant_id'=>$tenant_id],
            ['gid'=>$gid, 'value'=>'{"img":{"type":"upload","value":"http:\/\/qiniu.xiaohuixiang.3todo.com\/78e9f202008132215053684.jpg"},"comment":{"type":"input","value":"\u7cbe\u54c1\u63a8\u8350750*282"},"link":{"type":"input","value":"\/pages\/first-new-product\/index"},"wap_link":{"type":"input","value":"\/hot_new_goods\/1"}}', 'add_time'=>time(), 'sort'=>1, "status" => 0,'tenant_id'=>$tenant_id],
        ];
        $systemGroupDateModel->saveAll($system_data);


        //热门搜索
        $system_group= ['name'=>'热门搜索', 'info'=>'热门搜索', 'config_name'=>'routine_hot_search', 'fields'=>'[{"name":"\u6807\u7b7e","title":"title","type":"input","param":""}]', 'tenant_id'=>$tenant_id];
        $systemGroupModel->insertGetId($system_group);



        //热门榜单推荐图片
        $system_group= ['name'=>'热门榜单推荐', 'info'=>'热门榜单推荐图片', 'config_name'=>'routine_home_hot_banner', 'fields'=>'[{"name":"\u56fe\u7247","title":"img","type":"upload","param":""},{"name":"\u63cf\u8ff0","title":"comment","type":"input","param":""}]', 'tenant_id'=>$tenant_id];
        $gid=$systemGroupModel->insertGetId($system_group);
        $system_data=[
            ['gid'=>$gid, 'value'=>'{"img":{"type":"upload","value":"http:\/\/qiniu.xiaohuixiang.3todo.com\/78e9f202008132215053684.jpg"},"comment":{"type":"input","value":"1"}}', 'add_time'=>time(), 'sort'=>1, "status" => 0,'tenant_id'=>$tenant_id],
            ['gid'=>$gid, 'value'=>'{"img":{"type":"upload","value":"http:\/\/qiniu.xiaohuixiang.3todo.com\/78e9f202008132215053684.jpg"},"comment":{"type":"input","value":"asd"}}', 'add_time'=>time(), 'sort'=>1, "status" => 0,'tenant_id'=>$tenant_id],
        ];
        $systemGroupDateModel->saveAll($system_data);

        //首发新品推荐图片
        $system_group= ['name'=>'首发新品推荐', 'info'=>'首发新品推荐图片', 'config_name'=>'routine_home_new_banner', 'fields'=>'[{"name":"\u56fe\u7247","title":"img","type":"upload","param":""},{"name":"\u63cf\u8ff0","title":"comment","type":"input","param":""},{"name":"\u5c0f\u7a0b\u5e8f\u8df3\u8f6c\u94fe\u63a5","title":"link","type":"input","param":""},{"name":"\u516c\u4f17\u53f7\u8df3\u8f6c\u94fe\u63a5","title":"wap_link","type":"input","param":""}]', 'tenant_id'=>$tenant_id];
        $gid=$systemGroupModel->insertGetId($system_group);
        $system_data=[
            ['gid'=>$gid, 'value'=>'{"img":{"type":"upload","value":"http:\/\/qiniu.xiaohuixiang.3todo.com\/78e9f202008132215053684.jpg"},"comment":{"type":"input","value":"1"}}', 'add_time'=>time(), 'sort'=>1, "status" => 0,'tenant_id'=>$tenant_id],
        ];
        $systemGroupDateModel->saveAll($system_data);

        //促销单品推荐图片
        $system_group= ['name'=>'促销单品推荐', 'info'=>'促销单品推荐图片', 'config_name'=>'routine_home_benefit_banner', 'fields'=>'[{"name":"\u56fe\u7247","title":"img","type":"upload","param":""},{"name":"\u63cf\u8ff0","title":"comment","type":"input","param":""},{"name":"\u5c0f\u7a0b\u5e8f\u8df3\u8f6c\u94fe\u63a5","title":"link","type":"input","param":""},{"name":"\u516c\u4f17\u53f7\u8df3\u8f6c\u94fe\u63a5","title":"wap_link","type":"input","param":""}]', 'tenant_id'=>$tenant_id];
        $gid=$systemGroupModel->insertGetId($system_group);
        $system_data=[
            ['gid'=>$gid, 'value'=>'{"img":{"type":"upload","value":"http:\/\/qiniu.xiaohuixiang.3todo.com\/78e9f202008132215053684.jpg"},"comment":{"type":"input","value":"1"}}', 'add_time'=>time(), 'sort'=>1, "status" => 0,'tenant_id'=>$tenant_id],
        ];
        $systemGroupDateModel->saveAll($system_data);

        //分享海报
        $system_group= ['name'=>'分享海报', 'info'=>'分享海报', 'config_name'=>'routine_spread_banner', 'fields'=>'[{"name":"\u540d\u79f0","title":"title","type":"input","param":""},{"name":"\u80cc\u666f\u56fe","title":"pic","type":"upload","param":""}]', 'tenant_id'=>$tenant_id];
        $gid=$systemGroupModel->insertGetId($system_group);
        $system_data=[
            ['gid'=>$gid, 'value'=>'{"title":{"type":"input","value":"1"},"pic":{"type":"upload","value":"http:\/\/kaifa.crmeb.net\/uploads\/attach\/2019\/08\/20190810\/623a4c225738606e4c65f93217050c86.jpg"}}', 'add_time'=>time(), 'sort'=>1, "status" => 0,'tenant_id'=>$tenant_id],
            ['gid'=>$gid, 'value'=>'{"title":{"type":"input","value":"2"},"pic":{"type":"upload","value":"http:\/\/kaifa.crmeb.net\/uploads\/attach\/2019\/08\/20190810\/93669bff568cf8eb967670d9cd3ca78c.jpg"}}', 'add_time'=>time(), 'sort'=>1, "status" => 0,'tenant_id'=>$tenant_id],
        ];
        $systemGroupDateModel->saveAll($system_data);


        //商城的介绍
        $article=['cid'=>1,'title'=>'商城介绍','author'=>'','image_input'=>'',"status" => 0,'add_time'=>time(),'admin_id'=>$tenant_id,'tenant_id'=>$tenant_id];
        Article::create($article);


        return true;

    }

    public function create_init_by_hand(){
//        $tenant_id=36;
//        //商城的介绍
//        $article=['cid'=>1,'title'=>'商城介绍','author'=>'','image_input'=>'',"status" => 0,'add_time'=>time(),'admin_id'=>$tenant_id,'tenant_id'=>$tenant_id];
//        Article::create($article);
    }

   

}
