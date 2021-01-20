<template>
    <div>
        <div class="i-layout-page-header">
            <div class="i-layout-page-header">
                <span class="ivu-page-header-title">用户管理</span>
                <div>
                    <Tabs @on-click="onClickTab">
                        <TabPane :label="item.name" :name="item.type" v-for="(item,index) in headeNum" :key="index"/>
                    </Tabs>
                </div>
            </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt listbox">
            <Form ref="userFrom" :model="userFrom"  :label-width="labelWidth" :label-position="labelPosition" @submit.native.prevent>
                <Row :gutter="16">
                    <Col  span="18">
                        <Col span="24">
                            <Col v-bind="grid">
                                <FormItem label="用户搜索："  label-for="nickname">
                                  <Input v-model="userFrom.nickname" placeholder="请输入" element-id="nickname" clearable >
                                    <Select v-model="field_key" slot="prepend" style="width: 80px">
                                      <Option value="all">全部</Option>
                                      <Option value="uid">UID</Option>
                                      <Option value="phone">手机号</Option>
                                      <Option value="nickname">用户昵称</Option>
                                    </Select>
                                  </Input>
                                </FormItem>
                            </Col>
                        </Col>
                    </Col>
                    <template v-if="collapse">
                        <Col span="18">
                            <Col v-bind="grid">
                                <FormItem label="用户分组："  label-for="group_id">
                                    <Select v-model="group_id" placeholder="请选择" element-id="group_id" clearable>
                                        <Option value="all">全部</Option>
                                        <Option :value="item.id" v-for="(item, index) in groupList" :key="index">{{item.group_name}}</Option>
                                    </Select>
                                </FormItem>
                            </Col>
                            <Col v-bind="grid">
                                <FormItem label="用户标签："  label-for="label_id">
                                    <Select v-model="label_id" placeholder="请选择" element-id="label_id" clearable>
                                        <Option value="all">全部</Option>
                                        <Option :value="item.id" v-for="(item, index) in labelLists" :key="index">{{item.label_name}}</Option>
                                    </Select>
                                </FormItem>
                            </Col>
                        </Col>
                        <Col span="18">
                            <Col v-bind="grid">
                                <FormItem label="付费会员："  label-for="isMember">
                                    <Select v-model="userFrom.isMember" placeholder="请选择" element-id="isMember" clearable @on-change="changeMember">
                                        <Option :value="1">是</Option>
                                        <Option :value="0">否</Option>
                                    </Select>
                                </FormItem>
                            </Col>
                            <Col v-bind="grid">
                                <FormItem label="国家："  label-for="country">
                                    <Select v-model="userFrom.country" placeholder="请选择" element-id="country" clearable @on-change="changeCountry">
                                        <Option value="domestic">中国</Option>
                                        <Option value="abroad">外国</Option>
                                    </Select>
                                </FormItem>
                            </Col>
                            <Col v-bind="grid" v-if="userFrom.country ==='domestic'">
                                <FormItem label="省份：">
                                    <Cascader :data="addresData" :value="address" v-model="address" @on-change="handleChange"></Cascader>
                                </FormItem>
                            </Col>
                        </Col>
                        <Col span="18">
                            <Col v-bind="grid">
                                <FormItem label="性别："  label-for="sex">
                                    <RadioGroup v-model="userFrom.sex" type="button">
                                        <Radio label="">
                                            <span>全部</span>
                                        </Radio>
                                        <Radio label="1">
                                            <span>男</span>
                                        </Radio>
                                        <Radio label="2">
                                            <span>女</span>
                                        </Radio>
                                        <Radio label="0">
                                            <span>保密</span>
                                        </Radio>
                                    </RadioGroup>
                                </FormItem>
                            </Col>

                        </Col>
                        <Col span="18">
                            <Col v-bind="grid">
                                <FormItem label="访问情况："  label-for="user_time_type">
                                    <Select v-model="user_time_type" placeholder="请选择" element-id="user_time_type" clearable>
                                        <Option value="all">全部</Option>
                                        <Option value="visitno">时间段未访问</Option>
                                        <Option value="visit">时间段访问过</Option>
                                        <Option value="add_time">首次访问</Option>
                                    </Select>
                                </FormItem>
                            </Col>
                            <Col v-bind="grid">
                                <FormItem label="下单次数："  label-for="pay_count">
                                    <Select v-model="pay_count" placeholder="请选择" element-id="pay_count" clearable>
                                        <Option value="all">全部</Option>
                                        <Option value="-1">0次</Option>
                                        <Option value="0">1次以上</Option>
                                        <Option value="1">2次以上</Option>
                                        <Option value="2">3次以上</Option>
                                        <Option value="3">4次以上</Option>
                                        <Option value="4">5次以上</Option>
                                    </Select>
                                </FormItem>
                            </Col>
                        </Col>
                        <Col span="18">
                            <Col v-bind="grid">
                                <FormItem label="选择时间："  label-for="user_time">
                                    <!--<DatePicker clearable @on-change="onchangeTime" v-model="timeVal" :value="timeVal"  format="yyyy/MM/dd" type="daterange" placement="bottom-end" placeholder="选择时间" v-width="'100%'"></DatePicker>-->
                                    <DatePicker :editable="false" @on-change="onchangeTime" :value="timeVal" format="yyyy/MM/dd"
                                                type="datetimerange" placement="bottom-start" placeholder="自定义时间"
                                                style="width: 300px;" class="mr20" :options="options"></DatePicker>
                                </FormItem>
                            </Col>
                        </Col>
                    </template>
                    <Col span="6" class="ivu-text-right userFrom">
                        <FormItem>
                            <Button type="primary" icon="ios-search" label="default" class="mr15"  @click="userSearchs">搜索</Button>
                            <Button class="ResetSearch" @click="reset('userFrom')">重置</Button>
                            <a class="ivu-ml-8 font14 ml10" @click="collapse = !collapse">
                                <template v-if="!collapse">
                                    展开 <Icon type="ios-arrow-down" />
                                </template>
                                <template v-else>
                                    收起 <Icon type="ios-arrow-up" />
                                </template>
                            </a>
                        </FormItem>
                    </Col>
                </Row>
            </Form>
            <Divider dashed />
            <Row type="flex" justify="space-between" class="mt20">
                <Col span="24">
                    <Button v-auth="['admin-user-save']" type="primary" class="mr20" @click="save">添加用户</Button>
                    <Button v-auth="['admin-user-coupon']" class="mr20" @click="onSend">发送优惠券</Button>
                    <Button v-auth="['admin-user-group_set']" class="mr20" @click="setGroup">批量设置分组</Button>
                    <Button v-auth="['admin-user-set_label']" class="mr20" @click="setLabel">批量设置标签</Button>

                    <Button v-auth="['admin-user-synchro']" class="mr20" @click="synchro">同步公众号用户</Button>
                </Col>
                <Col span="24" class="userAlert" v-if="selectionList.length">
                    <Alert show-icon> 已选择<i class="userI"> {{selectionList.length}} </i>项</Alert>
                </Col>
            </Row>
            <Table :columns="columns" :data="userLists" class="mt25"
                   ref="table" highlight-row
                   :loading="loading"
                   no-userFrom-text="暂无数据"
                   no-filtered-userFrom-text="暂无筛选结果"
                   @on-selection-change="onSelectTab"
                   @on-sort-change="sortChanged"
            >
                <template slot-scope="{ row, index }" slot="avatars">
                    <div class="tabBox_img" v-viewer>
                        <img v-lazy="row.avatar">
                    </div>
                </template>
                <template slot-scope="{ row, index }" slot="nickname">
                    <div class="acea-row">
                        <Icon type="md-male" v-show="row.sex==='男'" color="#2db7f5" size="15" class="mr5"/>
                        <Icon type="md-female" v-show="row.sex==='女'" color="#ed4014" size="15" class="mr5"/>
                        <div v-text="row.nickname"></div>
                    </div>
