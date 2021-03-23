{extend name="public/container"}
{block name="content"}
<div class="layui-row layui-col-space15"  id="app">
<table class="layui-hide" id="List" lay-filter="List"></table>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
<script>
    //实例化form
    layList.form.render();
    //加载列表
    layList.tableList('List',"{:Url('record_ist',['product_id'=>$product_id,'unique'=>$unique])}",function (){
        return [
            {field: 'id', title: 'ID', width:'10%'},
            {field: 'add_time', title: '日期'},
            {field: 'pm_text', title: '类型',width:'10%'},
            {field: 'number', title: '数量',width:'15%'},
            {field: 'type_text', title: '来源',width:'20%'},

        ];
    })
  
</script>
{/block}
