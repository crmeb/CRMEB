{extend name="public/bootstraptable"}
{block name="head"}

{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <table id="table1"></table>
        <table id="table2"></table>
    </div>
</div>
{/block}
{block name="script"}

<script>
    $('#table2').bootstrapTable({
        method: "POST",
        url: "{:Url('billlist')}",
        dataType: "json",
        toolbar: '#toolbar',        //工具按钮用哪个容器
        pagination: true,                   //是否显示分页（*）
        cache: false,
        clickToSelect: true,
        showRefresh: true,                  //是否显示刷新按钮
        showPaginationSwitch: true,       //是否显示选择分页数按钮
        pageNumber: 1,                       //初始化加载第一页，默认第一页
        showColumns:true,
        pageSize: 5, //每页的记录行数（*）
        search: true,
        showToggle:true,//是否显示切换视图（table/card）按钮
        showFullscreen:true,//是否显示全屏按钮
        sortable: true,                     //是否启用排序
        sortOrder: "asc",
        columns: [
            {checkbox : true},
            { field: 'id', title: '编号',sortable:true },
            { field: 'title', title: '标题',sortable:true },
            { field: 'link_id', title: '关联ID',sortable:true },
            { field: 'pm', title: '是否支出',sortable:true },
            { field: 'number', title: '明细数字',sortable:true },
            { field: 'status', title: '状态',sortable:true },
            { field: 'add_time', title: '时间',sortable:true},
            { field: 'balance', title: '余额',sortable:true },
            { field: 'uid', title: '学生编号',sortable:true },
            { field: 'balance', title: '金额' },
            { field: 'mark', title: '生日' }
        ],
        onLoadSuccess: function(){  //加载成功时执行
            console.info("加载成功");
        },
        onLoadError: function(){  //加载失败时执行
            console.info("加载数据失败");
        }

    });

</script>
{/block}
