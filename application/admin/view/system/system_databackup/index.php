{extend name="public/container"}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>数据库备份记录</h5>
            </div>
            <div class="ibox-content" style="display: block;">
                <div class="table-responsive">
                    <table class="layui-hide" id="fileList" lay-filter="fileList"></table>
                    <script type="text/html" id="fileListtool">
                        <button type="button" class="layui-btn layui-btn-xs" lay-event="import"><i class="layui-icon layui-icon-edit"></i>倒入</button>
                        <button type="button" class="layui-btn layui-btn-xs" lay-event="delFile"><i class="layui-icon layui-icon-edit"></i>删除</button>
                        <button type="button" class="layui-btn layui-btn-xs" lay-event="downloadFile"><i class="layui-icon layui-icon-edit"></i>下载</button>

                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-title">
                <h5>数据库表列表</h5>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <div class="layui-btn-group conrelTable">
                        <button class="layui-btn layui-btn-sm layui-btn-normal" type="button" data-type="backup"><i class="fa fa-check-circle-o"></i>备份</button>
                        <button class="layui-btn layui-btn-sm layui-btn-normal" type="button" data-type="optimize"><i class="fa fa-check-circle-o"></i>优化表</button>
                        <button class="layui-btn layui-btn-sm layui-btn-normal" type="button" data-type="repair"><i class="fa fa-check-circle-o"></i>修复表</button>
                        <button class="layui-btn layui-btn-sm layui-btn-normal" type="button" data-type="refresh"><i class="layui-icon layui-icon-refresh" ></i>刷新</button>
                    </div>
                    <table class="layui-hide" id="tableList" lay-filter="tableList"></table>
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
    layui.use('table', function(){
        var table = layui.table;
        //
        table.render({
            elem: '#fileList'
            ,url:"{:Url('fileList')}"
            ,cols: [[
                {field: 'backtime', title: '备份名称', sort: true},
                {field: 'part', title: '备注'},
                {field: 'size', title: '大小'},
                {field: 'compress', title: '类型'},
                {field: 'time', title: '时间'},
                {fixed: 'right', title: '操作', width: '20%', align: 'center', toolbar: '#fileListtool'}
            ]]
            ,page: false
        });
        //监听工具条
        table.on('tool(fileList)', function(obj){
            var data = obj.data;
            if(obj.event === 'import'){
                layer.msg('ID：'+ data.id + ' 的查看操作');
            } else if(obj.event === 'delFile'){
                layer.confirm('真的删除行么', function(index){
                    obj.del();
                    layer.close(index);
                });
            } else if(obj.event === 'downloadFile'){
                layer.alert('编辑行：<br>'+ JSON.stringify(data))
            }
        });

    });
/**
    //加载sql备份列表
    var filelist = layList.tableList('fileList',"{:Url('fileList')}",function () {
        return [
            {field: 'backtime', title: '备份名称'},
            {field: 'part', title: '备注' },
            {field: 'size', title: '大小'},
            {field: 'compress', title: '类型'},
            {field: 'time', title: '时间'},
            {fixed: 'right', title: '操作', width: '20%', align: 'center', toolbar: '#fileListtool'}
        ];
    },5);
    //监听并执行备份列表操作
    layList.tool(function (event,data) {
        var layEvent = event;
        switch (layEvent){
            case 'import':
                $eb.createModalFrame('详情',layList.Url({a:'edit',p:{time:data.time}}));
                break;
            case 'delFile':
                console.log(data);
                layList.basePost(layList.Url({a:'delFile'}),{feilname:data.time},function (res) {
                    layList.msg(res.msg);
//                    layList.reload();
                });
                break;
            case 'downloadFile':
                $eb.createModalFrame('详情',layList.Url({a:'edit',p:{feilname:data.name}}));
                break;
        }
    },null,'fileList');
    //加载table
    layList.tableList('tableList',"{:Url('tablelist')}",function () {
        return [
            {type:'checkbox'},
            {field: 'name', title: '表名称'},
            {field: 'comment', title: '备注' },
            {field: 'engine', title: '类型'},
            {field: 'data_length', title: '大小'},
            {field: 'update_time', title: '更新时间'},
            {field: 'rows', title: '行数'}
//            {fixed: 'right', title: '操作', width: '10%', align: 'center', toolbar: '#barDemo'}
        ];
    },100);



//    layList.reload();
//    //监听并执行操作
//    layList.tool(function (event,data) {
//        var layEvent = event;
//        switch (layEvent){
//            case 'see':
//                $eb.createModalFrame('详情',layList.Url({a:'edit',p:{tablename:data.name}}));
//                break;
//        }
//    });
    //批量操作
    var action={
        optimize:function () {
            var tables=layList.getCheckData().getIds('name');
            if(tables.length){
                layList.basePost(layList.Url({a:'optimize'}),{tables:tables},function (res) {
                    layList.msg(res.msg);
                    filelist.layList.reload();
                });
            }else{
                layList.msg('请选择表');
            }
        },
        repair:function () {
            var tables=layList.getCheckData().getIds('name');
            if(tables.length){
                layList.basePost(layList.Url({a:'repair'}),{tables:tables},function (res) {
                    layList.msg(res.msg);
//                    layList.reload();
                });
            }else{
                layList.msg('请选择表');
            }
        },
        backup:function () {
            var tables=layList.getCheckData().getIds('name');
            if(tables.length){
                layList.basePost(layList.Url({a:'backup'}),{tables:tables},function (res) {
                    layList.msg(res.msg);
//                    layList.reload();
                });
            }else{
                layList.msg('请选择表');
            }
        },

    };
    $('.conrelTable').find('button').each(function () {
        var type=$(this).data('type');
        $(this).on('click',function () {
            action[type] && action[type]();
        })
    })
**/
</script>
{/block}
