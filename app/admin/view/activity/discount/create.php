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
    .please_select{
        font-size: 12px;
        color: #999;
    }
    td{
        text-align: center;
    }

    .blue{
        color: #0d8ddb;
    }
</style>
{/block}
{block name="content"}
<input type="hidden"  id="product_json_input" value="{$product_list_json}">
<div class="row">
    <div class="col-sm-12 panel panel-default" >
        <div class="panel-body" style="padding: 30px">
            <form class="form-horizontal" id="signupForm">
                <div class="form-group">
                    <div class="col-md-12">
                        <div class="input-group">
                            <span class="input-group-addon">活动名称</span>
                            <input maxlength="64" placeholder="请在这里输入名称" name="title" class="layui-input" id="title" value="{$news.title}">
                            <input type="hidden"  id="id" value="{$news.id}">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <div class="layui-inline">
                            <label class="layui-form-label">活动时间</label>
                            <div class="layui-input-inline" style="width: 200px;">
                                <input type="text" name="start_time" id="start_time" value="{$news.start_time_text}" placeholder="开始时间" autocomplete="off" class="layui-input">
                            </div>
                            <div class="layui-form-mid">-</div>
                            <div class="layui-input-inline" style="width: 200px;">
                                <input type="text" name="end_time" id="end_time" value="{$news.end_time_text}"  placeholder="结束时间" autocomplete="off" class="layui-input">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-12">
                        <label class="layui-form-label">小标题&nbsp;&nbsp;</label>
                        <div class="layui-input-inline" style="width: 200px;">
                            <input type="text" name="sec_title" id="sec_title" value="{$news.sec_title}" placeholder="2-5字，展现在商品详情页" autocomplete="off" class="layui-input">
                            <small class="blue">2-5字，展现在商品详情页</small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-12">
                        <label class="layui-form-label">预告设置</label>
                        <div class="layui-input-inline" style="width: 200px;">
                            <input type="text" name="before_hour" id="before_hour"  value="{$news.before_hour}" placeholder="活动开始多少小时进行预告通知" autocomplete="off" class="layui-input">
                            <small class="blue">活动前几小时进行活动预告通知</small>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label style="color:#aaa">预告内容</label>
                        <textarea id="before_msg" name="before_msg" class="layui-input" style="height:80px;resize:none;line-height:20px;color:#333;">{$news.before_msg}</textarea>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-12">
                        <div class="form-control" style="height:auto">
                            <label style="color:#ccc">图文封面</label>
                            <div class="row nowrap">
                                <div class="col-xs-3" style="width:160px">
                                    {if condition="$news['image']"}
                                    <div class="upload-image-box transition image_img" style="height: 80px;background-repeat:no-repeat;background-size:contain;background-image:url({$news['image']})">
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
                            <input type="hidden" name="image" id="image_input" value="{$news['image']}"/>
                            <p class="help-block" style="margin-top:10px;color:#ccc">封面大图片建议尺寸：900像素 * 500像素</p>
                        </div>
                    </div>
                </div>

                <div class="layui-btn-container">
                    <!--                        <a class="layui-btn layui-btn-sm" href="{:Url('index')}">分类首页</a>-->
                    <button type="button" class="layui-btn layui-btn-sm" onclick="addGoods()">添加商品</button>
                    {if count($product_list)>0}
                    <button type="button" class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame('编辑','{:Url('set_attr')}?id={$news.id}',{w:900,h:660})">设置商品规格</button>
                    {/if}
                </div>
                <table class="layui-table">
                    <colgroup>
                        <col width="150">
                        <col width="200">
                        <col>
                    </colgroup>
                    <thead>
                        <tr>
                            <th>商品图片</th>
                            <th>商品名称</th>
                            <th>商品价格</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="list-container">
                    {if count($product_list)>0}
                    {foreach $product_list as $key=>$vo}
                    <tr>
                        <td><img src="{$vo.image}" style="width: 80px;height: 80px;"></td>
                        <td>{$vo.store_name}</td>
                        <td>{$vo.price}</td>
                        <td>
                            <button type="button" class="layui-btn layui-btn-danger" onclick="delGoods(this,{$key})">删除</button>
                        </td>
                    </tr>
                    {/foreach}
                    {else}
                    <tr>
                        <td class="please_select" colspan="4">
                            请选择商品
                        </td>
                    </tr>
                    {/if}
                    </tbody>
                </table>

                <div class="form-group">
                    <div class="col-md-12">
                        <div class="col-md-12">
                            <label style="color:#aaa">促销价抹零</label>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="radio" name="is_cut_zero" id="is_cut_zero0" class="layui-radio" value="0" {eq name="news['is_cut_zero']" value="0"}checked{/eq}><label for="is_cut_zero0">&nbsp;&nbsp;否</label>&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="radio" name="is_cut_zero" id="is_cut_zero1" class="layui-radio" value="1" {eq name="news['is_cut_zero']" value="1"}checked{/eq}><label for="is_cut_zero1">&nbsp;&nbsp;是</label>&nbsp;&nbsp;&nbsp;&nbsp;
                            <span class="blue">抹零是指付钱时不计整数以外的尾数，抹零对低于1.00元的商品无效</span>
                        </div>

                    </div>
                </div>


                <div class="form-group">
                    <div class="col-md-12">
                        <div class="col-md-12">
                            <label style="color:#aaa">限购设置</label>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_type(0)" type="radio" name="limit_buy_type" id="limit_buy_type0" class="layui-radio" value="0" {eq name="news['limit_buy_type']" value="0"}checked{/eq}><label for="limit_buy_type0">&nbsp;&nbsp;不限购</label>&nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_type(1)" type="radio" name="limit_buy_type" id="limit_buy_type1" class="layui-radio" value="1" {eq name="news['limit_buy_type']" value="1"}checked{/eq}><label for="limit_buy_type1">&nbsp;&nbsp;购买数量限购</label>&nbsp;&nbsp;&nbsp;&nbsp;
