<template>
    <div>
        <div class="i-layout-page-header">
          <div class="i-layout-page-header">
            <span class="ivu-page-header-title">{{$route.meta.title}}</span>
          </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <Form class="mb20" ref="formValidate" :model="formValidate"  :label-width="labelWidth" :label-position="labelPosition" @submit.native.prevent>
                <Row type="flex" :gutter="24">
                    <Col v-bind="grid">
                        <FormItem label="是否有效：" label-for="status">
                            <Select v-model="formValidate.status" placeholder="请选择" element-id="status" clearable @on-change="userSearchs">
                                <Option value="1">开启</Option>
                                <Option value="0">关闭</Option>
                            </Select>
                        </FormItem>
                    </Col>
                    <Col v-bind="grid">
                        <FormItem label="模板名称："  label-for="name">
                            <Input search enter-button  v-model="formValidate.name" placeholder="请输入模板名称" @on-search="userSearchs"/>
                        </FormItem>
                    </Col>
                </Row>
                <Row type="flex">
                    <Col v-bind="grid">
                        <Button v-auth="['app-wechat-template-create','app-routine-create']" type="primary"  icon="md-add" @click="add">添加模板消息</Button>
                        <Button v-if="$route.path == '/admin/app/routine/routine_template/index'" v-auth="['app-wechat-template-sync']" icon="md-list" type="success" @click="syncTemplate"  style="margin-left: 20px;">一键同步</Button>
                    </Col>
                </Row>
            </Form>
            <Alert v-if="industry">
                <template slot="desc">
                    <div>
                        主营行业：{{industry.primary_industry.first_class?industry.primary_industry.first_class + '||' :industry.primary_industry}} {{industry.primary_industry.second_class ? industry.primary_industry.second_class : ''}}
                    </div>
                   <div>
                       副营行业：{{industry.secondary_industry.first_class?industry.primary_industry.first_class + '||'  : industry.primary_industry}}  {{industry.primary_industry.second_class ? industry.primary_industry.second_class : ''}}
                   </div>
                </template>
            </Alert>
            <Table :columns="columns1" :data="tabList" ref="table" class="mt25"
                   :loading="loading"
                   no-userFrom-text="暂无数据"
                   no-filtered-userFrom-text="暂无筛选结果">
                <template slot-scope="{ row, index }" slot="status">
                    <i-switch v-model="row.status" :value="row.status" :true-value="1" :false-value="0" @on-change="onchangeIsShow(row)" size="large">
                        <span slot="open">开启</span>
                        <span slot="close">关闭</span>
                    </i-switch>
                </template>
                <template slot-scope="{ row, index }" slot="content">
                    <div class="template_sp_box">
                        <span v-for="(item, index) in row.content" :key="index"  class="template_sp">{{item}}</span>
                    </div>
                </template>
                <template slot-scope="{ row, index }" slot="add_time">
                    <span> {{Number(row.add_time) | formatDate}}</span>
                </template>
                <template slot-scope="{ row, index }" slot="action">
                    <a href="javascript:void(0);"  @click="edit(row)">编辑</a>
                    <Divider type="vertical" />
                    <a href="javascript:void(0);"  @click="del(row, '删除模板', index)">删除</a>
                </template>
            </Table>
            <div class="acea-row row-right page">
                <Page :total="total" :current="formValidate.page" show-elevator show-total @on-change="pageChange"
                      :page-size="formValidate.limit"/>
            </div>
        </Card>
    </div>
</template>

