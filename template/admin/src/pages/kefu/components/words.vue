<template>
    <div>
        <!-- 常用语 -->
        <div class="words-mask" v-if="isWords">
            <div class="content">
                <div class="title-box">
                    <div class="tab-box">
                        <div class="tan-item" :class="{on:item.key == wordsTabCur}" v-for="(item,index) in wordsTab" @click.stop="bindTab(item)">{{item.title}}</div>
                        <div class="right-icon">
                            <span class="iconfont iconbianji2" @click.stop="isWordShow = true"></span>
                            <span class="iconfont iconcha" @click.stop="closeBox"></span>
                        </div>
                    </div>
                    <div class="input-box">
                        <Input v-model="wordsData.searchTxt" placeholder="搜索快捷回复" :search="true" @on-search="bindSearch"  />
                    </div>
                </div>
                <div class="scroll-box">
                    <div class="scroll-left">
                        <div class="left-item add_cate" @click.stop="openCate(0)" v-if="wordsTabCur"> <span class="iconfont iconjiahao"></span> 分组</div>
                        <div class="left-item" :class="{active:wordsData.cateId == item.id}" v-for="item in wordsData.cate" @click.stop="changeCate(item)">{{item.name}}</div>
                    </div>
                    <div class="right-box">
                        <vue-scroll :ops="wordsData.ops"
                                    @load-before-deactivate="handleWordsScroll"
                        >

                            <div class="slot-load" slot="load-deactive"></div>
                            <div class="slot-load" slot="load-beforeDeactive"></div>
                            <div class="slot-load" slot="load-active">下滑加载更多</div>
                            <div class="msg-item add-mg" v-show="wordsTabCur" @click.stop="addMsg"><span class="iconfont icontianjia11"></span>添加话术</div>
                            <div class="msg-item" v-for="(item,index) in wordsList" :key="index" @click.stop="selectWords(item)">
                                <span class="title">{{item.title}}</span>{{item.message}}
                            </div>
                        </vue-scroll>
                    </div>
                </div>
            </div>
        </div>
        <!-- 添加分组  -->
        <Modal
                v-model="cateData.isCate"
                width="300"
                :footer-hide="true"
                :closable="false"
                class-name="vertical-center-modal"
                class="words-box"
        >
            <div class="mask-title">
                {{cateData.status?'编辑分组':'新增分组'}}
                <span class="iconfont iconcha" @click.stop="closeCate"></span>
            </div>
            <div class="input-box">
                <Input class="noinput" v-model="cateData.name" placeholder="请输入分组名称" />
            </div>
            <div class="input-box">
                <Input class="noinput" v-model="cateData.sort" placeholder="请输入分组排序" />
            </div>
            <Button @click.stop="cateConfirm" class="subBtn" type="primary" :disabled="cateStatus">确定</Button>
        </Modal>
        <!-- 添加话术  -->
        <Modal
                v-model="msgData.isCateMeg"
                width="300"
                :footer-hide="true"
                :closable="false"
                class-name="vertical-center-modal"
                class="words-box"
        >
            <div class="mask-title">
                {{msgData.status?'修改话术':'添加话术'}}
                <span class="iconfont iconcha" @click.stop="closeMsgBox"></span>
            </div>
            <div class="input-box">
                <Input class="noinput" v-model="msgData.title" placeholder="请输入标题名称 (选填)" />
            </div>
            <div class="input-box text-area">
                <Input class="noinput" :rows="4" type="textarea" v-model="msgData.message" placeholder="请输入您的话术" />
            </div>
            <div class="input-box">
                <Select v-model="msgData.msgCateId">
                    <Option v-for="item in selectData" :value="item.id" :key="item.value">{{ item.name }}</Option>
                </Select>
            </div>
            <Button @click.stop="msgConfirm" class="subBtn" type="primary" :disabled="msgStatus">确定</Button>
        </Modal>
        <!-- 编辑弹窗  -->
        <div class="edit-box" v-if="isWordShow">
            <div class="head">
                <div class="tit-bar">{{wordsTabCur?'个人库':'公共库'}}<span @click.stop="isWordShow = false">完成</span></div>
                <div class="input-box noinput">
                    <Input v-model="wordsData.searchTxt" placeholder="搜索快捷回复" :search="true" @on-search="bindSearch" />
                </div>
            </div>
            <div class="scroll-box">
                <div class="scroll-left">
                    <div class="top">
                        <div class="left-item add_cate" @click.stop="openCate(0)" v-if="wordsTabCur"> <span class="iconfont iconjiahao"></span> 分组</div>
                        <div class="left-item" :class="{active:wordsData.cateId == item.id}" v-for="item in wordsData.cate" @click.stop="changeCate(item)">{{item.name}}</div>
                    </div>
                    <div class="bom" v-if="wordsTabCur">
                        <div class="left-item edits-box" @click.stop="editList.status = true">编辑分组</div>
                    </div>
                </div>
                <div class="right-box">
                    <vue-scroll :ops="wordsData.ops"
                                @load-before-deactivate="handleWordsScroll"
                    >

                        <div class="slot-load" slot="load-deactive"></div>
                        <div class="slot-load" slot="load-beforeDeactive"></div>
                        <div class="slot-load" slot="load-active">下滑加载更多</div>
                        <div class="msg-item" v-for="(item,index) in wordsList" :key="index">
                            <span class="title">{{item.title}}</span>{{item.message}}
                            <div class="edit-bar" v-if="wordsTabCur">
                                <span class="iconfont iconbianji1" @click.stop="bindEdit(item)"></span>
                                <span class="iconfont iconshanchu1" @click.stop="delMsg(item,'删除话术',index)"></span>
                            </div>
                        </div>
                    </vue-scroll>
                </div>
            </div>

        </div>
        <!-- 编辑分组列表 -->
        <Modal
                v-model="editList.status"
                width="300"
                :footer-hide="true"
                :closable="false"
                class-name="vertical-center-modal"
                class="words-box cate-list"
        >
            <div class="mask-title">
                编辑分组
                <span class="iconfont iconcha" @click.stop="editList.status = false"></span>
            </div>
            <div class="list-box">
                <div class="item" v-for="(item,index) in wordsData.cate" :index="index">
                    <span>{{item.name}}</span>
                    <div class="right-box">
                        <span class="iconfont iconbianji1" v-if="index>0" @click.stop="openCate(1,item)"></span>
                        <span class="iconfont iconshanchu1" v-if="index>0" @click.stop="delCate(item,'删除分组',index)"></span>
                    </div>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script>
    import { speeChcraft,serviceCate,addServiceCate,addSpeeChcraft,serviceCateUpdate,editServiceCate} from '@/api/kefu'
    export default {
        name: "words",
        props:{
            isWords:{
                type:Boolean,
                default:false
            }
        },
        computed:{
            cateStatus(){
                if(this.cateData.name && this.cateData.sort){
                    return false
                }else{
                    return true
                }
            },
            msgStatus(){
                if(this.msgData.message){
                    return false
                }else{
                    return true
                }
            }
        },
        data(){
            return {
                isWordShow:false, // 编辑窗
                wordsList: [],
                wordsTab:[
                    {
                        title:'个人库',
                        key:1
                    },
                    {
                        title:'公共库',
                        key:0
                    }
                ],
                wordsTabCur:1,
                wordsData:{
                    isScroll:true,
                    ops:{
                        vuescroll:{
                            mode: 'slide',
                            enable: false,
                            tips: {
                                deactive: 'Push to Load',
                                active: 'Release to Load',
                                start: 'Loading...',
                                beforeDeactive: 'Load Successfully!'
                            },
                            auto: false,
                            autoLoadDistance: 0,
                            pullRefresh: {
                                enable: false
                            },
                            pushLoad: {
                                enable: true,
                                auto: true,
                                autoLoadDistance: 10
                            }
                        },
                        bar:{
                            background:'#393232',
                            opacity:'.5',
                            size:'2px'
                        }
                    },
                    page:1,
                    limit:10,
                    searchTxt:'',
                    cate:[], // 分类
                    cateId:'' // 分类id
                },
                // 分组数据
                cateData:{
                    status:0, // 0 新增 1编辑
                    name:'',
                    sort:'',
                    isCate:false, // 分组状态开关
                    id:''
                },
                // 编辑分组列表
                editList:{
                    status:false
                },
                // 话术添加数据
                msgData:{
                    isCateMeg:false,
                    msgCateId:'',
                    message:'',
                    title:'',
                    status:0, // 0 新增 1修改
                    editId:''
                },
                selectData:''
            }
        },
        mounted() {
            Promise.all([this.getServiceCate()])
        },
        methods:{
            // 关闭添加话术弹窗
            closeMsgBox(){
                this.msgData.isCateMeg = false
            },
            // 选择话术
            selectWords(item){
                this.$emit('selectMsg',item.message)
            },
            // 关闭弹窗
            closeBox(){
                this.$emit('closeBox')
            },
            // 搜索
            bindSearch(){
                this.wordsData.page = 1
                this.wordsData.isScroll = true
                this.wordsList= []
                this.getWordsList()
            },
            // 顶部切换
            bindTab(item){
                this.wordsTabCur = item.key
                this.wordsData.isScroll = true
                this.wordsData.page = 1
                this.wordsData.cate= []
                this.wordsList= []
                this.getServiceCate()
            },
            // 选择话术分类
            changeCate(item){
                this.wordsData.isScroll = true
                this.wordsList = []
                this.wordsData.page = 1
                this.wordsData.cateId = item.id
                this.msgData.msgCateId = item.id
                this.getWordsList()
            },
            // 获取话术分类
            getServiceCate(){
                serviceCate({
                    type:this.wordsTabCur
                }).then(res=>{
                    let tempArr =  JSON.parse(JSON.stringify(res.data.data))
                    this.wordsData.cateId = res.data.data.length? res.data.data[0].id : ''
                    this.msgData.msgCateId = this.wordsData.cateId
                    this.wordsData.cate = res.data.data
                    this.selectData = tempArr
                    this.getWordsList()
                })
            },
            // 话术滚动到底部
            handleWordsScroll(vm, refreshDom, done){
                this.getWordsList()
                done();
            },
            // 常用语
            getWordsList() {
                speeChcraft({
                    page:this.wordsData.page,
                    limit:this.wordsData.limit,
                    title:this.wordsData.searchTxt,
                    cate_id:this.wordsData.cateId,
                    type:this.wordsTabCur
                }).then(res => {
                    this.wordsData.isScroll = res.data.length>=this.wordsData.limit
                    this.wordsList = this.wordsList.concat(res.data)
                    this.wordsData.page++
                })
            },
            // 打开分组弹窗
            openCate(key,item){
                this.cateData.status = key
                this.cateData.isCate = true
                if(key == 1){
                    this.cateData.name = item.name
                    this.cateData.id = item.id
                }
            },
            // 关闭分组弹窗
            closeCate(){
                this.cateData.isCate = false
                this.cateData.name = ''
                this.cateData.sort = ''
            },
            // 分组添加
            cateConfirm(){
                if(!this.cateData.status){
                    addServiceCate({
                        name:this.cateData.name,
                        sort:this.cateData.sort
                    }).then(res=>{
                        this.cateData.isCate = false
                        this.cateData.name = ''
                        this.cateData.sort = ''
                        this.page = 1
                        this.wordsData.isScroll = true
                        this.wordsList = []
                        this.$Message.success(res.msg)
                        this.getServiceCate()
                    }).catch(error=>{
                        this.$Message.error(error.msg)
                    })
                }else{
                    editServiceCate(this.cateData.id,{
                        name:this.cateData.name,
                        sort:this.cateData.sort
                    }).then(res=>{
                        this.cateData.isCate = false
                        this.cateData.name = ''
                        this.cateData.sort = ''
                        this.cateData.id = ""
                        this.page = 1
                        this.wordsData.isScroll = true
                        this.wordsList = []
                        this.$Message.success(res.msg)
                        this.getServiceCate()
                    })
                }

            },
            // 话术打开
            addMsg(){
                this.msgData.isCateMeg = true
                this.msgData.status = 0
            },
            // 话术添加
            msgConfirm(){
                if(!this.msgData.status){
                    addSpeeChcraft({
                        title:this.msgData.title,
                        cate_id:this.msgData.msgCateId,
                        message:this.msgData.message
                    }).then(res=>{
                        this.msgData.isCateMeg = false
                        this.msgData.title = ''
                        this.msgData.message = ""
                        this.msgData.msgCateId = this.wordsData.cateId
                        this.$Message.success(res.msg)
                        this.wordsData.isScroll = true
                        this.wordsData.page = 1
                        this.wordsList= []
                        this.getWordsList()
                    }).catch(error=>{
                        this.$Message.error(error.msg)
                    })
                }else{
                    serviceCateUpdate(this.msgData.editId,{
                        title:this.msgData.title,
                        cate_id:this.msgData.msgCateId,
                        message:this.msgData.message
                    }).then(res=>{
                        this.msgData.isCateMeg = false
                        this.msgData.title = ''
                        this.msgData.message = ""
                        this.msgData.msgCateId = this.wordsData.cateId
                        this.$Message.success(res.msg)
                        this.wordsData.isScroll = true
                        this.wordsData.page = 1
                        this.wordsList= []
                        this.getWordsList()
                    })
                }
            },
            // 编辑话术
            bindEdit(item){
                this.msgData.status = 1
                this.msgData.isCateMeg = true
                this.msgData.message = item.message
                this.msgData.title = item.title
                this.msgData.editId = item.id
            },
            // 删除话术
            delMsg(row, tit, num){
                let delfromData = {
                    title: tit,
                    num: num,
                    url: `/service/speechcraft/${row.id}`,
                    method: 'DELETE',
                    ids: '',
                    kefu:true
                };
                this.$modalSure(delfromData).then((res) => {
                    this.wordsList.splice(num,1)
                    this.$Message.success(res.msg);
                }).catch(res => {
                    this.$Message.error(res.msg);
                });

            },
            delCate(row, tit, num){
                let delfromData = {
                    title: tit,
                    num: num,
                    url: `/service/cate/${row.id}`,
                    method: 'DELETE',
                    ids: '',
                    kefu:true
                };
                this.$modalSure(delfromData).then((res) => {
                    this.wordsData.cate.splice(num,1)
                    this.page = 1
                    this.wordsData.isScroll = true
                    this.wordsList = []
                    this.$Message.success(res.msg)
                    this.getServiceCate()
                }).catch(res => {
                    this.$Message.error(res.msg);
                });
            }
        }
    }
