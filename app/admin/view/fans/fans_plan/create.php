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
    .form-group{
        padding-top: 20px;
    }
</style>
{/block}
{block name="content"}
<input type="hidden"  id="note_info_json" value="{$news.note_info_json}">
<input type="hidden"  id="coupon_info_json" value="{$news.coupon_info_json}">
<input type="hidden"  id="user_tag_json" value="{$news.user_tag_json}">

<div class="row">
    <div class="col-sm-12 panel panel-default" >
        <div class="panel-body" style="padding: 30px">
            <form class="form-horizontal" id="signupForm">
                <div class="form-group">
                    <div class="col-md-12">
                        <div class="input-group">
                            <span class="input-group-addon">标题</span>
                            <input maxlength="64" placeholder="请在这里输入标题" name="title" class="layui-input" id="title" value="{$news.title}">
                            <input type="hidden"  id="id" value="{$news.id}">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <div class="col-md-12">
                            <label style="color:#aaa">选择类型</label>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_type(1)" type="radio" name="type" id="type1" class="layui-radio" value="1" {eq name="news['type']" value="1"}checked{/eq}><label for="type1">&nbsp;&nbsp;互动粉丝转化</label>&nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_type(2)" type="radio" name="type" id="type2" class="layui-radio" value="2" {eq name="news['type']" value="2"}checked{/eq}><label for="type2">&nbsp;&nbsp;自定义人群</label>&nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_type(3)" type="radio" name="type" id="type3" class="layui-radio" value="3" {eq name="news['type']" value="3"}checked{/eq}><label for="type3">&nbsp;&nbsp;未消费新增人群</label>
                        </div>

                    </div>
                </div>

                <table class="layui-table  {if $news.type!=1}hide{/if}" id="dis_type1"">
                    <colgroup>
                        <col width="150">
                        <col width="200">
                        <col>
                    </colgroup>
                    <thead>
                    <tr>
                        <th>人群</th>
                        <th>数量</th>
                    </tr>
                    </thead>
                    <tbody>

                    <tr>
                        <td>所有客户</td>
                        <td>{$all_user_count}</td>
                    </tr>
                    </tbody>
                </table>


                <!-- 标签选择-->
                <div class="layui-btn-container  dis_type2 {if $news.type!=2}hide{/if}">
                    <!--                        <a class="layui-btn layui-btn-sm" href="{:Url('index')}">分类首页</a>-->
                    <button type="button" class="layui-btn layui-btn-sm" onclick="addTag()">选择标签</button>
                </div>
                <table class="layui-table  dis_type2 {if $news.type!=2}hide{/if}">
                    <colgroup>
                        <col>
                        <col>
                        <col>
                    </colgroup>
                    <thead>
                    <tr>
                        <th>标签名称</th>
                        <th>标签类型</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody id="list-container_tag">
                    {if count($news.user_tag_list)>0}
                    {foreach $news.user_tag_list as $key=>$vo}
                    <tr>
                        <td>{$vo.title}</td>
                        <td>{if $vo.is_auto}自动标签{else}手动标签{/if}</td>
                        <td><button type="button" class="layui-btn layui-btn-danger" onclick="delTag(this,{$key})">删除</button></td>
                    </tr>
                    {/foreach}
                    {else}
                    <tr>
                        <td class="please_select" colspan="3">
                            请选择标签
                        </td>
                    </tr>
                    {/if}
                    </tbody>
                </table>




                <table class="layui-table   {if $news.type!=3}hide{/if}" id="dis_type3">
                <colgroup>
                    <col width="150">
                    <col width="200">
                    <col>
                </colgroup>
                <thead>
                <tr>
                    <th>人群</th>
                    <th>数量</th>
                </tr>
                </thead>
                <tbody>

                <tr>
                    <td>未消费人群</td>
                    <td>{$not_pay_user_count}</td>
                </tr>
                </tbody>
                </table>


                <div class="form-group">
                    <div class="col-md-12">
                        <div class="col-md-12">
                            <label style="color:#aaa">计划方式</label>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_is_long(1)"  type="radio" name="is_long" id="is_long1" class="layui-radio" value="1" {eq name="news['is_long']" value="1"}checked{/eq}><label for="is_long1">&nbsp;&nbsp;自动长期计划</label>&nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_is_long(0)"  type="radio" name="is_long" id="is_long2" class="layui-radio" value="0" {eq name="news['is_long']" value="0"}checked{/eq}><label for="is_long2">&nbsp;&nbsp;手动定时计划</label>
                        </div>
                    </div>
                </div>
                <div class="layui-inline dis_is_long {if $news.is_long==1}hide{/if}">
                    <label class="layui-form-label">手动时间范围</label>
                    <div class="layui-input-inline" style="width: 200px;">
                        <input type="text" name="start_time" id="start_time" value="{$news.start_time}" placeholder="开始时间" autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-form-mid">-</div>
                    <div class="layui-input-inline" style="width: 200px;">
                        <input type="text" name="end_time" id="end_time" value="{$news.end_time}"  placeholder="结束时间" autocomplete="off" class="layui-input">
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-12">
                        <div class="col-md-12">
                            <label style="color:#aaa">优惠券配置</label>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_is_coupon(1)" type="radio" name="is_coupon" class="layui-radio" id="is_coupon1" value="1" {eq name="news['is_coupon']" value="1"}checked{/eq}><label for="is_coupon1">&nbsp;&nbsp;发放优惠券</label>&nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_is_coupon(0)"  type="radio" name="is_coupon" class="layui-radio" id="is_coupon2" value="0" {eq name="news['is_coupon']" value="0"}checked{/eq}><label for="is_coupon2">&nbsp;&nbsp;不发放优惠券</label>
                        </div>
                    </div>
                </div>

                <div class="layui-btn-container dis_coupon {if $news.is_coupon!=1}hide{/if}">
                    <!--                        <a class="layui-btn layui-btn-sm" href="{:Url('index')}">分类首页</a>-->
                    <button type="button" class="layui-btn layui-btn-sm" onclick="addCoupon()">选择优惠券</button>
                </div>
                <table class="layui-table dis_coupon {if $news.is_coupon!=1}hide{/if}">
                    <colgroup>
                        <col width="150">
                        <col width="200">
                        <col>
                    </colgroup>
                    <thead>
                    <tr>
                        <th>优惠券名称</th>
                        <th>优惠券类型</th>
                        <th>领取日期</th>
                        <th>发放数量</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody id="list-container_coupon">
                    {if count($news.coupon_info)>0}
                    {foreach $news.coupon_info as $key=>$vo}
                    <tr>
                        <td>{$vo.title}</td>
                        <td>{$vo.type_text}</td>
                        <td>{$vo.time_text}</td>
                        <td>{$vo.count_text|raw}</td>
                        <td><button type="button" class="layui-btn layui-btn-danger" onclick="delCoupon(this,{$key})">删除</button></td>
                    </tr>
                    {/foreach}
                    {else}
                    <tr>
                        <td class="please_select" colspan="5">
                            请选择优惠券
                        </td>
                    </tr>
                    {/if}
                    </tbody>
                </table>


                <div class="layui-btn-container">
                    <!--                        <a class="layui-btn layui-btn-sm" href="{:Url('index')}">分类首页</a>-->
                    <button type="button" class="layui-btn layui-btn-sm" onclick="addNote()">选择笔记</button>
                </div>
                <table class="layui-table">
                    <colgroup>
                        <col width="150">
                        <col width="200">
                        <col>
                    </colgroup>
                    <thead>
                    <tr>
                        <th>笔记图片</th>
                        <th>笔记名称</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody id="list-container">
                    {if count($news.note_info)>0}
                        {foreach $news.note_info as $key=>$vo}
                        <tr>
                            <td><img src="{$vo.image}" style="width: 80px;height: 80px;"></td>
                            <td>{$vo.title}</td>
                            <td><button type="button" class="layui-btn layui-btn-danger" onclick="delNote(this,{$key})">删除</button></td>
                        </tr>
                        {/foreach}
                    {else}
                    <tr>
                        <td class="please_select" colspan="3">
                            请选择笔记
                        </td>
                    </tr>
                    {/if}
                    </tbody>
                </table>



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

        //笔记关联内容
        var noteIdArr = [];
        for (let index = 0; index < noteList.length; index++) {
            const element = noteList[index];
            noteIdArr.push(element.id)
        }


        //优惠券关联内容
        var couponIdArr = [];
        for (let index = 0; index < couponList.length; index++) {
            const coupon_element = couponList[index];
            couponIdArr.push(coupon_element.id)
        }


        //标签关联内容
        var tagIdArr = [];
        for (let index = 0; index < tagList.length; index++) {
            const tag_element = tagList[index];
            tagIdArr.push(tag_element.id)
        }
