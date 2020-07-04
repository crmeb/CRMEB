{extend name="public/container"}
{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">搜索条件</div>
                <div class="layui-card-body">
                    <form class="layui-form layui-form-pane" action="">
                        <div class="layui-inline">
                            <label class="layui-form-label">产品名称</label>
                            <div class="layui-input-block">
                                <input type="text" name="store_name" class="layui-input" placeholder="请输入产品名称,关键字,编号">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <div class="layui-input-inline">
                                <button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit="search" lay-filter="search">
                                    <i class="layui-icon layui-icon-search"></i>搜索</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body">
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <!--图片-->
                    <script type="text/html" id="image">
                        <img style="cursor: pointer" lay-event="open_image" src="{{d.image}}">
                    </script>
                    <!--操作-->
                    <script type="text/html" id="act">
                        <button type="button" class="layui-btn layui-btn-normal layui-btn-sm" lay-event='select'>选择</button>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
{/block}
{block name='script'}
<script>
    var id='{$id}';
    layList.form.render();
    //加载列表
    layList.tableList('List',"{:Url('store.store_product/product_ist',['type'=>1])}",function (){
        return [
            {field: 'id', title: 'ID', sort: true,event:'id',width:'8%'},
            {field: 'image', title: '产品图片',templet:'#image'},
            {field: 'store_name', title: '产品名称',templet:'#store_name'},
            {field: 'right', title: '操作',align:'center',toolbar:'#act'},
        ]
    });
    //点击事件绑定
    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'select':
                $eb.$swal('delete',function(){
                    $eb.axios.post(layList.U({a:'edit_article',q:{id:id}}),{product_id:data.id}).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success',res.data.msg);
                            var index = parent.layer.getFrameIndex(window.name);
                            parent.layer.close(index);
                            parent.$(".J_iframe:visible")[0].contentWindow.location.reload();
                        }else
                            return Promise.reject(res.data.msg || '关联失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                },{title:'确定选择此产品？',text:'关联后可在文章列表取消',confirm:'确认'})
                break;
        }
    })
    //查询
    layList.search('search',function(where){
        layList.reload(where);
    });
</script>
{/block}