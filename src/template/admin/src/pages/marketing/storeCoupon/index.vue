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
                        <FormItem label="是否有效：" label-for="status">
                            <Select v-model="tableFrom.status" placeholder="请选择" clearable  element-id="status" @on-change="userSearchs">
                                <Option value="1">有效</Option>
                                <Option value="0">无效</Option>
                            </Select>
                        </FormItem>
                    </Col>
                    <Col v-bind="grid">
                        <FormItem label="优惠券名称："  label-for="title">
                            <Input search enter-button  v-model="tableFrom.title" placeholder="请输入优惠券名称" @on-search="userSearchs"/>
                        </FormItem>
                    </Col>
                </Row>
                <Row type="flex">
                    <Col v-bind="grid">
                        <Button v-auth="['admin-marketing-store_coupon-add']" type="primary"  icon="md-add" @click="add">添加优惠券</Button>
                    </Col>
                </Row>
            </Form>
            <Table :columns="columns1" :data="tableList" ref="table" class="mt25"
                   :loading="loading" highlight-row
                   no-userFrom-text="暂无数据"
                   no-filtered-userFrom-text="暂无筛选结果">
                <template slot-scope="{ row, index }" slot="status">
                    <Icon type="md-checkmark" v-if="row.status === 1" color="#0092DC" size="14"/>
                    <Icon type="md-close" v-else color="#ed5565" size="14"/>
                </template>
                <template slot-scope="{ row, index }" slot="add_time">
                    <span> {{row.add_time | formatDate}}</span>
                </template>
                <template slot-scope="{ row, index }" slot="action">
                    <a @click="couponInvalid(row,'修改优惠券',index)" v-if="row.status">立即失效</a>
                    <Divider type="vertical" v-if="row.status"/>
                    <a @click="couponSend(row)" v-if="row.status" v-auth="['admin-marketing-store_coupon-push']">发布</a>
                    <Divider type="vertical" v-if="row.status"/>
                    <a @click="couponDel(row,'删除优惠券',index)">删除</a>
                </template>
            </Table>
            <div class="acea-row row-right page">
                <Page :total="total" :current="tableFrom.page" show-elevator show-total @on-change="pageChange"
                      :page-size="tableFrom.limit"/>
            </div>
        </Card>
        <!--表单编辑-->
        <edit-from :FromData="FromData" @changeType="changeType" ref="edits"></edit-from>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    import { couponListApi, couponCreateApi, couponEditeApi, couponSendApi } from '@/api/marketing'
    import editFrom from '@/components/from/from'
    import { formatDate } from '@/utils/validate'
    export default {
        name: 'storeCoupon',
        filters: {
            formatDate (time) {
                if (time !== 0) {
                    let date = new Date(time * 1000)
                    return formatDate(date, 'yyyy-MM-dd hh:mm')
                }
            }
        },
        components: { editFrom },
        data () {
            return {
                grid: {
                    xl: 7,
                    lg: 7,
                    md: 12,
                    sm: 24,
                    xs: 24
                },
                loading: false,
                columns1: [
                    {
                        title: 'ID',
                        key: 'id',
                        width: 80
                    },
                    {
                        title: '优惠券名称',
                        key: 'title',
                        minWidth: 150
                    },
                    {
                        title: '优惠券类型',
                        key: 'type',
                        minWidth: 80
                    },
                    {
                        title: '面值',
                        key: 'coupon_price',
                        minWidth: 100
                    },
                    {
                        title: '最低消费额',
                        key: 'use_min_price',
                        minWidth: 100
                    },
                    {
                        title: '有效期限(天)',
                        key: 'coupon_time',
                        minWidth: 120
                    },
                    {
                        title: '排序',
                        key: 'sort',
                        minWidth: 80
                    },
                    {
                        title: '是否有效',
                        slot: 'status',
                        minWidth: 90
                    },
                    {
                        title: '添加时间',
                        slot: 'add_time',
                        minWidth: 150
                    },
                    {
                        title: '操作',
                        slot: 'action',
                        fixed: 'right',
                        minWidth: 170
                    }
                ],
                tableFrom: {
                    status: '',
                    title: '',
                    page: 1,
                    limit: 15
                },
                tableList: [],
                total: 0,
                FromData: null
            }
        },
        created () {
            this.getList()
        },
        computed: {
            ...mapState('media', [
                'isMobile'
            ]),
            labelWidth () {
                return this.isMobile ? undefined : 90
            },
            labelPosition () {
                return this.isMobile ? 'top' : 'left'
            }
        },
        methods: {
            // 失效
            couponInvalid (row, tit, num) {
                let delfromData = {
                    title: tit,
                    num: num,
                    url: `marketing/coupon/status/${row.id}`,
                    method: 'PUT',
                    ids: ''
                }
                this.$modalSure(delfromData).then((res) => {
                    this.$Message.success(res.msg)
                    this.getList()
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 发布
            couponSend (row) {
                this.$modalForm(couponSendApi(row.id)).then(() => this.getList())
            },
            // 删除
            couponDel (row, tit, num) {
                let delfromData = {
                    title: tit,
                    num: num,
                    url: `marketing/coupon/del/${row.id}`,
                    method: 'DELETE',
                    ids: ''
                }
                this.$modalSure(delfromData).then((res) => {
                    this.$Message.success(res.msg)
                    this.tableList.splice(num, 1)
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 列表
            getList () {
                this.loading = true
                this.tableFrom.status = this.tableFrom.status || ''
                couponListApi(this.tableFrom).then(async res => {
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
            changeType (data) {
                this.type = data
            },
            // 添加
            add () {
                // this.$modalForm(couponCreateApi()).then(() => this.getList());
                this.addType(0)
            },
            addType (type) {
                couponCreateApi(type).then(async res => {
                    if (res.data.status === false) {
                        return this.$authLapse(res.data)
                    }
                    this.FromData = res.data
                    this.$refs.edits.modals = true
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 编辑
            edit (row) {
                this.$modalForm(couponEditeApi(row.id)).then(() => this.getList())
            },
            // 表格搜索
            userSearchs () {
                this.tableFrom.page = 1
                this.getList()
            },
            // 修改成功
            submitFail () {
                this.getList()
            }
        }
    }
</script>

<style scoped>
    .ivu-col:nth-of-type(1) .ivu-form-item .ivu-form-item-label{
        width: 80px !important;
    }
    .ivu-col:nth-of-type(1) .ivu-form-item .ivu-form-item-content{
        margin-left: 80px !important;
    }
</style>
