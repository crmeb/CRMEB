// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

/**
 * diy配置
 * */

import toolCom from '@/components/diyComponents/index.js'

export default {
    namespaced: true,
    state: {
        activeName: {},
        defaultConfig: {
            headerSerch: {
                imgUrl:{
                    title: '最多可添加1张图片，图片建议宽度128 * 45px',
                    url: '',
                },
                hotList: {
                    title:'热词最多20个字，鼠标拖拽左侧圆点可调整热词顺序',
                    max:99,
                    list:[
                        {
                            val: '',
                            maxlength:20
                        }
                    ]
                },
            },
            swiperBg: {
                isShow:{
                    val: true
                },
                imgList:{
                    title: '最多可添加10张图片，建议宽度750px',
                    max: 10,
                    list:[
                        {
                            img: 'http://kaifa.crmeb.net/uploads/attach/2020/03/20200319/a32307fd1043c350932a462839288d38.jpg',
                            info: [
                                {
                                    title: '标题',
                                    value: '',
                                    maxlength: 10,
                                    tips: '选填，不超过十个字',
                                },
                                {
                                    title: '链接',
                                    value: '',
                                    maxlength: 999,
                                    tips: '请填写链接',
                                }
                            ]
                        },
                        {
                            img: 'http://kaifa.crmeb.net/uploads/attach/2020/03/20200319/906d46eb6f734eaf1fd820601893af0d.jpg',
                            info: [
                                {
                                    title: '标题',
                                    value: '',
                                    maxlength: 10,
                                    tips: '选填，不超过十个字',
                                },
                                {
                                    title: '链接',
                                    value: '',
                                    maxlength: 999,
                                    tips: '请填写链接',
                                }
                            ]
                        }
                    ]
                }
            },
            news: {
                isShow:{
                    val: true
                },
                logoConfig: {
                    title: '最多可添加1张图片，建议宽度130 * 36px',
                    url: require('@/assets/images/news.png')
                },
                listConfig: {
                    title: '最多可添加10个版块；鼠标拖拽左侧圆点可调整版块顺序',
                    max: 10,
                    list: [
                        {
                            chiild: [
                                {
                                    title: '标题',
                                    val: '标题',
                                    max: 20,
                                    pla: '选填，不超过四个字'
                                },
                                {
                                    title: '链接',
                                    val: '链接',
                                    max: 99,
                                    pla: '选填，不超过四个字'
                                }
                            ]
                        }
                    ]
                },
            },
            menus: {
                isShow:{
                    val: true
                },
                imgList:{
                    title: '最多可添加20个，图片建议宽度96*96px；鼠标拖拽左侧圆点可调整图标顺序',
                    max: 20,
                    list:[
                        {
                            img: 'http://admin.crmeb.net/uploads/attach/2020/05/20200515/723bb4d18893a5aa6871c94d19f3bc4d.png',
                            info: [
                                {
                                    title: '标题',
                                    value: '商品分类',
                                    maxlength: 5,
                                    tips: '请填写标题',
                                },
                                {
                                    title: '链接',
                                    value: '/pages/goods_cate/goods_cate',
                                    maxlength: 999,
                                    tips: '请填写链接',
                                }
                            ]
                        },
                        {
                            img: 'http://admin.crmeb.net/uploads/attach/2020/05/20200515/e908c8f088db07a0f4f6fddc2a7b96f9.png',
                            info: [
                                {
                                    title: '标题',
                                    value: '领优惠券',
                                    maxlength: 5,
                                    tips: '请填写标题',
                                },
                                {
                                    title: '链接',
                                    value: '/pages/users/user_get_coupon/index',
                                    maxlength: 999,
                                    tips: '请填写链接',
                                }
                            ]
                        },
                        {
                            img: 'http://admin.crmeb.net/uploads/attach/2020/05/20200515/1a9a1189bf4a1e9970517d31bcb00bbc.png',
                            info: [
                                {
                                    title: '标题',
                                    value: '行业资讯',
                                    maxlength: 5,
                                    tips: '请填写标题',
                                },
                                {
                                    title: '链接',
                                    value: '/pages/news_list/index',
                                    maxlength: 999,
                                    tips: '请填写链接',
                                }
                            ]
                        },
                        {
                            img: 'http://admin.crmeb.net/uploads/attach/2020/05/20200515/dded4f4779e705d54cf640826d1b5558.png',
                            info: [
                                {
                                    title: '标题',
                                    value: '我的收藏',
                                    maxlength: 5,
                                    tips: '请填写标题',
                                },
                                {
                                    title: '链接',
                                    value: '/pages/users/user_goods_collection/index',
                                    maxlength: 999,
                                    tips: '请填写链接',
                                }
                            ]
                        },
                        {
                            img: 'http://admin.crmeb.net/uploads/attach/2020/05/20200515/f95dd1f3f71fef869e80533df9ccb1a0.png',
                            info: [
                                {
                                    title: '标题',
                                    value: '拼团活动',
                                    maxlength: 5,
                                    tips: '请填写标题',
                                },
                                {
                                    title: '链接',
                                    value: '/pages/activity/goods_combination/index',
                                    maxlength: 999,
                                    tips: '请填写链接',
                                }
                            ]
                        },
                        {
                            img: 'http://admin.crmeb.net/uploads/attach/2020/05/20200515/8bf36e0cd9f9490c1f06abcd7efe8c2d.png',
                            info: [
                                {
                                    title: '标题',
                                    value: '秒杀活动',
                                    maxlength: 5,
                                    tips: '请填写标题',
                                },
                                {
                                    title: '链接',
                                    value: '/pages/activity/goods_seckill/index',
                                    maxlength: 999,
                                    tips: '请填写链接',
                                }
                            ]
                        },
                        {
                            img: 'http://admin.crmeb.net/uploads/attach/2020/05/20200515/5cbdc6eda8c4a2c92c88abffee50d1ff.png',
                            info: [
                                {
                                    title: '标题',
                                    value: '砍价活动',
                                    maxlength: 5,
                                    tips: '请填写标题',
                                },
                                {
                                    title: '链接',
                                    value: '/pages/activity/goods_bargain/index',
                                    maxlength: 999,
                                    tips: '请填写链接',
                                }
                            ]
                        },
                        {
                            img: 'http://admin.crmeb.net/uploads/attach/2020/05/20200515/fdb67663ea188163b0ad863a05f77fbf.png',
                            info: [
                                {
                                    title: '标题',
                                    value: '地址管理',
                                    maxlength: 5,
                                    tips: '请填写标题',
                                },
                                {
                                    title: '链接',
                                    value: '/pages/activity/goods_bargain/index',
                                    maxlength: 999,
                                    tips: '请填写链接',
                                }
                            ]
                        }
                    ]
                },
            },
            coupon: {
                isShow:{
                    val: true
                }
            },
            seckill: {
                isShow:{
                    val: true
                },
                numConfig: {
                    val: 6
                }
            },
            combination: {
                isShow:{
                    val: true
                }
            },
            bargain: {
                isShow:{
                    val: true
                }
            },
            recommend: {
                isShow:{
                    val: true
                },
                imgList:{
                    title: '图片建议尺寸338 * 206px；鼠标拖拽左侧圆点可调整版块顺序',
                    max: 100,
                    list:[
                        {
                            img: 'http://kaifa.crmeb.net/uploads/attach/2020/03/20200319/906d46eb6f734eaf1fd820601893af0d.jpg',
                            info: [
                                {
                                    title: '标题',
                                    value: '商品分类',
                                    maxlength: 5,
                                    tips: '请填写标题',
                                },
                                {
                                    title: '链接',
                                    value: '',
                                    maxlength: 999,
                                    tips: '请填写链接',
                                }
                            ]
                        }
                    ]
                },
            },
            topList: {
                isShow:{
                    val: true
                },
                numConfig: {
                    val: 6,
                    show: 1,
                    title:'商品数量'
                }
            },
            newProduct: {
                isShow:{
                    val: true
                },
                is_new: '1',
                goodsList: {
                    max: 20,
                    list: []
                }
            },
            productSort: {
                isShow:{
                    val: true
                },
                numConfig: {
                    val: 30,
                    show: 1,
                    title:'商品数量'
                }
            },
            tabBar:{
                tabBarList:{
                    title: '图片建议宽度81*81px',
                    list:[
                        {
                            name:'首页',
                            imgList:[require('@/assets/images/foo1-01.png'),require('@/assets/images/foo1-02.png')],
                            link: '/pages/index/index'
                        },
                        {
                            name:'分类',
                            imgList:[require('@/assets/images/foo2-01.png'),require('@/assets/images/foo2-02.png')],
                            link: '/pages/goods_cate/goods_cate'
                        },
                        // {
                        //     name:'周边',
                        //     imgList:[require('@/assets/images/foo3-01.png'),require('@/assets/images/foo3-02.png')],
                        //     pagePath: ''
                        // },
                        {
                            name:'购物车',
                            imgList:[require('@/assets/images/foo4-01.png'),require('@/assets/images/foo4-02.png')],
                            link: '/pages/order_addcart/order_addcart'
                        },
                        {
                            name:'我的',
                            imgList:[require('@/assets/images/foo5-01.png'),require('@/assets/images/foo5-02.png')],
                            link: '/pages/user/index'
                        }
                    ]
                }
            }
        },
        component: {
            headerSerch: {
                list: [
                    {
                        components: toolCom.c_upload_img,
                        configNme: 'imgUrl'
                    },
                    {
                        components: toolCom.c_hot_word,
                        configNme: 'hotList'
                    },
                ]
            },
            swiperBg: {
                list: [
                    {
                        components: toolCom.c_is_show,
                        configNme: 'isShow'
                    },
                    {
                        components: toolCom.c_upload_list,
                        configNme: 'imgList'
                    },
                ]
            },
            news: {
                list: [
                    {
                        components: toolCom.c_upload_img,
                        configNme: 'logoConfig'
                    },
                    {
                        components: toolCom.c_txt_list,
                        configNme: 'listConfig'
                    },
                ]
            },
            menus: {
                list: [
                    {
                        components: toolCom.c_is_show,
                        configNme: 'isShow'
                    },
                    {
                        components: toolCom.c_upload_list,
                        configNme: 'imgList'
                    }
                ]
            },
            coupon: {
                list:[
                    {
                        components: toolCom.c_is_show,
                        configNme: 'isShow'
                    }
                ]
            },
            seckill: {
                list:[
                    {
                        components: toolCom.c_is_show,
                        configNme: 'isShow'
                    },
                    {
                        components: toolCom.c_input_number,
                        configNme: 'numConfig'
                    }
                ]
            },
            combination:{
                list:[
                    {
                        components: toolCom.c_is_show,
                        configNme: 'isShow'
                    }
                ]
            },
            bargain:{
                list:[
                    {
                        components: toolCom.c_is_show,
                        configNme: 'isShow'
                    }
                ]
            },
            recommend: {
                list: [
                    {
                        components: toolCom.c_is_show,
                        configNme: 'isShow'
                    },
                    {
                        components: toolCom.c_upload_list,
                        configNme: 'imgList'
                    }
                ]
            },
            topList: {
                list:[
                    {
                        components: toolCom.c_is_show,
                        configNme: 'isShow'
                    },
                    {
                        components: toolCom.c_input_number,
                        configNme: 'numConfig'
                    }
                ]
            },
            newProduct: {
                list: [
                    {
                        components: toolCom.c_is_show,
                        configNme: 'isShow'
                    },
                    {
                        components: toolCom.c_goods,
                        configNme: 'goodsList'
                    }
                ]
            },
            productSort: {
                list:[
                    {
                        components: toolCom.c_is_show,
                        configNme: 'isShow'
                    },
                    {
                        components: toolCom.c_input_number,
                        configNme: 'numConfig'
                    }
                ]
            },
            tabBar: {
                list:[
                    {
                        components: toolCom.c_tab_bar,
                        configNme: 'tabBarList'
                    },
                ]
            }
        }
    },
    mutations: {
        /**
         * @description 设置选中name
         * @param {Object} state vuex state
         * @param {String} name
         */
        setConfig(state, name) {
            state.activeName = name
        },
        /**
         * @description 更新默认数据
         * @param {Object} state vuex state
         * @param {Object} data
         */
        updataConfig(state,data){
            let value = state.defaultConfig;
            for(let i in data){
                for(let j in value){
                    if(i===j){
                        value[j] = data[i]
                    }
                }
            }
            state.defaultConfig = value
        }
    },
    actions: {}
}