<!--                            <input onclick="set_type(2)" type="radio" name="limit_buy_type" id="limit_buy_type2" class="layui-radio" value="2" {eq name="news['limit_buy_type']" value="2"}checked{/eq}><label for="limit_buy_type2">&nbsp;&nbsp;前几件享受折扣</label>-->
                        </div>

                    </div>
                </div>

                <div class="layui-inline dis_type1 {if $news.limit_buy_type!=1}hide{/if}">
                    <label class="layui-form-label">每人每种商品限购几次</label>
                    <div class="layui-input-inline" style="width: 200px;">
                        <input type="text" name="limit_goods_num" id="limit_goods_num"  value="{$news.limit_goods_num}" placeholder="每人每种商品限购几次" autocomplete="off" class="layui-input">
                    </div>
                </div>

                <div class="layui-inline dis_type2 {if $news.limit_buy_type!=2}hide{/if}">
                    <label class="layui-form-label">每人每种商品前几件享受折扣</label>
                    <div class="layui-input-inline" style="width: 200px;">
                        <input type="text" name="limit_before_goods_num" id="limit_before_goods_num" value="{$news.limit_before_goods_num}" placeholder="每人每种商品限购几次" autocomplete="off" class="layui-input">
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

    layList.date({elem:'#start_time',theme:'#0092DC',type:'datetime'});
    layList.date({elem:'#end_time',theme:'#0092DC',type:'datetime'});


