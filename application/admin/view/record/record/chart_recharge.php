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
        <div class="layui-col-sm12">
            <div class="layui-card">
                <div class="layui-card-header">充值笔数</div>
                <div class="layui-card-body layui-row">
                    <div class="layui-col-md12">
                        <div class="layui-btn-container" ref="echarts_list" style="height:400px"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-sm12">
            <div class="layui-card">
                <div class="layui-card-header">充值金额</div>
                <div class="layui-card-body layui-row">
                    <div class="layui-col-md12">
                        <div class="layui-btn-container" ref="echarts_price" style="height:400px"></div>
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
                showtime: false,
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
                getEchartsRecharge:function(){
                    var index=layList.layer.load(2,{shade: [0.3,'#fff']}),that=this;
                    layList.baseGet(layList.U({a:'getEchartsRecharge',q:{data:this.data}}),function (rem) {
                        layList.layer.close(index);
                        var option=that.setfenbuoption(rem.data.seriesdata,rem.data.xdata,rem.data.zoom);
                        that.myChart.list.setOption(option);
                        var excoption=that.setsexoption(rem.data.data,rem.data.xdata,rem.data.zoom);
                        that.myChart.price.setOption(excoption);
                    },function () {
                        layList.layer.close(index);
                    });
                },
                setfenbuoption:function(seriesdata,xdata,dataZoom){
                    this.option={
                        title: {text:'充值金额柱状图',left:'center'},
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
                    return this.option;
                },
                setsexoption:function(seriesdata,xdata,dataZoom){
                    this.option={
                        title: {text:'充值笔数曲线图',left:'center'},
                        yAxis:{type:'value'},
                        xAxis:{type:'category',data:xdata},
                        series:[{data:seriesdata,type:'line'}],
                    };
                    if(dataZoom!=''){
                        this.option.dataZoom=[{startValue:dataZoom},{type:'inside'}];
                    }
                    return this.option;
                },
                search:function(){
                    this.getEchartsRecharge();
                },
                refresh:function () {
                    this.data='';
                    this.getEchartsRecharge();
                },
                setChart:function(name,myChartname){
                    this.myChart[myChartname]=echarts.init(name);
                },
            },
            mounted:function () {
                this.setChart(this.$refs.echarts_list,'list');
                this.setChart(this.$refs.echarts_price,'price');
                this.getEchartsRecharge();
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