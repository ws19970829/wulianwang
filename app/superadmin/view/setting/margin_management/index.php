{extend name="public/container"}
{block name="content"}
<div class="layui-fluid" style="background: #fff;margin-top: -10px;">
    <div class="layui-row layui-col-space15"  id="app">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body">
                    <form class="layui-form layui-form-pane" action="">
                        <div class="layui-form-item">
                            <div class="layui-inline">
                                <label class="layui-form-label">支付类型</label>
                                <div class="layui-input-block">
                                    <select name="type">
                                        <option value=" ">全部</option>
                                        <option value="1">微信</option>
                                        <option value="2">支付宝</option>
                                        <option value="3">银行卡</option>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">支付时间</label>
                                <div class="layui-input-inline" >
                                    <input type="text" name="start_time" id="start_time" value="" placeholder="开始时间" autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid">-</div>
                                <div class="layui-input-inline">
                                    <input type="text" name="end_time" id="end_time" value=""  placeholder="结束时间" autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <div class="layui-input-inline">
                                    <button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit="search" lay-filter="search">
                                        <i class="layui-icon layui-icon-search"></i>搜索</button>
<!--                                    <button class="layui-btn layui-btn-primary layui-btn-sm export"  lay-submit="export" lay-filter="export"><i class="fa fa-floppy-o" style="margin-right: 3px;"></i>导出</button>-->

                                </div>
                                <div class="layui-input-inline">
                                    <button class="layui-btn layui-btn-sm layui-btn-normal" onclick="$eb.createModalFrame(this.innerText,'{:Url('create')}')">设置金额</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!--商品列表-->
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body">
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <!--操作-->
                    <script type="text/html" id="act">
