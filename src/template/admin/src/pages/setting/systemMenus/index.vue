<template>
    <div>
        <div class="i-layout-page-header">
          <div class="i-layout-page-header">
            <span class="ivu-page-header-title">{{$route.meta.title}}</span>
          </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <Form ref="roleData" :model="roleData" :label-width="labelWidth" :label-position="labelPosition" @submit.native.prevent>
                <Row type="flex" :gutter="24">
                    <Col v-bind="grid">
                        <FormItem label="规则状态：">
                            <Select v-model="roleData.is_show" placeholder="请选择" clearable @on-change="getData">
                                <Option value='1'>显示</Option>
                                <Option value='0'>不显示</Option>
                            </Select>
                        </FormItem>
                    </Col>
                    <Col v-bind="grid">
                        <FormItem label="按钮名称：" prop="status2" label-for="status2">
                            <Input v-model="roleData.keyword" search enter-button placeholder="请输入按钮名称" @on-search="getData"/>
                        </FormItem>
                    </Col>
                </Row>
                <Row type="flex">
                    <Col v-bind="grid">
                        <Button v-auth="['setting-system_menus-add']" type="primary" @click="menusAdd('添加规则')"  icon="md-add">添加规则</Button>
                    </Col>
                </Row>
            </Form>
            <vxe-table
                    :border="false"
                    class="vxeTable mt25"
                    highlight-hover-row
                    highlight-current-row
                    :loading="loading" ref="xTable"
                    header-row-class-name="false"
                    :tree-config="{children: 'children'}"
                    :data="tableData">
                <vxe-table-column field="id" title="ID"  tooltip min-width="70"></vxe-table-column>
                <vxe-table-column field="menu_name" tree-node title="按钮名称" min-width="200"></vxe-table-column>
                <vxe-table-column field="api_url" title="接口路径"  min-width="150">
                    <template v-slot="{ row }">
                        <span>{{row.methods?'['+row.methods+']  '+   row.api_url:row.api_url}}</span>
                    </template>
                </vxe-table-column>
                <vxe-table-column field="unique_auth" title="前端权限" min-width="300"></vxe-table-column>
                <vxe-table-column field="menu_path" title="页面路由" min-width="240" tooltip="true"></vxe-table-column>
                <vxe-table-column field="flag" title="规则状态" min-width="120">
                    <template v-slot="{ row }">
                        <i-switch v-model="row.is_show" :value="row.is_show" :true-value="1" :false-value="0" @on-change="onchangeIsShow(row)" size="large">
                            <span slot="open">显示</span>
                            <span slot="close">隐藏</span>
                        </i-switch>
                    </template>
                </vxe-table-column>
                <vxe-table-column field="date" title="操作" align="center"  width="200" fixed="right">
                    <template v-slot="{ row,index }">
                        <span v-auth="['setting-system_menus-add']">
                            <a @click="addE(row,'添加子菜单')" v-if="row.auth_type === 1">添加子菜单</a>
                            <a @click="addE(row,'添加规则')" v-else>添加规则</a>
                        </span>
                        <Divider type="vertical"/>
                        <a @click="edit(row,'编辑')">编辑</a>
                        <Divider type="vertical"/>
                        <a @click="del(row,'删除规则')">删除</a>
                    </template>
                </vxe-table-column>
            </vxe-table>
        </Card>
         <menus-from   :formValidate="formValidate" :titleFrom="titleFrom"  @getList="getList" ref="menusFrom" @clearFrom="clearFrom"></menus-from>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    import { getTable, menusDetailsApi, isShowApi, editMenus } from '@/api/systemMenus'
    import formCreate from '@form-create/iview'
    import menusFrom from './components/menusFrom'
    export default {
        name: 'systemMenus',
        data () {
            return {
                spinShow: false,
                grid: {
                    xl: 7,
                    lg: 7,
                    md: 12,
                    sm: 24,
                    xs: 24
                },
                roleData: {
                    is_show: '',
                    keyword: ''
                },
                loading: false,
                tableData: [],
                FromData: null,
                icons: '',
                formValidate: {},
                titleFrom: '',
                modalTitleSs: ''
            }
        },
        components: { menusFrom, formCreate: formCreate.$form() },
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
            // this.formValidate.auth_type = '1';
            this.getData()
        },
        methods: {
            // 修改规则状态
            onchangeIsShow (row) {
                let data = {
                    id: row.id,
                    is_show: row.is_show
                }
                isShowApi(data).then(async res => {
                    this.$Message.success(res.msg)
                    this.$store.dispatch('menus/getMenusNavList')
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 请求列表
            getList () {
                this.formValidate = Object.assign({}, this.$options.data().formValidate)
                this.getData()
            },
            // 清除表单数据
            clearFrom () {
                this.formValidate = Object.assign({}, this.$options.data().formValidate)
            },
            // 添加子菜单
            addE (row, title) {
              let pid = row.id.toString();
                if (pid) {
                  menusDetailsApi(row.id).then(async res => {
                    this.formValidate.path = res.data.path;
                    this.formValidate.path.push(row.id);
                    this.formValidate.pid = pid;
                    this.$refs.menusFrom.modals = true
                    this.$refs.menusFrom.valids = false
                    this.titleFrom = title
                    this.formValidate.auth_type = 1
                    this.formValidate.is_show = '0'
                  }).catch(res => {
                    this.$Message.error(res.msg)
                  })
                } else {
                  this.formValidate.pid = pid;
                  this.$refs.menusFrom.modals = true
                  this.$refs.menusFrom.valids = false
                  this.titleFrom = title
                  this.formValidate.auth_type = 1
                  this.formValidate.is_show = '0'
                }
            },
            // 删除
            del (row, tit) {
                let delfromData = {
                    title: tit,
                    url: `/setting/menus/${row.id}`,
                    method: 'DELETE',
                    ids: ''
                }
                this.$modalSure(delfromData).then((res) => {
                    this.$Message.success(res.msg)
                    this.getData()
                    this.$store.dispatch('menus/getMenusNavList')
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 规则详情
            menusDetails (id) {
                menusDetailsApi(id).then(async res => {
                    this.formValidate = res.data
                    this.$refs.menusFrom.modals = true
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 编辑
            edit (row, title, index) {
                this.menusDetails(row.id)
                this.titleFrom = title
                this.$refs.menusFrom.valids = false
                this.$refs.menusFrom.getAddFrom(row.id);
            },
            // 添加
            menusAdd (title) {
                this.$refs.menusFrom.modals = true
                this.$refs.menusFrom.valids = false
                // this.formValidate = Object.assign(this.$data, this.$options.formValidate());
                this.titleFrom = title
                this.formValidate.auth_type = '1'
            },
            // 新增页面表单
            // getAddFrom () {
            //     this.spinShow = true;
            //     addMenus(this.roleData).then(async res => {
            //         this.FromData = res.data;
            //         this.$refs.menusFrom.modals = true;
            //         this.spinShow = false;
            //     }).catch(res => {
            //         this.spinShow = false;
            //         this.$Message.error(res.msg);
            //     })
            // },
            // 列表
            getData () {
                this.loading = true
                this.roleData.is_show = this.roleData.is_show || ''
                getTable(this.roleData).then(async res => {
                    this.tableData = res.data
                    this.loading = false
                }).catch(res => {
                    this.loading = false
                    this.$Message.error(res.msg)
                })
            },
            // 关闭按钮
            cancel () {
                this.$emit('onCancel')
            }
        }
    }
</script>

<style scoped lang="stylus">
    .vxeTable
      >>> .vxe-table--header-wrapper
        background #fff !important
</style>
