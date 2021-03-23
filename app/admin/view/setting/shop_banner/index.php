{extend name="public/container"}
{block name="content"}
<style>
    .ibox-title{
        border-width:0;
    }
</style>
<div class="row">
    <div class="col-sm-12">

        <div class="ibox float-e-margins">

            <div class="tabs-container ibox-title  gray-bg">
                <ul class="nav nav-tabs">

                </ul>

                <div class="ibox-content">
                    <div class="ibox-title">
                        <button type="button" class="btn btn-w-m btn-primary" onclick="$eb.createModalFrame(this.innerText,'{:Url('create')}')">添加数据</button>
                        <div class="ibox-tools">

                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped  table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center">编号</th>
                                <th class="text-center">图片</th>
                                <th class="text-center">外链</th>
                                <th class="text-center">排序</th>
                                <th class="text-center">操作</th>
                            </tr>
                            </thead>
                            <tbody class="">
                            {volist name="list" id="vo"}
                            <tr>
                                <td class="text-center">
                                    {$vo.id}
                                </td>
                                <td class="text-center">
                                    {$vo.img}
                                </td>
                                <td class="text-center">
                                    {$vo.url}
                                </td>
                                <td class="text-center">
                                    {$vo.sort}
                                </td>

                                <td class="text-center">
                                    <button class="btn btn-info btn-xs" type="button"  onclick="$eb.createModalFrame('编辑','{:Url('edit',array('id'=>$vo['id']))}')"><i class="fa fa-edit"></i> 编辑</button>
                                    <button class="btn btn-danger btn-xs " data-url="{:Url('delete',array('id'=>$vo['id']))}" type="button"><i class="fa fa-times"></i> 删除</button>
                                </td>
                            </tr>
                            {/volist}
                            </tbody>
                        </table>
                    </div>
                    {//include file="public/inner_page"}
                </div>

            </div>
        </div>
    </div>
</div>
{/block}
{block name="script"}
<script>
    $('.btn-danger').on('click',function(){
        window.t = $(this);
        var _this = $(this),url =_this.data('url');
        $eb.$swal('delete',function(){
            $eb.axios.get(url).then(function(res){
                console.log(res);
                if(res.status == 200 && res.data.code == 200) {
                    $eb.$swal('success',res.data.msg);
                    _this.parents('tr').remove();
                }else
                    return Promise.reject(res.data.msg || '删除失败')
            }).catch(function(err){
                $eb.$swal('error',err);
            });
        })
    });
    $(".image").on('click',function (e) {
        var images = $(this).data('image');
        $eb.openImage(images);
    })
</script>
{/block}
