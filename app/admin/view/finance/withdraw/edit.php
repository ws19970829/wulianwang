  {extend name="public/container"}

  {block name="head_top"}
  <style>
      .w30 {
          width: 300px !important;
      }

      .layui-form-item {
          margin-bottom: 10px
      }

      .z {
          display: none;
      }
      .notic{
          font-size: 12px;
          color: red
      }
  </style>
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
      <blockquote class="layui-elem-quote layui-text">
          申请提现金额：{$total}元  <span class="notic">(手续费：{$rate}元)</span>
      </blockquote>
      <div class="layui-form-item">
          <label class="layui-form-label">提现方式</label>
          <div class="layui-input-block w30">
              <input type="radio" name="extract_type" value="bank" title="银行卡" checked="" lay-filter="extract_type">
              <input type="radio" name="extract_type" value="alipay" title="支付宝" lay-filter="extract_type">
          </div>
      </div>
      <div class="layui-form-item b">
          <label class="layui-form-label">银行卡号</label>
          <div class="layui-input-block w30">
              <input type="text" name="bank_code"  autocomplete="off" class="layui-input">
          </div>
      </div>
      <div class="layui-form-item b">
          <label class="layui-form-label">开户行</label>
          <div class="layui-input-block w30">
              <input type="text" name="bank_name"  autocomplete="off" class="layui-input">
          </div>
      </div>

      
      <div class="layui-form-item z">
          <label class="layui-form-label">支付宝账号</label>
          <div class="layui-input-block w30">
              <input type="text" name="alipay_code"  autocomplete="off" class="layui-input">
          </div>
      </div>

      <div class="layui-form-item">
          <label class="layui-form-label">姓名</label>
          <div class="layui-input-block w30">
              <input type="text" name="real_name"  autocomplete="off" class="layui-input">
          </div>
      </div>

      <div class="layui-form-item">
          <label class="layui-form-label">备注</label>
          <div class="layui-input-block w30">
              <textarea placeholder="请输入内容" class="layui-textarea" name="mark"></textarea>
          </div>
      </div>

      <input type="hidden" name="tenant_id" value="{$tenant_id}">
      <input type="hidden" name="order_ids" value="{$order_ids}">
      <input type="hidden" name="extract_price" value="{$total}">

      
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

      layList.form.on('radio(extract_type)', function({
          value
      }) {
          if (value == 'bank') {
              $('.z').hide();
              $('.b').show();
          } else {
              $('.z').show();
              $('.b').hide();
          }
      });

      //监听提交
      layList.form.on('submit(submit)', function({field}) {
          if(field.extract_type=='alipay'){
              if(field.alipay_code==''){
                  layer.msg('请填写支付宝账号');
                  return false;
              }
          }else{
            if(field.bank_name==''){
                  layer.msg('请填写开户行名称');
                  return false;
              }
              if(field.bank_code==''){
                  layer.msg('请填写银行卡号');
                  return false;
              }
          }
          if(field.real_name==''){
             layer.msg('请填写姓名');
             return false;
           }

          $.ajax({
              type: "post",
              url: "{:url('save')}",
              data: field,
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