{extend name="public/container"}
{block name="content"}

<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">统计计划：<span style="color: red">{$plan_name}</span></div>
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
<!--                                    <select name="type">-->
<!--                                        <option value="">所有计划</option>-->
<!--                                        <option value="1">互动粉丝转化</option>-->
<!--                                        <option value="2">自定义人群</option>-->
<!--                                        <option value="3">未消费新增人群</option>-->
<!--                                    </select>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="layui-inline">-->
<!--                                <label class="layui-form-label">标题</label>-->
<!--                                <div class="layui-input-block">-->
<!--                                    <input type="text" name="title" class="layui-input" placeholder="请输入标题">-->
<!--                                </div>-->
<!--                            </div>-->
                            <div class="layui-inline">
                                <label class="layui-form-label">日期筛选</label>
                                <div class="layui-input-inline" style="width: 200px;">
                                    <input type="text" name="start_time" id="start_time" value="" placeholder="开始时间" autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid">-</div>
                                <div class="layui-input-inline" style="width: 200px;">
                                    <input type="text" name="end_time" id="end_time" value=""  placeholder="结束时间" autocomplete="off" class="layui-input">
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
        <div class="layui-col-sm3 layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header">
                    通知人数
                    <span class="layui-badge layuiadmin-badge layui-bg-blue">人</span>
                </div>
                <div class="layui-card-body">
                    <p class="layuiadmin-big-font">{$notice_user_num}</p>
                    <p>
                        微信消息推送到的人数
                        <!--<span class="layuiadmin-span-color">0<i class="fa fa-bar-chart"></i></span>-->
                    </p>
                </div>
            </div>
        </div>
        <div class="layui-col-sm3 layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header">
                    访客数
                    <span class="layui-badge layuiadmin-badge layui-bg-blue">人</span>
                </div>
                <div class="layui-card-body">
                    <p class="layuiadmin-big-font">{$read_user_num}</p>
                    <p>
                        接到推送后，进入页面的人数
                        <!--<span class="layuiadmin-span-color">0<i class="fa fa-bar-chart"></i></span>-->
                    </p>
                </div>
            </div>
        </div>
        <div class="layui-col-sm3 layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header">
                    付款人数
                    <span class="layui-badge layuiadmin-badge layui-bg-blue">人</span>
                </div>
                <div class="layui-card-body">
                    <p class="layuiadmin-big-font">{$pay_user_num}</p>
                    <p>
                        进入页面后付款的人数
                        <!--<span class="layuiadmin-span-color">0<i class="fa fa-bar-chart"></i></span>-->
                    </p>
                </div>
            </div>
        </div>
        <div class="layui-col-sm3 layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header">
                    付款订单数
                    <span class="layui-badge layuiadmin-badge layui-bg-blue">个</span>
                </div>
                <div class="layui-card-body">
                    <p class="layuiadmin-big-font">{$pay_order_num}</p>
                    <p>
                        付款的订单数量
                        <!--<span class="layuiadmin-span-color">0<i class="fa fa-bar-chart"></i></span>-->
                    </p>
                </div>
            </div>
        </div>
        <div class="layui-col-sm3 layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header">
                    付款金额
                    <span class="layui-badge layuiadmin-badge layui-bg-blue">元</span>
                </div>
                <div class="layui-card-body">
                    <p class="layuiadmin-big-font">{$pay_money}</p>
                    <p>
                        付款总金额
                        <!--<span class="layuiadmin-span-color">0<i class="fa fa-bar-chart"></i></span>-->
                    </p>
                </div>
            </div>
        </div>
        <div class="layui-col-sm3 layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header">
                    通知-访问转换率
                </div>
                <div class="layui-card-body">
                    <p class="layuiadmin-big-font">{$notify_read_rate}%</p>
                    <p>
                        访客数/通知人数
                        <!--<span class="layuiadmin-span-color">0<i class="fa fa-bar-chart"></i></span>-->
                    </p>
                </div>
            </div>
        </div>
        <div class="layui-col-sm3 layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header">
                    通知-付款转换率
                </div>
                <div class="layui-card-body">
                    <p class="layuiadmin-big-font">{$notify_pay_rate}%</p>
                    <p>
                        付款人数/通知人数
                        <!--<span class="layuiadmin-span-color">0<i class="fa fa-bar-chart"></i></span>-->
                    </p>
                </div>
            </div>
        </div>
        <div class="layui-col-sm3 layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header">
                    访问-付款转换率
                </div>
                <div class="layui-card-body">
                    <p class="layuiadmin-big-font">{$read_pay_rate}%</p>
                    <p>
                        付款人数/访客数
                        <!--<span class="layuiadmin-span-color">0<i class="fa fa-bar-chart"></i></span>-->
                    </p>
                </div>
            </div>
        </div>


        <!--产品列表-->
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">营销数据汇总(仅展示有推送发生的日期)</div>
                <div class="layui-card-body">
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <script type="text/html" id="pic">
                        {{# if(d.pic){ }}
                        <img style="cursor: pointer" lay-event='open_image' src="{{d.pic}}">
                        {{# }else{ }}
                        暂无图片
                        {{# } }}
                    </script>
                    <script type="text/html" id="is_show">
                        <input type='checkbox' name='id' lay-skin='switch' value="{{d.id}}" lay-filter='is_show' lay-text='显|隐'  {{ d.is_show == 1 ? 'checked' : '' }}>
                    </script>
                    <script type="text/html" id="status">
                        <input type='checkbox' name='id' lay-skin='switch' value="{{d.id}}" lay-filter='status' lay-text='启用|终止'  {{ d.status == 1 ? 'checked' : '' }}>
                    </script>
                    <script type="text/html" id="pid">
                        <a href="{:Url('index')}?pid={{d.id}}">查看</a>
                    </script>
                    <script type="text/html" id="act">
<!--                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('编辑','{:Url('create')}?id={{d.id}}')">-->
                        <button  class="layui-btn layui-btn-xs"><i class="fa fa-line-chart"></i>数据</button>
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('编辑','{:Url('create')}?id={{d.id}}',{w:900,h:760})">
                            <i class="fa fa-edit"></i> 编辑
                        </button>
                        <button class="layui-btn btn-danger layui-btn-xs" lay-event='delstor'>
                            <i class="fa fa-times"></i> 删除
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
    layList.date({elem:'#start_time',theme:'#0092DC',type:'date'});
    layList.date({elem:'#end_time',theme:'#0092DC',type:'date'});

//    setTimeout(function () {
//        $('.alert-info').hide();
//    },3000);
    //实例化form
    layList.form.render();
    //加载列表
    layList.tableList('List',"{:Url('data_list')}",function (){
        return [
//            {field: 'id', title: 'ID', sort: true,event:'id',width:'4%',align:'center'},
//            {field: 'image', title: '封面图片', event:'image', width: '12%',align:'center', templet: '<p lay-event="image"><img class="avatar" style="cursor: pointer" class="open_image" data-image="{{d.image}}" src="{{d.image}}" alt="{{d.title}}"></p>'},
            {field: 'send_date', title: '推送日期',align:'center'},
            {field: 'notice_user_num', title: '通知人数',align:'center'},
            {field: 'read_user_num', title: '访客数',align:'center'},
            {field: 'order_user_num', title: '下单人数',align:'center'},
            {field: 'order_num', title: '下单笔数',align:'center'},
            {field: 'order_money', title: '下单金额',align:'center'},
            {field: 'pay_user_num', title: '付款人数',align:'center'},
            {field: 'pay_order_num', title: '付款订单数',align:'center'},
            {field: 'pay_money', title: '付款金额',align:'center'},
            {field: 'notify_read_rate', title: '通知-访问转换率',align:'center'},
            {field: 'notify_pay_rate', title: '通知-付款转换率',align:'center'},
            {field: 'read_pay_rate', title: '访问-付款转换率',align:'center'},

//            {field: 'start_time_text', title: '计划开始时间',align:'center'
//                ,templet:function (d) {
//                 return d.is_long?'':d.start_time_text;
//                }
//            },

//            {field: 'status_text', title: '状态',align:'center'},

//            {field: 'sort', title: '排序',sort: true,event:'sort',edit:'sort',width:'8%',align:'center'},
//            {field: 'right', title: '操作',align:'center',toolbar:'#act',width:'18%',align:'center'},
        ];
    });
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
    layList.switch('status',function (odj,value) {
        if(odj.elem.checked==true){
            layList.baseGet(layList.Url({c:'fans.fans_data',a:'set_status',p:{status:1,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }else{
            layList.baseGet(layList.Url({c:'fans.fans_data',a:'set_status',p:{status:0,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }
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
                var url=layList.U({c:'fans.fans_data',a:'delete',q:{id:data.id}});
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
                $eb.openImage(data.pic);
                break;
        }
    })
</script>
{/block}
