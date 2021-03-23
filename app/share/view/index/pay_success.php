<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="{__FRAME_PATH}js/jquery.min.js"></script>
<!--    <link rel="stylesheet" href="./index.css">-->
    <title>快捷付款</title>

    <style>
        * {
            margin: 0;
            padding: 0;
        }

        body {
            background: #f5f5f5;
        }

        .cont {
            background: #fff;
            padding: 0 0.3rem;
        }

        .contTop {
            padding-top: 100px;
            height: 4rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .contTop img {
            width: 1rem;
            height: 1rem;
            /*border-radius: 1rem;*/
        }

        .contTop div {
            font-size: 0.64rem;
            color: #333;
            /*margin-top: 0.2rem;*/
        }

        .contList {
            border-top: 0.02rem solid #eee;
            display: flex;
            padding: 0.2rem;
        }

        .contList .contListLeft {
            font-size: 0.32rem;
            color: #666;
        }

        .contList .contListRight {
            flex: 1;
            padding-left: 0.2rem;
            font-size: 0.32rem;
            color: #333;
        }

        .contList .contListInput {
            flex: 1;
            padding-left: 0.2rem;
            font-size: 0.32rem;
            color: #333;
            outline: none;
            border-radius: 0;
            border: 0;
        }

        .contList input::-webkit-input-placeholder {
            color: #ccc;
        }

        .bt {
            margin: 0.8rem 0.5rem 0.3rem;
            height: 0.9rem;
            background-image: linear-gradient(180deg, #f2bd6e 0%, #d09b4c 100%), linear-gradient(#1d93eb, #1d93eb);
            background-blend-mode: normal, normal;
            border-radius: 0.9rem;
            font-size: 0.36rem;
            color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>

<body>
    <div class="cont">
        <div class="contTop">
            <img style="width: 100px;height: auto;" src="/system/images/pay_success.png" alt="">
    </div>
<!--    <div class="bt">-->
<!--        去支付-->
<!--    </div>-->
    <div style="text-align: center;font-size: 0.28rem;color: #999;">
        感谢您的支持，如有问题请及时联系卖家
    </div>
        <div style="height: 200px;">

        </div>


</body>
<script src="/static/plug/layer/mobile/layer.js"></script>

<script>
    ;
    (function(designWidth, maxWidth) {
        var doc = document,
            win = window,
            docEl = doc.documentElement,
            remStyle = document.createElement("style"),
            tid;

        function refreshRem() {
            var width = docEl.getBoundingClientRect().width;
            maxWidth = maxWidth || 540;
            width > maxWidth && (width = maxWidth);
            var rem = width * 100 / designWidth;
            remStyle.innerHTML = 'html{font-size:' + rem + 'px;}';
        }

        if (docEl.firstElementChild) {
            docEl.firstElementChild.appendChild(remStyle);
        } else {
            var wrap = doc.createElement("div");
            wrap.appendChild(remStyle);
            doc.write(wrap.innerHTML);
            wrap = null;
        }
        //要等 wiewport 设置好后才能执行 refreshRem，不然 refreshRem 会执行2次；
        refreshRem();

        win.addEventListener("resize", function() {
            clearTimeout(tid); //防止执行两次
            tid = setTimeout(refreshRem, 300);
        }, false);

        win.addEventListener("pageshow", function(e) {
            if (e.persisted) { // 浏览器后退的时候重新计算
                clearTimeout(tid);
                tid = setTimeout(refreshRem, 300);
            }
        }, false);

        if (doc.readyState === "complete") {
            doc.body.style.fontSize = "16px";
        } else {
            doc.addEventListener("DOMContentLoaded", function(e) {
                doc.body.style.fontSize = "16px";
            }, false);
        }
    })(750, 750);

    //提交支付
    function submit_pay(){
        var money=$('#money').val();
        var collection_id='{$collection_id}';
        $.ajax({
            url:"{:Url('create_order')}",
            data:{money:money,collection_id:collection_id},
            type:'post',
            dataType:'json',
            success:function(re){
                if(re.status == 200){
                    console.log(re.data);
                    //走微信支付
                    onBridgeReady(re.data)
                }else{
                    layer.open({
                        content: re.msg
                        ,skin: 'msg'
                        ,time: 2 //2秒后自动关闭
                    });
                }
            },
            error:function () {

            }
        })
    }

    //请求微信支付
    function onBridgeReady(pay_data){
        WeixinJSBridge.invoke(
            'getBrandWCPayRequest', pay_data,
            function(res){
                if(res.err_msg == "get_brand_wcpay_request:ok"){
                    window.location.href='/';
                    // 使用以上方式判断前端返回,微信团队郑重提示：
                    //res.err_msg将在用户支付成功后返回ok，但并不保证它绝对可靠。
                }
            });
    }

</script>

</html>