<!--                    <div v-show="row.vip_name" class="vipName">{{row.vip_name}}</div>-->
                </template>
                <!--                <template slot-scope="{ row, index }" slot="status">-->
                <!--                    <i-switch v-model="row.status" :value="row.status" :true-value="1" :false-value="0" @on-change="onchangeIsShow(row)" size="large">-->
                <!--                        <span slot="open">显示</span>-->
                <!--                        <span slot="close">隐藏</span>-->
                <!--                    </i-switch>-->
                <!--                </template>-->
                <template slot-scope="{ row, index }" slot="action">
                    <a @click="edit(row)">编辑</a>
                    <Divider type="vertical" />
                    <template>
                        <Dropdown @on-click="changeMenu(row,$event,index)">
                            <a href="javascript:void(0)">
                                更多
                                <Icon type="ios-arrow-down"></Icon>
                            </a>
                            <DropdownMenu slot="list">
                                <DropdownItem name="1">账户详情</DropdownItem>
                                <DropdownItem name="2">积分余额</DropdownItem>
                                <DropdownItem name="5">设置分组</DropdownItem>
                                <DropdownItem name="6">设置标签</DropdownItem>
                            </DropdownMenu>
                        </Dropdown>
                    </template>
                </template>
            </Table>
            <div class="acea-row row-right page">
                <Page :total="total" :current="userFrom.page" show-elevator show-total @on-change="pageChange"
                      :page-size="userFrom.limit"  /></div>
        </Card>
        <!-- 编辑表单 积分余额-->
        <edit-from ref="edits" :FromData="FromData" @submitFail="submitFail"></edit-from>
        <!-- 发送优惠券-->
        <send-from ref="sends" :userIds="user_ids"></send-from>
        <!-- 会员详情-->
        <user-details ref="userDetails"></user-details>
        <Modal v-model="customerShow" scrollable title="请选择商城用户" :closable="false" width="50%">
            <customerInfo @imageObject="imageObject"></customerInfo>
        </Modal>
        <Modal v-model="labelShow" scrollable title="请选择用户标签" :closable="false" width="320" :footer-hide="true">
            <userLabel :uid="labelActive.uid" @close="labelClose"></userLabel>
        </Modal>

    </div>
