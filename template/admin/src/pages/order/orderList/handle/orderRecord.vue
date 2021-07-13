<template>
    <Modal v-model="modals"  scrollable title="订单记录" width="700" class="order_box" footer-hide>
        <Card :bordered="false" dis-hover>
            <Table :columns="columns" border  :data="recordData" :loading="loading" no-data-text="暂无数据"
                   highlight-row   no-filtered-data-text="暂无筛选结果"></Table>
<!--            <div class="acea-row row-right page">-->
<!--                <Page :total="recordData.length" show-elevator show-total @on-change="pageChange"-->
<!--                      :page-size="page.limit"/>-->
<!--            </div>-->
        </Card>
    </Modal>
</template>

<script>
    import { getOrderRecord } from '@/api/order'
    export default {
        name: 'orderRecord',
        data () {
            return {
                modals: false,
                loading: false,
                recordData: [],
                page: {
                    page: 1, // 当前页
                    limit: 10 // 每页显示条数
                },
                columns: [
                    {
                        title: '订单ID',
                        key: 'oid',
                        align: 'center',
                        minWidth: 40
                    },
                    {
                        title: '操作记录',
                        key: 'change_message',
                        align: 'center',
                        minWidth: 280
                    },
                    {
                        title: '操作时间',
                        key: 'change_time',
                        align: 'center',
                        minWidth: 100
                    }
                ]
            }
        },
        methods: {
            pageChange (index) {
                this.page.pageNum = index
                this.getList()
            },
            getList (id) {
                let data = {
                    id: id,
                    datas: this.page
                }
                this.loading = true
                getOrderRecord(data).then(async res => {
                    this.recordData = res.data
                    this.loading = false
                }).catch(res => {
                    this.loading = false
                    this.$Message.error(res.msg)
                })
            }
        }
    }
</script>

<style scoped lang="stylus">
  .ivu-table-wrapper
    border-left: 1px solid #dcdee2;
    border-top: 1px solid #dcdee2;
  .order_box >>> .ivu-table th{background: #f8f8f9 !important;}
</style>
