  {extend name="public/container"}

  {block name="head_top"}

  <link href="{__ADMIN_PATH}plug/umeditor/themes/default/css/umeditor.css" type="text/css" rel="stylesheet">

  <link href="{__ADMIN_PATH}module/wechat/news/css/style.css" type="text/css" rel="stylesheet">

  <link href="{__FRAME_PATH}css/plugins/chosen/chosen.css" rel="stylesheet">

  <script type="text/javascript" src="{__ADMIN_PATH}plug/umeditor/third-party/jquery.min.js"></script>

  <script type="text/javascript" src="{__ADMIN_PATH}plug/umeditor/third-party/template.min.js"></script>

  <script type="text/javascript" charset="utf-8" src="{__ADMIN_PATH}plug/umeditor/umeditor.config.js"></script>

  <script type="text/javascript" charset="utf-8" src="{__ADMIN_PATH}plug/umeditor/umeditor.js"></script>

  <script src="{__ADMIN_PATH}frame/js/ajaxfileupload.js"></script>

  <script src="{__ADMIN_PATH}plug/validate/jquery.validate.js"></script>

  <script src="{__FRAME_PATH}js/plugins/chosen/chosen.jquery.js"></script>


  {/block}

  {block name="content"}

  <form class="layui-form" action="">
      <div class="layui-form-item">
         <div id="tree" class="demo-tree-more"></div>
          <div class="layui-input-block"></div>
          <div class="layui-form-item">
              <div class="layui-input-block">
                  <label class="layui-form-label"></label>
                  <button type="submit" class="layui-btn" lay-submit="" lay-filter="submit">确定</button>
              </div>
          </div>
      </div>
    </form>

  <script src="{__ADMIN_PATH}js/layuiList.js"></script>

  {/block}

  {block name="script"}

  <script>
    
      layList.form.render();
      var data = JSON.parse('{$catelist|raw}')
      //基本演示
      layList.tree.render({
          elem: '#tree'
          ,data: data
          ,showCheckbox: true  //是否显示复选框
          ,id: 'demoId1'
          ,isopen: false //加载完毕后的展开状态，默认值：true
        });


      //监听提交
      layList.form.on('submit(submit)', function(res) {
        var select = layList.tree.getChecked('demoId1');
        var cate_id = [];
        var tenant_id = '{$tenant_id}';
        if(select.length>=0){
            cate_id = get_cate_id(select);
        }
          $.ajax({
              type: "post",
              url: "{:url('cate_usable')}",
              data: {
                  tenant_id,
                  cate_id
              },
              dataType: "json",
              success: function(res) {
                  layList.msg(res.msg, function() {
                      var index = parent.layer.getFrameIndex(window.name); //获取当前窗口的name
                      parent.layer.close(index); //关闭窗口
                  });
              }
          });
          return false;
      });
       
       function get_cate_id(tree){
           var temp = [];
           var fn = function(tree){
                $.each(tree, function (i, v) { 
                    if(v.children){
                        fn(v.children)
                    }else{
                        temp.push(v.id);
                    }
                });
           }
           fn(tree);
           return temp;
       }
  </script>

  {/block}