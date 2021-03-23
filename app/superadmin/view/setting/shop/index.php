{extend name="public/container"}
{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15" id="app">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">搜索条件</div>
                <div class="layui-card-body">
                    <form class="layui-form layui-form-pane" action="">
                        <div class="layui-form-item">
                            <div class="layui-inline">
                                <label class="layui-form-label">名称</label>
                                <div class="layui-input-block">
                                    <input type="text" name="name" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <div class="layui-input-inline">
                                    <button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit="search" lay-filter="search">
                                        <i class="layui-icon layui-icon-search"></i>搜索</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">商家数据统计</div>
             
                <div class="layui-card-body">
                <button type="button" class="layui-btn layui-btn-sm" id='rec'>推荐</button>
                        <button type="button" class="layui-btn layui-btn-sm" id='unrec'>取消推荐</button>
                    <table class="layui-hide" id="shopList" lay-filter="userList"></table>
                    <script type="text/html" id="order">
                        <button type="button" class="layui-btn layui-btn-xs layui-btn-normal order" oid={{d.id}}>查看详情</button>
                    </script>
                    <script type="text/html" id="goods">
                        <button type="button" class="layui-btn layui-btn-xs layui-btn-normal goods" gid={{d.id}}>查看详情</button>
                    </script>
                    <script type="text/html" id="seckill">
                        <button type="button" class="layui-btn layui-btn-xs layui-btn-normal seckill" sid={{d.id}}>查看详情</button>
                    </script>
                    <script type="text/html" id="com">
                        <button type="button" class="layui-btn layui-btn-xs layui-btn-normal com" cid={{d.id}}>查看详情</button>
                    </script>
                    <script type="text/html" id="cate">
                        <button type="button" class="layui-btn layui-btn-xs layui-btn-normal cate" cid={{d.id}}>查看详情</button>
                    </script>
                    <script type="text/html" id="usable_cate">
                        <button type="button" class="layui-btn layui-btn-xs layui-btn-normal usable_cate" cid={{d.id}}>设置</button>
                    </script>
                    <script type="text/html" id="sort">
                      <input type="number" name="title" lay-verify="number" autocomplete="off"  class="layui-input sort" min='0' value='{{d.sort}}' tenant_id = '{{d.id}}'>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
<script>
    layList.form.render();
    layList.tableList('shopList', "{:Url('list')}", function() {
        return [
            {type:'checkbox'},
            {
                field: 'real_name',
                title: '名称',
                align: "center",
                width: "15%"
            },
            {
                field: 'sort',
                title: '排序',
                align: "center",
                width: "6%",
                templet:'#sort'
            },
            {
                field: 'order_num',
                title: '订单金额',
                sort: true,
                align: "center",
                width: "10%"
            },
            {
                field: 'goods_num',
                title: '商品数量',
                sort: true,
                align: "center",
                width: "8%"
            },
            {
                title: '订单信息',
                templet: "#order",
                width: '8%'
            },
            {
                title: '商品信息',
                templet: "#goods",
                width: '8%'
            },
            {
                title: '可用类别',
                templet: "#usable_cate",
                width: '8%',
                align: "center"
            },
            {
                title: '秒杀信息',
                templet: "#seckill",
                width: '8%'
            },
            {
                title: '拼团信息',
                templet: "#com",
                width: '8%'
            },
           
            { title: '推荐状态',align: "center",templet:function(d){
                        if(d.is_rec==1){
                            return '已推荐：<br/>'+ d.rec_start+'<br/>'+d.rec_end;
                        }else{
                            return '未推荐'
                        }
             }},
        ];
    });
    layList.search('search', function(where) {
        layList.reload(where, true);
    });
    layList.search('export', function(where) {
        location.href = layList.U({
            a: 'save_bell_export',
            q: {
                type: where.type,
                start_time: where.start_time,
                end_time: where.end_time,
                account: where.account,
                order_id: where.order_id
            }
        });
    });

    $('body').on('click', '.order', function() {
        var oid = $(this).attr('oid');
        location.href = `{:url('order.store_order/index')}?tenant_id=${oid}`
    })
    $('body').on('click', '.goods', function() {
        var gid = $(this).attr('gid');
        location.href = `{:url('store.store_product/index')}?tenant_id=${gid}`
    })
    $('body').on('click', '.seckill', function() {
        var sid = $(this).attr('sid');
        location.href = `{:url('ump.store_seckill/index')}?tenant_id=${sid}`
    })
    $('body').on('click', '.com', function() {
        var cid = $(this).attr('cid');
        location.href = `{:url('ump.store_combination/index')}?tenant_id=${cid}`
    })
    $('body').on('click', '.cate', function() {
        var cid = $(this).attr('cid');
        location.href = `{:url('store.store_category/index')}?tenant_id=${cid}`
    })
    $('body').on('click', '.usable_cate', function() {
        var cid = $(this).attr('cid');
        $eb.createModalFrame('设置类别',layList.U({a:'cate_usable',q:{tenant_id:cid}}),{h:500,w:600})
    })
    $('#rec').click(function(){
        var ids = layList.getCheckData().getIds('id');
        if(ids==''){
            layList.msg('请选择推荐的商家');
            return;
        }
        $eb.createModalFrame('推荐',layList.U({a:'rec',q:{ids:ids.join()}}),{h:400,w:600})
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

    $('#unrec').click(function(){
        var ids = layList.getCheckData().getIds('id');
        if(ids==''){
            layList.msg('请选择取消推荐的商家');
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

</script>
{/block}