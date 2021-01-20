<template>
    <div>
        <div class="i-layout-page-header">
            <div class="i-layout-page-header">
                <span class="ivu-page-header-title">{{$route.meta.title}}</span>
            </div>
        </div>
        <Row class="ivu-mt box-wrapper">
            <Col span="3" class="left-wrapper">
                <Menu :theme="theme3" :active-name="sortName" width="auto" >
                    <MenuGroup>
                        <MenuItem :name="item.id" class="menu-item" :class="index===current?'showOn':''"  v-for="(item,index) in labelSort" :key="index" @click.native="bindMenuItem(item,index)">
                            {{item.name}}
                            <div class="icon-box" v-if="index!=0">
                                <Icon type="ios-more" size="24" @click.stop="showMenu(item)" />
                            </div>
                            <div class="right-menu ivu-poptip-inner" v-show="item.status" v-if="index!=0">
                                <div class="ivu-poptip-body" @click="labelEdit(item)">
                                    <div class="ivu-poptip-body-content"><div class="ivu-poptip-body-content-inner">编辑小组</div>
                                    </div>
                                </div>
                                <div class="ivu-poptip-body" @click="deleteSort(item,'删除分类',index)">
                                    <div class="ivu-poptip-body-content"><div class="ivu-poptip-body-content-inner">删除小组</div>
                                    </div>
                                </div>
                            </div>
                        </MenuItem>
                    </MenuGroup>
                </Menu>
            </Col>
            <Col span="21" ref="rightBox">
                <Card :bordered="false" dis-hover>
                    <Row type="flex" class="mb20">
                        <Col span="24">
                            <Button v-auth="['setting-store_service-add']" type="primary"  icon="md-add" @click="add" class="mr10">添加话术</Button>
                            <Button v-auth="['setting-store_service-add']" type="success"  icon="md-add" @click="addSort" style="margin-left: 10px">添加分类</Button>
                        </Col>
                    </Row>
                    <Table :columns="columns1" :data="tableList"
                           :loading="loading" highlight-row
                           no-userFrom-text="暂无数据"
                           no-filtered-userFrom-text="暂无筛选结果">
                        <template slot-scope="{ row, index }" slot="avatar">
                            <div class="tabBox_img" v-viewer>
                                <img v-lazy="row.avatar">
                            </div>
                        </template>
                        <template slot-scope="{ row, index }" slot="add_time">
                            <span> {{row.add_time}}</span>
                        </template>

                        <template slot-scope="{ row, index }" slot="action">
                            <a @click="edit(row)">编辑</a>
                            <Divider type="vertical"/>
                            <a @click="del(row,'删除客服',index)">删除</a>
                        </template>
                    </Table>
                    <div class="acea-row row-right page">
                        <Page :total="total" show-elevator show-total @on-change="pageChange"
                              :page-size="tableFrom.limit"/>
                    </div>
                </Card>
            </Col>
        </Row>
    </div>
</template>

