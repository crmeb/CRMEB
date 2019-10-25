{extend name="public/container"}
{block name="head_top"}
{/block}
{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <div class="layui-col-md6 layui-col-sm6">
            <div class="layui-card">
                <div class="layui-card-header">当前积分排名</div>
                <div class="layui-card-body">
                    <table class="layui-table layuiadmin-page-table" lay-skin="line">
                        <thead>
                        <tr>
                            <th width="10%">排名</th>
                            <th width="60%">昵称/手机号</th>
                            <th width="30%">当前积分</th>
                        </tr>
                        </thead>
                        <tbody v-cloak="">
                        <tr v-for="(item,index) in pointList">
                            <td>{{page==1 ?index+1:(index+1)+(page-1)*limit}}</td>
                            <td>{{item.nickname}}</td>
                            <td>{{item.integral}}</td>
                        </tr>
                        <tr v-show="pointList.length<=0" style="text-align: center">
                            <td colspan="3">暂无数据</td>
                        </tr>
                        </tbody>
                    </table>
                    <div ref="page" v-show="count>limit" style="text-align: right;"></div>
                </div>
            </div>
        </div>
        <div class="layui-col-md6 layui-col-sm6">
            <div class="layui-card">
                <div class="layui-card-header">本月积分排名</div>
                <div class="layui-card-body">
                    <table class="layui-table layuiadmin-page-table" lay-skin="line">
                        <thead>
                        <tr>
                            <th width="10%">排名</th>
                            <th>昵称/手机号</th>
                            <th>当前积分</th>
                        </tr>
                        </thead>
                        <tbody v-cloak="">
                        <tr v-for="(item,index) in monthList">
                            <td>{{page==1 ?index+1:(index+1)+(monthpage-1)*limit}}</td>
                            <td>{{item.nickname}}</td>
                            <td>{{item.integral}}</td>
                        </tr>
                        <tr v-show="monthList.length<=0" style="text-align: center">
                            <td colspan="3">暂无数据</td>
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
                    this.getpointList();
                },
                monthpage:function (n) {
                    this.getmonthList()
                }
            },
            methods:{
                getmonthList:function(){
                    var that=this;
                    layList.baseGet(layList.U({a:'getMonthpountList',p:{page:this.page,limit:this.limit}}),function (rem) {
                        that.monthList=rem.data;
                    });
                },
                getpointList:function (){
                    var that=this;
                    var index=layList.layer.load(2,{shade: [0.3,'#fff']});
                    layList.baseGet(layList.U({a:'getpointList',p:{limit:this.limit,page:this.page}}),function (rem) {
                        layList.layer.close(index);
                        that.pointList=rem.data;
                    },function () {
                        layList.layer.close(index);
                    });
                }
            },
            mounted:function () {
                var that=this;
                this.getpointList();
                this.getmonthList();
                layList.baseGet(layList.U({a:'getPountCount'}),function (rem) {
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
                layList.baseGet(layList.U({a:'getMonthPountCount'}),function (rem) {
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