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
                                <label class="layui-form-label">等级名称</label>
                                <div class="layui-input-block">
                                    <input type="text" name="title" class="layui-input" placeholder="请输入等级名称">
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
                <div class="layui-card-header">等级列表</div>
                <div class="layui-card-body">
                    <div class="layui-btn-container">
                        <button class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame(this.innerText,'{:Url('create')}')">添加会员等级</button>
                    </div>
                    <table class="layui-hide" id="List" lay-filter="List"></table>
                    <script type="text/html" id="icon">
                        <img style="cursor: pointer;max-width: 50px;" lay-event='open_image' src="{{d.icon}}">
                    </script>
                    <script type="text/html" id="is_forever">
                        <input type='checkbox' name='id' disabled lay-skin='switch' value="{{d.id}}" lay-filter='is_forever' lay-text='永久|非永久'  {{ d.is_forever == 1 ? 'checked' : '' }}>
                    </script>
                    <script type="text/html" id="is_show">
                        <input type='checkbox' name='id' lay-skin='switch' value="{{d.id}}" lay-filter='is_show' lay-text='开启|关闭'  {{ d.is_show == 1 ? 'checked' : '' }}>
                    </script>
                    <script type="text/html" id="is_pay">
                        <input type='checkbox' name='id' disabled lay-skin='switch' value="{{d.id}}" lay-filter='is_pay' lay-text='付费|免费'  {{ d.is_pay == 1 ? 'checked' : '' }}>
                    </script>
                    <script type="text/html" id="act">
                        <button type="button" class="layui-btn layui-btn-xs" onclick="dropdown(this)">操作 <span class="caret"></span></button>
                        <ul class="layui-nav-child layui-anim layui-anim-upbit">
                            <li>
                                <a href="javascript:void(0)" onclick="$eb.createModalFrame(this.innerText,'{:Url(\'tash\')}?level_id={{d.id}}',{w:1000})">
                                    <i class="fa fa-wrench"></i> 等级任务
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" onclick="$eb.createModalFrame(this.innerText,'{:Url(\'create\')}?id={{d.id}}')">
                                    <i class="fa fa-edit"></i> 编辑等级
                                </a>
                            </li>
                            <li>
                                <a lay-event='delete' href="javascript:void(0)" >
                                    <i class="fa fa-times"></i> 删除等级
                                </a>
                            </li>
                        </ul>
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
    layList.tableList('List',"{:Url('get_system_vip_list')}",function (){
        return [
            {field: 'id', title: '编号', sort: true,event:'id',width:'6%',align:"center"},
            {field: 'icon', title: '等级图标',templet:'#icon',align:"center",width:'10%'},
            {field: 'name', title: '等级名称',edit:'name',width:'6%',align:"center"},
            {field: 'grade', title: '等级',edit:'grade',width:'6%',align:"center"},
            {field: 'discount', title: '享受折扣',edit:'discount',width:'8%',align:"center"},
            {field: 'valid_date', title: '有效时间',width:'12%',align:"center"},
            {field: 'is_forever', title: '是否永久',templet:'#is_forever',width:'8%',align:"center"},
            {field: 'is_show', title: '是否使用',templet:'#is_show',width:'8%',align:"center"},
            {field: 'explain', title: '等级说明',align:"center"},
            {field: 'right', title: '操作',align:'center',toolbar:'#act',width:'8%'},
        ];
    });
    //自定义方法
    var action= {
        set_value: function (field, id, value) {
            layList.baseGet(layList.Url({
                a: 'set_value',
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
            layList.baseGet(layList.Url({a:'set_show',p:{is_show:1,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }else{
            layList.baseGet(layList.Url({a:'set_show',p:{is_show:0,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }
    });
    //快速编辑
    layList.edit(function (obj) {
        var id=obj.data.id,value=obj.value;
        switch (obj.field) {
            case 'name':
                action.set_value('name',id,value);
                break;
            case 'grade':
                action.set_value('grade',id,value);
                break;
            case 'discount':
                action.set_value('discount',id,value);
                break;
        }
    });
    //监听并执行排序
    layList.sort(['id','sort'],true);
    //点击事件绑定
    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'delete':
                var url=layList.U({a:'delete',q:{id:data.id}});
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
                $eb.openImage(data.icon);
                break;
        }
    })
    //下拉框
    $(document).click(function (e) {
        $('.layui-nav-child').hide();
    })
    function dropdown(that){
        var oEvent = arguments.callee.caller.arguments[0] || event;
        oEvent.stopPropagation();
        var offset = $(that).offset();
        var top=offset.top-$(window).scrollTop();
        var index = $(that).parents('tr').data('index');
        $('.layui-nav-child').each(function (key) {
            if (key != index) {
                $(this).hide();
            }
        })
        if($(document).height() < top+$(that).next('ul').height()){
            $(that).next('ul').css({
                'padding': 10,
                'top': - ($(that).parent('td').height() / 2 + $(that).height() + $(that).next('ul').height()/2),
                'min-width': 'inherit',
                'position': 'absolute'
            }).toggle();
        }else{
            $(that).next('ul').css({
                'padding': 10,
                'top':$(that).parent('td').height() / 2 + $(that).height(),
                'min-width': 'inherit',
                'position': 'absolute'
            }).toggle();
        }
    }
</script>
{/block}
