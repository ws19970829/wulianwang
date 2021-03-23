{extend name="public/container"}
{block name="content"}
<div class="layui-fluid" style="background: #fff">
    <form class="layui-form" action="">
        {if $daishou==1}
    <blockquote class="layui-elem-quote layui-text">
  注：当前用户选择物流代收
    </blockquote>
        {/if}
        <div class="layui-form-item">
            <label class="layui-form-label">选择类型</label>
            <div class="layui-input-block">
                <input type="radio" name="type" value="1" lay-filter="type" title="发货" checked>
                <!-- <input type="radio" name="type" value="2" lay-filter="type" title="送货">
                <input type="radio" name="type" value="3" lay-filter="type" title="虚拟"> -->
            </div>
        </div>
        <div class="type" data-type="1">
            <div class="layui-form-item">
                <label class="layui-form-label">快递公司</label>
                <div class="layui-input-block">
                    <select name="delivery_name">
                        <option value="">请选择</option>
                        {volist name='$list' id='item' key='k'}
                        <option value="{$item['id']}" {if $express_id==$item['id']}selected{/if}>{$item['name']}</option>
                        {/volist}
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">快递单号</label>
                <div class="layui-input-block">
                    <input type="text" name="delivery_id"   placeholder="请输入快递单号" autocomplete="off" class="layui-input">
                </div>
            </div>
            <!-- <div style="height:5px"></div>
            <div class="layui-form-item">
            <label class="layui-form-label">单号记录</label>
            <div class="layui-upload layui-input-block">
                  <button type="button" class="layui-btn" id="test1">上传图片</button>
                  <div class="layui-upload-list">
                    <img class="layui-upload-img" id="demo1">
                    <p id="demoText"></p>
                  </div>
                </div> 
            </div> -->
        </div>
        <!-- <div class="type" data-type="2" style="display: none">
            <div class="layui-form-item">
                <label class="layui-form-label">送货人姓名</label>
                <div class="layui-input-block">
                    <input type="text" name="sh_delivery_name"   placeholder="请输入送货人姓名" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">送货人电话</label>
                <div class="layui-input-block">
                    <input type="text" name="sh_delivery_id"   placeholder="请输入送货人电话" autocomplete="off" class="layui-input">
                </div>
            </div>
        </div> -->
        <div class="layui-form-item" style="margin:10px 0;padding-bottom: 10px;">
            <div class="layui-input-block">
                <button class="layui-btn layui-btn-sm" lay-submit="" lay-filter="delivery">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary layui-btn-sm">重置</button>
            </div>
        </div>
    </form>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
{/block}
{block name="script"}
<script>
    var id={$id};
    var upload = layList.upload;
    layList.form.render();
    layList.form.on('radio(type)', function(data){
       $('.type').each(function () {
           if($(this).data('type') == data.value){
               $(this).show();
           }else{
               $(this).hide();
           }
       })
    });
    layList.search('delivery',function (data) {
        var daishou = '{$daishou}';
        if(!data.delivery_name) return layList.msg('请选择快递公司');

        if(daishou!=1){
            if(!data.delivery_id) return layList.msg('请填写快递单号');
        }
  
        var index = layList.layer.load(1, {
            shade: [0.1,'#fff']
        });
        layList.basePost(layList.U({a:'update_delivery',q:{id:id}}),data,function (res) {
            layList.layer.close(index);
            layList.msg(res.msg);
            parent.layer.close(parent.layer.getFrameIndex(window.name));
            parent.window.frames[parent.$(".page-tabs-content .active").index()].location.reload();
        },function (res) {
            layList.layer.close(index);
            layList.msg(res.msg);
        });
    });

      //普通图片上传
  var uploadInst = upload.render({
    elem: '#test1'
    ,url: 'https://httpbin.org/post' //改成您自己的上传接口
    ,before: function(obj){
      //预读本地文件示例，不支持ie8
      obj.preview(function(index, file, result){
        $('#demo1').attr('src', result); //图片链接（base64）
      });
    }
    ,done: function(res){
      //如果上传失败
      if(res.code > 0){
        return layer.msg('上传失败');
      }
      //上传成功
    }
    ,error: function(){
      //演示失败状态，并实现重传
      var demoText = $('#demoText');
      demoText.html('<span style="color: #FF5722;">上传失败</span> <a class="layui-btn layui-btn-xs demo-reload">重试</a>');
      demoText.find('.demo-reload').on('click', function(){
        uploadInst.upload();
      });
    }
  });

</script>
{/block}