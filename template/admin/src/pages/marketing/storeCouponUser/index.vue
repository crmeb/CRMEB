<template>
    <div>
        <div class="i-layout-page-header">
          <div class="i-layout-page-header">
            <span class="ivu-page-header-title">{{$route.meta.title}}</span>
          </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <Form ref="tableFrom" :model="tableFrom"  :label-width="labelWidth" :label-position="labelPosition" @submit.native.prevent>
                <Row type="flex" :gutter="24">
                    <Col v-bind="grid">
                        <FormItem label="是否有效：" >
                            <Select  placeholder="请选择" clearable v-model="tableFrom.status" @on-change="userSearchs">
                                <Option value="1">已使用</Option>
                                <Option value="0">未使用</Option>
                                <Option value="2">已过期</Option>
                            </Select>
                        </FormItem>
                    </Col>
                    <Col v-bind="grid">
                        <FormItem label="领取人：" label-for="nickname">
                            <Input  placeholder="请输入领取人" v-model="tableFrom.nickname" clearable/>
                        </FormItem>
                    </Col>
                    <Col v-bind="grid">
                        <FormItem label="优惠券搜索：" label-for="coupon_title">
                            <Input search enter-button placeholder="请输入优惠券名称" v-model="tableFrom.coupon_title" @on-search="userSearchs"/>
                        </FormItem>
                    </Col>
                </Row>
            </Form>
            <Table :columns="columns1" :data="tableList">
                <template slot-scope="{ row, index }" slot="is_fail">
                    <Icon type="md-checkmark" v-if="row.is_fail === 0" color="#0092DC" size="14"/>
                    <Icon type="md-close" v-else color="#ed5565" size="14"/>
                </template>
                <template slot-scope="{ row, index }" slot="add_time">
                    <span> {{row.add_time | formatDate}}</span>
                </template>
                <template slot-scope="{ row, index }" slot="end_time">
                    <span> {{row.end_time | formatDate}}</span>
                </template>
            </Table>
            <div class="acea-row row-right page">
                <Page :total="total" :current="tableFrom.page" show-elevator show-total @on-change="pageChange"
                      :page-size="tableFrom.limit"/>
            </div>
        </Card>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    import { userListApi } from '@/api/marketing'
    import { formatDate } from '@/utils/validate'
    export default {
        name: 'storeCouponUser',
        filters: {
            formatDate (time) {
                if (time !== 0) {
                    let date = new Date(time * 1000)
                    return formatDate(date, 'yyyy-MM-dd hh:mm')
                }
            }
        },
        data () {
            return {
                columns1: [
                    {
                        title: 'ID',
                        key: 'id',
                        width: 80
                    },
                    {
                        title: '优惠券名称',
                        key: 'coupon_title',
                        minWidth: 150
                    },
                    {
                        title: '领取人',
                        key: 'nickname',
                        minWidth: 130
                    },
                    {
                        title: '面值',
                        key: 'coupon_price',
                        minWidth: 100
                    },
                    {
                        title: '最低消费额',
                        key: 'use_min_price',
                        minWidth: 120
                    },
                    {
                        title: '开始使用时间',
                        slot: 'add_time',
                        minWidth: 150
                    },
                    {
                        title: '结束使用时间',
                        slot: 'end_time',
                        minWidth: 150
                    },
                    {
                        title: '获取方式',
                        key: 'type',
                        minWidth: 150
                    },
                    {
                        title: '是否可用',
                        slot: 'is_fail',
                        minWidth: 120
                    },
                    {
                        title: '状态',
                        key: 'status',
                        minWidth: 170
                    }
                ],
                tableList: [],
                grid: {
                    xl: 7,
                    lg: 7,
                    md: 12,
                    sm: 24,
                    xs: 24
                },
                tableFrom: {
                    status: '',
                    coupon_title: '',
                    nickname: '',
                    page: 1,
                    limit: 15
                },
                total: 0
            }
        },
        computed: {
            ...mapState('media', [
                'isMobile'
            ]),
            labelWidth () {
                return this.isMobile ? undefined : 90
            },
            labelPosition () {
                return this.isMobile ? 'top' : 'right'
            }
        },
        created () {
            this.getList()
        },
        methods: {
            // 列表
            getList () {
                this.loading = true
                this.tableFrom.status = this.tableFrom.status || ''
                userListApi(this.tableFrom).then(async res => {
                    let data = res.data
                    this.tableList = data.list
                    this.total = res.data.count
                    this.loading = false
                }).catch(res => {
                    this.loading = false
                    this.$Message.error(res.msg)
                })
            },
            pageChange (index) {
                this.tableFrom.page = index
                this.getList()
            },
            // 表格搜索
            userSearchs () {
                this.tableFrom.page = 1
                this.getList()
            }
        }
    }
</script>

<style scoped>

</style>
