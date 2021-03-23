<?php
/**
 * AutoTask.php
 * desc:
 * created on  2020/9/19 4:08 PM
 * Created by caogu
 */

namespace app\api\controller;


use app\admin\model\activity\Activity;
use app\admin\model\user\UserTag;
use app\admin\model\user\UserTagCity;
use app\admin\model\user\UserTagLog;
use app\admin\model\user\UserTagProduct;
use app\models\store\StoreCart;
use app\models\store\StoreOrder;
use app\models\system\SystemCity;
use app\models\user\User;
use app\models\user\UserMessage;
use app\models\user\WechatUser;

class AutoTask
{

    /**
     * 创建活动提前通知的功能
     */
    public function set_activity_message(){
        $activity_list=Activity::where('sended_msg','=',0)
            ->field('id,title,start_time,end_time,before_hour,tenant_id,before_msg')
            ->where('end_time','>',time())//已结束的任务不在发送范围内
            ->where('tenant_id','>',0)
            ->select();
        if(!$activity_list){
            die ('no_activity');
        }
        $activity_list=$activity_list->toArray();
        $time=time();
        foreach($activity_list as $value){
            //$value为每一条活动信息
            $tenant_id=$value['tenant_id'];

            //判断活动的时间是否在预定通知时间内
            $send_time=$value['start_time']-$value['before_hour']*3600;

            //发送时间在当前时间以后超过10分钟，则不发送
            if($send_time>time()+600){
                continue;
            }


            //获取该平台方下的所有的用户
            $uids=User::where('tenant_id','=',$tenant_id)->field('uid')->select();
            if(!$uids){
                continue;
            }


            $uids=$uids->column('uid');
            $msg_data=[];

            for($i=0;$i<count($uids);$i++){
                $content=$value['before_msg']?$value['before_msg']:'活动预告:【'.$value['title'].'】将于'.date('m月d日H点i分').'开始';
                $temp=[
                    'uid'=>$uids[$i],
                    'add_time'=>$time,
                    'title'=>$value['title'],
                    'content'=>$content
                ];
                $msg_data[]=$temp;
            }

            $res=(new UserMessage())->saveAll($msg_data);
            if($res){
                //更新活动的已发送状态
                Activity::where('id','=',$value['id'])->update(['sended_msg'=>1]);
            }
        }
        die ('success');
    }

