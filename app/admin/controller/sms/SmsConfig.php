<?php

namespace app\admin\controller\sms;

use app\admin\controller\AuthController;
use app\admin\model\system\SystemConfig;
use think\facade\Route;
use app\admin\model\system\SystemConfig as ConfigModel;
use crmeb\services\{FormBuilder, sms\Sms, SystemConfigService, UtilService, CacheService};

/**
 * 短信配置
 * Class SmsConfig
 * @package app\admin\controller\sms
 */
class SmsConfig extends AuthController
{
    /**
     * @var Sms
     */
    protected $smsHandle;

    protected function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub

    }

    /**
     * 展示配置
     * @return string
     * @throws \FormBuilder\exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        [$type, $tab_id] = UtilService::getMore([
            ['type', 0],
            ['tab_id', 0]
        ], null, true);

        if (!$tab_id) $tab_id = 1;
        $this->assign('tab_id', $tab_id);
        $list = ConfigModel::getAll($tab_id);
//        dump($list->toArray());
        if ($type == 3) {//其它分类
            $config_tab = null;
        } else {
            $config_tab = ConfigModel::getConfigTabAll($type);
            foreach ($config_tab as $kk => $vv) {
                $arr = ConfigModel::getAll($vv['value'])->toArray();
                if (empty($arr)) {
                    unset($config_tab[$kk]);
                }
            }
        }
        $formBuilder = ConfigModel::builder_config_from_data($list);
        $form = FormBuilder::make_post_form('编辑配置', $formBuilder, Route::buildUrl('save_basics'));
        $this->assign(compact('form'));
        $this->assign('config_tab', $config_tab);
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 保存配置
     */
    public function save_basics()
    {
        $request = app('request');
        if ($request->isPost()) {
            CacheService::clear();
            $post = $request->post();
            foreach ($post as $k => $v) {
                if (is_array($v)) {
                    $res = ConfigModel::where('menu_name', $k)
                        ->where('tenant_id','=',session('tenant_id'))
                        ->column('upload_type', 'type');
                    foreach ($res as $kk => $vv) {
                        if ($kk == 'upload') {
                            if ($vv == 1 || $vv == 3) {
                                $post[$k] = $v[0];
                            }
                        }
                    }
                }
            }
            foreach ($post as $k => $v) {
                $where=[
                    'tenant_id'=>session('tenant_id'),
                    'menu_name'=>$k,
                ];

//                ConfigModel::edit_by_tenant_id(['value' => json_encode($v)], $k, 'menu_name');
                ConfigModel::where($where)->update(['value'=>json_encode($v)]);
            }

            //添加公共短信模板
//            $this->smsHandle = new Sms('yunxin', [
//                'sms_account' => SystemConfigService::get('sms_account','',false),
//                'sms_token' => SystemConfigService::get('sms_token','',false),
//                'site_url' => sys_config('site_url')
//            ]);

            //添加公共短信模板
            $sms_account=SystemConfig::where('tenant_id','=',session('tenant_id'))
                ->where('menu_name','=','sms_account')
                ->value('value');
            $sms_token=SystemConfig::where('tenant_id','=',session('tenant_id'))
                ->where('menu_name','=','sms_token')
                ->value('value');
            $site_url=SystemConfig::where('tenant_id','=',session('tenant_id'))
                ->where('menu_name','=','site_url')
                ->value('value');
            if ($site_url === '' || $site_url === false) {
                $site_url='';
            }

            $this->smsHandle = new Sms('yunxin', [
                'sms_account' => trim($sms_account,"\""),
                'sms_token' => trim($sms_token,"\""),
                'site_url' => trim($site_url,"\""),
            ]);

            $templateList = $this->smsHandle->publictemp([]);
            if ($templateList['status'] != 400){
                if ($templateList['data']['data'])
                    foreach ($templateList['data']['data'] as $v) {
                        if ($v['is_have'] == 0)
                            $this->smsHandle->use($v['id'], $v['templateid']);
                    }

                return $this->successful('修改成功');
            }else{
                return $this->failed($templateList['msg']);
            }
        }
    }

    /**
     * 退出
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function logout()
    {
        $post = [
            'sms_account' => '',
            'sms_token' => ''
        ];
        foreach ($post as $k => $v) {
            if (is_array($v)) {
                $res = ConfigModel::where('menu_name', $k)
                    ->where('tenant_id','=',session('tenant_id'))
                    ->column('upload_type', 'type');
                foreach ($res as $kk => $vv) {
                    if ($kk == 'upload') {
                        if ($vv == 1 || $vv == 3) {
                            $post[$k] = $v[0];
                        }
                    }
                }
            }
        }
        foreach ($post as $k => $v) {
            $where=[
                'tenant_id'=>session('tenant_id'),
                'menu_name'=>$k,
            ];
            ConfigModel::where($where)
                ->update(['value' => json_encode($v)]);
        }
        CacheService::clear();
        return redirect(url('sms.smsConfig/index') . '?type=4&tab_id=18');
    }
}