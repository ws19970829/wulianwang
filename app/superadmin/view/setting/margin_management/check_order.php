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
                <input type="hidden" name="id" value="{$id}">
                <div class="layui-form-item">
                    <label class="layui-form-label">审核结果：</label>
                    <div class="layui-input-block">
                        <input type="radio" name="status" value="1" title="通过" checked="">
                        <input type="radio" name="status" value="2" title="不通过">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">备注：</label>
                    <div class="layui-input-block">
                        <textarea placeholder="请输入内容" class="layui-textarea" name="remark" id="remark"></textarea>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"></label>
                    <div class="layui-input-block">
                        <a class="layui-btn" onclick="save();">保存</a>
                    </div>
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
    })


    function save(){
        var $ = layui.$;
        var status = $('input[name=status]:checked').val();
        var remark = $('#remark').val();

        $.post("{:url('save_check_order')}",{id:id,status:status,remark:remark},function(ret){
            if(ret.code == 200){
                layer.msg(ret.msg);
                parent.location.reload();
            }
        });
    }



</script>
</body>
</html>