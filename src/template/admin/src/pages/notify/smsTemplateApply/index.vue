<template>
    <div>
        <div class="i-layout-page-header">
            <div class="i-layout-page-header">
                <router-link :to="{path:'/admin/setting/sms/sms_config/index'}"><Button icon="ios-arrow-back" size="small"  class="mr20">返回</Button></router-link>
                <span class="ivu-page-header-title mr20">{{$route.meta.title}}</span>
            </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <Form ref="levelFrom" :model="levelFrom"  :label-width="labelWidth" :label-position="labelPosition" @submit.native.prevent>
                <Row type="flex" :gutter="24" v-if="$route.path === '/admin/setting/sms/sms_template_apply/index'">
<!--                    <Col v-bind="grid">-->
<!--                        <FormItem label="模板类型：">-->
<!--                            <Select v-model="levelFrom.type" placeholder="请选择" clearable  @on-change="userSearchs">-->
<!--                                <Option value="1">验证码</Option>-->
<!--                                <Option value="2">通知</Option>-->
<!--                                <Option value="3">推广</Option>-->
<!--                            </Select>-->
<!--                        </FormItem>-->
<!--                    </Col>-->
<!--                    <Col v-bind="grid">-->
<!--                        <FormItem label="模板状态：">-->
<!--                            <Select v-model="levelFrom.status" placeholder="请选择" clearable  @on-change="userSearchs">-->
<!--                                <Option value="1">可用</Option>-->
<!--                                <Option value="0">不可用</Option>-->
<!--                            </Select>-->
<!--                        </FormItem>-->
<!--                    </Col>-->
<!--                    <Col v-bind="grid">-->
<!--                        <FormItem label="模板名称：" >-->
<!--                            <Input search enter-button  v-model="levelFrom.title" placeholder="请输入模板名称" @on-search="userSearchs"/>-->
<!--                        </FormItem>-->
<!--                    </Col>-->
                    <Col span="24">
                        <Button type="primary"  icon="md-add" @click="add">申请模板</Button>
                    </Col>
                </Row>
                <Row type="flex" :gutter="24" v-else>
                    <Col v-bind="grid">
                        <FormItem label="是否拥有：">
                            <Select v-model="levelFrom.is_have" placeholder="请选择" clearable  @on-change="userSearchs">
                                <Option value="1">有</Option>
                                <Option value="0">没有</Option>
                            </Select>
                        </FormItem>
                    </Col>
                </Row>
            </Form>
            <Table :columns="columns1" :data="levelLists" ref="table" class="mt25"
                   :loading="loading"
                   no-userFrom-text="暂无数据"
                   no-filtered-userFrom-text="暂无筛选结果">
                <template slot-scope="{ row, index }" slot="status">
                    <span v-show="row.status === 1">可用</span>
                    <span v-show="row.status === 0">不可用</span>
                </template>
                <template slot-scope="{ row, index }" slot="is_have" v-if="$route.path === '/admin/setting/sms/sms_template_apply/commons'">
                    <span v-show="row.status === 1">有</span>
                    <span v-show="row.status === 0">没有</span>
                </template>
            </Table>
            <div class="acea-row row-right page">
                <Page :total="total" :current="levelFrom.page" show-elevator show-total @on-change="pageChange"
                      :page-size="levelFrom.limit"/>
            </div>
        </Card>

        <!-- 新建表单-->
        <edit-from ref="edits" :FromData="FromData" @submitFail="submitFail"></edit-from>
    </div>
</template>
<script>
    import { mapState } from 'vuex';
    import { tempListApi, tempCreateApi, isLoginApi, serveInfoApi } from '@/api/setting';
    import editFrom from '@/components/from/from';
    export default {
        name: 'smsTemplateApply',
        components: { editFrom },
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
                columns1: [],
                levelFrom: {
                    type: '',
                    status: '',
                    title: '',
                    page: 1,
                    limit: 20
                },
                levelFrom2: {
                    is_have: '',
                    page: 1,
                    limit: 20
                },
                total: 0,
                FromData: null,
                delfromData: {},
                levelLists: []
            }
        },
        watch: {
            $route (to, from) {
                this.getList();
            }
        },
        created () {
            this.onIsLogin();
        },
        mounted () {
            serveInfoApi().then(res => {
                if (res.data.sms.open != 1) {
                    this.$router.push('/admin/setting/sms/sms_config/index?url=' + this.$route.path);
                }
            })
        },
        computed: {
            ...mapState('media', [
                'isMobile'
            ]),
            labelWidth () {
                return this.isMobile ? undefined : 75;
            },
            labelPosition () {
                return this.isMobile ? 'top' : 'right';
            }
        },
        methods: {
            // 查看是否登录
            onIsLogin () {
                this.spinShow = true;
                isLoginApi().then(async res => {
                    let data = res.data;
                    if (!data.status) {
                        this.$Message.warning('请先登录');
                        this.$router.push('/admin/setting/sms/sms_config/index?url=' + this.$route.path);
                    } else {
                        this.getList();
                    }
                }).catch(res => {
                    this.$Message.error(res.msg);
                })
            },
            // 等级列表
            getList () {
                this.loading = true;
                this.levelFrom.status = this.levelFrom.status || '';
                this.levelFrom.is_have = this.levelFrom.is_have || '';
                let data = {
                    data: this.$route.path === '/admin/setting/sms/sms_template_apply/index' ? this.levelFrom : this.levelFrom2,
                    url: this.$route.path === '/admin/setting/sms/sms_template_apply/index' ? 'serve/sms/temps' : 'notify/sms/public_temp'
                }
                let columns1 = [
                    {
                        title: 'ID',
                        key: 'id',
                        sortable: true,
                        width: 80
                    },
                    {
                        title: '模板ID',
                        key: 'templateid',
                        minWidth: 110
                    },
                    {
                        title: '模板名称',
                        key: 'title',
                        minWidth: 150
                    },
                    {
                        title: '模板内容',
                        key: 'content',
                        minWidth: 550
                    },
                    {
                        title: '模板类型',
                        key: 'type',
                        minWidth: 100
                    },
                    {
                        title: '模板状态',
                        slot: 'status',
                        minWidth: 100
                    }
                ];
                if (this.$route.path === '/admin/setting/sms/sms_template_apply/commons') {
                    this.columns1 = Object.assign([], columns1).slice(0, 6).concat([{
                        title: '是否拥有',
                        slot: 'is_have',
                        minWidth: 110
                    }]);
                } else {
                    this.columns1 = columns1;
                }
                tempListApi(data).then(async res => {
                    let data = res.data
                    this.levelLists = data.data;
                    this.total = data.count;
                    this.loading = false;
                }).catch(res => {
                    this.loading = false;
                    this.$Message.error(res.msg);
                })
            },
            pageChange (index) {
                this.levelFrom.page = index;
                this.getList();
            },
            // 添加
            add () {
                tempCreateApi().then(async res => {
                    this.FromData = res.data;
                    this.$refs.edits.modals = true;
                }).catch(res => {
                    this.$Message.error(res.msg);
                })
            },
            // 表格搜索
            userSearchs () {
                this.levelFrom.page = 1;
                this.getList();
            },
            // 修改成功
            submitFail () {
                this.getList();
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

