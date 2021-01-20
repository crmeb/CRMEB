<template>
    <div>
        <div class="i-layout-page-header">
          <div class="i-layout-page-header">
            <span class="ivu-page-header-title">{{$route.meta.title}}</span>
          </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt tableBox">
            <div slot="title">
                <span class="ivu-pl-8">数据库备份记录</span>
            </div>
            <Table ref="selection" :columns="columns4" :data="tabList" :loading="loading3"
                   no-data-text="暂无数据" highlight-row size="small"
                   no-filtered-data-text="暂无筛选结果">
                <template slot-scope="{ row, index }" slot="action">
                    <a @click="ImportFile(row)">导入</a>
                    <Divider type="vertical"/>
                    <a @click="del(row,'删除该备份',index)">删除</a>
                    <Divider type="vertical"/>
                    <a @click="download(row)">下载</a>
                </template>
            </Table>
        </Card>
        <Card  :bordered="false" dis-hover class="ivu-mt tableBox">
            <div slot="title">
                <span class="ivu-pl-8 mr10">数据库表列表</span>
                <Button type="primary" class="mr10" @click="getBackup">备份</Button>
                <Button type="primary" class="mr10" @click="getOptimize">优化表</Button>
                <Button type="primary" class="mr10" @click="getRepair">修复表</Button>
                <Button type="primary" class="mr10"  @click="exportData(1)">导出文件</Button>
            </div>
            <Table ref="selection" :columns="columns" :data="tabList2" :loading="loading"
                   highlight-row  no-data-text="暂无数据"  @on-selection-change="onSelectTab" size="small"
                   no-filtered-data-text="暂无筛选结果">
                <template slot-scope="{ row, index }" slot="action">
                    <a @click="Info(row)">详情</a>
                </template>
            </Table>
        </Card>
        <!-- 详情模态框-->
        <Modal v-model="modals" class="tableBox" scrollable  footer-hide closable :title="'[ '+rows.name+' ]'+rows.comment" :mask-closable="false"  width="750">
            <Table ref="selection" :columns="columns2" :data="tabList3" :loading="loading2"
                   no-data-text="暂无数据" highlight-row max-height="600" size="small"
                   no-filtered-data-text="暂无筛选结果">
            </Table>
        </Modal>
    </div>
</template>

