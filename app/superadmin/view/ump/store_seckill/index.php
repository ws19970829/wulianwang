{extend name="public/container"}
{block name="head_top"}
<script type="text/javascript" src="{__PLUG_PATH}jquery.downCount.js"></script>
{/block}
{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">秒杀商品搜索</div>
                <div class="layui-card-body">
                    <div class="alert alert-success alert-dismissable">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                        目前拥有{$countSeckill}个秒杀商品
                    </div>
                    <form class="layui-form">
                        <div class="layui-form-item">
                            <div class="layui-inline">
                                <label class="layui-form-label">搜　　索：</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="store_name" lay-verify="store_name" style="width: 100%" autocomplete="off" placeholder="请输入商品名称,关键字,编号" class="layui-input">
                                </div>
                            </div>
                          

                            {empty name='$tenant_id'}
                            <div class="layui-inline">
                                <label class="layui-form-label">所属商家：</label>
                                <div class="layui-input-inline">
                                    <select name="tenant_id" ">
                                        <option value="">全部</option>
                                        {foreach $shop as $vo}
                                        <option value="{$vo.id}">{$vo.real_name}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                            {else /}
                            <input type="hidden" name="tenant_id" value="{$tenant_id}">
                            {/empty}
                        </div>
                        
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <button class="layui-btn layui-btn-sm" lay-submit="" lay-filter="search" style="font-size:14px;line-height: 9px;">
                                    <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>搜索</button>
                                <!-- <button lay-submit="export" lay-filter="export" class="layui-btn layui-btn-primary layui-btn-sm">
                                    <i class="layui-icon layui-icon-delete layuiadmin-button-btn" ></i> Excel导出</button> -->
                            </label>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">秒杀商品列表</div>
                <div class="layui-card-body">
                    <!-- <div class="layui-btn-container">
                        <a class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame(this.innerText,'{:Url('create')}',{h:700,w:1100});">添加秒杀商品</a>
                    </div> -->
                    <button type="button" class="layui-btn layui-btn-sm" id='rec'>推荐</button>
                        <button type="button" class="layui-btn layui-btn-sm" id='unrec'>取消推荐</button>
                    <table class="layui-hide" id="seckillList" lay-filter="seckillList"></table>
                    <script type="text/html" id="status">
                        <input type='checkbox' name='status' lay-skin='switch' value="{{d.id}}" lay-filter='status' lay-text='开启|关闭'  {{ d.is_show == 1 ? 'checked' : '' }}>
                    </script>
                    <script type="text/html" id="stopTime">
                        <div class="count-time-{{d.id}}" data-time="{{d._stop_time}}">
                            <span class="days">00</span>
                            :
                            <span class="hours">00</span>
                            :
                            <span class="minutes">00</span>
                            :
                            <span class="seconds">00</span>
                        </div>
                    </script>
                    <script type="text/html" id="statusCn">
                        {{ d.status == 1 ? d.start_name : '关闭' }}
                    </script>
                    <script type="text/html" id="sort">
                      <input type="number" name="title" lay-verify="number" autocomplete="off"  class="layui-input sort" min='0' value='{{d.sort}}' tenant_id = '{{d.id}}'>
                    </script>
                    <script type="text/html" id="barDemo">
                        <button type="button" class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('{{d.title}}-设置规格','{:Url('attr_list')}?id={{d.id}}',{h:1000,w:1400});"><i class="layui-icon layui-icon-util"></i>规格</button>

                        <button type="button" class="layui-btn layui-btn-xs" onclick="dropdown(this)">操作<span class="caret"></span></button>
                        <ul class="layui-nav-child layui-anim layui-anim-upbit">
                            <li>
                                <a href="javascript:void(0);" onclick="$eb.createModalFrame('{{d.title}}-编辑','{:Url('edit')}?id={{d.id}}')"><i class="layui-icon layui-icon-edit"></i> 编辑活动</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" onclick="$eb.createModalFrame('{{d.title}}-编辑内容','{:Url('edit_content')}?id={{d.id}}')"><i class="layui-icon layui-icon-edit"></i>编辑内容</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="delstor" lay-event='delstor'><i class="layui-icon layui-icon-delete"></i> 删除</a>
                            </li>
                        </ul>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
{/block}
{block name="script"}
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
<script src="{__FRAME_PATH}js/content.min.js?v=1.0.0"></script>
<script>
    setTime();
    function setTime() {
        setTimeout(function () {
            $eb.axios.get("{:Url('get_seckill_id')}").then(function(res){
                $.each(res.data.count,function (index,item) {
                    var time = $('.count-time-'+item).attr('data-time');
                    if(time != ''){
                        $('.count-time-'+item).downCount({
                            date: time,
                            offset: +8
                        });
                    }
                })
            }).catch(function(err){
                console.log(err);
            });
        },2000);
    }
</script>
<script>
    layList.form.render();
    layList.tableList('seckillList',"{:Url('get_seckill_list')}?tenant_id={$tenant_id}",function () {
        return [
            {type:'checkbox'},
            {field: 'id', title: 'ID', sort: true,width:'6%',event:'id'},
            {
                field: 'sort',
                title: '排序',
                align: "center",
                width: "6%",
                templet:'#sort'
            },
            {field: 'shop_name',  width:'10%',title:'所属商家'},
            {field: 'image', title: '商品图片', width: '10%',templet: '<p><img src="{{d.image}}" alt="{{d.title}}" class="open_image" data-image="{{d.image}}"></p>'},
            {field: 'title', title: '商品名称'},
            // {field: 'ot_price', title: '原价',width:'6%'},
            {field: 'price', title: '活动价',width:'6%'},
            // {field: 'quota_show', title: '限量',width:'6%'},
            {field: 'quota', title: '限量剩余',width:'6%'},
            {field: 'is_hot', title: '推荐状态',width:'15%',templet:function(d){
                        if(d.is_hot==1){
                            return '已推荐：<br/>'+ d.rec_start+'<br/>'+d.rec_end;
                        }else{
                            return '未推荐'
                        }
                    }},
            {field: 'status_text', title: '秒杀状态',width:'6%'},
            {field: 'stop_time', title: '结束时间', width: '8%',toolbar: '#stopTime'},
            {field: 'status', title: '状态',width:'6%',toolbar:"#status"},
            // {field: 'right', title: '操作',width:'10%', align: 'center', toolbar: '#barDemo'}
        ]
    });
    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'delstor':
                var url=layList.U({c:'ump.store_seckill',a:'delete',q:{id:data.id}});
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success',res.data.msg);
                            obj.del();
                        }else
                            return Promise.reject(res.data.msg || '删除失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                })
                break;
        }
    })
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
    layList.search('search',function(where){
        layList.reload(where);
        setTime();
    });
    layList.search('export',function(where){
        location.href=layList.U({c:'ump.store_seckill',a:'save_excel',q:{status:where.status,store_name:where.store_name}});
    })
    layList.switch('status',function (odj,value,name) {
        if (odj.elem.checked == true) {
            layList.baseGet(layList.Url({
                c: 'ump.store_seckill',
                a: 'set_seckill_status',
                p: {status: 1, id: value}
            }), function (res) {
                layList.msg(res.msg);
            }, function () {
                odj.elem.checked = false;
                layui.form.render();
                layer.msg('操作失败，未设置秒杀商品');
                // layer.open({
                //     type: 1
                //     ,offset: 'auto'
                //     ,id: 'layerDemoauto' //防止重复弹出
                //     ,content: '<div style="padding: 20px 100px;">请先配置规格</div>'
                //     ,btn: '设置规格'
                //     ,btnAlign: 'c' //按钮居中
                //     ,shade: 0 //不显示遮罩
                //     ,yes: function(){
                //         layer.closeAll();
                //         $eb.createModalFrame('设置规格','{:Url('attr_list')}?id='+value+'',{h:1000,w:1400});
                //     }
                // });
            });
        } else {
            layList.baseGet(layList.Url({
                c: 'ump.store_seckill',
                a: 'set_seckill_status',
                p: {status: 0, id: value}
            }), function (res) {
                layList.msg(res.msg);
            });
        }
    })
    $('.js-group-btn').on('click',function(){
        $('.js-group-btn').css({zIndex:1});
        $(this).css({zIndex:2});
    });
    $('#delstor').on('click',function(){
        window.t = $(this);
        var _this = $(this),url =_this.data('url');
        $eb.$swal('delete',function(){
            $eb.axios.get(url).then(function(res){
                console.log(res);
                if(res.status == 200 && res.data.code == 200) {
                    $eb.$swal('success',res.data.msg);
                    _this.parents('tr').remove();
                }else
                    return Promise.reject(res.data.msg || '删除失败')
            }).catch(function(err){
                $eb.$swal('error',err);
            });
        })
    });
    $(document).on('click',".open_image",function (e) {
        var image = $(this).data('image');
        $eb.openImage(image);
    });
    $('#rec').click(function(){
        var ids = layList.getCheckData().getIds('id');
        if(ids==''){
            layList.msg('请选择推荐的活动');
            return;
        }
        $eb.createModalFrame('推荐',layList.U({a:'rec',q:{ids:ids.join()}}),{h:400,w:600})
    })

    $('#unrec').click(function(){
        var ids = layList.getCheckData().getIds('id');
        if(ids==''){
            layList.msg('请选择取消推荐的活动');
            return;
        }
        $.ajax({
            type: "post",
            url: "{:url('unrec')}",
            data: {ids:ids.join()},
            dataType: "json",
            success: function (res) {
                layList.msg(res.msg);
                layList.reload();   
            }
        });
    })
    $('body').on('blur','.sort',function(){
        var val = $(this).val();
        if(val==''){
            layList.msg('请填写排序');
            return; 
        } 
        if(typeof(parseInt(val))!='number'){
            layList.msg('请输入一个数字');
            return; 
        }
        if(val<0){
            layList.msg('请输入大于0的数字');
            return; 
        }
        var tenant_id = $(this).attr('tenant_id');
        $.ajax({
            type: "post",
            url: "{:url('sort')}",
            data: {sort:val,id:tenant_id},
            dataType: "json",
            success: function (res) {
                if(res.code==200){
                    layList.msg('操作成功'); 
                }else{
                    layList.msg('操作失败');
                }
            }
        });
    })
</script>
{/block}
