<template>
    <div style="width: 100%">
        <Modal v-model="modals" scrollable footer-hide closable title="用户详情" :mask-closable="false"
               width="700">
            <Spin size="large" fix v-if="spinShow"></Spin>
            <div class="">
                <div class="dashboard-workplace-header-tip">
                    <div class="dashboard-workplace-header-tip-desc">
                        <span class="dashboard-workplace-header-tip-desc-sp">姓名：{{detailsData.nickname}}</span>
                        <span class="dashboard-workplace-header-tip-desc-sp">上级推广人：{{detailsData.spread_name?detailsData.spread_name:'无'}}</span>
                        <span class="dashboard-workplace-header-tip-desc-sp">佣金总收入：{{detailsData.number}}</span>
                        <span class="dashboard-workplace-header-tip-desc-sp">用户余额：{{detailsData.now_money}}</span>
                        <span class="dashboard-workplace-header-tip-desc-sp">创建时间：{{detailsData.add_time}}</span>
                    </div>
                </div>
            </div>
            <Divider dashed/>
            <Form ref="formValidate" :label-width="75" :label-position="labelPosition" class="tabform" @submit.native.prevent>
                <Row :gutter="24" type="flex">
<!--                    <Col span="8">-->
<!--                        <FormItem label="订单号/昵称：">-->
<!--                            <Input enter-button placeholder="请输入" element-id="name" v-model="formValidate.nickname"-->
<!--                                   clearable/>-->
<!--                        </FormItem>-->
<!--                    </Col>-->
                    <Col span="12">
                        <FormItem label="时间范围：" class="tab_data">
                            <DatePicker :editable="false" style="width: 100%" @on-change="onchangeTime" format="yyyy-MM-dd" type="daterange" placement="bottom-end" placeholder="请选择时间范围"></DatePicker>
                        </FormItem>
                    </Col>
                    <Col span="4">
                            <Button type="primary" icon="ios-search" @click="userSearchs">搜索</Button>
                    </Col>
                </Row>
            </Form>
            <Table :columns="columns" :data="tabList"
                   ref="table"
                   :loading="loading"
                   no-userFrom-text="暂无数据"
                   no-filtered-userFrom-text="暂无筛选结果"
                   class="table"
            ></Table>
            <div class="acea-row row-right page">
                <Page :total="total" :current="formValidate.page" show-elevator show-total @on-change="pageChange"
                      :page-size="formValidate.limit"/>
            </div>
        </Modal>
    </div>
</template>

<script>
    import { commissionDetailApi, extractlistApi } from '@/api/finance'
    import { mapState } from 'vuex'
    export default {
        name: 'commissionDetails',
        data () {
            return {
                modals: false,
                spinShow: false,
                detailsData: {},
                Ids: 0,
                loading: false,
                formValidate: {
                    nickname: '',
                    start_time: '',
                    end_time: '',
                    page: 1, // 当前页
                    limit: 20 // 每页显示条数
                },
                total: 0,
                columns: [
                    // {
                    //     title:'昵称',
                    //     key:'nickname'
                    // },
                    {
                        title: '佣金金额',
                        key: 'number',
                        minWidth:80
                    },
                    {
                        title: '获得时间',
                        key: '_add_time',
                        minWidth:150
                    },
                    {
                        title: '备注',
                        key: 'mark',
                        minWidth:330
                    }
                ],
                tabList: []

            }
        },
        computed: {
            ...mapState('media', [
                'isMobile'
            ]),
            labelWidth () {
                return this.isMobile ? undefined : 100
            },
            labelPosition () {
                return this.isMobile ? 'top' : 'left'
            }
        },
        mounted () {
            if (this.Ids) {
                this.getList()
            }
        },
        methods: {
            // 时间
            onchangeTime (e) {
                this.formValidate.start_time = e[0]
                this.formValidate.end_time = e[1]
            },
            // 详情
            getDetails (id) {
                this.Ids = id
                this.spinShow = true
                commissionDetailApi(id).then(async res => {
                    if (res.status === 200) {
                        let data = res.data
                        this.detailsData = data.user_info
                        this.spinShow = false
                    } else {
                        this.spinShow = false
                        this.$Message.error(res.msg)
                    }
                }).catch(res => {
                    this.spinShow = false
                    this.$Message.error(res.msg)
                })
            },
            // 列表
            getList () {
                this.loading = true
                extractlistApi(this.Ids, this.formValidate).then(async res => {
                    let data = res.data
                    this.tabList = data.data
                    this.total = data.count
                    this.loading = false
                }).catch(res => {
                    this.loading = false
                    this.$Message.error(res.msg)
                })
            },
            pageChange (index) {
                this.formValidate.page = index
                this.getList()
            },
            // 搜索
            userSearchs () {
                this.formValidate.page = 1
                this.getList()
            }
        }
    }
</script>

<style lang="less">
    .table {
        .ivu-table-default{
            overflow-y: auto;
            max-height: 350px;
        }
    }
    .dashboard-workplace {
        &-header {
            &-avatar {
                width: 64px;
                height: 64px;
                border-radius: 50%;
                margin-right: 16px;
                font-weight: 600;
            }

            &-tip {
                width: 100%;
                display: inline-block;
                vertical-align: middle;

                &-title {
                    font-size: 13px;
                    color: #000000;
                    margin-bottom: 12px;
                }

                &-desc {
                    &-sp {
                        width: 33.33%;
                        color: #17233D;
                        font-size: 12px;
                        display: inline-block;
                        padding-bottom: 10px;
                    }
                }
            }

            &-extra {
                .ivu-col {
                    p {
                        text-align: right;
                    }

                    p:first-child {
                        span:first-child {
                            margin-right: 4px;
                        }

                        span:last-child {
                            color: #808695;
                        }
                    }

                    p:last-child {
                        font-size: 22px;
                    }
                }
            }
        }
    }
</style>
