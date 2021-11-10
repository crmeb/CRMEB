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
                        <MenuItem :name="item.id" class="menu-item" :class="index===current?'showOn':''" v-for="(item,index) in labelSort" :key="index" @click.native="bindMenuItem(item,index)">
                            {{item.name}}
                            <div class="icon-box" v-if="index!=0">
                                <Icon type="ios-more" size="24" @click.stop="showMenu(item)" />
                            </div>
                            <div class="right-menu ivu-poptip-inner" v-show="item.status" v-if="index!=0">
                                <div class="ivu-poptip-body" @click="labelEdit(item)">
                                    <div class="ivu-poptip-body-content"><div class="ivu-poptip-body-content-inner">编辑</div>
                                    </div>
                                </div>
                                <div class="ivu-poptip-body" @click="deleteSort(item,'删除分类',index)">
                                    <div class="ivu-poptip-body-content"><div class="ivu-poptip-body-content-inner">删除</div>
                                    </div>
                                </div>
                            </div>
                        </MenuItem>
                    </MenuGroup>
                </Menu>
            </Col>
            <Col span="21" ref="rightBox">
                <Card :bordered="false" dis-hover >
                    <Row type="flex">
                        <Col v-bind="grid">
                            <Button v-auth="['admin-user-label_add']" type="primary"  icon="md-add" @click="add">添加标签</Button>
                            <Button v-auth="['admin-user-label_add']" type="success"  icon="md-add" @click="addSort" style="margin-left: 10px">添加分类</Button>
                        </Col>
                    </Row>
                    <Table :columns="columns1" :data="labelLists" ref="table" class="mt25"
                           :loading="loading" highlight-row
                           no-userFrom-text="暂无数据"
                           no-filtered-userFrom-text="暂无筛选结果">
                        <template slot-scope="{ row, index }" slot="icons">
                            <div class="tabBox_img" v-viewer>
                                <img v-lazy="row.icon">
                            </div>
                        </template>
                        <template slot-scope="{ row, index }" slot="action">
                            <a @click="edit(row.id)">修改</a>
                            <Divider type="vertical" />
                            <a @click="del(row,'删除分组',index)">删除</a>
                        </template>
                    </Table>
                    <div class="acea-row row-right page">
                        <Page :total="total" show-elevator show-total @on-change="pageChange"
                              :page-size="labelFrom.limit"/>
                    </div>
                </Card>
            </Col>
        </Row>

    </div>
</template>

<script>
    import { mapState } from 'vuex';
    import { userLabelAll, userLabelApi,userLabelAddApi,userLabelEdit,userLabelCreate } from '@/api/user';
    export default {
        name: 'user_label',
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
                columns1: [
                    {
                        title: 'ID',
                        key: 'id',
                        align: "center",
                        width: 120
                    },
                    {
                        title: '分类名称',
                        key: 'cate_name',
                        align: "center",
                    },
                    {
                        title: '标签名称',
                        key: 'label_name',
                        align: "center"
                    },
                    {
                        title: '操作',
                        slot: 'action',
                        fixed: 'right',
                        width: 120
                    }
                ],
                labelFrom: {
                    page: 1,
                    limit: 15,
                    label_cate:''
                },
                labelLists: [],
                total: 0,
                theme3:'light',
                labelSort:[],
                sortName:'',
                current:0
            }
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
        created () {
            this.getUserLabelAll();
        },
        methods: {
            // 添加
            add () {
                this.$modalForm(userLabelAddApi(0)).then(() => this.getList());
            },
            // 分组列表
            getList () {
                this.loading = true;
                userLabelApi(this.labelFrom).then(async res => {
                    let data = res.data;
                    this.labelLists = data.list;
                    this.total = data.count;
                    this.loading = false;
                }).catch(res => {
                    this.loading = false;
                    this.$Message.error(res.msg);
                })
            },
            pageChange (index) {
                this.labelFrom.page = index;
                this.getList();
            },
            // 修改
            edit (id) {
                this.$modalForm(userLabelAddApi(id)).then(() => this.getList());
            },
            // 删除
            del (row, tit, num) {
                let delfromData = {
                    title: tit,
                    num: num,
                    url: `user/user_label/del/${row.id}`,
                    method: 'DELETE',
                    ids: ''
                };
                this.$modalSure(delfromData).then((res) => {
                    this.$Message.success(res.msg);
                    this.labelLists.splice(num, 1);

                    this.getList();
                }).catch(res => {
                    this.$Message.error(res.msg);
                });
            },
            // 标签分类
            getUserLabelAll(key){
                userLabelAll().then(res=>{
                    let obj = {
                        name:'全部',
                        id:''
                    }
                    res.data.unshift(obj)
                    res.data.forEach(el=>{
                        el.status = false
                    })
                    if(!key){
                        this.sortName = res.data[0].id
                        this.labelFrom.label_cate = res.data[0].id
                        this.getList();
                    }
                    this.labelSort = res.data
                })
            },
            // 显示标签小菜单
            showMenu(item){
                this.labelSort.forEach(el=>{
                    if(el.id == item.id){
                        el.status = item.status?false:true
                    }else{
                        el.status = false
                    }
                })
            },
            //编辑标签
            labelEdit(item){
                this.$modalForm(userLabelEdit(item.id)).then(() => this.getUserLabelAll(1));
            },
            // 添加分类
            addSort(){
                this.$modalForm(userLabelCreate()).then(() => this.getUserLabelAll());
            },
            deleteSort(row,tit,num){
                let delfromData = {
                    title: tit,
                    num: num,
                    url: `user/user_label_cate/${row.id}`,
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
            bindMenuItem(name,index){
                this.labelFrom.page = 1;
                this.current = index;
                this.labelSort.forEach(el=>{
                    el.status = false
                })
                this.labelFrom.label_cate = name.id
                this.getList();
            }
        }
    }
</script>

<style lang="stylus" scoped>
    .showOn{
        color: #2d8cf0;
        background: #f0faff;
        z-index: 2;
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