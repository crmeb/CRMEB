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
                    <Button v-auth="['admin-template']" type="primary"  icon="md-add" @click="add">添加模板</Button>
                </Col>
            </Row>
            <Table :columns="columns1" :data="list" ref="table" class="mt25"
                   :loading="loading" highlight-row
                   no-userFrom-text="暂无数据"
                   no-filtered-userFrom-text="暂无筛选结果">
                <template slot-scope="{ row, index }" slot="region">
                    <div class="font-blue">首页</div>
                </template>
                <template slot-scope="{ row, index }" slot="action">
                    <div style="display: inline-block" v-if="row.status != 1">
                        <a @click="setStatus(row,index)">设为首页</a>
                    </div>
                    <Divider type="vertical" v-if="row.status != 1"/>
                    <div style="display: inline-block" v-if="row.status || row.type">
                        <a @click="edit(row)">编辑</a>
                    </div>
                    <Divider type="vertical" v-if="row.status || row.type"/>
                    <template>
                        <Dropdown @on-click="changeMenu(row,index,$event)">
                            <a href="javascript:void(0)">更多
                                <Icon type="ios-arrow-down"></Icon>
                            </a>
                            <DropdownMenu slot="list">
                                <DropdownItem name="1" v-show="!row.type">设置默认数据</DropdownItem>
                                <DropdownItem name="2" v-show="!row.type">恢复默认数据</DropdownItem>
                                <DropdownItem name="3" v-show="row.id !=1">删除模板</DropdownItem>
                            </DropdownMenu>
                        </Dropdown>
                    </template>
                </template>
            </Table>
        </Card>
        <Modal v-model="isTemplate" scrollable  footer-hide closable title="开发移动端链接"  :z-index="1" width="500" @on-cancel="cancel">
            <div class="article-manager">
                <Card :bordered="false" dis-hover class="ivu-mt">
                    <Form ref="formItem" :model="formItem" :label-width="120" label-position="right" :rules="ruleValidate" @submit.native.prevent>
                        <Row type="flex" :gutter="24">
                            <Col span="24">
                                <Col v-bind="grid">
                                    <FormItem label="开发移动端链接：" prop="link" label-for="link">
                                        <Input v-model="formItem.link"  placeholder="http://localhost:8080"/>
                                    </FormItem>
                                </Col>
                            </Col>
                        </Row>
                        <Row type="flex">
                            <Col v-bind="grid">
                                <Button type="primary" class="ml20" @click="handleSubmit('formItem')" style="width: 100%">提交</Button>
                            </Col>
                        </Row>
                    </Form>
                </Card>
            </div>
        </Modal>
    </div>
</template>

<script>
    import { diyList, diyDel, setStatus, recovery, getDiyCreate, getRecovery } from '@/api/diy';
    import { getCookies, setCookies } from '@/libs/util'
    import { mapState } from 'vuex';
    export default {
        name: 'devise_list',
        data () {
            return {
                grid: {
                    xl: 18,
                    lg: 18,
                    md: 18,
                    sm: 24,
                    xs: 24
                },
                loading: false,
                columns1: [
                    {
                        title: '页面ID',
                        key: 'id',
                        minWidth: 120
                    },
                    {
                        title: '页面名称',
                        key: 'name',
                        minWidth:170
                    },
                    {
                        title: '页面类型',
                        key: 'template_name',
                        minWidth: 120
                    },
                    {
                        title: '添加时间',
                        key: 'add_time',
                        minWidth: 170
                    },
                    {
                        title: '更新时间',
                        key: 'update_time',
                        minWidth: 170
                    },
                    {
                        title: '操作',
                        slot: 'action',
                        fixed: 'right',
                        minWidth: 300
                    }
                ],
                list: [],
                isTemplate: false,
                formItem:{
                    id:0,
                    link:''
                },
                ruleValidate: {
                    link: [
                        { required: true, message: '请输入移动端链接', trigger: 'blur' }
                    ]
                },
            }
        },
        created () {
            this.formItem.link = getCookies('moveLink');
            this.getList()
        },
        methods: {
            cancel(){
                this.$refs['formItem'].resetFields()
            },
            handleSubmit (name) {
                this.$refs[name].validate((valid) => {
                    if (valid) {
                       setCookies('moveLink',this.formItem.link);
                       this.$router.push({ path: '/admin/setting/pages/diy', query: { id: this.formItem.id, type: 1 } });
                    } else {
                        return false
                    }
                })
            },
            changeMenu(row, index, name){
                switch(name){
                    case '1':
                        this.setDefault(row);
                        break;
                    case '2':
                        this.recovery(row);
                        break;
                    case '3':
                        this.del(row,'删除此模板',index);
                        break;
                    default:
                }
            },
            //设置默认数据
            setDefault(row){
                getRecovery(row.id).then(res=>{
                    this.$Message.success(res.msg);
                    this.getList()
                }).catch(err=>{
                    this.$Message.error(err.msg);
                })
            },
            // 添加
            add () {
                this.$modalForm(getDiyCreate()).then(() => this.getList())
            },
            // 获取列表
            getList () {
                this.loading = true
                diyList().then(res => {
                    this.loading = false
                    this.list = res.data.list
                })
            },
            // 编辑
            edit (row) {
                this.formItem.id = row.id;
                if(row.type){
                    this.isTemplate = true
                }else {
                    this.$router.push({ path: '/admin/setting/pages/diy', query: { id: row.id, type: 0 } });
                }
            },
            // 删除
            del (row, tit, num) {
                let delfromData = {
                    title: tit,
                    num: num,
                    url: `diy/del/${row.id}`,
                    method: 'DELETE',
                    ids: ''
                };
                this.$modalSure(delfromData).then((res) => {
                    this.getList()
                }).catch(res => {
                    this.$Message.error(res.msg);
                });
            },
            // 使用模板
            setStatus (row) {
                setStatus(row.id).then(res => {
                    this.$Message.success(res.msg);
                    this.getList()
                }).catch(error=>{
                    this.$Message.error(error.msg);
                })
            },
            recovery (row) {
                recovery(row.id).then(res => {
                    this.$Message.success(res.msg);
                    this.getList()
                })
            }
        }
    }
</script>

<style scoped>

</style>
