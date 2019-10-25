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
                            <th class="text-center">金额</th>
                            <th class="text-center">收入/支出</th>
                            <th class="text-center">记录</th>
                            <th class="text-center">时间</th>
                        </tr>
                        </thead>
                        <tbody class="">
                        {volist name="list" id="vo"}
                        <tr>
                            <td class="text-center">
                                {$vo.number}
                            </td>
                            <td class="text-center">
                                {if condition="$vo['pm']"}
                                  收入
                                {else/}
                                  支出
                                {/if}
                            </td>
                            <td class="text-center">
                                {$vo.mark}
                            </td>
                            <td class="text-center">
                                {$vo.add_time}
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
