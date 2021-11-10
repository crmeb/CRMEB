<template>

        <div class="mobile-page">
            <div class="flex-box" :style="{background:bgColor,marginTop:mTOP+'px'}">
                <div class="left">
                    <div class="img-box">
                        <img :src="imgUrl" alt="" v-if="imgUrl">
                        <div class="empty-box on" v-else><span class="iconfont-diy icontupian"></span></div>
                    </div>
                    <div class="name">{{txt}}</div>
                </div>
                <div class="btn" :style="{borderColor:themeColor,color:themeColor}">关注</div>
            </div>
        </div>
</template>

    <script>
    import { mapState, mapMutations } from 'vuex'
    export default {
        name: 'z_wechat_attention',
        cname: '关注公众号',
        configName: 'c_wechat_attention',
        icon: 'iconguanzhugongzhonghao1',
        type: 2, // 0 基础组件 1 营销组件 2工具组件
        defaultName: 'follow', // 外面匹配名称
        props: {
            index: {
                type: null,
                default: -1
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
                    name: 'follow',
                    timestamp: this.num,
                    setUp: {
                        tabVal: 0
                    },
                    titleConfig: {
                        title: '名称',
                        value: '标题',
                        place: '请输入标题',
                        max: 30
                    },
                    imgConfig: {
                        title: '最多可添加1张图片，建议宽度92 * 92px',
                        url: ''
                    },
                    codeConfig: {
                        title: '添加二维码，建议宽度92 * 92p',
                        url: ''
                    },
                    themeColor: {
                        title: '主题颜色',
                        default: [
                            {
                                item: '#F96E29'
                            }
                        ],
                        color: [
                            {
                                item: '#F96E29'
                            }
                        ]
                    },
                    bgColor: {
                        title: '背景颜色',
                        default: [
                            {
                                item: '#f5f5f5'
                            }
                        ],
                        color: [
                            {
                                item: '#f5f5f5'
                            }
                        ]
                    },
                    mbConfig: {
                        title: '页面间距',
                        val: 0,
                        min: 0
                    }
                },
                cSlider: '',
                bgColor: '',
                confObj: {},
                pageData: {},
                edge: '',
                udEdge: '',
                themeColor: '',
                mTOP: 0,
                imgUrl: '',
                txt:''
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
                    this.bgColor = data.bgColor.color[0].item
                    this.themeColor = data.themeColor.color[0].item
                    this.mTOP = data.mbConfig.val;
                    this.imgUrl = data.imgConfig.url
                    this.txt = data.titleConfig.value
                }
            }
        }
    }
    </script>

<style scoped lang="stylus">
.flex-box
    display flex
    align-items center
    justify-content space-between
    padding 0 10px
    height 70px
    background #DDDDDD
    .left
        display flex
        align-items center
        .img-box
            width 46px
            height 46px
            overflow hidden
            border-radius 50%
            img
                width 100%
                height 100%
        .name
            width 230px
            margin-left 10px
            font-size 18px
            color #000
    .btn
        width:60px;
        height:26px;
        border:1px solid rgba(2,160,232,1);
        opacity:1;
        border-radius:3px;
        color #02A0E8
        font-size 14px
        text-align center
        line-height 26px
    .iconfont-diy
        font-size 20px
</style>
