{extend name="public/container"}
{block name="head_top"}
<script type="text/javascript" src="{__PLUG_PATH}jquery.downCount.js"></script>
{/block}
{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">拼团产品搜索</div>
                <div class="layui-card-body">
                    <div class="alert alert-success alert-dismissable">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                        目前拥有{$countCombination}个拼团产品
                    </div>
                    <form class="layui-form">
                        <div class="layui-form-item">
                            <div class="layui-inline">
                                <label class="layui-form-label">搜　　索：</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="store_name" lay-verify="store_name" style="width: 100%" autocomplete="off" placeholder="请输入产品名称,关键字,编号" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">拼团状态：</label>
                                <div class="layui-input-inline">
                                    <select name="is_show" lay-verify="is_show">
                                        <option value="">全部</option>
                                        <option value="1">开启</option>
                                        <option value="0">关闭</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                <button class="layui-btn layui-btn-sm" lay-submit="" lay-filter="search" style="font-size:14px;line-height: 9px;">
                                    <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>搜索</button>
                                <button lay-submit="export" lay-filter="export" class="layui-btn layui-btn-primary layui-btn-sm">
                                    <i class="layui-icon layui-icon-delete layuiadmin-button-btn" ></i> Excel导出</button>
                            </label>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="layui-col-sm6 layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header">
                    总展现量
                    <span class="layui-badge layuiadmin-badge">量</span>
                </div>
                <div class="layui-card-body">
                    <p class="layuiadmin-big-font">{$statistics.browseCount}</p>
                </div>
            </div>
        </div>
        <div class="layui-col-sm6 layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header">
                    访客人数
                    <span class="layui-badge layuiadmin-badge">人</span>
                </div>
                <div class="layui-card-body">
                    <p class="layuiadmin-big-font">{$statistics.visitCount}</p>
                </div>
            </div>
        </div>
        <div class="layui-col-sm6 layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header">
                    参与人数
                    <span class="layui-badge layuiadmin-badge">人</span>
                </div>
                <div class="layui-card-body">
                    <p class="layuiadmin-big-font">{$statistics.partakeCount}</p>
                </div>
            </div>
        </div>
        <div class="layui-col-sm6 layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header">
                    成团数量
                    <span class="layui-badge layuiadmin-badge">量</span>
                </div>
                <div class="layui-card-body">
                    <p class="layuiadmin-big-font">{$statistics.pinkCount}</p>
                </div>
            </div>
        </div>
        <!-- end-->
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">拼团产品列表</div>
                <div class="layui-card-body">
                    <table class="layui-hide" id="combinationList" lay-filter="combinationList"></table>
                    <script type="text/html" id="status">
                        <input type='checkbox' name='status' lay-skin='switch' value="{{d.id}}" lay-filter='status' lay-text='开启|关闭'  {{ d.is_show == 1 ? 'checked' : '' }}>
                    </script>
                    <script type="text/html" id="stopTime">
                        <div class="count-time-{{d.id}}" data-time="{{d._stop_time}}">
                            <span class="days">00</span>
                            :
                            <span class="hours">00</span>
                            :
                            <span class="minutes">00</span>
                            :
                            <span class="seconds">00</span>
                        </div>
                    </script>
                    <script type="text/html" id="barDemo">
                        <button type="button" class="layui-btn layui-btn-xs" onclick="dropdown(this)"><i class="layui-icon layui-icon-edit"></i>操作</button>
                        <ul class="layui-nav-child layui-anim layui-anim-upbit">
                            <li>
                                <a href="javascript:void(0);" onclick="$eb.createModalFrame('{{d.title}}-编辑','{:Url('edit')}?id={{d.id}}')"><i class="layui-icon layui-icon-edit"></i> 编辑</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" onclick="$eb.createModalFrame('{{d.title}}-编辑内容','{:Url('edit_content')}?id={{d.id}}')"><i class="fa fa-pencil"></i> 编辑内容</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="delstor" lay-event='delstor'><i class="layui-icon layui-icon-delete"></i> 删除</a>
                            </li>
                        </ul>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
{/block}
{block name="script"}
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
<script>
    layList.form.render();
    layList.tableList('combinationList',"{:Url('get_combination_list')}",function () {
        return [
            {field: 'id', title: '编号', sort: true,event:'id'},
            {field: 'image', title: '拼团图片',width:'10%',templet: '<p><img src="{{d.image}}" alt="{{d.title}}" class="open_image" data-image="{{d.image}}"></p>'},
            {field: 'title', title: '拼团名称',width:'10%'},
            {field: 'ot_price', title: '原价'},
            {field: 'price', title: '拼团价'},
            {field: 'stock', title: '库存'},
            {field: 'people', title: '拼团人数',templet: '<span>【{{d.people}}】人</span>'},
            {field: 'count_people_browse', title: '访客人数'},
            {field: 'browse', title: '展现量'},
            {field: 'count_people_all', title: '参与人数',templet: '<span>【{{d.count_people_all}}】人</span>'},
            {field: 'count_people_pink', title: '成团数量',templet: '<span>【{{d.count_people_pink}}】团</span>'},
            {field: 'browse', title: '浏览量'},
            {field: 'is_show', title: '产品状态',templet:"#status"},
            {field: '_stop_time', title: '结束时间',width:'8%',toolbar: '#stopTime'},
            {field: 'right', title: '操作', align: 'center', toolbar: '#barDemo'}
        ]
    });
    layList.search('search',function(where){
        layList.reload(where);
        setTime();
    });
    layList.search('export',function(where){
        location.href=layList.U({c:'ump.store_combination',a:'save_excel',q:{
            is_show:where.is_show,
            store_name:where.store_name
        }});
    })
    setTime();
    window.$combinationId = <?php echo json_encode($combinationId);?>;
    function setTime() {
        setTimeout(function () {
            $.each($combinationId,function (index,item) {
               if($('.count-time-'+item).attr('data-time')!=undefined){
                    $('.count-time-'+item).downCount({
                        date: $('.count-time-'+item).attr('data-time'),
                        offset: +8
                    });
               }
            })
        },3000);
    }
    layList.switch('status',function (odj,value,name) {
        if (odj.elem.checked == true) {
            layList.baseGet(layList.Url({
                c: 'ump.store_combination',
                a: 'set_combination_status',
                p: {status: 1, id: value}
            }), function (res) {
                layList.msg(res.msg);
            });
        } else {
            layList.baseGet(layList.Url({
                c: 'ump.store_combination',
                a: 'set_combination_status',
                p: {status: 0, id: value}
            }), function (res) {
                layList.msg(res.msg);
            });
        }
    })
    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'delstor':
                var url=layList.U({c:'ump.store_combination',a:'delete',q:{id:data.id}});
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
        }
    })
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