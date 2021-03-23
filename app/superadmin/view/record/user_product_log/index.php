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
                                <label class="layui-form-label">产品名称</label>
                                <div class="layui-input-block">
                                    <input type="text" name="cate_name" class="layui-input" placeholder="请输入分类名称">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">选择时间：</label>
                                <div class="layui-input-inline">
                                    <input type="text" class="layui-input time-w" name="user_time" lay-verify="user_time"  id="user_time" placeholder=" - ">
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
        <!--产品列表-->
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">商品分析列表</div>
                <div class="layui-card-body">
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <script type="text/html" id="store_name">
                        {{d.store_name}}
                        <p style="color:#dab176">￥{{d.price}}</p>
                    </script>
                    <script type="text/html" id="pic">
                        {{# if(d.image){ }}
                        <img style="cursor: pointer" lay-event='open_image' src="{{d.image}}">
                        {{# }else{ }}
                        暂无图片
                        {{# } }}
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
    setTimeout(function () {
        $('.alert-info').hide();
    },3000);
    //实例化form
    layList.form.render();
    //加载列表
    layList.tableList('List',"{:Url('get_user_product_log_list')}",function (){
        return [
            {field: 'pic', title: '商品图',templet:'#pic',align:'center'},
            {field: 'store_name', title: '商品名称',templet:'#store_name',align:'center'},
            {field: 'exposure_times', title: '曝光次数',sort: true,event:'sort',edit:'sort',width:'8%',align:'center'},
            {field: 'exposure_number', title: '曝光人数',sort: true,event:'sort',edit:'sort',width:'8%',align:'center'},
            {field: 'visitor_number', title: '访客数',sort: true,event:'sort',edit:'sort',width:'8%',align:'center'},
            {field: 'views_number', title: '浏览量',sort: true,event:'sort',edit:'sort',width:'8%',align:'center'},
            {field: 'cart_user_num', title: '加购人数',sort: true,event:'sort',edit:'sort',width:'8%',align:'center'},
            {field: 'cart_num', title: '加购件数',sort: true,event:'sort',edit:'sort',width:'8%',align:'center'},
            {field: 'pay_number', title: '支付人数',sort: true,event:'sort',edit:'sort',width:'8%',align:'center'},
            {field: 'conversion_rate', title: '转化率',sort: true,event:'sort',edit:'sort',width:'8%',align:'center'},
        ];
    });
    layList.date('user_time');
    //自定义方法
    var action= {
        set_category: function (field, id, value) {
            layList.baseGet(layList.Url({
                c: 'store.store_category',
                a: 'set_category',
                q: {field: field, id: id, value: value}
            }), function (res) {
                layList.msg(res.msg);
            });
        },
    }
    //查询
    layList.search('search',function(where){
        layList.reload(where,true);
    });
    layList.switch('is_show',function (odj,value) {
        if(odj.elem.checked==true){
            layList.baseGet(layList.Url({c:'store.store_category',a:'set_show',p:{is_show:1,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }else{
            layList.baseGet(layList.Url({c:'store.store_category',a:'set_show',p:{is_show:0,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }
    });
    layList.search('search',function(where){
        if(where['user_time_type'] != '' && where['user_time'] == '') return layList.msg('请选择选择时间');
        if(where['user_time_type'] == '' && where['user_time'] != '') return layList.msg('请选择访问情况');
        layList.reload(where,true);
    });
    //快速编辑
    layList.edit(function (obj) {
        var id=obj.data.id,value=obj.value;
        switch (obj.field) {
            case 'cate_name':
                action.set_category('cate_name',id,value);
                break;
            case 'sort':
                action.set_category('sort',id,value);
                break;
        }
    });
    //监听并执行排序
    layList.sort(['id','sort'],true);
    //点击事件绑定
    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'delstor':
                var url=layList.U({c:'store.store_category',a:'delete',q:{id:data.id}});
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
            case 'open_image':
                $eb.openImage(data.image);
                break;
        }
    })
</script>
{/block}
