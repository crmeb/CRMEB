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
                                    <label class="layui-form-label">状　　态:</label>
                                    <div class="layui-input-block" data-type="data" v-cloak="">
                                        <button class="layui-btn layui-btn-sm" type="button" v-for="item in is_status" @click="setStatus(item)" :class="{'layui-btn-primary':status!==item.value}">{{item.name}}</button>
                                    </div>
                                </div>
                                <div class="layui-col-lg12">
                                    <label class="layui-form-label">身　　份:</label>
                                    <div class="layui-input-block" data-type="data" v-cloak="">
                                        <button class="layui-btn layui-btn-sm" type="button" v-for="item in is_promoter" @click="setPromoter(item)" :class="{'layui-btn-primary':Promoter!==item.value}">{{item.name}}</button>
                                    </div>
                                </div>
                                <div class="layui-col-lg12">
                                    <label class="layui-form-label">加入时间:</label>
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
        <div class="layui-col-sm6 layui-col-md3" v-for="item in badge" v-cloak="">
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
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">会员统计</div>
                <div class="layui-card-body layui-row">
                    <div class="layui-col-md12">
                        <div class="layui-btn-container" ref="echarts_list" style="height:400px"></div>
                    </div>
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
                dataList: [
                    {name: '全部', value: ''},
                    {name: '今天', value: 'today'},
                    {name: '本周', value: 'week'},
                    {name: '本月', value: 'month'},
                    {name: '本季度', value: 'quarter'},
                    {name: '本年', value: 'year'},
                ],
                is_promoter:[
                    {name:'全部',value:''},
                    {name:'普通用户',value:0},
                    {name:'推广员',value:1},
                ],
                is_status:[
                    {name:'全部',value:''},
                    {name:'正常',value:1},
                    {name:'锁定',value:0},
                ],
                status:'',
                Promoter:'',
                data: '',
                myChart: {},
                showtime: false,
            },
            methods:{
                getBadgeList:function(){
                    var that=this;
                    layList.baseGet(layList.U({a:'getBadgeList',q:{status:this.status,is_promoter:this.Promoter,data:this.data}}),function (rem) {
                        that.badge=rem.data;
                    });
                },
                getUserChart:function(){
                    var that=this;
                    layList.baseGet(layList.U({a:'getUserChartList',q:{status:this.status,is_promoter:this.Promoter,data:this.data}}),function (rem) {
                        var option=that.setoption(rem.data.seriesdata,rem.data.xdata,rem.data.Zoom);
                        console.log(option);
                        that.myChart.list.setOption(option);
                    });
                },
                setoption:function(seriesdata,xdata,Zoom,title,type){
                    var _type=type || 'line';
                    var _title=title || '新增会员曲线图';
                    switch (_type){
                        case 'line':
                            this.option={
                                title: {text:_title,x:'center'},
                                xAxis : [{type : 'category', data :xdata,}],
                                yAxis : [{type : 'value'}],
                                series:[{type:_type,data:seriesdata,markPoint :{
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
                            break;
                        case 'pic':
                            this.option={
                                title: {text:_title,left:'center'},
                                legend: {data:xdata,bottom:10,left:'center'},
                                tooltip: {trigger: 'item'},
                                series:[{ type: 'pie', radius : '65%', center: ['50%', '50%'], selectedMode: 'single',data:seriesdata}],
                            };
                            break;
                    }
                    if(Zoom!=''){
                        this.option.dataZoom=[{startValue:Zoom},{type:'inside'}];
                    }
                    return  this.option;
                },
                search:function(){
                    this.getBadgeList();
                    this.getUserChart();
                },
                refresh:function () {
                    this.data='';
                    this.getBadgeList();
                    this.getUserChart();
                },
                setChart:function(name,myChartname){
                    this.myChart[myChartname]=echarts.init(name);
                },
                setStatus:function (item) {
                    this.status=item.value;
                },
                setPromoter:function (item) {
                    this.Promoter=item.value;
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
            },
            mounted:function () {
                this.setChart(this.$refs.echarts_list,'list');
                this.getBadgeList();
                this.getUserChart();
                var that=this;
                layList.laydate.render({
                    elem:this.$refs.date_time,
                    trigger:'click',
                    eventElem:this.$refs.time,
                    range:true,
                    change:function (value){
                        that.data=value;
                    }
                });
            }
        })
    })
  </script>
{/block}