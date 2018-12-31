{extend name="public/container"}
{block name="head_top"}
<script src="{__PLUG_PATH}echarts.common.min.js"></script>
{/block}
{block name="content"}
<div class="layui-fluid">
    <div class="layui-row layui-col-space15"  id="app">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">搜索条件</div>
                <div class="layui-card-body">
                    <div class="layui-carousel layadmin-carousel layadmin-shortcut" lay-anim="" lay-indicator="inside" lay-arrow="none" style="background:none">
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
            </div>
        </div>
        <div class="layui-col-sm6 layui-col-md6" v-for="item in badge" v-cloak="">
            <div class="layui-card">
                <div class="layui-card-header">
                    {{item.name}}
                    <span class="layui-badge layuiadmin-badge" :class="item.background_color">{{item.field}}</span>
                </div>
                <div class="layui-card-body">
                    <p class="layuiadmin-big-font">{{item.count}}</p>
                    <p>
                        {{item.content}}
                        <span class="layuiadmin-span-color">{{item.sum}}<i :class="item.class"></i></span>
                    </p>
                </div>
            </div>
        </div>
        <div class="layui-col-sm8">
            <div class="layui-card">
                <div class="layui-card-header">返佣图表</div>
                <div class="layui-card-body layui-row">
                    <div class="layui-col-md12">
                        <div class="layui-btn-container" ref="echarts_list" style="height:400px"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-sm4">
            <div class="layui-card">
                <div class="layui-card-header">返佣分布</div>
                <div class="layui-card-body layui-row">
                    <div class="layui-col-md12">
                        <div class="layui-btn-container" ref="echarts_list_sex" style="height:400px"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-sm12">
            <div class="layui-card">
                <div class="layui-card-header">返佣列表</div>
                <div class="layui-card-body layui-row">
                    <table class="layui-table layuiadmin-page-table" lay-skin="line">
                        <thead>
                        <tr>
                            <th width="60%">返佣商</th>
                            <th width="30%">返佣级别</th>
                            <th width="10%">返佣金额</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="item in FanYongList">
                            <td>
                                <div class="layui-col-md2 layui-col-sm2">
                                    <a href="javascript:;">
                                        <div class="layadmin-text-center">
                                            <img :src="item.avatar">
                                        </div>
                                    </a>
                                </div>
                                <div class="layui-col-md5 layui-col-sm5">
                                    <div class="layui-colla-content layui-show" style="border: none">
                                        <p style="color: #836FFF">订单编号:{{item.order_id}}</p>
                                        <p style="color:#B03060">返利时间:{{item.add_time}}</p>
                                        <p style="color:#388E8E">买 家 I D:{{item.down_uid}}</p>
                                        <p style="color:#333333" v-show="item.spread_uid!=0">上 级 I D:{{item.spread_uid}}</p>
                                    </div>
                                </div>
                                <div class="layui-col-md5 layui-col-sm5">
                                    <div class="layui-colla-content layui-show" style="border: none">
                                        <p style="color: #D2B48C">会员ID:{{item.uid}}</p>
                                        <p style="color: #9B30FF">昵　称:{{item.nickname}}</p>
                                    </div>
                                </div>
                            </td>
                            <td>{{item.level==0? '顶级':item.level+'级分销'}}</td>
                            <td>{{item.number}}</td>
                        </tr>
                        <tr v-show="FanYongList.length<=0" style="text-align: center">
                            <td colspan="3">暂无数据</td>
                        </tr>
                        </tbody>
                    </table>
                    <div ref="page" v-show="count>limit" style="text-align: right;"></div>
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
                option: {},
                badge:[],
                sexoption:{},
                FanYongList:[],
                dataList: [
                    {name: '全部', value: ''},
                    {name: '今天', value: 'today'},
                    {name: '本周', value: 'week'},
                    {name: '本月', value: 'month'},
                    {name: '本季度', value: 'quarter'},
                    {name: '本年', value: 'year'},
                ],
                data: '',
                myChart: {},
                count:0,
                limit:10,
                page:1,
                showtime: false,
            },
            watch:{
                page:function (n) {
                    // console.log(n);
                    this.getFanList();
                }
            },
            methods:{
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
                getFanList:function(){
                    var that=this;
                    layList.baseGet(layList.U({a:'getFanList',p:{page:this.page,limit:this.limit}}),function (rem) {
                        that.FanYongList=rem.data;
                    });
                },
                getbadge:function(){
                    var that=this;
                    layList.baseGet(layList.U({a:'getRebateBadge',q:{data:this.data}}),function (rem) {
                        that.badge=rem.data;
                    });
                },
                getEchartsData:function(){
                    var index=layList.layer.load(2,{shade: [0.3,'#fff']}),that=this;
                    layList.baseGet(layList.U({a:'getUserBillBrokerage',q:{data:this.data}}),function (rem) {
                        layList.layer.close(index);
                        var option=that.setfenbuoption(rem.data.listdata,rem.data.legdata,rem.data.dataZoom);
                        that.myChart.list.setOption(option);
                        var excoption=that.setsexoption(rem.data.fenbudate,rem.data.fenbu_legend);
                        that.myChart.sex.setOption(excoption);
                    },function () {
                        layList.layer.close(index);
                    });
                },
                setfenbuoption:function(seriesdata,xdata,dataZoom){
                     this.option={
                        title: {text:'返佣金额柱状图表',subtext:"时间段内的返佣金额"},
                        tooltip: {trigger: 'axis'},
                        toolbox: {show : true,},
                        xAxis : [{type : 'category', data :xdata,}],
                        yAxis : [{type : 'value'}],
                        series:[{type:'bar',data:seriesdata,markPoint :{
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
                    if(dataZoom!=''){
                        this.option.dataZoom=[{startValue:dataZoom},{type:'inside'}];
                    }
                    return  this.option;
                },
                setsexoption:function(seriesdata,legdata){
                    return this.sexoption={
                        title: {text:'返佣比例图表',left:'center',subtext:'单个用户返佣和总佣金比例TOP8'},
                        tooltip: {trigger: 'item'},
                        legend: {data:legdata,bottom:10,left:'center'},
                        series:[{ type: 'pie', radius : '65%', center: ['50%', '50%'], selectedMode: 'single',data:seriesdata}],
                    };
                },
                search:function(){
                    this.getEchartsData();
                    this.getbadge();
                },
                refresh:function () {
                    this.data='';
                    this.getEchartsData();
                },
                setChart:function(name,myChartname){
                    this.myChart[myChartname]=echarts.init(name);
                },
            },
            mounted:function () {
                this.setChart(this.$refs.echarts_list,'list');
                this.setChart(this.$refs.echarts_list_sex,'sex');
                this.getEchartsData();
                this.getbadge();
                this.getFanList();
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
                var that=this;
                layList.baseGet(layList.U({a:'getFanCount'}),function (rem) {
                    that.count=rem.data;
                    layList.laypage.render({
                        elem: that.$refs.page
                        ,count:that.count
                        ,limit:that.limit
                        ,theme: '#1E9FFF',
                        jump:function(obj){
                            that.page=obj.curr;
                        }
                    });
                });
            }
        })
    })
</script>
{/block}