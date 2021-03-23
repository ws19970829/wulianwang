<?php

namespace crmeb\repositories;


use app\admin\model\sms\SmsRecord;
use app\admin\model\system\SystemConfig;
use crmeb\services\sms\Sms;
use think\facade\Log;

/**
 * 短信发送
 * Class ShortLetterRepositories
 * @package crmeb\repositories
 */
class ShortLetterRepositories
{
    /**
     * 发送短信
     * @param $switch 发送开关
     * @param $phone 手机号码
     * @param array $data 模板替换内容
     * @param string $template 模板编号
     * @param string $logMsg 错误日志记录
     * @param int $tenant_id
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function send($switch, $phone, array $data, string $template, $logMsg = '',$tenant_id=0)
    {

        if ($switch && $phone) {
            //添加公共短信模板
            $sms_account=SystemConfig::where('tenant_id','=',$tenant_id)
                ->where('menu_name','=','sms_account')
                ->value('value');
            $sms_token=SystemConfig::where('tenant_id','=',$tenant_id)
                ->where('menu_name','=','sms_token')
                ->value('value');
            $site_url=SystemConfig::where('tenant_id','=',$tenant_id)
                ->where('menu_name','=','site_url')
                ->value('value');
            if ($site_url === '' || $site_url === false) {
                $site_url='';
            }


            $sms = new Sms([
                'sms_account' =>trim($sms_account,"\""),
                'sms_token' => trim($sms_token,"\""),
                'site_url' => trim($site_url,"\""),
            ]);
            $res = $sms->send($phone, $template, $data);
            if ($res === false) {
                $errorSmg = $sms->getError();
                Log::info($logMsg ?? $errorSmg);
                return $errorSmg;
            } else {
                SmsRecord::sendRecord($phone, $res['data']['content'], $res['data']['template'], $res['data']['id'],$tenant_id);
            }
            return true;
        } else {
            return false;
        }
    }
}