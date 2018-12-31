{extend name="public/container"}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">
                <div class="row">
                    <div class="m-b m-l">
                        <form action="" class="form-inline">
                            <div class="input-group">
                                <input size="26" type="text" name="order_id" value="{$where.order_id}" placeholder="请输入订单编号、编号" class="input-sm form-control"> <span class="input-group-btn">
                                    <button type="submit" class="btn btn-sm btn-primary"> <i class="fa fa-search" ></i>搜索</button> </span>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>
                            <th class="text-center">编号</th>
                            <th class="text-center">姓名</th>
                            <th class="text-center">订单编号</th>
                            <th class="text-center">支付金额</th>
                            <th class="text-center">支付类型</th>
                            <th class="text-center">支付时间</th>
                            <th class="text-center">操作</th>
                        </tr>
                        </thead>
                        <tbody class="">
                        {volist name="list" id="vo"}
                        <tr>
                            <td class="text-center">{$vo.id}</td>
                            <td class="text-center">{$vo.nickname}</td>
                            <td class="text-center">{$vo.order_id}</td>
                            <td class="text-center">
                                {$vo.price}
                                {if condition="$vo['refund_price'] GT 0"}
                                <p>退款金额：{$vo.refund_price}</p>
                                {/if}
                            </td>
                            <td class="text-center">微信支付</td>
                            <td class="text-center">{$vo.pay_time|date='Y-m-d H:i:s',###}</td>
                            <td class="text-center">
                                <button class="btn btn-info btn-xs" type="button"  onclick="$eb.createModalFrame('退款','{:Url('edit',array('id'=>$vo['id']))}',{h:'300',w:'500'})"><i class="fa fa-paste"></i> 退款</button>
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
{block name="script"}
<script>
    $(".open_image").on('click',function (e) {
        var image = $(this).data('image');
        $eb.openImage(image);
    })
</script>
{/block}
