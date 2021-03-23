{extend name="public/container"}
{block name="head_top"}
<script src="{__PLUG_PATH}sweetalert2/sweetalert2.all.min.js"></script>
{/block}
{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15" id="app">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">搜索条件</div>
                <div class="layui-card-body">
                    <form class="layui-form layui-form-pane" action="">
                        <div class="layui-form-item">
                            <div class="layui-inline">
                                <label class="layui-form-label">商家名称</label>
                                <div class="layui-input-block">
                                    <input type="text" name="nickname" class="layui-input">
                                </div>
                            </div>

                            <div class="layui-inline">
                                <div class="layui-input-inline">
                                    <button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit="search"
                                            lay-filter="search">
                                        <i class="layui-icon layui-icon-search"></i>搜索
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">结算列表</div>
                <div class="layui-card-body">

                    <div class="layui-form">
                        <table class="layui-table">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>商家名称</th>
                                <th>收入总额</th>
                                <th>退款总额</th>
                                <th>毛利润</th>
                                <th>当前服务费率</th>
                                <th>应结算总额</th>
                                <th>已结算总额</th>
                                <th>未结算总额</th>
<!--                                <th width="8%">结算方式</th>-->
<!--                                <th width="14%">操作</th>-->
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(item,index) in messageList">
                                <td>{{item.id}}</td>

                                <td>
                                    <div class="layui-col-md9 layui-col-sm9 layui-col-xs9">{{item.real_name}}</div>
                                </td>
                                <td>{{item.income_text}}</td>
                                <td>{{item.refund_price_text}}</td>
                                <td>{{item.maolirun_text}}</td>
                                <td>{{item.business_rate}}</td>
                                <td>{{item.yingjiesuan_text}}</td>
                                <td>{{item.yijiesuan_text}}</td>
                                <td>{{item.wait_settle_money_text}}</td>
<!--                                <td>银联结算</td>-->
<!--                                <td>-->
<!--                                    <button type="button" class="layui-btn layui-btn-normal layui-btn-sm"-->
<!--                                            @click="edit(item,index)">回复-->
<!--                                    </button>-->
<!--                                    <button type="button" class="layui-btn layui-btn-normal layui-btn-sm"-->
<!--                                            @click="delReply(item,index)">删除-->
<!--                                    </button>-->
<!--                                </td>-->
                            </tr>

                            </tbody>
                        </table>
                        <div ref="pages" style="text-align: right;" v-if="count > where.limit"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
{/block}
{block name="script"}
<script type="text/javascript">
    var product_id = <?=$product_id?>;
    require(['vue'], function (Vue) {
        new Vue({
            el: "#app",
            data: {
                where: {
                    page: 1,
                    title: '',
                    is_reply: '',
                    limit: 10,
                    product_name: '',
                    producr_id: product_id,
                    message_page: 1,
                    order_id: '',
                    nickname: '',
                    score_type: '',
                },
                product: {
                    loading: false,
                    loadend: false,
                    loadTitle: '加载更多',
                },
                product_name: '',
                messageList: [],
                message: {
                    loading: false,
                    loadend: false,
                    loadTitle: '加载更多',
                },
                count: 0,
            },
            watch: {
                'where.producr_id': function (n) {
                    this.message.loadend = false;
                    this.where.message_page = 1;
                    this.$set(this, 'messageList', []);
                    this.getMessageList();
                },
                'where.message_page': function (n) {
                    this.message.loadend = false;
                    this.getMessageList(true);
                }
            },
            methods: {
                see: function (nickname, uid) {
                    $eb.createModalFrame(nickname + '-会员详情', layList.Url({c: 'user.user', a: 'see', p: {uid: uid}}));
                },
                Reset: function () {
                    if (!this.where.product_name) return;
                    this.where.page = 1;
                    this.product.loadend = false;
                    this.product_name = '';
                    this.where.product_name = '';
                    this.$set(this, 'productImaesList', []);
                    this.getProductImaesList();
                },
                seachs: function () {
                    this.where.page = 1;
                    this.product.loadend = false;
                    if (!this.where.product_name && !this.product_name) return layList.msg('请输入产品名称再进行查找！');
                    if (this.where.product_name == this.product_name) return;
                    this.product_name = this.where.product_name;
                    this.$set(this, 'productImaesList', []);
                    this.getProductImaesList();
                },
                delReply: function (item, index) {
                    var url = layList.U({a: 'delete', p: {id: item.id}}), that = this;
                    $eb.$swal('delete', function () {
                        $eb.axios.get(url).then(function (res) {
                            if (res.status == 200 && res.data.code == 200) {
                                $eb.$swal('success', res.data.msg);
                                that.messageList.splice(index, 1);
                                that.$set(that, 'messageList', that.messageList);
                            } else
                                return Promise.reject(res.data.msg || '删除失败')
                        }).catch(function (err) {
                            $eb.$swal('error', err);
                        });
                    })
                },
                edit: function (item, index) {
                    var url = layList.U({a: 'set_reply'}), rid = item.id;
                    $eb.$alert('textarea', {
                        'title': '请输入回复内容',
                        'value': item.merchant_reply_content
                    }, function (result) {
                        $eb.axios.post(url, {content: result, id: rid}).then(function (res) {
                            if (res.status == 200 && res.data.code == 200) {
                                item.merchant_reply_time = 1;
                                item.merchant_reply_content = result;
                                $eb.swal(res.data.msg);
                            } else
                                $eb.swal(res.data.msg);
                        });
                    })
                },
                loadList: function () {
                    this.getProductImaesList();
                },
                lockImage: function (href) {
                    return layList.layer.open({
                        type: 1,
                        title: false,
                        closeBtn: 0,
                        shadeClose: true,
                        content: '<img src="' + href + '" style="display: block;width: 100%;" />'
                    });
                },
                getMessageList: function (isFa) {
                    var that = this;
                    if (that.message.loading) return;
                    if (that.message.loadend) return;
                    var index = layList.layer.load(1, {shade: [0.1, '#fff']});
                    layList.baseGet(layList.U({a: 'get_settle_list', q: that.where}), function (res) {
                        var list = res.data.list;
                        var loadend = list.length < that.where.limit;
                        that.message.loading = false;
                        that.message.loadend = loadend;
                        that.count = res.data.count;
                        that.$set(that, 'messageList', list);
                        layList.layer.close(index);
                        isFa || that.initPage();
                    }, function (res) {
                        that.message.loading = false;
                        layList.layer.close(index);
                    });
                },
                slitherMonitor: function () {
                    var clientHeight = document.documentElement.clientHeight;
                    if (this.$refs.producr.offsetHeight >= clientHeight) this.$refs.producr.style.overflowX = 'scroll';
                    else this.$refs.producr.style.overflow = 'hidden';
                },
                initPage: function () {
                    var that = this;
                    that.$nextTick(function () {
                        layList.laypage.render({
                            elem: that.$refs.pages
                            , count: that.count
                            , limit: that.where.limit
                            , layout: ['count', 'prev', 'page', 'next', 'limit', 'refresh', 'skip']
                            , jump: function (obj) {
                                that.where.message_page = obj.curr
                            }
                        });
                    })
                }
            },
            mounted: function () {
                layList.form.render();
                this.getMessageList();
                //查询
                var that = this;
                layList.search('search', function (where) {
                    if ((where.product_name && that.where.product_name == where.product_name) || (where.is_reply && that.where.is_reply == where.is_reply)) return false;
                    that.where.product_name = where.product_name;
                    that.where.is_reply = where.is_reply;
                    that.where.nickname = where.nickname;
                    that.where.message_page = 1;
                    that.where.order_id = where.order_id;
                    that.where.score_type = where.score_type;
                    that.message.loadend = false;
                    that.getMessageList();
                });
            }
        })
    })
</script>
{/block}
