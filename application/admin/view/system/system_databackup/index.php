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
                    <div class="layui-btn-group conrelTable">
                        <button class="layui-btn layui-btn-sm layui-btn-normal" type="button" data-type="set_grant"><i class="fa fa-check-circle-o"></i>备份</button>
                        <button class="layui-btn layui-btn-sm layui-btn-normal" type="button" data-type="optimize"><i class="fa fa-check-circle-o"></i>优化表</button>
                        <button class="layui-btn layui-btn-sm layui-btn-normal" type="button" data-type="repair"><i class="fa fa-check-circle-o"></i>修复表</button>
                        <button class="layui-btn layui-btn-sm layui-btn-normal" type="button" data-type="refresh"><i class="layui-icon layui-icon-refresh" ></i>刷新</button>
                    </div>
                    <table class="layui-hide" id="userList" lay-filter="userList"></table>
                    <script type="text/html" id="barDemo">
                        <button type="button" class="layui-btn layui-btn-xs" lay-event="see"><i class="layui-icon layui-icon-edit"></i>详情</button>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
<script>
    //加载table
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
    //监听并执行 uid 的排序
    layList.tool(function (event,data) {
        var layEvent = event;
        switch (layEvent){
            case 'see':
                $eb.createModalFrame('详情',layList.Url({a:'edit',p:{uid:data.name}}));
                break;
        }
    });
    var action={
        optimize:function () {
            var tables=layList.getCheckData().getIds('name');
            if(ids.length){
                layList.basePost(layList.Url({a:'optimize',p:{is_echo:1,status:0}}),{tables:tables},function (res) {
                    layList.msg(res.msg);
                    layList.reload();
                });
            }else{
                layList.msg('请选择要封禁的会员');
            }
        },
        repair:function () {
            var ids=layList.getCheckData().getIds('name');
            if(ids.length){
                layList.basePost(layList.Url({a:'repair',p:{is_echo:1,status:0}}),{tables:tables},function (res) {
                    layList.msg(res.msg);
                    layList.reload();
                });
            }else{
                layList.msg('请选择要封禁的会员');
            }
        },

    };
    $('.conrelTable').find('button').each(function () {
        var type=$(this).data('type');
        $(this).on('click',function () {
            action[type] && action[type]();
        })
    })

</script>
{/block}
