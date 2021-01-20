<template>
    <div>
        <div class="i-layout-page-header">
          <div class="i-layout-page-header">
            <span class="ivu-page-header-title">{{$route.meta.title}}</span>
          </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <Row type="flex">
                <Col v-bind="grid">
                    <Button type="primary" @click="goIndex" class="mr20">配置分类</Button>
                    <Button type="primary" icon="md-add" @click="configureAdd">添加配置</Button>
                </Col>
            </Row>
            <Divider dashed />
            <Table :columns="columns1" :data="classList" ref="table"
                   :loading="loading"
                   no-userFrom-text="暂无数据"
                   no-filtered-userFrom-text="暂无筛选结果">
                <template slot-scope="{ row, index }" slot="values">
                    <span v-if=" row.type === 'text' || row.type ==='textarea' ||  row.type ==='radio' || row.type ==='checkbox'">{{row.value}}</span>
                    <div class="valBox acea-row" v-if=" row.type ==='upload' && row.upload_type === 3 ">
                        <div v-if="row.value instanceof Array">
                            <div class="valPicbox acea-row row-column-around"  v-for="(item, index) in row.value" :key="index">
                                <div class="valPicbox_pic"><Icon type="md-document" /></div>
                                <span class="valPicbox_sp">{{item.filename}}</span>
                            </div>
                        </div>
<!--                        <div v-else>-->
<!--                            <div class="valPicbox acea-row row-column-around">-->
<!--                                <div class="valPicbox_pic"><Icon type="md-document" /></div>-->
<!--                                <span class="valPicbox_sp">{{row.filename}}</span>-->
<!--                            </div>-->
<!--                        </div>-->
                    </div>
                    <div class="valBox acea-row" v-if=" row.type ==='upload' && row.upload_type !== 3">
                        <div v-if="row.value instanceof Array">
                            <div class="valPicbox acea-row row-column-around"  v-for="(item, index) in row.value" :key="index">
                                <div class="valPicbox_pic"><img v-lazy="item.filepath"></div>
                                <span class="valPicbox_sp">{{item.filename}}</span>
                            </div>
                        </div>
<!--                        <div v-else>-->
<!--                            <div class="valPicbox acea-row row-column-around">-->
<!--                                <div class="valPicbox_pic"><img :src="row.filepath ? row.filepath : require('../../../assets/images/moren.jpg')"></div>-->
<!--                                <span class="valPicbox_sp">{{row.filename}}</span>-->
<!--                            </div>-->
<!--                        </div>-->
                    </div>
                </template>
                <template slot-scope="{ row, index }" slot="statuss">
                    <i-switch v-model="row.status" :value="row.status" :true-value="1" :false-value="0" @on-change="onchangeIsShow(row)" size="large">
                        <span slot="open">显示</span>
                        <span slot="close">隐藏</span>
                    </i-switch>
                </template>
                <template slot-scope="{ row, index }" slot="action">
                    <a @click="edit(row)">编辑</a>
                    <Divider type="vertical" />
                    <a @click="del(row,'删除分类',index)">删除</a>
                </template>
            </Table>
<!--            <div class="acea-row row-right page">-->
<!--                <Page :total="total" show-elevator show-total @on-change="pageChange"-->
<!--                      :page-size="formValidate.limit"/>-->
<!--            </div>-->
        </Card>

        <!-- 新建 表单-->
        <Modal v-model="modals2"  scrollable  footer-hide closable  title="添加配置字段" :mask-closable="false" :z-index="1" width="700">
            <Tabs v-model="typeFrom.type" @on-click="onhangeTab" class="tabsName">
                <TabPane label="文本框 " name="0"></TabPane>
                <TabPane label="多行文本框" name="1"></TabPane>
                <TabPane label="单选框" name="2"></TabPane>
                <TabPane label="文件上传" name="3"></TabPane>
                <TabPane label="多选框" name="4"></TabPane>
                <TabPane label="下拉框" name="5"></TabPane>
            </Tabs>
            <form-create v-if="rules.length!=0"  :rule="rules" @on-submit="onSubmit"  class="formBox" ref="fc" handleIcon="false"></form-create>
        </Modal>
        <!-- 编辑表单-->
        <edit-from ref="edits" :FromData="FromData" @submitFail="submitFail"></edit-from>
     </div>
</template>

