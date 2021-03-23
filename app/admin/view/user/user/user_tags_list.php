{extend name="public/container"}
{block name="head_top"}
<script src="{__FRAME_PATH}js/content.min.js"></script>
<script src="{__PLUG_PATH}sweetalert2/sweetalert2.all.min.js"></script>
{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">

                <div class="table-responsive">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>
                            <th class="text-center">ID</th>
                            <th class="text-center">标签名称</th>
                            <th class="text-center">操作</th>
                        </tr>
                        </thead>
                        <tbody class="">
                        {volist name="list" id="vo"}
                        <tr>
                            <td class="text-center">
                                {$vo.tag_id}
                            </td>
                            <td class="text-center">
                                {$vo.tag_title}
                            </td>
                            <td class="text-center">
                                <button class="btn btn-danger btn-xs grant" data-url="{:Url('user.user/del_user_tags',array('id'=>$vo['tag_id'],'uid'=>$uid))}" type="button"><i class="fa fa-times"></i> 删除
                                </button>
                            </td>
                        </tr>
                        {/volist}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
{/block}
{block name="script"}
<script>
    $('.grant').on('click',function(){
        window.t = $(this);
        var _this = $(this),url =_this.data('url');
        swal({
            title: "确认",
            text:"您确定要删除该标签吗？",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText:"是的，我要删除！",
            cancelButtonText:"让我再考虑一下…",
            closeOnConfirm: false,
            closeOnCancel: false
        }).then(function(){
            $eb.axios.get(url).then(function(res){
                if(res.status == 200 && res.data.code == 200) {
                    swal(res.data.msg);
                    window.location.reload();
                }else
                    return Promise.reject(res.data.msg || '删除失败')
            }).catch(function(err){
                swal(err);
            });
        }).catch(console.log);
    });
</script>
{/block}