<script>
    import { routineListApi, routineSetStatusApi, routineCreateApi, routineEditApi, wechatListApi, wechatCreateApi, wechatEditApi, wechatSetStatusApi ,routineSyncTemplate} from '@/api/app';
    import { mapState } from 'vuex';
    import { formatDate } from '@/utils/validate';
    export default {
        name: 'routineTemplate',
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
                        title: '模板ID',
                        key: 'tempid',
                        minWidth: 300
                    },
                    {
                        title: '模板名',
                        key: 'name',
                        minWidth: 120
                    },
                    {
                        title: '回复内容',
                        slot: 'content',
                        minWidth: 200
                    },
                    {
                        title: '状态',
                        slot: 'status',
                        minWidth: 150
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
                        minWidth: 120
                    }
                ],
                formValidate: {
                    status: '',
                    name: '',
                    page: 1,
                    limit: 20
                },
                tabList: [],
                total: 0,
                FromData: null,
                delfromData: {},
                industry: null
            }
        },
        created () {
            this.getList()
        },
        watch: {
            $route (to, from) {
                this.getList()
            }
        },
        computed: {
            ...mapState('media', [
                'isMobile'
            ]),
            labelWidth () {
                return this.isMobile ? undefined : 80
            },
            labelPosition () {
                return this.isMobile ? 'top' : 'left'
            }
        },
        methods: {
            // 删除
            del (row, tit, num) {
                let delfromData = null
                if (this.$route.path === '/admin/app/routine/routine_template/index') {
                    delfromData = {
                        title: tit,
                        num: num,
                        url: `app/routine/${row.id}`,
                        method: 'DELETE',
                        ids: ''
                    }
                } else {
                    delfromData = {
                        title: tit,
                        num: num,
                        url: `app/wechat/template/${row.id}`,
                        method: 'DELETE',
                        ids: ''
                    }
                }
                this.$modalSure(delfromData).then((res) => {
                    this.$Message.success(res.msg)
                    this.tabList.splice(num, 1)
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 修改是否显示
            onchangeIsShow (row) {
                let data = {
                    id: row.id,
                    status: row.status
                }
                let functon
                if (this.$route.path === '/admin/app/routine/routine_template/index') {
                    functon = routineSetStatusApi(data)
                } else {
                    functon = wechatSetStatusApi(data)
                }
                functon.then(async res => {
                    this.$Message.success(res.msg)
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 等级列表
            getList () {
                this.loading = true
                this.formValidate.status = this.formValidate.status || ''
                let functon
                if (this.$route.path === '/admin/app/routine/routine_template/index') {
                    functon = routineListApi(this.formValidate)
                } else {
                    functon = wechatListApi(this.formValidate)
                }
                functon.then(async res => {
                    console.log(res)
                    let data = res.data
                    this.tabList = data.list
                    this.total = data.count
                    this.industry = data.industry || null
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
            // 添加
            add () {
                // let functon;
                if (this.$route.path === '/admin/app/routine/routine_template/index') {
                    this.$modalForm(routineCreateApi(this.formValidate)).then(() => this.getList())
                    // functon = routineCreateApi(this.formValidate);
                } else {
                    this.$modalForm(wechatCreateApi(this.formValidate)).then(() => this.getList())
                    // functon = wechatCreateApi(this.formValidate);
                }
                // functon.then(async res => {
                //     this.FromData = res.data;
                //     this.$refs.edits.modals = true;
                // }).catch(res => {
                //     this.$Message.error(res.msg);
                // })
            },
            // 编辑
            edit (row) {
                if (this.$route.path === '/admin/app/routine/routine_template/index') {
                    this.$modalForm(routineEditApi(row.id)).then(() => this.getList())
                    // functon = routineEditApi(row.id);
                } else {
                    this.$modalForm(wechatEditApi(row.id)).then(() => this.getList())
                    // functon = wechatEditApi(row.id);
                }
            },
            // 表格搜索
            userSearchs () {
                this.formValidate.page = 1;
                this.getList();
            },
            // 同步订阅消息
            syncTemplate(){
                routineSyncTemplate().then(res=>{
                    this.$Message.success(res.msg)
                    this.getList()
                }).catch(error=>{
                    this.$Message.error(res.msg)
                })
            }
        }
    }
</script>

<style scoped lang="stylus">
    .template_sp_box
        padding 5px 0
        box-sizing border-box
       .template_sp
          display block
          padding 2px 0
          box-sizing border-box
</style>
