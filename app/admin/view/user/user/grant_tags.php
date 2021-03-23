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
<!--                <div class="row">-->
<!--                    <div class="col-sm-8 m-b-xs">-->
<!--                        <form action="" class="form-inline">-->
<!--                            <i class="fa fa-search" style="margin-right: 10px;"></i>-->
<!--                            <div class="input-group" style="width: 80%">-->
<!--                                <input type="text" name="title" value="{$where.title}" placeholder="请输入标签名称" class="input-sm form-control"> <span class="input-group-btn">-->
<!--                                    <button type="submit" class="btn btn-sm btn-primary"> 搜索</button> </span>-->
<!--                            </div>-->
<!--                        </form>-->
<!--                    </div>-->
<!--                </div>-->
                <div class="table-responsive">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>
                            <th class="text-center">ID</th>
                            <th class="text-center">标签名称</th>
                            <th class="text-center">是否自动标签</th>
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
                                {$vo.title}
                            </td>
                            <td class="text-center">
                                {$vo.is_auto}
                            </td>
                            <td class="text-center">
                                <button class="btn btn-primary btn-xs grant" data-url="{:Url('user.user/save_add_tags',array('id'=>$vo['id'],'uid'=>$uid))}" type="button"><i class="fa  fa-arrow-circle-o-right"></i> 添加
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
            text:"您确定要添加标签吗？",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText:"是的，我要添加！",
            cancelButtonText:"让我再考虑一下…",
            closeOnConfirm: false,
            closeOnCancel: false
        }).then(function(){
            $eb.axios.get(url).then(function(res){
                if(res.status == 200 && res.data.code == 200) {
                    swal(res.data.msg);
                }else
                    return Promise.reject(res.data.msg || '添加失败')
            }).catch(function(err){
                swal(err);
            });
        }).catch(console.log);
    });
</script>
{/block}
