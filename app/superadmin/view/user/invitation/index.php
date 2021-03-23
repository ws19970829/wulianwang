{extend name="public/container"}
{block name="head_top"}

{/block}
{block name="content"}
<div class="layui-fluid" style="background: #fff;margin-top: -10px;">
<div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">搜索条件</div>
                <div class="layui-card-body">
                    <form class="layui-form layui-form-pane" action="">
                        <div class="layui-form-item">
<!--                            <div class="layui-inline">-->
<!--                                <label class="layui-form-label">所有分类</label>-->
<!--                                <div class="layui-input-block">-->
<!--                                    <select name="is_show">-->
<!--                                        <option value="">是否显示</option>-->
<!--                                        <option value="1">显示</option>-->
<!--                                        <option value="0">不显示</option>-->
<!--                                    </select>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="layui-inline">-->
<!--                                <label class="layui-form-label">所有分类</label>-->
<!--                                <div class="layui-input-block">-->
<!--                                    <select name="pid">-->
<!--                                        <option value="">所有菜单</option>-->
<!--                                        <option value="">----</option>-->
<!--                                    </select>-->
<!--                                </div>-->
<!--                            </div>-->
                            <div class="layui-inline">
                                <label class="layui-form-label">手机号</label>
                                <div class="layui-input-block">
                                    <input type="text" name="phone" class="layui-input" placeholder="请输入手机号">
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
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <!--                <div class="layui-card-header">门店列表</div>-->
                <div class="layui-card-body">
                    <div class="layui-btn-container">
                        <button class="layui-btn layui-btn-sm"
                                onclick="$eb.createModalFrame(this.innerText,'{:Url('addGroup')}',{h:300,w:420})">添加
                        </button>
                    </div>
                    <table class="layui-hide" id="List" lay-filter="List"></table>

                    <script type="text/html" id="act">
                    <button type="button" class="layui-btn layui-btn-xs layui-btn-normal" lay-event='message'>
                            发送通知
                        </button>
                        <button type="button" class="layui-btn layui-btn-xs layui-btn-normal" lay-event='del'>
                            删除
                        </button>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
{/block}
{block name="script"}
<script>
    layList.tableList('List', "{:Url('groupList')}", function () {
        return [
            {field: 'id', title: 'ID', sort: true, event: 'id', width: '10%'},
            {field: 'phone', title: '手机号'},
            {field: 'code', title: '邀请码', width: '10%'},
            {field: 'add_time', title: '发放时间', width: '20%'},
            {field: 'is_submit', title: '是否注册', width: '10%',templet:function(d){
                if(d.is_submit){
                    return '是'
                }else{
                    return '否'
                }
            }},
            {field: 'right', title: '操作', align: 'center', toolbar: '#act', width: '20%'},
        ];
    });
    //查询
    layList.search('search',function(where){
        layList.reload(where,true);
    });
    //点击事件绑定
    layList.tool(function (event, data, obj) {
        switch (event) {
            case 'del':
                var url = layList.U({c: 'user.invitation', a: 'delete', q: {id: data.id}});
                var code = {title: "操作提示", text: "确定删除该记录？", type: 'info', confirm: '是的，删除'};
                $eb.$swal('delete', function () {
                    $eb.axios.get(url).then(function (res) {
                        if (res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success', res.data.msg);
                            obj.del();
                            location.reload();
                        } else
                            return Promise.reject(res.data.msg || '删除失败')
                    }).catch(function (err) {
                        $eb.$swal('error', err);
                    });
                }, code)
                break;
            case 'open_image':
                $eb.openImage(data.image);
                break;
            case 'edit':
                $eb.createModalFrame(data.group_name + '-编辑', layList.U({a: 'addGroup', q: {id: data.id}}), {h: 250, w: 720});
                break;
                case 'message':
            var url = "{:url('send_message')}?id="+data.id
                $.get(url, {},
                    function (data, textStatus, jqXHR) {
                        if(data.code==200){
                            layList.layer.msg('发送成功');
                        }else{
                            layList.layer.msg('发送失败');
                        }
                    },
                    "json"
                );
                break;
        }
    })
</script>
{/block}