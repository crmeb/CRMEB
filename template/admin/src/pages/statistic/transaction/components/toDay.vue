<template>
    <Card :bordered="false" dis-hover class="ivu-mt">
        <Row>
            <Col v-if="statisticsData" class="br" v-bind="grid">
                <div>
                    <div class="title mb15">今日订单金额</div>
                    <div class="price">￥<i>{{ statisticsData.left.series[0].money }}</i></div>
                </div>
                <echarts-new v-if="optionData" key="1" ref="visitChart" height="100%" width="100%" :option-data="optionData" :styles="style" />
            </Col>
            <Col v-if="statisticsData" v-bind="grid">
                <div class="pl25">
                    <div class="toDay">
                        <span class="toDay-title spBlock mb10">今日订单数</span>
                        <span class="toDay-number spBlock mb10">{{ statisticsData.right.today.series[0].now_money }}</span>
                        <span class="toDay-time spBlock">昨日：{{statisticsData.right.today.series[0].last_money}}</span>
                        <span class="toDay-time spBlock">日环比：
                            <i class="content-is" :class="Number(statisticsData.right.today.series[0].rate)>=0?'up':'down'">{{ Math.floor(statisticsData.right.today.series[0].rate) }}%</i>
                            <Icon :color="Number(statisticsData.right.today.series[0].rate)>=0?'#F5222D':'#39C15B'" :type="Number(statisticsData.right.today.series[0].rate)>=0?'md-arrow-dropup':'md-arrow-dropdown'" />
                        </span>
                        <echarts-new v-if="optionTodatOrder" key="2" ref="visitChart" height="100%" width="100%" :option-data="optionTodatOrder" :styles="styleToday" />
                        <span class="toDay-title spBlock mb10">本月订单数</span>
                        <span class="toDay-number spBlock mb10">{{ statisticsData.right.month[0].now_money }}</span>
                        <span class="toDay-time spBlock">上月：{{statisticsData.right.month[0].last_money}}</span>
                        <span class="toDay-time spBlock">月环比：
                            <i class="content-is" :class="Number(statisticsData.right.month[0].rate)>=0?'up':'down'">{{ Math.floor(statisticsData.right.month[0].rate) }}%</i>
                           <Icon :color="Number(statisticsData.right.month[0].rate)>=0?'#F5222D':'#39C15B'" :type="Number(statisticsData.right.month[0].rate)>=0?'md-arrow-dropup':'md-arrow-dropdown'" />
                        </span>
                    </div>
                    <div class="toDay" style="border: none;">
                        <span class="toDay-title spBlock mb10">今日支付人数</span>
                        <span class="toDay-number spBlock mb10">{{ statisticsData.right.today.series[1].now_money }}</span>
                        <span class="toDay-time spBlock">昨日：{{statisticsData.right.today.series[1].last_money}}</span>
                        <span class="toDay-time spBlock">日环比：
                            <i class="content-is" :class="Number(statisticsData.right.today.series[1].rate)>=0?'up':'down'">{{ Math.floor(statisticsData.right.today.series[1].rate) }}%</i>
                            <Icon :color="Number(statisticsData.right.today.series[1].rate)>=0?'#F5222D':'#39C15B'" :type="Number(statisticsData.right.today.series[1].rate)>=0?'md-arrow-dropup':'md-arrow-dropdown'" />
                        </span>
                        <echarts-new v-if="optionOrderUser" key="3" ref="visitChart" height="100%" width="100%" :option-data="optionOrderUser" :styles="styleToday" />
                        <span class="toDay-title spBlock mb10">本月支付人数</span>
                        <span class="toDay-number spBlock mb10">{{ statisticsData.right.month[1].now_money }}</span>
                        <span class="toDay-time spBlock">上月：{{statisticsData.right.month[1].last_money}}</span>
                        <span class="toDay-time spBlock">月环比：
                            <i class="content-is" :class="Number(statisticsData.right.month[1].rate)>=0?'up':'down'">{{ Math.floor(statisticsData.right.month[1].rate) }}%</i>
                           <Icon :color="Number(statisticsData.right.month[1].rate)>=0?'#F5222D':'#39C15B'" :type="Number(statisticsData.right.month[1].rate)>=0?'md-arrow-dropup':'md-arrow-dropdown'" />
                        </span>
                    </div>
                </div>
            </Col>
        </Row>
    </Card>
