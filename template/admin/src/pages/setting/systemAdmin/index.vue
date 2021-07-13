<template>
    <div>
        <div class="i-layout-page-header">
          <div class="i-layout-page-header">
            <span class="ivu-page-header-title">{{$route.meta.title}}</span>
          </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <Form ref="formValidate" :model="formValidate"  :label-width="labelWidth" :label-position="labelPosition" @submit.native.prevent>
                <Row type="flex"  :gutter="24">
                    <Col v-bind="grid">
                        <FormItem label="状态："  label-for="status1">
                            <Select v-model="status" placeholder="请选择"  @on-change="userSearchs" clearable>
                                <Option value="all">全部</Option>
                                <Option value="1">开启</Option>
                                <Option value="0">关闭</Option>
                            </Select>
                        </FormItem>
                    </Col>
                    <Col v-bind="grid">
                        <FormItem label="搜索："  label-for="status2">
                            <Input search enter-button placeholder="请输入姓名或者账号" v-model="formValidate.name" @on-search="userSearchs"/>
                        </FormItem>
                    </Col>
                </Row>
                <Row type="flex">
                    <Col v-bind="grid">
                            <Button v-auth="['setting-system_admin-add']" type="primary" @click="add"  icon="md-add">添加管理员</Button>
                    </Col>
                </Row>
            </Form>
            <Table :columns="columns1" :data="list" class="mt25" no-userFrom-text="暂无数据"
                   no-filtered-userFrom-text="暂无筛选结果"  :loading="loading" highlight-row>
                <template slot-scope="{ row, index }" slot="roles">
                    <div v-if="row.roles.length!==0">
                        <Tag color="blue" v-for="(item,index) in row.roles.split(',')" :key="index" v-text="item"></Tag>
                    </div>
                </template>
                <template slot-scope="{ row, index }" slot="status">
                    <i-switch v-model="row.status" :value="row.status" :true-value="1" :false-value="0" @on-change="onchangeIsShow(row)" size="large">
                        <span slot="open">开启</span>
                        <span slot="close">关闭</span>
                    </i-switch>
                </template>
                <template slot-scope="{ row, index }" slot="action">
                    <a @click="edit(row)">编辑</a>
                    <Divider type="vertical"/>
                    <a @click="del(row,'删除管理员', index)">删除</a>
                </template>
            </Table>
            <div class="acea-row row-right page">
                <Page :total="total" :current="formValidate.page" show-elevator show-total @on-change="pageChange"
                      :page-size="formValidate.limit"/>
            </div>
        </Card>
       <!-- 添加 编辑 -->
        <admin-from :FromData="FromData" ref="adminfrom" @submitFail="submitFail"></admin-from>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    import { adminListApi, adminFromApi, adminEditFromApi, setShowApi } from '@/api/systemAdmin'
    import adminFrom from '../../../components/from/from'
    export default {
        name: 'systemAdmin',
        components: { adminFrom },
        data () {
            return {
                grid: {
                    xl: 7,
                    lg: 7,
                    md: 12,
                    sm: 24,
                    xs: 24
                },
                total: 0,
                loading: false,
                roleData: {
                    status1: ''
                },
                formValidate: {
                    roles: '',
                    status: '',
                    name: '',
                    page: 1, // 当前页
                    limit: 20 // 每页显示条数
                },
                status:'',
                list: [],
                columns1: [
                    {
                        title: '姓名',
                        key: 'real_name',
                        minWidth: 120
                    },
                    {
                        title: '账号',
                        key: 'account',
                        minWidth: 150
                    },
                    {
                        title: '身份',
                        slot: 'roles',
                        minWidth: 250
                    },
                    {
                        title: '最后一次登录时间',
                        key: '_last_time',
                        minWidth: 180
                    },
                    {
                        title: '最后一次登录ip',
                        key: 'last_ip',
                        minWidth: 180
                    },
                    {
                        title: '开启',
                        slot: 'status',
                        minWidth: 90
                    },
                    {
                        title: '操作',
                        key: 'action',
                        slot: 'action',
                        fixed: 'right',
                        minWidth: 120
                    }
                ],
                FromData: null,
                modalTitleSs: '',
                ids: Number
            }
        },
        computed: {
            ...mapState('media', [
                'isMobile'
            ]),
            labelWidth () {
                return this.isMobile ? undefined : 50
            },
            labelPosition () {
                return this.isMobile ? 'top' : 'left'
            }
        },
        created () {
            this.getList()
        },
        methods: {
            // 修改是否开启
            onchangeIsShow (row) {
                let data = {
                    id: row.id,
                    status: row.status
                }
                setShowApi(data).then(async res => {
                    this.$Message.success(res.msg)
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 请求列表
            submitFail () {
                this.getList()
            },
            // 列表
            getList () {
                this.loading = true
                this.formValidate.roles = this.formValidate.roles || ''
                adminListApi(this.formValidate).then(async res => {
                    this.total = res.data.count
                    this.list = res.data.list
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
            // 添加表单
            add () {
                adminFromApi().then(async res => {
                    this.FromData = res.data
                    this.$refs.adminfrom.modals = true
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 编辑
            edit (row) {
                adminEditFromApi(row.id).then(async res => {
                    if (res.data.status === false) {
                        return this.$authLapse(res.data)
                    }
                    this.FromData = res.data
                    this.$refs.adminfrom.modals = true
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 删除
            del (row, tit, num) {
                let delfromData = {
                    title: tit,
                    num: num,
                    url: `setting/admin/${row.id}`,
                    method: 'DELETE',
                    ids: ''
                }
                this.$modalSure(delfromData).then((res) => {
                    this.$Message.success(res.msg)
                    this.list.splice(num, 1)
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 表格搜索
            userSearchs () {
                this.formValidate.status = this.status === 'all'?'':this.status;
                this.formValidate.page = 1
                this.list = []
                this.getList()
            }
        }
    }
</script>

<style scoped>

</style>
