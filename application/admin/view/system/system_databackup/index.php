{extend name="public/container"}
{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <div class="layui-col-md12">

        </div>
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">数据库表列表</div>
                <div class="layui-card-body">
                    <table class="layui-hide" id="userList" lay-filter="userList"></table>
                    <script type="text/html" id="barDemo">
                        <button type="button" class="layui-btn layui-btn-xs" lay-event="edit"><i class="layui-icon layui-icon-edit"></i>编辑</button>
                        <button type="button" class="layui-btn layui-btn-xs" lay-event="see"><i class="layui-icon layui-icon-edit"></i>详情</button>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
<script>

    layList.tableList('userList',"{:Url('tablelist')}",function () {
        return [
            {type:'checkbox'},
            {field: 'name', title: '表名称'},
            {field: 'comment', title: '备注' },
            {field: 'engine', title: '类型'},
            {field: 'data_length', title: '大小'},
            {field: 'update_time', title: '更新时间'},
            {field: 'rows', title: '行数'},
            {fixed: 'right', title: '操作', width: '10%', align: 'center', toolbar: '#barDemo'}
        ];
    },100);
    layList.reload();

</script>
{/block}
