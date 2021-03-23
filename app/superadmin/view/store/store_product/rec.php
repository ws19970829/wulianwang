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
          <div class="layui-input-block">
              <label class="layui-form-label">推荐时间</label>
              <div class="layui-input-inline">
                  <input type="text" class="layui-input" id="test10" placeholder=" - " name='rec_time'>
              </div>
          </div>
      </div>
      <div class="layui-input-block"></div>
      <div class="layui-form-item">
          <div class="layui-input-block">
              <label class="layui-form-label"></label>
              <button type="submit" class="layui-btn" lay-submit="" lay-filter="submit">确定</button>
          </div>
      </div>
  </form>

  <script src="{__ADMIN_PATH}js/layuiList.js"></script>

  {/block}

  {block name="script"}

  <script>
      layList.form.render();

      var laydate = layList.laydate;
      var form = layList.form;
      laydate.render({
          elem: '#test10',
          type: 'datetime',
          range: true
      });

      //监听提交
      form.on('submit(submit)', function({
          field: {
              rec_time
          }
      }) {

          if (rec_time=='') {
              layList.msg('请选择日期');
              return false;
          }
          var ids = '{$ids}';
          $.ajax({
              type: "post",
              url: "{:url('rec')}",
              data: {
                  ids,
                  rec_time
              },
              dataType: "json",
              success: function(res) {
                  layList.msg(res.msg,function(){
                    var index=parent.layer.getFrameIndex(window.name); //获取当前窗口的name
                    parent.layer.close(index);		//关闭窗口
                  });
              }
          });
          return false;
      });
  </script>

  {/block}