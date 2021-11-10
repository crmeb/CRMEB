<template>
    <div class="mobile-page" :style="{padding:'0 '+prConfig+'px'}">
        <div class="home_bargain" :class="bgStyle===0?'bargainOn':''" :style="{background: `linear-gradient(180deg,${bgColor[0].item} 0%,${bgColor[1].item} 100%)`,marginTop:`${mTop}px`}" v-if="bgColor.length>0">
            <div class="title-bar" :style="{color:titleColor}">砍价专区·BARGAINING</div>
            <div class="list-wrapper">
                <div class="item" v-for="(item,index) in list" :key="index" :style="{marginRight:listRight+'px'}">
                    <div class="img-box">
                        <img v-if="item.img" :src="item.img" alt="">
                        <div class="empty-box on" v-else>
                            <span class="iconfont-diy icontupian"></span>
                        </div>
                    </div>
                    <div class="con-box" v-if="bntShow || priceShow">
                        <div class="price" v-if="priceShow">
                            <span :style="{color:txtColor}">￥</span>
                            <p :style="{color:txtColor}">{{item.price}}</p>
                        </div>
                        <div v-if="bntShow" class="btn" :style="{background: txtColor}">立即砍价</div>
                    </div>
                </div>
            </div>
            <div class="doc">
                <span class="active"></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    export default {
        name: 'home_bargain',
        cname: '砍价',
        icon:'iconkanjia1',
        configName: 'c_home_bargain',
        type:1,// 0 基础组件 1 营销组件 2工具组件
        defaultName:'bargain', // 外面匹配名称
        props: {
            index: {
                type: null
            },
            num: {
                type: null
            }
        },
        computed: {
            ...mapState('admin/mobildConfig', ['defaultArray'])
        },
        watch: {
            pageData: {
                handler (nVal, oVal) {
                    this.setConfig(nVal)
                },
                deep: true
            },
            num: {
                handler (nVal, oVal) {
                    let data = this.$store.state.admin.mobildConfig.defaultArray[nVal]
                    this.setConfig(data)
                },
                deep: true
            },
            'defaultArray': {
                handler (nVal, oVal) {
                    let data = this.$store.state.admin.mobildConfig.defaultArray[this.num]
                    this.setConfig(data);
                },
                deep: true
            }
        },
        data () {
            return {
                // 默认初始化数据禁止修改
                defaultConfig: {
                    name: 'bargain',
                    timestamp: this.num,
                    setUp: {
                        tabVal: 0
                    },
                    numConfig: {
                        val: 3
                    },
                    priceShow: {
                        title: '是否显示价格',
                        val: true
                    },
                    bntShow: {
                        title: '是否显示按钮',
                        val: true
                    },
                    themeColor: {
                        title: '主题风格',
                        name: 'themeColor',
                        default: [{
                            item: '#E93323'
                        }],
                        color: [
                            {
                                item: '#E93323'
                            }
                        ]
                    },
                    titleColor: {
                        title: '标题颜色',
                        name: 'txtColor',
                        default: [{
                            item: '#FF6000'
                        }],
                        color: [
                            {
                                item: '#FF6000'
                            }
                        ]
                    },
                    bgColor: {
                        title: '背景颜色',
                        name: 'bgColor',
                        default: [
                            {
                                item: '#FDDBB2'
                            },
                            {
                                item: '#FDEFC6'
                            }
                        ],
                        color: [
                            {
                                item: '#FDDBB2'
                            },
                            {
                                item: '#FDEFC6'
                            }

                        ]
                    },
                    bgStyle: {
                        title: '背景样式',
                        name: 'bgStyle',
                        type: 1,
                        list: [
                            {
                                val: '直角',
                                icon: 'iconPic_square'
                            },
                            {
                                val: '圆角',
                                icon: 'iconPic_fillet'
                            }
                        ]
                    },
                    prConfig: {
                        title: '背景边距',
                        val: 10,
                        min: 0
                    },
                    productGap: {
                        title: '商品间距',
                        val: 10,
                        min: 0
                    },
                    mbCongfig: {
                        title: '页面间距',
                        val: 0,
                        min: 0
                    }
                },
                bgColor: '',
                mTop: '',
                txtColor: '',
                listRight: '',
                titleColor: '',
                list: [
                    {
                        img: '',
                        price: '234'
                    },
                    {
                        img: '',
                        price: '234'
                    },
                    {
                        img: '',
                        price: '234'
                    }
                ],
                priceShow: true,
                bntShow: true,
                pageData: {},
                bgStyle:1,
                prConfig:0
            }
        },
        mounted () {
            this.$nextTick(() => {
                this.pageData = this.$store.state.admin.mobildConfig.defaultArray[this.num]
                this.setConfig(this.pageData)
            })
        },
        methods: {
            setConfig (data) {
                if(!data) return
                if(data.mbCongfig){
                    this.bgColor = data.bgColor.color;
                    this.mTop = data.mbCongfig.val;
                    this.txtColor = data.themeColor.color[0].item;
                    this.listRight = data.productGap.val;
                    this.titleColor = data.titleColor.color[0].item;
                    this.priceShow = data.priceShow.val;
                    this.prConfig = data.prConfig.val;
                    this.bgStyle = data.bgStyle.type;
                    this.bntShow = data.bntShow.val;
                }
            }
        }
    }
</script>

<style scoped lang="stylus">
.mobile-page{
    /*margin-bottom 10px*/
}
.bargainOn{
    border-radius 0!important
}
.home_bargain
    width 100%
    padding 15px 10px 10px
    background-size 100% 100%
    border-radius 6px
    .title-bar
        width 100%
        height 29px
        font-size 19px
        text-align center
    .list-wrapper
        display flex
        margin-top 10px
        width 100%
        overflow hidden
        .item
            flex-shrink 0
            width 105px
            border-radius:8px 8px 0px 0px;
            overflow hidden
            .img-box
                width 100%
                height 105px
                img,.box
                    width 100%
                    height 100%
                .box
                    background #D8D8D8
            .con-box
                display flex
                flex-direction column
                align-items center
                padding 6px 0 10px
                background #fff
                border-radius:0 0 8px 8px;
                .price
                    display flex
                    align-items center
                    justify-content center
                    color #FF4444
                    p
                        font-size 16px
                        font-weight bold
                    span
                        font-size 12px
                .btn
                    width:68px;
                    height:17px;
                    background:linear-gradient(270deg,rgba(255,84,0,1) 0%,rgba(255,0,0,1) 100%);
                    border-radius:9px;
                    color #fff
                    text-align center
                    line-height 19px
                    font-size 12px
    .doc
        display flex
        align-items center
        justify-content center
        margin-top 10px
        span
            width:4px;
            height:2px;
            margin 0 3px
            background:#979797;
            &.active
                width 8px
                background #fff
</style>
