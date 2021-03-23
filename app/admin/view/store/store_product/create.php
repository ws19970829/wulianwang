<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link href="{__FRAME_PATH}css/font-awesome.min.css" rel="stylesheet">
    <link href="{__ADMIN_PATH}plug/umeditor/themes/default/css/umeditor.css" type="text/css" rel="stylesheet">
    <script type="text/javascript" src="{__ADMIN_PATH}plug/umeditor/third-party/jquery.min.js"></script>
    <script type="text/javascript" src="{__ADMIN_PATH}plug/umeditor/third-party/template.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="{__ADMIN_PATH}plug/umeditor/umeditor.config.js"></script>
    <script type="text/javascript" charset="utf-8" src="{__ADMIN_PATH}plug/umeditor/umeditor.min.js"></script>
    <script type="text/javascript" src="{__ADMIN_PATH}plug/umeditor/lang/zh-cn/zh-cn.js"></script>
    <link rel="stylesheet" href="/static/plug/layui/css/layui.css">
    <script src="/static/plug/layui/layui.js"></script>
    <script src="{__PLUG_PATH}vue/dist/vue.min.js"></script>
    <script src="/static/plug/axios.min.js"></script>
    <script src="{__MODULE_PATH}widget/aliyun-oss-sdk-4.4.4.min.js"></script>
    <script src="{__MODULE_PATH}widget/cos-js-sdk-v5.min.js"></script>
    <script src="{__MODULE_PATH}widget/qiniu-js-sdk-2.5.5.js"></script>
    <script src="{__MODULE_PATH}widget/plupload.full.min.js"></script>
    <script src="{__MODULE_PATH}widget/videoUpload.js"></script>
    <style>
        .layui-form-item {
            margin-bottom: 0px;
        }

        .pictrueBox {
            display: inline-block !important;
        }

        .pictrue {
            width: 60px;
            height: 60px;
            border: 1px dotted rgba(0, 0, 0, 0.1);
            margin-right: 15px;
            display: inline-block;
            position: relative;
            cursor: pointer;
        }

        .pictrue img {
            width: 100%;
            height: 100%;
        }

        .upLoad {
            width: 58px;
            height: 58px;
            line-height: 58px;
            border: 1px dotted rgba(0, 0, 0, 0.1);
            border-radius: 4px;
            background: rgba(0, 0, 0, 0.02);
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .rulesBox {
            display: flex;
            flex-wrap: wrap;
            margin-left: 10px;
        }

        .layui-tab-content {
            margin-top: 15px;
        }

        .ml110 {
            margin: 18px 0 4px 110px;
        }

        .rules {
            display: flex;
        }

        .rules-btn-sm {
            height: 30px;
            line-height: 30px;
            font-size: 12px;
            width: 109px;
        }

        .rules-btn-sm input {
            width: 79% !important;
            height: 84% !important;
            padding: 0 10px;
        }

        .ml10 {
            margin-left: 10px !important;
        }

        .ml40 {
            margin-left: 40px !important;
        }

        .closes {
            position: absolute;
            left: 86%;
            top: -18%;
        }

        .red {
            color: red;
        }

        .layui-input-block .layui-video-box {
            width: 22%;
            height: 180px;
            border-radius: 10px;
            background-color: #707070;
            margin-top: 10px;
            position: relative;
            overflow: hidden;
        }

        .layui-input-block .layui-video-box i {
            color: #fff;
            line-height: 180px;
            margin: 0 auto;
            width: 50px;
            height: 50px;
            display: inherit;
            font-size: 50px;
        }

        .layui-input-block .layui-video-box .mark {
            position: absolute;
            width: 100%;
            height: 30px;
            top: 0;
            background-color: rgba(0, 0, 0, .5);
            text-align: center;
        }

        .store_box {
            display: flex;
        }

        .info {
            color: #c9c9c9;
            padding-left: 10px;
            line-height: 30px;
        }
    </style>
</head>

<body>
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15" id="app" v-cloak="">
            <div class="layui-card">
                <div class="layui-card-header">
                    <span class="">{{id ? '商品修改': '商品添加' }}</span>
                    <button style="margin-left: 20px" type="button" class="layui-btn layui-btn-primary layui-btn-xs" @click="goBack">返回列表</button>
                </div>
                <div class="layui-card-body">
                    <form class="layui-form" action="" v-cloak="">
                        <div class="layui-tab layui-tab-brief" lay-filter="docTabBrief">
                            <ul class="layui-tab-title">
                                <li class="layui-this" lay-id='1'>基础信息</li>
                                <li lay-id='2'>商品详情</li>
                                <li lay-id='3'>其他设置</li>
                            </ul>
                            <div class="layui-tab-content">
                                <div class="layui-tab-item layui-show">
                                    <div class="layui-row layui-col-space15">
                                        <div class="layui-col-xs12 layui-col-sm12 layui-col-md12">
                                            <div class="grid-demo grid-demo-bg1">
                                                <label class="layui-form-label">商品分类<i class="red">*</i></label>
                                                <div class="layui-form-item">
                                                    <!-- <label class="layui-form-label">商品分类<i class="red">*</i></label> -->
                                                    <!-- <div class="layui-input-block" id="cate_id">
                                                </div> -->
                                                    <div id="tree" class="demo-tree-more"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="layui-col-xs12 layui-col-sm12 layui-col-md12">
                                            <div class="grid-demo grid-demo-bg1">
                                                <div class="layui-form-item">
                                                    <label class="layui-form-label">商品名称<i class="red">*</i></label>
                                                    <div class="layui-input-block">
                                                        <input type="text" name="store_name" lay-verify="title" autocomplete="off" placeholder="请输入商品名称" class="layui-input" v-model="formData.store_name" maxlength="100">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="layui-col-xs2 layui-col-sm2 layui-col-md2">
                                            <div class="grid-demo grid-demo-bg1">
                                                <div class="layui-form-item">
                                                    <label class="layui-form-label">起订量（展示）
                                                        <i class="red">*</i></label>
                                                    <div class="layui-input-block">
                                                        <input type="number" name="moq" lay-verify="number" autocomplete="off" placeholder="请输入起订量" class="layui-input" v-model="formData.moq" min='0'>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="layui-col-xs2 layui-col-sm2 layui-col-md22">
                                            <div class="grid-demo grid-demo-bg1">
                                                <div class="layui-form-item">
                                                    <label class="layui-form-label">展示价格</label>
                                                    <div class="layui-input-block">
                                                        <input type="number" name="price" lay-verify="number" autocomplete="off" placeholder="￥/元" class="layui-input" v-model="formData.price" min="0">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="layui-col-xs12 layui-col-sm12 layui-col-md12">
                                            <div class="grid-demo grid-demo-bg1">
                                                <div class="layui-form-item">
                                                    <label class="layui-form-label">商品关键字</label>
                                                    <div class="layui-input-block">
                                                        <input style="width: 40%" type="text" name="keyword" lay-verify="title" autocomplete="off" placeholder="请输入商品关键字" class="layui-input" v-model="formData.keyword">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="layui-col-xs12 layui-col-sm12 layui-col-md12">
                                            <div class="grid-demo grid-demo-bg1">
                                                <div class="layui-form-item">
                                                    <label class="layui-form-label">单位</label>
                                                    <div class="layui-input-block">
                                                        <input style="width: 40%" type="text" name="unit_name" lay-verify="title" autocomplete="off" placeholder="请输入单位" class="layui-input" v-model="formData.unit_name">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="layui-col-xs12 layui-col-sm12 layui-col-md12">
                                            <div class="grid-demo grid-demo-bg1">
                                                <div class="layui-form-item layui-form-text">
                                                    <label class="layui-form-label">商品简介</label>
                                                    <div class="layui-input-block">
                                                        <textarea name="store_info" v-model="formData.store_info" placeholder="请输入商品简介" class="layui-textarea"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="layui-form-item submit">
                                            <label class="layui-form-label">主图视频</label>
                                            <div class="layui-input-block">
                                                <input type="text" name="link_key" v-model="videoLink" style="width:50%;display:inline-block;margin-right: 10px;" autocomplete="off" placeholder="请输入视频链接" class="layui-input">
                                                <button type="button" @click="uploadVideo" class="layui-btn layui-btn-sm layui-btn-normal">{{videoLink ? '确认添加' : '上传视频'}}</button>
                                                <input ref="filElem" type="file" style="display: none">
                                            </div>
                                            <div class="layui-input-block video_show" style="width: 30%;margin-top: 20px;" v-if="upload.videoIng">
                                                <div class="layui-progress" style="margin-bottom: 10px">
                                                    <div class="layui-progress-bar layui-bg-blue" :style="'width:'+progress+'%'"></div>
                                                </div>
                                                <button type="button" class="layui-btn layui-btn-sm layui-btn-danger percent">{{progress}}%</button>
                                            </div>
                                            <div class="layui-input-block" v-if="formData.video_link">
                                                <div class="layui-video-box" v-if="formData.video_link">
                                                    <video style="width:100%;height: 100%!important;border-radius: 10px;" :src="formData.video_link" controls="controls">
                                                        您的浏览器不支持 video 标签。
                                                    </video>
                                                    <div class="mark" @click="delVideo">
                                                        <span class="layui-icon layui-icon-delete" style="font-size: 30px; color: #1E9FFF;"></span>
                                                    </div>

                                                </div>
                                                <div class="layui-video-box" v-else>
                                                    <i class="layui-icon layui-icon-play"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="layui-col-xs12 layui-col-sm12 layui-col-md12">
                                            <div class="grid-demo grid-demo-bg1">
                                                <div class="layui-form-item">
                                                    <label class="layui-form-label">商品封面图<i class="red">*</i>
                                                        <br>
                                                        <span style="color: #999;font-size: 12px;">(建议正方形)</span>
                                                    </label>
                                                    <div class="pictrueBox">
                                                        <div class="pictrue" v-if="formData.image" @click="uploadImage('image')">
                                                            <img :src="formData.image"></div>
                                                        <div class="upLoad" @click="uploadImage('image')" v-else>
                                                            <i class="layui-icon layui-icon-camera" class="iconfont" style="font-size: 26px;"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="layui-col-xs12 layui-col-sm12 layui-col-md12">
                                            <div class="grid-demo grid-demo-bg1">
                                                <div class="layui-form-item">
                                                    <label class="layui-form-label">商品轮播图<i class="red">*</i>
                                                        <br>
                                                        <span style="color: #999;font-size: 12px;">(建议正方形)</span>
                                                    </label>
                                                    <div class="pictrueBox pictrue" v-for="(item,index) in formData.slider_image">
                                                        <img :src="item">
                                                        <i class="layui-icon closes" @click="deleteImage('slider_image',index)">&#x1007</i>
                                                    </div>
                                                    <div class="pictrueBox">
                                                        <div class="upLoad" @click="uploadImage('slider_image')" v-if="formData.slider_image.length <= rule.slider_image.maxLength">
                                                            <i class="layui-icon layui-icon-camera" class="iconfont" style="font-size: 26px;"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- <div class="layui-col-xs12 layui-col-sm12 layui-col-md12">
                                        <div class="grid-demo grid-demo-bg1">
                                            <div class="layui-form-item">
                                                <label class="layui-form-label">商品规格<i class="red">*</i></label>
                                                <div class="layui-input-block">
                                                    <input type="radio" name="spec_type" value="0" title="单规格"
                                                           lay-filter="spec_type"
                                                           :checked="formData.spec_type == 0 ? true : false">
                                                    <input type="radio" name="spec_type" value="1" title="多规格"
                                                           lay-filter="spec_type"
                                                           :checked="formData.spec_type == 1 ? true : false">
                                                </div>
                                            </div>
                                        </div>
                                    </div> -->

                                        <div class="layui-col-xs12 layui-col-sm12 layui-col-md12" style="margin-top: 20px">
                                            <div class="layui-form-item">
                                                <label class="layui-form-label">商品属性：</label>
                                            </div>
                                        </div>
                                        <div class="grid-demo grid-demo-bg1" style="margin-top: 20px">
                                            <div class="layui-form-item">
                                                <div class="layui-input-block" style="margin-left:0">
                                                    <table class="layui-table">
                                                        <thead>
                                                            <tr>
                                                                <!-- <th>图片<i class="red">*</i></th> -->
                                                                <!-- <th>售价<i class="red">*</i></th>
                                                                <th>成本价</th>
                                                                <th>原价<i class="red">*</i></th> -->
                                                                <th style="width:25%">属性 <i class="red"></i><span style="font-size: 12px;color:red" >(名称请勿重复)</span></th>
                                                                <th>库存<i class="red">*</i></th>
                                                                <!-- <th>产品编号</th> -->
                                                                <!-- <th>重量(KG)</th>
                                                                <th>体积(m³)</th> -->
                                                                <th>起订量</th>
                                                                <th>优惠量</th>
                                                                <th>大于等于优惠量的售价</th>
                                                                <th>小于优惠量的售价</th>
                                                                <th>排序</th>
                                                                <th width="16%" style="text-align: center;">操作</th>
                                                            </tr>
                                                        </thead>
                                                        <tr>
                                                            <!-- <td>
                                                                    <div class="pictrueBox">
                                                                        <div class="pictrue" v-if="batchAttr.pic"
                                                                             @click="uploadImage('batchAttr.pic')"><img
                                                                                    :src="batchAttr.pic"></div>
                                                                        <div class="upLoad" @click="uploadImage('batchAttr.pic')"
                                                                             v-else>
                                                                            <i class="layui-icon layui-icon-camera" class="iconfont"
                                                                               style="font-size: 26px;"></i>
                                                                        </div>
                                                                    </div>
                                                                </td> -->

                                                            <td><input type="text" v-model="batchAttr.suk" class="layui-input"></td>
                                                            <input type="hidden" v-model="batchAttr.price" class="layui-input">

                                                            <!-- <td><input type="text" v-model="batchAttr.cost"
                                                                           class="layui-input"></td> -->
                                                            <input type="hidden" v-model="batchAttr.cost" class="layui-input">
                                                            <!-- 
                                                                <td><input type="text" v-model="batchAttr.ot_price"
                                                                           class="layui-input"></td> -->

                                                            <input type="hidden" v-model="batchAttr.ot_price" class="layui-input">

                                                            <td>
                                                                <input type="text" v-model="batchAttr.stock" class="layui-input">
                                                            </td>
                                                            <!-- 
                                                                <td>
                                                                    <input type="text" v-model="batchAttr.bar_code"
                                                                           class="layui-input">
                                                                </td> -->
                                                            <input type="hidden" v-model="batchAttr.bar_code" class="layui-input">
                                                            <!-- <td>
                                                                    <input type="text" v-model="batchAttr.weight"
                                                                           class="layui-input">
                                                                </td> -->
                                                            <input type="hidden" v-model="batchAttr.weight" class="layui-input">
                                                            <!-- <td>
                                                                    <input type="text" v-model="batchAttr.volume"
                                                                           class="layui-input">
                                                                </td> -->
                                                            <input type="hidden" v-model="batchAttr.volume" class="layui-input">
                                                            <td>
                                                                <input type="number" v-model="batchAttr.moq" class="layui-input">
                                                            </td>
                                                            <td>
                                                                <input type="number" v-model="batchAttr.discount" class="layui-input">
                                                            </td>
                                                            <td>
                                                                <input type="number" v-model="batchAttr.discount_gt" class="layui-input" min=0>
                                                            </td>
                                                            <td>
                                                                <input type="number" v-model="batchAttr.discount_lt" class="layui-input">
                                                            </td>
                                                            <td>
                                                                <input type="number" v-model="batchAttr.sort" class="layui-input" min="0">
                                                            </td>
                                                            <td style="text-align: center;">
                                                                <button class="layui-btn layui-btn-xs" type="button" @click="addParam">新增属性
                                                                </button>
                                                                <button class="layui-btn layui-btn-xs" type="button" @click="batchAdd">批量修改
                                                                </button>
                                                                <!-- <button class="layui-btn layui-btn-sm layui-btn-danger" type="button"
                                                                            @click="batchClear">清空
                                                                    </button> -->
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>



                                        <div class="layui-col-xs12 layui-col-sm12 layui-col-md12" style="margin-top: 10px">
                                            <div class="layui-form-item" style="margin-left: 0">
                                                <!-- <label class="layui-form-label">商品属性：</label> -->
                                                <div class="layui-input-block" style="margin-left:0">
                                                    <table class="layui-table">
                                                        <thead>
                                                            <tr>
                                                                <th v-for="(item,index) in formHeader" v-if="item.align">
                                                                    {{item.title}}
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tr v-for="(item,index) in formData.attrs">
                                                            <!-- <td v-for="(n,v) in item.detail">{{n}}</td> -->
                                                            <!-- <td>
                                                                    <div class="pictrueBox">
                                                                        <div class="pictrue" v-if="item.pic"
                                                                             @click="uploadImage('attrs.'+index+'.pic')"><img
                                                                                    :src="item.pic"></div>
                                                                        <div class="upLoad" @click="uploadImage('attrs.'+index+'.pic')"
                                                                             v-else>
                                                                            <i class="layui-icon layui-icon-camera"
                                                                               class="iconfont" style="font-size: 26px;"></i>
                                                                        </div>
                                                                    </div>
                                                                </td> -->
                                                            <td style="width:25%">
                                                                <input type="text" v-model="item.suk" class="layui-input">
                                                            </td>
                                                            <input type="hidden" v-model="item.advance_sale" class="layui-input">
                                                            <input type="hidden" v-model="item.advance_day" class="layui-input">
                                                            <input type="hidden" v-model="item.advance_time" class="layui-input">
                                                            <!-- 
                                                                <td><input type="number" v-model="item.price"
                                                                           class="layui-input"></td> -->
                                                            <input type="hidden" v-model="item.price" class="layui-input">

                                                            <input type="hidden" v-model="item.cost" class="layui-input">

                                                            <input type="hidden" v-model="item.ot_price" class="layui-input">

                                                            <!-- <td><input type="number" v-model="item.cost"
                                                                           class="layui-input"></td>
                                                                <td><input type="number" v-model="item.ot_price"
                                                                           class="layui-input"></td> -->
                                                            <input type="hidden" v-model="item.bar_code" class="layui-input">

                                                            <td><input type="number" v-model="item.stock" class="layui-input"></td>
                                                            <!-- <td>
                                                                    <input type="text" v-model="item.bar_code"
                                                                           class="layui-input">
                                                                </td> -->
                                                            <input type="hidden" v-model="item.weight" class="layui-input">
                                                            <input type="hidden" v-model="item.volume" class="layui-input">
                                                            <!-- <td>
                                                                    <input type="number" v-model="item.weight"
                                                                           class="layui-input">
                                                                </td>
                                                                <td>
                                                                    <input type="number" v-model="item.volume"
                                                                           class="layui-input">
                                                                </td> -->
                                                            <td>
                                                                <input type="number" v-model="item.moq" class="layui-input" min=0>
                                                            </td>
                                                            <td>
                                                                <input type="number" v-model="item.discount" class="layui-input" min=0>
                                                            </td>
                                                            <td>
                                                                <input type="number" v-model="item.discount_gt" class="layui-input" min=0>
                                                            </td>
                                                            <td>
                                                                <input type="number" v-model="item.discount_lt" class="layui-input" min=0>
                                                            </td>
                                                            <td>
                                                                <input type="number" v-model="item.sort" class="layui-input"min=0>
                                                            </td>
                                                            <td>
                                                                <button class="layui-btn layui-btn-sm layui-btn-danger" type="button" @click="deleteAttrs(index)">删除
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="layui-tab-item">
                                    <div class="layui-row layui-col-space15">
                                        <textarea type="text/plain" name="description" id="myEditor" style="width:100%;">{{formData.description}}</textarea>
                                    </div>
                                </div>
                                <div class="layui-tab-item">
                                    <div class="layui-row layui-col-space15">
                                        <!-- <div class="layui-col-xs12 layui-col-sm4 layui-col-md4">
                                        <div class="grid-demo grid-demo-bg1">
                                            <div class="layui-form-item">
                                                <label class="layui-form-label">虚拟销量</label>
                                                <div class="layui-input-block">
                                                    <input type="number" name="ficti" lay-verify="title" autocomplete="off"
                                                           placeholder="请输入虚拟销量" class="layui-input" v-model="formData.ficti">
                                                </div>
                                            </div>
                                        </div>
                                    </div> -->
                                        <!--                                    <div class="layui-col-xs12 layui-col-sm4 layui-col-md4">-->
                                        <!--                                        <div class="grid-demo grid-demo-bg1">-->
                                        <!--                                            <div class="layui-form-item">-->
                                        <!--                                                <label class="layui-form-label">积分</label>-->
                                        <!--                                                <div class="layui-input-block">-->
                                        <!--                                                    <input type="number" name="give_integral" lay-verify="title"-->
                                        <!--                                                           autocomplete="off" placeholder="请输入积分" class="layui-input" v-model="formData.give_integral">-->
                                        <!--                                                </div>-->
                                        <!--                                            </div>-->
                                        <!--                                        </div>-->
                                        <!--                                    </div>-->
                                        <div class="layui-col-xs12 layui-col-sm4 layui-col-md4">
                                            <div class="grid-demo grid-demo-bg1">
                                                <div class="layui-form-item">
                                                    <label class="layui-form-label">排序</label>
                                                    <div class="layui-input-block">
                                                        <input type="number" name="sort" lay-verify="title" autocomplete="off" placeholder="请输入排序" class="layui-input" v-model="formData.sort">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="layui-row layui-col-space15">
                                        <div class="layui-col-xs12 layui-col-sm4 layui-col-md4">
                                            <div class="grid-demo grid-demo-bg1">
                                                <div class="layui-form-item">
                                                    <label class="layui-form-label">商品状态</label>
                                                    <div class="layui-input-block">
                                                        <input type="radio" name="is_show" lay-filter="is_show" value="1" title="上架" :checked="formData.is_show == 1 ? true : false">
                                                        <input type="radio" name="is_show" lay-filter="is_show" value="0" title="下架" :checked="formData.is_show == 0 ? true : false">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                        <!-- <div class="layui-col-xs12 layui-col-sm12 layui-col-md12">
                                        <div class="grid-demo grid-demo-bg1">
                                            <div class="layui-form-item">
                                                <label class="layui-form-label">佣金设置</label>
                                                <div class="layui-input-block">
                                                    <input type="radio" name="is_sub" lay-filter="is_sub" value="1" title="单独设置"
                                                           :checked="formData.is_sub == 1 ? true : false">
                                                    <input type="radio" name="is_sub" lay-filter="is_sub" value="0" title="默认设置"
                                                           :checked="formData.is_sub == 0 ? true : false">
                                                </div>
                                            </div>
                                        </div>
                                    </div> -->

                                        <div class="layui-col-xs12 layui-col-sm4 layui-col-md4">
                                            <div class="grid-demo grid-demo-bg1">
                                                <div class="layui-form-item">
                                                    <label class="layui-form-label">是否推荐</label>
                                                    <div class="layui-input-block">
                                                        <input type="radio" name="is_hot" lay-filter="is_hot" value="1" title="开启" :checked="formData.is_hot == 1 ? true : false">
                                                        <input type="radio" name="is_hot" lay-filter="is_hot" value="0" title="关闭" :checked="formData.is_hot == 0 ? true : false">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--多属性结束-->

                                        <!--                                    <div class="layui-col-xs12 layui-col-sm4 layui-col-md4">-->
                                        <!--                                        <div class="grid-demo grid-demo-bg1">-->
                                        <!--                                            <div class="layui-form-item">-->
                                        <!--                                                <label class="layui-form-label">商品状态</label>-->
                                        <!--                                                <div class="layui-input-block">-->
                                        <!--                                                    <input type="radio" name="is_show" lay-filter="is_show" value="1" title="上架"-->
                                        <!--                                                           :checked="formData.is_show == 1 ? true : false">-->
                                        <!--                                                    <input type="radio" name="is_show" lay-filter="is_show" value="0" title="下架"-->
                                        <!--                                                           :checked="formData.is_show == 0 ? true : false">-->
                                        <!--                                                </div>-->
                                        <!--                                            </div>-->
                                        <!--                                        </div>-->
                                        <!--                                    </div>-->
                                        <!--                                    <div class="layui-col-xs12 layui-col-sm4 layui-col-md4">-->
                                        <!--                                        <div class="grid-demo grid-demo-bg1">-->
                                        <!--                                            <div class="layui-form-item">-->
                                        <!--                                                <label class="layui-form-label">热卖单品</label>-->
                                        <!--                                                <div class="layui-input-block">-->
                                        <!--                                                    <input type="radio" name="is_hot" lay-filter="is_hot" value="1" title="开启"-->
                                        <!--                                                           :checked="formData.is_hot == 1 ? true : false">-->
                                        <!--                                                    <input type="radio" name="is_hot" lay-filter="is_hot" value="0" title="关闭"-->
                                        <!--                                                           :checked="formData.is_hot == 0 ? true : false">-->
                                        <!--                                                </div>-->
                                        <!--                                            </div>-->
                                        <!--                                        </div>-->
                                        <!--                                    </div>-->
                                        <!--                                    <div class="layui-col-xs12 layui-col-sm4 layui-col-md4">-->
                                        <!--                                        <div class="grid-demo grid-demo-bg1">-->
                                        <!--                                            <div class="layui-form-item">-->
                                        <!--                                                <label class="layui-form-label">促销单品</label>-->
                                        <!--                                                <div class="layui-input-block">-->
                                        <!--                                                    <input type="radio" name="is_benefit" lay-filter="is_benefit" value="1" title="开启"-->
                                        <!--                                                           :checked="formData.is_benefit == 1 ? true : false">-->
                                        <!--                                                    <input type="radio" name="is_benefit" lay-filter="is_benefit" value="0" title="关闭"-->
                                        <!--                                                           :checked="formData.is_benefit == 0 ? true : false">-->
                                        <!--                                                </div>-->
                                        <!--                                            </div>-->
                                        <!--                                        </div>-->
                                        <!--                                    </div>-->
                                        <!--                                    <div class="layui-col-xs12 layui-col-sm4 layui-col-md4">-->
                                        <!--                                        <div class="grid-demo grid-demo-bg1">-->
                                        <!--                                            <div class="layui-form-item">-->
                                        <!--                                                <label class="layui-form-label">精品推荐</label>-->
                                        <!--                                                <div class="layui-input-block">-->
                                        <!--                                                    <input type="radio" name="is_best" lay-filter="is_best" value="1" title="开启"-->
                                        <!--                                                           :checked="formData.is_best == 1 ? true : false">-->
                                        <!--                                                    <input type="radio" name="is_best" lay-filter="is_best" value="0" title="关闭"-->
                                        <!--                                                           :checked="formData.is_best == 0 ? true : false">-->
                                        <!--                                                </div>-->
                                        <!--                                            </div>-->
                                        <!--                                        </div>-->
                                        <!--                                    </div>-->
                                        <!--                                    <div class="layui-col-xs12 layui-col-sm4 layui-col-md4">-->
                                        <!--                                        <div class="grid-demo grid-demo-bg1">-->
                                        <!--                                            <div class="layui-form-item">-->
                                        <!--                                                <label class="layui-form-label">首发新品</label>-->
                                        <!--                                                <div class="layui-input-block">-->
                                        <!--                                                    <input type="radio" name="is_new" lay-filter="is_new" value="1" title="开启"-->
                                        <!--                                                           :checked="formData.is_new == 1 ? true : false">-->
                                        <!--                                                    <input type="radio" name="is_new" lay-filter="is_new" value="0" title="关闭"-->
                                        <!--                                                           :checked="formData.is_new == 0 ? true : false">-->
                                        <!--                                                </div>-->
                                        <!--                                            </div>-->
                                        <!--                                        </div>-->
                                        <!--                                    </div>-->
                                        <!--                                    <div class="layui-col-xs12 layui-col-sm4 layui-col-md4">-->
                                        <!--                                        <div class="grid-demo grid-demo-bg1">-->
                                        <!--                                            <div class="layui-form-item">-->
                                        <!--                                                <label class="layui-form-label">优品推荐</label>-->
                                        <!--                                                <div class="layui-input-block">-->
                                        <!--                                                    <input type="radio" name="is_good" lay-filter="is_good" value="1" title="开启"-->
                                        <!--                                                           :checked="formData.is_good == 1 ? true : false">-->
                                        <!--                                                    <input type="radio" name="is_good" lay-filter="is_good" value="0" title="关闭"-->
                                        <!--                                                           :checked="formData.is_good == 0 ? true : false">-->
                                        <!--                                                </div>-->
                                        <!--                                            </div>-->
                                        <!--                                        </div>-->
                                        <!--                                    </div>-->
                                        <!--                                    <div class="layui-row layui-col-space15">-->
                                        <!--                                        <div class="layui-col-xs12 layui-col-sm12 layui-col-md12">-->
                                        <!--                                            <div class="grid-demo grid-demo-bg1">-->
                                        <!--                                                <div class="layui-form-item">-->
                                        <!--                                                    <label class="layui-form-label">活动优先级</label>-->
                                        <!--                                                    <div class="layui-input-block">-->
                                        <!--                                                        <span class="layui-btn layui-btn-sm layui-btn-normal" :style="'background-color:'+activity[item]" v-for="(item,index) in formData.activity" :key="index"-->
                                        <!--                                                              draggable="true"-->
                                        <!--                                                              @dragstart="handleDragStart($event, item)"-->
                                        <!--                                                              @dragover.prevent="handleDragOver($event, item)"-->
                                        <!--                                                              @dragenter="handleDragEnter($event, item)"-->
                                        <!--                                                              @dragend="handleDragEnd($event, item)">-->
                                        <!--                                                        {{item}}-->
                                        <!--                                                    </span>-->
                                        <!--                                                        <span class="info">可拖动按钮调整活动的优先展示顺序</span>-->
                                        <!--                                                    </div>-->
                                        <!--                                                </div>-->
                                        <!--                                            </div>-->
                                        <!--                                        </div>-->
                                        <!--                                    </div>-->

                                        <!-- <div class="layui-col-xs12 layui-col-sm4 layui-col-md4">
                                        <div class="grid-demo grid-demo-bg1">
                                            <div class="layui-form-item">
                                                <label class="layui-form-label">显示库存</label>
                                                <div class="layui-input-block">
                                                    <input type="radio" name="view_stock" lay-filter="view_stock" value="1" title="开启"
                                                           :checked="formData.view_stock == 1 ? true : false">
                                                    <input type="radio" name="view_stock" lay-filter="view_stock" value="0" title="关闭"
                                                           :checked="formData.view_stock == 0 ? true : false">
                                                </div>
                                            </div>
                                        </div>
                                    </div> -->

                                        <!-- <div class="layui-col-xs12 layui-col-sm4 layui-col-md4">
                                        <div class="grid-demo grid-demo-bg1">
                                            <div class="layui-form-item">
                                                <label class="layui-form-label">显示销量</label>
                                                <div class="layui-input-block">
                                                    <input type="radio" name="view_sale_num" lay-filter="view_sale_num" value="1" title="开启"
                                                           :checked="formData.view_sale_num == 1 ? true : false">
                                                    <input type="radio" name="view_sale_num" lay-filter="view_sale_num" value="0" title="关闭"
                                                           :checked="formData.view_sale_num == 0 ? true : false">
                                                </div>
                                            </div>
                                        </div>
                                    </div> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="layui-tab-content">
                            <div class="layui-row layui-col-space15">
                                <div class="layui-col-xs12 layui-col-sm12 layui-col-md12">
                                    <div class="grid-demo grid-demo-bg1">
                                        <div class="layui-form-item" v-if="id">
                                            <button class="layui-btn layui-btn-primary layui-btn-sm" id="submit" type="button" @click="handleSubmit()">保存</button>
                                            <button class="layui-btn layui-btn-primary layui-btn-sm" type="button" @click="back" v-if="layTabId != 1">上一步</button>
                                            <button class="layui-btn layui-btn-normal layui-btn-sm" type="button" v-if="layTabId != 3" @click="next">下一步</button>
                                        </div>
                                        <div class="layui-form-item" v-else>
                                            <button class="layui-btn layui-btn-primary layui-btn-sm" type="button" @click="back" v-if="layTabId != 1">上一步</button>
                                            <button class="layui-btn layui-btn-normal layui-btn-sm" type="button" @click="next" v-if="layTabId != 3">下一步</button>
                                            <button class="layui-btn layui-btn-normal layui-btn-sm" id="submit" type="button" v-if="layTabId == 3" @click="handleSubmit()">提交</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
                </form>
            </div>
        </div>
    </div>
    </div>
    </div>
    <script>
        var id = '{$id}';
        new Vue({
            el: '#app',
            data: {
                id: id,
                //分类列表
                cateList: [],
                //运费模板
                tempList: [],
                upload: {
                    videoIng: false
                },
                formData: {
                    moq: 0,
                    cate_id: [],
                    temp_id: 0,
                    commission: 0,
                    store_name: '',
                    keyword: '',
                    unit_name: '',
                    postage: '',
                    store_info: '',
                    image: '',
                    video_link: '',
                    slider_image: [],
                    price: '',
                    spec_type: 1,
                    attr: {
                        pic: '',
                        price: 0,
                        cost: 0,
                        ot_price: 0,
                        stock: 0,
                        bar_code: '',
                        weight: 0,
                        volume: 0,
                        brokerage: 0,
                        brokerage_two: 0,
                        discount: 0,
                        discount_gt: 0,
                        discount_lt: 0,
                        advance_sale: 0,
                        advance_time: 0,
                        advance_day: '',
                        moq: 0,
                        suk: '',
                        sort:0
                    },
                    attrs: [],
                    description: '',
                    // ficti: 0,
                    give_integral: 0,
                    sort: 0,
                    is_show: 1,
                    is_hot: 0,
                    is_benefit: 0,
                    is_best: 0,
                    is_new: 0,
                    view_stock: 0,
                    view_sale_num: 0,
                    is_good: 0,
                    is_sub: 0,
                    items: [
                        // {
                        //     value: '',
                        //     detailValue:'',
                        //     attrHidden:false,
                        //     detail:[]
                        // }
                    ],
                    activity: ['秒杀', '砍价', '拼团'],
                },
                videoLink: '',
                //批量添加属性
                batchAttr: {
                    pic: '',
                    price: 0,
                    cost: 0,
                    ot_price: 0,
                    stock: 0,
                    bar_code: '',
                    weight: 0,
                    volume: 0,
                    discount: 0,
                    discount_gt: 0,
                    discount_lt: 0,
                    moq: 0,
                    suk: '',
                    sort:0
                },
                //多属性header头
                formHeader: [],
                formHeader1: [],
                // 规格数据
                formDynamic: {
                    attrsName: '',
                    attrsVal: ''
                },
                brokerage: {
                    brokerage: '',
                    brokerage_two: '',
                },
                activity: {
                    '秒杀': '#1E9FFF',
                    '砍价': '#189688',
                    '拼团': '#FEB900'
                },
                attr: [], //临时属性
                newRule: false, //是否添加新规则
                radioRule: ['is_sub', 'is_show', 'is_hot', 'is_benefit', 'is_new', 'is_good', 'view_stock', 'view_sale_num', 'is_best', 'spec_type'], //radio 当选规则
                rule: { //多图选择规则
                    slider_image: {
                        maxLength: 15
                    }
                },
                ruleList: [],
                ruleIndex: -1,
                progress: 0,
                um: null, //编译器实例化
                form: null, //layui.form
                layTabId: 1,
                ruleBool: id ? true : false,
            },
            watch: {
                'formData.is_sub': function(n) {
                    if (n == 1) {
                        this.formHeader.push({
                            title: '一级返佣(元)'
                        });
                        //                    this.formHeader.push({title:'二级级返佣(元)'});
                    } else {
                        this.formHeader.pop();
                        this.formHeader.pop();
                    }
                },
                'formData.spec_type': function(n) {
                    if (n) {
                        this.render();
                    }
                },
                // 'formData.image':function (n) {
                //     if(!this.batchAttr.pic){
                //         this.batchAttr.pic = n;
                //     }
                //     if(!this.formData.attr.pic){
                //         this.formData.attr.pic = n;
                //     }
                // }
            },
            methods: {
                addParam() {
                    let _tempAttr = {...this.batchAttr};
                    if (_tempAttr.suk == '') {
                        return this.showMsg('请输入属性名称')
                    }
                    if (!this.legal_int(_tempAttr.discount)) {
                        return this.showMsg('优惠量输入有误!');
                    }
                    if (!this.legal_int(_tempAttr.discount_gt, 2)) {
                        return this.showMsg('大于等于优惠量的售价输入有误!');
                    }
                    if (!this.legal_int(_tempAttr.discount_lt, 2)) {
                        return this.showMsg('小于优惠量的售价输入有误!');
                    }
                    if (!this.legal_int(_tempAttr.moq)) {
                        return this.showMsg('起订量输入有误!');
                    }
                    if (!this.legal_int(_tempAttr.stock)) {
                        return this.showMsg('库存输入有误!');
                    }
                    if (!this.legal_int(_tempAttr.sort)) {
                        return this.showMsg('排序输入有误!');
                    }
                    this.formData.attrs.push(_tempAttr);
                    this.$forceUpdate();
                },
                back: function() {
                    var that = this;
                    layui.use(['element'], function() {
                        layui.element.tabChange('docTabBrief', that.layTabId == 1 ? 1 : parseInt(that.layTabId) - 1);
                    });
                },
                next: function() {
                    var that = this;
                    layui.use(['element'], function() {
                        layui.element.tabChange('docTabBrief', that.layTabId == 3 ? 3 : parseInt(that.layTabId) + 1);
                    });
                },
                legal_int: function(value, type = 1) {
                    if (type == 1) {
                        var reg = /^(0|[1-9][0-9]*|-[1-9][0-9]*)$/
                    } else {
                        var reg = /^(([0-9]|([1-9][0-9]{0,9}))((\.[0-9]{1,2})?))$/
                    }
                    if (!reg.test(value)) {
                        return false;
                    }
                    return true;
                },
                goBack: function() {
                    location.href = this.U({
                        c: 'store.StoreProduct',
                        a: 'index'
                    });
                },
                U: function(opt) {
                    var m = opt.m || 'admin',
                        c = opt.c || window.controlle || '',
                        a = opt.a || 'index',
                        q = opt.q || '',
                        p = opt.p || {};
                    var params = Object.keys(p).map(function(key) {
                        return key + '/' + p[key];
                    }).join('/');
                    var gets = Object.keys(q).map(function(key) {
                        return key + '=' + q[key];
                    }).join('&');

                    return '/' + m + '/' + c + '/' + a + (params == '' ? '' : '/' + params) + (gets == '' ? '' : '?' + gets);
                },
                /**
                 * 提示
                 * */
                showMsg: function(msg, success) {
                    $('#submit').removeAttr('disabled').text('提交');
                    layui.use(['layer'], function() {
                        layui.layer.msg(msg, success);
                    });
                },
                addBrokerage: function() {
                    if (this.brokerage.brokerage >= 0 && this.brokerage.brokerage_two >= 0) {
                        var that = this;
                        this.$set(this.formData, 'attrs', this.formData.attrs.map(function(item) {
                            item.brokerage = that.brokerage.brokerage;
                            item.brokerage_two = that.brokerage.brokerage_two;
                            return item;
                        }));
                    } else {
                        return this.showMsg('请填写返佣金额在进行批量添加');
                    }
                },
                batchClear: function() {
                    this.$set(this, 'batchAttr', {
                        pic: '',
                        price: 0,
                        cost: 0,
                        ot_price: 0,
                        stock: 0,
                        bar_code: '',
                        weight: 0,
                        volume: 0,
                    });
                },
                /**
                 * 批量添加
                 * */
                batchAdd: function() {
                    var that = this;
                    this.$set(this.formData, 'attrs', this.formData.attrs.map(function(item) {
                        if (that.batchAttr.pic) {
                            item.pic = that.batchAttr.pic;
                        }
                        if (that.batchAttr.suk != '') {
                            item.suk = that.batchAttr.suk;
                        }
                        if (that.batchAttr.price > 0) {
                            item.price = that.batchAttr.price;
                        }
                        if (that.batchAttr.cost > 0) {
                            item.cost = that.batchAttr.cost;
                        }
                        if (that.batchAttr.ot_price > 0) {
                            item.ot_price = that.batchAttr.ot_price;
                        }
                        if (that.batchAttr.stock > 0) {
                            item.stock = that.batchAttr.stock;
                        }
                        if (that.batchAttr.bar_code != '') {
                            item.bar_code = that.batchAttr.bar_code;
                        }
                        if (that.batchAttr.weight > 0) {
                            item.weight = that.batchAttr.weight;
                        }
                        if (that.batchAttr.volume > 0) {
                            item.volume = that.batchAttr.volume;
                        }
                        if (that.batchAttr.discount > 0) {
                            item.discount = that.batchAttr.discount;
                        }
                        if (that.batchAttr.discount_gt > 0) {
                            item.discount_gt = that.batchAttr.discount_gt;
                        }
                        if (that.batchAttr.discount_lt > 0) {
                            item.discount_lt = that.batchAttr.discount_lt;
                        }
                        if (that.batchAttr.moq > 0) {
                            item.moq = that.batchAttr.moq;
                        }
                        if (that.batchAttr.sort > 0) {
                            item.sort = that.batchAttr.sort;
                        }
                        return item;
                    }));

                },
                /**
                 * 获取商品信息
                 * */
                getProductInfo: function() {
                    var that = this;
                    that.requestGet(that.U({
                        c: "store.StoreProduct",
                        a: 'get_product_info',
                        q: {
                            id: that.id
                        }
                    })).then(function(res) {
                        that.$set(that, 'cateList', res.data.cateList);
                        that.$set(that, 'tempList', res.data.tempList);
                        var productInfo = res.data.productInfo || {};

                        if (productInfo.id && that.id) {
                            that.$set(that, 'formData', productInfo);
                            // that.generate();
                        }


                        that.getRuleList();
                        that.init();
                    }).catch(function(res) {
                        that.showMsg(res.msg);
                    })
                },
                /**
                 * 给某个属性添加属性值
                 * @param item
                 * */
                addDetail: function(item) {
                    if (!item.detailValue) return false;
                    if (item.detail.find(function(val) {
                            if (item.detailValue == val) {
                                return true;
                            }
                        })) {
                        return this.showMsg('添加的属性值重复');
                    }
                    item.detail.push(item.detailValue);
                    item.detailValue = '';
                },
                /**
                 * 删除某个属性值
                 * @param item 父级循环集合
                 * @param inx 子集index
                 * */
                deleteValue: function(item, inx) {
                    if (item.detail.length > 1) {
                        item.detail.splice(inx, 1);
                    } else {
                        return this.showMsg('请设置至少一个属性');
                    }
                },
                /**
                 * 删除某条属性
                 * @param index
                 * */
                deleteItem: function(index) {
                    this.formData.items.splice(index, 1);
                },
                /**
                 * 删除某条属性
                 * @param index
                 * */
                deleteAttrs: function(index) {
                    var that = this;
                    var suk = that.formData.attrs[index]['suk'];
                    if (that.id > 0) {
                        that.requestGet(that.U({
                            c: "store.StoreProduct",
                            a: 'check_activity',
                            q: {
                                id: that.id,
                                suk
                            }
                        })).then(function(res) {
                            if (res.code == 200) {
                                that.formData.attrs.splice(index, 1);
                                that.$forceUpdate();
                            }
                        }).catch(function(res) {
                            if (res.code == 200) {
                                if (that.formData.attrs.length > 1) {
                                    that.formData.attrs.splice(index, 1);
                                    that.$forceUpdate();
                                } else {
                                    return that.showMsg('请设置至少一个规则');
                                }
                            }else{
                                return that.showMsg(res.msg);
                            }
                        })
                    } else {

                        if (that.formData.attrs.length > 1) {
                            that.formData.attrs.splice(index, 1);
                            this.$forceUpdate();
                        } else {
                            return that.showMsg('请设置至少一个规则');
                        }
                    }
                },
                /**
                 * 创建属性
                 * */
                createAttrName: function() {
                    if (this.formDynamic.attrsName && this.formDynamic.attrsVal) {
                        if (this.formData.items.find(function(val) {
                                if (val.value == this.formDynamic.attrsName) {
                                    return true;
                                }
                            }.bind(this))) {
                            return this.showMsg('添加的属性重复');
                        }
                        this.formData.items.push({
                            value: this.formDynamic.attrsName,
                            detailValue: '',
                            attrHidden: false,
                            detail: [this.formDynamic.attrsVal]
                        });
                        this.formDynamic.attrsName = '';
                        this.formDynamic.attrsVal = '';
                        this.newRule = false;
                    } else {
                        return this.showMsg('请添加完整的规格!');
                    }
                },
                /**
                 * 删除图片
                 * */
                deleteImage: function(key, index) {
                    var that = this;
                    if (index != undefined) {
                        that.formData[key].splice(index, 1);
                        that.$set(that.formData, key, that.formData[key]);
                    } else {
                        that.$set(that.formData, key, '');
                    }
                },
                createFrame: function(title, src, opt) {
                    opt === undefined && (opt = {});
                    var h = 0;
                    if (window.innerHeight < 800 && window.innerHeight >= 700) {
                        h = window.innerHeight - 50;
                    } else if (window.innerHeight < 900 && window.innerHeight >= 800) {
                        h = window.innerHeight - 100;
                    } else if (window.innerHeight < 1000 && window.innerHeight >= 900) {
                        h = window.innerHeight - 150;
                    } else if (window.innerHeight >= 1000) {
                        h = window.innerHeight - 200;
                    } else {
                        h = window.innerHeight;
                    }
                    var area = [(opt.w || window.innerWidth / 2) + 'px', (!opt.h || opt.h > h ? h : opt.h) + 'px'];
                    layui.use('layer', function() {
                        return layer.open({
                            type: 2,
                            title: title,
                            area: area,
                            fixed: false, //不固定
                            maxmin: true,
                            moveOut: false, //true  可以拖出窗外  false 只能在窗内拖
                            anim: 5, //出场动画 isOutAnim bool 关闭动画
                            offset: 'auto', //['100px','100px'],//'auto',//初始位置  ['100px','100px'] t[ 上 左]
                            shade: 0, //遮罩
                            resize: true, //是否允许拉伸
                            content: src, //内容
                            move: '.layui-layer-title'
                        });
                    });
                },
                changeIMG: function(name, value) {
                    if (this.getRule(name).maxLength !== undefined) {
                        var that = this;
                        value.map(function(v) {
                            that.formData[name].push(v);
                        });
                        this.$set(this.formData, name, this.formData[name]);
                    } else {
                        if (name == 'batchAttr.pic') {
                            this.batchAttr.pic = value;
                        } else {
                            if (name.indexOf('.') !== -1) {
                                var key = name.split('.');
                                if (key.length == 2) {
                                    this.formData[key[0]][key[1]] = value;
                                } else if (key.length == 3) {
                                    this.formData[key[0]][key[1]][key[2]] = value;
                                } else if (key.length == 4) {
                                    this.$set(this.formData[key[0]][key[1]][key[2]], key[3], value)
                                }
                            } else {
                                this.formData[name] = value;
                            }
                        }
                    }
                },
                getRule: function(name) {
                    return this.rule[name] || {};
                },
                uploadImage: function(name) {
                    return this.createFrame('选择图片', this.U({
                        c: "widget.images",
                        a: 'index',
                        p: {
                            fodder: name
                        }
                    }), {
                        h: 545,
                        w: 900
                    });
                },
                uploadVideo: function() {
                    if (this.videoLink) {
                        this.formData.video_link = this.videoLink;
                    } else {
                        $(this.$refs.filElem).click();
                    }
                },
                get_cate_id: function(tree) {
                    let temp = [];
                    let fn = function(tree) {
                        $.each(tree, function(i, v) {
                            if (v.children) {
                                fn(v.children)
                            } else {
                                temp.push(v.id);
                            }
                        });
                    }
                    fn(tree);
                    return temp;
                },
                delVideo: function() {
                    var that = this;
                    that.$set(that.formData, 'video_link', '');
                },
                insertEditor: function(list) {
                    this.um.execCommand('insertimage', list);
                },
                insertEditorVideo: function(src) {
                    this.um.setContent('<div><video style="width: 99%" src="' + src + '" class="video-ue" controls="controls" width="100"><source src="' + src + '"></source></video></div><br>', true);
                },
                getContent: function() {
                    return this.um.getContent();
                },
                /**
                 * 监听radio字段
                 */
                eeventRadio: function() {
                    var that = this;
                    that.radioRule.map(function(val) {
                        that.form.on('radio(' + val + ')', function(res) {
                            that.formData[val] = res.value;
                        });
                    })
                },
                init: function() {
                    var that = this;
                    window.UMEDITOR_CONFIG.toolbar = [
                        // 加入一个 test
                        'source | undo redo | bold italic underline strikethrough | superscript subscript | forecolor backcolor | removeformat |',
                        'insertorderedlist insertunorderedlist | selectall cleardoc paragraph | fontfamily fontsize',
                        '| justifyleft justifycenter justifyright justifyjustify |',
                        'link unlink | emotion selectimgs video  | map',
                        '| horizontal print preview fullscreen', 'drafts', 'formula'
                    ];
                    UM.registerUI('selectimgs', function(name) {
                        var me = this;
                        var $btn = $.eduibutton({
                            icon: 'image',
                            click: function() {
                                that.createFrame('选择图片', "{:Url('widget.images/index',['fodder'=>'editor'])}");
                            },
                            title: '选择图片'
                        });

                        this.addListener('selectionchange', function() {
                            //切换为不可编辑时，把自己变灰
                            var state = this.queryCommandState(name);
                            $btn.edui().disabled(state == -1).active(state == 1)
                        });
                        return $btn;

                    });
                    UM.registerUI('video', function(name) {
                        var me = this;
                        var $btn = $.eduibutton({
                            icon: 'video',
                            click: function() {
                                that.createFrame('选择视频', "{:Url('widget.video/index',['fodder'=>'video'])}");
                            },
                            title: '选择视频'
                        });

                        this.addListener('selectionchange', function() {
                            //切换为不可编辑时，把自己变灰
                            var state = this.queryCommandState(name);
                            $btn.edui().disabled(state == -1).active(state == 1)
                        });
                        return $btn;

                    });
                    //实例化编辑器
                    this.um = UM.getEditor('myEditor', {
                        initialFrameWidth: '99%',
                        initialFrameHeight: 400
                    });
                    this.um.setContent(that.formData.description);
                    that.$nextTick(function() {
                        layui.use(['form', 'element', 'tree'], function() {
                            that.form = layui.form;
                            that.tree = layui.tree;
                            that.form.render();
                            that.tree.render({
                                elem: '#tree',
                                data: that.cateList,
                                showCheckbox: true //是否显示复选框
                                    ,
                                id: 'demoId1',
                                isopen: false //加载完毕后的展开状态，默认值：true
                            });
                            that.form.on('select(temp_id)', function(data) {
                                that.$set(that.formData, 'temp_id', data.value);
                            });
                            that.form.on('select(rule_index)', function(data) {
                                that.ruleIndex = data.value;
                            });
                            layui.element.on('tab(docTabBrief)', function() {
                                that.layTabId = this.getAttribute('lay-id');
                            });
                            that.eeventRadio();
                        });

                    })
                },
                requestPost: function(url, data) {
                    return new Promise(function(resolve, reject) {
                        axios.post(url, data).then(function(res) {
                            if (res.status == 200 && res.data.code == 200) {
                                resolve(res.data)
                            } else {
                                reject(res.data);
                            }
                        }).catch(function(err) {
                            reject({
                                msg: err
                            })
                        });
                    })
                },
                requestGet: function(url) {
                    return new Promise(function(resolve, reject) {
                        axios.get(url).then(function(res) {
                            if (res.status == 200 && res.data.code == 200) {
                                resolve(res.data)
                            } else {
                                reject(res.data);
                            }
                        }).catch(function(err) {
                            reject({
                                msg: err
                            })
                        });
                    })
                },
                generates: function() {
                    var that = this;
                    that.generate(1);
                },
                generate: function(type = 0) {
                    var that = this;
                    this.requestPost(that.U({
                        c: "store.StoreProduct",
                        a: 'is_format_attr',
                        p: {
                            id: that.id,
                            type: type
                        }
                    }), {
                        attrs: this.formData.items
                    }).then(function(res) {
                        that.$set(that.formData, 'attrs', res.data.value);
                        that.$set(that, 'formHeader', res.data.header);
                        if (that.id && that.formData.is_sub == 1 && that.formData.spec_type == 1) {
                            that.formHeader.push({
                                title: '一级返佣(元)'
                            });
                            //                        that.formHeader.push({title:'二级级返佣(元)'});
                        }
                    }).catch(function(res) {
                        return that.showMsg(res.msg);
                    });
                },
                handleSubmit: function() {
                    var that = this;
                    var select = that.tree.getChecked('demoId1');
                    if (select.length >= 0) {
                        cate_id = that.get_cate_id(select);
                    } else {
                        return that.showMsg('请选择商品分类');
                    }
                    that.formData.cate_id = cate_id;

                    if (!that.price < 0) {
                        return that.showMsg('展示价格填写有误');
                    }

                    // if (!that.formData.temp_id) {
                    //     return that.showMsg('请选择运费模板');
                    // }
                    if (!that.formData.store_name) {
                        return that.showMsg('请填写商品名称');
                    }
                    if (!that.formData.image) {
                        return that.showMsg('请填选择商品图');
                    }
                    if (!that.formData.slider_image.length) {
                        return that.showMsg('请填选择商品轮播图');
                    }
                    if (!that.formData.attrs.length) {
                        return that.showMsg('请添加多规格属性');
                    }
                    for (var index in that.formData.attrs) {
                        var arr = that.formData.attrs[index];
                        if (arr.suk == '') {
                            return that.showMsg('请填第' + (parseInt(index) + 1) + '条的属性');
                        }
                        if (!that.legal_int(arr.moq)) {
                            return that.showMsg('请填第' + (parseInt(index) + 1) + '条的起订量');
                        }
                        if (!that.legal_int(arr.stock)) {
                            return that.showMsg('请填写多规格属性第' + (parseInt(index) + 1) + '条的库存');
                        }
                        if (!that.legal_int(arr.discount)) {
                            return that.showMsg('第' + (parseInt(index) + 1) + '条的优惠量有误');
                        }
                        if (!that.legal_int(arr.discount_gt, 2)) {
                            return that.showMsg('第' + (parseInt(index) + 1) + '条的大于优惠量售价有误');
                        }
                        if (!that.legal_int(arr.discount_lt, 2)) {
                            return that.showMsg('第' + (parseInt(index) + 1) + '条的小于优惠量售价有误');
                        }
                    }

                    that.formData.description = that.getContent();
                    $('#submit').attr('disabled', 'disabled').text('保存中...');
                    that.requestPost(that.U({
                        c: 'store.StoreProduct',
                        a: 'save',
                        p: {
                            id: that.id
                        }
                    }), that.formData).then(function(res) {
                        that.confirm();
                    }).catch(function(res) {
                        that.showMsg(res.msg);
                    });
                },
                confirm: function() {
                    var that = this;
                    layui.use(['layer'], function() {
                        var layer = layui.layer;
                        layer.confirm(that.id ? '修改成功是否返回产品列表' : '添加成功是否返回产品列表', {
                            btn: ['返回列表', that.id ? '继续修改' : '继续添加'] //按钮
                        }, function() {
                            location.href = that.U({
                                c: 'store.StoreProduct',
                                a: 'index'
                            });
                        }, function() {
                            location.reload();
                        });
                    });
                },
                render: function() {
                    this.$nextTick(function() {
                        layui.use(['form'], function() {
                            layui.form.render('select');
                        });
                    })
                },
                // 移动
                handleDragStart(e, item) {
                    this.dragging = item;
                },
                handleDragEnd(e, item) {
                    this.dragging = null
                },
                handleDragOver(e) {
                    e.dataTransfer.dropEffect = 'move'
                },
                handleDragEnter(e, item) {
                    e.dataTransfer.effectAllowed = 'move'
                    if (item === this.dragging) {
                        return
                    }
                    var newItems = [...this.formData.activity];
                    var src = newItems.indexOf(this.dragging);
                    var dst = newItems.indexOf(item);
                    newItems.splice(dst, 0, ...newItems.splice(src, 1))
                    this.formData.activity = newItems;
                },
                getRuleList: function(type) {
                    var that = this;
                    that.requestGet(that.U({
                        c: 'store.StoreProduct',
                        a: 'get_rule'
                    })).then(function(res) {
                        that.$set(that, 'ruleList', res.data);
                        if (type !== undefined) {
                            that.render();
                        }
                    });
                },
                addRule: function() {
                    return this.createFrame('添加商品规则', this.U({
                        c: 'store.StoreProductRule',
                        a: 'create'
                    }));
                },
                allRule: function() {
                    if (this.ruleIndex != -1) {
                        var rule = this.ruleList[this.ruleIndex];
                        if (rule) {
                            this.ruleBool = true;
                            var rule_value = rule.rule_value.map(function(item) {
                                return item;
                            });
                            this.$set(this.formData, 'items', rule_value);
                            this.$set(this.formData, 'attrs', []);
                            this.$set(this, 'formHeader', []);
                            return true;
                        }
                    }
                    this.showMsg('选择的属性无效');
                }
            },
            mounted: function() {
                var that = this;
                axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
                that.getProductInfo();
                window.$vm = that;
                window.changeIMG = that.changeIMG;
                window.insertEditor = that.insertEditor;
                window.insertEditorVideo = that.insertEditorVideo;
                window.successFun = function() {
                    that.getRuleList(1);
                }
                $(that.$refs.filElem).change(function() {
                    var inputFile = this.files[0];
                    that.requestPost(that.U({
                        c: "widget.video",
                        a: 'get_signature'
                    })).then(function(res) {
                        AdminUpload.upload(res.data.uploadType, {
                            token: res.data.uploadToken || '',
                            file: inputFile,
                            accessKeyId: res.data.accessKey || '',
                            accessKeySecret: res.data.secretKey || '',
                            bucketName: res.data.storageName || '',
                            region: res.data.storageRegion || '',
                            domain: res.data.domain || '',
                            uploadIng: function(progress) {
                                that.upload.videoIng = true;
                                that.progress = progress;
                            }
                        }).then(function(res) {
                            //成功
                            that.$set(that.formData, 'video_link', res.url);
                            that.progress = 0;
                            that.upload.videoIng = false;
                            return that.showMsg('上传成功');
                        }).catch(function(err) {
                            //失败
                            console.info(err);
                            return that.showMsg('上传错误请检查您的配置');
                        });
                    }).catch(function(res) {
                        return that.showMsg(res.msg || '获取密钥失败,请检查您的配置');
                    });
                })
            }
        });
    </script>
</body>

</html>
<script>
    import Layout from "../../../../../public/static/plug/iview/dist/iview";
    export default {
        components: {
            Layout
        }
    }
</script>