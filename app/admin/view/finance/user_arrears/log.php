{extend name="public/container"}
{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">搜索条件</div>
                <div class="layui-card-body">
                    <form class="layui-form layui-form-pane" action="">
                        <div class="layui-form-item">
        
                            <div class="layui-inline">
                                <label class="layui-form-label">订单号</label>
                                <div class="layui-input-block">
                                    <input type="text" name="order_id" class="layui-input">
                                </div>
                            </div>
                            <!-- <div class="layui-inline">
                                <label class="layui-form-label">时间范围</label>
                                <div class="layui-input-inline" style="width: 200px;">
                                    <input type="text" name="start_time" placeholder="开始时间" id="start_time" class="layui-input">
                                </div>
                                <div class="layui-form-mid">-</div>
                                <div class="layui-input-inline" style="width: 200px;">
                                    <input type="text" name="end_time" placeholder="结束时间" id="end_time" class="layui-input">
                                </div>
                            </div> -->

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

                <div class="layui-card-body">
                    <table class="layui-hide" id="orderList" ></table>
                    
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
<script>
    layList.form.render();
    let data_table = layList.data_table;
    layList.tableList('orderList',"{:Url('log_list')}?id={$id}",function () {
        return [
            {field: 'order_id', title: '订单号' ,align:"center"},
            {field: 'arreas', title: '欠款金额' ,align:"center",width:"16%"},
            {field: 'add_time', title: '创建时间',align:"center",width:"30%"},
        ];
    });
    layList.search('search',function(where){
        // if(where.start_time!=''){
        //     if(where.end_time==''){
        //         layList.msg('请选择结束时间');
        //         return;
        //     }
        // }
        // if(where.end_time!=''){
        //     if(where.start_time==''){
        //         layList.msg('请选择开始时间');
        //         return;
        //     }
        // }
        layList.reload(where,true);
    });
    layList.search('export',function(where){
        location.href=layList.U({a:'save_bell_export',q:{order_id:where.order_id}});
    });

</script>
{/block}