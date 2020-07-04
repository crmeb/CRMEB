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
                            <label class="layui-form-label">用户昵称</label>
                            <div class="layui-input-block">
                                <input type="text" name="nickname" class="layui-input" placeholder="请输入用户昵称">
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
                        <img style="cursor: pointer" lay-event="open_image" src="{{d.avatar}}">
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
    var parentinputname = '{$Request.param.fodder}';
    layList.form.render();
    //加载列表
    layList.tableList('List',"{:Url('system.SystemStoreStaff/get_user_list')}",function (){
        return [
            {field: 'uid', title: 'ID', sort: true,event:'id'},
            {field: 'nickname', title: '微信用户名称'},
            {field: 'avatar', title: '头像',templet:'#image'},
            {field: 'right', title: '操作',align:'center',toolbar:'#act'},
        ]
    });
    //点击事件绑定
    layList.tool(function (event,data,obj) {
        console.log(data)
        switch (event) {
            case 'select':
                console.log(parent.$f.getValue('nickname'));
                parent.$f.changeField('image',data.avatar);
                parent.$f.changeField('uid',data.uid);
                parent.$f.changeField('avatar',data.avatar);
                parent.$f.closeModal(parentinputname);
                break;
        }
    })
    //查询
    layList.search('search',function(where){
        layList.reload(where);
    });
</script>
{/block}