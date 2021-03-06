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
                                <label class="layui-form-label">账户</label>
                                <div class="layui-input-block">
                                    <input type="text" name="account" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">订单号</label>
                                <div class="layui-input-block">
                                    <input type="text" name="order_id" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">时间范围</label>
                                <div class="layui-input-inline" style="width: 200px;">
                                    <input type="text" name="start_time" placeholder="开始时间" id="start_time" class="layui-input">
                                </div>
                                <div class="layui-form-mid">-</div>
                                <div class="layui-input-inline" style="width: 200px;">
                                    <input type="text" name="end_time" placeholder="结束时间" id="end_time" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">筛选类型</label>
                                <div class="layui-input-block">
                                    <select name="type">
                                        <option value=" ">全部</option>
                                        {volist name='selectList' id='val'}
                                        <option value="{$val.type}">{$val.title}</option>
                                        {/volist}
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <div class="layui-input-inline">
                                    <button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit="search" lay-filter="search">
                                        <i class="layui-icon layui-icon-search"></i>搜索</button>
                                    <!-- <button class="layui-btn layui-btn-primary layui-btn-sm export"  lay-submit="export" lay-filter="export">
                                        <i class="fa fa-floppy-o" style="margin-right: 3px;"></i>导出</button> -->
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">资金监控日志</div>
                <div class="layui-card-body">
                    <table class="layui-hide" id="userList" lay-filter="userList"></table>
                    <script type="text/html" id="number">
                        {{#  if(d.pm ==0 && d.type !='share'){ }}
                        <span style="color:#FF5722">-{{d.number}}</span>
                        {{# }else if(d.pm && d.type != 'share'){ }}
                        <span style="color:#009688">{{d.number}}</span>
                        {{# }else{ }}
                        <span style="color:#009688">0</span>
                        {{# } }}
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
<script>
    layList.form.render();
    layList.date({elem:'#start_time',theme:'#393D49',type:'datetime'});
    layList.date({elem:'#end_time',theme:'#393D49',type:'datetime'});
    layList.tableList('userList',"{:Url('billlist')}",function () {
        return [
            {field: 'number', title: '金额',sort:true,templet:'#number',align:"center",width:"10%"},
            {field: 'title', title: '类型',align:"center",width:"10%"},
            {field: 'mark', title: '备注',align:"center"},
            {field: 'add_time', title: '创建时间',align:"center",width:"16%"},
            {field: 'uid', title: '会员ID', sort: true,event:'uid',align:"center",width:"10%"},
            {field: 'account', title: '账户' ,align:"center",width:"16%"},
            {field: 'order_id', title: '订单号' ,align:"center",width:"16%"},
        ];
    });
    layList.search('search',function(where){
        if(where.start_time!=''){
            if(where.end_time==''){
                layList.msg('请选择结束时间');
                return;
            }
        }
        if(where.end_time!=''){
            if(where.start_time==''){
                layList.msg('请选择开始时间');
                return;
            }
        }
        layList.reload(where,true);
    });
    layList.search('export',function(where){
        location.href=layList.U({a:'save_bell_export',q:{type:where.type,start_time:where.start_time,end_time:where.end_time,account:where.account,order_id:where.order_id}});
    });
</script>
{/block}