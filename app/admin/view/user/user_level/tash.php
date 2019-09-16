{extend name="public/container"}
{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">搜索条件</div>
                <div class="layui-card-body">
                    <form class="layui-form layui-form-pane" action="">
                        <div class="layui-form-item">
                            <div class="layui-inline">
                                <label class="layui-form-label">是否显示</label>
                                <div class="layui-input-block">
                                    <select name="is_show">
                                        <option value="">是否显示</option>
                                        <option value="1">显示</option>
                                        <option value="0">不显示</option>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">任务名称</label>
                                <div class="layui-input-block">
                                    <input type="text" name="title" class="layui-input" placeholder="请输入任务名称">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <div class="layui-input-inline">
                                    <button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit="search" lay-filter="search">
                                        <i class="layui-icon layui-icon-search"></i>搜索</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!--产品列表-->
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">任务列表</div>
                <div class="layui-card-body">
                    <div class="alert alert-info" role="alert">
                        添加等级任务,任务类型中的{literal}{$num}{/literal}会自动替换成限定数量+系统预设的单位生成任务名
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="layui-btn-container">
                        <button class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame(this.innerText,'{:Url('create_tash',['level_id'=>$level_id])}')">添加会员任务</button>
                        <button class="layui-btn layui-btn-normal layui-btn-sm" onclick="window.location.reload()"><i class="layui-icon layui-icon-refresh"></i>  刷新</button>
                    </div>
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <script type="text/html" id="image">
                        <img style="cursor: pointer" lay-event='open_image' src="{{d.image}}">
                    </script>
                    <script type="text/html" id="is_show">
                        <input type='checkbox' name='id' lay-skin='switch' value="{{d.id}}" lay-filter='is_show' lay-text='显示|隐藏'  {{ d.is_show == 1 ? 'checked' : '' }}>
                    </script>
                    <script type="text/html" id="is_must">
                        <input type='checkbox' name='id' lay-skin='switch' value="{{d.id}}" lay-filter='is_must' lay-text='全部完成|达成其一'  {{ d.is_must == 1 ? 'checked' : '' }}>
                    </script>
                    <script type="text/html" id="act">
                        <button class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('编辑','{:Url('create_tash')}?level_id={$level_id}&id={{d.id}}')">
                            <i class="fa fa-paste"></i> 编辑
                        </button>
                        <button class="layui-btn layui-btn-xs layui-btn-danger" lay-event='delete'>
                            <i class="fa fa-warning"></i> 删除
                        </button>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
{/block}
{block name="script"}
<script>
    //实例化form
    layList.form.render();
    //加载列表
    layList.tableList('List',"{:Url('get_tash_list',['level_id'=>$level_id])}",function (){
        return [
            {field: 'id', title: '编号', sort: true,event:'id'},
            {field: 'level_name', title: '等级名称',},
            {field: 'name', title: '任务名称',},
            {field: 'sort', title: '排序',sort: true,event:'sort',edit:'sort',width:'7%'},
            {field: 'is_show', title: '是否显示',templet:'#is_show',width:'10%'},
            {field: 'is_must', title: '务必达成',templet:'#is_must',width:'15%'},
            {field: 'illustrate', title: '任务说明'},
            {field: 'right', title: '操作',align:'center',toolbar:'#act',width:'13%'},
        ];
    });
    //自定义方法
    var action= {
        set_value: function (field, id, value) {
            layList.baseGet(layList.Url({
                a: 'set_tash_value',
                q: {field: field, id: id, value: value}
            }), function (res) {
                layList.msg(res.msg);
            });
        },
    }
    //查询
    layList.search('search',function(where){
        layList.reload(where,true);
    });
    layList.switch('is_show',function (odj,value) {
        if(odj.elem.checked==true){
            layList.baseGet(layList.Url({a:'set_tash_show',p:{is_show:1,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }else{
            layList.baseGet(layList.Url({a:'set_tash_show',p:{is_show:0,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }
    });

    layList.switch('is_must',function (odj,value) {
        if(odj.elem.checked==true){
            layList.baseGet(layList.Url({a:'set_tash_must',p:{is_must:1,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }else{
            layList.baseGet(layList.Url({a:'set_tash_must',p:{is_must:0,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }
    });
    //快速编辑
    layList.edit(function (obj) {
        var id=obj.data.id,value=obj.value;
        switch (obj.field) {
            case 'title':
                action.set_value('title',id,value);
                break;
            case 'sort':
                action.set_value('sort',id,value);
                break;
        }
    });
    //监听并执行排序
    layList.sort(['id','sort'],true);
    //点击事件绑定
    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'delete':
                var url=layList.U({a:'delete_tash',q:{id:data.id}});
                $eb.$swal('delete',function(){
                    $eb.axios.get(url).then(function(res){
                        if(res.status == 200 && res.data.code == 200) {
                            $eb.$swal('success',res.data.msg);
                            obj.del();
                        }else
                            return Promise.reject(res.data.msg || '删除失败')
                    }).catch(function(err){
                        $eb.$swal('error',err);
                    });
                })
                break;
            case 'open_image':
                $eb.openImage(data.image);
                break;
        }
    })
</script>
{/block}
