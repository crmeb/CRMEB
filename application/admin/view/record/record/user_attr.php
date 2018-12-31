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
        <div class="layui-col-sm8">
            <div class="layui-card">
                <div class="layui-card-header">地区分布</div>
                <div class="layui-card-body layui-row">
                    <div class="layui-col-md12">
                        <div class="layui-btn-container" ref="echarts_list" style="height:400px"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-sm4">
            <div class="layui-card">
                <div class="layui-card-header">性别分布</div>
                <div class="layui-card-body layui-row">
                    <div class="layui-col-md12">
                        <div class="layui-btn-container" ref="echarts_list_sex" style="height:400px"></div>
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
                sexoption:{},
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
                getEchartsData:function(){
                    var index=layList.layer.load(2,{shade: [0.3,'#fff']}),that=this;
                    layList.baseGet(layList.U({a:'getEchartsData',q:{data:this.data}}),function (rem) {
                        layList.layer.close(index);
                        var option=that.setfenbuoption(rem.data.dataList,rem.data.legdata);
                        that.myChart.list.setOption(option);
                        var excoption=that.setsexoption(rem.data.sexList,rem.data.sexlegdata);
                        that.myChart.sex.setOption(excoption);
                    },function () {
                        layList.layer.close(index);
                    });
                },
                setfenbuoption:function(seriesdata,xdata){
                    return this.option={
                        title: {text:'用户地区分布图表'},
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
                },
                setsexoption:function(seriesdata,legdata){
                    return this.sexoption={
                        title: {text:'用户性别分布图表',left:'center'},
                        tooltip: {trigger: 'item'},
                        legend: {data:legdata,bottom:10,left:'center'},
                        series:[{ type: 'pie', radius : '65%', center: ['50%', '50%'], selectedMode: 'single',data:seriesdata}],
                    };
                },
                search:function(){
                    this.getEchartsData();
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