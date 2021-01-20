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
            a_headerSerch: {
                isShow:{
                    val: true
                },
                imgUrl:{
                    title: '最多可添加1张图片，图片建议宽度128 * 45px',
                    url: 'http://kaifa.crmeb.net/uploads/attach/2019/10/20191023/db7b7bef9dffdedd27e9a3aa34218cea.png'
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
                },
            },
            c_menus: {
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
            d_news: {
                isShow:{
                    val: true
                },
                imgUrl:{
                    title: '最多可添加10个模板，图片建议宽度124 * 28px',
                    url: 'http://kaifa.crmeb.net/uploads/attach/2019/10/20191023/db7b7bef9dffdedd27e9a3aa34218cea.png'
                },
                newList:{
                    max: 10,
                    list:[{
                        chiild: [
                            {
                                title: '标题',
                                val: 'CRMEB_PRO 1.1正式公测啦',
                                max: 20,
                                pla: '选填，不超过四个字'
                            },
                            {
                                title: '链接',
                                val: '链接',
                                max: 99,
                                pla: '选填'
                            }
                        ]
                    }]
                },
            },
            e_activity: {
                isShow:{
                    val: true
                },
                imgList: {
                    isDelete: true,
                    title: '最多可添加3组模块，第一张260*260px,后两张416*124px',
                    max: 3,
                    list: [
                        {
                            img: 'http://datong.crmeb.net/public/uploads/attach/2019/03/28/5c9ccf7e9f4d0.jpg',
                            info: [
                                {
                                    title: '标题',
                                    value: '一起来拼团',
                                    maxlength: 20,
                                    tips: '标题',
                                },
                                {
                                    title: '描述',
                                    value: '优惠多多',
                                    maxlength: 20,
                                    tips: '描述',
                                },
                                {
                                    title: '链接',
                                    value: '/pages/activity/goods_combination/index',
                                    maxlength: 999,
                                    tips: '链接',
                                }
                            ]
                        },
                        {
                            img: 'http://datong.crmeb.net/public/uploads/attach/2019/03/28/5c9ccf7e97660.jpg',
                            info: [
                                {
                                    title: '标题',
                                    value: '秒杀专区',
                                    maxlength: 20,
                                    tips: '标题',
                                },
                                {
                                    title: '描述',
                                    value: '新能源汽车优惠多多',
                                    maxlength: 20,
                                    tips: '描述',
                                },
                                {
                                    title: '链接',
                                    value: '/pages/activity/goods_seckill/index',
                                    maxlength: 999,
                                    tips: '链接',
                                }
                            ]
                        },
                        {
                            img: 'http://datong.crmeb.net/public/uploads/attach/2019/03/28/5c9ccfc86a6c1.jpg',
                            info: [
                                {
                                    title: '标题',
                                    value: '砍价活动',
                                    maxlength: 20,
                                    tips: '标题',
                                },
                                {
                                    title: '描述',
                                    value: '呼朋唤友来砍价~~',
                                    maxlength: 20,
                                    tips: '描述',
                                },
                                {
                                    title: '链接',
                                    value: '/pages/activity/goods_bargain/index',
                                    maxlength: 999,
                                    tips: '链接',
                                }
                            ]
                        }
                    ],
                },
                max: 3
            },
            f_alive:{
                isShow:{
                    val: true
                },
                titleInfo:{
                    title:'',
                    list:[
                        {
                            title:'标题',
                            val:'直播间',
                            max: 20,
                            pla: '选填，不超过六个字'
                        }
                    ]
                },
                numConfig:{
                    title:'显示数量',
                    val:3
                }
            },
            f_scroll_box: {
                isShow:{
                    val: true
                },
                titleInfo:{
                    title:'',
                    list:[
                        {
                            title:'标题',
                            val:'快速选择',
                            max: 20,
                            pla: '选填，不超过四个字'
                        },
                        {
                            title:'简介',
                            val:'诚意推荐品质商品',
                            max: 20,
                            pla: '选填'
                        }
                    ]
                },
                tabConfig: {
                    tabVal: 0,
                    type: 1,
                    tabList: [
                        {
                            name: '自动选择',
                            icon: 'iconzidongxuanze'
                        },
                        {
                            name: '手动选择',
                            icon: 'iconshoudongxuanze'
                        }
                    ]
                },
                selectConfig: {
                    title: '商品分类',
                    type: 1,//type=1时只是传二级分类
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
                numConfig: {
                    title:'显示数量',
                    val: 6
                },
                goodsList: {
                    max: 20,
                    list: []
                }
            },
            g_recommend: {
                isShow:{
                    val: true
                },
                titleInfo:{
                    title:'',
                    list:[
                        {
                            title:'标题',
                            val:'精品推荐',
                            max: 20,
                            pla: '选填，不超过四个字'
                        },
                        {
                            title:'简介',
                            val:'诚意推荐品质商品',
                            max: 20,
                            pla: '选填'
                        }
                    ]
                },
                numConfig:{
                    title:'显示数量',
                    val:10
                },
                imgList: {
                    title: '最多可添加10个轮播图，图片建议宽度750 * 280px',
                    max: 10,
                    list: [
                        {
                            info:[
                                {
                                    title: '链接',
                                    value: '',
                                    maxlength: 999,
                                    tips: '请填写链接',
                                }
                            ],
                            img: 'http://kaifa.crmeb.net/uploads/attach/2020/03/20200319/505554c6d46688d5b4541861e5056335.jpg',
                        },
                        {
                            info:[
                                {
                                    title: '链接',
                                    value: '',
                                    maxlength: 999,
                                    tips: '请填写链接',
                                }
                            ],
                            img: 'http://kaifa.crmeb.net/uploads/attach/2020/03/20200319/aeee0e4c7432bb37b34857fa3a7b3916.jpg',
                        },
                    ],
                },
            },
            h_popular:{
                isShow:{
                    val: true
                },
                titleInfo:{
                    title:'',
                    list:[
                        {
                            title:'标题',
                            val:'热门榜单',
                            max: 20,
                            pla: '选填，不超过四个字'
                        },
                        {
                            title:'简介',
                            val:'根据销量、搜索、好评等综合得出',
                            max: 20,
                            pla: '选填'
                        }
                    ]
                },
            },
            i_m_banner:{
                isShow:{
                    val: true
                },
                imgList: {
                    title: '最多可添加10个轮播图，图片建议宽度750 * 190px',
                    max: 10,
                    list: [
                        {
                            info:[
                                {
                                    title: '链接',
                                    value: '',
                                    maxlength: 999,
                                    tips: '请填写链接',
                                }
                            ],
                            img: 'http://kaifa.crmeb.net/uploads/attach/2020/03/20200319/a32307fd1043c350932a462839288d38.jpg',
                        },
                        {
                            info:[
                                {
                                    title: '链接',
                                    value: '',
                                    maxlength: 999,
                                    tips: '请填写链接',
                                }
                            ],
                            img: 'http://kaifa.crmeb.net/uploads/attach/2020/03/20200319/906d46eb6f734eaf1fd820601893af0d.jpg',
                        },
                    ],
                },
            },
            i_new_goods:{
                isShow:{
                    val: true
                },
                titleInfo:{
                    title:'',
                    list:[
                        {
                            title:'标题',
                            val:'首发新品',
                            max: 20,
                            pla: '选填，不超过四个字'
                        },
                        {
                            title:'简介',
                            val:'多个优质商品最新上架',
                            max: 20,
                            pla: '选填'
                        }
                    ]
                },
                numConfig:{
                    title:'显示数量',
                    val:10
                }
            },
            j_promotion:{
                isShow:{
                    val: true
                },
                titleInfo:{
                    title:'',
                    list:[
                        {
                            title:'标题',
                            val:'促销单品',
                            max: 20,
                            pla: '选填，不超过四个字'
                        },
                        {
                            title:'简介',
                            val:'库存商品优惠促销活动',
                            max: 20,
                            pla: '选填'
                        }
                    ]
                },
                numConfig:{
                    title:'显示数量',
                    val:10
                }
            },
            z_tabBar:{
                isShow:{
                    val: true
                },
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
            a_headerSerch: {
                list: [
                    {
                        components: toolCom.c_is_show,
                        configNme: 'isShow'
                    },
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
            c_menus: {
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
            d_news: {
                list: [
                    {
                        components: toolCom.c_is_show,
                        configNme: 'isShow'
                    },
                    {
                        components: toolCom.c_upload_img,
                        configNme: 'imgUrl'
                    },
                    {
                        components: toolCom.c_txt_list,
                        configNme: 'newList'
                    },
                ]
            },
            e_activity: {
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
            f_alive: {
                list: [
                    {
                        components: toolCom.c_is_show,
                        configNme: 'isShow'
                    },
                    {
                        components: toolCom.c_input_list,
                        configNme: 'titleInfo'
                    },
                    {
                        components: toolCom.c_input_number,
                        configNme: 'numConfig'
                    },
                ]
            },
            f_scroll_box: {
                list:[
                    {
                        components: toolCom.c_is_show,
                        configNme: 'isShow'
                    },
                    {
                        components: toolCom.c_input_list,
                        configNme: 'titleInfo'
                    },
                    {
                        components: toolCom.c_tab,
                        configNme: 'tabConfig'
                    },
                    {
                        components: toolCom.c_select,
                        configNme: 'selectConfig'
                    },
                    {
                        components: toolCom.c_input_number,
                        configNme: 'numConfig'
                    }
                ]
            },
            g_recommend: {
                list: [
                    {
                        components: toolCom.c_is_show,
                        configNme: 'isShow'
                    },
                    {
                        components: toolCom.c_input_list,
                        configNme: 'titleInfo'
                    },
                    {
                        components: toolCom.c_input_number,
                        configNme: 'numConfig'
                    },
                    {
                        components: toolCom.c_upload_list,
                        configNme: 'imgList'
                    },
                ]
            },
            h_popular:{
                list: [
                    {
                        components: toolCom.c_is_show,
                        configNme: 'isShow'
                    },
                    {
                        components: toolCom.c_input_list,
                        configNme: 'titleInfo'
                    },
                ]
            },
            i_new_goods:{
                list: [
                    {
                        components: toolCom.c_is_show,
                        configNme: 'isShow'
                    },
                    {
                        components: toolCom.c_input_list,
                        configNme: 'titleInfo'
                    },
                    {
                        components: toolCom.c_input_number,
                        configNme: 'numConfig'
                    }
                ]
            },
            j_promotion:{
                list: [
                    {
                        components: toolCom.c_is_show,
                        configNme: 'isShow'
                    },
                    {
                        components: toolCom.c_input_list,
                        configNme: 'titleInfo'
                    },
                    {
                        components: toolCom.c_input_number,
                        configNme: 'numConfig'
                    }
                ]
            },
            i_m_banner:{
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
            z_tabBar: {
                list:[
                    {
                        components: toolCom.c_is_show,
                        configNme: 'isShow'
                    },
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
        upDataGoodList(state, type){
            let list = [];
            if(type){
                list = [
                    {
                        components: toolCom.c_is_show,
                        configNme: 'isShow'
                    },
                    {
                        components: toolCom.c_input_list,
                        configNme: 'titleInfo'
                    },
                    {
                        components: toolCom.c_tab,
                        configNme: 'tabConfig'
                    },
                    {
                        components: toolCom.c_goods,
                        configNme: 'goodsList'
                    }
                ];
            }else {
                list = [
                    {
                        components: toolCom.c_is_show,
                        configNme: 'isShow'
                    },
                    {
                        components: toolCom.c_input_list,
                        configNme: 'titleInfo'
                    },
                    {
                        components: toolCom.c_tab,
                        configNme: 'tabConfig'
                    },
                    {
                        components: toolCom.c_select,
                        configNme: 'selectConfig'
                    },
                    {
                        components: toolCom.c_input_number,
                        configNme: 'numConfig'
                    }
                ];
            }
            state.component.f_scroll_box.list = list;
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
