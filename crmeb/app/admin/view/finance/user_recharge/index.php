{extend name="public/container"}
{block name="head_top"}
<style>

</style>
{/block}
{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <!--搜索条件-->
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">搜索条件</div>
                <div class="layui-card-body">
                    <div class="layui-carousel layadmin-carousel layadmin-shortcut" lay-anim="" lay-indicator="inside" lay-arrow="none" style="background:none">
                        <div class="layui-card-body">
                            <div class="layui-row layui-col-space10 layui-form-item">
                                <div class="layui-col-lg12">
                                    <label class="layui-form-label">时间选择:</label>
                                    <div class="layui-input-block" data-type="data" v-cloak="">
                                        <button class="layui-btn layui-btn-sm" type="button" v-for="item in dataList" @click="setData(item)" :class="{'layui-btn-primary':where.data!=item.value}">{{item.name}}</button>
                                        <button class="layui-btn layui-btn-sm" type="button" ref="time" @click="setData({value:'zd',is_zd:true})" :class="{'layui-btn-primary':where.data!='zd'}">自定义</button>
                                        <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" v-show="showtime==true" ref="date_time">{$year.0} - {$year.1}</button>
                                    </div>
                                </div>
                                <div class="layui-col-lg12">
                                    <label class="layui-form-label">支付类型:</label>
                                    <div class="layui-input-block" v-cloak="">
                                        <button class="layui-btn layui-btn-sm" :class="{'layui-btn-primary':where.paid !== item.value}" @click="where.paid = item.value" type="button" v-for="item in paidStatus">{{item.name}}
                                            <span v-if="item.count!=undefined" class="layui-badge layui-bg-gray">{{item.count}}</span></button>
                                    </div>
                                </div>
                                <div class="layui-col-lg12">
                                    <label class="layui-form-label">用户昵称:</label>
                                    <div class="layui-input-block">
                                        <input type="text" name="nickname" style="width: 50%" v-model="where.nickname" placeholder="请输入姓名、电话、UID" class="layui-input">
                                    </div>
                                </div>
                                <div class="layui-col-lg12">
                                    <div class="layui-input-block">
                                        <button @click="search" type="button" class="layui-btn layui-btn-sm layui-btn-normal">
                                            <i class="layui-icon layui-icon-search"></i>搜索</button>
                                        <button @click="excel" type="button" class="layui-btn layui-btn-warm layui-btn-sm export" type="button">
                                            <i class="fa fa-floppy-o" style="margin-right: 3px;"></i>导出</button>
                                        <button @click="refresh" type="reset" class="layui-btn layui-btn-primary layui-btn-sm">
                                            <i class="layui-icon layui-icon-refresh" ></i>刷新</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end-->
        <!-- 中间详细信息-->
        <div :class="item.col!=undefined ? 'layui-col-sm'+item.col+' '+'layui-col-md'+item.col:'layui-col-sm6 layui-col-md3'" v-for="item in badge" v-cloak="" v-if="item.count > 0">
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
        <!--enb-->
    </div>
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">充值记录列表</div>
                <div class="layui-card-body">
                    <table class="layui-hide" id="userList" lay-filter="userList"></table>
                    <script type="text/html" id="avatar">
                        <img style="cursor: pointer" lay-event='open_image' src="{{d.avatar}}">
                    </script>
                    <script type="text/html" id="act">
                        {{# if(d.paid && parseFloat(d.refund_price) <= 0) { }}
                        <button type="button" class="layui-btn layui-btn-xs layui-btn-normal" lay-event='refund'>退款</button>
                        {{# }else if(d.paid == 0){ }}
                        <button type="button" class="layui-btn layui-btn-xs layui-btn-danger" lay-event='delect'>删除</button>
                        {{# }else{ }}
                        <button type="button" class="layui-btn layui-btn-xs layui-btn-disabled">暂无操作</button>
                        {{# } }}
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
    layList.form.render();
    layList.tableList('userList',"{:Url('get_user_recharge_list')}",function () {
        return [
            {field: 'uid', title: 'UID', sort: true,width:'5%'},
            {field: 'avatar', title: '头像',templet:'#avatar'},
            {field: 'nickname', title: '用户昵称'},
            {field: 'order_id', title: '订单号'},
            {field: 'price', title: '支付金额',sort:true},
            {field: 'paid_type', title: '是否支付'},
            {field: '_recharge_type', title: '充值类型'},
            {field: '_pay_time', title: '支付时间'},
            {field: 'right', title: '操作',toolbar:'#act',width:'5%'},
        ];
    });
    layList.date({elem:'#start_time',theme:'#393D49',type:'datetime'});
    layList.date({elem:'#end_time',theme:'#393D49',type:'datetime'});
    layList.search('search',function(where){
        if(where.start_time!='' && where.end_time=='') return layList.msg('请选择结束时间')
        if(where.end_time!='' && where.start_time=='') return layList.msg('请选择开始时间')
        console.log(where);
        layList.reload(where,true);
    });
    layList.tool(function (event,data,obj) {
        switch (event) {
            case 'open_image':
                if($eb)
                    $eb.openImage(data.avatar);
                else
                    layList.layer.open({
                        type: 1,
                        title: false,
                        closeBtn: 0,
                        shadeClose: true,
                        content: '<img src="'+data.avatar+'" style="display: block;width: 100%;" />'
                    });
                break;
            case 'refund':
                $eb.createModalFrame('退款',layList.U({a:'edit',q:{id:data.id}}),{h:'300',w:'500'});
                break;
            case 'delect':
                var url =layList.U({a:'delect',p:{id:data.id}});
                if (data.paid) return layList.msg('已支付的记录无法删除！');
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
                },{'title':'您确定要删除此条充值记录吗？','text':'删除后将无法恢复,请谨慎操作！','confirm':'是的，我要删除'})
                break;
        }
    });
    require(['vue'],function(Vue) {
        new Vue({
            el: "#app",
            data: {
                badge: [],
                dataList: [
                    {name: '全部', value: ''},
                    {name: '今天', value: 'today'},
                    {name: '昨天', value: 'yesterday'},
                    {name: '最近7天', value: 'lately7'},
                    {name: '最近30天', value: 'lately30'},
                    {name: '本月', value: 'month'},
                    {name: '本年', value: 'year'},
                ],
                paidStatus:[
                    {name:'全部',value:''},
                    {name:'已支付',value:1},
                    {name:'未支付',value:0},
                ],
                where:{
                    data:'',
                    paid:'',
                    nickname: '',
                    excel:0,
                },
                showtime: false,
            },
            watch:{

            },
            methods:{
                getBadge:function(){
                    var that=this;
                    layList.basePost(layList.Url({a:'get_badge'}),this.where,function (rem) {
                        that.badge=rem.data;
                    });
                },
                setData:function(item){
                    var that=this;
                    if(item.is_zd==true){
                        that.showtime=true;
                        this.where.data=this.$refs.date_time.innerText;
                    }else{
                        this.showtime=false;
                        this.where.data=item.value;
                    }
                },
                search:function () {
                    this.where.excel=0;
                    this.getBadge();
                    layList.reload(this.where,true);
                },
                excel:function () {
                    this.where.excel=1;
                    location.href=layList.U({a:'get_user_recharge_list',q:this.where});
                },
                refresh:function () {
                    layList.reload();
                    this.getBadge();
                }
            },
            mounted:function () {
                this.getBadge();
                layList.laydate.render({
                    elem:this.$refs.date_time,
                    trigger:'click',
                    eventElem:this.$refs.time,
                    range:true,
                    change:function (value){
                        that.where.data=value;
                    }
                });
                window.SuccessFun = this.search
            }
        })
    });
</script>
{/block}
