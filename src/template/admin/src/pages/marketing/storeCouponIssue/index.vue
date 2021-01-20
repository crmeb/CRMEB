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
                            <Select v-model="tableFrom.status" placeholder="请选择" clearable  @on-change="userSearchs">
                                <Option value="1">正常</Option>
                                <Option value="0">未开启</Option>
                            </Select>
                        </FormItem>
                    </Col>
                  <Col v-bind="grid">
                    <FormItem label="类型：" label-for="status">
                      <Select v-model="receive_type" placeholder="请选择" clearable  @on-change="userSearchs">
                        <Option value="all">全部</Option>
                        <Option value="1">手动领取</Option>
                        <Option value="2">新人券</Option>
                        <Option value="3">赠送券</Option>
                        <Option value="4">会员券</Option>
                      </Select>
                    </FormItem>
                  </Col>
                    <Col v-bind="grid">
                        <FormItem label="优惠券名称："  label-for="coupon_title">
                            <Input search enter-button  v-model="tableFrom.coupon_title" placeholder="请输入优惠券名称" @on-search="userSearchs"/>
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
                <template slot-scope="{ row }" slot="count">
                    <span v-if="row.is_permanent">不限量</span>
                    <div v-else>
                        <span class="fa">发布：{{row.total_count}}</span>
                        <span class="sheng">剩余：{{row.remain_count}}</span>
                    </div>
                </template>
                <template slot-scope="{ row }" slot="type">
                    <span v-if="row.type === 1">品类券</span>
                    <span v-else-if="row.type === 2">商品券</span>
                    <span v-else-if="row.type === 3">会员券</span>
                    <span v-else>通用券</span>
                </template>
                <template slot-scope="{ row }" slot="receive_type">
                    <span v-if="row.receive_type === 1">手动领取</span>
                    <span v-else-if="row.receive_type === 2">新人券</span>
                    <span v-else-if="row.receive_type === 3">赠送券</span>
                    <span v-else>会员券</span>
                </template>
                <template slot-scope="{ row }" slot="start_time">
                    <div v-if="row.start_time">
                        {{row.start_time | formatDate}} - {{row.end_time | formatDate}}
                    </div>
                    <span v-else>不限时</span>
                </template>
                <template slot-scope="{ row }" slot="start_use_time">
                    <div v-if="row.start_use_time">
                        {{row.start_use_time | formatDate}} - {{row.end_use_time | formatDate}}
                    </div>
                    <div v-else>
                        {{ row.coupon_time }}天
                    </div>
                </template>
                <template slot-scope="{ row }" slot="status">
                    <i-switch v-model="row.status" :value="row.status" :true-value="1" :false-value="0" size="large" @on-change="openChange(row)">
                        <span slot="open">开启</span>
                        <span slot="close">关闭</span>
                    </i-switch>
                </template>
                <template slot-scope="{ row, index }" slot="action">
                    <a @click="receive(row)">领取记录</a>
                    <Divider type="vertical"/>
                    <a @click="copy(row)">复制</a>
                    <Divider type="vertical"/>
                    <a @click="couponDel(row,'删除发布的优惠券',index)">删除</a>
                </template>
            </Table>
            <div class="acea-row row-right page">
                <Page :total="total" :current="tableFrom.page" show-elevator show-total @on-change="pageChange"
                      :page-size="tableFrom.limit"/>
            </div>
        </Card>
        <!-- 领取记录 -->
        <Modal v-model="modals2" scrollable  footer-hide closable title="领取记录" :mask-closable="false"  width="700">
            <Table :columns="columns2" :data="receiveList" ref="table" class="mt25"
                   :loading="loading2" highlight-row
                   no-userFrom-text="暂无数据"
                   no-filtered-userFrom-text="暂无筛选结果">
                <template slot-scope="{ row, index }" slot="avatar">
                    <div class="tabBox_img" v-viewer>
                        <img v-lazy="row.avatar">
                    </div>
                </template>
            </Table>
            <div class="acea-row row-right page">
                <Page :total="total2" show-elevator show-total @on-change="receivePageChange"
                      :page-size="receiveFrom.limit"/>
            </div>
        </Modal>
    </div>
</template>