//        console.log(goodsIdArr.toString());


        list.title = $('#title').val();/* 标题 */
        list.id = $('#id').val();/* 原文链接 */
        list.start_time = $('#start_time').val();/* 计划开始时间 */
        list.end_time = $('#end_time').val();/* 计划结束时间 */
        list.is_long = $("input[name='is_long']:checked").val();
        list.is_coupon = $("input[name='is_coupon']:checked").val();
        list.type = $("input[name='type']:checked").val();
        list.note_id = noteIdArr;
        list.coupon_ids = couponIdArr;
        list.tag_ids = tagIdArr;


        var Expression = /http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/;
        var objExp=new RegExp(Expression);
        if(list.title == ''){
            $eb.message('error','请输入标题');
            return false;
        }

        var data = {};
        var index = layList.layer.load(1, {
            shade: [0.5,'#fff'] //0.1透明度的白色背景
        });

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
    });


    //设置类型的显示隐藏单选交互
    function set_type(type){
        if(type==1){
            $('#dis_type1').removeClass('hide');
            $('.dis_type2').addClass('hide');
            $('#dis_type3').addClass('hide');
        }

        if(type==2){
            $('.dis_type2').removeClass('hide');
            $('#dis_type1').addClass('hide');
            $('#dis_type3').addClass('hide');
        }

        if(type==3){
            $('#dis_type3').removeClass('hide');
            $('#dis_type1').addClass('hide');
            $('.dis_type2').addClass('hide');
        }

    }

    //设置优惠券的显示隐藏
    function set_is_coupon(is_coupon){
        if(is_coupon==1){
            $('.dis_coupon').removeClass('hide');
        }else{
            $('.dis_coupon').addClass('hide');
        }
    }

    //设置时间计划的显示隐藏
    function set_is_long(is_long){
        if(is_long==1){
            $('.dis_is_long').addClass('hide');
        }else{
            $('.dis_is_long').removeClass('hide');
        }
    }




    //---------------------------------------------------------------------
    //选择笔记的方法-start
    //---------------------------------------------------------------------
    var noteList;
    var noteListjson;
    noteListjson=$('#note_info_json').val();
    if(noteListjson){
        noteList = JSON.parse($('#note_info_json').val());
    }else{
        noteList=[];
    }
    console.log(noteList);
    var interval_id;

    function addNote(){
        var noteIdArr = [];
        console.log(noteList);
        for (let index = 0; index < noteList.length; index++) {
            const element = noteList[index];
            noteIdArr.push(element.id)
        }
        console.log(noteIdArr.toString());


        var addNoteUrl = "{:Url('select_note')}?ids=" +noteIdArr.toString()
        $eb.createModalFrame(this.innerText,addNoteUrl,{w:700,h:560})
        interval_id = setInterval('get_cache()',1000);
    }

    //轮询获取选择的商品
