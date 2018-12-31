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
        <div class="layui-col-sm12">
            <div class="layui-card">
                <div class="layui-card-header">发放积分:</div>
                <div class="layui-card-body layui-row">
                    <div class="layui-col-md12">
                        <div class="layui-btn-container" ref="echarts_list" id="echarts_list" style="height:400px"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-sm12">
            <div class="layui-card">
                <div class="layui-card-header">使用积分:</div>
                <div class="layui-card-body layui-row">
                    <div class="layui-col-md12">
                        <div class="layui-btn-container" ref="echarts_use" id="echarts_list" style="height:400px"></div>
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
                data: '',
                title: '全部商品',
                count: 0,
                myChart: {},
                showtime: false,
                limit:20,
            },
            watch: {

            },
            methods: {
                getScoreBadgeList:function(){
                    var that=this;
                    layList.baseGet(layList.U({a:'getScoreBadgeList',q:{data:this.data}}),function (rem) {
                        that.badge=rem.data;
                    });
                },
                getScoreCurve:function(){
                    var that=this;
                    layList.baseGet(layList.U({a:'getScoreCurve',q:{data:this.data,limit:this.limit}}),function (rem) {
                        var dataCurve=that.setoption(rem.data.seriesdata,rem.data.date,rem.data.zoom,true);
                        that.myChart.list.setOption(dataCurve);
                        var dataCurve=that.setoption(rem.data.deduction_seriesdata,rem.data.deduction_date,rem.data.deduction_zoom);
                        that.myChart.use.setOption(dataCurve);
                    });
                },
                setoption:function(seriesdata,xdata,Zoom,isUse){
                    var text=isUse ? '发放积分趋势图':'使用积分趋势图';
                    this.option={
                        title: {text:text,x:'center'},
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
                    this.getScoreBadgeList();
                    this.getScoreCurve();
                },
                search:function(){
                    this.getScoreBadgeList();
                    this.getScoreCurve();
                },
                setChart:function(name,myChartname){
                    this.myChart[myChartname]=echarts.init(name);
                },
            },
            mounted:function () {
                this.setChart(this.$refs.echarts_list,'list');
                this.setChart(this.$refs.echarts_use,'use');
                this.getScoreBadgeList();
                this.getScoreCurve();
                layList.laydate.render({
                    elem:this.$refs.date_time,
                    trigger:'click',
                    eventElem:this.$refs.time,
                    range:true,
                });
            }
        })
    })
</script>
{/block}