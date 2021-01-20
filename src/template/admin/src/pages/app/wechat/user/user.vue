<template>
    <div>
        <div class="i-layout-page-header">
          <div class="i-layout-page-header">
            <span class="ivu-page-header-title">{{$route.meta.title}}</span>
          </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <div class="table_box">
                <Form ref="formValidate" :model="formValidate" :label-width="labelWidth" :label-position="labelPosition" class="tabform" @submit.native.prevent>
                    <Row :gutter="24" type="flex" justify="end">
                        <Col span="24" class="ivu-text-left">
                            <FormItem :label="fromList.title+'：'">
                                <RadioGroup type="button" v-model="formValidate.data" class="mr15"
                                            @on-change="selectChange(formValidate.data)">
                                    <Radio :label="itemn.val" v-for="(itemn,indexn) in fromList.fromTxt" :key="indexn">
                                        {{itemn.text}}
                                    </Radio>
                                </RadioGroup>
                                <DatePicker :editable="false" @on-change="onchangeTime" :value="timeVal" format="yyyy/MM/dd"
                                            type="daterange" placement="bottom-end" placeholder="自定义时间"
                                            style="width: 200px;"></DatePicker>
                            </FormItem>
                        </Col>
                        <Col span="24" class="ivu-text-left">
                            <FormItem label="用户分组：">
                                <RadioGroup type="button" v-model="formValidate.groupid">
                                    <Radio :label="item.id" v-for="(item, index) in groupList" :key="index">{{item.name}}</Radio>
                                </RadioGroup>
                            </FormItem>
                        </Col>
                        <Col span="24" class="ivu-text-left">
                            <FormItem label="用户标签：">
                                <TagSelect v-model="tagidList">
                                    <TagSelectOption :name="item.id" v-for="(item, index) in tagList" :key="index">{{item.name}}</TagSelectOption>
                                </TagSelect>
                            </FormItem>
                        </Col>
                        <Col span="24" class="ivu-text-left">
                            <Col :xl="7" :lg="12" :md="12" :sm="24" :xs="24">
                                <FormItem label="用户名称：">
                                    <Input  placeholder="请输入用户名称" v-model="formValidate.nickname" class="perW90"></Input>
                                </FormItem>
                            </Col>
                            <Col :xl="7" :lg="12" :md="12" :sm="24" :xs="24" class="sex_box">
                                <FormItem label="性别：">
                                    <Select v-model="formValidate.sex" style="width: 90%;" clearable>
                                        <Option value="1">男</Option>
                                        <Option value="2">女</Option>
                                        <Option value="0">保密</Option>
                                    </Select>
                                </FormItem>
                            </Col>
                            <Col :xl="7" :lg="12" :md="12" :sm="24" :xs="24" class="subscribe_box">
                                <FormItem label="是否关注公众号：">
                                    <Select v-model="formValidate.subscribe" style="width: 90%;" clearable>
                                        <Option value="1">是</Option>
                                        <Option value="0">否</Option>
                                    </Select>
                                </FormItem>
                            </Col>
                            <Col :xl="3" :lg="3" :md="3" :sm="24" :xs="24" class="btn_box">
                                <FormItem>
                                    <Button type="primary" icon="ios-search" label="default" class="userSearch"  @click="userSearchs">搜索</Button>
                                </FormItem>
                            </Col>
                        </Col>
                        <Divider dashed />
                        <Col span="24">
                            <Button type="primary" class="mr20" @click="onSend">发送优惠券</Button>
                            <Button class="greens mr20" size="default" @click="onSendPic">
                                <Icon type="md-list"></Icon>
                                发送图文消息
                            </Button>
                        </Col>
                    </Row>
                </Form>
            </div>
            <Table ref="selection" :columns="columns4" :data="tabList" :loading="loading"
                   no-data-text="暂无数据" highlight-row class="mt25"
                   no-filtered-data-text="暂无筛选结果"
                   @on-selection-change="onSelectTab">
                <template slot-scope="{ row, index }" slot="headimgurl">
                    <div class="tabBox_img" v-viewer>
                        <img v-lazy="row.headimgurl">
                    </div>
                </template>
                <template slot-scope="{ row }" slot="sex">
                    <span v-show="row.sex === 1">男</span>
                    <span v-show="row.sex === 2">女</span>
                    <span v-show="row.sex === 0">保密</span>
                </template>
                <template slot-scope="{ row }" slot="country">
                    <span>{{row.country + row.province + row.city}}</span>
                </template>
                <template slot-scope="{ row }" slot="subscribe">
                    <span v-show="row.subscribe === 1">关注</span>
                    <span v-show="row.subscribe === 0">未关注</span>
                </template>
                <template slot-scope="{ row }" slot="createModalFrame">
                    <template>
                        <Dropdown @on-click="changeMenu(row,$event)">
                            <a href="javascript:void(0)">操作
                                <Icon type="ios-arrow-down"></Icon>
                            </a>
                            <DropdownMenu slot="list">
                                <DropdownItem name="1" v-show='row.subscribe'>修改分组</DropdownItem>
                                <DropdownItem name="2" v-show='row.subscribe'>修改标签</DropdownItem>
                                <DropdownItem name="3" v-show='row.subscribe'>同步标签</DropdownItem>
                                <DropdownItem v-show='!row.subscribe'>无法操作</DropdownItem>
                            </DropdownMenu>
                        </Dropdown>
                    </template>
                </template>
            </Table>
            <div class="acea-row row-right page">
                <Page :total="total" show-elevator show-total @on-change="pageChange"
                      :page-size="formValidate.limit"/>
            </div>
        </Card>
        <!-- 用户分组和标签编辑-->
        <edit-from ref="edits" :FromData="FromData" @submitFail="submitFail"></edit-from>
        <!-- 发送优惠券-->
        <send-from ref="sends" :userIds="user_ids"></send-from>
        <!--发送图文消息 -->
        <Modal v-model="modal13" scrollable title="发送消息" :z-index="100" width="1200" height="800" footer-hide class="modelBox">
            <news-category v-if="modal13" :isShowSend="isShowSend" :userIds="user_ids" :scrollerHeight="scrollerHeight" :contentTop="contentTop" :contentWidth="contentWidth" :maxCols="maxCols"></news-category>
        </Modal>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    import { wechatUserListtApi, tagListtApi, groupsEditApi } from '@/api/app'
    import newsCategory from '@/components/newsCategory/index'
    import editFrom from '@/components/from/from'
    import sendFrom from '@/components/sendCoupons/index'
    export default {
        name: 'user',
        components: {
            newsCategory,
            editFrom,
            sendFrom
        },
        data () {
            return {
                tagidList: [],
                isShowSend: true,
                maxCols: 4,
                scrollerHeight: '600',
                contentTop: '130',
                contentWidth: '98%',
                modal13: false,
                timeVal: [],
                fromList: {
                    title: '选择时间',
                    custom: true,
                    fromTxt: [
                        { text: '全部', val: '' },
                        { text: '今天', val: 'today' },
                        { text: '昨天', val: 'yesterday' },
                        { text: '最近7天', val: 'lately7' },
                        { text: '最近30天', val: 'lately30' },
                        { text: '本月', val: 'month' },
                        { text: '本年', val: 'year' }
                    ]
                },
                formValidate: {
                    limit: 15,
                    page: 1,
                    nickname: '',
                    data: '',
                    tagid_list: '',
                    sex: '',
                    groupid: 0,
                    subscribe: '',
                    export: 2
                },
                loading: false,
                tabList: [],
                total: 0,
                value2: '',
                grid: {
                    xl: 8,
                    lg: 8,
                    md: 8,
                    sm: 24,
                    xs: 24
                },
                columns4: [
                    {
                        type: 'selection',
                        min: 60,
                        align: 'center'
                    },
                    {
                        title: 'ID',
                        key: 'uid'
                    },
                    {
                        title: '微信用户名称',
                        key: 'nickname'
                    },
                    {
                        title: '头像',
                        slot: 'headimgurl'
                    },
                    {
                        title: '性别',
                        slot: 'sex'
                    },
                    {
                        title: '地区',
                        slot: 'country'
                    },
                    {
                        title: '是否关注公众号',
                        slot: 'subscribe'
                    },
                    {
                        title: '用户分组',
                        key: 'groupid'
                    },
                    {
                        title: '用户标签',
                        key: 'tagid_list'
                    },
                    {
                        title: '操作',
                        slot: 'createModalFrame',
                        fixed: 'right',
                        width: 100
                    }
                ],
                tagList: [],
                groupList: [],
                FromData: null,
                selectionList: [],
                user_ids: ''
            }
        },
        computed: {
            ...mapState('media', [
                'isMobile'
            ]),
            labelWidth () {
                return this.isMobile ? undefined : 80
            },
            labelPosition () {
                return this.isMobile ? 'top' : 'right'
            }
        },
        created () {
            this.getListTag()
            this.getList()
        },
        methods: {
            // 操作
            changeMenu (row, name) {
                switch (name) {
                case '1':
                    this.editGroup(`app/wechat/user_group/${row.openid}/edit`)
                    break
                case '2':
                    this.editGroup(`app/wechat/user_tag/${row.openid}/edit`)
                    break
                default:
                    let delfromData = {
                        title: '同步该用户标签',
                        url: `app/wechat/syn_tag/${row.openid}`,
                        method: 'PUT',
                        ids: ''
                    }
                    this.$modalSure(delfromData).then((res) => {
                        this.$Message.success(res.msg)
                        this.getList()
                    }).catch(res => {
                        this.$Message.error(res.msg)
                    })
                }
            },
            // 修改用户分组 标签
            editGroup (url) {
                groupsEditApi(url).then(async res => {
                    this.FromData = res.data
                    this.$refs.edits.modals = true
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 修改成功
            submitFail () {
                this.getList()
            },
            // 同步标签
            submitModel () {
                this.getList()
            },
            // 点击发送优惠券
            onSend () {
                if (this.selectionList.length === 0) {
                    this.$Message.warning('请选择要发送优惠券的用户')
                } else {
                    this.$refs.sends.modals = true
                    this.$refs.sends.getList()
                }
            },
            // 发送图文消息
            onSendPic () {
                if (this.selectionList.length === 0) {
                    this.$Message.warning('请选择要发送图文消息的用户')
                } else {
                    this.modal13 = true
                }
            },
            // 全选
            onSelectTab (selection) {
                this.selectionList = selection
                let data = []
                this.selectionList.map((item) => {
                    data.push(item.uid)
                })
                this.user_ids = data.join(',')
            },
            // 具体日期
            onchangeTime (e) {
                this.timeVal = e
                this.formValidate.data = this.timeVal.join('-')
                this.getList()
            },
            // 选择时间
            selectChange (tab) {
                this.formValidate.data = tab
                this.timeVal = []
                this.getList()
            },
            // 标签 分组
            getListTag () {
                let obj = {
                    id: '',
                    name: '全部'
                }
                tagListtApi().then(async res => {
                    let data = res.data
                    this.tagList = data.tagList
                    this.groupList = data.groupList
                    this.groupList.unshift(obj)
                }).catch(res => {
                    this.loading = false
                    this.$Message.error(res.msg)
                })
            },
            // 列表
            getList () {
                this.loading = true
                this.formValidate.sex = this.formValidate.sex || ''
                this.formValidate.subscribe = this.formValidate.subscribe || ''
                this.formValidate.tagid_list = this.tagidList.join(',')
                wechatUserListtApi(this.formValidate).then(async res => {
                    let data = res.data
                    this.tabList = data.list
                    this.total = data.count
                    this.loading = false
                }).catch(res => {
                    this.loading = false
                    this.$Message.error(res.msg)
                })
            },
            pageChange (index) {
                this.formValidate.page = index
                this.getList()
            },
            // 搜索
            userSearchs () {
                this.getList()
            }
        }
    }
</script>

<style scoped lang="stylus">
    .Refresh
        font-size 12px
        color #1890FF
        cursor pointer
    .userFrom
        >>> .ivu-form-item-content
            margin-left: 0px !important
    .tabBox_img
        width 36px
        height 36px
        border-radius:4px
        cursor pointer
        img
            width 100%
            height 100%
    .subscribe_box
       >>> .ivu-form-item-label
          width 110px !important
       >>> .ivu-form-item-content
          margin-left  110px !important
    .sex_box
        >>> .ivu-form-item-label
          width 60px !important
        >>> .ivu-form-item-content
          margin-left  60px !important
    .btn_box
        >>> .ivu-form-item-content
          margin-left 0 !important
    .modelBox
        >>> .ivu-modal-body
            padding 0 16px 16px 16px !important
</style>
