<?php
/**
 * Created by PhpStorm.
 * User: lingyun
 * Date: 2020/8/8
 * Time: 11:18
 * Desc: 微信海关清关
 */
namespace app\api\controller\payservice;

use app\models\system\Express;
use think\facade\Cache;
use crmeb\services\UtilService;
use think\facade\Db;
use app\models\store\StoreOrderCustoms;
use think\Request;

class WxCustoms
{
    /**
     * @Author  lingyun
     * @Desc    商户平台配置信息
     * return array
     */
    public function get_config($type=1){
        $key = 'jHgyfPhMJL8vVtGuzeSduOshBXk9JMZh';
        $appid = 'wxc61a463995b029d4';
        $customs = 'GUANGZHOU_ZS';       //海关
        $mch_customs_no = '3702960NEN';       //商户海关备案号
        $mch_id = '1519748711';

        if($type == 1){
            $data['appid'] = $appid;
            $data['customs'] = $customs;
            $data['mch_customs_no'] = $mch_customs_no;
            $data['mch_id'] = $mch_id;

            return ['data'=>$data,'key'=>$key];
        }else if($type == 2){
            $data['appid'] = $appid;
            $data['customs'] = $customs;
            $data['mch_id'] = $mch_id;

            return ['data'=>$data,'key'=>$key];
        }else{
            $data['appid'] = $appid;
            $data['customs'] = $customs;
            $data['mch_customs_no'] = $mch_customs_no;
            $data['mch_id'] = $mch_id;

            return ['data'=>$data,'key'=>$key];
        }

        return ['data'=>$data,'key'=>$key];
    }

    /**
     * @Author  lingyun
     * @Desc    微信清关接口
     */
    public function customs_report($out_trade_no='',$transaction_id=''){
        $config = $this->get_config(1);
        $key = $config['key'];

//        $data['action_type'] = 'MODIFY';      //修改，默认ADD
        $data['appid'] = $config['data']['appid'];
//        $data['cert_id'] = '370213199802155213';      //身份证号
//        $data['cert_type'] = 'IDCARD';      //证件类型
        $data['customs'] = $config['data']['customs'];
        $data['mch_customs_no'] = $config['data']['mch_customs_no'];
        $data['mch_id'] = $config['data']['mch_id'];

//        $data['name'] = '袁绍航';        //姓名
//        $data['nonce_str'] = $this->create_nonce_str();
        $data['out_trade_no'] = $out_trade_no;       //订单号
        $data['transaction_id'] = $transaction_id;       //微信支付订单号
        $stringA = $this->create_sign_data($data,false);
        $stringSignTemp = $stringA."&key=".$key;

        $sign = strtoupper(md5($stringSignTemp));
        $data['sign'] = $sign;
        $postXml = $this->create_xml($data);

        $url = 'https://api.mch.weixin.qq.com/cgi-bin/mch/customs/customdeclareorder';
        $result = $this->curl_post_ssl($url,$postXml);

        return $result;
    }

    /**
     * @Author  lingyun
     * @Desc    微信清关 - 拆单报关
     * @param string $action_type   ADD-新增报关，MODIFY-修改报关
     * @param string $out_trade_no  商户订单号
     * @param string $transaction_id    微信支付订单号
     * @param string $sub_order_no  商户子订单号
     * @param string $product_fee   商品价格
     * @param string $transport_fee     物流费
     * @param string $cert_id   证件号码
     * @param string $user_name     姓名
     * return array|mixed
     */
    public function customs_split_report($action_type='ADD',$out_trade_no='',$transaction_id='',$sub_order_no='',$product_fee='',$transport_fee='',$cert_id='',$user_name=''){
        $config = $this->get_config(1);
        $key = $config['key'];

        $data['action_type'] = $action_type;
        $data['appid'] = $config['data']['appid'];
        $data['cert_id'] = $cert_id;
        $data['cert_type'] = 'IDCARD';
        $data['customs'] = $config['data']['customs'];
        $data['fee_type'] = 'CNY';
        $data['mch_customs_no'] = $config['data']['mch_customs_no'];
        $data['mch_id'] = $config['data']['mch_id'];
        $data['name'] = $user_name;
//        $data['nonce_str'] = $this->create_nonce_str();
        $data['order_fee'] = $product_fee*100+$transport_fee*100;        //子单号金额
        $data['out_trade_no'] = $out_trade_no;       //订单号
        $data['product_fee'] = $product_fee*100;       //商品价格
        $data['sub_order_no'] = $sub_order_no;
        $data['transaction_id'] = $transaction_id;       //微信支付订单号
        $data['transport_fee'] = $transport_fee*100;     //物流费

        $stringA = $this->create_sign_data($data,false);
        $stringSignTemp = $stringA."&key=".$key;

        $sign = strtoupper(md5($stringSignTemp));
        $data['sign'] = $sign;

        $postXml = $this->create_xml($data);

        $url = 'https://api.mch.weixin.qq.com/cgi-bin/mch/customs/customdeclareorder';
        $responseXml = $this->curl_post_ssl($url,$postXml);

        return $responseXml;
    }

