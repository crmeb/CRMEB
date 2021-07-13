<template>
    <div>
        <div class="i-layout-page-header">
          <div class="i-layout-page-header">
            <span class="ivu-page-header-title">商品规格</span>
          </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <Form ref="artFrom" :model="artFrom" :label-width="80" label-position="right" class="tabform" @submit.native.prevent>
                <Row :gutter="24" type="flex" justify="end">
                    <Col span="24" class="ivu-text-left">
                        <FormItem label="规格搜索：">
                            <Input search enter-button v-model="artFrom.rule_name"  placeholder="请输入规格名称" style="width: 30%;"  @on-search="userSearchs"></Input>
                        </FormItem>
                    </Col>
                    <Col span="24">
                        <Button v-auth="['product-rule-save']" class="mr20" type="primary" icon="md-add" @click="addAttr">添加商品规格</Button>
                        <Button v-auth="['product-product-rule-delete']" @click="del(null,'批量删除规格')">批量删除</Button>
                    </Col>
                </Row>
            </Form>
            <Table class="mt25" ref="selection" :columns="columns4" :data="tableList"  :loading="loading"
                   highlight-row @on-selection-change="onSelectTab"
                   no-data-text="暂无数据"
                   no-filtered-data-text="暂无筛选结果">
                <template slot-scope="{ row }" slot="attr_value">
                    <span v-for="(item,index) in row.attr_value" :key="index" v-text="item" style="display: block"></span>
                </template>
                <template slot-scope="{ row }" slot="action">
                    <a @click="edit(row)">编辑</a>
                    <Divider type="vertical"/>
                    <a @click="del(row,'删除规格')">删除</a>
                </template>
            </Table>
            <div class="acea-row row-right page">
                <Page :total="total" :current="artFrom.page" show-elevator show-total @on-change="pageChange"
                      :page-size="artFrom.limit"/>
            </div>
        </Card>
        <add-attr ref="addattr" @getList="userSearchs"></add-attr>
  </div>
</template>

<script>
    import { mapState } from 'vuex'
    import addAttr from './addAttr'
    import { ruleListApi } from '@/api/product'
    export default {
        name: 'productAttr',
        components: { addAttr },
        data () {
            return {
                loading: false,
                artFrom: {
                    page: 1,
                    limit: 15,
                    rule_name: ''
                },
                columns4: [
                    {
                        type: 'selection',
                        width: 60
                    },
                    {
                        title: 'ID',
                        key: 'id',
                        width: 80
                    },
                    {
                        title: '规格名称',
                        key: 'rule_name',
                        minWidth: 150
                    },
                    {
                        title: '商品规格',
                        key: 'attr_name',
                        minWidth: 250
                    },
                    {
                        title: '商品属性',
                        slot: 'attr_value',
                        minWidth: 300
                    },
                    {
                        title: '操作',
                        slot: 'action',
                        fixed: 'right',
                        minWidth: 120
                    }
                ],
                tableList: [],
                total: 0,
                ids: '',
                selectionList: []
            }
        },
        computed: {
            ...mapState('admin/order', [
                'orderChartType'
            ])
        },
        created () {
            this.getDataList()
        },
        methods: {
            // 全选
            onSelectTab (selection) {
                this.selectionList = selection
                let data = []
                this.selectionList.map((item) => {
                    data.push(item.id)
                })
                this.ids = data.join(',')
            },
            // 删除
            del (row, tit) {
                let data = {}
                if (tit === '批量删除规格') {
                    if (this.selectionList.length === 0) return this.$Message.warning('请选择要删除的规格！')
                    data = {
                        ids: this.ids
                    }
                } else {
                    data = {
                        ids: row.id
                    }
                }
                let delfromData = {
                    title: tit,
                    num: 0,
                    url: `product/product/rule/delete`,
                    method: 'DELETE',
                    ids: data
                }
                this.$modalSure(delfromData).then((res) => {
                    this.$Message.success(res.msg)
                    this.getDataList()
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            addAttr () {
                this.$refs.addattr.modal = true
            },
            // 编辑
            edit (row) {
                this.$refs.addattr.modal = true
                this.$refs.addattr.getIofo(row)
            },
            // 列表；
            getDataList () {
                this.loading = true
                ruleListApi(this.artFrom).then(res => {
                    let data = res.data
                    this.tableList = data.list
                    this.total = res.data.count
                    this.loading = false
                }).catch(res => {
                    this.loading = false
                    this.$Message.error(res.msg)
                })
            },
            pageChange (status) {
                this.artFrom.page = status
                this.getDataList()
            },
            // 表格搜索
            userSearchs () {
                this.artFrom.page = 1
                this.getDataList()
            }
        }
    }
</script>

<style scoped>

</style>
