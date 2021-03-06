<?php

namespace app\admin\controller\sms;

use app\admin\controller\AuthController;
use app\admin\model\system\SystemConfig;
use crmeb\services\JsonService;
use app\admin\model\sms\SmsRecord as SmsRecordModel;
use crmeb\services\sms\Sms;
use crmeb\services\UtilService;

/**
 * 短息发送日志
 * Class SmsLog
 * @package app\admin\controller\sms
 */
class SmsRecord extends AuthController
{
    /**
     * @var Sms
     */
    protected $smsHandle;

    protected function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
//        $this->smsHandle = new Sms('yunxin', [
//            'sms_account' => sys_config('sms_account'),
//            'sms_token' => sys_config('sms_token'),
//            'site_url' => sys_config('site_url')
//        ]);
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
            'sms_account' =>trim($sms_account,"\""),
            'sms_token' => trim($sms_token,"\""),
            'site_url' => trim($site_url,"\""),
        ]);
    }

    /**
     * 短信记录页面
     * @return string
     */
    public function index()
    {
        if (!$this->smsHandle->isLogin()) return redirect(url('sms.smsConfig/index').'?type=4&tab_id=18');
        return $this->fetch();
    }

    /**
     * 获取短信记录列表
     */
    public function recordList()
    {
        $where = UtilService::getMore([
            ['page', 1],
            ['limit', 20],
            ['type', ''],
            ['uid', ''],
            ['phone', ''],
        ]);
        return JsonService::successlayui(SmsRecordModel::getRecordList($where));
    }
}