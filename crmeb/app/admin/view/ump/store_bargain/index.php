{extend name="public/container"}
{block name="head_top"}
<script type="text/javascript" src="{__PLUG_PATH}jquery.downCount.js"></script>
{/block}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>砍价商品搜索</h5>
                <div class="ibox-tools">
                    <a class="collapse-link">
                        <i class="fa fa-chevron-up"></i>
                    </a>
                </div>
            </div>
            <div class="ibox-content">
                <div class="alert alert-success alert-dismissable">
                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                    目前拥有{$countBargain}个砍价商品
                </div>
                <form class="layui-form">
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">搜　　索：</label>
                            <div class="layui-input-inline">
                                <input type="text" name="store_name" lay-verify="store_name" style="width: 100%" autocomplete="off" placeholder="请输入商品名称,编号" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">砍价状态：</label>
                            <div class="layui-input-inline">
                                <select name="status" lay-verify="status">
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
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div class="ibox-content">
                <div class="table-responsive" style="margin-top: 20px;">
                    <div class="layui-btn-container">
                        <a class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame(this.innerText,'{:Url('create')}',{h:700,w:1100});">添加砍价商品</a>
                    </div>
                    <table class="layui-hide" id="bargainList" lay-filter="bargainList"></table>
                    <script type="text/html" id="status">
                        <input type='checkbox' name='status' lay-skin='switch' value="{{d.id}}" lay-filter='status' lay-text='开启|关闭'  {{ d.status == 1 ? 'checked' : '' }}>
                    </script>
                    <script type="text/html" id="statusCn">
                        {{ d.status == 1 ? d.start_name : '关闭' }}
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
                        <button type="button" class="layui-btn layui-btn-xs" onclick="$eb.createModalFrame('{{d.title}}-设置规格','{:Url('attr_list')}?id={{d.id}}',{h:1000,w:1400});"><i class="layui-icon layui-icon-util"></i>规格</button>

                        <button type="button" class="layui-btn layui-btn-xs" onclick="dropdown(this)">操作<span class="caret"></span></button>
                        <ul class="layui-nav-child layui-anim layui-anim-upbit">
                            <li>
                                <a href="javascript:void(0);" onclick="$eb.createModalFrame('{{d.title}}-编辑','{:Url('edit')}?id={{d.id}}')"><i class="layui-icon layui-icon-edit"></i>编辑活动</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" onclick="$eb.createModalFrame('{{d.title}}-编辑内容','{:Url('edit_content')}?id={{d.id}}')"><i class="layui-icon layui-icon-edit"></i>编辑详情</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);"  onclick="$eb.createModalFrame('{{d.title}}-编辑规则','{:Url('edit_rule')}?id={{d.id}}')"><i class="layui-icon layui-icon-edit"></i>编辑规则</a>
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
<script src="{__FRAME_PATH}js/content.min.js?v=1.0.0"></script>
<script>
    layList.form.render();
    window.$bargainId = <?php echo json_encode($bargainId);?>;
    $(document).on('click','#time',function () {
        var arr = $('#time').text().split(' - ');
        var reg = new RegExp("-","g");//g,表示全部替换。
        var newArr = [];
        if(arr.length >1){
            $.each(arr,function (index,item) {
                newArr.push(item.replace(reg,"/"));
            })
            $('#time').data('value',newArr.join(' - '));
        }
    });
    layList.tableList('bargainList',"{:Url('get_bargain_list')}",function () {
        return [
            {field: 'id', title: '编号', sort: true,width:'5%',event:'id'},
            {field: 'image', title: '砍价图片',event:'open_image', width: '8%',templet: '<p><img src="{{d.image}}" alt="{{d.title}}"></p>'},
            {field: 'title', title: '砍价名称'},
            {field: 'price', title: '砍价价格',width:'6%'},
            {field: 'bargain_min_price', title: '砍价区间',width:'8%',templet: '<span>{{d.bargain_min_price}}~{{d.bargain_max_price}}</span>'},
            {field: 'min_price', title: '最低价',width:'6%'},
            {field: 'count_people_all', title: '参与人数',width:'7%',templet: '<span>【{{d.count_people_all}}】人</span>'},
            {field: 'count_people_help', title: '帮忙砍价人数',width:'7%',templet: '<span>【{{d.count_people_help}}】人</span>'},
            {field: 'count_people_success', title: '砍价成功人数',width:'7%',templet: '<span>【{{d.count_people_success}}】人</span>'},
            {field: 'quota_show', title: '限量',width:'4%'},
            {field: 'quota', title: '限量剩余',width:'6%'},
            {field: '_stop_time', title: '结束时间', width: '8%',toolbar: '#stopTime'},
            {field: 'status', title: '状态',width:'6%',templet:"#status"},
            {field: 'right', title: '操作', width: '10%', align: 'center', toolbar: '#barDemo'}
        ]
    });
    layList.laydate.render({
        elem:'#time',
        trigger:'click',
        eventElem:'#zdy_time',
        range:true,
    });
    setTime();
    function setTime(){
        setTimeout(function () {
            $.each($bargainId,function (index,item) {
                console.log($('.count-time-'+item).attr('data-time'));
                $('.count-time-'+item).downCount({
                    date: $('.count-time-'+item).attr('data-time'),
                    offset: +8
                });
            })
        },3000);
    }
    layList.search('search',function(where){
        layList.reload(where);
        setTime();
    });
    layList.search('export',function(where){
        location.href=layList.U({c:'ump.store_bargain',a:'get_bargain_list',q:{
                store_name:where.store_name,
                status:where.status,
                export:1,
            }});
    })
    layList.switch('status',function (odj,value,name){
        if(odj.elem.checked==true){
            layList.baseGet(layList.Url({c:'ump.store_bargain',a:'set_bargain_status',p:{status:1,id:value}}),function (res) {
                layList.msg(res.msg);
            }, function () {
                odj.elem.checked = false;
                layui.form.render();
                layer.open({
                    type: 1
                    ,offset: 'auto'
                    ,id: 'layerDemoauto' //防止重复弹出
                    ,content: '<div style="padding: 20px 100px;">请先配置规格</div>'
                    ,btn: '设置规格'
                    ,btnAlign: 'c' //按钮居中
                    ,shade: 0 //不显示遮罩
                    ,yes: function(){
                        layer.closeAll();
                        $eb.createModalFrame('设置规格','{:Url('attr_list')}?id='+value+'',{h:1000,w:1400});
                    }
                });
            });
        }else{
            layList.baseGet(layList.Url({c:'ump.store_bargain',a:'set_bargain_status',p:{status:0,id:value}}),function (res) {
                layList.msg(res.msg);
            });
        }
    });
    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'delstor':
                var url=layList.U({c:'ump.store_bargain',a:'delete',q:{id:data.id}});
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
