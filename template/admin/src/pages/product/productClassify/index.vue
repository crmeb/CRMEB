<template>
    <div class="article-manager">
        <div class="i-layout-page-header">
          <div class="i-layout-page-header">
            <span class="ivu-page-header-title">商品分类</span>
          </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <Form ref="artFrom" :model="artFrom" :label-width="75" label-position="right" @submit.native.prevent>
                <Row type="flex" :gutter="24">
                    <Col v-bind="grid">
                        <FormItem label="商品分类：" prop="pid" label-for="pid">
                            <Select v-model="artFrom.pid" @on-change="userSearchs" clearable>
                                <Option v-for="item in treeSelect" :value="item.id" :key="item.id">{{ item.html + item.cate_name }}</Option>
                            </Select>
                        </FormItem>
                    </Col>
                    <Col v-bind="grid">
                        <FormItem label="状态："  label-for="is_show">
                            <Select v-model="artFrom.is_show" placeholder="请选择" clearable  @on-change="userSearchs">
                                <Option value="1">显示</Option>
                                <Option value="0">隐藏</Option>
                            </Select>
                        </FormItem>
                    </Col>
                    <Col v-bind="grid">
                        <FormItem label="分类名称："  label-for="status2">
                            <Input search enter-button placeholder="请输入" v-model="artFrom.cate_name" @on-search="userSearchs"/>
                        </FormItem>
                    </Col>
                </Row>
                <Row type="flex">
                    <Col v-bind="grid">
                        <Button v-auth="['product-save-cate']" type="primary" class="bnt" icon="md-add" @click="addClass">添加分类</Button>
                    </Col>
                </Row>
            </Form>
            <vxe-table
                    class="mt25"
                    highlight-hover-row
                    :loading="loading"
                    header-row-class-name="false"
                    :tree-config="{children: 'children'}"
                    :data="tableData">
                <vxe-table-column field="id" title="ID"  tooltip width="80"></vxe-table-column>
                <vxe-table-column field="cate_name" tree-node title="分类名称"  min-width="250" ></vxe-table-column>
                <vxe-table-column field="pic" title="分类图标" min-width="100">
                    <template v-slot="{ row }">
                        <div class="tabBox_img" v-viewer>
                            <img v-lazy="row.pic">
                        </div>
                    </template>
                </vxe-table-column>
                <vxe-table-column field="sort" title="排序" min-width="100" tooltip="true"></vxe-table-column>
                <vxe-table-column field="is_show" title="状态" min-width="120">
                    <template v-slot="{ row }">
                        <i-switch v-model="row.is_show" :value="row.is_show" :true-value="1" :false-value="0" @on-change="onchangeIsShow(row)" size="large">
                            <span slot="open">显示</span>
                            <span slot="close">隐藏</span>
                        </i-switch>
                    </template>
                </vxe-table-column>
                <vxe-table-column field="date" title="操作" width="250" fixed="right" align="center">
                    <template v-slot="{ row, index }">
                        <a @click="edit(row)">编辑</a>
                        <Divider type="vertical"/>
                        <a @click="del(row,'删除商品分类',index)">删除</a>
                    </template>
                </vxe-table-column>
            </vxe-table>
<!--            <div class="acea-row row-right page">-->
<!--                <Page :total="total" :current="artFrom.page" show-elevator show-total @on-change="pageChange"-->
<!--                      :page-size="artFrom.limit"/>-->
<!--            </div>-->
        </Card>
        <!-- 添加 编辑表单-->
        <edit-from ref="edits" :FromData="FromData" @submitFail="userSearchs"></edit-from>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    import { productListApi, productCreateApi, productEditApi, setShowApi, treeListApi } from '@/api/product'
    import editFrom from '../../../components/from/from'
    export default {
        name: 'product_productClassify',
        components: {
            editFrom
        },
        data () {
            return {
                treeSelect: [],
                FromData: null,
                grid: {
                    xl: 7,
                    lg: 7,
                    md: 12,
                    sm: 24,
                    xs: 24
                },
                loading: false,
                artFrom: {
                    pid: 0,
                    is_show: '',
                    page: 1,
                    cate_name: '',
                    limit: 15
                },
                total: 0,
                tableData: []
            }
        },
        computed: {
            ...mapState('admin/userLevel', [
                'categoryId'
            ])
        },
        mounted () {
            this.goodsCategory()
            this.getList()
        },
        methods: {
            // 商品分类；
            goodsCategory () {
                treeListApi(0).then(res => {
                    this.treeSelect = res.data
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 列表
            getList () {
                this.loading = true
                this.artFrom.is_show = this.artFrom.is_show || ''
                this.artFrom.pid = this.artFrom.pid || ''
                productListApi(this.artFrom).then(async res => {
                    let data = res.data
                    this.tableData = data.list
                    this.total = data.count
                    this.loading = false
                }).catch(res => {
                    this.loading = false
                    this.$Message.error(res.msg)
                })
            },
            pageChange (index) {
                this.artFrom.page = index
                this.getList()
            },
            // 添加
            addClass () {
                this.$modalForm(productCreateApi()).then(() => this.getList())
            },
            // 编辑
            edit (row) {
                this.$modalForm(productEditApi(row.id)).then(() => this.getList())
            },
            // 修改状态
            onchangeIsShow (row) {
                let data = {
                    id: row.id,
                    is_show: row.is_show
                }
                setShowApi(data).then(async res => {
                    this.$Message.success(res.msg)
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 下拉树
            handleCheckChange (data) {
                let value = ''
                let title = ''
                this.list = []
                this.artFrom.pid = 0
                data.forEach((item, index) => {
                    value += `${item.id},`
                    title += `${item.title},`
                })
                value = value.substring(0, value.length - 1)
                title = title.substring(0, title.length - 1)
                this.list.push({
                    value,
                    title
                })
                this.artFrom.pid = value
                this.getList()
            },
            // 删除
            del (row, tit, num) {
                let delfromData = {
                    title: tit,
                    num: num,
                    url: `product/category/${row.id}`,
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
            // 表格搜索
            userSearchs () {
                this.artFrom.page = 1
                this.getList()
            }
        }
    }
</script>
<style scoped lang="stylus">
    .treeSel >>>.ivu-select-dropdown-list
        padding 0 10px!important
        box-sizing border-box
    .tabBox_img
        width 36px
        height 36px
        border-radius:4px
        cursor pointer
        img
            width 100%
            height 100%
</style>