</template>

<script>
    import { statisticTopTradeApi } from '@/api/statistic'
    import echartsNew from '@/components/echartsNew/index'
    import echarts from 'echarts'
    export default {
        name: 'ToDay',
        components: {
            echartsNew
        },
        data() {
            return {
                style: {
                    height: '200px'
                },
                styleToday: {
                    height: '130px'
                },
                legendData: ['今天', '昨天'],
                seriesData: [],
                timer: [],
                grid: {
                    xl: 12,
                    lg: 12,
                    md: 12,
                    sm: 24,
                    xs: 24
                },
                statisticsData: {},
                optionTodatOrder: {}, // 今日订单数
                orderData: {},
                orderUserData: {},
                optionData: {},  // 今日交易数据
                listLoading: false,
                optionDataOrder: {},
                optionOrderUser: {} // 今日支付人数
            }
        },
        beforeDestroy() {
            if (this.visitChart) {
                this.visitChart.dispose()
                this.visitChart = null
            }
        },
        mounted() {
            this.getList()
            // this.getOrder()
            // this.getOrderUser()
        },
        methods: {
            getList() {
                this.listLoading = true
                statisticTopTradeApi({time: 'today'}).then(res => {
                    // 今日交易数据
                    this.statisticsData = res.data;
                    const leftOrder = res.data.left;
                    const leftToday = [];
                    const leftLegendData = leftOrder.x;
                    Object.keys(leftOrder.series[0].value).forEach((key) => {
                        leftToday.push(Number(leftOrder.series[0].value[key]))
                    })
                    const leftYesterday = [];
                    Object.keys(leftOrder.series[1].value).forEach((key) => {
                        leftYesterday.push(Number(leftOrder.series[1].value[key]))
                    })
                    const seriesData = [
                        {
                            name: '今天',
                            type: 'line',
                            areaStyle: {
                                normal: {
                                    color: new echarts.graphic.LinearGradient(
                                        0, 0, 0, 1,
                                        [{
                                            offset: 0,
                                            color: '#5B8FF9'
                                        },
                                            {
                                                offset: 0.5,
                                                color: '#fff'
                                            },
                                            {
                                                offset: 1,
                                                color: '#fff'
                                            }
                                        ]
                                    )
                                }
                            },
                            itemStyle: {
                                normal: {
                                    color: '#5B8FF9',
                                    lineStyle: {
                                        color: '#5B8FF9'
                                    }
                                }
                            },
                            data: leftToday,
                            symbol: "none",
                            smooth: true
                        },
                        {
                            name: '昨天',
                            type: 'line',
                            areaStyle: {
                                normal: {
                                    color: new echarts.graphic.LinearGradient(
                                        0, 0, 0, 1,
                                        [{
                                            offset: 0,
                                            color: '#BFBFBF'
                                        },
                                            {
                                                offset: 0.5,
                                                color: '#fff'
                                            },
                                            {
                                                offset: 1,
                                                color: '#fff'
                                            }
                                        ]
                                    )
                                }
                            },
                            itemStyle: {
                                normal: {
                                    color: '#D9D9D9',
                                    lineStyle: {
                                        color: '#D9D9D9'
                                    }
                                }
                            },
                            data: leftYesterday,
                            symbol: "none",
                            smooth: true
                        }
                    ]
                    this.optionData = {
                        tooltip: {
                            trigger: 'axis',
                        },
                        legend: {
                            x: '1px',
                            y: '10px',
                            data: ['今天', '昨天']
                        },
                        grid: {
                            left: '0%',
                            right: '4%',
                            bottom: '3%',
                            containLabel: true
                        },
                        xAxis: [{
                            boundaryGap: false,
                            data: leftLegendData,
                            axisLine: {
                                show: false
                            },
                            show: false
                        }],
                        yAxis: {
                            show: false
                        },
                        series: seriesData
                    }

                    // 今日订单数
                    const rightOrder = res.data.right;
                    const rightLegendData = rightOrder.today.x;
                    const rightTodayOrder = [];
                    Object.keys(rightOrder.today.series[0].value).forEach((key) => {
                        rightTodayOrder.push(Number(rightOrder.today.series[0].value[key]))
                    })
                    const rightSeriesDataOrder = [{
                        name: '今天',
                        type: 'line',
                        areaStyle: {
                            normal: {
                                color: new echarts.graphic.LinearGradient(
                                    0, 0, 0, 1,
                                    [{
                                        offset: 0,
                                        color: '#5B8FF9'
                                    },
                                        {
                                            offset: 0.5,
                                            color: '#fff'
                                        },
                                        {
                                            offset: 1,
                                            color: '#fff'
                                        }
                                    ]
                                )
                            }
                        },
                        itemStyle: {
                            normal: {
                                color: '#5B8FF9',
                                lineStyle: {
                                    color: '#5B8FF9'
                                }
                            }
                        },
                        data: rightTodayOrder,
                        symbol: "none",
                        smooth: true
                    }]
                    this.optionTodatOrder = {
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'cross',
                                label: {
                                    backgroundColor: '#6a7985'
                                }
                            }
                        },
                        legend: {
                            x: '1px',
                            y: '10px',
                            data: ['今天']
                        },
                        grid: {
                            left: '0%',
                            right: '4%',
                            bottom: '3%',
                            containLabel: true
                        },
                        axisLine: {
                            show: false
                        },
                        xAxis: [{
                            type: 'category',
                            boundaryGap: false,
                            data: rightLegendData,
                            axisLine: {
                                show: false
                            },
                            show: false
                        }],
                        yAxis: {
                            show: false
                        },
                        series: rightSeriesDataOrder
                    }

                    // 今日支付人数
                    const rightTodayPay = [];
                    Object.keys(rightOrder.today.series[1].value).forEach((key) => {
                        rightTodayPay.push(Number(rightOrder.today.series[1].value[key]))
                    })
                    const seriesDataPay = [{
                        name: '今天',
                        type: 'line',
                        areaStyle: {
                            normal: {
                                color: new echarts.graphic.LinearGradient(
                                    0, 0, 0, 1,
                                    [{
                                        offset: 0,
                                        color: '#5B8FF9'
                                    },
                                        {
                                            offset: 0.5,
                                            color: '#fff'
                                        },
                                        {
                                            offset: 1,
                                            color: '#fff'
                                        }
                                    ]
                                )
                            }
                        },
                        itemStyle: {
                            normal: {
                                color: '#5B8FF9',
                                lineStyle: {
                                    color: '#5B8FF9'
                                }
                            }
                        },
                        data: rightTodayPay,
                        symbol: "none",
                        smooth: true
                    }]
                    this.optionOrderUser = {
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'cross',
                                label: {
                                    backgroundColor: '#6a7985'
                                }
                            }
                        },
                        legend: {
                            x: '1px',
                            y: '10px',
                            data: ['今天']
                        },
                        grid: {
                            left: '0%',
                            right: '4%',
                            bottom: '3%',
                            containLabel: true
                        },
                        axisLine: {
                            show: false
                        },
                        xAxis: [{
                            type: 'category',
                            boundaryGap: false,
                            data: leftLegendData,
                            axisLine: {
                                show: false
                            },
                            show: false
                        }],
                        yAxis: {
                            show: false
                        },
                        series: seriesDataPay
                    }
                }).catch(res => {
                    this.listLoading = false
                    this.$Message.error(res.msg)
                })
            },
            getOrder() {

            },
            getOrderUser() {

            }
        }
    }
</script>

<style lang="less" scoped>
    .up,
    .el-icon-caret-top,
    .content-is {
        color: #F5222D;
        font-size: 12px;
        opacity: 1 !important;

        &.down {
            color: #39C15B;
        }
    }

    .down,
    .el-icon-caret-bottom .content-is {
        font-size: 12px;
    }

    .el-icon-caret-bottom {
        color: #39C15B;
    }

    .br {
        border-right: 1px solid rgba(0, 0, 0, 0.1);
    }

    .toDay {
        width: 49%;
        display: inline-block;

        &-title {
            font-size: 14px;
        }

        &-number {
            font-size: 20px;
        }

        &-time {
            font-size: 12px;
            color: #8C8C8C;
            margin-bottom: 5px;
        }
    }

    .title {
        font-size: 16px;
        color: #000000;
        font-weight: 500;
    }

    .price {
        i {
            font-style: normal;
            font-size: 21px;
            color: #000;
        }
    }
</style>
