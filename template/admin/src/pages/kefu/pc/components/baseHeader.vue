<template>
    <div class="base-header">
        <div class="left-wrapper">
            <Input class="search_box" prefix="ios-search" placeholder="搜索用户名称" @on-enter="bindSearch" @on-change="inputChange"/>
            <div class="user_info">
                <img v-lazy="kefuInfo.avatar"
                     alt="">
                <span>{{kefuInfo.nickname}}</span>
                <div  class="status-box">
                    <div class="status" :class="online ? 'on':'off'" @click.stop="setOnline">
                        <span class="dot"></span>
                        {{online ? '在线': '下线'}}
                    </div>

                    <div class="online-down" v-show="isOnline">
                        <div class="item" @click.stop="changeOnline(1)"><span class="iconfont iconduihao" v-if="online"></span><i class="green"></i>在线</div>
                        <div class="item" @click.stop="changeOnline(0)"><span class="iconfont iconduihao" v-if="!online"></span><i></i>下线</div>
                    </div>
                </div>

            </div>
            <div class="out-btn" @click.stop="outLogin">退出登录</div>
        </div>
        <div class="right-menu">
            <div class="menu-item" :class="{on:index == curIndex }" v-for="(item,index) in menuList" :key="index" @click.stop="selectTab(item)">{{item.title}}</div>
        </div>
    </div>
</template>

<script>
    import { mapState, mapActions } from 'vuex';
    import bus from '@/utils/bus'
    export default {
        name: "baseHeader",
        props:{
            kefuInfo:{
                type:Object,
                default:function () {
                    return {}
                }
            },
            online:{
                type:Boolean | Number,
                default:true
            }
        },
        computed: {
        },
        data() {
            return {
                menuList: [
                    {
                        key: 0,
                        title: '客户信息',
                    },
                    {
                        key: 1,
                        title: '交易订单',
                    },
                    {
                        key: 2,
                        title: '商品信息',
                    },
                ],
                curIndex: 0,
                isOnline:false
            }
        },
        mounted() {
            document.addEventListener('click',()=>{
                this.isOnline = false
            })
        },
        methods:{
            ...mapActions('kefu/', [
                'logout',
                'logoutKefu'
            ]),
            selectTab(item){
                this.curIndex = item.key
                this.bus.$emit('selectRightMenu',this.curIndex)
            },
            setOnline(){
                this.isOnline = !this.isOnline

            },
            changeOnline(type){
                this.$emit('setOnline',type);
                this.isOnline = false
            },
            // 退出登录
            outLogin(){
                let self = this
                this.$Modal.confirm({
                    title: '退出登录确认',
                    content: '您确定退出登录当前账户吗？打开的标签页和个人设置将会保存。',
                    onOk: () => {
                        self.logoutKefu({
                            confirm: false,
                            vm: self
                        });
                    },
                    onCancel: () => {

                    }
                });

            },
            // 搜索
            bindSearch(e){
                this.$emit('search', e.target.value);
            },
            // inputChange
            inputChange(e){
                this.bus.$emit('change',e.target.value)
            }
        }
    }
</script>

<style lang="stylus" scoped>
    .base-header
        z-index 99
        position relative
        display flex
        align-items center
        justify-content space-between
        height 66px
        padding 0 0 0 15px
        background: linear-gradient(270deg, #1890FF 0%, #3875EA 100%);
        color #fff
        flex-shrink 0
        .left-wrapper
            position relative
            display flex
            flex 1
            align-items center

            .search_box
                width 295px
                border-radius 17px
                overflow hidden

            .user_info
                display flex
                align-items center
                margin-left 30px

                img
                    width 40px
                    height 40px
                    margin-right 10px
                    border-radius 50%

                span
                    font-size 16px

                .status-box
                    position relative
                    cursor pointer
                .status
                    display flex
                    align-items center
                    padding 0 10px
                    margin-left 5px
                    background #EAFFEB
                    color: rgba(0, 0, 0, 0.65);
                    border-radius 9px

                    .dot
                        width 6px
                        height 6px
                        margin-right 3px
                        border-radius 50%
                        background #48D452

                    &.off
                        background #F3F3F3

                        .dot
                            background #999999
                .online-down
                    z-index 50
                    position absolute
                    left 0
                    bottom -70px
                    width 86px
                    background #fff
                    color #333
                    box-shadow: 0px 2px 4px 0px rgba(0, 0, 0, 0.08);
                    border-radius: 5px;
                    .item
                        position relative
                        display flex
                        align-items center
                        padding 7px 10px 7px 30px
                        cursor pointer
                        i
                            width 10px
                            height 10px
                            margin-right 8px
                            border-radius 50%
                            background #999999
                            &.green
                                background #48D452
                        .iconfont
                            position absolute
                            left 10px
                            top 50%
                            transform translateY(-50%)
                            font-size 12px

            .out-btn
                position absolute
                right 30px
                top 50%
                transform translateY(-50%)
                width: 86px;
                height: 26px;
                line-height 28px
                text-align center
                background: #FFFFFF;
                border-radius: 16px;
                color #3875EA
                font-size 13px
                cursor pointer

        .right-menu
            display flex
            align-items center
            .menu-item
                position relative
                margin-right 30px
                font-size 14px
                font-weight 400
                cursor pointer
                &.on
                    font-weight 600
                    &::after
                        position: absolute;
                        left 0
                        bottom -22px
                        content ''
                        width 100%
                        height 2px
                        background #fff
</style>
