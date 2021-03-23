<?php
/**
 * Created by PhpStorm.
 * User: lingyun
 * Date: 2020/8/10
 * Time: 23:30
 * Desc: 支付宝报关
 */
namespace app\api\controller\payservice;

use app\models\system\Express;
use think\facade\Cache;
use crmeb\services\UtilService;
use think\facade\Db;
use app\models\store\StoreOrderCustoms;
use think\Request;

class AliCustoms
{
    protected $alipay_config = [];

    public function __construct(){
        $this->alipay_config['partner']		= '2088331440974722';
        $this->alipay_config['private_key']	= 'MIICXAIBAAKBgQDK1JMG0+K3w4TcDQW7uEazp9vrv+rLvgAlEsThWzVQEFkJhjwTAAx1tMdi5chU3DUo8317TMB5bG7GyMdORomireUaaLxBuu+VOaYoV8lSyAcIuJwNxOwhYWJUsLbstFFN/VbXNynQQrUDJdVdw+WEo0PENt6TAyFS2Yf9ImzVpQIDAQABAoGAP+jfMuWMqG547IVF6zJTRMR9bTkZmH0TuprBYmjE0ad1BqU/RJVdV7FQqf7RMrv4HCEsxq8WbqwV85jMBAQB1arvDiFUbvQh+nhaXZ8qimJVQWDRxY3BUgdLmbjCqX/6Muhchs8Meg2l0fsJTRRci/dWaR0V/hfzrABFJXHyS4ECQQDlI2tjkakffguhb3GyWd8/87cku8U971PMK9zrWZpjLH/uCUV8L9mWLYrwwbQUSxmR1b/47fp3mh4233/xx/9xAkEA4pui3cLtmDvGtUl1S2fYVXmZKFRXHLVytFveozfas6367ikCE/8VEfBg3YmVIsCF2OTy3hJgMEN+RlwmuBkHdQJAAy6AuTs2i/dmFfHENGPHE85AhsQMsxV1pmodgS8XU7U0eYuraVQIw2sSeNFXvMhmLH45Ui2LwsljDgQAdM0AIQJBANEZHbKVYNndJqE3dEUtQGC2wI2HLY6vG3WzY/+l1WeA/Y9vRZyf/pg7/5XMk1Gq5pbZxAUIXbuLO+S+uXecmNUCQGzDdrobh182xomgLqr/l7fJuX67XhfjuJLA0jmMY1AcFD5uZgh136ghBv4VKus6L1C6upuoNEylAm+QUEUYhbI=';
        $this->alipay_config['alipay_public_key']= 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDK1JMG0+K3w4TcDQW7uEazp9vrv+rLvgAlEsThWzVQEFkJhjwTAAx1tMdi5chU3DUo8317TMB5bG7GyMdORomireUaaLxBuu+VOaYoV8lSyAcIuJwNxOwhYWJUsLbstFFN/VbXNynQQrUDJdVdw+WEo0PENt6TAyFS2Yf9ImzVpQIDAQAB';
        $this->alipay_config['sign_type']    = strtoupper('RSA');
        $this->alipay_config['input_charset']= strtolower('utf-8');
        $this->alipay_config['cacert']    = './alipay_customs/cacert.pem';
        $this->alipay_config['transport']    = 'http';
    }

    /**
     * @Author  lingyun
     * @Desc    拆单报关
     * @param string $order_no
     * @param string $trade_no
     * @param string $order_detail_no
     * @param string $amount
     * return 排序前的数组|array|mixed|string
     */
    public function customs($order_no='',$trade_no='',$order_detail_no='',$amount=''){
        $data = array(
            'amount' => $amount,                                    //报关金额
            'customs_place' => "zongshu",                            //海关编号
            'is_split'=>'T',
            'merchant_customs_code' => "3702960NEN",              //商户海关备案号
            'merchant_customs_name' => "中综投国际物流有限公司",      //商户海关备案名称
            'out_request_no' => $order_no,                   //报关流水号（订单号）
            'partner' => "2088331440974722",                      //合作者身份ID，2088开头
            'service' => "alipay.acquire.customs",                //接口名称
            'sub_out_biz_no'=>$order_detail_no,                     //拆单订单号
            'trade_no' => $trade_no,                                //支付宝流水号
            '_input_charset' => "utf-8",                          //参数编码字符集
        );

        $data = $this->argSort($data);//对数组排序
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $sign_data= $this->createLinkstring($data);
        //生成sign参数
        $sign = urlencode($this->rsaSign($sign_data));

        //签名结果与签名方式 加入请求提交参数组中
        $data['sign_type'] = 'RSA';
        $data['sign'] = $sign;
        $url = 'https://mapi.alipay.com/gateway.do?';
        $data = $this->createLinkstring($data);

        //发送get请求的url
        $getUrl = $url.$data;

        $response = $this->getHttpResponseGET($getUrl);
        //接受报关返回的xml，转换成数组
        $xml = simplexml_load_string($response);
        $data = json_decode(json_encode($xml),TRUE);

        return $data;
    }

    /**
     * 对数组排序
     * @param $para 排序前的数组
     * return 排序后的数组
     */
    function argSort($para) {
        ksort($para);
        reset($para);
        return $para;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param $para 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    function createLinkstring($para) {
        $arg  = "";
//        while (list ($key, $val) = each ($para)) {
//            $arg.=$key."=".$val."&";
//        }

        foreach($para as $key => $val){
            $arg.=$key."=".$val."&";
        }

        //去掉最后一个&字符
//        $arg = substr($arg,0,count($arg)-2);
        $arg = trim($arg,'&');

        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
        return $arg;
    }

    /**
     * RSA签名
     * @param $data 待签名数据
     * @param $private_key 商户私钥字符串
     * return 签名结果
     */
    function rsaSign($data) {
        //商户私钥
        $private_key = $this->alipay_config['private_key'];

        //以下为了初始化私钥，保证在您填写私钥时不管是带格式还是不带格式都可以通过验证。
        $private_key=str_replace("-----BEGIN RSA PRIVATE KEY-----","",$private_key);
        $private_key=str_replace("-----END RSA PRIVATE KEY-----","",$private_key);
        $private_key=str_replace("\n","",$private_key);

        $private_key="-----BEGIN RSA PRIVATE KEY-----".PHP_EOL .wordwrap($private_key, 64, "\n", true). PHP_EOL."-----END RSA PRIVATE KEY-----";

        $res=openssl_get_privatekey($private_key);

        if($res)
        {
            openssl_sign($data, $sign,$res);
        }
        else {
            echo "您的私钥格式不正确!"."<br/>"."The format of your private_key is incorrect!";
            exit();
        }
        openssl_free_key($res);
        //base64编码
        $sign = base64_encode($sign);
        return $sign;
    }

    /**
     * 远程获取数据，GET模式
     * 注意：
     * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
     * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
     * @param $url 指定URL完整路径地址
     * @param $cacert_url 指定当前工作目录绝对路径
     * return 远程输出的数据
     */
    function getHttpResponseGET($url) {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
        $responseText = curl_exec($curl);
        //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);

        return $responseText;
    }


}