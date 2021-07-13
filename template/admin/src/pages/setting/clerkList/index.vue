<template>
    <div>
        <div class="i-layout-page-header">
          <div class="i-layout-page-header">
            <span class="ivu-page-header-title">{{$route.meta.title}}</span>
          </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <Form ref="artFrom" :model="artFrom"  :label-width="labelWidth" :label-position="labelPosition" @submit.native.prevent>
                <Row type="flex" :gutter="24">
                    <Col v-bind="grid" class="mr">
                        <FormItem label="提货点名称："  label-for="store_name">
                            <Select v-model="artFrom.store_id" element-id="store_id" clearable @on-change="userSearchs">
                                <Option v-for="item in storeSelectList" :value="item.id" :key="item.id">{{ item.name }}</Option>
                            </Select>
                        </FormItem>
                    </Col>
                    <!--<Col v-bind="grid" class="mr">-->
                        <!--<Button type="primary" class="mr15" @click="userSearchs">搜索</Button>-->
                    <!--</Col>-->
                </Row>
            </Form>
            <Row type="flex">
                <Col v-bind="grid">
                    <Button v-auth="['merchant-store_staff-create']" type="primary"  icon="md-add" @click="add">添加核销员</Button>
                </Col>
            </Row>
            <Table :columns="columns" :data="storeLists" ref="table" class="mt25"
                   :loading="loading" highlight-row
                   no-userFrom-text="暂无数据"
                   no-filtered-userFrom-text="暂无筛选结果">
                <template slot-scope="{ row, index }" slot="avatar">
                    <div class="tabBox_img" v-viewer>
                        <img v-lazy="row.avatar">
                    </div>
                </template>
                <template slot-scope="{ row, index }" slot="status">
                    <i-switch v-model="row.status" :value="row.status" :true-value="1" :false-value="0" @on-change="onchangeIsShow(row.id,row.status)" size="large">>
                        <span slot="open">显示</span>
                        <span slot="close">隐藏</span>
                    </i-switch>
                </template>
                <template slot-scope="{ row, index }" slot="action">
                    <a @click="edit(row.id)">编辑</a>
                    <Divider type="vertical"/>
                    <a @click="del(row,'删除核销员',index)">删除</a>
                </template>
            </Table>
            <div class="acea-row row-right page">
                <Page :total="total" :current="artFrom.page" show-elevator show-total @on-change="pageChange"
                      :page-size="artFrom.limit"/>
            </div>
        </Card>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    import { storeStaffApi, storeStaffCreateApi, merchantStoreListApi, storeStaffSetShowApi, storeStaffEditApi } from '@/api/setting'
    export default {
        name: 'setting_staff',
        components: {},
        computed: {
            ...mapState('media', [
                'isMobile'
            ]),
            ...mapState('userLevel', [
                'categoryId'
            ]),
            labelWidth () {
                return this.isMobile ? undefined : 85
            },
            labelPosition () {
                return this.isMobile ? 'top' : 'right'
            }
        },
        data () {
            return {
                grid: {
                    xl: 10,
                    lg: 10,
                    md: 12,
                    sm: 24,
                    xs: 24
                },
                artFrom: {
                    page: 1,
                    limit: 15,
                    store_id: 0
                },
                loading: false,
                columns: [
                    {
                        title: 'ID',
                        key: 'id',
                        width: 80,
                        sortable: true
                    },
                    {
                        title: '微信名称',
                        key: 'nickname',
                        minWidth: 100
                    },
                    {
                        title: '头像',
                        slot: 'avatar',
                        minWidth: 100
                    },
                    {
                        title: '客服名称',
                        key: 'staff_name',
                        minWidth: 100
                    },
                    {
                        title: '所属提货点',
                        key: 'name',
                        minWidth: 100
                    },
                    {
                        title: '添加时间',
                        key: 'add_time',
                        minWidth: 100
                    },
                    {
                        title: '状态',
                        slot: 'status',
                        minWidth: 100
                    },
                    {
                        title: '操作',
                        slot: 'action',
                        fixed: 'right',
                        minWidth: 120
                    }
                ],
                storeLists: [],
                storeSelectList: [],
                total: 0
            }
        },
        mounted () {
            this.getList()
            this.storeList()
        },
        methods: {
            storeList () {
                let that = this
                merchantStoreListApi().then(res => {
                    that.storeSelectList = res.data
                })
            },
            getList () {
                let that = this
                that.loading = true
                storeStaffApi(that.artFrom).then(res => {
                    that.loading = false
                    that.storeLists = res.data.list
                    that.total = res.data.count
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 搜索；
            userSearchs () {
                this.artFrom.page = 1
                this.getList()
            },
            pageChange (index) {
                this.artFrom.page = index
                this.getList()
            },
            // 删除
            del (row, tit, num) {
                let delfromData = {
                    title: tit,
                    num: num,
                    url: `merchant/store_staff/del/${row.id}`,
                    method: 'DELETE',
                    ids: ''
                }
                this.$modalSure(delfromData).then((res) => {
                    this.$Message.success(res.msg)
                    this.storeLists.splice(num, 1)
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 添加核销员；
            add () {
                this.$modalForm(storeStaffCreateApi(0)).then(() => this.getList())
            },
            onchangeIsShow (id, is_show) {
                let that = this
                storeStaffSetShowApi(id, is_show).then(res => {
                    that.$Message.success(res.msg)
                    that.getList()
                })
            },
            edit (id) {
                this.$modalForm(storeStaffEditApi(id)).then(() => this.getList())
            }
        }
    }
</script>

<style scoped lang="stylus">
    .tabBox_img
        width 36px
        height 36px
        border-radius:4px
        cursor pointer
        img
            width 100%
            height 100%
</style>