//     var interval_id = setInterval('get_cache()',1000);


    function get_cache(){
        $.ajax({
            url:"{:Url('get_cache_note')}",
            data:{},
            type:'post',
            dataType:'json',
            success:function(re){
                if(re.code == 200){
                    console.log(re.data);
                    noteList = re.data


                    let htmllet = "";
                    for (let index = 0; index < re.data.length; index++) {
                        const element = re.data[index];
                        htmllet += `<tr>
                            <td><img src="${element.image}" style="width: 80px;height: 80px;"></td>
                            <td>${element.title}</td>
                            <td><button type="button" class="layui-btn layui-btn-danger" onclick="delNote(this,${index})">删除</button></td>
                        </tr>`;
                    }
                    document.getElementById("list-container").innerHTML = htmllet;
                    //停止轮询
                    clearInterval(interval_id);
                }
            },

        })
    }

    function delNote(Obj,index){
        console.log(noteList)
        noteList.splice(index, 1)
        console.log(noteList);
//        Obj.parentNode.parentNode.parentNode.removeChild(Obj.parentNode.parentNode);
        let htmllet = "";
        for (let index = 0; index < noteList.length; index++) {
            const element = noteList[index];
            htmllet += `<tr>
                            <td><img src="${element.image}" style="width: 80px;height: 80px;"></td>
                            <td>${element.store_name}</td>
                            <td>${element.price}</td>
                            <td><button type="button" class="layui-btn layui-btn-danger" onclick="delNote(this,${index})">删除</button></td>
                        </tr>`;
        }
        document.getElementById("list-container").innerHTML = htmllet;
    }

    //---------------------------------------------------------------------
    //选择笔记的方法-end
    //---------------------------------------------------------------------


    //---------------------------------------------------------------------
    //选择优惠券的方法-start
    //---------------------------------------------------------------------
    var couponList;
    var couponListjson;
    couponListjson=$('#coupon_info_json').val();
    if(couponListjson){
        couponList = JSON.parse($('#coupon_info_json').val());
    }else{
        couponList=[];
    }
    console.log(couponList);
    var interval_id_coupon;

    function addCoupon(){
        var couponIdArr = [];
        console.log(couponList);
        for (let index = 0; index < couponList.length; index++) {
            const element = couponList[index];
            couponIdArr.push(element.id)
        }
        console.log(couponIdArr.toString());


        var addNoteUrl = "{:Url('select_coupon')}?ids=" +couponIdArr.toString()
        $eb.createModalFrame(this.innerText,addNoteUrl,{w:700,h:560})
        interval_id_coupon = setInterval('get_cache_coupon()',1000);
    }

    //轮询获取选择的商品
    //     var interval_id = setInterval('get_cache()',1000);


    function get_cache_coupon(){
        $.ajax({
            url:"{:Url('get_cache_coupon')}",
            data:{},
            type:'post',
            dataType:'json',
            success:function(re){
                if(re.code == 200){
                    console.log(re.data);
                    couponList = re.data;


                    let htmllet = "";
                    for (let index = 0; index < re.data.length; index++) {
                        const element = re.data[index];
                        htmllet += `<tr>
                            <td>${element.title}</td>
                            <td>${element.type_text}</td>
                            <td>${element.time_text}</td>
                            <td>${element.count_text}</td>
                            <td><button type="button" class="layui-btn layui-btn-danger" onclick="delCoupon(this,${index})">删除</button></td>
                        </tr>`;
                    }
                    document.getElementById("list-container_coupon").innerHTML = htmllet;
                    //停止轮询
                    clearInterval(interval_id_coupon);
                }
            },

        })
    }

    function delCoupon(Obj,index){
//        console.log(couponList)
        couponList.splice(index, 1)
//        console.log(couponList);
//        Obj.parentNode.parentNode.parentNode.removeChild(Obj.parentNode.parentNode);
        let htmllet = "";
        for (let index = 0; index < couponList.length; index++) {
            const element = couponList[index];
            htmllet += `<tr>
                            <td>${element.title}</td>
                            <td>${element.type_text}</td>
                            <td>${element.time_text}</td>
                            <td>${element.count_text}</td>
                            <td><button type="button" class="layui-btn layui-btn-danger" onclick="delCoupon(this,${index})">删除</button></td>
                        </tr>`;
        }
        document.getElementById("list-container_coupon").innerHTML = htmllet;
    }

    //---------------------------------------------------------------------
    //选择笔记的方法-end
    //---------------------------------------------------------------------


    //---------------------------------------------------------------------
    //选择用户标签的方法-start
    //---------------------------------------------------------------------
    var tagList;
    var tagListjson;
    tagListjson=$('#user_tag_json').val();
    if(tagListjson){
        tagList = JSON.parse($('#user_tag_json').val());
    }else{
        tagList=[];
    }
    console.log(tagList);
    var interval_id_tag;

    function addTag(){
        var tagIdArr = [];
        console.log(tagList);
        for (let index = 0; index < tagList.length; index++) {
            const element = tagList[index];
            tagIdArr.push(element.id)
        }
        console.log(tagIdArr.toString());


        var addTagUrl = "{:Url('select_tag')}?ids=" +tagIdArr.toString()
        $eb.createModalFrame(this.innerText,addTagUrl,{w:700,h:560})
        interval_id_tag = setInterval('get_cache_tag()',1000);
    }


    function get_cache_tag(){
        $.ajax({
            url:"{:Url('get_cache_tag')}",
            data:{},
            type:'post',
            dataType:'json',
            success:function(re){
                if(re.code == 200){
                    console.log(re.data);
                    tagList = re.data


                    let htmllet = "";
                    for (let index = 0; index < re.data.length; index++) {
                        const element = re.data[index];
                        htmllet += `<tr>
                            <td>${element.title}</td>
                            <td>${element.is_auto_text}</td>
                            <td><button type="button" class="layui-btn layui-btn-danger" onclick="delTag(this,${index})">删除</button></td>
                        </tr>`;
                    }
                    document.getElementById("list-container_tag").innerHTML = htmllet;
                    //停止轮询
                    clearInterval(interval_id_tag);
                }
            },

        })
    }

    function delTag(Obj,index){
        console.log(tagList)
        tagList.splice(index, 1)
        console.log(tagList);
//        Obj.parentNode.parentNode.parentNode.removeChild(Obj.parentNode.parentNode);
        let htmllet = "";
        for (let index = 0; index < tagList.length; index++) {
            const element = tagList[index];
            htmllet += `<tr>
                            <td>${element.title}</td>
                            <td>${element.is_auto_text}</td>
                            <td><button type="button" class="layui-btn layui-btn-danger" onclick="delTag(this,${index})">删除</button></td>
                        </tr>`;
        }
        document.getElementById("list-container_tag").innerHTML = htmllet;
    }

    //---------------------------------------------------------------------
    //选择标签的方法-end
    //---------------------------------------------------------------------

</script>
{/block}