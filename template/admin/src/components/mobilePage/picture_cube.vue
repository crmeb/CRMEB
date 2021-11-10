<template>
    <div class="mobile-page">
        <div class="advert" :class="{pageOn:bgStyle===1}" :style="{marginLeft:prConfig+'px',marginRight:prConfig+'px',marginTop:slider+'px',background:bgColor}">
            <div class="advertItem01 acea-row" v-if="style===0" v-for="(item,index) in picList" :key="index">
                <img :src="item.image" v-if="item.image">
                <div class="empty-box" v-else>
                    <span class="iconfont-diy icontupian"></span>
                </div>
            </div>
            <div class="advertItem02 acea-row" v-if="style===1">
                <div class="item" v-for="(item,index) in picList" :key="index">
                    <img :src="item.image" v-if="item.image">
                    <div class="empty-box" v-else>
                        <span class="iconfont-diy icontupian"></span>
                    </div>
                </div>
            </div>
            <div class="advertItem02 advertItem03 acea-row" v-if="style===2">
                <div class="item" v-for="(item,index) in picList" :key="index">
                    <img :src="item.image" v-if="item.image">
                    <div class="empty-box" v-else>
                        <span class="iconfont-diy icontupian"></span>
                    </div>
                </div>
            </div>
            <div class="advertItem04 acea-row" v-if="style===3">
                <div class="item">
                    <img :src="picList[0].image" v-if="picList[0].image">
                    <div class="empty-box" v-else>
                        <span class="iconfont-diy icontupian"></span>
                    </div>
                </div>
                <div class="item">
                    <div class="pic">
                        <img :src="picList[1].image" v-if="picList[1].image">
                        <div class="empty-box" v-else>
                            <span class="iconfont-diy icontupian"></span>
                        </div>
                    </div>
                    <div class="pic">
                        <img :src="picList[2].image" v-if="picList[2].image">
                        <div class="empty-box" v-else>
                            <span class="iconfont-diy icontupian"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="advertItem02 advertItem05 acea-row" v-if="style===4">
                <div class="item" v-for="(item,index) in picList" :key="index">
                    <img :src="item.image" v-if="item.image">
                    <div class="empty-box" v-else>
                        <span class="iconfont-diy icontupian"></span>
                    </div>
                </div>
            </div>
            <div class="advertItem06 acea-row" v-if="style===5">
                <div class="item" v-for="(item,index) in picList" :key="index">
                    <img :src="item.image" v-if="item.image">
                    <div class="empty-box" v-else>
                        <span class="iconfont-diy icontupian"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    export default {
        name: 'picture_cube',
        cname: '图片魔方',
        configName: 'c_picture_cube',
        icon: 'iconcuxiaoliebiao1',
        type: 0, // 0 基础组件 1 营销组件 2工具组件
        defaultName: 'pictureCube', // 外面匹配名称
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
                    name: 'pictureCube',
                    timestamp: this.num,
                    tabConfig: {
                        title: '选择样式',
                        tabVal: 0,
                        type: 1,
                        tabList: [
                            {
                                name: '样式一',
                                icon: 'iconyangshi1',
                                count: 1
                            },
                            {
                                name: '样式二',
                                icon: 'iconyangshi2',
                                count: 2
                            },
                            {
                                name: '样式三',
                                icon: 'iconyangshi3',
                                count: 3
                            },
                            {
                                name: '样式四',
                                icon: 'iconyangshi9',
                                count: 3
                            },
                            {
                                name: '样式五',
                                icon: 'iconyangshi8',
                                count: 4
                            },
                            {
                                name: '样式六',
                                icon: 'iconyangshi4',
                                count: 4
                            }
                        ]
                    },
                    picStyle: {
                        tabVal: 0,
                        picList: []
                    },
                    menuConfig: {
                        title: '',
                        maxList: 1,
                        isCube: 1,
                        list: [
                            {
                                img: '',
                                info: [
                                    {
                                        title: '链接',
                                        tips: '请输入链接',
                                        value: '',
                                        max: 100
                                    }
                                ]

                            }
                        ]
                    },
                    bgColor: {
                        title: '背景颜色',
                        default: [
                            {
                                item: '#fff'
                            }
                        ],
                        color: [
                            {
                                item: '#fff'
                            }
                        ]
                    },
                    bgStyle: {
                        title: '背景样式',
                        name: 'bgStyle',
                        type: 0,
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
                        val: 0,
                        min: 0
                    },
                    // 页面间距
                    mbConfig: {
                        title: '页面间距',
                        val: 0,
                        min: 0
                    }
                },
                pageData: {},
                style:0,
                picList:[],
                bgColor: [],
                slider: 0,
                bgStyle:0,
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
                if(data.tabConfig){
                    this.style = data.tabConfig.tabVal
                    this.bgStyle = data.bgStyle.type;
                    this.prConfig = data.prConfig.val;
                    this.slider = data.mbConfig.val;
                    this.bgColor = data.bgColor.color[0].item;
                    if(!data.picStyle.picList.length){
                        this.picList = [
                            {
                                "image": "",
                                "link": ""
                            }
                        ]
                    }else {
                        this.picList = data.picStyle.picList
                    }
                }
            }
        }
    }
