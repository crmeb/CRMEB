<template>
    <div class="chatList">
        <div class="tab-head">
            <div class="item" :class="{active:item.key == hdTabCur}" v-for="(item, index) in hdTab" :key="index" @click="changeTab(item)">{{item.title}}</div>
        </div>
        <div class="scroll-box">
            <vue-scroll :ops="ops" @handle-scroll="handleScroll" v-if="userList.length>0"
            >
                <div class="chat-item" v-for="(item,index) in userList" :key="index" :class="{active:curId == item.id}" @click="selectUser(item)">
                    <div class="avatar">
                        <img v-lazy="item.avatar" alt="">
                        <div class="status" :class="{off:item.online == 0}"></div>
                    </div>
                    <div class="user-info">
                        <div class="hd">
                            <span class="name line1">{{item.nickname}}</span>
                            <template v-if="item.type == 2">
                                <span class="label">小程序</span>
                            </template>
                            <template v-if="item.type == 3">
                                <span class="label H5">H5</span>
                            </template>
                            <template v-if="item.type == 1">
                                <span class="label wechat">公众号</span>
                            </template>
                            <template v-if="item.type == 0">
                                <span class="label pc">PC端</span>
                            </template>
                        </div>
                        <div class="bd line1">
                            <template v-if="item.message_type <=2">{{item.message}}</template>
                            <template v-if="item.message_type ==3">[图片]</template>
                            <template v-if="item.message_type ==5">[商品]</template>
                            <template v-if="item.message_type ==6">[订单]</template>
                        </div>
                    </div>
                    <div class="right-box">
                        <div class="time">{{item.update_time | toDay}}</div>
                        <div class="num">
                            <Badge :count="item.mssage_num">
                                <a href="#" class="demo-badge"></a>
                            </Badge>
                        </div>
                    </div>
                </div>
            </vue-scroll>
            <empty v-else msg="暂无用户列表" status="1"></empty>
        </div>




    </div>
</template>

<script>
    import {Socket} from '@/libs/socket';
    import dayjs from 'dayjs'
    import { record } from '@/api/kefu'
    import { HappyScroll } from 'vue-happy-scroll'
    import empty from "../../components/empty";
    import {forEach} from "../../../../libs/tools";
    export default {
        name: "chatList",
        props:{
            userOnline:{
                type:Object,
                default:function () {
                    return {}
                }
            },
            newRecored:{
                type:Object,
                default:function () {
                    return {}
                }
            },
            searchData:{
                type:String,
                default:''
            }
        },
        components:{
            HappyScroll,
            empty
        },
        watch:{
            userOnline:{
                handler(nVal,oVal){
                    if(nVal.hasOwnProperty('to_uid')){
                        this.userList.forEach((el,index)=>{
                            if(el.to_uid == nVal.to_uid){
                                el.online = nVal.online
                                if(nVal.online == 1){
                                    this.$Notice.info({
                                        title: '上线通知',
                                        desc: `${el.nickname}上线`
                                    });
                                }

                            }
                        })
                    }
                },
                deep:true
            },
            searchData:{
                handler(nVal,oVal){
                    if(nVal !=oVal){
                        this.nickname = nVal
                        this.page = 1
                        this.isScroll = true
                        this.userList = []
                        this.isSearch = true
                        this.getList()
                    }
                },
                deep:true
            }
        },
        data(){
            return {
                hdTabCur:0,
                hdTab:[
                    {
                        key:0,
                        title:'用户列表'
                    },
                    {
                        key:1,
                        title:'游客列表'
                    }
                ],
                userList:[],
                curId:'',
                page:1,
                limit:15,
                isScroll:true,
                nickname:'',
                isSearch:false,
                ops:{
                    vuescroll:{
                        mode: 'native',
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
                        size:'5px'
                    }
                },
            }
        },
        filters: {
            toDay: function (value) {
                if (!value) return ''
                return  dayjs.unix(value).format('M月D日 HH:mm')

            }
        },
        mounted() {
            let that = this
            Socket.then(ws=>{
                //用户转接
                ws.$on('transfer', data => {
                    let status = false
                    that.userList.forEach((el,index,arr)=>{
                        if(data.recored.id == el.id){
                            status = true
                            if(data.recored.is_tourist == that.hdTabCur){
                                let oldVal = data.recored
                                arr.splice(index,1)

                                if(index == 0){
                                    this.$emit('setDataId',oldVal)
                                    oldVal.mssage_num = 0
                                }
                                arr.unshift(oldVal)
                            }
                            this.$Notice.info({
                                title: '您有一条转接消息！',
                            });
                        }
                    })
                    if(!status){
                        if(data.recored.is_tourist == this.hdTabCur)
                            this.userList.unshift(data.recored)
                    }
                })
                ws.$on('mssage_num', data => {
                    if(data.recored.id){
                        let status = false
                        that.userList.forEach((el,index,arr)=>{
                            if(data.recored.id == el.id){
                                status = true
                                if(data.recored.is_tourist == that.hdTabCur){
                                    let oldVal = data.recored
                                    arr.splice(index,1)
                                    arr.unshift(oldVal)
                                }
                            }
                        })
                        if(!status){
                            if(data.recored.is_tourist == this.hdTabCur)
                                this.userList.unshift(data.recored)
                        }
                    }
                    if(data.recored.is_tourist != this.hdTabCur && data.recored.id){
                        this.$Notice.info({
                            title: this.hdTabCur?'用户发来消息啦！':'游客发来消息啦！',
                        });
                    }
                })
            });
            this.bus.$on('change', data => {
                this.nickname = data
            })
            this.getList()

        },
        methods:{
            //切换
            changeTab(item){
                if(this.hdTabCur == item.key) return
                this.hdTabCur = item.key
                this.isScroll = true
                this.page = 1
                this.userList = []
                this.$emit('changeType',item.key)
                this.getList()
            },
            getList(){
                if(!this.isScroll) return
                record({
                    nickname:this.nickname,
                    page:this.page,
                    limit:this.limit,
                    is_tourist:this.hdTabCur
                }).then( res => {
                    if(res.data.length>0){
                        res.data[0].mssage_num = 0
                        this.isScroll = res.data.length>=this.limit

                        this.userList = this.userList.concat(res.data)

                        if (this.page == 1 && res.data.length>0 &&!this.isSearch ) {
                            this.curId = res.data[0].id
                            this.$emit('setDataId',res.data[0])
                        }
                        this.page++
                    }else{
                        this.$emit('setDataId',0)
                    }

                })
            },
            chartReachBottom(){
                this.getList()
            },
            // 选择用户
            selectUser(item){
                if(this.curId == item.id) return
                item.mssage_num = 0
                this.curId = item.id
                this.$emit('setDataId',item)
            },
            handleScroll(vertical, horizontal, nativeEvent) {
                if(vertical.process == 1){
                    this.getList()
                }
            }
        }
    }
