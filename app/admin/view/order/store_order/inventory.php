{extend name="public/container"}
{block name="content"}

<style>
    @media print {

        input,
        .noprint {
            display: none
        }

         .layui-table td{
             padding: 0;
         }

         #oid{
             width: 250px!important;
         }
         .store_name{
            overflow:hidden; 

            text-overflow:ellipsis; 

            white-space:nowrap; 
            max-width: 150px;
         }

        /* .printonly{
		display:block;
		width:50%
	} */


    }

    .layui-elem-field {
        margin: 0;
        text-align: center;
    }

    legend {
        border: none !important;
        margin: 0 !important;
    }

    .layui-elem-field {
        border: none !important;
    }

    /* 横向打印 */
    /* @page {
        size: landscape;
    } */


    /* 纵向打印 */
    @page { size: portrait; }

    h4 {
        font-weight: 700;
        font-size: 12px
    }
   
</style>

<div class="ibox-content order-info">

    <button type="button" class="layui-btn layui-btn-xsc noprint" style="margin-bottom: 5px" onclick=" print();">打印</button>
    <fieldset class="layui-elem-field layui-field-title">
        <legend>{$orderinfo.shop_name}销售清单</legend>
    </fieldset>
    <table class="layui-table">
        <colgroup>
            <col width="150">
            <col width="150">
            <col>
            <col>
        </colgroup>

        <tbody>
            <tr>
                <td colspan="1">
                    <h4>下单日期：</h4>
                </td>
                <td colspan="1" id="aa">{:date('Y-m-d H:i',$orderinfo.add_time)}</td>
                <td colspan="1" id ='oid'>
                    <h4>订单号：</h4>
                </td>
                <td colspan="3">
                    {$orderinfo.order_id}
                </td>
               
            </tr>
            <tr>
                <td>
                    <h4>客户名称</h4>
                </td>
                <td colspan="1">{$orderinfo.real_name}</td>
                <td colspan="2">
                    <h4>联系电话：</h4>
                </td>
                <td colspan="2">{$orderinfo.user_phone}</td>
            </tr>
            <tr>
                <td>
                    <h4>收货地址：</h4>
                </td>
                <td colspan="5">{$orderinfo.user_address}</td>

            </tr>
            <tr>
                <td>
                    <h4>物流信息：</h4>
                </td>
                <td colspan="5">{$orderinfo.express_name}</td>
            </tr>
            <tr>
                <td>
                    <h4>买家留言</h4>
                </td>
                <td colspan="5">{$orderinfo.mark|default='无'}</td>
            </tr>
            <tr>
                <td >
                    <h4>商品全名</h4>
                </td>
                <td style="min-width:300px!important;">
                    <h4>规格</h4>
                </td>
                <td>
                    <h4>单位</h4>
                </td>
                <td>
                    <h4>数量</h4>
                </td>
                <td>
                    <h4>单价</h4>
                </td>
                <td>
                    <h4>金额</h4>
                </td>
            </tr>
            {foreach $goodsinfo as $vo}
                <tr>
                    <td class="store_name">{$vo.cart_info_filter.store_name}</td>
                    <td>{$vo.cart_info_filter.suk}</td>
                    <td>{$vo.cart_info_filter.unit_name}</td>
                    <td>{$vo.cart_info_filter.cart_num} </td>
                    <td>{$vo.cart_info_filter.unit_price}</td>
                    <td>{$vo.cart_info_filter.total}</td>
                </tr>
            {/foreach}
           
            <tr>
                <td >
                    <h4>活动优惠</h4>
                </td>
                <td colspan="2">{$orderinfo.coupon_price}元</td>
                <td >
                    <h4>优惠券优惠</h4>
                </td>
                <td colspan="2">{$orderinfo.coupon_price}元</td>
            </tr>
            <tr>
                <td >
                    <h4>商品合计</h4>
                </td>
                <td colspan="2">{$orderinfo.total_price}元</td>
                <td >
                    <h4>实收金额</h4>
                </td>
                <td colspan="2">{$orderinfo.pay_price}元</td>
            </tr>
            <tr>
                <td>
                    <h4>商家名称：</h4>
                </td>
                <td  colspan="2">{$orderinfo.shop_name}</td>
                <td>
                    <h4>客服电话：</h4>
                </td>
                <td colspan="2">{$sys_phone}</td>
            </tr>
        </tbody>
    </table>


    <script src="{__FRAME_PATH}js/content.min.js?v=1.0.0"></script>
    {/block}
    {block name="script"}

    {/block}