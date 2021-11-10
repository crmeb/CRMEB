<template>
    <div class="mobile-page">
        <div class="box" :style="{background:bgColor,marginLeft:edge+'px',marginRight:edge+'px',marginTop:udEdge+'px'}" v-html="richText"></div>
    </div>
</template>

<script>
    import { mapState, mapMutations } from 'vuex'
    export default {
        name: 'z_ueditor',
        cname: '富文本',
        configName: 'c_ueditor_box',
        icon: 'iconfuwenben1',
        type:2,// 0 基础组件 1 营销组件 2工具组件
        defaultName:'richText', // 外面匹配名称
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
                    name: 'richText',
                    timestamp: this.num,
                    setUp: {
                        tabVal: 0
                    },
                    bgColor: {
                        title: '背景色',
                        name: 'bgColor',
                        default: [{
                            item: '#f5f5f5'
                        }],
                        color: [
                            {
                                item: '#f5f5f5'
                            }
                        ]
                    },
                    lrConfig: {
                        title: '左右边距',
                        val: 0,
                        min: 0
                    },
                    udConfig: {
                        title: '上下边距',
                        val: 0,
                        min: 0
                    },
                    richText: {
                        val:''
                    }
                },
                cSlider: '',
                bgColor: '',
                confObj: {},
                pageData: {},
                edge: '',
                udEdge: '',
                richText: ''
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
                if(data.lrConfig){
                    this.bgColor = data.bgColor.color[0].item
                    this.edge = data.lrConfig.val;
                    this.udEdge = data.udConfig.val
                    this.richText = data.richText.val
                }
            }
        }
    }
</script>

<style scoped lang="stylus">
    .mobile-page /deep/video{
        width 100%!important
    }
    .box
        min-height 100px
        padding 10px
        background #F5F5F5
        img
            max-width 100%
            height auto
</style>
