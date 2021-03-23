<?php


namespace app\admin\model\fans;
use app\admin\model\order\StoreOrder;
use crmeb\basic\BaseModel;
use app\admin\model\store\StoreProduct;

/**
 * 营销计划推送记录表
 * Class StoreOrder
 * @package app\admin\model\store
 */
class FansPlanUser extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'fans_plan_user';

    protected function getAddTimeAttr($val){
        return $val?date('Y-m-d',$val):'';
    }


    /** 获取器 - 通知到的人数 */
    protected function getNoticeUserNumAttr($val,$data){
        //通知到的人数
        $notice_user_num=FansPlanUser::where('tenant_id','=',session('tenant_id'))
            ->where('send_date','=',$data['send_date'])
            ->group('uid')
            ->count();
        return $notice_user_num;
    }

    /** 获取器 - 访客人数 */
    protected function getReadUserNumAttr($val,$data){
        $start_time=strtotime($data['send_date']);
        $end_time=$start_time+86399;

        $read_user_num=FansNoteReadLog::where('tenant_id','=',session('tenant_id'))
            ->group('uid')
            ->where('add_time','>=',$start_time)
            ->where('add_time','<=',$end_time)
            ->count();
        return $read_user_num;
    }

    /** 获取器 - 下单人数 */
    protected function getOrderUserNumAttr($val,$data){
        $start_time=strtotime($data['send_date']);
        $end_time=$start_time+86399;

        $order_user_num=StoreOrder::where('tenant_id','=',session('tenant_id'))
            ->where('fans_note_id','>',0)//只取通过笔记下单的订单
            ->group('uid')
            ->where('add_time','>=',$start_time)
            ->where('add_time','<=',$end_time)
            ->count();
        return $order_user_num;
    }

    /** 获取器 - 下单笔数 */
    protected function getOrderNumAttr($val,$data){
        $start_time=strtotime($data['send_date']);
        $end_time=$start_time+86399;

        $order_num=StoreOrder::where('tenant_id','=',session('tenant_id'))
            ->where('fans_note_id','>',0)//只取通过笔记下单的订单
            ->where('add_time','>=',$start_time)
            ->where('add_time','<=',$end_time)
            ->count();
        return $order_num;
    }

    /** 获取器 - 下单金额 */
    protected function getOrderMoneyAttr($val,$data){
        $start_time=strtotime($data['send_date']);
        $end_time=$start_time+86399;
        $order_money=StoreOrder::where('tenant_id','=',session('tenant_id'))
            ->where('fans_note_id','>',0)//只取通过笔记下单的订单
            ->where('add_time','>=',$start_time)
            ->where('add_time','<=',$end_time)
            ->sum('total_price');
        return number_format($order_money,2);
    }

    /** 获取器 - 付款人数 */
    protected function getPayUserNumAttr($val,$data){
        $start_time=strtotime($data['send_date']);
        $end_time=$start_time+86399;
        $pay_user_num=StoreOrder::where('tenant_id','=',session('tenant_id'))
            ->where('fans_note_id','>',0)//只取通过笔记下单的订单
            ->where('add_time','>=',$start_time)
            ->where('add_time','<=',$end_time)
            ->where('pay_time','>',0)
            ->group('uid')
            ->count();
        return $pay_user_num;
    }

    /** 获取器 - 付款订单数 */
    protected function getPayOrderNumAttr($val,$data){
        $start_time=strtotime($data['send_date']);
        $end_time=$start_time+86399;
        $pay_order_num=StoreOrder::where('tenant_id','=',session('tenant_id'))
            ->where('fans_note_id','>',0)//只取通过笔记下单的订单
            ->where('add_time','>=',$start_time)
            ->where('add_time','<=',$end_time)
            ->where('pay_time','>',0)
            ->count();
        return $pay_order_num;
    }

    /** 获取器 - 付款金额 */
    protected function getPayMoneyAttr($val,$data){
        $start_time=strtotime($data['send_date']);
        $end_time=$start_time+86399;
        $pay_money=StoreOrder::where('tenant_id','=',session('tenant_id'))
            ->where('fans_note_id','>',0)//只取通过笔记下单的订单
            ->where('add_time','>=',$start_time)
            ->where('add_time','<=',$end_time)
            ->where('pay_time','>',0)
            ->sum('total_price');
        return number_format($pay_money,2);
    }


    //获取营销计划的统计数据
    public static function getDataList($where){
        $data = self::systemPage($where,true)->page((int)$where['page'], (int)$where['limit'])->select();
        if(!count($data)){
            $data=[];
        }else{
            $data->append(['notice_user_num','read_user_num','order_user_num','order_num','order_money','pay_user_num','pay_order_num','pay_money']);
            $data=$data->toArray();
            //处理三种比例的计算

            foreach($data as $key=>$val){

                $data[$key]['notify_pay_rate']=0;
                $data[$key]['notify_read_rate']=0;
                $data[$key]['read_pay_rate']=0;

                //通知-访问转换率   访客数/通知人数
                if($val['notice_user_num']>0){
                    $data[$key]['notify_read_rate']=round($val['read_user_num']/$val['notice_user_num']*100,2).'%';
                }


                //通知-付款转换率   付款人数/通知人数
                if($val['notice_user_num']>0){
                    $data[$key]['notify_pay_rate']=round($val['pay_user_num']/$val['notice_user_num']*100,2).'%';
                }


                //访客-付款转换率   付款人数/访客数
                if($val['notice_user_num']>0){
                    $data[$key]['read_pay_rate']=round($val['pay_user_num']/$val['notice_user_num']*100,2).'%';
                }
            }


        }
//        $data = ($data = self::systemPage($where,true)->page((int)$where['page'], (int)$where['limit'])->select()) && count($data) ? $data->toArray() : [];
        $count = self::systemPage($where,true)->count();
        return compact('count', 'data');
    }

    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where, $isAjax = false)
    {
        $model = new self;
        $model=$model->where('tenant_id','=',session('tenant_id'));

        $model=$model->group('send_date');
        if($where['title']){
//            $title=$where['title'];
//            $model=$model->where('title','like',"%$title%");
        }

        if($where['start_time']){
            $start_time=strtotime($where['start_time']);
            $model=$model->where('add_time','>=',$start_time);
        }

        if($where['end_time']){
            $end_time=strtotime($where['end_time'])+86399;
            $model=$model->where('add_time','<=',$end_time);
        }

        if ($isAjax === true) {
            $model = $model->order('id desc');
            return $model;
        }
        return self::page($model, function ($item) {}, $where);
    }



}