</template>

<script>
    import userLabel from "../../../components/userLabel";
    import { mapState } from 'vuex';
    import expandRow from './tableExpand.vue';
    import { userList, getUserData, isShowApi, editOtherApi, userSetGroup, userGroupApi, userSetLabelApi, userLabelApi, userSynchro,getUserSaveForm } from '@/api/user';
    import editFrom from '../../../components/from/from';
    import sendFrom from '@/components/sendCoupons/index';
    import userDetails from './handle/userDetails';
    import newsCategory from '@/components/newsCategory/index';
    import city from '@/utils/city';
    import customerInfo from '@/components/customerInfo'
    export default {
        name: 'user_list',
        components: { expandRow, editFrom, sendFrom, userDetails, newsCategory, customerInfo,userLabel },
        data () {
            return {
                labelShow:false,
                customerShow: false,
                promoterShow: false,
                labelActive:{
                  uid:0
                },
                formInline: {
                    uid:0,
                    spread_uid:0,
                    image:''
                },
                options: {
                    shortcuts: [
                        {
                            text: '今天',
                            value () {
                                const end = new Date();
                                const start = new Date();
                                start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate()));
                                return [start, end];
                            }
                        },
                        {
                            text: '昨天',
                            value () {
                                const end = new Date();
                                const start = new Date();
                                start.setTime(start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 1)));
                                end.setTime(end.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 1)));
                                return [start, end];
                            }
                        },
                        {
                            text: '最近7天',
                            value () {
                                const end = new Date();
                                const start = new Date();
                                start.setTime(start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 6)));
                                return [start, end];
                            }
                        },
                        {
                            text: '最近30天',
                            value () {
                                const end = new Date();
                                const start = new Date();
                                start.setTime(start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 29)));
                                return [start, end];
                            }
                        },
                        {
                            text: '本月',
                            value () {
                                const end = new Date();
                                const start = new Date();
                                start.setTime(start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), 1)));
                                return [start, end];
                            }
                        },
                        {
                            text: '本年',
                            value () {
                                const end = new Date();
                                const start = new Date();
                                start.setTime(start.setTime(new Date(new Date().getFullYear(), 0, 1)));
                                return [start, end];
                            }
                        }
                    ]
                },
                collapse: false,
                headeNum: [
                    { 'type': '', 'name': '全部' },
                    { 'type': 'wechat', 'name': '微信公众号' },
                    { 'type': 'routine', 'name': '微信小程序' },
                    { 'type': 'h5', 'name': 'H5' },
                    { 'type': 'pc', 'name': 'PC' }
                ],
                address: [],
                addresData: city,
                isShowSend: true,
                modal13: false,
                maxCols: 4,
                scrollerHeight: '600',
                contentTop: '130',
                contentWidth: '98%',
                grid: {
                    xl: 8,
                    lg: 8,
                    md: 12,
                    sm: 24,
                    xs: 24
                },
                grid2: {
                    xl: 18,
                    lg: 16,
                    md: 12,
                    sm: 24,
                    xs: 24
                },
                loading: false,
                total: 0,
                userFrom: {
                    label_id: '',
                    user_type: '',
                    status: '',
                    sex: '',
                    is_promoter: '',
                    country: '',
                    isMember: '',
                    pay_count: '',
                    user_time_type: '',
                    user_time: '',
                    nickname: '',
                    province: '',
                    city: '',
                    page: 1,
                    limit: 15,
                    group_id: '',
                    field_key:'',
                },
                field_key: '',
                group_id: '',
                label_id:'',
                user_time_type:'',
                pay_count:'',
                columns: [
                    {
                        type: 'expand',
                        width: 40,
                        render: (h, params) => {
                            return h(expandRow, {
                                props: {
                                    row: params.row
                                }
                            })
                        }
                    },
                    {
                        type: 'selection',
                        width: 60,
                        align: 'center'
                    },
                    {
                        title: 'ID',
                        key: 'uid',
                        width: 80
                    },
                    {
                        title: '头像',
                        slot: 'avatars',
                        minWidth: 60
                    },
                    {
                        title: '姓名',
                        slot: 'nickname',
                        minWidth: 150
                    },
                    {
                        title: '分组',
                        key: 'group_id',
                        minWidth: 100
                    },
                    {
                        title: '手机号',
                        key: 'phone',
                        minWidth: 100
                    },
                    {
                        title: '用户类型',
                        key: 'user_type',
                        minWidth: 100
                    },
                    {
                        title: '余额',
                        key: 'now_money',
                        sortable: 'custom',
                        minWidth: 100
                    },
                    // {
                    //     title: '状态',
                    //     slot: 'status',
                    //     minWidth: 100
                    // },
                    {
                        title: '操作',
                        slot: 'action',
                        fixed: 'right',
                        minWidth: 120
                    }
                ],
                userLists: [],
                FromData: null,
                selectionList: [],
                user_ids: '',
                selectedData: [],
                timeVal: [],
                array_ids: [],
                groupList: [],
                levelList: [],
                labelFrom: {
                    page: 1,
                    limit: ''
                },
                labelLists: []
            }
        },
        computed: {
            ...mapState('media', [
                'isMobile'
            ]),
            labelWidth () {
                return this.isMobile ? undefined : 100;
            },
            labelPosition () {
                return this.isMobile ? 'top' : 'right';
            }
        },
        created () {
            this.getList();
        },
        mounted () {
            this.userGroup();
            this.groupLists();
        },
        methods: {
            // 标签弹窗关闭
            labelClose(){
              this.labelShow = false
            },
            save () {
                this.$modalForm(getUserSaveForm()).then(() => this.$refs.sends.getList());
            },
            synchro () {
                userSynchro().then(res => {
                    this.$Message.success(res.msg);
                }).catch(err => {
                    this.$Message.error(err);
                })
            },
            // 分组列表
            groupLists () {
                this.loading = true;
                userLabelApi(this.labelFrom).then(async res => {
                    let data = res.data;
                    this.labelLists = data.list;
                }).catch(res => {
                    this.loading = false;
                    this.$Message.error(res.msg);
                })
            },
            onClickTab (type) {
                this.userFrom.page = 1;
                this.userFrom.user_type = type;
                this.getList();
            },
            userGroup () {
                let data = {
                    page: 1,
                    limit: ''
                };
                userGroupApi(data).then(res => {
                    this.groupList = res.data.list;
                })
            },
            // 批量设置分组；
            setGroup () {
                if (this.selectionList.length === 0) {
                    this.$Message.warning('请选择要设置分组的用户');
                } else {
                    let uids = { uids: this.array_ids };
                    this.$modalForm(userSetGroup(uids)).then(() => this.$refs.sends.getList());
                }
            },
            // 批量设置标签；
            setLabel () {
                if (this.selectionList.length === 0) {
                    this.$Message.warning('请选择要设置标签的用户');
                } else {
                    let uids = { uids: this.array_ids };
                    this.$modalForm(userSetLabelApi(uids)).then(() => this.$refs.sends.getList());
                }
            },
            // 选择国家
            changeCountry () {
                if (this.userFrom.country === 'abroad' || !this.userFrom.country) {
                    this.selectedData = [];
                    this.userFrom.province = '';
                    this.userFrom.city = '';
                    this.address = [];
                }
            },
            // 选择地址
            handleChange (value, selectedData) {
                this.selectedData = selectedData.map(o => o.label);
                this.userFrom.province = this.selectedData[0];
                this.userFrom.city = this.selectedData[1];
            },
            // 具体日期
            onchangeTime (e) {
                this.timeVal = e;
                this.userFrom.user_time = this.timeVal.join('-');
            },
            // 操作
            changeMenu (row, name, index) {
                let uid = [];
                uid.push(row.uid);
                let uids = { uids: uid };
                switch (name) {
                    case '1':
                        this.$refs.userDetails.modals = true;
                        this.$refs.userDetails.getDetails(row.uid);
                        break;
                    case '2':
                        this.getOtherFrom(row.uid);
                        break;
                    case '5':
                        this.$modalForm(userSetGroup(uids)).then(() => this.$refs.sends.getList());
                        break;
                    case '6':
                        this.openLabel(row)
                        break;
                }
            },
            openLabel(row){
                this.labelShow = true
                this.labelActive.uid = row.uid
            },
            customer () {
                this.customerShow = true
            },
            imageObject (e) {
                this.customerShow = false;
                this.formInline.spread_uid = e.uid;
                this.formInline.image = e.image;
            },
            cancel (name) {
                this.promoterShow = false;
                this.$refs[name].resetFields();
            },
            // 删除
            del (row, tit, num, name) {
                let delfromData = {
                    title: tit,
                    num: num,
                    url: name==='user'?`user/del_level/${row.uid}`:`agent/stair/delete_spread/${row.uid}`,
                    method: name==='user'?'DELETE':'PUT',
                    ids: ''
                }
                this.$modalSure(delfromData).then((res) => {
                    this.$Message.success(res.msg);
                    this.getList();
                }).catch(res => {
                    this.$Message.error(res.msg);
                });
            },
            // 清除会员删除成功
            submitModel () {
                this.getList();
            },
            // 会员列表
            getList () {
                this.userFrom.user_type = this.userFrom.user_type || '';
                this.userFrom.status = this.userFrom.status || '';
                this.userFrom.sex = this.userFrom.sex || '';
                this.userFrom.is_promoter = this.userFrom.is_promoter || '';
                this.userFrom.country = this.userFrom.country || '';
                this.userFrom.pay_count = this.pay_count === 'all'?'':this.pay_count;
                this.userFrom.user_time_type = this.user_time_type === 'all'?'':this.user_time_type;
                this.userFrom.label_id = this.label_id === 'all'?'':this.label_id;
                this.userFrom.field_key = this.field_key === 'all'?'':this.field_key;
                this.userFrom.group_id = this.group_id === 'all'?'':this.group_id;
                this.loading = true;
                userList(this.userFrom).then(async res => {
                    let data = res.data;
                    this.userLists = data.list;
                    this.total = data.count;
                    this.loading = false;
                }).catch(res => {
                    this.loading = false;
                    this.$Message.error(res.msg);
                })
            },
            pageChange (index) {
                this.selectionList = [];
                this.userFrom.page = index
                this.getList();
            },
            // 搜索
            userSearchs () {
                this.userFrom.page = 1;
                this.getList();
            },
            // 重置
            reset (name) {
                this.userFrom = {
                    user_type: '',
                    status: '',
                    sex: '',
                    is_promoter: '',
                    country: '',
                    pay_count: '',
                    user_time_type: '',
                    user_time: '',
                    nickname: '',
                    field_key:'',
                    group_id:'',
                    label_id:'',
                    page: 1, // 当前页
                    limit: 20 // 每页显示条数
                };
                this.field_key = '';
                this.group_id = '';
                this.label_id = '';
                this.user_time_type = '';
                this.pay_count = '';
                this.timeVal = [];
                this.getList();
            },
            // 获取编辑表单数据
            getUserFrom (id) {
                getUserData(id).then(async res => {
                    if (res.data.status === false) {
                        return this.$authLapse(res.data);
                    }
                    this.FromData = res.data;
                    this.$refs.edits.modals = true;
                }).catch(res => {
                    this.$Message.error(res.msg);
                })
            },
            // 获取积分余额表单
            getOtherFrom (id) {
                editOtherApi(id).then(async res => {
                    if (res.data.status === false) {
                        return this.$authLapse(res.data);
                    }
                    this.FromData = res.data;
                    this.$refs.edits.modals = true;
                }).catch(res => {
                    this.$Message.error(res.msg);
                })
            },
            // 修改状态
            onchangeIsShow (row) {
                let data = {
                    id: row.uid,
                    status: row.status
                }
                isShowApi(data).then(async res => {
                    this.$Message.success(res.msg);
                }).catch(res => {
                    this.$Message.error(res.msg);
                })
            },
            // 全选
            onSelectTab (selection) {
                this.selectionList = selection;
                let data = [];
                this.selectionList.map((item) => {
                    data.push(item.uid)
                });
                this.array_ids = data;
                this.user_ids = data.join(',');
            },
            // 点击发送优惠券
            onSend () {
                if (this.selectionList.length === 0) {
                    this.$Message.warning('请选择要发送优惠券的用户');
                } else {
                    this.$refs.sends.modals = true;
                    this.$refs.sends.getList()
                }
            },
            // 编辑
            edit (row) {
                this.getUserFrom(row.uid);
            },
            // 修改成功
            submitFail () {
                this.getList();
            },
            // 排序
            sortChanged (e) {
                this.userFrom[e.key] = e.order
                this.getList();
            }
        }
    }
