<template>
    <div class="mobile-page paddingBox" v-if="bgColor.length>0">
        <div class="home-hot" :style="{marginTop:slider+'px',background:boxColor}">
            <div class="hd">
                <p class="txt" :style="{color:txtColor}">{{titleTxt}}</p>
                <p class="color-txt" :style="`background: linear-gradient(90deg,${bgColor[0].item},${bgColor[1].item});`">{{msgTxt}}</p>
            </div>
            <div class="bd">
                <div class="item" v-for="(item,index) in hotList" :key="index">
                    <div class="left">
                        <div class="title">{{item.info[0].value}}</div>
                        <div class="des">{{item.info[1].value}}</div>
                        <div class="link">GO！</div>
                    </div>
                    <div class="img-box">
                        <img :src="item.img" alt="" v-if="item.img">
                        <div class="empty-box on" v-else><span class="iconfont-diy icontupian"></span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    export default {
        name: 'home_hot',
        cname: '活动魔方',
        icon:'iconhuodongmofang1',
        configName: 'c_home_hot',
        type:1,// 0 基础组件 1 营销组件 2工具组件
        defaultName:'activeParty', // 外面匹配名称
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
                    name: 'activeParty',
                    timestamp: this.num,
                    setUp: {
                        tabVal: 0
                    },
                    titleConfig: {
                        title: '促销标题',
                        value: '超值爆款',
                        place: '请输入标题',
                        max: 10
                    },
                    desConfig: {
                        title: '促销简介',
                        value: '美好生活由此开始',
                        place: '请输入简介',
                        max: 8
                    },
                    menuConfig: {
                        title: '最多可添加4个版块，图片建议尺寸140 * 140px；鼠标拖拽左侧圆点可 调整版块顺序',
                        maxList: 4,
                        list: [
                            {
                                img: '',
                                info: [
                                    {
                                        title: '标题',
                                        value: '今日推荐',
                                        tips: '选填，不超过4个字',
                                        max: 4
                                    },
                                    {
                                        title: '简介',
                                        value: '店主诚意推荐 品质商品',
                                        tips: '选填，不超过20个字',
                                        max: 20
                                    },
                                    {
                                        title: '链接',
                                        value: '',
                                        tips: '请输入链接',
                                        max: 100
                                    }
                                ]

                            },
                            {
                                img: '',
                                info: [
                                    {
                                        title: '标题',
                                        value: '热门榜单',
                                        tips: '选填，不超过4个字',
                                        max: 4
                                    },
                                    {
                                        title: '简介',
                                        value: '店主诚意推荐 品质商品',
                                        tips: '选填，不超过20个字',
                                        max: 20
                                    },
                                    {
                                        title: '链接',
                                        value: '',
                                        tips: '请输入链接',
                                        max: 100
                                    }
                                ]

                            },
                            {
                                img: '',
                                info: [
                                    {
                                        title: '标题',
                                        value: '首发新品',
                                        tips: '选填，不超过4个字',
                                        max: 4
                                    },
                                    {
                                        title: '简介',
                                        value: '新品上架等 你来拿',
                                        tips: '选填，不超过20个字',
                                        max: 20
                                    },
                                    {
                                        title: '链接',
                                        value: '',
                                        tips: '请输入链接',
                                        max: 100
                                    }
                                ]

                            },
                            {
                                img: '',
                                info: [
                                    {
                                        title: '标题',
                                        value: '促销单品',
                                        tips: '选填，不超过4个字',
                                        max: 4
                                    },
                                    {
                                        title: '简介',
                                        value: '综合评选好 产品',
                                        tips: '选填，不超过20个字',
                                        max: 20
                                    },
                                    {
                                        title: '链接',
                                        value: '',
                                        tips: '请输入链接',
                                        max: 100
                                    }
                                ]
                            }
                        ]
                    },
                    themeColor: {
                        title: '主题颜色',
                        name: 'themeColor',
                        default: [{
                            item: '#fc3c3e'
                        }],
                        color: [
                            {
                                item: '#fc3c3e'
                            }

                        ]
                    },
                    bgColor: {
                        title: '标签背景颜色',
                        name: 'bgColor',
                        default: [
                            {
                                item: '#F62C2C'
                            },
                            {
                                item: '#F96E29'
                            }
                        ],
                        color: [
                            {
                                item: '#F62C2C'
                            },
                            {
                                item: '#F96E29'
                            }
                        ]
                    },
                    boxColor: {
                        title: '背景颜色',
                        name: 'boxColor',
                        default: [{
                            item: '#ffe5e3'
                        }],
                        color: [
                            {
                                item: '#ffe5e3'
                            }

                        ]
                    },
                    // 页面间距
                    mbConfig: {
                        title: '页面间距',
                        val: 0,
                        min: 0
                    }
                },
                titleTxt: '',
                msgTxt: '',
                slider: '',
                hotList: [],
                txtColor: '',
                bgColor: [],
                pageData: {},
                boxColor:''
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
                if(data.mbConfig){
                    this.titleTxt = data.titleConfig.value;
                    this.msgTxt = data.desConfig.value;
                    this.slider = data.mbConfig.val;
                    this.hotList = data.menuConfig.list;
                    this.txtColor = data.themeColor.color[0].item;
                    this.bgColor = data.bgColor.color;
                    this.boxColor = data.boxColor.color[0].item;
                }
                // if (Object.keys(data).length > 0) {
                //     this.titleTxt = data.inputList[0].value;
                //     this.msgTxt = data.inputList[1].value;
                //     this.slider = data.sliderList[0].val;
                //     this.hotList = data.menu;
                //     this.txtColor = data.colorList[0].color[0].item;
                //     this.bgColor = data.colorList[1].color;
                // }
            }
        }
    }
</script>

<style scoped lang="stylus">
    .paddingBox{
        padding 0 10px!important
    }
    .home-hot
        padding 15px 10px
        background #FFE5E3
        border-radius:12px;
        .hd
            display flex
            align-items center
            .txt
                margin-right 10px
                color #FC3C3E
                font-size 16px
                font-weight bold
            .color-txt
                width: 110px;
                height: 18px;
                border-radius: 13px 0 13px 0;
                color: #fff;
                text-align: center;
                font-size: 11px;
                box-shadow: 3px 1px 1px 1px rgba(255,203,199,.8);
        .bd
            display flex
            flex-wrap wrap
            .item
                display flex
                width 158px
                margin-top 10px
                margin-right 13px
                padding 10px
                background #fff
                border-radius:8px;
                &:nth-child(2n)
                    margin-right 0
                .left
                    width 69px
                    .title
                        font-size 14px
                    .des
                        font-size 12px
                        color #999999
                    .link
                        width:56px;
                        height:18px;
                        padding 0 10px
                        margin-top: 3px;
                        background:linear-gradient(90deg,#4BC4FF,#207EFF 100%);
                        border-radius:9px;
                        color #fff
                        font-size 13px
                .img-box
                    flex 1
                    img
                        width 100%
                        height 100%
                    .box
                        width 100%
                        height 100%
                        background #D8D8D8
                &:nth-child(2) .left .link
                    background:linear-gradient(90deg,#FF9043,#FF531D 100%);
                &:nth-child(3) .left .link
                    background:linear-gradient(90deg,#96E187,#48CE2C 100%);
                &:nth-child(4) .left .link
                    background:linear-gradient(90deg,#FFC560,#FF9C00 100%);
</style>
