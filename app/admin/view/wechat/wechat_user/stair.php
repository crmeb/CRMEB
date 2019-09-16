{extend name="public/container"}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">
                <div class="table-responsive">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>
                            <th class="text-center">用户头像</th>
                            <th class="text-center">用户名称</th>
                            <th class="text-center">绑定时间</th>
                            <th class="text-center">订单个数</th>
                            <th class="text-center">获得佣金</th>
                        </tr>
                        </thead>
                        <tbody class="">
                        {volist name="list" id="vo"}
                            <tr>
                                <td class="text-center">
                                    <img src="{$vo.avatar}" alt="{$vo.nickname}" title="{$vo.nickname}" style="width:50px;height: 50px;cursor: pointer;" class="head_image" data-image="{$vo.avatar}">
                                </td>
                                <td class="text-center">
                                    {$vo.nickname}
                                </td>
                                <td class="text-center">
                                    {$vo.add_time|date="Y-m-d H:i:s"}
                                </td>
                                <td class="text-center">
                                    {$vo.orderCount}
                                </td>
                                <td class="text-center">
                                    {$vo.now_money}
                                </td>
                            </tr>
                        {/volist}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
{/block}
