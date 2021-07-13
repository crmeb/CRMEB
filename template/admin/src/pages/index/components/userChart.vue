<template>
    <div @resize="handleResize">
        <Row :gutter="24">
            <Col :xl="16" :lg="12" :md="24" :sm="24" :xs="24" class="ivu-mb dashboard-console-visit">
                <Card :bordered="false" dis-hover>
                    <div slot="title">
                        <Avatar icon="ios-pulse" size="small" style="color: #1890ff;background-color: #e6f7ff"/>
                        <span class="ivu-pl-8">用户</span>
                    </div>
                    <echarts-from ref="userChart" :echartsTitle="line" :infoList="infoList" :series="series" v-if="infoList&&series.length!==0"></echarts-from>
                </Card>
            </Col>
            <Col :xl="8" :lg="12" :md="24" :sm="24" :xs="24">
                <Card :bordered="false" dis-hover class="dashboard-console-visit">
                    <div slot="title">
                        <Avatar icon="ios-analytics" size="small" style="color: #1890ff;background-color: #e6f7ff" />
                        <span class="ivu-pl-8">购买用户统计</span>
                    </div>
                    <echarts-from ref="visitChart" :infoList="infoList" :echartsTitle="circle"></echarts-from>
                </Card>
            </Col>
        </Row>
    </div>
</template>

<script>
    import { userApi, rankApi } from '@/api/index'
    import echartsFrom from '@/components/echarts/index'
    export default {
        name: 'user-chart',
        components: { echartsFrom },
        data () {
            return {
                line: 'line',
                circle: 'circle',
                infoList: {},
                series: [],
                xData: [],
                y1Data: [],
                y2Data: [],
                lists: [],
                bing_data: [],
                bing_xdata: []
            }
        },
        methods: {
            // 统计
            getStatistics () {
                userApi().then(async res => {
                    this.infoList = res.data
                    this.series = [
                        {
                            data: res.data.series,
                            name: '人数（人）',
                            type: 'line',
                            tooltip: true,
                            smooth: true,
                            symbol: 'none',
                            areaStyle: {
                                normal: {
                                    opacity: 0.2
                                }
                            }
                        }
                    ]
                    this.bing_data = res.bing_data
                    this.bing_xdata = res.bing_xdata
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            getRank () {
                rankApi().then(async res => {
                    let data = res.data
                    this.lists = data.list
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 监听页面宽度变化，刷新表格
            handleResize () {
                if (this.infoList && this.series.length !== 0) this.$refs.userChart.handleResize()
                if (this.infoList) this.$refs.visitChart.handleResize()
            }
        },
        mounted () {
            this.getStatistics()
            this.getRank()
        },
        beforeDestroy () {
            if (this.visitChart) {
                this.visitChart.dispose()
                this.visitChart = null
            }
        }
    }
</script>

<style scoped lang="less">
    .dashboard-console-visit{
        ul{
            li{
                list-style-type: none;
                margin-top: 12px;
            }
        }
    }
    .trees-coadd{
        width: 100%;
        height: 100%;
        .scollhide{
            width: 100%;
            height: 100%;
            overflow-x: hidden;
            overflow-y: scroll;
        }
    }
    .scollhide::-webkit-scrollbar {
        display: none;
    }
    .names{
        display: inline-block;
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
        width: 84%;
        margin-bottom: -7px;
    }
</style>
