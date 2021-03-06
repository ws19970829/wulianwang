{extend name="public/container"}
{block name="head_top"}
<link href="{__MODULE_PATH}wechat/news/css/index.css" type="text/css" rel="stylesheet">
{/block}
{block name="content"}
<style>
    tr td img{height: 50px;}
</style>
<div class="row">
    <div class="col-sm-12 m-l-n-md">
        <div class="ibox">
            <div class="ibox-title">
                <div class="row">
                    <div class="m-b m-l">
                        <form action="" class="form-inline">

                            <div class="input-group">
                                <input type="text" name="title" value="{$where.title}" placeholder="请输入关键词" class="input-sm form-control"> <span class="input-group-btn"><button type="submit" class="btn btn-sm btn-primary"> <i class="fa fa-search" ></i>搜索</button> </span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="ibox-content">
                <table class="footable table table-striped  table-bordered " data-page-size="20">
                    <thead>
                    <tr>
                        <th class="text-center" width="5%">id</th>
                        <th class="text-left" >标题</th>
                        <th class="text-center" width="15%">添加时间</th>
                        <th class="text-center" width="20%">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    {volist name="list" id="vo"}
                    <tr>
                        <td>{$vo.id}</td>
                        <td>{$vo.title}</td>
                        <td>{$vo.add_time|date="Y-m-d H:i:s"}</td>

                        <td class="text-center">
                            <button style="margin-top: 5px;" class="btn btn-info btn-xs" type="button"  onclick="$eb.createModalFrame('编辑','{:Url('create',array('id'=>$vo['id']))}',{w:1100,h:760})"><i class="fa fa-edit"></i> 编辑</button>
                        </td>
                    </tr>
                    {/volist}
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

{/block}
{block name="script"}
<script>

    $('.del_news_one').on('click',function(){
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

    $('.openWindow').on('click',function () {
        return $eb.createModalFrame('选择产品',$(this).data('url'));
    });

    $('.underline').on('click',function () {
        var url=$(this).data('url');
        $eb.$swal('delete',function(){
            $eb.axios.get(url).then(function(res){
                if(res.status == 200 && res.data.code == 200) {
                    $eb.$swal('success',res.data.msg);
                    window.location.reload();
                }else
                    return Promise.reject(res.data.msg || '取消失败')
            }).catch(function(err){
                $eb.$swal('error',err);
            });
        },{title:'确认取消关联产品？',text:'取消后可再关联页选择产品重新关联',confirm:'确定'})
    })
</script>
{/block}
