<?php

namespace app\api\controller;

use app\admin\model\fans\FansPlan;
use app\admin\model\fans\FansPlanUser;
use app\models\store\StoreOrder;
use app\models\user\User;
use app\models\user\WechatUser;
use app\Request;


/**
 * 粉丝营销
 * Class PublicController
 * @package app\api\controller
 */
class FansController
{
    /**
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index(Request $request)
    {
        dump(input('param.'));
    }


    /**
     * 获取营销笔记的推送内容
     * @param string $openid
     * @return array|string
     */
    public function get_staff_plan_info($openid=''){
        if(!$openid){
            return [
                'code'=>0,
                'msg'=>'openid不能为空',
                'data'=>[]
            ];
        }

        //获取用户的tenant_id
        $uid=WechatUser::openidTouid($openid);
//        mylog('营销计划',$uid);

        $tenant_id=User::getTenantIDbyUID($uid);
//        mylog('营销计划',$tenant_id);

        if(!$tenant_id) {
            return [
                'code'=>0,
                'msg'=>'用户tenant_id不存在',
                'data'=>[]
            ];
        }

        //获取平台方当前生效的营销计划
        $where=[
            'tenant_id'=>$tenant_id,
            'status'=>1,
            'is_del'=>0
        ];
        $plan_list=(new FansPlan())->where($where)->with('withNote')->select();
        if(!count($plan_list)){
            return [
                'code'=>0,
                'msg'=>'该商家不存在营销计划',
                'data'=>[]
            ];
        }

        $plan_list=$plan_list->toArray();

//        mylog('营销计划',$plan_list);

        //TODO：判断当前用户已经推过的营销 不再推了
        //获取该用户已经接受过的营销计划
        $sended_plan_list=FansPlanUser::where('uid','=',$uid)->field('fans_plan_id')->select();

        $fans_plan_id_arr=[];
        if(count($sended_plan_list)){
            $fans_plan_id_arr=$sended_plan_list->column('fans_plan_id');
        }


        //对营销计划进行过滤，去掉过期的\未开始\已推送过的营销计划
        $data=[];
        foreach($plan_list as $val){


            //不是长期计划的，判断是否已经过期，或者未到生效时间
            if(!$val['is_long']){
                //如果没有设置起止时间，则跳过
                if(!$val['start_time'] || !$val['end_time']){
                    continue;
                }
                //未开始的跳过
                if(time()<$val['start_time']){
                    continue;
                }
                //已过期的 跳过
                if(time()>$val['end_time']){
                    continue;
                }
            }


            //没有关联到笔记的，跳过
            if(!isset($val['withNote'])){
                continue;
            }

            //已经推送过的，跳过
            if($fans_plan_id_arr){
                if(in_array($val['id'],$fans_plan_id_arr)){
                    continue;
                }
            }

            //类型为"未消费新增人群"(type=3)的，如果用户消费过，就跳过
            if($val['type']==3){
                $pay_res=(new StoreOrder())
                    ->where('pay_time','>',0)
                    ->where('uid','=',$uid)
                    ->count();
                if($pay_res){
                    continue;
                }
            }


            //TODO：判断用户的标签，不符合的不再推



            $data[]=$val;
        }


        //【重要】，没有匹配到合适的营销计划，则返回空
        if(!count($data)){
            return [
                'code'=>0,
                'msg'=>'当前无有效营销计划',
                'data'=>[]
            ];
        }


        //过滤完毕，当前plan_list中的内容都是有效的营销计划
        $plan_list=$data;
        $data=[];
        foreach($plan_list as $val){
            $tem=[
                'title'=>$val['withNote']['title'],
                'description'=>$val['withNote']['synopsis'],//摘要描述
                'image'=>$val['withNote']['image'],
                'url'=>config('site.fans_plan_url').'?id='.$val['withNote']['id'].'&u='.$uid,
                'uid'=>$uid,
                'fans_plan_id'=>$val['id'],
                'fans_note_id'=>$val['note_id'],
                'tenant_id'=>$val['tenant_id'],
                'send_date'=>date('Y-m-d',time())
            ];
            $data[]=$tem;
        }


        return [
            'code'=>1,
            'msg'=>'获取成功',
            'data'=>$data
        ];
    }




}