{extend name="public/container"}
{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <div class="layui-col-md12">

        </div>
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">资金监控日志</div>
                <div class="layui-card-body">
                    <table class="layui-hide" id="userList" lay-filter="userList"></table>

                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
<script>

    layList.tableList('userList',"{:Url('tablelist')}",function () {
        return [
            {field: 'name', title: '表名称'},
            {field: 'comment', title: '备注' },
            {field: 'engine', title: '类型'},
            {field: 'data_length', title: '大小'},
            {field: 'update_time', title: '更新时间'},
            {field: 'collation', title: '字段数'},
        ];
    });

</script>
{/block}
