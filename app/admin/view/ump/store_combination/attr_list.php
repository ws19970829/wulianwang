<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    {include file="public/head"}
    <script src="/static/plug/jquery-1.4.1.min.js"></script>
    <link rel="stylesheet" href="/static/plug/layui/css/layui.css">
    <script src="/static/plug/layui/layui.js"></script>
    <style>
        .layui-form-checkbox{
            margin-top: 0!important;
            margin-right: 0;
        }
        .layui-form-checkbox i{
            border-left: 1px solid #d2d2d2;
        }
    </style>
</head>
<body>
<form class="layui-form" action="" style="padding: 30px 50px 0px 0px;">
    <div class="layui-form-item">
        <label class="layui-form-label">规格选择</label>
        <div class="layui-input-block">
            <table class="layui-table attrTab">
                <thead>
                <tr>
                    <th>属性名称</th>
                    <!-- <th>图片</th> -->
                    
                    <!-- <th>成本价</th>
                    <th>原价</th> -->
                    
                    <th>活动价格</th>
                    <th>限量</th>
                    <th>起订量</th>
                    <th>库存</th>
                    <!-- <th>重量</th>
                    <th>体积</th> -->
                    <th>选择</th>
                </tr>
                </thead>
                <tbody>
                {volist name="attr" id="vo" key="k"}
                <tr>
                    {volist name="$vo" id="item"}

                    {if in_array($key,$arr)}
                        {if $key=='suk'}
                        <td><input type="text" name="attr[{$k}][{$key}]" class="layui-input" 
                    value="{$item}" readonly /></td>

                        {elseif  $key=='price'/}
                        <td><input type="number" name="attr[{$k}][{$key}]" class="layui-input max" 
                    value="{$item}" min="0"  oninput="checknum(this)"></td>

                        {elseif  $key=='quota'/}
                        <td><input type="number" name="attr[{$k}][{$key}]" class="layui-input max" 
                    value="{$item}" min="0" max="{$vo['true_stock']}" oninput="checknum(this)"></td>

                        {elseif $key=='true_stock'}
                        <td><input type="number" name="attr[{$k}][{$key}]" class="layui-input" 
                    value="{$item}" readonly /></td>

                        {elseif $key=='moq'}
                        <td><input type="number" name="attr[{$k}][{$key}]" class="layui-input" 
                    value="{$item}" min="1" /></td>

                        {elseif $key=='check'}
                    <td><input type="checkbox" name="ids[]" value="{$k}" {if condition="$item eq 1"}checked{/if}></td>
                    {/if}

                 {else/}
                    <input type="hidden" name="attr[{$k}][{$key}]" class="layui-input" value="{$item}">
                {/if}

                    {/volist}
                </tr>
                {/volist}
                </tbody>
            </table>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-input-block">
            <input type="hidden" name="id" value="{$id}">
            <button type="button" class="layui-btn" lay-submit lay-filter="formDemo">立即提交</button>
        </div>
    </div>
</form>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
<script>
    var cl = '';
    layui.use('form', function () {
        var form = layui.form;
        form.on('submit(formDemo)', function (data) {
            layList.basePost('save_attr', data.field, function (res) {
                parent.layer.close(parent.layer.getFrameIndex(window.name));
                parent.layer.msg(res.msg, {icon:1,time:2000});
            }, function (res) {
                parent.layer.msg(res.msg, {icon:1,time:2000});
            });
        });
    });
    function createFrame(title,src,opt,k){
        cl = k;
        opt === undefined && (opt = {});
        var h = parent.document.body.clientHeight - 100;
        return layer.open({
            type: 2,
            title:title,
            area: [(opt.w || 700)+'px', (opt.h || h)+'px'],
            fixed: false, //不固定
            maxmin: true,
            moveOut:false,//true  可以拖出窗外  false 只能在窗内拖
            anim:5,//出场动画 isOutAnim bool 关闭动画
            offset:'auto',//['100px','100px'],//'auto',//初始位置  ['100px','100px'] t[ 上 左]
            shade:0,//遮罩
            resize:true,//是否允许拉伸
            content: src,//内容
            move:'.layui-layer-title'
        });
    }
    function changeIMG(index,pic){
        $('#'+cl).children('input').val(pic);
        $('#'+cl).children('img').attr('src',pic);
    }
    function checknum(e){
        if(parseInt(e.value)>parseInt(e.max)){
            $(e).val(e.max);
        }
        if(parseInt(e.value)<0){
            $(e).val(0);
        }
    }
</script>
</body>
</html>