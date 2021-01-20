<template>
    <Modal v-model="modals" :z-index="1"  scrollable  footer-hide closable title="等级任务" :mask-closable="false" width="950" @on-cancel="handleReset">
        <Form ref="levelFrom" :model="levelFrom"  :label-width="labelWidth" :label-position="labelPosition" @submit.native.prevent>
            <Row type="flex" :gutter="24">
                <Col v-bind="grid">
                    <FormItem label="优惠券名称：" prop="status2" label-for="status2">
                        <Input search enter-button  v-model="levelFrom.name" placeholder="请输入优惠券名称" @on-search="userSearchs" style="width: 100%"/>
                    </FormItem>
                </Col>
            </Row>
        </Form>
        <Divider dashed />
        <Row type="flex">
            <Col v-bind="grid" class="mb15">
                <Button type="primary"  icon="md-add" @click="add">添加等级任务</Button>
            </Col>
            <Col span="24" class="userAlert">
                <Alert show-icon closable>添加等级任务,任务类型中的{$num}会自动替换成限定数量+系统预设的单位生成任务名</Alert>
            </Col>
        </Row>
        <Divider dashed />
        <Table :columns="columns1" :data="levelLists" ref="table"
               :loading="loading"
               no-userFrom-text="暂无数据"
               no-filtered-userFrom-text="暂无筛选结果">
            <template slot-scope="{ row, index }" slot="is_shows">
                <i-switch v-model="row.is_show" :value="row.is_show" :true-value="1" :false-value="0"  size="large" @on-change="onchangeIsShow(row)">
                    <span slot="open">显示</span>
                    <span slot="close">隐藏</span>
                </i-switch>
            </template>
            <template slot-scope="{ row, index }" slot="is_musts">
                <i-switch v-model="row.is_must" :value="row.is_must" :true-value="1" :false-value="0"  size="large" @on-change="onchangeIsMust(row)">
                    <span slot="open">全部完成</span>
                    <span slot="close">达成其一</span>
                </i-switch>
            </template>
            <template slot-scope="{ row, index }" slot="action">
                <a @click="edit(row)">编辑  | </a>
                <a @click="del(row,'删除等级任务',index)">  删除</a>
            </template>
        </Table>
        <div class="acea-row row-right page">
            <Page :total="total" show-elevator show-total @on-change="pageChange"
                  :page-size="levelFrom.limit"/>
        </div>
        <!-- 新建 编辑表单-->
        <edit-from ref="edits" :FromData="FromData" @submitFail="submitFail" :titleType="titleType"></edit-from>
    </Modal>
</template>

<script>
    import { mapState, mapMutations } from 'vuex'
    import { taskListApi, setTaskShowApi, setTaskMustApi, createTaskApi } from '@/api/user'
    import editFrom from '@/components/from/from'
    export default {
        name: 'sendOut',
        components: { editFrom },
        data () {
            return {
                // levelIds: this.levelId,
                grid: {
                    xl: 10,
                    lg: 10,
                    md: 12,
                    sm: 24,
                    xs: 24
                },
                modals: false,
                levelFrom: {
                    is_show: '',
                    name: '',
                    page: 1,
                    limit: 20
                },
                total: 0,
                levelLists: [],
                loading: false,
                columns1: [
                    {
                        title: 'ID',
                        key: 'id',
                        sortable: true,
                        width: 80
                    },
                    {
                        title: '等级名称',
                        key: 'level_name',
                        minWidth: 100
                    },
                    {
                        title: '任务名称',
                        key: 'name',
                        minWidth: 120
                    },
                    {
                        title: '排序',
                        sort: 'grade',
                        sortable: true,
                        minWidth: 100
                    },
                    {
                        title: '是否显示',
                        slot: 'is_shows',
                        minWidth: 110
                    },
                    {
                        title: '务必达成',
                        slot: 'is_musts',
                        minWidth: 135
                    },
                    {
                        title: '任务说明',
                        key: 'illustrate',
                        minWidth: 120
                    },
                    {
                        title: '操作',
                        slot: 'action',
                        fixed: 'right',
                        minWidth: 120
                    }
                ],
                FromData: null,
                ids: 0,
                modalTitleSs: '',
                titleType: 'task'
            }
        },
        computed: {
            ...mapState('media', [
                'isMobile'
            ]),
            ...mapState('userLevel', [
                'levelId'
            ]),
            labelWidth () {
                return this.isMobile ? undefined : 75
            },
            labelPosition () {
                return this.isMobile ? 'top' : 'right'
            }
        },
        methods: {
            ...mapMutations('userLevel', [
                'getTaskId',
                'getlevelId'
            ]),
            // 添加
            add () {
                this.ids = ''
                this.getFrom()
            },
            // 新建 编辑表单
            getFrom () {
                let data = {
                    id: this.ids,
                    level_id: this.levelId
                }
                createTaskApi(data).then(async res => {
                    this.FromData = res.data
                    this.$refs.edits.modals = true
                    this.getTaskId(this.ids)
                    // this.getlevelId(this.levelId);
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 编辑
            edit (row) {
                this.ids = row.id
                this.getFrom()
            },
            // 关闭模态框
            handleReset () {
                this.modals = false
            },
            // 表格搜索
            userSearchs () {
                this.getList()
            },
            // 任务列表
            getList () {
                this.loading = true
                taskListApi(this.levelId, this.levelFrom).then(async res => {
                    let data = res.data
                    this.levelLists = data.list
                    this.total = res.data.count
                    this.loading = false
                }).catch(res => {
                    this.loading = false
                    this.$Message.error(res.msg)
                })
            },
            pageChange (index) {
                this.levelFrom.page = index
                this.getList()
            },
            // 修改显示隐藏
            onchangeIsShow (row) {
                let data = {
                    id: row.id,
                    is_show: row.is_show
                }
                setTaskShowApi(data).then(async res => {
                    this.$Message.success(res.msg)
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 设置任务是否达成
            onchangeIsMust (row) {
                let data = {
                    id: row.id,
                    is_must: row.is_must
                }
                setTaskMustApi(data).then(async res => {
                    this.$Message.success(res.msg)
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 新建编辑提交成功
            submitFail () {
                this.getList()
            },
            // 删除任务
            del (row, tit, num) {
                let delfromData = {
                    title: tit,
                    num: num,
                    url: `user/user_level/delete_task/${row.id}`,
                    method: 'DELETE',
                    ids: ''
                }
                this.$modalSure(delfromData).then((res) => {
                    this.$Message.success(res.msg)
                    this.levelLists.splice(num, 1)
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            }
        }
    }
</script>

<style scoped>

</style>