<script>
    import { mapState } from 'vuex';
    import { wechatSpeechcraft,speechcraftCreate,speechcraftEdit,speechcraftcate,speechcraftcateCreate,speechcraftcateEdit } from '@/api/setting';
    export default {
        name: 'index',
        filters: {
            typeFilter (status) {
                const statusMap = {
                    'wechat': '微信用户',
                    'routine': '小程序用户'
                }
                return statusMap[status]
            }
        },
        computed: {
            ...mapState('media', [
                'isMobile'
            ]),
            labelWidth () {
                return this.isMobile ? undefined : 80;
            },
            labelPosition () {
                return this.isMobile ? 'top' : 'left';
            }
        },
        data () {
            return {
                isChat: true,
                formValidate3: {
                    page: 1,
                    limit: 15
                },
                total3: 0,
                loading3: false,
                modals3: false,
                tableList3: [],
                columns3: [
                    {
                        title: '用户名称',
                        key: 'nickname',
                        width: 200
                    },
                    {
                        title: '客服头像',
                        slot: 'headimgurl'
                    },
                    {
                        title: '操作',
                        slot: 'action'
                    }
                ],
                formValidate5: {
                    page: 1,
                    limit: 15,
                    uid: 0,
                    to_uid: 0,
                    id: 0
                },
                total5: 0,
                loading5: false,
                tableList5: [],
                FromData: null,
                formValidate: {
                    page: 1,
                    limit: 15,
                    data: '',
                    type: '',
                    nickname: ''
                },
                tableList2: [],
                modals: false,
                total: 0,
                tableFrom: {
                    page: 1,
                    limit: 15,
                    cate_id:0
                },
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
                loading: false,
                tableList: [],
                columns1: [
                    {
                        title: 'ID',
                        key: 'id',
                        width: 80
                    },
                    {
                        title: '详情',
                        key: 'message',
                        minWidth: 320
                    },
                    {
                        title: '排序',
                        key: 'sort',
                        minWidth: 60
                    },
                    {
                        title: '添加时间',
                        slot: 'add_time',
                        minWidth: 120
                    },
                    {
                        title: '操作',
                        slot: 'action',
                        fixed: 'right',
                        minWidth: 150
                    }
                ],
                columns4: [
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
                        title: '微信用户名称',
                        key: 'nickname',
                        minWidth: 160
                    },
                    {
                        title: '客服头像',
                        slot: 'headimgurl',
                        minWidth: 60
                    },
                    {
                        title: '用户类型',
                        slot: 'user_type',
                        width: 100
                    },
                    {
                        title: '性别',
                        slot: 'sex',
                        minWidth: 60
                    },
                    {
                        title: '地区',
                        slot: 'country',
                        minWidth: 120
                    },
                    {
                        title: '是否关注公众号',
                        slot: 'subscribe',
                        minWidth: 120
                    }
                ],
                loading2: false,
                total2: 0,
                addFrom: {
                    uids: []
                },
                selections: [],
                rows: {},
                rowRecord: {},
                theme3:'light',
                labelSort:[],
                sortName:"",
                current:0
            }
        },
        created () {
            this.getUserLabelAll();
        },
        methods: {
            getUserLabelAll(key){
                speechcraftcate().then(res=>{
                    let data = res.data.data;
                    let obj = {
                        name:'全部',
                        id:''
                    }
                    data.unshift(obj);
                    data.forEach(el=>{
                        el.status = false
                    })
                    if(!key){
                        this.sortName = data[0].id
                        this.tableFrom.cate_id = data[0].id;
                        this.getList();
                    }
                    this.labelSort = data
                })
            },
            // 添加分类
            addSort(){
                this.$modalForm(speechcraftcateCreate()).then(() => this.getUserLabelAll());
            },
            //编辑标签
            labelEdit(item){
                this.$modalForm(speechcraftcateEdit(item.id)).then(() => this.getUserLabelAll(1));
            },
            deleteSort(row,tit,num){
                let delfromData = {
                    title: tit,
                    num: num,
                    url: `app/wechat/speechcraftcate/${row.id}`,
                    method: 'DELETE',
                    ids: ''
                };
                this.$modalSure(delfromData).then((res) => {
                    this.$Message.success(res.msg);
                    this.labelSort.splice(num, 1);
                    this.labelSort = []
                    this.getUserLabelAll()
                }).catch(res => {
                    this.$Message.error(res.msg);
                });
            },
            // 显示标签小菜单
            showMenu(item){
                console.log('454545');
                this.labelSort.forEach(el=>{
                    console.log('rrrr',el.id);
                    console.log('rrrr2',el.id);
                    if(el.id == item.id){
                        console.log('ssss',item.status);
                        el.status = item.status?false:true
                    }else{
                        el.status = false
                    }
                })
            },
            bindMenuItem(name,index){
                this.current = index;
                this.labelSort.forEach(el=>{
                    el.status = false
                })
                this.tableFrom.cate_id = name.id;
                this.getList();
            },
            cancel () {
                this.formValidate = {
                    page: 1,
                    limit: 10,
                    data: '',
                    type: '',
                    nickname: ''
                }
            },
            handleReachBottom () {
                return new Promise(resolve => {
                    this.formValidate.page = this.formValidate.page + 1;
                    setTimeout(() => {
                        // this.loading2 = true;
                        kefucreateApi(this.formValidate).then(async res => {
                            let data = res.data
                            // this.tableList2 = data.list;
                            if (data.list.length > 0) {
                                for (let i = 0; i < data.list.length; i++) {
                                    this.tableList2.push(data.list[i])
                                }
                            }
                            this.total2 = data.count;
                            this.loading2 = false;
                        }).catch(res => {
                            this.loading2 = false;
                            this.$Message.error(res.msg);
                        })
                        resolve();
                    }, 2000);
                });
            },
            // 查看对话
            look (row) {
                this.isChat = false;
                this.rowRecord = row;
                this.getChatlist()
            },
            // 查看对话列表
            getChatlist () {
                this.loading5 = true;
                this.formValidate5.uid = this.rows.uid;
                this.formValidate5.to_uid = this.rowRecord.uid;
                this.formValidate5.id = this.rows.id;
                kefuChatlistApi(this.formValidate5).then(async res => {
                    let data = res.data
                    this.tableList5 = data.list;
                    this.total5 = data.count;
                    this.loading5 = false;
                }).catch(res => {
                    this.loading5 = false;
                    this.$Message.error(res.msg);
                })
            },
            pageChange5 (index) {
                this.formValidate5.page = index;
                this.getChatlist();
            },
            // 修改成功
            submitFail () {
                this.getList();
            },
            // 聊天记录
            record (row) {
                this.rows = row;
                this.modals3 = true;
                this.isChat = true;
                this.getListRecord();
            },
            // 聊天记录列表
            getListRecord () {
                this.loading3 = true;
                kefuRecordApi(this.formValidate3, this.rows.id).then(async res => {
                    let data = res.data;
                    this.tableList3 = data.list ? data.list : [];
                    this.total3 = data.count;
                    this.loading3 = false;
                }).catch(res => {
                    this.loading3 = false;
                    this.$Message.error(res.msg);
                })
            },
            pageChange3 (index) {
                this.formValidate3.page = index;
                this.getListRecord();
            },
            // 编辑
            edit (row) {
                this.$modalForm(speechcraftEdit(row.id)).then(() => this.getList());
            },
            // 添加
            add () {
                this.$modalForm(speechcraftCreate()).then(() => this.getList());
            },
            // 全选
            onSelectTab (selection) {
                this.selections = selection;
                let data = [];
                this.selections.map((item) => {
                    data.push(item.uid)
                });
                this.addFrom.uids = data;
            },
            // 具体日期
            onchangeTime (e) {
                this.timeVal = e;
                this.formValidate.data = this.timeVal.join('-');
                this.formValidate.page = 1;
                this.getListService();
            },
            // 选择时间
            selectChange (tab) {
                this.formValidate.data = tab;
                this.timeVal = [];
                this.formValidate.page = 1;
                this.getListService();
            },
            // 客服列表
            getListService () {
                this.loading2 = true;
                kefucreateApi(this.formValidate).then(async res => {
                    let data = res.data
                    this.tableList2 = data.list;
                    this.total2 = data.count;
                    this.tableList2.map((item) => {
                        item._isChecked = false;
                    });
                    this.loading2 = false;
                }).catch(res => {
                    this.loading2 = false;
                    this.$Message.error(res.msg);
                })
            },
            pageChange2 (pageIndex) {
                this.formValidate.page = pageIndex;
                this.getListService();
                this.addFrom.uids = [];
            },
            // 搜索
            userSearchs () {
                this.formValidate.page = 1;
                this.getListService();
            },
            // 删除
            del (row, tit, num) {
                let delfromData = {
                    title: tit,
                    num: num,
                    url: `/app/wechat/speechcraft/${row.id}`,
                    method: 'DELETE',
                    ids: ''
                };
                this.$modalSure(delfromData).then((res) => {
                    this.$Message.success(res.msg);
                    this.tableList.splice(num, 1);
                }).catch(res => {
                    this.$Message.error(res.msg);
                });
            },
            // 列表
            getList () {
                console.log('eeeeeeeeeee2');
                this.loading = true;
                wechatSpeechcraft(this.tableFrom).then(async res => {
                    let data = res.data
                    console.log('eeeeeeeeeee3',data.list);
                    this.tableList = data.list;
                    this.total = res.data.count;
                    this.loading = false;
                }).catch(res => {
                    this.loading = false;
                    this.$Message.error(res.msg);
                })
            },
            pageChange (index) {
                this.tableFrom.page = index;
                this.getList();
            },
            // 修改是否显示
            onchangeIsShow (row) {
                let data = {
                    id: row.id,
                    status: row.status
                }
                kefusetStatusApi(data).then(async res => {
                    this.$Message.success(res.msg);
                }).catch(res => {
                    this.$Message.error(res.msg);
                })
            },
            // 添加客服
            putRemark () {
                if (this.addFrom.uids.length === 0) {
                    return this.$Message.warning('请选择要添加的客服');
                }
                kefuAddApi(this.addFrom).then(async res => {
                    this.$Message.success(res.msg);
                    this.modals = false;
                    this.getList();
                }).catch(res => {
                    this.loading = false;
                    this.$Message.error(res.msg);
                })
            }
        }
    }
