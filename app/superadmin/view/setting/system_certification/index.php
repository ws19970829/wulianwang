{extend name="public/container"}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-title">
                <!--<button type="button" class="btn btn-w-m btn-primary" onclick="$eb.createModalFrame(this.innerText,'{:Url('create')}')">添加商家</button>-->
                <div class="ibox-tools">

                </div>
            </div>
            <div class="ibox-content">
                <div class="row">
                    <div class="m-b m-l">
                        <?php /*  <form action="" class="form-inline">
                              <i class="fa fa-search" style="margin-right: 10px;"></i>
                              <select name="is_show" aria-controls="editable" class="form-control input-sm">
                                  <option value="">是否显示</option>
                                  <option value="1" {eq name="params.is_show" value="1"}selected="selected"{/eq}>显示</option>
                                  <option value="0" {eq name="params.is_show" value="0"}selected="selected"{/eq}>不显示</option>
                              </select>
                              <select name="access" aria-controls="editable" class="form-control input-sm">
                                  <option value="">子管理员是否可用</option>
                                  <option value="1" {eq name="params.access" value="1"}selected="selected"{/eq}>可用</option>
                                  <option value="0" {eq name="params.access" value="0"}selected="selected"{/eq}>不可用</option>
                              </select>
                          <div class="input-group">
                              <input type="text" name="keyword" value="{$params.keyword}" placeholder="请输入关键词" class="input-sm form-control"> <span class="input-group-btn">
                                      <button type="submit" class="btn btn-sm btn-primary"> 搜索</button> </span>
                          </div>
                          </form>  */ ?>
                        <form action="" class="form-inline">

                            <!--                            <select name="roles" aria-controls="editable" class="form-control input-sm">-->
                            <!--                                <option value="">身份</option>-->
                            <!--                                {volist name="$role" id="vo" key="k"}-->
                            <!--                                 <option value="{$key}" {eq name="where.roles" value="$key"}selected="selected"{/eq}>{$vo}</option>-->
                            <!--                                {/volist}-->
                            <!--                            </select>-->
                            <input type="hidden" name="roles" value="2">
                            <div class="input-group">
                                <input type="text" name="name" value="{$where.name}" placeholder="请输入姓名或者账号" class="input-sm form-control"> <span class="input-group-btn">
                                    <button type="submit" class="btn btn-sm btn-primary"> <i class="fa fa-search"></i>搜索</button> </span>
                            </div>
                        </form>
                    </div>

                </div>
                <div class="table-responsive">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>
<!--                            <th class="text-center">商家名称</th>-->
                            <th class="text-center">联系人</th>
                            <th class="text-center">联系电话</th>
                            <th class="text-center">通讯地址</th>
                            <th class="text-center">身份证号</th>
                            <th class="text-center">介绍</th>
                            <th class="text-center">状态</th>
                            <th class="text-center">操作</th>
                        </tr>
                        </thead>
                        <tbody class="">
                        {volist name="list" id="vo"}
                        <tr>
<!--                            <td class="text-center">-->
<!--                                {$vo.merchant_name}-->
<!--                            </td>-->
                            <td class="text-center">
                                {$vo.link_user}
                            </td>
                            <td class="text-center">
                                {$vo.link_tel}
                            </td>
                            <td class="text-center">
                                {$vo.addr}
                            </td>
                            <td class="text-center">
                                {$vo.user_card_num}
                            </td>
                            <td class="text-center">
                                {$vo.remark}
                            </td>
                            <td class="text-center">
                                {$vo.status_name }

                            </td>

                            <td class="text-center">
                                <button class="btn btn-info btn-xs" type="button"  onclick="$eb.createModalFrame('查看/审核认证管理','{:Url('edit',array('id'=>$vo['id']))}')"><i class="fa fa-edit"></i> 查看/审核</button>
                                <button class="btn btn-danger btn-xs" data-url="{:Url('delete',array('id'=>$vo['id']))}" type="button"><i class="fa fa-times"></i> 删除
                                </button>
                            </td>
                        </tr>
                        {/volist}
                        </tbody>
                    </table>
                </div>
                {include file="public/inner_page"}
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
</script>
{/block}
