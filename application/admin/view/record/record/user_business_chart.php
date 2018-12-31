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
                                    <label class="layui-form-label">时间范围:</label>
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
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">购物会员统计</div>
                <div class="layui-card-body layui-row">
                    <div class="layui-col-md12">
                        <div class="layui-btn-container" ref="echarts_list" style="height:300px"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">会员访问统计</div>
                <div class="layui-card-body layui-row">
                    <div class="layui-col-md12">
                        <div class="layui-btn-container" ref="echarts_user" style="height:300px"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">购物会员数量饼状图</div>
                <div class="layui-card-body layui-row">
                    <div class="layui-col-md12">
                        <div class="layui-btn-container" ref="echarts_shop" style="height:300px"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md6">
                <div class="layui-card">
                    <div class="layui-card-header">会员积分排行榜 <span class="layui-badge layui-bg-orange">TOP{$limit}</span></div>
                    <div class="layui-card-body layui-row">
                        <table class="layui-table">
                            <thead>
                                <tr>
                                    <th>排名</th>
                                    <th>昵称/手机</th>
                                    <th>会员积分</th>
                                    <th>注册时间</th>
                                </tr>
                            </thead>
                            <tbody>
                                {volist name='integralList' id='vo'}
                                <tr>
                                    <td>{$key+1}</td>
                                    <td>{$vo.nickname}/{$vo.phone}</td>
                                    <td>{$vo.integral}</td>
                                    <td>{$vo.add_time}</td>
                                </tr>
                                {/volist}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="layui-col-md6">
                <div class="layui-card">
                    <div class="layui-card-header">会员余额排行榜 <span class="layui-badge layui-bg-orange">TOP{$limit}</span></div>
                    <div class="layui-card-body layui-row">
                        <table class="layui-table">
                            <thead>
                            <tr>
                                <th>排名</th>
                                <th>昵称/手机</th>
                                <th>会员余额</th>
                                <th>注册时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            {volist name='moneyList' id='vo'}
                            <tr>
                                <td>{$key+1}</td>
                                <td>{$vo.nickname}/{$vo.phone}</td>
                                <td>{$vo.now_money}</td>
                                <td>{$vo.add_time}</td>
                            </tr>
                            {/volist}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md6">
                <div class="layui-card">
                    <div class="layui-card-header">购物笔数排行榜 <span class="layui-badge layui-bg-orange">TOP{$limit}</span></div>
                    <div class="layui-card-body layui-row">
                        <table class="layui-table">
                            <thead>
                            <tr>
                                <th>排名</th>
                                <th>昵称/手机</th>
                                <th>购物次数</th>
                                <th>注册时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            {volist name='shopcountList' id='vo'}
                            <tr>
                                <td>{$key+1}</td>
                                <td>{$vo.nickname}/{$vo.phone}</td>
                                <td>{$vo.sum_count}</td>
                                <td>{$vo.add_time}</td>
                            </tr>
                            {/volist}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="layui-col-md6">
                <div class="layui-card">
                    <div class="layui-card-header">	购物金额 <span class="layui-badge layui-bg-orange">TOP{$limit}</span></div>
                    <div class="layui-card-body layui-row">
                        <table class="layui-table">
                            <thead>
                            <tr>
                                <th>排名</th>
                                <th>昵称/手机</th>
                                <th>购物金额</th>
                                <th>注册时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            {volist name='orderList' id='vo'}
                            <tr>
                                <td>{$key+1}</td>
                                <td>{$vo.nickname}/{$vo.phone}</td>
                                <td>{$vo.sum_price}</td>
                                <td>{$vo.add_time}</td>
                            </tr>
                            {/volist}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md6">
                <div class="layui-card">
                    <div class="layui-card-header">上月消费排行榜 <span class="layui-badge layui-bg-orange">TOP{$limit}</span></div>
                    <div class="layui-card-body layui-row">
                        <table class="layui-table">
                            <thead>
                                <tr>
                                    <th>排名</th>
                                    <th>昵称/手机</th>
                                    <th>消费金额</th>
                                    <th>注册时间</th>
                                </tr>
                            </thead>
                            <tbody>
                                {volist name='lastorderList' id='vo'}
                                <tr>
                                    <td>{$key+1}</td>
                                    <td>{$vo.nickname}/{$vo.phone}</td>
                                    <td>{$vo.sum_price}</td>
                                    <td>{$vo.add_time}</td>
                                </tr>
                                {/volist}
                            </tbody>
                        </table>
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
                    {name: '本季度',value:'quarter'},
                    {name: '本年', value: 'year'},
                ],
                status:'',
                Promoter:'',
                data: '',
                myChart: {},
                showtime: false,
            },
            methods:{
                getUserBusinesHeade:function(){
                    var that=this;
                    layList.baseGet(layList.U({a:'getUserBusinesHeade',q:{data:this.data}}),function (rem) {
                        that.badge=rem.data;
                    });
                },
                getUserBusinessChart:function(){
                    var that=this;
                    layList.baseGet(layList.U({a:'getUserBusinessChart',q:{data:this.data}}),function (rem) {
                        var user=that.setoption(rem.data.seriesdata,rem.data.xdata,null,null,null,rem.data.zoom);
                        that.myChart.list.setOption(user);
                        var visit=that.setoption(rem.data.visit_data,rem.data.visit_xdata,null,'会员访问人数统计曲线图','line',rem.data.visit_zoom);
                        that.myChart.user.setOption(visit);
                        var shop=that.setoption(rem.data.shop_data,rem.data.shop_xdata,null,'多次购物会员数量饼状图','pic','');
                        that.myChart.shop.setOption(shop);
                    });
                },
                setoption:function(seriesdata,xdata,legend,title,type,Zoom){
                    var _type=type || 'line';
                    var _title=title || '购物会员人数趋势图';
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
                            if(legend!=undefined || legend!=null){
                                this.option.legend={left:'center',data:legend};
                            }
                            break;
                        case 'lines':
                            this.option={
                                title: {text:_title,x:'center'},
                                xAxis : [{type : 'category', data :xdata,}],
                                yAxis : [{type : 'value'}],
                                series:seriesdata,
                            };
                            if(legend!=undefined || legend!=null){
                                this.option.legend={left:'left',data:legend};
                            }
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
                    this.getUserBusinesHeade();
                    this.getUserBusinessChart();
                },
                refresh:function () {
                    this.data='';
                    this.getUserBusinesHeade();
                    this.getUserBusinessChart();
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
                this.setChart(this.$refs.echarts_user,'user');
                this.setChart(this.$refs.echarts_shop,'shop');
                this.getUserBusinesHeade();
                this.getUserBusinessChart();
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