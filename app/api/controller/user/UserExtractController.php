<?php

namespace app\api\controller\user;

use app\admin\model\system\SystemConfig;
use app\api\controller\PublicController;
use app\models\store\StoreOrder;
use app\models\user\User;
use app\models\user\UserBill;
use app\models\user\UserExtract;
use app\Request;
use crmeb\services\UtilService;

/**
 * 提现类
 * Class UserExtractController
 * @package app\api\controller\user
 */
class UserExtractController
{
    /**
     * 提现银行
     * @param Request $request
     * @return mixed
     */
    public function bank(Request $request)
    {
        $user = $request->user();
        $broken_time = intval(sys_config('extract_time'));
        $search_time = time() - 86400 * $broken_time;
        //可提现佣金
        //返佣 +
        $brokerage_commission = UserBill::where(['uid' => $user['uid'], 'category' => 'now_money', 'type' => 'brokerage'])
            ->where('add_time', '>', $search_time)
            ->where('pm', 1)
            ->sum('number');
        //退款退的佣金 -
        $refund_commission = UserBill::where(['uid' => $user['uid'], 'category' => 'now_money', 'type' => 'brokerage'])
            ->where('add_time', '>', $search_time)
            ->where('pm', 0)
            ->sum('number');
        $data['broken_commission'] = bcsub($brokerage_commission, $refund_commission, 2);
        if ($data['broken_commission'] < 0)
            $data['broken_commission'] = 0;
//        return $data;
        $data['brokerage_price'] = $user['brokerage_price'];
        //可提现佣金
        $data['commissionCount'] = $data['brokerage_price'] - $data['broken_commission'];

        //可提现佣金 为余额
        $data['commissionCount'] = $user['now_money'];

        $tenant_id=User::getTenantIDbyUID($user['uid']);


//        $extractBank = sys_config('user_extract_bank') ?? []; //提现银行
        $extractBank =(new PublicController())->getSysConfigValue('user_extract_bank',$tenant_id); //提现银行

        if($extractBank){

            $extractBank = str_replace("\r\n", "\n", $extractBank);//防止不兼容
            $extractBank='["\\'.$extractBank.'"]';


            $extractBank=json_decode($extractBank,true);
            $str=isset($extractBank[0])?$extractBank[0]:'';
            $arr=explode(PHP_EOL,$str);
//            $data['extractBank'] = explode("\n", is_array($arr) ? (isset($extractBank[0]) ? $extractBank[0] : $extractBank) : $extractBank);
            $data['extractBank'] = $arr;

        }else{
            $data['extractBank']=[];
        }


//        $data['minPrice'] = sys_config('user_extract_min_price');//提现最低金额
        $data['minPrice'] = (new PublicController())->getSysConfigValue('user_extract_min_price',$tenant_id); //提现银行
        return app('json')->successful($data);
    }


    /**
     * 提现申请
     * @param Request $request
     * @return mixed
     */
    public function cash(Request $request)
    {
        $extractInfo = UtilService::postMore([
            ['alipay_code', ''],
            ['extract_type', ''],
            ['money', 0],
            ['name', ''],
            ['bankname', ''],
            ['cardnum', ''],
            ['weixin', ''],
        ], $request);
        if (!preg_match('/^(([1-9][0-9]*)|(([0]\.\d{1,2}|[1-9][0-9]*\.\d{1,2})))$/', $extractInfo['money'])) return app('json')->fail('提现金额输入有误');
        //提现设置最低金额
        if($extractInfo['money'] < sys_config('user_extract_min_price')) return app('json')->fail('金额小于最低提现金额');
        $user = $request->user();
        $broken_time = intval(sys_config('extract_time'));
        $search_time = time() - 86400 * $broken_time;
        //可提现佣金
        //返佣 +
        $brokerage_commission = UserBill::where(['uid' => $user['uid'], 'category' => 'now_money', 'type' => 'brokerage'])
            ->where('add_time', '>', $search_time)
            ->where('pm', 1)
            ->sum('number');
        //退款退的佣金 -
        $refund_commission = UserBill::where(['uid' => $user['uid'], 'category' => 'now_money', 'type' => 'brokerage'])
            ->where('add_time', '>', $search_time)
            ->where('pm', 0)
            ->sum('number');
        $data['broken_commission'] = bcsub($brokerage_commission, $refund_commission, 2);
        if ($data['broken_commission'] < 0)
            $data['broken_commission'] = 0;
        $data['brokerage_price'] = $user['brokerage_price'];
        //可提现佣金
        $commissionCount = $data['brokerage_price'] - $data['broken_commission'];
        if ($extractInfo['money'] > $commissionCount) return app('json')->fail('可提现佣金不足');
        if (!$extractInfo['cardnum'] == '')
            if (!preg_match('/^([1-9]{1})(\d{14}|\d{18})$/', $extractInfo['cardnum']))
                return app('json')->fail('银行卡号输入有误');
        if (UserExtract::userExtract($request->user(), $extractInfo))
            return app('json')->successful('申请提现成功!');
        else
            return app('json')->fail(UserExtract::getErrorInfo('提现失败'));
    }
}