{extend name="public/container"}
{block name="head_top"}
{/block}
{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <div class="layui-col-md6 layui-col-sm6">
            <div class="layui-card">
                <div class="layui-card-header">总佣金排名</div>
                <div class="layui-card-body">
                    <table class="layui-table layuiadmin-page-table" lay-skin="line">
                        <thead>
                        <tr>
                            <th>排名</th>
                            <th>昵称/手机号</th>
                            <th>收入佣金</th>
                            <th>佣金余额</th>
                        </tr>
                        </thead>
                        <tbody v-cloak="">
                        <tr v-for="(item,index) in pointList">
                            <td>{{page==1 ?index+1:(index+1)+(page-1)*limit}}</td>
                            <td>{{item.real_name}}</td>
                            <td>{{item.extract_price}}</td>
                            <td>{{item.balance}}</td>
                        </tr>
                        <tr v-show="pointList.length<=0" style="text-align: center">
                            <td colspan="4">暂无数据</td>
                        </tr>
                        </tbody>
                    </table>
                    <div ref="page" v-show="count>limit" style="text-align: right;"></div>
                </div>
            </div>
        </div>
        <div class="layui-col-md6 layui-col-sm6">
            <div class="layui-card">
                <div class="layui-card-header">本月佣金排名</div>
                <div class="layui-card-body">
                    <table class="layui-table layuiadmin-page-table" lay-skin="line">
                        <thead>
                        <tr>
                            <th>排名</th>
                            <th>昵称/手机号</th>
                            <th>收入佣金</th>
                            <th>佣金余额</th>
                        </tr>
                        </thead>
                        <tbody v-cloak="">
                        <tr v-for="(item,index) in monthList">
                            <td>{{page==1 ?index+1:(index+1)+(monthpage-1)*limit}}</td>
                            <td>{{item.real_name}}</td>
                            <td>{{item.extract_price}}</td>
                            <td>{{item.balance}}</td>
                        </tr>
                        <tr v-show="monthList.length<=0" style="text-align: center">
                            <td colspan="4">暂无数据</td>
                        </tr>
                        </tbody>
                    </table>
                    <div ref="month_page" v-show="monthcount>limit" style="text-align: right;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
<script>
    require(['vue'],function(Vue) {
        new Vue({
            el: "#app",
            data: {
                pointList:[],
                monthList:[],
                count:0,
                limit:20,
                page:1,
                monthpage:1,
                monthcount:0
            },
            watch:{
                page:function (n){
                    this.getCommissionList();
                },
                monthpage:function (n) {
                    this.getMonthCommissionList()
                }
            },
            methods:{
                getMonthCommissionList:function(){
                    var that=this;
                    layList.baseGet(layList.U({a:'getMonthCommissionList',p:{page:this.page,limit:this.limit}}),function (rem) {
                        that.monthList=rem.data;
                    });
                },
                getCommissionList:function (){
                    var that=this;
                    var index=layList.layer.load(2,{shade: [0.3,'#fff']});
                    layList.baseGet(layList.U({a:'getCommissionList',p:{limit:this.limit,page:this.page}}),function (rem) {
                        layList.layer.close(index);
                        that.pointList=rem.data;
                    },function () {
                        layList.layer.close(index);
                    });
                }
            },
            mounted:function () {
                var that=this;
                this.getCommissionList();
                this.getMonthCommissionList();
                layList.baseGet(layList.U({a:'getCommissonCount'}),function (rem) {
                    that.count=rem.data;
                    layList.laypage.render({
                        elem: that.$refs.month_page
                        ,count:that.count
                        ,limit:that.limit
                        ,theme: '#1E9FFF',
                        jump:function(obj){
                            that.page=obj.curr;
                        }
                    });
                });
                layList.baseGet(layList.U({a:'getMonthCommissonCount'}),function (rem) {
                    that.count=rem.data;
                    layList.laypage.render({
                        elem: that.$refs.page
                        ,count:that.monthcount
                        ,limit:that.limit
                        ,theme: '#1E9FFF',
                        jump:function(obj){
                            that.monthpage=obj.curr;
                        }
                    });
                });
            }
        })
    })
</script>
{/block}