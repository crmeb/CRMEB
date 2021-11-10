<template>
    <div :style="{marginLeft:prConfig+'px',marginRight:prConfig+'px',marginTop:slider+'px',background:bgColor}" :class="bgStyle?'pageOn':''">
        <!--多行展示-->
        <div class="mobile-page" v-if="isOne">
            <div class="list_menu">
                <div class="item" :class="number===1?'four':number===2?'five':''" v-for="(item,index) in vuexMenu" :key="index">
                    <div class="img-box" :class="menuStyle?'on':''">
                        <img :src="item.img" alt="" v-if="item.img">
                        <div class="empty-box on" v-else> <span class="iconfont-diy icontupian"></span> </div>
                    </div>
                    <p :style="{color:txtColor}">{{item.info[0].value}}</p>
                </div>
            </div>
        </div>
        <!--单行展示-->
        <div class="mobile-page" v-else>
            <div class="home_menu">
                <div class="menu-item" v-for="(item,index) in vuexMenu" :key="index">
                    <div class="img-box" :class="menuStyle?'on':''">
                        <img :src="item.img" alt="" v-if="item.img">
                        <div class="empty-box on" v-else> <span class="iconfont-diy icontupian"></span> </div>
                    </div>
                    <p :style="{color:txtColor}">{{item.info[0].value}}</p>
                </div>
            </div>
        </div>
        <div class="dot" :class="{ 'line-dot': pointerStyle === 0,'': pointerStyle === 1}" v-if="isOne && pointerStyle<2">
            <div class="dot-item" :style="{background: `${pointerColor}`}"></div>
            <div class="dot-item"></div>
            <div class="dot-item"></div>
        </div>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    export default {
        name: 'home_menu',
        cname: '导航组',
        icon: 'icondaohangzu1',
        configName: 'c_home_menu',
        type:0,// 0 基础组件 1 营销组件 2工具组件
        defaultName:'menus', // 外面匹配名称
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
                    name:'menus',
                    timestamp: this.num,
                    setUp: {
                        tabVal: 0
                    },
                    tabConfig: {
                        title: '展示样式',
                        tabVal: 0,
                        type: 1,
                        tabList: [
                            {
                                name: '单行展示',
                                icon: 'icondanhang'
                            },
                            {
                                name: '多行展示',
                                icon: 'iconduohang'
                            }
                        ]
                    },
                    rowsNum: {
                        title: '显示行数',
                        name: 'rowsNum',
                        type: 0,
                        list: [
                            {
                                val: '2行',
                                icon: 'icon2hang'
                            },
                            {
                                val: '3行',
                                icon: 'icon3hang'
                            },
                            {
                                val: '4行',
                                icon: 'icon4hang'
                            }
                        ]
                    },
                    menuStyle: {
                        title: '图标样式',
                        name: 'menuStyle',
                        type: 0,
                        list: [
                            {
                                val: '方形',
                                icon: 'iconPic_square'
                            },
                            {
                                val: '圆形',
                                icon: 'icondayuanjiao'
                            }
                        ]
                    },
                    number: {
                        title: '显示个数',
                        name: 'number',
                        type: 0,
                        list: [
                            {
                                val: '3个',
                                icon: 'icon3ge'
                            },
                            {
                                val: '4个',
                                icon: 'icon4ge1'
                            },
                            {
                                val: '5个',
                                icon: 'icon5ge1'
                            }
                        ]
                    },
                    pointerStyle: {
                        title: '指示器样式',
                        name: 'pointerStyle',
                        type: 0,
                        list: [
                            {
                                val: '长条',
                                icon: 'iconSquarepoint'
                            },
                            {
                                val: '圆形',
                                icon: 'iconDot'
                            },
                            {
                                val: '无指示器',
                                icon: 'iconjinyong'
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
                    menuConfig: {
                        title: '最多可添加1张图片，建议宽度90 * 90px',
                        maxList: 100,
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
                                        title: '链接',
                                        value: '',
                                        tips: '请输入链接',
                                        max: 100
                                    }
                                ]
                            }
                        ]
                    },
                    pointerColor: {
                        title: '指示器颜色',
                        name: 'pointerColor',
                        default: [{
                            item: '#f44'
                        }],
                        color: [
                            {
                                item: '#f44'
                            }

                        ]
                    },
                    bgColor: {
                        title: '背景颜色',
                        name: 'bgColor',
                        default: [{
                            item: '#fff'
                        }],
                        color: [
                            {
                                item: '#fff'
                            }

                        ]
                    },
                    titleColor: {
                        title: '文字颜色',
                        name: 'themeColor',
                        default: [{
                            item: '#333333'
                        }],
                        color: [
                            {
                                item: '#333333'
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
                vuexMenu: '',
                txtColor: '',
                boxStyle: '',
                slider: '',
                bgColor: '',
                menuStyle: 0,
                isOne: 0,
                number: 0,
                rowsNum: 0,
                pointerStyle: 0,
                pointerColor:'',
                pageData: {},
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
            // 对象转数组
            objToArr(data) {
                let obj = Object.keys(data);
                let m = obj.map((key) => data[key]);
                return m;
            },
            setConfig (data) {
                if(!data) return;
                if(data.mbConfig){
                    this.txtColor = data.titleColor.color[0].item;
                    this.menuStyle = data.menuStyle.type;
                    this.pointerStyle = data.pointerStyle.type;
                    this.pointerColor = data.pointerColor.color[0].item;
                    // this.boxStyle = data.rowStyle.type;
                    this.slider = data.mbConfig.val;
                    this.bgStyle = data.bgStyle.type;
                    this.prConfig = data.prConfig.val;
                    this.bgColor = data.bgColor.color[0].item;
                    this.isOne = data.tabConfig.tabVal;
                    let rowsNum = data.rowsNum.type;
                    let number = data.number.type;
                    let list = this.objToArr(data.menuConfig.list);
                    this.number = number;
                    this.rowsNum = rowsNum;
                    let vuexMenu = [];
                    if(rowsNum===0){
                        if(number===0){
                            vuexMenu = list.splice(0, 6);
                        }else if(number===1){
                            vuexMenu = list.splice(0, 8);
                        }else {
                            vuexMenu = list.splice(0, 10);
                        }
                    }else if(rowsNum===1){
                        if(number===0){
                            vuexMenu = list.splice(0, 9);
                        }else if(number===1){
                            vuexMenu = list.splice(0, 12);
                        }else {
                            vuexMenu = list.splice(0, 15);
                        }
                    }else {
                        if(number===0){
                            vuexMenu = list.splice(0, 12);
                        }else if(number===1){
                            vuexMenu = list.splice(0, 16);
                        }else {
                            vuexMenu = list.splice(0, 20);
                        }
                    }
                    this.vuexMenu = vuexMenu;
                }
            }
        }
    }
</script>

<style scoped lang="stylus">
.pageOn{
    border-radius 10px!important
}
.list_menu
    padding 0 12px 12px
    display flex
    flex-wrap wrap
    .item
        margin-top 12px
        font-size 11px
        color #282828
        text-align center
        width 33.3333%
        &.four
            width 25%
        &.five
            width 20%
        .img-box
             width 50px
             height 50px
             margin 0 auto 5px auto
             &.on
                 border-radius 50%;
                 img,.empty-box
                   border-radius 50%;

             img
               width 100%
               height 100%
    .icontupian
        font-size 16px
.home_menu
    padding 0 12px 12px
    display flex
    overflow hidden
    .menu-item
        margin-top 12px
        font-size 11px
        color #282828
        text-align center
        margin-right 30px

        .img-box
            width 50px
            height 50px
            &.on
                border-radius 50%;
                img,.empty-box
                    border-radius 50%;
        .box,img
            width 100%
            height 100%
        .box
            background #D8D8D8
        p
            margin-top 5px
        &:nth-child(5n)
            margin-right 0
    &.on
        .menu-item
            margin-right 51px
            &:nth-child(5n)
                margin-right 51px
            &:nth-child(4n)
                margin-right 0
    .icontupian
        font-size 16px
.dot {
    display: flex;
    align-items: center;
    justify-content center;
    padding-bottom 10px;

    &.number{
        bottom: 15px;
    }

    .num{
        width 25px;
        height 18px;
        line-height 18px;
        background-color #000;
        color #fff;
        opacity 0.3;
        border-radius 8px;
        font-size 12px;
        text-align center;
    }

    .dot-item {
        width: 5px;
        height: 5px;
        background: #AAAAAA;
        border-radius: 50%;
        margin: 0 3px;
    }

    &.line-dot {

        .dot-item {
            width: 8px;
            height: 2px;
            background: #AAAAAA;
            margin: 0 3px;
        }
    }
}
</style>
