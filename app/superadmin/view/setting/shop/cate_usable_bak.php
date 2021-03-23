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
          <div class="layui-input-block" id="cate_id">
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
      layui.config({
          base: '/static/plug/layui/'
      }).extend({
          selectN: './selectN',
      }).use('selectM', function() {
          var selectM = layui.selectM;
          var select = '{$select}'.split(',');

          selectM({
              //元素容器【必填】
              elem: '#cate_id'
                  //候选数据【必填】
                  ,
              data: JSON.parse('{$catelist|raw}')
                  //默认值
                  ,
              selected: select
                  //最多选中个数，默认5
                  ,
              max: 100,
              name: 'cate_id',

              delimiter: ','
                  //候选项数据的键名
                  ,
              field: {
                  idName: 'id',
                  titleName: 'cate_name',
                  //statusName: 'disabled'
              }
          });
      });
      layList.form.render();



      //监听提交
      layList.form.on('submit(submit)', function({
          field: {
              cate_id
          }
      }) {

          if (cate_id == '') {
              layList.msg('请选择类别');
              return false;
          }
          var tenant_id = '{$tenant_id}';
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
  </script>

  {/block}