</script>

<style lang="stylus" scoped>
    .words-mask {
        z-index: 50;
        position: fixed;
        left: 0;
        top: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);

        .content {
            position: absolute;
            left: 0;
            right: 0;
            top: 1.14rem;
            bottom: 0;
            display: flex;
            flex-direction: column;
            background: #fff;
            border-radius: 0.06rem 0.06rem 0px 0px;

            .title-box {
                padding: 0 .3rem .3rem;
                position: relative;
                border-bottom 1px solid #F5F6F9
                .tab-box{
                    position relative
                    display flex
                    justify-content space-between
                    padding .4rem 2.2rem .3rem
                    font-size .32rem
                    color #9F9F9F
                    .on{
                        color #3875EA
                        font-weight bold
                    }
                    .right-icon{
                        position absolute
                        right 0
                        top 50%
                        transform translateY(-50%)
                        .iconfont{
                            margin-left .2rem
                            font-size .48rem
                            color #C8CAD0
                        }
                    }
                }
                .input-box{
                    display: flex;
                    align-items: center;
                    width: 6.9rem;
                    height: .64rem;
                    padding-right: 0.05rem;
                    margin-left: .18rem;
                    border-radius: .32rem;
                    overflow hidden

                    >>> .ivu-input{
                        background #F5F6F9
                    }
                    >>> .ivu-input, .ivu-input:hover, .ivu-input:focus {
                        border transparent
                        box-shadow: none;
                    }
                }
                .icon-cha1 {
                    position: absolute;
                    right: 0;
                    top: 50%;
                    transform: translateY(-50%);
                }
            }

            .scroll-box {
                flex: 1;
                display flex
                overflow: hidden;
                .scroll-left{
                    width 1.76rem
                    height 100%
                    overflow-y scroll
                    -webkit-overflow-scrolling touch
                    background #F5F6F9
                    .left-item{
                        position relative
                        display flex
                        align-items center
                        justify-content center
                        width 100%
                        height 1.09rem
                        color #282828
                        font-size .26rem
                        &.active{
                            color #3875EA
                            background #fff
                            &:after{
                                content ' '
                                position: absolute;
                                left 0
                                top 50%
                                transform translateY(-50%)
                                width 0.06rem
                                height .46rem
                                background #3875EA
                            }
                        }
                        &.add_cate{
                            color #9F9F9F
                            font-size .26rem
                            .iconfont{
                                margin-right 0.1rem
                                font-size .24rem
                            }
                        }
                    }
                }
                .right-box{
                    flex 1
                    overflow scroll
                    -webkit-overflow-scrolling touch
                }
                .msg-item {
                    padding: .25rem .3rem;
                    color #888888
                    font-size .28rem
                    .title{
                        margin-right .2rem
                        color #282828
                    }
                    &.add-mg{
                        display flex
                        align-items center
                        justify-content flex-end
                        font-size .28rem
                        padding .15rem .3rem
                        .iconfont{
                            font-size .36rem
                            margin-right .1rem
                        }
                    }
                }
            }
        }
    }

    .words-box{
        .mask-title{
            position relative
            text-align center
            margin-bottom .5rem
            color #282828
            font-size .32rem
            font-weight bold
            .iconfont{
                position absolute
                right 0
                top 50%
                transform translateY(-50%)
                color #C8CAD0
                font-size .44rem
                font-weight normal
            }
        }
        .input-box{
            height .68rem
            margin-top .32rem
            background #F5F5F5
            border-radius: .14rem;
            &.text-area{
                height 1.92rem
                textarea{
                    display block
                    height 100%
                }
            }
        }
        .subBtn{
            width 100%
            height .86rem
            margin-top .6rem
            margin-bottom .3rem
            font-size .3rem !important
            border-radius: .43rem;
            &[disabled]{
                background #C8CAD0
                color #fff
                font-size .3rem !important
            }
        }
    }
    .edit-box{
        z-index 99
        position fixed
        left 0
        right 0
        top 0
        bottom 0
        display flex
        flex-direction column
        background #fff
        .head{
            padding .4rem .3rem .3rem
            .tit-bar{
                position relative
                text-align center
                font-size .32rem
                color #282828
                font-weight bold
                span{
                    position absolute
                    right 0
                    top 50%
                    transform translateY(-50%)
                    color #3875EA
                    font-size .28rem
                    font-weight normal
                }
            }
            .input-box{
                margin-top .3rem
                background #F5F6F9
                border-radius: .39rem;
            }
        }
        .scroll-box {
            flex: 1;
            display flex
            overflow: hidden;
            .scroll-left{
                display flex
                flex-direction column
                position relative
                width 1.76rem
                height 100%
                background #F5F6F9
                .top{
                    flex 1
                    overflow-y scroll
                    -webkit-overflow-scrolling touch
                }

                .left-item{
                    position relative
                    display flex
                    align-items center
                    justify-content center
                    width 100%
                    height 1.09rem
                    color #282828
                    font-size .26rem
                    &.active{
                        color #3875EA
                        background #fff
                        &:after{
                            content ' '
                            position: absolute;
                            left 0
                            top 50%
                            transform translateY(-50%)
                            width 0.06rem
                            height .46rem
                            background #3875EA
                        }
                    }
                    &.add_cate{
                        color #9F9F9F
                        font-size .26rem
                        .iconfont{
                            margin-right 0.1rem
                            font-size .24rem
                        }
                    }
                    &.edits-box{
                        color #3875ea
                    }
                }
            }
            .right-box{
                flex 1
                padding-left .3rem
            }
            .msg-item {
                padding: .25rem .3rem .25rem 0;
                color #888888
                font-size .28rem
                .title{
                    margin-right .2rem
                    color #282828
                }
                &.add-mg{
                    display flex
                    align-items center
                    justify-content flex-end
                    font-size .28rem
                    padding .15rem .3rem
                    .iconfont{
                        font-size .36rem
                        margin-right .1rem
                    }
                }
                .edit-bar{
                    display flex
                    align-items center
                    justify-content flex-end
                    margin-top .25rem
                    padding-bottom .1rem
                    border-bottom 1px solid #F0F2F7
                    .iconfont{
                        margin-left .3rem
                        font-size .32rem
                    }
                }
            }
        }
    }
    .cate-list{
        .list-box{
            max-height 7.5rem
            overflow-y scroll
            -webkit-overflow-scrolling touch
            .item{
                display flex
                align-items center
                justify-content space-between
                height 1rem
                border-bottom 1px solid #F0F2F7
                color #282828
                font-size .28rem
                .iconfont{
                    color #9F9F9F
                    font-size .32rem
                    margin-left .4rem
                }
            }
        }
    }
</style>
<style>
    .kf_mobile .ivu-modal-wrap{
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .vertical-center-modal{
        display: flex;
        align-items: center;
        justify-content: center;


    }
    .ivu-modal{
        top: 0;
    }
    .noinput input, .noinput textarea{
        border-color: transparent !important;
        background: transparent !important;
        resize: none;
    }
    .noinput input:hover,.noinput input:focus,.noinput textarea:hover,.noinput textarea:focus{
        border-color: transparent !important;
        box-shadow:none !important;
    }
</style>