<script>
    import { backupListApi, backupReadListApi, backupBackupApi, backupOptimizeApi, backupRepairApi, filesListApi, filesDownloadApi, filesImportApi } from '@/api/system'
    import Setting from '@/setting'
    import { getCookies } from '@/libs/util'
    export default {
        name: 'systemDatabackup',
        data () {
            return {
                modals: false,
                loading: false,
                tabList: [],
                columns4: [
                    {
                        title: '备份名称',
                        key: 'filename',
                        minWidth: 200,
                        sortable: true
                    },
                    {
                        title: 'part',
                        key: 'part',
                        minWidth: 100
                    },
                    {
                        title: '大小',
                        key: 'size',
                        minWidth: 150
                    },
                    {
                        title: 'compress',
                        key: 'compress',
                        minWidth: 100
                    },
                    {
                        title: '时间',
                        key: 'backtime',
                        minWidth: 150
                    },
                    {
                        title: '操作',
                        slot: 'action',
                        fixed: 'right',
                        minWidth: 150
                    }
                ],
                tabList2: [],
                columns: [
                    {
                        type: 'selection',
                        width: 60,
                        align: 'center'
                    },
                    {
                        title: '表名称',
                        key: 'name',
                        minWidth: 200,
                        sortable: true
                    },
                    {
                        title: '备注',
                        key: 'comment',
                        minWidth: 200
                    },
                    {
                        title: '类型',
                        key: 'engine',
                        minWidth: 130,
                        sortable: true
                    },
                    {
                        title: '大小',
                        key: 'data_length',
                        minWidth: 130,
                        sortable: true
                    },
                    {
                        title: '更新时间',
                        key: 'update_time',
                        minWidth: 150,
                        sortable: true
                    },
                    {
                        title: '行数',
                        key: 'rows',
                        minWidth: 100,
                        sortable: true
                    },
                    {
                        title: '操作',
                        slot: 'action',
                        fixed: 'right',
                        minWidth: 150
                    }
                ],
                selectionList: [],
                tabList3: [],
                columns2: [
                    {
                        title: '字段名',
                        key: 'COLUMN_NAME'
                    },
                    {
                        title: '数据类型',
                        key: 'COLUMN_TYPE'
                    },
                    {
                        title: '默认值',
                        key: 'COLUMN_DEFAULT'
                    },
                    {
                        title: '允许非空',
                        key: 'IS_NULLABLE'
                    },
                    {
                        title: '自动递增',
                        key: 'EXTRA'
                    },
                    {
                        title: '备注',
                        key: 'COLUMN_COMMENT'
                    }
                ],
                rows: {},
                dataList: {},
                loading2: false,
                loading3: false,
                header: {},
                Token: ''
            }
        },
        computed: {
            fileUrl () {
                const search = '/adminapi/'
                const start = Setting.apiBaseURL.indexOf(search)
                return Setting.apiBaseURL.substring(0, start)// 截取字符串
            }
        },
        created () {
            this.getToken()
            this.getList()
            this.getfileList()
        },
        methods: {
            // 导入
            ImportFile (row) {
                filesImportApi({
                    part: row.part,
                    time: row.time
                }).then(async res => {
                    this.$Message.success(res.msg)
                    this.getfileList()
                }).catch(res => {
                    this.loading = false
                    this.$Message.error(res.msg)
                })
            },
            // 删除备份记录表
            del (row, tit, num) {
                let delfromData = {
                    title: tit,
                    num: num,
                    url: `system/backup/del_file`,
                    method: 'DELETE',
                    ids: {
                        filename: row.time
                    }
                }
                this.$modalSure(delfromData).then((res) => {
                    this.$Message.success(res.msg)
                    this.tabList.splice(num, 1)
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 下载备份记录表
            // download (row) {
            //     let data = {
            //         time: row.time
            //     }
            //     filesDownloadApi(data).then(async res => {
            //         let blob = new Blob([res], { type: 'application/vnd.ms-excel' }); // res就是接口返回的文件流了
            //         let objectUrl = URL.createObjectURL(blob);
            //         window.location.href = objectUrl;
            //         console.log(res)
            //         // this.$Message.success(res.msg);
            //     }).catch(res => {
            //         console.log(res)
            //         // this.$Message.error(res.msg);
            //     })
            // },
            // download (row) {
            //     var elemIF = document.createElement('iframe')
            //     elemIF.src = 'backup/download?time=' + row.time
            //     elemIF.style.display = 'none'
            //     document.body.appendChild(elemIF)
            // },
            // download (row) {
            //     window.open(this.fileUrl + row.filepath);
            // },
            // 上传头部token
            getToken () {
                this.Token = getCookies('token')
            },
            download (row) {
                let data = {
                    time: row.time
                }
                filesDownloadApi(data).then(async res => {
                    // let blob = new Blob([res], { type: 'application/vnd.ms-excel' }); // res就是接口返回的文件流了
                    let blob = new Blob([res], { type: 'application/zip' })
                    let objectUrl = URL.createObjectURL(blob)
                    console.log(objectUrl)
                    window.location.href = objectUrl
                    // this.$Message.success(res.msg);
                }).catch(res => {
                    console.log(res)
                    // this.$Message.error(res.msg);
                })
            },
            // async download (row) {
            //     try {
            //         let data = {
            //             time: row.time
            //         }
            //         await filesDownloadApi(data).then(async res => {
            //             let blob = new Blob([res], { type: 'application/vnd.ms-excel' }); // res就是接口返回的文件流了
            //             let objectUrl = URL.createObjectURL(blob);
            //             window.location.href = objectUrl;
            //             // this.$Message.success(res.msg);
            //         }).catch(res => {
            //             console.log(res)
            //             // this.$Message.error(res.msg);
            //         })
            //     } catch (err) {
            //         console.log(err)
            //     }
            // },
            // 导出备份记录表
            exportData () {
                const columns = this.columns.slice(1, 7)
                this.$refs.selection.exportCsv({
                    filename: '导出',
                    columns: columns,
                    data: this.tabList2
                })
            },
            // 全选
            onSelectTab (selection) {
                this.selectionList = selection
                let tables = []
                this.selectionList.map((item) => {
                    tables.push(item.name)
                })
                this.dataList = {
                    tables: tables.join(',')
                }
            },
            // 备份表
            getBackup () {
                if (this.selectionList.length === 0) {
                    return this.$Message.warning('请选择表')
                }
                backupBackupApi(this.dataList).then(async res => {
                    this.$Message.success(res.msg)
                    this.getfileList()
                }).catch(res => {
                    this.loading = false
                    this.$Message.error(res.msg)
                })
            },
            // 备份记录表列表
            getfileList () {
                this.loading3 = true
                filesListApi().then(async res => {
                    let data = res.data
                    this.tabList = data.list
                    this.loading3 = false
                }).catch(res => {
                    this.loading3 = false
                    this.$Message.error(res.msg)
                })
            },
            // 优化表
            getOptimize () {
                if (this.selectionList.length === 0) {
                    return this.$Message.warning('请选择表')
                }
                backupOptimizeApi(this.dataList).then(async res => {
                    this.$Message.success(res.msg)
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 修复表
            getRepair () {
                if (this.selectionList.length === 0) {
                    return this.$Message.warning('请选择表')
                }
                backupRepairApi(this.dataList).then(async res => {
                    this.$Message.success(res.msg)
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 数据库列表
            getList () {
                this.loading = true
                backupListApi().then(async res => {
                    let data = res.data
                    this.tabList2 = data.list
                    this.loading = false
                }).catch(res => {
                    this.loading = false
                    this.$Message.error(res.msg)
                })
            },
            // 详情
            Info (row) {
                this.rows = row
                this.modals = true
                this.loading2 = true
                let data = {
                    tablename: row.name
                }
                backupReadListApi(data).then(async res => {
                    let data = res.data
                    this.tabList3 = data.list
                    this.loading2 = false
                }).catch(res => {
                    this.loading2 = false
                    this.$Message.error(res.msg)
                })
            }
        }
    }
</script>

<style scoped lang="stylus">
    .tableBox >>> .ivu-table-header table
       border none !important
</style>
