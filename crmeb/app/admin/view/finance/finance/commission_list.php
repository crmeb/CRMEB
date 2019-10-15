{extend name="public/container"}
{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12 layui-col-sm12 layui-col-lg12">
            <div class="layui-card">
                <div class="layui-card-header">搜索</div>
                <div class="layui-card-body">
                    <form class="layui-form layui-form-pane" action="">
                        <div class="layui-form-item">
                            <div class="layui-inline">
                                <label class="layui-form-label">昵称/ID</label>
                                <div class="layui-input-block">
                                    <input type="text" name="nickname" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">佣金范围</label>
                                <div class="layui-input-inline" style="width: 100px;">
                                    <input type="text" name="price_min" placeholder="￥" autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid">-</div>
                                <div class="layui-input-inline" style="width: 100px;">
                                    <input type="text" name="price_max" placeholder="￥" autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">佣金排序</label>
                                <div class="layui-input-block">
                                    <select name="order">
                                        <option value="1" selected="">升序</option>
                                        <option value="0">降序</option>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <div class="layui-input-inline">
                                    <button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit="search" lay-filter="search">
                                        <i class="layui-icon layui-icon-search"></i>搜索</button>
                                    <button class="layui-btn layui-btn-primary layui-btn-sm export" type="button" lay-submit="excel" lay-filter="excel">
                                        <i class="fa fa-floppy-o" style="margin-right: 3px;"></i>导出</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="layui-col-md12 layui-col-sm12 layui-col-lg12">
            <div class="layui-card">
                <div class="layui-card-header">佣金记录列表</div>
                <div class="layui-card-body">
                    <table class="layui-hide" id="List" lay-filter="List">
                    </table>
                    <script type="text/html" id="barDemo">
                        <button type="button" class="layui-btn layui-btn-xs" lay-event="see" onclick="$eb.createModalFrame('{{d.nickname}}-详情','{:Url('content_info')}?uid={{d.uid}}')"><i class="layui-icon layui-icon-edit"></i>详情</button>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
<script>
    layList.form.render();
    layList.tableList('List',"{:Url('get_commission_list')}",function () {
        return [
            {field: 'nickname', title: '昵称/姓名',unresize:true,width:"16%",align:'center'},
            {field: 'sum_number', title: '总佣金金额',sort:true,unresize:true,align:'center'},
            {field: 'now_money', title: '账户余额',unresize:true,align:'center'},
            {field: 'brokerage_price', title: '账户佣金',unresize:true,align:'center'},
            {field: 'extract_price', title: '提现到账佣金',unresize:true,align:'center'},
            {fixed: 'right', title: '操作',align:'center',unresize:true,toolbar:'#barDemo',width:"10%"},
        ];
    });
    layList.search('search');
    layList.search('excel',function (where) {
        where.excel = 1;
        location.href=layList.U({a:'get_commission_list',q:where});
    })
</script>
{/block}