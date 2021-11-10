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
            monograph: {
                isShow:{
                    val: true
                },
                imgList:{
                    title: '建议宽度750px',
                    max: '',
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
            picTxt: {
                isShow:{
                    val: true
                },
                richText: {
                    val:''
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
            monograph: {
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
            picTxt: {
                list: [
                    {
                        components: toolCom.c_is_show,
                        configNme: 'isShow'
                    },
                    {
                        components: toolCom.c_page_ueditor,
                        configNme: 'richText'
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
