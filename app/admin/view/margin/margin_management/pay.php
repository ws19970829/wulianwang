<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>缴纳保证金</title>
    <script src="{__PLUG_PATH}vue/dist/vue.min.js"></script>
    <link href="{__PLUG_PATH}iview/dist/styles/iview.css" rel="stylesheet">
    <script src="{__PLUG_PATH}iview/dist/iview.min.js"></script>
    <script src="{__PLUG_PATH}jquery/jquery.min.js"></script>
    <script src="{__PLUG_PATH}form-create/province_city.js"></script>
    <script src="{__PLUG_PATH}form-create/form-create.min.js"></script>
    <link href="{__PLUG_PATH}layui/css/layui.css" rel="stylesheet">
    <script src="{__PLUG_PATH}layui/layui.all.js"></script>
    <style>
        /*弹框样式修改*/
        .ivu-modal{top: 20px;}
        .ivu-modal .ivu-modal-body{padding: 10px;}
        .ivu-modal .ivu-modal-body .ivu-modal-confirm-head{padding:0 0 10px 0;}
        .ivu-modal .ivu-modal-body .ivu-modal-confirm-footer{display: none;padding-bottom: 10px;}
        .ivu-date-picker {display: inline-block;line-height: normal;width: 280px;}
        .ivu-modal-footer{display: none;}
        .coupon_form .ivu-input-number{width: 150px; !important;}
        .layui-form-label {
            float: left;
            display: block;
            padding: 9px 15px;
            width: 100px;
            font-weight: 400;
            line-height: 20px;
            text-align: right;
        }
    </style>
</head>
<body>
<div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief">
    <div class="layui-tab-content" style="height: 100px;">
        <div class="layui-tab-item layui-show coupon_form" id="formdiv">
            <form id="form_data" class="layui-form" method="post" action="{:url('save')}">
                <input type="hidden" name="opmode" value="add">
                <div class="layui-form-item" style="text-align:center;">
                    <span>缴纳金额：￥{$margin_money}</span>
                </div>
                <div class="layui-form-item" style="text-align: center;">
                    <img src="/admin/margin.MarginManagement/pay_order?order_no={$order_no}" width="100" height="100">
                </div>
                <div class="layui-form-item" style="text-align: center;">
                    请扫码进行支付
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    var scopeId = '';
    layui.config({
        base : '/static/plug/layui/'
    }).extend({
        selectN: './selectN',
    }).use('layer',function () {
        var layer = layui.layer;
        var $ = layui.$;

        setInterval('search_state()',1500);
    })



    function search_state(){
        var $ = layui.$;
        var order_no = "{$order_no}";
        $.post("{:url('get_order_state')}",{order_no:order_no},function(ret){
            if(ret.code == 200){
                layer.msg(ret.msg);
                parent.close_open();
            }
        });
    }

</script>
</body>
</html>