{extend name="public/container"}
{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">搜索条件</div>
                <div class="layui-card-body">
                    <form class="layui-form layui-form-pane" action="">
                        <div class="layui-inline">
                            <label class="layui-form-label">产品名称</label>
                            <div class="layui-input-block">
                                <input type="text" name="title" class="layui-input" placeholder="请输入产品名称,关键字,编号">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <div class="layui-input-inline">
                                <button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit="search" lay-filter="search">
                                    <i class="layui-icon layui-icon-search"></i>搜索</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body">
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <!--图片-->
                    <script type="text/html" id="image">
                        <img style="cursor: pointer" lay-event="open_image" src="{{d.image}}">
                    </script>
                    <!--操作-->
                    <script type="text/html" id="act">
                        <button type="button" class="layui-btn layui-btn-normal layui-btn-sm" lay-event='select'>选择</button>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
{/block}
{block name='script'}
<script>
    var parentinputname = '{$Request.param.fodder}';
    layList.form.render();
    //加载列表
    layList.tableList('List',"{:Url('fans.fans_note/note_list',['type'=>1])}",function (){
        return [
            {field: 'id', title: 'ID', sort: true,event:'id'},
            {field: 'image', title: '笔记图片',templet:'#image'},
            {field: 'title', title: '笔记名称',templet:'#store_name'},
            {field: 'right', title: '操作',align:'center',toolbar:'#act'},
        ]
    });
    //点击事件绑定
    layList.tool(function (event,data,obj) {
        console.log(data)
        switch (event) {
            case 'select':
                console.log(data.id);

                //这里进行ajax传输，把数据传到后台，并存储到缓存中，供父页面轮询，从缓存中获取到用户勾选的内容
                var list = {};
                list.id = data.id;
                list.image = data.image;
                list.title = data.title;
                $.ajax({
                    url:"{:Url('save_cache_note')}",
                    data:list,
                    type:'post',
                    dataType:'json',
                    success:function(re){
                        if(re.code == 200){
                            $eb.closeModalFrame(window.name);
                        }else{
                            $eb.message('error',re.msg);
                        }
                    },
                    error:function () {
                        layer.close(index);
                    }
                })


        }
    })
    //查询
    layList.search('search',function(where){
        layList.reload(where);
    });
</script>
{/block}