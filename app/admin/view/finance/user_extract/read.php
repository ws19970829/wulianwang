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

  <div class="row">

      <div class="col-sm-12 panel panel-default">

          <div class="panel-body" style="padding: 30px">

              <form class="form-horizontal" id="signupForm">

                  <div class="form-group">

                      <div class="col-md-12">

                          <div class="input-group">

                              <span class="input-group-addon">提现金额</span>

                              <input type="number" value="{$vo.extract_price}" class="layui-input" readonly>

                          </div>

                      </div>

                  </div>

                  <div class="form-group">

                    <div class="col-md-12">

                        <div class="input-group">

                            <span class="input-group-addon">申请时间</span>

                            <input value="{:date('Y-m-d H:i:s',$vo.add_time)}" class="layui-input" readonly>

                        </div>

                    </div>

                    </div>


                  <div class="form-group">

                      <div class="col-md-12">

                          <div class="input-group">

                              <span class="input-group-addon">提现方式</span>

                              <input value="{if $vo.extract_type=='bank' }银行 {else /}支付宝{/if}" class="layui-input" readonly>

                          </div>

                      </div>

                  </div>

                  <div class="form-group">

                      <div class="col-md-12">

                          <div class="input-group">

                              <span class="input-group-addon">姓名</span>

                              <input value="{$vo.real_name}" class="layui-input" readonly>

                          </div>

                      </div>

                  </div>


                  <div class="form-group">

                      <div class="col-md-12">

                          <div class="input-group">

                              <span class="input-group-addon">支付宝账户</span>

                              <input value="{$vo.alipay_code}" class="layui-input" readonly>

                          </div>

                      </div>

                  </div>

                  <div class="form-group">

                      <div class="col-md-12">

                          <div class="input-group">

                              <span class="input-group-addon">银行卡号</span>

                              <input value="{$vo.bank_code}" class="layui-input" readonly>

                          </div>

                      </div>

                  </div>

                  <div class="form-group">

                      <div class="col-md-12">

                          <div class="input-group">

                              <span class="input-group-addon">开户行</span>

                              <input value="{$vo.bank_name}" class="layui-input" readonly>

                          </div>

                      </div>

                  </div>

                  
                  <div class="form-group">

                      <div class="col-md-12">

                          <div class="input-group">

                              <span class="input-group-addon">备注</span>

                              <textarea readonly class="layui-textarea">{$vo.mark}</textarea>

                          </div>

                      </div>

                  </div>

              </form>

          </div>

      </div>

  </div>

  <script src="{__ADMIN_PATH}js/layuiList.js"></script>

  {/block}

  {block name="script"}

  <script>
      layList.form.render();

      //监听并执行 uid 的排序

      layList.tool(function(event, data, obj) {



          var layEvent = event;

          switch (layEvent) {

              case 'download':

                  break;

              case 'open_image':

                  $eb.openImage(data.avatar);

                  break;

          }

      });

      $('.download').click(function() {

          var src = $(this).attr('src');

          var name = $(this).attr('name');

          var ext = $(this).attr('ext');

          if (src == '' || name == '' || ext == '') {

              $eb.message('error', '文件已过期');

              return;

          }

          location.href = "{:url('down')}?src=" + src + '&name=' + name + '&ext=' + ext



      })



      $('.online_see').click(function() {
          // $eb.openImage($(this).attr('src'));
          // return;
          var href = $(this).attr('src')

          layer.open({

              type: 1,

              title: false,

              closeBtn: 0,

              shadeClose: true,

              content: '<img src="' + href + '" style="display: block;position: fixed;bottom: 0;top: 0;left: 0;right: 0;height: 50%;margin: auto;" />'

          });



      })
  </script>

  {/block}