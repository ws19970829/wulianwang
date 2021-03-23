<?php
/**
 * 支付控制类
 * @author 大梦
 * @DateTime 2019/07/06
 */

namespace app\wap\controller;

use Yansongda\Pay\Pay;

class Payment
{
    protected $config = [
        'app_id' => '2021002107616507',
        'notify_url' => 'http://hibug.3todo.com/api/alinotify',
        'return_url' => 'http://hibug.3todo.com/api/zhifubao/return_notify',
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAidIxTjeT70/cx//NDBoa6QOcBb5zFDA4TmEPHHcxf0K76WGWOoENcWmieCrPe7eL71yelFcEEEPDmmy/z5UsOZx/eDqeBsZASs8hGMpuMWeAEu8mt/9CN8iZxaEW5ebEh/MJWLGN91JWLshchyNpLxAORBjo/Gfibpn4pTTTzoBCETc/RFHkhe76AiaXtAu3MqsYtbcxcsca6rrX7KLD7wyf94zVgmmdxPcN5mpx0jrWLfrYWaEZt3X66agaaJIlH8+liMt0+FMiqbx6KD+eclYEemHbjhIjxRpS0SduD81L5j5iIoxkIKsA4+9e0XCw5IctW4wjaK5Z7YeysaIYJwIDAQAB',
        // 加密方式： **RSA2**
        'private_key' => 'MIIEpAIBAAKCAQEAsRs9anB9VXT6aVcM5f7Ku5DZi89wPep4qEAW8cuW8vpofDjB6gHXNPz6bcP7j4V7qRzcr38679drt4HUbfHCdX6DU0RF1IOksmtAfuTK3Sq3XAwLi95bKy/qTX4daTohBaVva23Zb1zMvwkJKX5lb++tl6qDbD5IBpEBDsXAC2J0XNugWhtVohBLqKXSMdTzsqs7jMH/fzokzP+Ko83WUzqkheKAmPmprNDNWa+xZO9ij6y/av0hIj5xv6XZYCk242+iWGPikIF0OeIgFN7dwZMq1t/PBKlPSUEr/oj74YQ5iqZi6UV5PbRPmGagZGYbM22qbYK31m2Bbjj+FB6raQIDAQABAoIBAFBbs7of4D5uHVC+lrOksAf66TpunNb7FFQDAGYlohANXms+tX2f6C26u0YirnmobzuERP28FNnOPKm+1swr7bex95RIXgbW1FvAHjt1RDalsxyounR0J5mo2/7dQisEnQca0TtFVGsbCCuFxgp1t0Da10EBtS/f8n5IPNqfD0waVGzgFg3SS5V9O3GO3U++PNEVD+CMe5Bbjj/bisjPTBznhlRMF6Hn8HIzV6LwPP2Xw9tOonOtKZGSIIyno4YTFw5g1Uid0ZRf3j+bH3bAVVz7uGBMqfYwp0GyAq2hnNHjbm9g4oEJgrskrMff97ZZfNUtrayIrg3LNIWJqJPqtKECgYEA+N2C1aQiOBNx3VIMjAZE1qr47wNsGCzHaaWNTSsnzhCSRXM+XtzUvb3KTV4WApvl2szRLN1FpBroViQ0jCR0rghSjXTgKvaN+kZnkEZPNzA6OXiwl5lcbMzA8D+gMQYbttThySN3WVUt+wuJonDk2cJrU+ZU9UQYlWdkGETI9WsCgYEAti8SPa1uDJ3Di56ep+mfBP/ULRUKc9mD5HLHEY/XO+D2/wgDjjoMG2/9zl/y8UQkCKocZqloK7Nq45/HqqT0Z4JEyiY5upzmb9TRjlgrrk37Be0e8Wt8BAHT4EkrO8c58yeweT+JofgGtbDGITqAm76+ri8azDoYS0Vz30mxg3sCgYAc/Cet3FUD9PfRECpX8K751aXyqJJham27V0XS1BDpxjJTFm4QPGYmx0EVq7yihyfJSQufzxG6YEvpJIReQiy4zE1ZHmCxgE/e825Cdn3pbpuJcA4ZSgpivaJHGRH+Q7jcnYTCmXfgFrSpRZm9Kqrs1eEMP4prmofGdulIjlJdiwKBgQCAckOMFMCoSHrb72OwwzgIkE6J0nHiKBOjd4D+0slPQFckjAPlvOtYVOc/H8rra710Fmubvgjh9sd/4OEnYNGoQOI7HCRLe9/ELOnqUCPL6tL25K2STNPNB3TYhv3iUZ573xm/ApsJBRPF+fJW7yZJqHxL1o4wOy1xtG/zl8N+DQKBgQC9tgXXg9zwWSvifTp6+iEyBy+l+/g5RcHKFhtlLtIu5zm7dsjmWmV+V+N9jhSYGMpSIYBluoTyJ/dZokK7Z7MFSibmHfHy1Mgm13D5ffTsUO3NN20UWJKZerB3jyUEYXJjOU7H16MJB/rGxnNmhv7HIhKd9e4FKb+TeRu2BAckvg==',
        'log' => [ // optional
            'file' => '../runtime/log/notify.log',
            'level' => 'debug'
        ],
        // 'mode' => '', // optional,设置此参数，将进入沙箱模式
    ];


    /**
     * 支付宝支付 - App
     * @author Meng
     * @dateTime 2020-11-27
     * @param    [type]     $order_id  [description]
     * @param    [type]     $body      [description]
     * @param    [type]     $pay_price [description]
     * @return   [type]                [description]
     */
    public function aliPayApp($order_id = '65454832315826', $body = '专属测试用例', $pay_price = 0.01)
    {
        $order = [
            'subject'      => $body,
            'out_trade_no' => $order_id,
            'total_amount' => $pay_price,
        ];

        $pay = Pay::alipay($this->config)->app($order);

        return $pay->getContent();
    }

    public function index()
    {
        $paySign = $this->aliPayApp();

        return json(['code'=>1,'data'=>$paySign]);

       // return $this->api_success('下单成功', $paySign);
    }
}