    //给用户自动打标签的方法
    public function set_user_tag(){
        //获取系统中所有的"自动标签"
        $tag_list=UserTag::where('is_auto','=',1)
            ->where('status','=',1)
            ->select();
        if(!count($tag_list)){
            echo '无有效自动标签';
            exit;
        }
        $tag_list=$tag_list->toArray();
        foreach($tag_list as $val){
            //type 1 满足任意1个条件即可。2必须满足所有条件。
            $type=$val['type'];
            $user_arr=[];
            $type_2_user_arr=User::getAllUserIds($val['tenant_id']);
            $tenant_id=$val['tenant_id'];


            //最后消费时间的用户
            //最后消费时间类型  0不启用 1最近多少天 2指定日期
            if($val['pay_time_type']){
                $pay_time_type=$val['pay_time_type'];
                $start_time=0;
                $end_time=0;
                if($pay_time_type==1){
                    //消费的开始和结束时间以天数来推算
                    $last_day=$val['last_day'];
                    $start_time=time()-$last_day*86400;
                    $end_time=time();
                }elseif ($pay_time_type==2){
                    //消费的开始和结束时间，以用户设置的时间来计算
                    $start_time=$val['pay_start_time'];
                    $end_time=$val['pay_end_time'];
                }

                $tem_user_arr=StoreOrder::where('pay_time','>',$start_time)
                    ->where('pay_time','<',$end_time)
                    ->where('tenant_id','=',$tenant_id)
                    ->group('uid')
                    ->field('uid')
                    ->select();
                if(count($tem_user_arr)>0){
                    $tem_user_arr=$tem_user_arr->column('uid');
                    //满足任意条件，使用数组合并
                    $user_arr=array_merge($user_arr,$tem_user_arr);

                    //满足所有条件，使用数组交集
                    $type_2_user_arr=array_intersect($type_2_user_arr,$tem_user_arr);
                }
            }



            //累积消费次数
            if($val['is_pay_num_type']){
                //消费次数下限
                $pay_num_lower=$val['pay_num_lower'];
                //消费次数上限
                $pay_num_upper=$val['pay_num_upper'];
                $tem_user_arr=StoreOrder::field('uid,count(*) as pay_count')->
                where('pay_time','>',0)
                    ->where('tenant_id','=',$tenant_id)
                    ->group('uid')
                    ->select();
                if(count($tem_user_arr)>0){
                    $tem_user_arr=$tem_user_arr->toArray();
                    foreach($tem_user_arr as $v){
                        if($v['pay_count']<$pay_num_lower || $v['pay_count']>$pay_num_upper){
                            continue;
                        }
                        $user_arr[]=$v['uid'];
                    }

                }
            }


            //累积消费金额
            if($val['is_pay_money_type']){
                //消费金额下限
                $pay_money_lower=$val['pay_money_lower'];
                //消费金额上限
                $pay_money_upper=$val['pay_money_upper'];
                $tem_user_arr=StoreOrder::field('uid,sum(total_price) as pay_money')->
                where('pay_time','>',0)
                    ->where('tenant_id','=',$tenant_id)
                    ->group('uid')
                    ->select();
                if(count($tem_user_arr)>0){
                    $tem_user_arr=$tem_user_arr->toArray();
                    foreach($tem_user_arr as $v){
                        if($v['pay_money']<$pay_money_lower || $v['pay_money']>$pay_money_upper){
                            continue;
                        }
                        $user_arr[]=$v['uid'];
                    }

                }
            }

            //客单价-采用订单金额平均值
            if($val['is_per_price']){
                //消费金额下限
                $per_price_lower=$val['per_price_lower'];
                //消费金额上限
                $per_price_upper=$val['per_price_upper'];
                $tem_user_arr=StoreOrder::field('uid,avg(total_price) as pay_avg_money')->
                where('pay_time','>',0)
                    ->where('tenant_id','=',$tenant_id)
                    ->group('uid')
                    ->select();
                if(count($tem_user_arr)>0){
                    $tem_user_arr=$tem_user_arr->toArray();
                    foreach($tem_user_arr as $v){
                        if($v['pay_avg_money']<$per_price_lower || $v['pay_avg_money']>$per_price_upper){
                            continue;
                        }
                        $user_arr[]=$v['uid'];
                    }

                }
            }

            //购买以下任意商品
            if($val['is_product_type']){
                $product_list=(new UserTagProduct())->where('user_tag_id','=',$val['id'])->field('product_id')->select();
                if(count($product_list)>0){
                    $product_ids_arr=$product_list->column('product_id');
                    $tem_user_arr=StoreCart::where('product_id','in',$product_ids_arr)
                        ->field('uid')
                        ->where('is_pay','=',1)
                        ->group('uid')
                        ->select();
                    if(count($tem_user_arr)>0){
                        $tem_user_arr=$tem_user_arr->column('uid');
                        //满足任意条件，使用数组合并
                        $user_arr=array_merge($user_arr,$tem_user_arr);

                        //满足所有条件，使用数组交集
                        $type_2_user_arr=array_intersect($type_2_user_arr,$tem_user_arr);
                    }
                }
            }


            //最近访问时间
            if($val['last_view_day']){
                $start_time=time()-$val['last_view_day']*86400;
                $tem_user_arr=User::where('last_time','>',$start_time)->field('uid')->select();
                if(count($tem_user_arr)>0){
                    $tem_user_arr=$tem_user_arr->column('uid');
                    //满足任意条件，使用数组合并
                    $user_arr=array_merge($user_arr,$tem_user_arr);

                    //满足所有条件，使用数组交集
                    $type_2_user_arr=array_intersect($type_2_user_arr,$tem_user_arr);
                }

            }

            //地区筛选
            if($val['is_city_type']){
                //查找标签关联的地区id
                $city_ids=(new UserTagCity())->where('user_tag_id','=',$val['id'])->field('system_city_id')->select();
                if(count($city_ids)>0){
                    $system_city_id=$city_ids->column('system_city_id');
                    //获取地区对应的名称
                    $city_name_arr=(new SystemCity())->where('id','in',$system_city_id)->field('name')->select();
                    if(count($city_name_arr)>0){
                        $city_name_arr=$city_name_arr->column('name');
                        //去除省市区，并匹配用户表中的city
                        $city_name_str=implode(',',$city_name_arr);
                        $city_name_str=str_replace('省','',$city_name_str);
                        $city_name_str=str_replace('市','',$city_name_str);
                        $city_name_str=str_replace('区','',$city_name_str);
                        $tem_user_arr=WechatUser::where('city','in',$city_name_str)->field('uid')->select();
                        if(count($tem_user_arr)>0){
                            $tem_user_arr=$tem_user_arr->column('uid');
                            //满足任意条件，使用数组合并
                            $user_arr=array_merge($user_arr,$tem_user_arr);

                            //满足所有条件，使用数组交集
                            $type_2_user_arr=array_intersect($type_2_user_arr,$tem_user_arr);
                        }
                    }
                }
            }

            //性别限制
            if($val['sex']){
                $tem_user_arr=WechatUser::where('sex','=',$val['sex'])->field('uid')->select();
                if(count($tem_user_arr)>0){
                    $tem_user_arr=$tem_user_arr->column('uid');
                    //满足任意条件，使用数组合并
                    $user_arr=array_merge($user_arr,$tem_user_arr);

                    //满足所有条件，使用数组交集
                    $type_2_user_arr=array_intersect($type_2_user_arr,$tem_user_arr);
                }
            }



            //为数据处理入库


            if($type==1){
                //满足任意条件的逻辑
                //过滤掉重复的用户
                $user_arr=array_unique($user_arr);
                //重建索引-这里的用户是满足当前标签下任意条件的用户
                $user_arr=array_column($user_arr,null);

            }else{
                //满足所有条件的逻辑
                $type_2_user_arr=array_column($type_2_user_arr,null);
                //重建索引-这里的用户是满足当前标签下任意条件的用户
                $type_2_user_arr=array_column($type_2_user_arr,null);
                if(!$type_2_user_arr){
                    exit;
                }
                $user_arr=$type_2_user_arr;
            }


            //获取当前标签下已经拥有该标签的用户
            $have_this_tag_user_ids=UserTagLog::where('tag_id','=',$val['id'])->field('uid')->select();
            if(count($have_this_tag_user_ids)>0){
                //获取差集用户
                $have_this_tag_user_ids=$have_this_tag_user_ids->column('uid');
                $intersection = array_diff($user_arr, $have_this_tag_user_ids);
                $intersection=array_column($intersection,null);
            }else{
                $intersection=$user_arr;
            }
            if(count($intersection)>0){
                //存入用户标签库
                $data=[];
                foreach($intersection as $v){
                    $data[]=[
                        'uid'=>$v,
                        'tag_id'=>$val['id']
                    ];
                }
                (new UserTagLog())->insertAll($data);
            }


        }
    }
}