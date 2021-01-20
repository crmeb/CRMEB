/**
 * diy配置
 * */

import toolCom from '@/components/diyComponents/index.js'

export default {
    namespaced: true,
    state: {
        periphery:1,
        activeName: {},
        defaultConfig: {
            b_swiperBg: {
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
            c_monograph: {
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
            d_picTxt: {
                isShow:{
                    val: true
                },
                richText: {
                    val:''
                }
            },
            z_tabBar:{
                tabBarList:{
                    title: '图片建议宽度81*81px',
                    list:[
                        {
                            name:'首页',
                            selectedIconPath: 'http://v4.crmeb.net/uploads/attach/2020/09/20200902/32bb4bcaa09576bbb9552d7106712a1c.png',
                            iconPath: 'http://v4.crmeb.net/uploads/attach/2020/09/20200902/7259bc2bcf185545169ef483f1f526d5.png',
                            pagePath: '/pages/index/index'
                        },
                        {
                            name:'分类',
                            selectedIconPath: 'http://v4.crmeb.net/uploads/attach/2020/09/20200902/6d8c681a09cf41e95e8fd06d08b8dfb0.png',
                            iconPath: 'http://v4.crmeb.net/uploads/attach/2020/09/20200902/0326cadc13f4973b1841ecc0b0677677.png',
                            pagePath: '/pages/goods_cate/goods_cate'
                        },
                        {
                            name:'购物车',
                            selectedIconPath: 'http://v4.crmeb.net/uploads/attach/2020/09/20200902/48f26b02197f5d567a70a775039a22ea.png',
                            iconPath: 'http://v4.crmeb.net/uploads/attach/2020/09/20200902/cfe458ccce402196b964bb2896fd60f4.png',
                            pagePath: '/pages/order_addcart/order_addcart'
                        },
                        {
                            name:'我的',
                            selectedIconPath: 'http://v4.crmeb.net/uploads/attach/2020/09/20200902/fb87741eafab96fe940e874d4e58dbda.png',
                            iconPath: 'http://v4.crmeb.net/uploads/attach/2020/09/20200902/3aac48eac153b68534d7f3650d7c5553.png',
                            pagePath: '/pages/user/index'
                        }
                    ]
                }
            }
        },
        component: {
            b_swiperBg: {
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
            c_monograph: {
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
            d_picTxt: {
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
            z_tabBar: {
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
            state.defaultConfig = data
            let value = Object.assign({}, state.defaultConfig);
            state.defaultConfig = value
        }
    },
    actions: {}
}