</script>

<style scoped lang="stylus">
    .showOn{
        color: #2d8cf0;
        background: #f0faff;
        z-index: 2;
    }
    .tabBox_img
        width 36px
        height 36px
        border-radius:4px;
        cursor pointer
        img
            width 100%
            height 100%
    .modelBox
        >>>
        .ivu-table-header
            width 100% !important
    .trees-coadd
        width: 100%;
        height: 385px;
        .scollhide
            width: 100%;
            height: 100%;
            overflow-x: hidden;
            overflow-y: scroll;
    // margin-left: 18px;
    .scollhide::-webkit-scrollbar {
        display: none;
    }
    /deep/ .ivu-menu-vertical .ivu-menu-item-group-title{
        display: none;
    }
    /deep/ .ivu-menu-vertical.ivu-menu-light:after{
        display none
    }

    .left-wrapper
        height 904px
        background #fff
        border-right 1px solid #dcdee2
    .menu-item
        z-index 50
        position: relative;
        display flex
        justify-content space-between
        word-break break-all
        .icon-box
            z-index 3
            position absolute
            right 20px
            top 50%
            transform translateY(-50%)
            display none
        &:hover .icon-box
            display block
        .right-menu
            z-index 10
            position absolute
            right: -106px;
            top: -11px;
            width auto
            min-width: 121px;
</style>
