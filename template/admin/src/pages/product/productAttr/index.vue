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
            <Table class="mt25" ref="table" :columns="columns4" :data="tableList"  :loading="loading"
                   highlight-row @on-select="handleSelectRow" @on-select-cancel="handleCancelRow"
                   @on-select-all="handleSelectAll" @on-select-all-cancel="handleSelectAll"
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
                selectedIds: new Set(),//选中合并项的id
                ids: ''
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
            //全选和取消全选时触发
            handleSelectAll (selection) {
                if (selection.length === 0) {
                    //获取table的数据；
                    let data = this.$refs.table.data;
                    data.forEach((item) => {
                        if (this.selectedIds.has(item.id)) {
                            this.selectedIds.delete(item.id)
                        }
                    })
                } else {
                    selection.forEach(item => {
                        this.selectedIds.add(item.id)
                    })
                }
                this.$nextTick(() => {//确保dom加载完毕
                    this.setChecked();
                });
            },
            //  选中某一行
            handleSelectRow (selection,row) {
                this.selectedIds.add(row.id);
                this.$nextTick(() => {//确保dom加载完毕
                    this.setChecked();
                });
            },
            //  取消某一行
            handleCancelRow (selection,row) {
                this.selectedIds.delete(row.id);
                this.$nextTick(() => {//确保dom加载完毕
                    this.setChecked();
                });
            },
            setChecked () {
                //将new Set()转化为数组
                this.ids = [...this.selectedIds].join(',');
                // 找到绑定的table的ref对应的dom，找到table的objData对象，objData保存的是当前页的数据
                let objData = this.$refs.table.objData
                for (let index in objData) {
                    if (this.selectedIds.has(objData[index].id)) {
                        objData[index]._isChecked = true;
                    }
                }
            },
            // 删除
            del (row, tit) {
                let data = {}
                if (tit === '批量删除规格') {
                    if (this.selectedIds.size === 0) return this.$Message.warning('请选择要删除的规格！')
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
                    this.$nextTick(() => {//确保dom加载完毕
                        this.setChecked();
                    });
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
