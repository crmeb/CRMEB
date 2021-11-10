<template>
    <div>
        <div class="i-layout-page-header">
          <div class="i-layout-page-header">
            <span class="ivu-page-header-title">{{$route.meta.title}}</span>
          </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <div class="backs" @click="goBack">
                <Icon type="ios-folder-outline" class="mr5"/><span>返回上级</span>
            </div>
            <Table ref="selection" :columns="columns4" :data="tabList" :loading="loading"
                   no-data-text="暂无数据" highlight-row class="mt20" @on-current-change="currentChange"
                   no-filtered-data-text="暂无筛选结果">
                <template slot-scope="{ row }" slot="filename">
                    <Icon type="ios-folder-outline" v-if="row.isDir" class="mr5"/>
                    <Icon type="ios-document-outline" v-else class="mr5"/>
                    <span>{{row.filename}}</span>
                </template>
                <template slot-scope="{ row }" slot="isWritable">
                    <span v-text="row.isWritable?'是':'否'"></span>
                </template>
                <template slot-scope="{ row, index }" slot="action">
                    <a @click="open(row)"   v-if="row.isDir">打开</a>
                    <a @click="edit(row)"   v-else>编辑</a>
                </template>
            </Table>
        </Card>
        <Modal v-model="modals"  scrollable  footer-hide closable :title="title" :mask-closable="false"  width="900">
            <Button type="primary" id="savefile" class="mr5 mb15" @click="savefile">保存</Button>
            <Button id="undo" class="mr5 mb15" @click="undofile">撤销</Button>
<!--            <Button id="redo" class="mr5 mb15" @click="redofile">回退</Button>-->
<!--            <Button id="refresh" class="mb15" @click="refreshfile">刷新</Button>-->
            <textarea ref="mycode" class="codesql public_text" v-model="code"></textarea>
            <Spin size="large" fix v-if="spinShow"></Spin>
        </Modal>
    </div>
</template>

<script>
    import { opendirListApi, openfileApi, savefileApi } from '@/api/system'
    import CodeMirror from 'codemirror/lib/codemirror'
    import 'codemirror/theme/ambiance.css'
    require('codemirror/mode/javascript/javascript')
    export default {
        name: 'opendir',
        data () {
            return {
                code: '',
                modals: false,
                spinShow: false,
                loading: false,
                tabList: [],
                columns4: [
                    {
                        title: '文件/文件夹名',
                        slot: 'filename',
                        minWidth: 150,
                        back: '返回上级'
                    },
                    {
                        title: '文件/文件夹路径',
                        key: 'real_path',
                        minWidth: 150
                    },
                    {
                        title: '文件/文件夹大小',
                        key: 'size',
                        minWidth: 100
                    },
                    {
                        title: '是否可写',
                        slot: 'isWritable',
                        minWidth: 100
                    },
                    {
                        title: '更新时间',
                        key: 'mtime',
                        minWidth: 150
                    },
                    {
                        title: 'Action',
                        slot: 'action',
                        minWidth: 150
                    }
                ],
                formItem: {
                    dir: '',
                    superior: 0,
                    filedir: ''
                },
                rows: {},
                pathname: '',
                title: ''
            }
        },
        mounted () {
            this.editor = CodeMirror.fromTextArea(this.$refs.mycode, {
                value: 'http://www.crmeb.com', // 文本域默认显示的文本
                mode: 'text/javascript',
                theme: 'ambiance', // CSS样式选择
                indentUnit: 4, // 缩进单位，默认2
                smartIndent: true, // 是否智能缩进
                tabSize: 4, // Tab缩进，默认4
                readOnly: false, // 是否只读，默认false
                showCursorWhenSelecting: true,
                lineNumbers: true // 是否显示行号
            })
        },
        created () {
            this.getList()
        },
        methods: {
            // 点击行
            currentChange (currentRow) {
                if (currentRow.isDir) {
                    this.open(currentRow)
                } else {
                    this.edit(currentRow)
                }
            },
            // 列表
            getList () {
                this.loading = true
                opendirListApi(this.formItem).then(async res => {
                    let data = res.data
                    this.tabList = data.list
                    this.dir = data.dir
                    this.loading = false
                }).catch(res => {
                    this.loading = false
                    this.$Message.error(res.msg)
                })
            },
            // 返回上级
            goBack () {
                this.formItem = {
                    dir: this.dir,
                    superior: 1,
                    filedir: ''
                }
                this.getList()
            },
            // 打开
            open (row) {
                this.rows = row
                this.formItem = {
                    dir: row.path,
                    superior: 0,
                    filedir: row.filename
                }
                this.getList()
            },
            // 编辑
            edit (row) {
                this.modals = true
                this.spinShow = true
                this.pathname = row.pathname
                this.title = row.filename
                openfileApi(row.pathname).then(async res => {
                    let data = res.data
                    this.code = data.content
                    this.editor.setValue(this.code)
                    this.spinShow = false
                }).catch(res => {
                    this.spinShow = false
                    this.$Message.error(res.msg)
                })
            },
            // 保存
            savefile () {
                let data = {
                    comment: this.editor.getValue(),
                    filepath: this.pathname
                }
                savefileApi(data).then(async res => {
                    this.$Message.success(res.msg)
                    this.modals = false
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 撤销
            undofile () {
                this.editor.undo()
            },
            redofile () {
                this.editor.redo()
            },
            // 刷新
            refreshfile () {
                this.editor.refresh()
            }
        }
    }
</script>

<style scoped lang="stylus">
    .mt20
        >>>.ivu-icon-ios-folder-outline
           font-size 14px !important
        >>> .ivu-icon-ios-document-outline
           font-size 18px !important
        >>> .ivu-table-row
           cursor pointer
    .mr5
       margin-right 5px
    .backs
       cursor pointer
       display inline-block

</style>