    /**
     * @Author  lingyun
     * @Desc    查询清关结果
     */
    public function customs_search(){
        $config = $this->get_config(2);
        $key = $config['key'];
        $data = $config['data'];

        $data['out_trade_no'] = 'wx159351427945339570';       //订单号
//        $data['sub_order_no'] = 'wx159349992400739295_3';       //订单号
//        $data['fee_type'] = date('YmdHis',time());       //订单号
//        $data['transaction_id'] = '4200000589202006306689242201';       //微信支付订单号
        $stringA = $this->create_sign_data($data,false);
        $stringSignTemp = $stringA."&key=".$key;

        $sign = strtoupper(md5($stringSignTemp));
        $data['sign'] = $sign;

        $postXml = $this->create_xml($data);

        $url = 'https://api.mch.weixin.qq.com/cgi-bin/mch/customs/customdeclarequery';
        $responseXml = $this->curl_post_ssl($url,$postXml);

        var_dump($responseXml);exit();
    }

    /**
     * @Author  lingyun
     * @Desc    海关报关重推
     */
    public function customs_retweet(){
        $config = $this->get_config();
        $key = $config['key'];
        $data['appid'] = $config['data']['appid'];
        $data['customs'] = $config['data']['customs'];
        $data['mch_customs_no'] = $config['data']['mch_customs_no'];
        $data['mch_id'] = $config['data']['mch_id'];

        $data['out_trade_no'] = 'wx159290424898530513';       //订单号
        $stringA = $this->create_sign_data($data,false);
        $stringSignTemp = $stringA."&key=".$key;
        $sign = strtoupper(md5($stringSignTemp));
        $data['sign'] = $sign;
        $postXml = $this->create_xml($data);

        $url = 'https://api.mch.weixin.qq.com/cgi-bin/mch/newcustoms/customdeclareredeclare';
        $responseXml = $this->curl_post_ssl($url,$postXml);

        var_dump($responseXml);exit();
    }

    /**
     * @Author  lingyun
     * @Desc    创建xml数据
     */
    public function create_xml($data,$type=1){
        $xml = '<xml>';
        foreach($data as $key => $val){
            if($type == 2){
                if(is_numeric($val)){
                    $xml .= '<'.$key.'>'.$val.'</'.$key.'>';
                }else{
                    $xml .= '<'.$key.'><![CDATA['.$val.']]></'.$key.'>';
                }
            }else{
                $xml .= '<'.$key.'>'.$val.'</'.$key.'>';
            }
        }
        $xml .= '</xml>';

        return $xml;
    }

    /**
     * @Author  lingyun
     * @Desc    签名
     * @param $data
     * @param $unlencode
     * return string
     */
    public function create_sign_data($data,$unlencode){
        $result = '';
        foreach($data as $k => $v){
            $result .= $k.'='.$v.'&';
        }

        $result = trim($result,'&');
        return $result;
    }

    /**
     * @Author  lingyun
     * @Desc    随机字符串
     * @param int $length
     * return string
     */
    public function create_nonce_str($length=32){
        $chars = 'abcdefghigklmnopqrstuvwsyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for($i=0;$i<$length;$i++){
            $str .= substr($chars,mt_rand(0,strlen($chars)-1),1);
        }

        return $str;
    }

    /**
     * @Author  lingyun
     * @Desc    发送请求
     * @param $url
     * @param $postXml
     * return mixed
     */
    public function curl_post_ssl($url,$postXml){
        $header[] = "Content-type: text/xml;charset=utf-8";

        $ch = curl_init();  // 初始一个curl会话
        curl_setopt($ch, CURLOPT_URL, $url);    // 设置url
        curl_setopt($ch, CURLOPT_POST, 1);  // post 请求
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postXml);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $result = curl_exec($ch);
        curl_close($ch);

//        $result = simplexml_load_string($result);
//        $result= json_encode($result);
//        $result=json_decode($result,true);

        $result = (array)simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);

        return $result;
    }

    /**
     * @Author  lingyun
     * @Desc    更新清关订单状态 -- 未拆单
     */
    public function update_customs_state(){
        $datas = Db::name('store_order_customs')->where('type',1)->where('state','not in',['SUCCESS','FAIL'])->order('id asc')->limit(10)->select();
        $update_data = [];
        if(!empty($datas)){
            $datas = $datas->toArray();
            foreach($datas as $k => $v){
                $result = $this->customs_report($v['out_trade_no'],$v['transaction_id']);

                $arr = [];
                if($result['return_code'] == 'SUCCESS'){
                    $arr['id'] = $v['id'];
                    $arr['err_code'] = $result['err_code'];
                    $arr['err_code_des'] = $result['err_code_des'];
                    if($result['result_code'] == 'SUCCESS'){
                        $arr['count'] = isset($result['count'])?$result['count']:'';
                        $arr['mch_customs_no'] = isset($result['mch_customs_no_0'])?$result['mch_customs_no_0']:'';
                        $arr['customs'] = isset($result['customs_0'])?$result['customs_0']:'';
                        $arr['state'] = isset($result['state_0'])?$result['state_0']:$result['state'];
                        $arr['explanation'] = isset($result['explanation_0'])?$result['explanation_0']:'';
                        $arr['modify_time'] = isset($result['modify_time_0'])?$result['modify_time_0']:$result['modify_time'];
                        $arr['modify_time_int'] = strtotime($arr['modify_time']);
                        $arr['cert_check_result'] = isset($result['cert_check_result_0'])?$result['cert_check_result_0']:$result['cert_check_result'];
                        $arr['verify_department'] = isset($result['verify_department_0'])?$result['verify_department_0']:$result['verify_department'];
                        $arr['verify_department_trade_id'] = isset($result['verify_department_trade_id_0'])?$result['verify_department_trade_id_0']:$result['verify_department_trade_id'];
                    }
                }

                if(!empty($arr)){
                    array_push($update_data,$arr);
                }
            }

            (new StoreOrderCustoms())->saveAll($update_data);
        }
    }
}