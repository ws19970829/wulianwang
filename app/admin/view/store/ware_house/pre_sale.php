{extend name="public/container"}
{block name="head_top"}

{/block}
{block name="content"}
<style>
    .notice {
        font-size: 12px;
        color: red;
    }

    .layui-form-item {
        margin-top: 20px
    }

    .layui-fluid {
        padding: 20px 0 20px 20px
    }

    .layui-form input[type="radio"],
    .layui-form select {
        display: inline-block;
    }
</style>
<div class="layui-fluid" style="background: #fff;margin-top: -10px;">
    <div class="layui-row layui-col-space15">
        <form class="layui-form">
            <input type="hidden" name="product_id" value="{$data.product_id}">
            <input type="hidden" name="unique" value="{$data.unique}">
            <div class="layui-form-item">
                <label class="layui-form-label">开启预售</label>
                <div class="layui-input-block">
                    否 <input type="radio" name="advance_sale" value="0" {if $data.advance_sale==0}checked{/if}> &nbsp; &nbsp; 是 <input type="radio" name="advance_sale" value="1" {if $data.advance_sale==1}checked{/if}> </div> </div> 
                    <!-- <div class="layui-form-item">
                    <label class="layui-form-label">预售日期</label>
                    <div class="layui-input-inline">
                        <span class="notice">注：app中不做展示</span>
                        <input type="text" class="layui-input" id="test11" placeholder="yyyy年MM月dd日" autocomplete="off" name="advance_day" value="{$data.advance_day}">
                    </div>
                </div> -->
                <div class="layui-form-item">
                    <label class="layui-form-label">发货天数</label>
                    <div class="layui-input-inline">
                        <span class="notice">注：在商品选择规格时做展示</span>
                        <input type="number" name="advance_time" placeholder="请输入天数(阿拉伯数字)" class="layui-input" value="{$data.advance_time}">
                    </div>
                </div>

                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <button type="submit" class="layui-btn" lay-submit="" lay-filter="demo1">修改</button>
                    </div>
                </div>
        </form>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
{/block}
{block name="script"}
<script>
    var laydate = layList.laydate;
    var form = layList.form;
    var layer = layList.layer;
    layList.tableList('List', "{:Url('groupList')}", function() {
        return [{
                field: 'id',
                title: 'ID',
                sort: true,
                event: 'id',
                width: '20%'
            },
            {
                field: 'group_name',
                title: '分组名称',
                width: '60%'
            },
            {
                field: 'right',
                title: '操作',
                align: 'center',
                toolbar: '#act',
                width: '20%'
            },
        ];
    });

    //自定义格式
    laydate.render({
        elem: '#test11',
        format: 'yyyy-MM-dd',
        trigger: 'click'
    });

    form.on('submit(demo1)', function({
        field
    }) {

        var advance_day = field.advance_day||'';
        var advance_sale = field.advance_sale;
        var advance_time = field.advance_time;
        var product_id = field.product_id;
        var unique = field.unique;
        if (advance_sale) {
            // if (!advance_day) {
            //     layer.msg('请填写预售日期');
            //     return false;
            // }
            if (!advance_time) {
                layer.msg('请填写发货天数');
                return false;
            }
        }

        $.ajax({
            type: "post",
            url: "{:url('pre_sale')}",
            data: {
                advance_day,
                advance_sale,
                advance_time,
                product_id,
                unique
            },
            dataType: "json",
            success: res => {
                if (res.status == 200) {
                    layer.msg('修改成功', () => {
                        window.parent.location.reload();
                    });
                } else {
                    layer.msg('修改失败', () => {
                        window.parent.location.reload();
                    });
                }
            }
        });
        return false;
    });

    //点击事件绑定
    layList.tool(function(event, data, obj) {
        switch (event) {
            case 'del':
                var url = layList.U({
                    c: 'user.user_group',
                    a: 'delete',
                    q: {
                        id: data.id
                    }
                });
                var code = {
                    title: "操作提示",
                    text: "确定删除该分组？",
                    type: 'info',
                    confirm: '是的，删除'
                };
                $eb.$swal('delete', function() {
                    $eb.axios.get(url).then(function(res) {
                        if (res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success', res.data.msg);
                            obj.del();
                            location.reload();
                        } else
                            return Promise.reject(res.data.msg || '删除失败')
                    }).catch(function(err) {
                        $eb.$swal('error', err);
                    });
                }, code)
                break;
            case 'open_image':
                $eb.openImage(data.image);
                break;
            case 'edit':
                $eb.createModalFrame(data.group_name + '-编辑', layList.U({
                    a: 'addGroup',
                    q: {
                        id: data.id
                    }
                }), {
                    h: 250,
                    w: 720
                });
                break;
        }
    })
</script>
{/block}