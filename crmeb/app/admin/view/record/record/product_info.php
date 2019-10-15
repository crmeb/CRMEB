{extend name="public/container"}
{block name="head_top"}
<!--suppress JSAnnotator -->
<style>
    .layui-input-block button{
        border: 1px solid rgba(0,0,0,0.1);
        margin-bottom: 10px;
    }
    .layuiadmin-badge, .layuiadmin-btn-group, .layuiadmin-span-color {
        position: absolute;
        right: 15px;
    }
    .layuiadmin-badge {
        top: 50%;
        margin-top: -9px;
        color: #01AAED;
    }
</style>
<script src="{__PLUG_PATH}echarts.common.min.js"></script>
{/block}
{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space10"  id="app">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">搜索条件</div>
                <div class="layui-card-body">
                    <div class="layui-row layui-col-space10 layui-form-item">
                        <div class="layui-col-lg12">
                            <label class="layui-form-label">创建时间:</label>
                            <div class="layui-input-block" data-type="data" v-cloak="">
                                <button class="layui-btn layui-btn-sm" type="button" v-for="item in dataList" @click="setData(item)" :class="{'layui-btn-primary':data!=item.value}">{{item.name}}</button>
                                <button class="layui-btn layui-btn-sm" type="button" ref="time" @click="setData({value:'zd',is_zd:true})" :class="{'layui-btn-primary':data!='zd'}">自定义</button>
                                <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" v-show="showtime==true" ref="date_time">{$year.0} - {$year.1}</button>
                            </div>
                        </div>
                        <div class="layui-col-lg12">
                            <div class="layui-input-block">
                                <button @click="search" type="button" class="layui-btn layui-btn-sm layui-btn-normal">
                                    <i class="layui-icon layui-icon-search"></i>搜索</button>
                                <button @click="refresh" type="reset" class="layui-btn layui-btn-primary layui-btn-sm">
                                    <i class="layui-icon layui-icon-refresh" ></i>刷新</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div :class="item.col!=undefined ? 'layui-col-sm'+item.col+' '+'layui-col-md'+item.col:'layui-col-sm3 layui-col-md3'" v-for="item in badge" v-cloak="">
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
        <div class="layui-col-sm12">
            <div class="layui-card">
                <div class="layui-card-header">发放优惠券:</div>
                <div class="layui-card-body layui-row">
                    <div class="layui-col-md12">
                        <div class="layui-btn-container" ref="echarts_list" id="echarts_list" style="height:400px"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-sm12">
            <div class="layui-card">
                <div class="layui-card-header">销售记录:</div>
                <div class="layui-card-body layui-row">
                    <table class="layui-table" lay-skin="line">
                        <thead>
                        <tr>
                            <th>序号</th>
                            <th>时间</th>
                            <th>会员昵称</th>
                            <th>商品单价(元)</th>
                            <th>购买数量</th>
                            <th>销售总额(元)</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="item in SalelList">
                            <td>{{item.id}}</td>
                            <td>{{item._add_time}}</td>
                            <td>{{item.nickname}}</td>
                            <td>{{item.price}}</td>
                            <td>{{item.num}}</td>
                            <td>{{item.price*item.num}}</td>
                        </tr>
                        <tr v-show="SalelList.length<=0" style="text-align: center">
                            <td colspan="6">暂无数据</td>
                        </tr>
                        </tbody>
                    </table>
                    <div ref="page" v-show="count > limit" style="text-align: right;"></div>
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
                id:<?=$id?>,
                option: {},
                badge:[],
                SalelList:[],
                dataList: [
                    {name: '全部', value: ''},
                    {name: '今天', value: 'today'},
                    {name: '本周', value: 'week'},
                    {name: '本月', value: 'month'},
                    {name: '本季度', value: 'quarter'},
                    {name: '本年', value: 'year'},
                ],
                page:1,
                data: '',
                title: '全部商品',
                count: 0,
                myChart: {},
                showtime: false,
                limit:20,
            },
            watch: {
                page:function () {
                    this.getSalelList();
                }
            },
            methods: {
                getProductBadgeList:function(){
                    var that=this;
                    layList.baseGet(layList.U({a:'getProductBadgeList',q:{id:this.id,data:this.data}}),function (rem) {
                        that.badge=rem.data;
                    });
                },
                getProductCurve:function(){
                    var that=this;
                    layList.baseGet(layList.U({a:'getProductCurve',q:{data:this.data,limit:this.limit,id:this.id}}),function (rem) {
                        var quxian=that.setoption(rem.data.seriesdata,rem.data.date,rem.data.zoom);
                        that.myChart.list.setOption(quxian);
                    });
                },
                getSalelList(){
                    var that=this;
                    layList.baseGet(layList.U({a:'getSalelList',q:{id:this.id,data:this.data,page:this.page,limit:this.limit}}),function (rem) {
                        that.SalelList=rem.data;
                    });
                },
                setoption:function(seriesdata,xdata,Zoom){
                    this.option={
                        title: {text:'销售走势',x:'center'},
                        xAxis : [{type : 'category', data :xdata,}],
                        yAxis : [{type : 'value'}],
                        series:[{type:'line',data:seriesdata,markPoint :{
                                data : [
                                    {type : 'max', name: '最大值'},
                                    {type : 'min', name: '最小值'}
                                ]
                            },
                            itemStyle:{
                                color:'#81BCFF'
                            }
                        }
                        ],
                    };
                    if(Zoom!=''){
                        this.option.dataZoom=[{startValue:Zoom},{type:'inside'}];
                    }
                    return  this.option;
                },
                setData:function(item){
                    var that=this;
                    if(item.is_zd==true){
                        that.showtime=true;
                        this.data=this.$refs.date_time.innerText;
                    }else{
                        this.showtime=false;
                        this.data=item.value;
                    }
                },
                refresh:function () {
                    this.data='';
                    this.getProductBadgeList();
                    this.getProductCurve();
                    this.getSalelList();
                    this.getProductCount();
                },
                search:function(){
                    this.getProductBadgeList();
                    this.getProductCurve();
                    this.getSalelList();
                    this.getProductCount();
                },
                getProductCount:function(){
                    var that=this;
                    layList.baseGet(layList.U({a:'getProductCount',q:{id:this.id,data:this.data}}),function(rem){
                        that.count=rem.data;
                        console.log(that.count)
                        layList.laypage.render({
                            elem: that.$refs.page
                            ,count:that.count
                            ,limit:that.limit
                            ,theme: '#1E9FFF',
                            jump:function(obj){
                                that.page=obj.curr;
                            }
                        });
                    })
                },
                setChart:function(name,myChartname){
                    this.myChart[myChartname]=echarts.init(name);
                },
            },
            mounted:function () {
                this.setChart(this.$refs.echarts_list,'list');
                this.getProductBadgeList();
                this.getProductCurve();
                this.getSalelList();
                this.getProductCount();
                var that=this;
                layList.laydate.render({
                    elem:this.$refs.date_time,
                    trigger:'click',
                    eventElem:this.$refs.time,
                    range:true,
                    change:function (value) {
                        that.data=value;
                    }
                });


            }
        })
    })
</script>
{/block}