//    var editor = document.getElementById('myEditor');
//    editor.style.height = '300px';
//    window.UMEDITOR_CONFIG.toolbar = [
//        // 加入一个 test
//        'source | undo redo | bold italic underline strikethrough | superscript subscript | forecolor backcolor | removeformat |',
//        'insertorderedlist insertunorderedlist | selectall cleardoc paragraph | fontfamily fontsize' ,
//        '| justifyleft justifycenter justifyright justifyjustify |',
//        'link unlink | emotion selectimgs video  | map',
//        '| horizontal print preview fullscreen', 'drafts', 'formula'
//    ];
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

    //设置类型的显示隐藏单选交互
    function set_type(type){
        if(type==0){
            $('.dis_type1').addClass('hide');
            $('.dis_type2').addClass('hide');
        }

        if(type==1){
            $('.dis_type1').removeClass('hide');
            $('.dis_type2').addClass('hide');
        }

        if(type==2){
            $('.dis_type2').removeClass('hide');
            $('.dis_type1').addClass('hide');
        }

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
    var goodsList;
    var goodsListjson;


    goodsListjson=$('#product_json_input').val();
    if(goodsListjson){
        goodsList = JSON.parse(goodsListjson);
    }else{
        goodsList=[];
    }

    console.log('goodsList===》',goodsList)

    $('.save_news').on('click',function(){

        var goodsIdArr = []
        for (let index = 0; index < goodsList.length; index++) {
            const element = goodsList[index];
            goodsIdArr.push(element.id)
        }
//        console.log(goodsIdArr.toString());


//        $news['id'] = '';
//        $news['image_input'] = '';
//        $news['image'] = '';
//        $news['title'] = '';
//        $news['sec_title'] = '';
//        $news['start_time'] = '';
//        $news['end_time'] = '';
//        $news['before_hour'] = 0;
//        $news['before_msg'] = '';
//        $news['is_cut_zero'] = 0;
//        $news['limit_buy_type'] = 0;
//        $news['limit_goods_num'] = 0;
//        $news['limit_before_goods_num'] = 0;



        var list = {};
        list.title = $('#title').val();/* 标题 */
        list.image_input = $('#image_input').val();/* 图片 */
        list.id = $('#id').val();
        list.sec_title = $('#sec_title').val();
        list.start_time = $('#start_time').val();/* 计划开始时间 */
        list.end_time = $('#end_time').val();/* 计划结束时间 */
        list.before_hour = $('#before_hour').val();
        list.before_msg = $('#before_msg').val();
        list.is_cut_zero = $("input[name='is_cut_zero']:checked").val();
        list.limit_buy_type = $("input[name='limit_buy_type']:checked").val();
        list.limit_goods_num = $('#limit_goods_num').val();
        list.limit_before_goods_num = $('#limit_before_goods_num').val();

        list.goodsIdArr = goodsIdArr;
        var Expression = /http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/;
        var objExp=new RegExp(Expression);
        if(list.title == ''){
            $eb.message('error','请输入标题');
            return false;
        }
        if(list.image_input == ''){
            $eb.message('error','请添加图片');
            return false;
        }


        var data = {};
        var index = layList.layer.load(1, {
            shade: [0.5,'#fff'] //0.1透明度的白色背景
        });;
        $.ajax({
            url:"{:Url('save')}",
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
    var interval_id
    function addGoods(){
        var goodsIdArr = []
        for (let index = 0; index < goodsList.length; index++) {
            const element = goodsList[index];
            goodsIdArr.push(element.id)
        }
        console.log(goodsIdArr.toString());

        
        var addGoodsUrl = '{:Url('select')}?ids=' +goodsIdArr.toString()
        $eb.createModalFrame(this.innerText,addGoodsUrl,{w:700,h:560})
        interval_id = setInterval('get_cache()',1000);
    }

    //轮询获取选择的商品
    // var interval_id = setInterval('get_cache()',1000);
    
    
    function get_cache(){
        $.ajax({
            url:"{:Url('get_cache')}",
            data:{},
            type:'post',
            dataType:'json',
            success:function(re){
                if(re.code == 200){
                    console.log(re.data);
                    goodsList = re.data
                    

                    let htmllet = "";
                    for (let index = 0; index < re.data.length; index++) {
                        const element = re.data[index];
            htmllet += `<tr>
                            <td><img src="${element.image}" style="width: 80px;height: 80px;"></td>
                            <td>${element.store_name}</td>
                            <td>${element.price}</td>
                            <td><button type="button" class="layui-btn layui-btn-danger" onclick="delGoods(this,${index})">删除</button></td>
                        </tr>`;
                    }
                    document.getElementById("list-container").innerHTML = htmllet;
                    //停止轮询
                    clearInterval(interval_id);               
                }
            },

        })
    }

    function delGoods(Obj,index){
        console.log(goodsList)
        goodsList.splice(index, 1)
        console.log(goodsList);
//        Obj.parentNode.parentNode.parentNode.removeChild(Obj.parentNode.parentNode);
        let htmllet = "";
        for (let index = 0; index < goodsList.length; index++) {
            const element = goodsList[index];
            htmllet += `<tr>
                            <td><img src="${element.image}" style="width: 80px;height: 80px;"></td>
                            <td>${element.store_name}</td>
                            <td>${element.price}</td>
                            <td><button type="button" class="layui-btn layui-btn-danger" onclick="delGoods(this,${index})">删除</button></td>
                        </tr>`;
        }
        document.getElementById("list-container").innerHTML = htmllet;
    }


    //设置商品的属性
    function set_attr(Obj,index){
        var attrIdArr = []
        for (let index = 0; index < goodsList.length; index++) {
            const element = goodsList[index];
            attrIdArr.push(element.id)
        }
        console.log(attrIdArr.toString());


        var addGoodsUrl = '{:Url('select')}?ids=' +goodsIdArr.toString()
        $eb.createModalFrame(this.innerText,addGoodsUrl,{w:700,h:560})
        interval_id = setInterval('get_cache()',1000);
    }


</script>
{/block}