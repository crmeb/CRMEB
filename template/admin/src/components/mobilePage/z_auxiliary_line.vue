<template>
    <div class="mobile-page">
        <div class="box"
            :style="{
                borderBottomWidth:cSlider+'px',
                borderBottomColor:bgColor,
                borderBottomStyle:style,
                marginLeft:edge+'px',
                marginRight:edge+'px',
                marginTop:udEdge+'px',
            }"
        ></div>
<!--        {{pageData.styleList[0].list[pageData.styleList[0].type].style}}-->
    </div>
</template>

<script>
    import { mapState, mapMutations } from 'vuex'
    export default {
        name: 'z_auxiliary_line',
        cname: '辅助线',
        configName: 'c_auxiliary_line',
        icon: 'iconfuzhuxian1',
        type:2,// 0 基础组件 1 营销组件 2工具组件
        defaultName:'guide', // 外面匹配名称
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
                    name: 'guide',
                    timestamp: this.num,
                    lineColor: {
                        title: '线条颜色',
                        default: [{
                            item: '#f5f5f5'
                        }],
                        color: [
                            {
                                item: '#f5f5f5'
                            }
                        ]
                    },
                    lineStyle: {
                        title: '线条样式',
                        type: 0,
                        list: [
                            {
                                val: '虚线',
                                style: 'dashed',
                                icon:''
                            },
                            {
                                val: '实线',
                                style: 'solid'
                            },
                            {
                                val: '点状线',
                                style: 'dotted'
                            }
                        ]
                    },
                    heightConfig:{
                        title: '组件高度',
                        val: 1,
                        min: 1
                    },
                    lrEdge:{
                        title: '左右边距',
                        val: 0,
                        min: 0
                    },
                    mbConfig: {
                        title: '页面间距',
                        val: 0,
                        min: 0
                    },
                },
                cSlider: '',
                bgColor: '',
                confObj: {},
                pageData: {},
                edge: '',
                udEdge: '',
                styleType: '',
                style: ''
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
                    let styleType = data.lineStyle.type
                    this.cSlider = data.heightConfig.val;
                    this.bgColor = data.lineColor.color[0].item
                    this.edge = data.lrEdge.val;
                    this.udEdge = data.mbConfig.val
                    this.style = data.lineStyle.list[styleType].style
                }
            }
        }
    }
</script>

<style scoped lang="stylus">
    .mobile-page{
        padding 7px 0;
    }
    /*.box*/
    /*    height 20px*/
    /*    background #F5F5F5*/
</style>