<script>
    import { mapState } from 'vuex';
    import { releasedListApi, releasedissueLogApi, releaseStatusApi, delCouponReleased, couponStatusApi } from '@/api/marketing';
    import { formatDate } from '@/utils/validate';
    export default {
        name: 'storeCouponIssue',
        filters: {
            formatDate (time) {
                if (time !== 0) {
                    let date = new Date(time * 1000);
                    return formatDate(date, 'yyyy-MM-dd hh:mm');
                }
            }
        },
        data () {
            return {
                modals2: false,
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
                        key: 'coupon_title',
                        minWidth: 150,
                        // render (h, data) {
                        //     let row = data.row, content = '';
                        //     if (row.is_give_subscribe) {
                        //         content = '关注';
                        //     } else if (row.is_full_give) {
                        //         content = '满赠';
                        //     } else {
                        //         content = '普通'
                        //     }
                        //     return h('div', [
                        //         h('Tag', { attrs: {
                        //             color: 'blue'
                        //         } }, content),
                        //         h('span', data.row.coupon_title)
                        //     ]);
                        // }
                    },
                    {
                        title: '优惠券类型',
                        slot: 'type',
                        minWidth: 80
                    },
                    {
                        title: '面值',
                        key: 'coupon_price',
                        minWidth: 100
                    },
                    {
                        title: '领取方式',
                        slot: 'receive_type',
                        minWidth: 100
                    },
                    {
                        title: '领取日期',
                        slot: 'start_time',
                        minWidth: 250
                    },
                    {
                        title: '使用时间',
                        slot: 'start_use_time',
                        minWidth: 250
                    },
                    {
                        title: '发布数量',
                        slot: 'count',
                        minWidth: 90
                    },
                    {
                        title: '是否开启',
                        slot: 'status',
                        minWidth: 90
                    },
                    {
                        title: '操作',
                        slot: 'action',
                        fixed: 'right',
                        minWidth: 200
                    }
                ],
                tableFrom: {
                    status: '',
                    coupon_title: '',
                    receive_type:'',
                    page: 1,
                    limit: 15
                },
                receive_type:'',
                tableList: [],
                total: 0,
                FromData: null,
                receiveList: [],
                loading2: false,
                columns2: [
                    {
                        title: '用户名',
                        key: 'nickname',
                        minWidth: 150
                    },
                    {
                        title: '用户头像',
                        slot: 'avatar',
                        minWidth: 100
                    },
                    {
                        title: '领取时间',
                        key: 'add_time',
                        minWidth: 140
                    }
                ],
                total2: 0,
                receiveFrom: {
                    page: 1,
                    limit: 15
                },
                rows: {}
            }
        },
        created () {
            this.getList();
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
                this.delfromData = {
                    title: tit,
                    num: num,
                    url: `marketing/coupon/status/${row.id}`,
                    method: 'PUT',
                    ids: ''
                };
                this.$refs.modelSure.modals = true;
            },
            // 领取记录
            receive (row) {
                this.modals2 = true;
                this.rows = row;
                this.getReceivelist(row)
            },
            getReceivelist (row) {
                this.loading2 = true;
                releasedissueLogApi(row.id).then(async res => {
                    let data = res.data
                    this.receiveList = data.list;
                    this.total2 = res.data.count;
                    this.loading2 = false;
                }).catch(res => {
                    this.loading2 = false;
                    this.$Message.error(res.msg);
                })
            },
            // 领取记录改变分页
            receivePageChange (index) {
                this.receiveFrom.page = index;
                this.getReceivelist(this.rows)
            },
            // 删除
            couponDel (row, tit, num) {
                let delfromData = {
                    title: tit,
                    num: num,
                    success: delCouponReleased(row.id)
                };
                this.$modalSure(delfromData).then((res) => {
                    this.$Message.success(res.msg);
                    this.tableList.splice(num, 1)
                }).catch(res => {
                    this.$Message.error(res.msg);
                });
            },
            // 列表
            getList () {
                this.loading = true;
                this.tableFrom.receive_type = this.receive_type === 'all'?'':this.receive_type;
                this.tableFrom.status = this.tableFrom.status || '';
                releasedListApi(this.tableFrom).then(async res => {
                    let data = res.data
                    this.tableList = data.list;
                    this.total = res.data.count;
                    this.loading = false;
                }).catch(res => {
                    this.loading = false;
                    this.$Message.error(res.msg);
                })
            },
            pageChange (index) {
                this.tableFrom.page = index;
                this.getList();
            },
            // 表格搜索
            userSearchs () {
                this.tableFrom.page = 1;
                this.getList();
            },
            // 添加优惠券
            add () {
                this.$router.push({ path: '/admin/marketing/store_coupon_issue/create'});
            },
            // 复制
            copy (data) {
                this.$router.push({ path: `/admin/marketing/store_coupon_issue/create/${data.id}`});
            },
            // 是否开启
            openChange (data) {
                couponStatusApi(data).then(() => this.getList());
            }
        }
    }
</script>

<style scoped lang="stylus">
    .fa
        color #0a6aa1
        display block
    .sheng
        color #ff0000
        display block
    .tabBox_img
        width 36px
        height 36px
        border-radius:4px
        cursor pointer
        img
            width 100%
            height 100%

</style>