</script>

<style scoped lang="stylus">
    .picBox
        display: inline-block;
        cursor: pointer;
        .upLoad
            width: 58px;
            height: 58px;
            line-height: 58px;
            border: 1px dotted rgba(0, 0, 0, 0.1);
            border-radius: 4px;
            background: rgba(0, 0, 0, 0.02);
        .pictrue
            width: 60px;
            height: 60px;
            border: 1px dotted rgba(0, 0, 0, 0.1);
            margin-right: 10px;
            img
                width: 100%;
                height: 100%;
    .userFrom
        >>> .ivu-form-item-content
            margin-left: 0px !important
    .userAlert
        margin-top 20px
    .userI
        color #1890FF
        font-style normal
    img{height:36px;display:block;}
    .tabBox_img
        width 36px
        height 36px
        border-radius:4px;
        cursor pointer
        img
            width 100%
            height 100%
    .tabBox_tit
        width 60%
        font-size 12px !important
        margin 0 2px 0 10px
        letter-spacing: 1px;
        padding: 5px 0;
        box-sizing: border-box;
    .modelBox
        >>> .ivu-modal-body
            padding 0 16px 16px 16px !important
    .vipName
        color: #dab176
    .listbox
        >>>.ivu-divider-horizontal
            margin 0 !important
</style>
