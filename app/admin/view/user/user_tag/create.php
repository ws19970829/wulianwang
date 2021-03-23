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
<input type="hidden"  id="product_json_input" value="{$product_list_json}">
<input type="hidden"  id="city_list_json" value="{$city_list_json}">

<div class="row">
    <div class="col-sm-12 panel panel-default" >
        <div class="panel-body" style="padding: 30px">
            <form class="form-horizontal" id="signupForm">
                <div class="form-group">
                    <div class="col-md-12">
                        <div class="input-group">
                            <span class="input-group-addon">标签名称</span>
                            <input maxlength="64" placeholder="请在这里输入名称" name="title" class="layui-input" id="title" value="{$news.title}">
                            <input type="hidden"  id="id" value="{$news.id}">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <div class="col-md-12">
                            <label style="color:#aaa">标签类型</label>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="radio" name="is_auto" id="is_auto1"  class="layui-radio" value="0" {eq name="news['is_auto']" value="0"}checked{/eq}><label for="is_auto1">&nbsp;&nbsp;手动标签</label>&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="radio" name="is_auto" id="is_auto2"  class="layui-radio" value="1" {eq name="news['is_auto']" value="1"}checked{/eq}><label for="is_auto2">&nbsp;&nbsp;自动标签</label>&nbsp;&nbsp;&nbsp;&nbsp;
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-12">
                        <div class="col-md-12">
                            <label style="color:#aaa">满足条件</label>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_type(1)" type="radio" name="type" id="type1" class="layui-radio" value="1" {eq name="news['type']" value="1"}checked{/eq}><label for="type1">&nbsp;&nbsp;满足任意一个被选中条件即可</label>&nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_type(2)" type="radio" name="type" id="type2" class="layui-radio" value="2" {eq name="news['type']" value="2"}checked{/eq}><label for="type2">&nbsp;&nbsp;必须满足所有条件</label>&nbsp;&nbsp;&nbsp;&nbsp;
                        </div>
                    </div>
                </div>



                <div class="form-group">
                    <div class="col-md-12">
                        <div class="col-md-12">
                            <label style="color:#aaa">最后的消费时间</label>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_pay_time_type(0)"  type="radio" name="pay_time_type" id="pay_time_type0" class="layui-radio" value="0" {eq name="news['pay_time_type']" value="0"}checked{/eq}><label for="pay_time_type0">&nbsp;&nbsp;不启用</label>&nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_pay_time_type(1)"  type="radio" name="pay_time_type" id="pay_time_type1" class="layui-radio" value="1" {eq name="news['pay_time_type']" value="1"}checked{/eq}><label for="pay_time_type1">&nbsp;&nbsp;最近几天</label>&nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_pay_time_type(2)"  type="radio" name="pay_time_type" id="pay_time_type2" class="layui-radio" value="2" {eq name="news['pay_time_type']" value="2"}checked{/eq}><label for="pay_time_type2">&nbsp;&nbsp;指定时间区间</label>&nbsp;&nbsp;&nbsp;&nbsp;
                        </div>
                    </div>
                </div>
                <div class="layui-inline dis_pay_time_type1 {if $news.pay_time_type!=1}hide{/if}">
                    <label class="layui-form-label">最近几天</label>
                    <div class="layui-input-inline">
                        <input type="text" name="last_day" id="last_day" value="{$news.last_day}" placeholder="最近几天" autocomplete="off" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline dis_pay_time_type2 {if $news.pay_time_type!=2}hide{/if}">
                    <label class="layui-form-label">指定时间区间</label>
                    <div class="layui-input-inline" style="width: 200px;">
                        <input type="text" name="pay_start_time" id="start_time" value="{$news.pay_start_time}"  placeholder="结束时间" autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-form-mid">-</div>
                    <div class="layui-input-inline" style="width: 200px;">
                        <input type="text" name="pay_end_time" id="end_time" value="{$news.pay_end_time}" placeholder="开始时间" autocomplete="off" class="layui-input">
                    </div>
                </div>


                <div class="form-group">
                    <div class="col-md-12">
                        <div class="col-md-12">
                            <label style="color:#aaa">累积消费次数</label>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_pay_num_type(0)"  type="radio" name="is_pay_num_type" id="is_pay_num_type0" class="layui-radio" value="0" {eq name="news['is_pay_num_type']" value="0"}checked{/eq}><label for="is_pay_num_type0">&nbsp;&nbsp;不启用</label>&nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_pay_num_type(1)"  type="radio" name="is_pay_num_type" id="is_pay_num_type1" class="layui-radio" value="1" {eq name="news['is_pay_num_type']" value="1"}checked{/eq}><label for="is_pay_num_type1">&nbsp;&nbsp;启用</label>&nbsp;&nbsp;&nbsp;&nbsp;
                        </div>
                    </div>
                </div>
                <div class="layui-inline dis_is_pay_num_type {if $news.is_pay_num_type!=1}hide{/if}">
                    <label class="layui-form-label">累积消费次数</label>
                    <div class="layui-input-inline">
                        <input type="text" name="pay_num_lower" id="pay_num_lower" value="{$news.pay_num_lower}"  placeholder="至少几次" autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-form-mid">-</div>
                    <div class="layui-input-inline">
                        <input type="text" name="pay_num_upper" id="pay_num_upper" value="{$news.pay_num_upper}" placeholder="最多几次" autocomplete="off" class="layui-input">
                    </div>
                </div>



                <div class="form-group">
                    <div class="col-md-12">
                        <div class="col-md-12">
                            <label style="color:#aaa">累积消费金额</label>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_pay_money_type(0)"  type="radio" name="is_pay_money_type" id="is_pay_money_type0" class="layui-radio" value="0" {eq name="news['is_pay_money_type']" value="0"}checked{/eq}><label for="is_pay_money_type0">&nbsp;&nbsp;不启用</label>&nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_pay_money_type(1)"  type="radio" name="is_pay_money_type" id="is_pay_money_type1" class="layui-radio" value="1" {eq name="news['is_pay_money_type']" value="1"}checked{/eq}><label for="is_pay_money_type1">&nbsp;&nbsp;启用</label>&nbsp;&nbsp;&nbsp;&nbsp;
                        </div>
                    </div>
                </div>
                <div class="layui-inline dis_is_pay_money_type {if $news.is_pay_money_type!=1}hide{/if}">
                    <label class="layui-form-label">累积消费金额</label>
                    <div class="layui-input-inline">
                        <input type="text" name="pay_money_lower" id="pay_money_lower" value="{$news.pay_money_lower}"  placeholder="至少金额" autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-form-mid">-</div>
                    <div class="layui-input-inline">
                        <input type="text" name="pay_money_upper" id="pay_money_upper" value="{$news.pay_money_upper}" placeholder="最多金额" autocomplete="off" class="layui-input">
                    </div>
                </div>

              <div class="form-group">
                    <div class="col-md-12">
                        <div class="col-md-12">
                            <label style="color:#aaa">客单价</label>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_is_per_price(0)"  type="radio" name="is_per_price" id="is_per_price0" class="layui-radio" value="0" {eq name="news['is_per_price']" value="0"}checked{/eq}><label for="is_per_price0">&nbsp;&nbsp;不启用</label>&nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_is_per_price(1)"  type="radio" name="is_per_price" id="is_per_price1" class="layui-radio" value="1" {eq name="news['is_per_price']" value="1"}checked{/eq}><label for="is_per_price1">&nbsp;&nbsp;启用</label>&nbsp;&nbsp;&nbsp;&nbsp;
                        </div>
                    </div>
                </div>
                <div class="layui-inline dis_is_per_price {if $news.is_per_price!=1}hide{/if}">
                    <label class="layui-form-label">客单价</label>
                    <div class="layui-input-inline">
                        <input type="text" name="per_price_lower" id="per_price_lower" value="{$news.per_price_lower}"  placeholder="至少金额" autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-form-mid">-</div>
                    <div class="layui-input-inline">
                        <input type="text" name="per_price_upper" id="per_price_upper" value="{$news.per_price_upper}" placeholder="最多金额" autocomplete="off" class="layui-input">
                    </div>
                </div>


                <div class="form-group">
                    <div class="col-md-12">
                        <div class="col-md-12">
                            <label style="color:#aaa">购买以下任意商品</label>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_is_product_type(0)" type="radio" name="is_product_type" class="layui-radio" id="is_product_type0" value="0" {eq name="news['is_product_type']" value="0"}checked{/eq}><label for="is_product_type0">不启用</label>&nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_is_product_type(1)"  type="radio" name="is_product_type" class="layui-radio" id="is_product_type1" value="1" {eq name="news['is_product_type']" value="1"}checked{/eq}><label for="is_product_type1">启用</label>&nbsp;&nbsp;&nbsp;&nbsp;
                        </div>
                    </div>
                </div>

                <div class="layui-btn-container dis_is_product_type {if $news.is_product_type!=1}hide{/if}">
                    <!--                        <a class="layui-btn layui-btn-sm" href="{:Url('index')}">分类首页</a>-->
                    <button type="button" class="layui-btn layui-btn-sm" onclick="addGoods()">添加商品</button>
                </div>
                <table class="layui-table dis_is_product_type {if $news.is_product_type!=1}hide{/if}">
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
                        <td><button type="button" class="layui-btn layui-btn-danger" onclick="delGoods(this,{$key})">删除</button></td>
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
                            <label style="color:#aaa">最近访问时间</label>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_type(0)" type="radio" name="last_view_day" id="last_view_day0" class="layui-radio" value="0" {eq name="news['last_view_day']" value="0"}checked{/eq}><label for="last_view_day0">&nbsp;&nbsp;不限制</label>&nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_type(7)" type="radio" name="last_view_day" id="last_view_day7" class="layui-radio" value="7" {eq name="news['last_view_day']" value="7"}checked{/eq}><label for="last_view_day7">&nbsp;&nbsp;7天</label>&nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_type(30)" type="radio" name="last_view_day" id="last_view_day30" class="layui-radio" value="30" {eq name="news['last_view_day']" value="30"}checked{/eq}><label for="last_view_day30">&nbsp;&nbsp;30天</label>&nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_type(60)" type="radio" name="last_view_day" id="last_view_day60" class="layui-radio" value="60" {eq name="news['last_view_day']" value="60"}checked{/eq}><label for="last_view_day60">&nbsp;&nbsp;60天</label>&nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_type(90)" type="radio" name="last_view_day" id="last_view_day90" class="layui-radio" value="90" {eq name="news['last_view_day']" value="90"}checked{/eq}><label for="last_view_day90">&nbsp;&nbsp;90天</label>&nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_type(180)" type="radio" name="last_view_day" id="last_view_day180" class="layui-radio" value="180" {eq name="news['last_view_day']" value="180"}checked{/eq}><label for="last_view_day180">&nbsp;&nbsp;180天</label>&nbsp;&nbsp;&nbsp;&nbsp;
                        </div>
                    </div>
                </div>


                <div class="form-group">
                    <div class="col-md-12">
                        <div class="col-md-12">
                            <label style="color:#aaa">地区条件</label>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_is_city_type(0)" type="radio" name="is_city_type" class="layui-radio" id="is_city_type0" value="0" {eq name="news['is_city_type']" value="0"}checked{/eq}><label for="is_city_type0">不启用</label>&nbsp;&nbsp;&nbsp;&nbsp;
                            <input onclick="set_is_city_type(1)"  type="radio" name="is_city_type" class="layui-radio" id="is_city_type1" value="1" {eq name="news['is_city_type']" value="1"}checked{/eq}><label for="is_city_type1">启用</label>&nbsp;&nbsp;&nbsp;&nbsp;
                        </div>
                    </div>
                </div>

                <div class="layui-btn-container dis_is_city_type {if $news.is_city_type!=1}hide{/if}">
                    <!--                        <a class="layui-btn layui-btn-sm" href="{:Url('index')}">分类首页</a>-->
                    <button type="button" class="layui-btn layui-btn-sm" onclick="addCity()">添加地区</button>
                </div>
                <table class="layui-table dis_is_city_type {if $news.is_city_type!=1}hide{/if}">
                    <colgroup>
                        <col>
                        <col>
                        <col>
                    </colgroup>
                    <thead>
                    <tr>
                        <th>省</th>
                        <th>市</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody id="list-container_city">
                    {if count($city_list)>0}
                    {foreach $city_list as $key=>$vo}
                    <tr>
                        <td>{$vo.province_name}</td>
                        <td>{$vo.name}</td>
                        <td><button type="button" class="layui-btn layui-btn-danger" onclick="delCity(this,{$key})">删除</button></td>
                    </tr>
                    {/foreach}
                    {else}
                    <tr>
                        <td class="please_select" colspan="3">
                            请选择地区
                        </td>
                    </tr>
                    {/if}
                    </tbody>
                </table>

                <div class="form-group">
                    <div class="col-md-12">
                        <div class="col-md-12">
                            <label style="color:#aaa">性别条件</label>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="radio" name="sex" id="sex0" class="layui-radio" value="0" {eq name="news['sex']" value="0"}checked{/eq}><label for="sex0">&nbsp;&nbsp;不限制</label>&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="radio" name="sex" id="sex1" class="layui-radio" value="1" {eq name="news['sex']" value="1"}checked{/eq}><label for="sex1">&nbsp;&nbsp;男</label>&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="radio" name="sex" id="sex2" class="layui-radio" value="2" {eq name="news['sex']" value="2"}checked{/eq}><label for="sex2">&nbsp;&nbsp;女</label>&nbsp;&nbsp;&nbsp;&nbsp;
                         </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-12">
                        <div class="col-md-12">
                            <label style="color:#aaa">排序</label>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <div class="layui-input-inline">
                                <input type="number" id="sort" name="sort" value="{$news.sort}" class="layui-input">
                            </div>
                        </div>
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
    });



    //商品选择关联
    var goodsList;
    var goodsListjson;
    goodsListjson=$('#product_json_input').val();
    if(goodsListjson){
        goodsList = JSON.parse(goodsListjson);
    }else{
        goodsList=[];
    }


  //地区选择关联
    var cityList;
    var cityListjson;
    cityListjson=$('#city_list_json').val();
    if(cityListjson){
        cityList = JSON.parse(cityListjson);
    }else{
        cityList=[];
    }



    /**
     * 提交图文
     * */
    $('.save_news').on('click',function(){
        var list = {};


        //选择商品关联内容
        var goodsIdArr = [];
        for (let index = 0; index < goodsList.length; index++) {
            const element = goodsList[index];
            goodsIdArr.push(element.id)
        }


        //选择地区关联内容
        var cityIdArr = [];
        for (let index_city = 0; index_city < cityList.length; index_city++) {
            const element_city = cityList[index_city];
            console.log(index_city);
            cityIdArr.push(element_city.id)
        }

        console.log(cityIdArr);
        console.log(cityList.length);


        list.title = $('#title').val();/* 标题 */
        list.id = $('#id').val();
        list.is_auto = $("input[name='is_auto']:checked").val();/* 标签类型 */
        list.type = $("input[name='type']:checked").val();/* 满足条件 */
        list.pay_time_type = $("input[name='pay_time_type']:checked").val();/* 最后的消费时间 */
        list.last_day = $('#last_day').val();/* 最近几天 */
        list.pay_start_time = $('#start_time').val();/* 计划开始时间 */
        list.pay_end_time = $('#end_time').val();/* 计划结束时间 */
        list.is_pay_num_type = $("input[name='is_pay_num_type']:checked").val();/* 累积消费次数 */
        list.pay_num_lower = $('#pay_num_lower').val();/* 累积消费次数 */
        list.pay_num_upper = $('#pay_num_upper').val();/* 累积消费次数 */

        list.is_pay_money_type = $("input[name='is_pay_money_type']:checked").val();/* 累积消费金额 */
        list.pay_money_lower = $('#pay_money_lower').val();/* 累积消费金额 */
        list.pay_money_upper = $('#pay_money_upper').val();/* 累积消费金额 */

        list.is_per_price = $("input[name='is_per_price']:checked").val();/* 客单价 */
        list.per_price_lower = $('#per_price_lower').val();/* 客单价 */
        list.per_price_upper = $('#per_price_upper').val();/* 客单价 */


        list.last_view_day = $("input[name='last_view_day']:checked").val();/* 最近访问时间 */


        list.is_product_type = $("input[name='is_product_type']:checked").val();/* 购买以下任意商品 */
        list.goodsIdArr = goodsIdArr;

        list.is_city_type = $("input[name='is_city_type']:checked").val();/* 地区 */
        list.cityIdArr = cityIdArr;

        list.sex = $("input[name='sex']:checked").val();/* 性别 */
        list.sort = $('#sort').val();/* 排序 */


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
            $('#dis_type2').addClass('hide');
            $('#dis_type3').addClass('hide');
        }

        if(type==2){
            $('#dis_type2').removeClass('hide');
            $('#dis_type1').addClass('hide');
            $('#dis_type3').addClass('hide');
        }

        if(type==3){
            $('#dis_type3').removeClass('hide');
            $('#dis_type1').addClass('hide');
            $('#dis_type2').addClass('hide');
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

    //设置购买任意商品选项
    function set_is_product_type(is_product_type){
        if(is_product_type==1){
            $('.dis_is_product_type').removeClass('hide');
        }else{
            $('.dis_is_product_type').addClass('hide');
        }
    }

    //设置地区选项
    function set_is_city_type(is_city_type){
        if(is_city_type==1){
            $('.dis_is_city_type').removeClass('hide');
        }else{
            $('.dis_is_city_type').addClass('hide');
        }
    }


    //累积消费次数隐藏
    function set_pay_num_type(is_pay_num_type){
        if(is_pay_num_type==1){
            $('.dis_is_pay_num_type').removeClass('hide');
        }else{
            $('.dis_is_pay_num_type').addClass('hide');
        }
    }

  //累积消费次数隐藏
    function set_pay_money_type(is_pay_money_type){
        if(is_pay_money_type==1){
            $('.dis_is_pay_money_type').removeClass('hide');
        }else{
            $('.dis_is_pay_money_type').addClass('hide');
        }
    }

    //客单价
    function set_is_per_price(is_per_price){
        if(is_per_price==1){
            $('.dis_is_per_price').removeClass('hide');
        }else{
            $('.dis_is_per_price').addClass('hide');
        }
    }

    //设置最后消费时间的显示隐藏
    function set_pay_time_type(pay_time_type){
        if(pay_time_type==1){
            $('.dis_pay_time_type2').addClass('hide');
            $('.dis_pay_time_type1').removeClass('hide');
            return false;
        }

        if(pay_time_type==2){
            $('.dis_pay_time_type1').addClass('hide');
            $('.dis_pay_time_type2').removeClass('hide');
            return false;
        }

        $('.dis_pay_time_type1').addClass('hide');
        $('.dis_pay_time_type2').addClass('hide');


    }

    //---------------------------------------------------------------------
    //选择商品的方法-start
    //---------------------------------------------------------------------

    var interval_id;
    function addGoods(){
        var goodsIdArr = [];
        for (let index = 0; index < goodsList.length; index++) {
            const element = goodsList[index];
            goodsIdArr.push(element.id)
        }
        console.log(goodsIdArr.toString());


        var addGoodsUrl = "{:Url('fans.fans_note/select')}?ids=" +goodsIdArr.toString()
        $eb.createModalFrame(this.innerText,addGoodsUrl,{w:700,h:560})
        interval_id = setInterval('get_cache()',1000);
    }

    //轮询获取选择的商品
    // var interval_id = setInterval('get_cache()',1000);


    function get_cache(){
        $.ajax({
            url:"{:Url('fans.fans_note/get_cache')}",
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
    //---------------------------------------------------------------------
    //选择商品的方法-end
    //---------------------------------------------------------------------



    //---------------------------------------------------------------------
    //选择地区的方法-start
    //---------------------------------------------------------------------

    var interval_id_city;
    function addCity(){
        var cityIdArr = [];
        for (let index = 0; index < cityList.length; index++) {
            const element = cityList[index];
            cityIdArr.push(element.id)
        }
        console.log(cityIdArr.toString());


        var addCityUrl = "{:Url('user.user_tag/city')}?ids=" +cityIdArr.toString()
        $eb.createModalFrame(this.innerText,addCityUrl,{w:890,h:660})
        interval_id_city = setInterval('get_cache_city()',1000);
    }

    //轮询获取选择的商品
    // var interval_id = setInterval('get_cache()',1000);


    function get_cache_city(){
        $.ajax({
            url:"{:Url('user.user_tag/get_cache_city')}",
            data:{},
            type:'post',
            dataType:'json',
            success:function(re){
                if(re.code == 200){
                    console.log(re.data);
                    cityList = re.data


                    let htmllet = "";
                    for (let index = 0; index < re.data.length; index++) {
                        const element = re.data[index];
                        htmllet += `<tr>
                            <td>${element.province_name}</td>
                            <td>${element.name}</td>
                            <td><button type="button" class="layui-btn layui-btn-danger" onclick="delCity(this,${index})">删除</button></td>
                        </tr>`;
                    }
                    document.getElementById("list-container_city").innerHTML = htmllet;
                    //停止轮询
                    clearInterval(interval_id_city);
                }
            },

        })
    }

    function delCity(Obj,index){
        console.log(cityList)
        cityList.splice(index, 1)
        console.log(cityList);
//        Obj.parentNode.parentNode.parentNode.removeChild(Obj.parentNode.parentNode);
        let htmllet = "";
        for (let index = 0; index < cityList.length; index++) {
            const element = cityList[index];
            htmllet += `<tr>
                            <td>${element.province_name}</td>
                            <td>${element.name}</td>
                            <td><button type="button" class="layui-btn layui-btn-danger" onclick="delCity(this,${index})">删除</button></td>
                        </tr>`;
        }
        document.getElementById("list-container_city").innerHTML = htmllet;
    }
    //---------------------------------------------------------------------
    //选择商品的方法-end
    //---------------------------------------------------------------------


</script>
{/block}