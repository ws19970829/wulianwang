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

  <style>
      .wrapper-content {

          padding: 0 !important;

      }
  </style>

  {/block}

  {block name="content"}

  <form class="layui-form" action="">
      <div class="layui-form-item">
          <label class="layui-form-label">推荐时间</label>
          <div class="layui-input-inline">
              <input type="text" class="layui-input" id="test10" placeholder=" - " name='rec_time'>
          </div>
          <div class="layui-form-item">
              <div class="layui-input-block">
                  <button type="submit" class="layui-btn" lay-submit="" lay-filter="submit">确定</button>
              </div>
          </div>
      </div>

      <script src="{__ADMIN_PATH}js/layuiList.js"></script>

      {/block}

      {block name="script"}

      <script>
          layList.form.render();

          var laydate = layList.laydate;
          laydate.render({
              elem: '#test10',
              type: 'datetime',
              range: true
          });

           //监听提交
         form.on('submit(submit)', function(data){
          console.log(data);
           return false;
         });
      </script>

      {/block}