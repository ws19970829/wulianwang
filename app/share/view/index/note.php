<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- <script src="./web/js/rem.js"></script> -->
    <!-- <script src="./web/js/jquery.js"></script> -->
    <meta content="width=device-width,initial-scale=1,user-scalable=no" name="viewport">
    <script src="{__FRAME_PATH}js/jquery.min.js"></script>
    <title>{$info.title}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }

        body {
            background: #f5f5f5;
        }

        .salon-detail {
            padding: 0.3rem;
            box-sizing: border-box;
            /* border-bottom: 0.2rem solid #f5f8f5; */
            background: #ffffff;
        }

        .salon-detail .tit {
            font-size: 0.34rem;
            text-align: center;
            font-weight: bold;
            color: #333333;
        }

        .salon-detail .date {
            margin-top: 0.1rem;
            font-size: 0.26rem;
            color: #666666;
            text-align: center;
        }




        .shopList {
            padding: 15px 15px 0;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .shopList .list {
            background: #ffffff;
            margin-bottom: 20px;
            width: 160px;
            transition: all 0.5s;
            -o-transition: all 0.5s;
            -moz-transition: all 0.5s;
            -webkit-transition: all 0.5s;
            box-shadow: 0 10px 15px #f3f3f3;
        }

        .shopList .list .list-top {
            display: block;
            width: 160px;
            height: 160px;
            object-fit: cover;
        }

        .shopList .list .list-bottom {
            padding: 10px;
        }

        .shopList .list .list-bottom .list-title {
            color: #333;
            font-size: 16px;
            font-weight: bold;
        }

        .shopList .list .list-bottom .list-price {
            display: flex;
            justify-content: space-between;
            align-items: center;

        }

        .shopList .list .list-bottom .list-price>div:nth-of-type(1) {
            font-size: 14px;
            color: #ff5000;
        }

        .shopList .list .list-bottom .list-price>div:nth-of-type(2) {
            font-size: 12px;
            color: #999;
        }

        .shopList .list:hover {
            box-shadow: 0 10px 15px #999;
        }

        .btBottom {
            position: fixed;
            bottom: 0;
            width: 100%;
            height: 1.2rem;
            box-sizing: border-box;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 0.12rem;
            background: #ffffff;
        }

        .btBottomLeft {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btBottomLeft>div {
            display: flex;
            justify-content: space-between;
            align-items: center;


        }

        .btBottomLeft>div span {
            font-size: 12px;
            color: #999;
            padding-left: 0.1rem;
        }

        .btBottomLeft img {
            width: 0.4rem;
            height: 0.4rem;
        }

        .btBottomLeft>div:nth-of-type(2) {
            padding-left: 0.2rem;
        }



        .btBottomRight {
            margin-left: 0.2rem;
            height: 0.8rem;
            background-image: linear-gradient(180deg, #f2bd6e 0%, #d09b4c 100%), linear-gradient(#1d93eb, #1d93eb);
            background-blend-mode: normal,
            normal;
            border-radius: 0.8rem;
            /* width: 4rem; */
            flex: 1;
            font-size: 0.34rem;
            color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .cont img {
            max-width: 100%;
        }
    </style>
</head>

<body>
<div class="salon-detail">
<!--    <div class="tit">{$info.title}</div>-->
<!--    <div class="date">{$info.add_time}</div>-->
    <div class="desc cont">
        {$info.content|raw}
    </div>
</div>

<div class="shopList">
    {foreach $info.product_list as $vo}
    <div class="list">
        <img class="list-top" src="{$vo.image}" onclick="jump_url('{$vo.url}')">
        <div class="list-bottom"  onclick="jump_url('{$vo.url}')">
            <div class="list-title" style="text-overflow: -o-ellipsis-lastline;overflow: hidden;text-overflow:ellipsis;display: -webkit-box;-webkit-line-clamp: 2;-webkit-box-orient: vertical;height: 44px">
                {$vo.store_name}
            </div>
            <div class="list-price">
                <div>
                    ￥{$vo.price}
                </div>
                <div>
                    {$vo.sales}人购买
                </div>
            </div>
        </div>
    </div>
    {/foreach}

    <div style="width: 200px;"></div>
</div>
<div style="height: 1.2rem;"></div>
<div class="btBottom">
    <div class="btBottomLeft">
        <div>
            <img src="data:image/png;base64,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" alt="">
            <span>{$info.dianzan_num}</span>
        </div>
        <div>
            <img src="data:image/png;base64,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" alt="">
            <span>{$info.view_num}</span>

        </div>
    </div>
    <div class="btBottomRight" onclick="into_shop()">进入店铺</div>
    <input type="hidden" id="site_url" value="{$site_url}">
</div>

</body>
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

    function into_shop(){
        var is_admin_view='{$is_admin_view}';
        if(is_admin_view!=1){
            var site_url=$('#site_url').val();
            window.location.href=site_url;
        }
    }

    function jump_url(url){
        //如果是后台预览，则屏蔽跳转
        var is_admin_view='{$is_admin_view}';
        if(is_admin_view!=1){
            window.location.href=url;
        }
    }
</script>

</html>