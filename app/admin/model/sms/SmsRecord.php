<?php

namespace app\admin\model\sms;

use app\admin\model\system\SystemConfig;
use crmeb\basic\BaseModel;
use crmeb\services\sms\Sms;

/**
 * @mixin think\Model
 */
class SmsRecord extends BaseModel
{

    /**
     * 短信状态
     * @var array
     */
    protected static $resultcode = ['100' => '成功', '130' => '失败', '131' => '空号', '132' => '停机', '133' => '关机', '134' => '无状态'];

    protected function getAddTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    public static function vaildWhere($where)
    {
        $model = new static();
        if ($where['type']) $model = $model->where('resultcode', $where['type']);
        return $model;
    }

    /**
     * 获取短信记录列表
     * @param $where
     * @return array
     */
    public static function getRecordList($where)
    {
        $data = self::vaildWhere($where)
            ->where('tenant_id','=',session('tenant_id'))
            ->page((int)$where['page'], (int)$where['limit'])->select();
        $recordIds = [];
        foreach ($data as $k => $item) {
            if (!$item['resultcode']) {
                $recordIds[] = $item['record_id'];
            } else {
                $data[$k]['_resultcode'] = self::$resultcode[$item['resultcode']] ?? '无状态';
            }
        }
        unset($item);
        if (count($recordIds)) {

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

//            $smsHandle = new Sms('yunxin', [
//                'sms_account' => sys_config('sms_account'),
//                'sms_token' => sys_config('sms_token'),
//                'site_url' => sys_config('site_url')
//            ]);

            $smsHandle = new Sms('yunxin', [
                'sms_account' =>trim($sms_account,"\""),
                'sms_token' => trim($sms_token,"\""),
                'site_url' => trim($site_url,"\""),
            ]);

            $codeLists = $smsHandle->getStatus($recordIds);
            if ($codeLists && isset($codeLists['status']) && $codeLists['status'] == 200 && isset($codeLists['data']) && is_array($codeLists['data'])) {
                foreach ($codeLists['data'] as $item) {
                    if (isset($item['id']) && isset($item['resultcode'])) {
                        self::where('record_id', $item['id'])->update(['resultcode' => $item['resultcode']]);
                        foreach ($data as $key => $value) {
                            if ($item['id'] == $value['record_id']) {
                                $data[$key]['_resultcode'] = $item['_resultcode'];
                            }
                        }
                    }
                }
            }
        }
        $count = self::vaildWhere($where)->count();
        return compact('count', 'data');
    }

    /**
     * 发送记录
     * @param $phone
     * @param $content
     * @param $template
     * @param $record_id
     * @param $tenant_id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function sendRecord($phone, $content, $template, $record_id,$tenant_id=0)
    {
        $map = [
            'uid' => sys_config('sms_account'),
            'phone' => $phone,
            'content' => $content,
            'add_time' => time(),
            'template' => $template,
            'record_id' => $record_id,
            'add_ip' => app()->request->ip(),
            'tenant_id'=>$tenant_id
        ];
        $msg = SmsRecord::create($map);
        if ($msg)
            return true;
        else
            return false;
    }
}
