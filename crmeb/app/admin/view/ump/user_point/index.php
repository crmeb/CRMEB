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
                                <label class="layui-form-label">昵称/ID</label>
                                <div class="layui-input-block">
                                    <input type="text" name="nickname" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">时间范围</label>
                                <div class="layui-input-inline" style="width: 200px;">
                                    <input type="text" name="start_time" id="start_time" placeholder="开始时间" autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid">-</div>
                                <div class="layui-input-inline" style="width: 200px;">
                                    <input type="text" name="end_time" id="end_time" placeholder="结束时间" autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <div class="layui-input-inline">
                                    <button class="layui-btn layui-btn-sm layui-btn-normal" lay-submit="search" lay-filter="search">
                                        <i class="layui-icon layui-icon-search"></i>搜索</button>
                                    <button class="layui-btn layui-btn-primary layui-btn-sm export" lay-submit="export" lay-filter="export">
                                        <i class="fa fa-floppy-o" style="margin-right: 3px;"></i>导出</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div :class="item.col!=undefined ? 'layui-col-sm'+item.col+' '+'layui-col-md'+item.col:'layui-col-sm6 layui-col-md3'" v-for="item in badge" v-cloak="">
            <div class="layui-card">
                <div class="layui-card-header">
                    {{item.name}}
                    <span class="layui-badge layuiadmin-badge" :class="item.background_color">{{item.field}}</span>
                </div>
                <div class="layui-card-body">
                    <p class="layuiadmin-big-font">{{item.count}}</p>
                    <p v-show="item.content!=undefined">
                        {{item.content}}
                        <span class="layuiadmin-span-color">{{item.sum}}<i :class="item.class"></i></span>
                    </p>
                </div>
            </div>
        </div>
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">积分日志</div>
                <div class="layui-card-body">
                    <table class="layui-hide" id="userList" lay-filter="userList"></table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
{/block}
{block name="script"}
<script>
    require(['vue'],function(Vue) {
        new Vue({
            el: "#app",
            data: {
                badge:[],
            },
            methods:{
                getUserPointBadgeList:function(where){
                    var q={},that=this,where=where || {};
                    q.start_time=where.start_time || '';
                    q.end_time=where.end_time || '';
                    q.nickname=where.nickname || '';
                    layList.baseGet(layList.U({c:'ump.user_point',a:'getUserPointBadgeList',q:q}),function (rem) {
                        that.badge=rem.data;
                    });
                }
            },
            mounted:function () {
                this.getUserPointBadgeList();
                layList.form.render();
                layList.tableList('userList',"{:Url('getponitlist')}",function () {
                    return [
                        {field: 'id', title: 'ID', sort: true,event:'uid',width:'8%'},
                        {field: 'title', title: '标题' },
                        {field: 'balance', title: '积分余量',sort:true,event:'now_money'},
                        {field: 'number', title: '明细数字',sort:true},
                        {field: 'mark', title: '备注'},
                        {field: 'nickname', title: '用户微信昵称'},
                        {field: 'add_time', title: '添加时间',align:'center'},
                    ];
                });
                layList.date({elem:'#start_time',theme:'#393D49',type:'datetime'});
                layList.date({elem:'#end_time',theme:'#393D49',type:'datetime'});
                var that=this;
                layList.search('search',function(where){
                    if(where.start_time!=''){
                        if(where.end_time==''){
                            layList.msg('请选择结束时间');
                            return;
                        }
                    }
                    if(where.end_time!=''){
                        if(where.start_time==''){
                            layList.msg('请选择开始时间');
                            return;
                        }
                    }
                    layList.reload(where);
                    that.getUserPointBadgeList(where);
                });
                layList.search('export',function(where){
                    var q={},where=where || {};
                    q.start_time=where.start_time || '';
                    q.end_time=where.end_time || '';
                    q.nickname=where.nickname || '';
                    location.href=layList.U({c:'ump.user_point',a:'export',q:q});
                })
            }
        })
    });

</script>
{/block}
