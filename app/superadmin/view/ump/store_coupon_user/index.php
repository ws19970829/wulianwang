{extend name="public/container"}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">
                <div class="row">
                    <div class="m-b m-l">

                        <form action="" class="form-inline">

<!--                            <select name="is_fail" aria-controls="editable" class="form-control input-sm">-->
<!--                                <option value="">是否有效</option>-->
<!--                                <option value="1" {eq name="where.is_fail" value="1"}selected="selected"{/eq}>是</option>-->
<!--                                <option value="0" {eq name="where.is_fail" value="0"}selected="selected"{/eq}>否</option>-->
<!--                            </select>-->
                            <select name="status" aria-controls="editable" class="form-control input-sm">
                                <option value="">状态</option>
                                <option value="1" {eq name="where.status" value="1"}selected="selected"{/eq}>已使用</option>
                                <option value="0" {eq name="where.status" value="0"}selected="selected"{/eq}>未使用</option>
                                <!-- <option value="2" {eq name="where.status" value="2"}selected="selected"{/eq}>已过期</option> -->
                            </select>
                            <div class="input-group">
                                <input type="text" name="nickname" value="{$where.account}" placeholder="请输入发放人账户" class="input-sm form-control"> <span class="input-group-btn">
                            </div>
                            <div class="input-group">
                                <input type="text" name="coupon_title" value="{$where.coupon_title}" placeholder="请输入优惠券名称" class="input-sm form-control"> <span class="input-group-btn">
                                    <button type="submit" class="btn btn-sm btn-primary"> <i class="fa fa-search" ></i>搜索</button> </span>
                            </div>
                        </form>
                    </div>

                </div>
                <div class="table-responsive">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>
                            <th class="text-center">优惠券名称</th>
                            <th class="text-center">发放人</th>
                            <th class="text-center">优惠券面值</th>
                            <th class="text-center">优惠券最低消费</th>
                            <th class="text-center">优惠券开始使用时间</th>
                            <th class="text-center">优惠券结束使用时间</th>
                            <th class="text-center">领取后有效使用时间(天)</th>
                            <th class="text-center">领取状态</th>

                        </tr>
                        </thead>
                        <tbody class="">
                        {volist name="list" id="vo"}

                            <td class="text-center">
                                {$vo.title}
                            </td>
                            <td class="text-center">
                                {$vo.account}
                            </td>
                            <td class="text-center">
                                {$vo.coupon_price}
                            </td>
                            <td class="text-center">
                                {$vo.use_min_price}
                            </td>
                            <td class="text-center">
                                {$vo.start_time}
                            </td>
                            <td class="text-center">
                                {$vo.end_time}
                            </td>
                            <td class="text-center">
                                {$vo.coupon_time}
                            </td>
                            <td class="text-center">
                                {if condition="$vo['is_use'] eq 1"}
                                已使用
                                {else/}
                                未使用
                                {/if}
                            </td>
                        </tr>
                        {/volist}
                        </tbody>
                    </table>
                </div>
                {include file="public/inner_page"}
            </div>
        </div>
    </div>
</div>
{/block}
