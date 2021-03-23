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
                                <label class="layui-form-label">标题</label>
                                <div class="layui-input-block">
                                    <input type="text" name="title" class="layui-input" placeholder="请输入标题">
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
                    累积阅读数
                    <span class="layui-badge layuiadmin-badge layui-bg-blue">次</span>
                </div>
                <div class="layui-card-body">
                    <p class="layuiadmin-big-font">{$view_total}</p>
                    <p>
                        推送的营销笔记的阅读总次数
                        <!--<span class="layuiadmin-span-color">0<i class="fa fa-bar-chart"></i></span>-->
                    </p>
                </div>
            </div>
        </div>
        <div class="layui-col-sm3 layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header">
                    累积引导成交金额(元)
                    <span class="layui-badge layuiadmin-badge layui-bg-blue">元</span>
                </div>
                <div class="layui-card-body">
                    <p class="layuiadmin-big-font">{$pay_money_total}</p>
                    <p>
                        通过营销笔记成交的总金额
                        <!--<span class="layuiadmin-span-color">0<i class="fa fa-bar-chart"></i></span>-->
                    </p>
                </div>
            </div>
        </div>
        <!--产品列表-->
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">店铺笔记</div>
                <div class="layui-card-body">
<!--                    <div class="alert alert-info" role="alert">-->
<!--                        注:点击父级名称可查看子集分类,点击分页首页可返回顶级分类;分类名称和排序可进行快速编辑;-->
<!--                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>-->
<!--                    </div>-->
                    <div class="layui-btn-container">
<!--                        <a class="layui-btn layui-btn-sm" href="{:Url('index')}">分类首页</a>-->
                        <button type="button" class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame(this.innerText,'{:Url('create')}',{w:1100,h:760})">添加笔记</button>
                    </div>
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
                    <script type="text/html" id="pid">
                        <a href="{:Url('index')}?pid={{d.id}}">查看</a>
                    </script>
                    <script type="text/html" id="act">
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('预览','{:Url('/share/index/note')}?id={{d.id}}&is_admin_view=1',{w:400,h:760})">
                            <i class="fa fa-eye"></i> 预览
                        </button>
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('编辑','{:Url('create')}?id={{d.id}}',{w:1100,h:760})">
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
    setTimeout(function () {
        $('.alert-info').hide();
    },3000);
    //实例化form
    layList.form.render();
    //加载列表
    layList.tableList('List',"{:Url('note_list')}",function (){
        return [
            {field: 'id', title: 'ID', sort: true,event:'id',width:'4%',align:'center'},
            {field: 'image', title: '封面图片', event:'image', width: '12%',align:'center', templet: '<p lay-event="image"><img class="avatar" style="cursor: pointer" class="open_image" data-image="{{d.image}}" src="{{d.image}}" alt="{{d.title}}"></p>'},
            {field: 'title', title: '标题',align:'center',width: '18%'},
            {field: 'link', title: '网址',align:'center',width: '18%'},
            {field: 'view_user_num', title: '浏览人数',align:'center'},
            {field: 'view_num', title: '浏览次数',align:'center'},
            {field: 'add_time', title: '创建时间',align:'center'},
//            {field: 'sort', title: '排序',sort: true,event:'sort',edit:'sort',width:'8%',align:'center'},
//            {field: 'is_show', title: '状态',templet:'#is_show',width:'10%',align:'center'},
            {field: 'right', title: '操作',align:'center',toolbar:'#act',width:'18%',align:'center'},
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
                var url=layList.U({c:'fans.fans_note',a:'delete',q:{id:data.id}});
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
