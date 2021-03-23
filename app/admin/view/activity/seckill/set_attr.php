{extend name="public/container"}
{block name="head_top"}
<link href="{__ADMIN_PATH}plug/umeditor/themes/default/css/umeditor.css" type="text/css" rel="stylesheet">
<link href="{__ADMIN_PATH}module/wechat/news/css/style.css" type="text/css" rel="stylesheet">
<link href="{__FRAME_PATH}css/plugins/chosen/chosen.css" rel="stylesheet">
<script type="text/javascript" src="{__ADMIN_PATH}plug/umeditor/third-party/jquery.min.js"></script>
<script type="text/javascript" src="{__ADMIN_PATH}plug/umeditor/third-party/template.min.js"></script>
<script type="text/javascript" charset="utf-8" src="{__ADMIN_PATH}plug/umeditor/umeditor.config.js"></script>
<script type="text/javascript" charset="utf-8" src="{__ADMIN_PATH}plug/umeditor/umeditor.js"></script>
<script src="{__ADMIN_PATH}frame/js/ajaxfileupload.js"></script>
<script src="{__ADMIN_PATH}plug/validate/jquery.validate.js"></script>
<script src="{__FRAME_PATH}js/plugins/chosen/chosen.jquery.js"></script>
<style>
    .wrapper-content {
        padding: 0 !important;
    }
    .please_select{
        font-size: 12px;
        color: #999;
    }
    td{
        text-align: center;
    }

    .blue{
        color: #0d8ddb;
    }
</style>
{/block}
{block name="content"}
<input type="hidden"  id="product_json_input" value="{$product_list_json}">
<div class="row">
    <div class="col-sm-12 panel panel-default" >
        <div class="panel-body" style="padding: 30px">
            <form class="form-horizontal" id="signupForm">
                <div class="form-group">
                    <div class="col-md-12">
                        <div class="input-group">
                            <span class="input-group-addon">活动名称</span>
                            <input maxlength="64" readonly placeholder="请在这里输入名称" name="title" class="layui-input" id="title" value="{$news.title}">
                            <input type="hidden"  id="id" value="{$news.id}">
                        </div>
                    </div>
                </div>
            </form>

                <table class="layui-table">
                    <colgroup>
                        <col width="150">
                        <col width="200">
                        <col>
                    </colgroup>
                    <thead>
                        <tr>
                            <th>商品图片</th>
                            <th>商品名称</th>
                            <th>规格</th>
                            <th>价格(元)</th>
                            <th>库存</th>
                            <th>活动价格</th>
                            <th>活动库存</th>
<!--                            <th>操作</th>-->
                        </tr>
                    </thead>
                    <form id="attr_form" action="{:url('save_attr')}" method="post">
                    <tbody id="list-container">
                    {if count($product_list)>0}
                    {foreach $product_list as $key=>$vo}

                    <!--多规格的循环，使用规格子数组来循环-->
                    {volist name='$vo.attrs' id='v'}
                    <tr>
                        <td><img src="{$vo.image}" style="width: 80px;height: 80px;"></td>
                        <td>{$vo.store_name}</td>
                        <td>
                            {volist name='$v.detail' id='d'}
                            {$key}:{$d}&nbsp;&nbsp;
                            {/volist}
                        </td>
                        <td>{$v.price}</td>
                        <td>{$v.stock}</td>
                        <td><input type="text" class="layui-input" name="activity_price" value="{$v.activity_price|default=''}"></td>
                        <td><input type="text" class="layui-input" name="activity_stock" value="{$v.activity_stock|default=''}"></td>
                        <input type="hidden" name="product" value="{$vo.id}">
<!--                        <td>-->
<!--                            <button type="button" class="layui-btn layui-btn-danger" onclick="delGoods(this,{$key})">删除</button>-->
<!--                        </td>-->
                    </tr>
                    {/volist}

                    {/foreach}
                    {else}
                    <tr>
                        <td class="please_select" colspan="4">
                            请选择商品
                        </td>
                    </tr>
                    {/if}
                    </tbody>
                </table>

                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-offset-4 col-md-9">
                            <button type="button" class="btn btn-w-m btn-info save_news">保存</button>
                        </div>
                    </div>
                </div>
            </form>


        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>

{/block}
{block name="script"}
<script>

    $('.save_news').on('click',function(){

//        let form_data = $("#attr_form").serialize();
//        console.log(form_data);

        var fields = $('#attr_form').serializeArray();
        var json_data=JSON.stringify(fields);

        console.log(fields);
        console.log(json_data);
        var obj = {}; //声明一个对象
        $.each(fields, function(index, field) {
            obj[field.name] = field.value; //通过变量，将属性值，属性一起放到对象中
//            obj[field.name].push(field.value) ; //通过变量，将属性值，属性一起放到对象中
        })
    var attr_list = {
        attr_list:json_data,
        activity_id:'{$activity_id}'
    }
//
        console.log(obj);
//        return false;

        $.ajax({
            url:"{:Url('save_attr')}",
            data:attr_list,
            type:'post',
            dataType:'json',
            success:function(re){
//                layer.close(index);
                if(re.code == 200){
//                    data[re.data] = list;
//                    $('.type-all>.active>.new-id').val(re.data);
                    $eb.message('success',re.msg);
                    location.reload();
//                    setTimeout(function (e) {
//                        parent.$(".J_iframe:visible")[0].contentWindow.location.reload();
//
////                                parent.layer.close(parent.layer.getFrameIndex(window.name));
//                    },600)
                }else{
                    $eb.message('error',re.msg);
                }
            },
            error:function () {
//                layer.close(index);
            }
        })
    });






</script>
{/block}