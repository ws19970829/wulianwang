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
   <div class="col-sm-12 panel panel-default" >
       <div class="panel-body" style="padding: 30px">
           <form class="form-horizontal" id="signupForm">
               <input type="hidden"  id="id" value="{$news.id}">
               <div class="form-group">
                   <div class="col-md-12">
                       <div class="form-control" style="height:auto">
                           <label style="color:#ccc">图文封面大图片设置</label>
                           <div class="row nowrap">
                               <div class="col-xs-3" style="width:160px">
                                   {if condition="$news['image_input']"}
                                   <div class="upload-image-box transition image_img" style="height: 80px;background-repeat:no-repeat;background-size:contain;background-image:url({$news['image_input']})">
                                       <input value="" type="hidden" name="local_url">
                                   </div>
                                   {else/}
                                   <div class="upload-image-box transition image_img" style="height: 80px;background-repeat:no-repeat;background-size:contain;background-image:url('/system/module/wechat/news/images/image.png')">
                                       <input value="" type="hidden" name="local_url">
                                   </div>
                                   {/if}
                               </div>
                               <div class="col-xs-6">
                                   <input type="file" class="upload" name="image" style="display: none;" id="image" />
                                   <br>
                                   <a class="btn btn-sm add_image upload_span">上传图片</a>
                                   <br>
                                   <br>
                               </div>
                           </div>
                           <input type="hidden" name="image" id="image_input" value="{$news['image_input']}"/>
                           <p class="help-block" style="margin-top:10px;color:#ccc">封面大图片建议尺寸：900像素 * 500像素</p>
                       </div>
                   </div>
               </div>
               <div class="form-group">
                   <div class="col-md-12">
                       <label style="color:#aaa">文章内容</label>
                       <textarea type="text/plain" id="myEditor" style="width:100%;">{$news['content']}</textarea>
                   </div>
               </div>
               <div class="form-actions">
                   <div class="row">
                       <div class="col-md-offset-4 col-md-9">
                           <button type="button" class="btn btn-w-m btn-info save_news">保存</button>
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
            var editor = document.getElementById('myEditor');
            editor.style.height = '300px';
            window.UMEDITOR_CONFIG.toolbar = [
                // 加入一个 test
                'source | undo redo | bold italic underline strikethrough | superscript subscript | forecolor backcolor | removeformat |',
                'insertorderedlist insertunorderedlist | selectall cleardoc paragraph | fontfamily fontsize' ,
                '| justifyleft justifycenter justifyright justifyjustify |',
                'link unlink | emotion selectimgs video  | map',
                '| horizontal print preview fullscreen', 'drafts', 'formula'
            ];
            UM.registerUI('selectimgs',function(name){
                var me = this;
                var $btn = $.eduibutton({
                    icon : 'image',
                    click : function(){
                        createFrame('选择图片','{:Url('widget.images/index')}?fodder=editor');
                    },
                    title: '选择图片'
                });

                this.addListener('selectionchange',function(){
                    //切换为不可编辑时，把自己变灰
                    var state = this.queryCommandState(name);
                    $btn.edui().disabled(state == -1).active(state == 1)
                });
                return $btn;

            });
            //实例化编辑器
            var um = UM.getEditor('myEditor');

            /**
             * 获取编辑器内的内容
             * */
            function getContent() {
                return (UM.getEditor('myEditor').getContent());
            }
            function hasContent() {
                return (UM.getEditor('myEditor').hasContents());
            }
            function createFrame(title,src,opt){
                opt === undefined && (opt = {});
                return layer.open({
                    type: 2,
                    title:title,
                    area: [(opt.w || 800)+'px', (opt.h || 550)+'px'],
                    fixed: false, //不固定
                    maxmin: true,
                    moveOut:false,//true  可以拖出窗外  false 只能在窗内拖
                    anim:5,//出场动画 isOutAnim bool 关闭动画
                    offset:'auto',//['100px','100px'],//'auto',//初始位置  ['100px','100px'] t[ 上 左]
                    shade:0,//遮罩
                    resize:true,//是否允许拉伸
                    content: src,//内容
                    move:'.layui-layer-title'
                });
            }
            //选择图片
            function changeIMG(index,pic){
                $(".image_img").css('background-image',"url("+pic+")");
                $(".active").css('background-image',"url("+pic+")");
                $('#image_input').val(pic);
            }
            //选择图片插入到编辑器中
            function insertEditor(list){
                console.log(list);
                um.execCommand('insertimage', list);
            }
            /**
             * 上传图片
             * */
            $('.upload_span').on('click',function (e) {
//                $('.upload').trigger('click');
                createFrame('选择图片','{:Url('widget.images/index')}?fodder=image');
            })

            /**
             * 编辑器上传图片
             * */
            $('.edui-icon-image').on('click',function (e) {
//                $('.upload').trigger('click');
                createFrame('选择图片','{:Url('widget.images/index')}?fodder=image');
            })

            /**
             * 提交图文
             * */
            $('.save_news').on('click',function(){
                var list = {};
                list.content = getContent();/* 内容 */
                list.id = $('#id').val();/* 原文链接 */
                list.image_input = $('#image_input').val();/* 图片 */
                if(list.content == ''){
                    $eb.message('error','请输入内容');
                    return false;
                }
                var data = {};
                var index = layList.layer.load(1, {
                    shade: [0.5,'#fff'] //0.1透明度的白色背景
                });;
                $.ajax({
                    url:"{:Url('add_new')}",
                    data:list,
                    type:'post',
                    dataType:'json',
                    success:function(re){
                        layer.close(index);
                        if(re.code == 200){
                            data[re.data] = list;
                            $('.type-all>.active>.new-id').val(re.data);
                            $eb.message('success',re.msg);
                            location.reload();
                            setTimeout(function (e) {
                                parent.$(".J_iframe:visible")[0].contentWindow.location.reload();

//                                parent.layer.close(parent.layer.getFrameIndex(window.name));
                            },600)
                        }else{
                            $eb.message('error',re.msg);
                        }
                    },
                    error:function () {
                        layer.close(index);
                    }
                })
            });
            $('.article-add ').on('click',function (e) {
                var num_div = $('.type-all').children('div').length;
                if(num_div > 7){
                    $eb.message('error','一组图文消息最多可以添加8个');
                    return false;
                }
                var url = "{__MODULE_PATH}wechat/news/images/image.png";
                html = '';
                html += '<div class="news-item transition active news-image" style=" margin-bottom: 20px;background-image:url('+url+')">'
                html += '<input type="hidden" name="new_id" value="" class="new-id">';
                html += '<span class="news-title del-news">x</span>';
                html += '</div>';
                $(this).siblings().removeClass("active");
                $(this).before(html);
            })
            $(document).on("click",".del-news",function(){
                $(this).parent().remove();
            })
            $(document).ready(function() {
                var config = {
                    ".chosen-select": {},
                    ".chosen-select-deselect": {allow_single_deselect: true},
                    ".chosen-select-no-single": {disable_search_threshold: 10},
                    ".chosen-select-no-results": {no_results_text: "沒有找到你要搜索的分类"},
                    ".chosen-select-width": {width: "95%"}
                };
                for (var selector in config) {
                    $(selector).chosen(config[selector])
                }
            })
        </script>
{/block}