</script>
<style scoped lang="stylus">
    .pageOn{
        border-radius 12px!important
        .advertItem01{
            img{
                border-radius 10px
            }
        }
        .advertItem02{
            .item{
                &:nth-child(1){
                    img{
                        border-radius 10px 0 0 10px
                    }
                }
                &:nth-child(2){
                    img{
                        border-radius 0 10px 10px 0
                    }
                }
            }
        }
        .advertItem03{
            .item{
                &:nth-child(1){
                    img{
                        border-radius 10px 0 0 10px
                    }
                }
                &:nth-child(2){
                    img{
                        border-radius 0
                    }
                }
                &:nth-child(3){
                    img{
                        border-radius 0 10px 10px 0
                    }
                }
            }
        }
        .advertItem04{
            .item{
                &:nth-child(1){
                    img{
                        border-radius 10px 0 0 10px
                    }
                }
                &:nth-child(2){
                    .pic{
                        &:nth-child(1){
                            img{
                                border-radius 0 10px 0 0
                            }
                        }
                        &:nth-child(2){
                            img{
                                border-radius 0 0 10px 0
                            }
                        }
                    }
                }
            }
        }
        .advertItem05{
            .item{
                &:nth-child(1){
                    img{
                        border-radius 10px 0 0 10px
                    }
                }
                &:nth-child(2){
                    img{
                        border-radius 0
                    }
                }
                &:nth-child(4){
                    img{
                        border-radius 0 10px 10px 0
                    }
                }
            }
        }
        .advertItem06{
            .item{
                &:nth-child(1){
                    img{
                        border-radius 10px 0 0 0
                    }
                }
                &:nth-child(2){
                    img{
                        border-radius 0 10px 0 0
                    }
                }
                &:nth-child(3){
                    img{
                        border-radius 0 0 0 10px
                    }
                }
                &:nth-child(4){
                    img{
                        border-radius 0 0 10px 0
                    }
                }
            }
        }
    }
    .mobile-page{
        .advert{
            .advertItem01{
                width 100%;
                height 100%;
                .empty-box{
                    width 100%;
                    height 379px;
                    border-radius 0;
                    .icontupian{
                        font-size 50px;
                        color #999;
                    }
                }
                img{
                    width 100%;
                    height 100%
                }
            }
            .advertItem02{
                width 100%
                .item{
                    width 50%;
                    height auto;
                    img{
                        width 100%;
                        height 100%;
                    }
                    .empty-box{
                        width 100%;
                        height 189.5px;
                        border-radius 0;
                    }
                }
            }
            .advertItem03{
                .item{
                    width 33.3333%;
                    .empty-box{
                        width 100%;
                        height 126.4px;
                        border-radius 0;
                    }
                }
            }
            .advertItem04{
                .item{
                    width 50%;
                    height 189.5px;
                    .empty-box{
                        width 100%;
                        height 100%;
                        border-radius 0;
                    }
                    img{
                        width 100%;
                        height 100%;
                    }
                    .pic{
                        width 100%;
                        height 94.75px;
                    }
                }
            }
            .advertItem05{
                .item{
                    width 25%;
                    .empty-box{
                        width 100%;
                        height 94.75px;
                        border-radius 0;
                    }
                }
            }
            .advertItem06{
                .item{
                    width 50%;
                    height 95px;
                    img{
                        width 100%;
                        height 100%;
                    }
                    .empty-box{
                        width 100%;
                        height 100%;
                        border-radius 0;
                    }
                }
            }
        }
    }
</style>