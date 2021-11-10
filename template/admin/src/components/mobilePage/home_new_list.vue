<template>
    <div class="mobile-page" :style="{marginTop:mTOP+'px',padding:'0 '+prConfig+'px'}">
        <div class="list-wrapper" :class="{pageOn:bgStyle===1}" :style="{background:bgColor}">
            <div class="item" :class="{on:listStyle == 0,pageOn:conStyle===1}" v-for="(item,index) in list" :key="index" :style="{marginBottom:itemEdge+'px'}">
                <div class="empty-box on" v-if="list[0].type==='noList'"><span class="iconfont-diy icontupian"></span></div>
                <div class="pictrue" v-else>
                    <img :src="item.image_input[0]">
                </div>
                <div class="info">
                    <div class="title line2">{{item.title}}</div>
                    <div class="time"> {{ item.add_time | formatDate }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { categoryList } from '@/api/diy'
    import { mapState } from 'vuex';
    import { formatDate } from '@/utils/validate'
    export default {
        name: 'home_new_list',
        filters: {
            formatDate (time) {
                if (time !== 0) {
                    let date = new Date(time * 1000);
                    return formatDate(date, 'yyyy-MM-dd hh:mm');
                }
            }
        },
        cname: '新闻列表',
        icon: 'iconwenzhangliebiao1',
        configName: 'c_new_list',
        type: 0, // 0 基础组件 1 营销组件 2工具组件
        defaultName: 'articleList', // 外面匹配名称
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
                list: [],
                // 默认初始化数据禁止修改
                defaultConfig: {
                    name: 'articleList',
                    timestamp: this.num,
                    setUp: {
                        tabVal: 0
                    },
                    numConfig: {
                        val: 3,
                        title:'文章数量'
                    },
                    selectConfig: {
                        title: '文章分类',
                        activeValue: '',
                        list: [
                            {
                                activeValue: '',
                                title: ''
                            },
                            {
                                activeValue: '',
                                title: ''
                            }
                        ]
                    },
                    selectList: {
                        title: '文章列表',
                        list :[]
                    },
                    listStyle: {
                        cname: 'listStyle',
                        title: '文本位置',
                        type: 0,
                        list: [
                            {
                                val: '居左',
                                icon: 'icondoc_left'
                            },
                            {
                                val: '居右',
                                icon: 'icondoc_right'
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
                    conStyle: {
                        title: '内容样式',
                        name: 'conStyle',
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
                    itemConfig: {
                        title: '文章间距',
                        val: 0,
                        min: 0
                    },
                    mbConfig: {
                        title: '页面间距',
                        val: 0,
                        min: 0
                    }
                },
                mTOP: 0,
                bgColor: [],
                itemEdge: 0,
                listStyle: 0,
                itemStyle:0,
                bgStyle:0,
                conStyle:0,
                prConfig:0
            }
        },
        created () {

        },
        mounted () {
            this.$nextTick(() => {
                this.pageData = this.$store.state.admin.mobildConfig.defaultArray[this.num]
                this.setConfig(this.pageData)
                //this.categoryList()
            })
        },
        methods: {
            categoryList () {
                categoryList().then(res => {
                    this.pageData.selectConfig.list = res.data
                    this.pageData.selectConfig.list.map(item => {
                        item.id.toString();
                        // return item;
                    });
                    this.$store.commit('admin/mobildConfig/UPDATEARR', { num: this.num, val: this.pageData })
                })
            },
            setConfig (data) {
                if(!data) return
                if(data.mbConfig){
                    this.bgColor = data.bgColor.color[0].item
                    this.mTOP = data.mbConfig.val;
                    this.itemEdge = data.itemConfig.val;
                    this.listStyle = data.listStyle.type;
                    this.bgStyle = data.bgStyle.type;
                    this.prConfig = data.prConfig.val;
                    this.conStyle = data.conStyle.type;
                    let selectList = data.selectList.list || [];
                    if(selectList.length){
                        this.list = selectList;
                    }else {
                        this.list = [
                            {
                                title: '文章标题文章标题文章标题文章 标题文章标题',
                                add_time: '1621474811',
                                type: 'noList'
                            }
                        ];
                    }
                }
            }
        }
    }
</script>

<style scoped lang="stylus">
.pageOn{
    border-radius 10px!important
}
.list-wrapper
    padding 10px 0
    .item
        display flex
        align-items center
        justify-content space-between
        padding 10px
        background-color #fff
        margin 0 10px
        &:nth-last-child(1)
            margin-bottom 0!important
        &.on
            flex-flow row-reverse
        .img-box
            width:125px;
            height:78px;
            background #E8E8E8
        .info
            display flex
            flex-direction column
            justify-content space-between
            width 209px
            height:78px;
            .title
                color #282828
                font-size 15px
            .time
                color #999999
                font-size 12px
        .empty-box
            width:125px;
            height:78px;
        .pictrue
            width 125px;
            height 78px;
            img
               width 100%;
               height 100%;
</style>