<script>
    import { configTabListApi, configTabAddApi, configTabEditApi, configSetStatusApi } from '@/api/system'
    import formCreate from '@form-create/iview'
    import editFrom from '@/components/from/from'
    import request from '@/libs/request'
    export default {
        name: 'list',
        components: { formCreate: formCreate.$form(), editFrom },
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
                formValidate: {
                    tab_id: 0,
                    page: 1,
                    limit: 20
                },
                total: 0,
                columns1: [
                    {
                        title: 'ID',
                        key: 'id',
                        width: 80
                    },
                    {
                        title: '配置名称',
                        key: 'info',
                        minWidth: 130
                    },
                    {
                        title: '字段变量',
                        key: 'menu_name',
                        minWidth: 140
                    },
                    {
                        title: '字段类型',
                        key: 'type',
                        minWidth: 90
                    },
                    {
                        title: '值',
                        slot: 'values',
                        minWidth: 230
                    },
                    {
                        title: '是否显示',
                        slot: 'statuss',
                        minWidth: 90
                    },
                    {
                        title: '操作',
                        slot: 'action',
                        fixed: 'right',
                        minWidth: 120
                    }
                ],
                FromData: null,
                FromRequestData: {},
                modalTitleSs: '',
                classList: [],
                num: 0,
                typeFrom: {
                    type: 0,
                    tab_id: this.$route.params.id
                },
                rules: []
            }
        },
        watch: {
            $route: {
                handler: function (val, oldVal) {
                    this.getList()
                },
                // 深度观察监听
                deep: true
            }
        },
        mounted () {
            this.getList()
        },
        methods: {
            // 点击tab
            onhangeTab (name) {
                this.typeFrom.type = name
                this.classAdd()
            },
            // 新增表单
            classAdd () {
                configTabAddApi(this.typeFrom).then(async res => {
                    if (res.data.status === false) {
                        return this.$authLapse(res.data)
                    }
                    let data = res.data || {}
                    this.FromRequestData = { action: data.action, method: data.method }
                    this.rules = data.rules
                    this.modals2 = true
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 编辑表单
            edit (row) {
                configTabEditApi(row.id).then(async res => {
                    if (res.data.status === false) {
                        return this.$authLapse(res.data)
                    }
                    let data = res.data || {}
                    this.FromRequestData = { action: data.action, method: data.method }
                    this.rules = data.rules
                    this.$refs.edits.modals = true
                    this.modals2 = true
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 提交表单
            onSubmit (formData) {
                request({
                    url: this.FromRequestData.action,
                    method: this.FromRequestData.method,
                    data: formData
                }).then(res => {
                    this.$Message.success(res.msg)
                    setTimeout(() => {
                        this.modals2 = false
                    }, 1000)
                    setTimeout(() => {
                        this.getList()
                    }, 1500)
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 修改成功
            submitFail () {
                this.getList()
            },
            // 跳转到配置分类页面
            goIndex () {
                this.$router.push({ path: '/admin/system/config/system_config_tab/index' })
            },
            // 添加配置
            configureAdd () {
                // this.modals2 = true;
                this.classAdd()
            },
            // 列表
            getList () {
                this.loading = true
                this.formValidate.tab_id = this.$route.params.id
                configTabListApi(this.formValidate).then(async res => {
                    let data = res.data
                    this.classList = data.list
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
            // 删除
            del (row, tit, num) {
                let delfromData = {
                    title: tit,
                    num: num,
                    url: `/setting/config/${row.id}`,
                    method: 'DELETE',
                    ids: ''
                }
                this.$modalSure(delfromData).then((res) => {
                    this.$Message.success(res.msg)
                    this.classList.splice(num, 1)
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 修改是否显示
            onchangeIsShow (row) {
                configSetStatusApi(row.id, row.status).then(async res => {
                    this.$Message.success(res.msg)
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            }
        }
    }
</script>
<style scoped lang="stylus">
    .tabsName
      margin-bottom 15px
    .valBox
        margin 10px 0
    .valPicbox
        border: 1px solid #e7eaec;
    .valPicbox_pic
        width 200px
        height 100px
        display flex
        align-items center
        justify-content center
        img
          width 100%
          height 100%
        >>> .ivu-icon-md-document
            font-size: 70px;
            color: #dadada;
    .valPicbox_sp
        display block
        font-size 12px
        width 200px
        padding 7px
        box-sizing border-box
        border-top: 1px solid #e7eaec;
</style>
