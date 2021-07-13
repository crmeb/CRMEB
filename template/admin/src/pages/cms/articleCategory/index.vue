<template>
    <div>
        <div class="i-layout-page-header">
          <div class="i-layout-page-header">
            <span class="ivu-page-header-title">{{$route.meta.title}}</span>
          </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <Form ref="formValidate" :model="formValidate"  :label-width="labelWidth" :label-position="labelPosition" @submit.native.prevent>
                <Row type="flex" :gutter="24">
                    <Col v-bind="grid">
                        <FormItem label="是否显示："  label-for="status">
                            <Select v-model="status" placeholder="请选择" element-id="status" clearable @on-change="userSearchs">
                                <Option value="all">全部</Option>
                                <Option value="1">显示</Option>
                                <Option value="0">不显示</Option>
                            </Select>
                        </FormItem>
                    </Col>
                    <Col v-bind="grid">
                        <FormItem label="分类昵称：" prop="title" label-for="status2">
                            <Input search enter-button placeholder="请输入分类昵称" v-model="formValidate.title" @on-search="userSearchs"/>
                        </FormItem>
                    </Col>
                </Row>
                <Row type="flex">
                    <Col v-bind="grid">
                        <Button v-auth="['cms-category-create']" type="primary" icon="md-add" @click="add">添加文章分类</Button>
                    </Col>
                </Row>
            </Form>
            <Divider dashed/>
            <vxe-table
                    class="vxeTable"
                    highlight-hover-row
                    :loading="loading"
                    header-row-class-name="false"
                    :tree-config="{children: 'children'}"
                    :data="categoryList">
                <vxe-table-column field="id" title="ID"  tooltip width="80"></vxe-table-column>
                <vxe-table-column field="title" title="分类昵称" min-width="130"></vxe-table-column>
                <vxe-table-column field="image" title="分类图片"  min-width="130">
                    <template v-slot="{ row }">
                        <div class="tabBox_img" v-viewer>
                            <img v-lazy="row.image">
                        </div>
                    </template>
                </vxe-table-column>
                <vxe-table-column field="status" title="状态" min-width="120">
                    <template v-slot="{ row }">
                        <i-switch v-model="row.status" :value="row.status" :true-value="1" :false-value="0" @on-change="onchangeIsShow(row)" size="large">
                            <span slot="open">显示</span>
                            <span slot="close">隐藏</span>
                        </i-switch>
                    </template>
                </vxe-table-column>
                <vxe-table-column field="date" title="操作" align="center"  width="250" fixed="right">
                    <template v-slot="{ row }">
                        <a @click="edit(row)">编辑</a>
                        <Divider type="vertical" />
                        <a @click="del(row,'删除文章分类')">删除</a>
                        <Divider type="vertical" />
                        <a @click="lookUp(row)">查看文章</a>
                    </template>
                </vxe-table-column>
            </vxe-table>
<!--            <div class="acea-row row-right page">-->
<!--                <Page :total="total" :current="formValidate.page" show-elevator show-total @on-change="pageChange"-->
<!--                      :page-size="formValidate.limit"/>-->
<!--            </div>-->
        </Card>
     </div>
</template>
<script>
    import { mapState, mapMutations } from 'vuex'
    import { categoryAddApi, categoryEditApi, categoryListApi, statusApi } from '@/api/cms'
    export default {
        name: 'articleCategory',
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
                formValidate: {
                    status: '',
                    page: 1,
                    limit: 20,
                    type: 0
                },
                status: '',
                total: 0,
                columns1: [
                    {
                        title: 'ID',
                        key: 'id',
                        width: 80
                    },
                    {
                        title: '分类昵称',
                        key: 'title',
                        minWidth: 130
                    },
                    {
                        title: '分类图片',
                        slot: 'images',
                        minWidth: 130
                    },
                    {
                        title: '状态',
                        slot: 'statuss',
                        minWidth: 130
                    },
                    {
                        title: '操作',
                        slot: 'action',
                        fixed: 'right',
                        minWidth: 120
                    }
                ],
                FromData: null,
                modalTitleSs: '',
                categoryId: 0,
                categoryList: []
            }
        },
        computed: {
            ...mapState('media', [
                'isMobile'
            ]),
            labelWidth () {
                return this.isMobile ? undefined : 75
            },
            labelPosition () {
                return this.isMobile ? 'top' : 'right'
            }
        },
        mounted () {
            this.getList()
        },
        methods: {
            ...mapMutations('userLevel', [
                'getCategoryId'
            ]),
            // 添加
            add () {
                this.$modalForm(categoryAddApi()).then(() => this.getList())
                // categoryAddApi().then(async res => {
                //     this.$refs.edits.modals = true;
                //     this.FromData = res.data;
                // }).catch(res => {
                //     this.$Message.error(res.msg);
                // })
            },
            // 编辑
            edit (row) {
                this.$modalForm(categoryEditApi(row.id)).then(() => this.getList())
                // categoryEditApi(row.id).then(async res => {
                //     this.FromData = res.data;
                //     this.$refs.edits.modals = true;
                // }).catch(res => {
                //     this.$Message.error(res.msg);
                // })
            },
            // 删除
            del (row, tit) {
                let delfromData = {
                    title: tit,
                    num: 0,
                    url: `cms/category/${row.id}`,
                    method: 'DELETE',
                    ids: ''
                }
                this.$modalSure(delfromData).then((res) => {
                    this.$Message.success(res.msg)
                    this.getList()
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 列表
            getList () {
                this.loading = true
                this.formValidate.status = this.status === 'all'?'':this.status;
                categoryListApi(this.formValidate).then(async res => {
                    let data = res.data
                    this.categoryList = data.list
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
            // 表格搜索
            userSearchs () {
                this.formValidate.page = 1
                this.getList()
            },
            // 修改是否显示
            onchangeIsShow (row) {
                let data = {
                    id: row.id,
                    status: row.status
                }
                statusApi(data).then(async res => {
                    this.$Message.success(res.msg)
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 查看文章
            lookUp (row) {
                this.$router.push({ path: '/admin/cms/article/index',query:{
                        id:row.id
                    }});
                //xia mian chu cun mei yong;
                this.getCategoryId(row.id)
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