</script>

<style lang="stylus" scoped>
.chatList
    display flex
    flex-direction column
    width 320px
    height 742px
    border-right 1px solid #ECECEC
    .tab-head
        display flex
        align-items center
        justify-content space-between
        height 50px
        flex-shrink 0
        padding 0 52px
        font-size 14px
        color #000000
        .item
            position relative
            cursor pointer
            &:after
                display none
                content ' '
                position absolute
                left 50%
                bottom -15px
                transform translateX(-50%)
                height 2px
                width 100%
                background #1890FF
            &.active
                color #1890FF
                &:after
                    display block

    .scroll-box
        flex 1
        height 500px
        overflow hidden
    .chat-item
        display flex
        align-items center
        justify-content space-between
        padding 12px 10px
        height 74px
        box-sizing border-box
        border-left 3px solid transparent
        cursor pointer
        &.active
            background #EFF0F1
            border-left 3px solid #1890FF
        .avatar
            position relative
            width 40px
            height 40px
            img
                display block
                width 100%
                height 100%
                border-radius 50%
            .status
                position absolute
                right 3px
                bottom 0
                width 8px
                height 8px
                background #48D452
                border 1px solid #fff
                border-radius 50%
                &.off
                    background #999999
        .user-info
            width 155px
            margin-left 12px
            margin-top: 5px;
            font-size 16px
            .hd
                display flex
                align-items center
                color: rgba(0, 0, 0, 0.65);
                .name
                    max-width 67%
                .label
                    margin-left 5px
                    color #3875EA
                    font-size 12px
                    background #D8E5FF
                    border-radius 2px
                    padding 1px 5px
                    &.H5
                        background #FAF1D0
                        color #DC9A04
                    &.wechat
                        background: rgba(64, 194, 73, 0.16);
                        color #40C249
                    &.pc
                        background: rgba(100, 64, 194, 0.16);;
                        color #6440C2
            .bd
                margin-top 3px
                font-size 12px
                color #8E959E
        .right-box
            position relative
            flex 1
            display flex
            flex-direction column
            align-items flex-end
            color #8E959E
            .num
                margin-right 12px
.chart-scroll
    margin-top -10px
</style>

