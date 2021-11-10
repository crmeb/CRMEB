<template>
    <div class="search-box" :style="{background: `linear-gradient(90deg,${bgColor[0].item} 0%,${bgColor[1].item} 100%)`,marginTop:`${slider}px`,paddingLeft:`${prConfig}px` }" v-if="bgColor.length>0">
        <img :src="logoUrl" alt="" v-if="logoUrl">
        <div class="box" :class="{on:rollStyle,center:txtPosition}">搜索商品</div>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    export default {
        name: 'search_box',
        cname: '搜索框',
        icon: 'iconsousukuang1',
        configName: 'c_search_box',
        type:0,// 0 基础组件 1 营销组件 2工具组件
        defaultName:'headerSerch', // 外面匹配名称
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
                    name: 'headerSerch',
                    timestamp: this.num,
                    setUp: {
                        tabVal: 0
                    },
                    // tabConfig: {
                    //     tabVal: 0,
                    //     type: 1,
                    //     tabList: [
                    //         {
                    //             name: '样式1',
                    //             icon:'iconsearch_1'
                    //         },
                    //         {
                    //             name: '样式2',
                    //             icon:'iconsearch_2'
                    //         }
                    //     ]
                    // },
                    bgColor: {
                        title: '背景颜色(渐变)',
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
                    boxStyle: {
                        title: '边框样式',
                        name: 'boxStyle',
                        type: 0,
                        list: [
                            {
                                val: '圆角',
                                icon:'iconPic_fillet'
                            },
                            {
                                val: '直角',
                                icon:'iconPic_square'
                            }
                        ]
                    },
                    txtStyle: {
                        title: '文本位置',
                        name: 'txtStyle',
                        type: 0,
                        list: [
                            {
                                val: '居左',
                                icon:'icondoc_left'
                            },
                            {
                                val: '居中',
                                icon:'icondoc_center'
                            }
                        ]
                    },
                    prConfig: {
                        title: '背景边距',
                        val: 10,
                        min: 0
                    },
                    // 页面间距
                    mbConfig: {
                        title: '页面间距',
                        val: 0,
                        min: 0
                    },
                    hotWords: {
                        list: [
                            {
                                val: ''
                            }
                        ]
                    },
                    logoConfig:{
                        type: 1,
                        header: '设置logo',
                        title: '',
                        url: ''
                    }
                },
                // tabVal: '',
                bgColor: [],
                rollStyle: '',
                txtPosition: '',
                slider: '',
                pageData: {},
                prConfig:0,
                logoUrl:''
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
                    this.bgColor = data.bgColor.color;
                    this.rollStyle = data.boxStyle.type;
                    this.txtPosition = data.txtStyle.type;
                    this.slider = data.mbConfig.val;
                    this.logoUrl = data.logoConfig.url;
                    this.prConfig = data.prConfig.val;
                }
            }
        }
    }
</script>

<style scoped lang="stylus">
.search-box
    display flex
    align-items center
    width 100%
    height 48px
    padding 10px 10px 10px 0
    cursor pointer;
    img
        width 76px
        height 30px
        margin-right 10px
    .box
        flex 1
        height 30px
        line-height 30px
        color #999
        font-size 12px
        padding-left 10px
        background #fff
        border-radius:15px;
        &.on
            border-radius 0
        &.center
            text-align center
            padding-left 0
</style>