<!--                        <button type="button" class="layui-btn layui-btn-xs layui-btn-normal" lay-event='edit'>-->
<!--                            详情-->
<!--                        </button>-->
                        {{# if(d.status == 0){}}
                        <button type="button" class="layui-btn layui-btn-xs layui-btn-normal" lay-event='pass'>
                            通过
                        </button>
                        <button type="button" class="layui-btn btn-danger layui-btn-xs layui-btn-normal" lay-event='unpass'>
                            不通过
                        </button>
                        {{# }else{ }}
                        <button type="button" class="layui-btn layui-btn-xs layui-btn-normal layui-btn-primary">
                            已审核
                        </button>
                        {{# } }}
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
<script>

    layList.date({elem:'#start_time',theme:'#0092DC',type:'datetime'});
    layList.date({elem:'#end_time',theme:'#0092DC',type:'datetime'});


    layList.form.render();
    //加载列表
    layList.tableList('List',"{:Url('order_list')}",function (){
        return [
            {field: 'admin_name', title: '商家名称', sort: true,event:'id'},
            {field: 'add_time', title: '操作时间',templet:'#image'},
            {field: 'price', title: '支付金额',templet:'#store_name'},
            {field: 'type', title: '付款类型',templet:function(d){
                if(d.type == 1){
                    return '<div>微信支付</div>';
                }else if(d.type == 2){
                    return '<div>支付宝</div>';
                }else{
                    return '<div>银行卡</div>';
                }
            }},
            {field: 'status', title: '审核状态',templet:function(d){
                if(d.status == 0){
                    return '<div>审核中</div>';
                }else if(d.status == 1){
                    return '<div>已通过</div>';
                }else{
                    return '<div>未通过</div>';
                }
            }},
            {field: 'right', title: '操作',align:'center',toolbar:'#act',width:'14%'},
        ];
    });
    //excel下载
    layList.search('export',function(where){
        where.excel = 1;
        location.href=layList.U({a:'order_list',q:where});
    })
    //下拉框
    $(document).click(function (e) {
        $('.layui-nav-child').hide();
    })
    function dropdown(that){
        var oEvent = arguments.callee.caller.arguments[0] || event;
        oEvent.stopPropagation();
        var offset = $(that).offset();
        var top=offset.top-$(window).scrollTop();
        var index = $(that).parents('tr').data('index');
        $('.layui-nav-child').each(function (key) {
            if (key != index) {
                $(this).hide();
            }
        })
        if($(document).height() < top+$(that).next('ul').height()){
            $(that).next('ul').css({
                'padding': 10,
                'top': - ($(that).parent('td').height() / 2 + $(that).height() + $(that).next('ul').height()/2),
                'min-width': 'inherit',
                'position': 'absolute'
            }).toggle();
        }else{
            $(that).next('ul').css({
                'padding': 10,
                'top':$(that).parent('td').height() / 2 + $(that).height(),
                'min-width': 'inherit',
                'position': 'absolute'
            }).toggle();
        }
    }
    //快速编辑
    layList.edit(function (obj) {
        var id=obj.data.id,value=obj.value;
        switch (obj.field) {
            case 'price':
                action.set_product('price',id,value);
                break;
            case 'stock':
                action.set_product('stock',id,value);
                break;
            case 'sort':
                action.set_product('sort',id,value);
                break;
            case 'ficti':
                action.set_product('ficti',id,value);
                break;
        }
    });
    //上下加商品
    layList.switch('is_show',function (odj,value) {
        if(odj.elem.checked==true){
            layList.baseGet(layList.Url({c:'store.store_product',a:'set_show',p:{is_show:1,id:value}}),function (res) {
                layList.msg(res.msg, function () {
                    layList.reload();
                });
            });
        }else{
            layList.baseGet(layList.Url({c:'store.store_product',a:'set_show',p:{is_show:0,id:value}}),function (res) {
                layList.msg(res.msg, function () {
                    layList.reload();
                });
            });
        }
    });
    //点击事件绑定
    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'delstor':
                var url=layList.U({c:'store.store_product',a:'delete',q:{id:data.id}});
                if(data.is_del) var code = {title:"操作提示",text:"确定恢复商品操作吗？",type:'info',confirm:'是的，恢复该商品'};
                else var code = {title:"操作提示",text:"确定将该商品移入回收站吗？",type:'info',confirm:'是的，移入回收站'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success',res.data.msg);
                            obj.del();
                            location.reload();
                        }else
                            return Promise.reject(res.data.msg || '删除失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                },code)
                break;
            case 'open_image':
                $eb.openImage(data.image);
                break;
            case 'edit':
                location.href = layList.U({a:'create',q:{id:data.id}});
                break;
            case 'pass':
                $.post("{:url('save_check_order')}",{id:data.id,status:1},function(ret){
                    if(ret.code == 200){
                        layer.msg(ret.msg,{time:1500},function(){
                            location.reload();
                        });
                    }else{
                        layer.msg(ret.msg);
                    }
                })
                break;
            case 'pass':
                $.post("{:url('save_check_order')}",{id:data.id,status:2},function(ret){
                    if(ret.code == 200){
                        layer.msg(ret.msg,{time:1500},function(){
                            location.reload();
                        });
                    }else{
                        layer.msg(ret.msg);
                    }
                })
                break;
            case 'starting':
                var url=layList.U({c:'store.store_product',a:'is_starting',q:{id:data.id}});
                if(data.is_starting == 0) var code = {title:"操作提示",text:"确认此商品为首发商品？",type:'info',confirm:'是的'};
                else var code = {title:"操作提示",text:"确认此商品取消首发商品？",type:'info',confirm:'是的'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success',res.data.msg);
                            obj.del();
                            location.reload();
                        }else
                            return Promise.reject(res.data.msg || '更改失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                },code)
                break;
            case 'recommend':
                var url=layList.U({c:'store.store_product',a:'is_recommend',q:{id:data.id}});
                if(data.is_recommend == 0) var code = {title:"操作提示",text:"确认此商品为推荐商品？",type:'info',confirm:'是的'};
                else var code = {title:"操作提示",text:"确认此商品取消推荐商品？",type:'info',confirm:'是的'};
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success',res.data.msg);
                            obj.del();
                            location.reload();
                        }else
                            return Promise.reject(res.data.msg || '更改失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                },code)
                break;
            case 'attr':
                $eb.createModalFrame(data.store_name+'-属性',layList.U({a:'attr',q:{id:data.id}}),{h:600,w:800})
                break;
        }
    })
    //排序
    layList.sort(function (obj) {
        var type = obj.type;
        switch (obj.field){
            case 'id':
                layList.reload({order: layList.order(type,'id')},true,null,obj);
                break;
            case 'sales':
                layList.reload({order: layList.order(type,'sales')},true,null,obj);
                break;
        }
    });
    //查询
    layList.search('search',function(where){
        layList.reload(where,true);
    });
    //自定义方法
    var action={
        set_product:function(field,id,value){
            layList.baseGet(layList.Url({c:'store.store_product',a:'set_product',q:{field:field,id:id,value:value}}),function (res) {
                layList.msg(res.msg);
            });
        },
        show:function(){
            var ids=layList.getCheckData().getIds('id');
            if(ids.length){
                layList.basePost(layList.Url({c:'store.store_product',a:'product_show'}),{ids:ids},function (res) {
                    layList.msg(res.msg);
                    layList.reload();
                });
            }else{
                layList.msg('请选择要上架的商品');
            }
        }
    };
    //多选事件绑定
    $('.layui-btn-container').find('button').each(function () {
        var type=$(this).data('type');
        $(this).on('click',function(){
            action[type] && action[type]();
        })
    });
    /**
     * 审核订单
     */
    function check_order(){
        layer.open({
            title: '缴纳保证金',
            type: 2,
            skin: 'layui-layer-rim', //加上边框
            area: ['400px', '400px'], //宽高
            content: '{:url("check_order")}?',
            cancel: function(){

            }
        });
    }
</script>
{/block}
