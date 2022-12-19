// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

/**
 * diy配置
 * */

import toolCom from '@/components/diyComponents/index.js';

export default {
  namespaced: true,
  state: {
    activeName: {},
    defaultConfig: {
      headerSerch: {
        defaultVal: {
          isShow: {
            val: true,
          },
          imgUrl: {
            title: '最多可添加1张图片，图片建议宽度128 * 45px',
            url: 'http://kaifa.crmeb.net/uploads/attach/2019/10/20191023/db7b7bef9dffdedd27e9a3aa34218cea.png',
          },
          titleInfo: {
            title: '',
            type: 8,
            list: [
              {
                title: '商城简介',
                val: '好物尽享 任你选择',
                max: 20,
                pla: '选填，不超过10个字',
              },
            ],
          },
          hotList: {
            title: '热词最多20个字，鼠标拖拽左侧圆点可调整热词顺序',
            max: 99,
            list: [
              {
                val: '',
                maxlength: 20,
              },
            ],
          },
        },
        default: {
          isShow: {
            val: true,
          },
          imgUrl: {
            title: '最多可添加1张图片，图片建议宽度128 * 45px',
            url: 'http://kaifa.crmeb.net/uploads/attach/2019/10/20191023/db7b7bef9dffdedd27e9a3aa34218cea.png',
          },
          titleInfo: {
            title: '',
            type: 8,
            list: [
              {
                title: '商城简介',
                val: '好物尽享 任你选择',
                max: 20,
                pla: '选填，不超过10个字',
              },
            ],
          },
          hotList: {
            title: '热词最多20个字，鼠标拖拽左侧圆点可调整热词顺序',
            max: 99,
            list: [
              {
                val: '',
                maxlength: 20,
              },
            ],
          },
        },
      },
      swiperBg: {
        defaultVal: {
          isShow: {
            val: true,
          },
          imgList: {
            title: '最多可添加10张图片，建议宽度750px',
            max: 10,
            list: [
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
                  },
                ],
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
                  },
                ],
              },
            ],
          },
        },
        default: {
          isShow: {
            val: true,
          },
          imgList: {
            title: '最多可添加10张图片，建议宽度750px',
            max: 10,
            list: [
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
                  },
                ],
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
                  },
                ],
              },
            ],
          },
        },
      },
      menus: {
        defaultVal: {
          isShow: {
            val: true,
          },
          imgList: {
            title: '最多可添加20个，图片建议宽度96*96px；鼠标拖拽左侧圆点可调整图标顺序',
            max: 20,
            list: [
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
                  },
                ],
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
                  },
                ],
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
                    value: '/pages/extension/news_list/index',
                    maxlength: 999,
                    tips: '请填写链接',
                  },
                ],
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
                  },
                ],
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
                  },
                ],
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
                  },
                ],
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
                  },
                ],
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
                  },
                ],
              },
            ],
          },
        },
        default: {
          isShow: {
            val: true,
          },
          imgList: {
            title: '最多可添加20个，图片建议宽度96*96px；鼠标拖拽左侧圆点可调整图标顺序',
            max: 20,
            list: [
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
                  },
                ],
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
                  },
                ],
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
                    value: '/pages/extension/news_list/index',
                    maxlength: 999,
                    tips: '请填写链接',
                  },
                ],
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
                  },
                ],
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
                  },
                ],
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
                  },
                ],
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
                  },
                ],
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
                  },
                ],
              },
            ],
          },
        },
      },

      tabNav: {
        defaultVal: {
          isShow: {
            val: true,
          },
        },
        default: {
          isShow: {
            val: true,
          },
        },
      },
      news: {
        defaultVal: {
          isShow: {
            val: true,
          },
          imgUrl: {
            title: '最多可添加10个模板，图片建议宽度124 * 28px',
            url: 'http://kaifa.crmeb.net/uploads/attach/2019/10/20191023/db7b7bef9dffdedd27e9a3aa34218cea.png',
          },
          newList: {
            max: 10,
            list: [
              {
                chiild: [
                  {
                    title: '标题',
                    val: 'CRMEB_PRO 1.1正式公测啦',
                    max: 20,
                    pla: '选填，不超过四个字',
                  },
                  {
                    title: '链接',
                    val: '链接',
                    max: 99,
                    pla: '选填',
                  },
                ],
              },
            ],
          },
        },
        default: {
          isShow: {
            val: true,
          },
          imgUrl: {
            title: '最多可添加10个模板，图片建议宽度124 * 28px',
            url: 'http://kaifa.crmeb.net/uploads/attach/2019/10/20191023/db7b7bef9dffdedd27e9a3aa34218cea.png',
          },
          newList: {
            max: 10,
            list: [
              {
                chiild: [
                  {
                    title: '标题',
                    val: 'CRMEB_PRO 1.1正式公测啦',
                    max: 20,
                    pla: '选填，不超过四个字',
                  },
                  {
                    title: '链接',
                    val: '链接',
                    max: 99,
                    pla: '选填',
                  },
                ],
              },
            ],
          },
        },
      },
      activity: {
        defaultVal: {
          isShow: {
            val: true,
          },
          imgList: {
            isDelete: true,
            title: '最多可添加3组模块，第一张336*298px,后两张416*124px',
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
                  },
                ],
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
                  },
                ],
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
                  },
                ],
              },
            ],
          },
          max: 3,
        },
        default: {
          isShow: {
            val: true,
          },
          imgList: {
            isDelete: true,
            title: '最多可添加3组模块，第一张336*298px,后两张416*124px',
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
                  },
                ],
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
                  },
                ],
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
                  },
                ],
              },
            ],
          },
          max: 3,
        },
      },
      alive: {
        defaultVal: {
          isShow: {
            val: true,
          },
          titleInfo: {
            title: '',
            list: [
              {
                title: '标题',
                val: '直播间',
                max: 20,
                pla: '选填，不超过六个字',
              },
              {
                title: '介绍',
                val: '精彩直播',
                max: 8,
                pla: '选填，不超过8个字',
              },
              {
                title: '链接',
                val: '/pages/columnGoods/live_list/index',
                max: 999,
                pla: '选填',
              },
            ],
          },
          numConfig: {
            title: '显示数量',
            val: 3,
          },
        },
        default: {
          isShow: {
            val: true,
          },
          titleInfo: {
            title: '',
            list: [
              {
                title: '标题',
                val: '直播间',
                max: 20,
                pla: '选填，不超过六个字',
              },
              {
                title: '介绍',
                val: '精彩直播',
                max: 8,
                pla: '选填，不超过8个字',
              },
              {
                title: '链接',
                val: '/pages/columnGoods/live_list/index',
                max: 999,
                pla: '选填',
              },
            ],
          },
          numConfig: {
            title: '显示数量',
            val: 3,
          },
        },
      },
      scrollBox: {
        defaultVal: {
          isShow: {
            val: true,
          },
          titleInfo: {
            title: '',
            list: [
              {
                title: '标题',
                val: '快速选择',
                max: 4,
                pla: '选填，不超过4个字',
              },
              {
                title: '介绍',
                val: '诚意推荐品质商品',
                max: 8,
                pla: '选填，不超过8个字',
              },
              {
                title: '链接',
                val: '/pages/goods_cate/goods_cate',
                max: 999,
                pla: '选填',
              },
            ],
          },
          // tabConfig: {
          //     tabVal: 0,
          //     type: 1,
          //     tabList: [
          //         {
          //             name: '自动选择',
          //             icon: 'iconzidongxuanze'
          //         },
          //         {
          //             name: '手动选择',
          //             icon: 'iconshoudongxuanze'
          //         }
          //     ]
          // },
          // selectConfig: {
          //     title: '商品分类',
          //     type: 1,//type=1时只是传二级分类
          //     activeValue: '',
          //     list: [
          //         {
          //             activeValue: '',
          //             title: ''
          //         },
          //         {
          //             activeValue: '',
          //             title: ''
          //         }
          //     ]
          // },
          // numConfig: {
          //     title:'显示数量',
          //     val: 6
          // },
          // goodsList: {
          //     max: 20,
          //     list: []
          // }
        },
        default: {
          isShow: {
            val: true,
          },
          titleInfo: {
            title: '',
            list: [
              {
                title: '标题',
                val: '快速选择',
                max: 4,
                pla: '选填，不超过4个字',
              },
              {
                title: '介绍',
                val: '诚意推荐品质商品',
                max: 8,
                pla: '选填，不超过8个字',
              },
              {
                title: '链接',
                val: '/pages/goods_cate/goods_cate',
                max: 999,
                pla: '选填',
              },
            ],
          },
          // tabConfig: {
          //     tabVal: 0,
          //     type: 1,
          //     tabList: [
          //         {
          //             name: '自动选择',
          //             icon: 'iconzidongxuanze'
          //         },
          //         {
          //             name: '手动选择',
          //             icon: 'iconshoudongxuanze'
          //         }
          //     ]
          // },
          // selectConfig: {
          //     title: '商品分类',
          //     type: 1,//type=1时只是传二级分类
          //     activeValue: '',
          //     list: [
          //         {
          //             activeValue: '',
          //             title: ''
          //         },
          //         {
          //             activeValue: '',
          //             title: ''
          //         }
          //     ]
          // },
          // numConfig: {
          //     title:'显示数量',
          //     val: 6
          // },
          // goodsList: {
          //     max: 20,
          //     list: []
          // }
        },
      },
      adsRecommend: {
        defaultVal: {
          isShow: {
            val: true,
          },
          imgList: {
            title: '图片建议尺寸338 * 206px；鼠标拖拽左侧圆点可调整版块顺序',
            max: 10,
            list: [
              {
                img: 'http://kaifa.crmeb.net/uploads/attach/2020/03/20200319/906d46eb6f734eaf1fd820601893af0d.jpg',
                info: [
                  {
                    title: '链接',
                    value: '',
                    maxlength: 999,
                    tips: '请填写链接',
                  },
                ],
              },
            ],
          },
        },
        default: {
          isShow: {
            val: true,
          },
          imgList: {
            title: '图片建议尺寸338 * 206px；鼠标拖拽左侧圆点可调整版块顺序',
            max: 10,
            list: [
              {
                img: 'http://kaifa.crmeb.net/uploads/attach/2020/03/20200319/906d46eb6f734eaf1fd820601893af0d.jpg',
                info: [
                  {
                    title: '链接',
                    value: '',
                    maxlength: 999,
                    tips: '请填写链接',
                  },
                ],
              },
            ],
          },
        },
      },
      coupon: {
        defaultVal: {
          isShow: {
            val: true,
          },
          numConfig: {
            val: 10,
          },
        },
        default: {
          isShow: {
            val: true,
          },
          numConfig: {
            val: 10,
          },
        },
      },
      seckill: {
        defaultVal: {
          isShow: {
            val: true,
          },
          tabConfig: {
            tabVal: 0,
            type: 1,
            tabList: [
              {
                name: '自动选择',
                icon: 'iconzidongxuanze',
              },
              {
                name: '手动选择',
                icon: 'iconshoudongxuanze',
              },
            ],
          },
          titleInfo: {
            title: '',
            type: 2,
            list: [
              {
                title: '商品类型',
                val: '限时秒杀',
                max: 20,
                pla: '选填，不超过四个字',
              },
            ],
          },
          selectConfig: {
            title: '商品分类',
            activeValue: '',
            list: [
              {
                activeValue: '',
                title: '',
              },
              {
                activeValue: '',
                title: '',
              },
            ],
          },
          numConfig: {
            val: 6,
          },
          goodsSort: {
            title: '商品排序',
            name: 'goodsSort',
            type: 0,
            list: [
              {
                val: '系统排序',
                icon: 'iconComm_whole',
              },
              {
                val: '销量最高',
                icon: 'iconComm_number',
              },
              {
                val: '最新上架',
                icon: 'iconzuixin',
              },
            ],
          },
          goodsList: {
            max: 20,
            list: [],
          },
        },
        default: {
          isShow: {
            val: true,
          },
          tabConfig: {
            tabVal: 0,
            type: 1,
            tabList: [
              {
                name: '自动选择',
                icon: 'iconzidongxuanze',
              },
              {
                name: '手动选择',
                icon: 'iconshoudongxuanze',
              },
            ],
          },
          titleInfo: {
            title: '',
            type: 2,
            list: [
              {
                title: '商品类型',
                val: '限时秒杀',
                max: 20,
                pla: '选填，不超过四个字',
              },
            ],
          },
          selectConfig: {
            title: '商品分类',
            activeValue: '',
            list: [
              {
                activeValue: '',
                title: '',
              },
              {
                activeValue: '',
                title: '',
              },
            ],
          },
          numConfig: {
            val: 6,
          },
          goodsSort: {
            title: '商品排序',
            name: 'goodsSort',
            type: 0,
            list: [
              {
                val: '系统排序',
                icon: 'iconComm_whole',
              },
              {
                val: '销量最高',
                icon: 'iconComm_number',
              },
              {
                val: '最新上架',
                icon: 'iconzuixin',
              },
            ],
          },
          goodsList: {
            max: 20,
            list: [],
          },
        },
      },
      combination: {
        defaultVal: {
          isShow: {
            val: true,
          },
          tabConfig: {
            tabVal: 0,
            type: 1,
            tabList: [
              {
                name: '自动选择',
                icon: 'iconzidongxuanze',
              },
              {
                name: '手动选择',
                icon: 'iconshoudongxuanze',
              },
            ],
          },
          titleInfo: {
            title: '',
            type: 3,
            list: [
              {
                title: '商品类型',
                val: '拼团列表',
                max: 20,
                pla: '选填，不超过四个字',
              },
            ],
          },
          selectConfig: {
            title: '商品分类',
            activeValue: '',
            list: [
              {
                activeValue: '',
                title: '',
              },
              {
                activeValue: '',
                title: '',
              },
            ],
          },
          numConfig: {
            val: 6,
          },
          goodsSort: {
            title: '商品排序',
            name: 'goodsSort',
            type: 0,
            list: [
              {
                val: '系统排序',
                icon: 'iconComm_whole',
              },
              {
                val: '销量最高',
                icon: 'iconComm_number',
              },
              {
                val: '最新上架',
                icon: 'iconzuixin',
              },
            ],
          },
          goodsList: {
            max: 20,
            list: [],
          },
        },
        default: {
          isShow: {
            val: true,
          },
          tabConfig: {
            tabVal: 0,
            type: 1,
            tabList: [
              {
                name: '自动选择',
                icon: 'iconzidongxuanze',
              },
              {
                name: '手动选择',
                icon: 'iconshoudongxuanze',
              },
            ],
          },
          titleInfo: {
            title: '',
            type: 3,
            list: [
              {
                title: '商品类型',
                val: '拼团列表',
                max: 20,
                pla: '选填，不超过四个字',
              },
            ],
          },
          selectConfig: {
            title: '商品分类',
            activeValue: '',
            list: [
              {
                activeValue: '',
                title: '',
              },
              {
                activeValue: '',
                title: '',
              },
            ],
          },
          numConfig: {
            val: 6,
          },
          goodsSort: {
            title: '商品排序',
            name: 'goodsSort',
            type: 0,
            list: [
              {
                val: '系统排序',
                icon: 'iconComm_whole',
              },
              {
                val: '销量最高',
                icon: 'iconComm_number',
              },
              {
                val: '最新上架',
                icon: 'iconzuixin',
              },
            ],
          },
          goodsList: {
            max: 20,
            list: [],
          },
        },
      },
      bargain: {
        defaultVal: {
          isShow: {
            val: true,
          },
          tabConfig: {
            tabVal: 0,
            type: 1,
            tabList: [
              {
                name: '自动选择',
                icon: 'iconzidongxuanze',
              },
              {
                name: '手动选择',
                icon: 'iconshoudongxuanze',
              },
            ],
          },
          titleInfo: {
            title: '',
            type: 8,
            list: [
              {
                title: '商品类型',
                val: '砍价列表',
                max: 20,
                pla: '选填，不超过四个字',
              },
            ],
          },
          selectConfig: {
            title: '商品分类',
            activeValue: '',
            list: [
              {
                activeValue: '',
                title: '',
              },
              {
                activeValue: '',
                title: '',
              },
            ],
          },
          numConfig: {
            val: 6,
          },
          goodsSort: {
            title: '商品排序',
            name: 'goodsSort',
            type: 0,
            list: [
              {
                val: '系统排序',
                icon: 'iconComm_whole',
              },
              {
                val: '销量最高',
                icon: 'iconComm_number',
              },
              {
                val: '最新上架',
                icon: 'iconzuixin',
              },
            ],
          },
          goodsList: {
            max: 20,
            list: [],
          },
        },
        default: {
          isShow: {
            val: true,
          },
          tabConfig: {
            tabVal: 0,
            type: 1,
            tabList: [
              {
                name: '自动选择',
                icon: 'iconzidongxuanze',
              },
              {
                name: '手动选择',
                icon: 'iconshoudongxuanze',
              },
            ],
          },
          titleInfo: {
            title: '',
            type: 8,
            list: [
              {
                title: '商品类型',
                val: '砍价列表',
                max: 20,
                pla: '选填，不超过四个字',
              },
            ],
          },
          selectConfig: {
            title: '商品分类',
            activeValue: '',
            list: [
              {
                activeValue: '',
                title: '',
              },
              {
                activeValue: '',
                title: '',
              },
            ],
          },
          numConfig: {
            val: 6,
          },
          goodsSort: {
            title: '商品排序',
            name: 'goodsSort',
            type: 0,
            list: [
              {
                val: '系统排序',
                icon: 'iconComm_whole',
              },
              {
                val: '销量最高',
                icon: 'iconComm_number',
              },
              {
                val: '最新上架',
                icon: 'iconzuixin',
              },
            ],
          },
          goodsList: {
            max: 20,
            list: [],
          },
        },
      },
      goodList: {
        defaultVal: {
          isShow: {
            val: true,
          },
          titleInfo: {
            title: '',
            list: [
              {
                title: '标题',
                val: '快速选择',
                max: 4,
                pla: '选填，不超过4个字',
              },
              {
                title: '介绍',
                val: '诚意推荐品质商品',
                max: 8,
                pla: '选填，不超过8个字',
              },
              {
                title: '链接',
                val: '/pages/columnGoods/HotNewGoods/index',
                max: 999,
                pla: '选填',
              },
            ],
          },
          tabConfig: {
            tabVal: 0,
            type: 1,
            tabList: [
              {
                name: '自动选择',
                icon: 'iconzidongxuanze',
              },
              {
                name: '手动选择',
                icon: 'iconshoudongxuanze',
              },
            ],
          },
          selectSortConfig: {
            title: '商品类型',
            activeValue: '',
            list: [
              {
                activeValue: '0',
                title: '商品列表',
              },
              {
                activeValue: '4',
                title: '热门榜单',
              },
              {
                activeValue: '5',
                title: '首发新品',
              },
              {
                activeValue: '6',
                title: '促销单品',
              },
              {
                activeValue: '7',
                title: '精品推荐',
              },
            ],
          },
          selectConfig: {
            title: '商品分类',
            activeValue: '',
            list: [
              {
                activeValue: '',
                title: '',
              },
              {
                activeValue: '',
                title: '',
              },
            ],
          },
          numConfig: {
            val: 6,
          },
          goodsSort: {
            title: '商品排序',
            name: 'goodsSort',
            type: 0,
            list: [
              {
                val: '系统排序',
                icon: 'iconComm_whole',
              },
              {
                val: '销量最高',
                icon: 'iconComm_number',
              },
              {
                val: '最新上架',
                icon: 'iconzuixin',
              },
            ],
          },
          goodsList: {
            max: 20,
            list: [],
          },
        },
        default: {
          isShow: {
            val: true,
          },
          titleInfo: {
            title: '',
            list: [
              {
                title: '标题',
                val: '快速选择',
                max: 4,
                pla: '选填，不超过4个字',
              },
              {
                title: '介绍',
                val: '诚意推荐品质商品',
                max: 8,
                pla: '选填，不超过8个字',
              },
              {
                title: '链接',
                val: '/pages/columnGoods/HotNewGoods/index?type=1',
                max: 999,
                pla: '选填',
              },
            ],
          },
          tabConfig: {
            tabVal: 0,
            type: 1,
            tabList: [
              {
                name: '自动选择',
                icon: 'iconzidongxuanze',
              },
              {
                name: '手动选择',
                icon: 'iconshoudongxuanze',
              },
            ],
          },
          selectSortConfig: {
            title: '商品类型',
            activeValue: '',
            list: [
              {
                activeValue: '0',
                title: '商品列表',
              },
              {
                activeValue: '4',
                title: '热门榜单',
              },
              {
                activeValue: '5',
                title: '首发新品',
              },
              {
                activeValue: '6',
                title: '促销单品',
              },
              {
                activeValue: '7',
                title: '精品推荐',
              },
            ],
          },
          selectConfig: {
            title: '商品分类',
            activeValue: '',
            list: [
              {
                activeValue: '',
                title: '',
              },
              {
                activeValue: '',
                title: '',
              },
            ],
          },
          numConfig: {
            val: 6,
          },
          goodsSort: {
            title: '商品排序',
            name: 'goodsSort',
            type: 0,
            list: [
              {
                val: '系统排序',
                icon: 'iconComm_whole',
              },
              {
                val: '销量最高',
                icon: 'iconComm_number',
              },
              {
                val: '最新上架',
                icon: 'iconzuixin',
              },
            ],
          },
          goodsList: {
            max: 20,
            list: [],
          },
        },
      },
      picTxt: {
        defaultVal: {
          isShow: {
            val: true,
          },
          richText: {
            val: '',
          },
        },
        default: {
          isShow: {
            val: true,
          },
          richText: {
            val: '',
          },
        },
      },
      titles: {
        defaultVal: {
          isShow: {
            val: true,
          },
          titleInfo: {
            title: '',
            list: [
              {
                title: '标题',
                val: '精品推荐',
                max: 20,
                pla: '选填，不超过四个字',
              },
              {
                title: '标题',
                val: '精品推荐',
                max: 20,
                pla: '选填，不超过四个字',
              },
              {
                title: '链接',
                val: '/pages/columnGoods/HotNewGoods/index?type=1',
                max: 999,
                pla: '选填',
              },
            ],
          },
        },
        default: {
          isShow: {
            val: true,
          },
          titleInfo: {
            title: '',
            list: [
              {
                title: '标题',
                val: '精品推荐',
                max: 20,
                pla: '选填，不超过四个字',
              },
              {
                title: '标题',
                val: '精品推荐',
                max: 20,
                pla: '选填，不超过四个字',
              },
              {
                title: '链接',
                val: '/pages/columnGoods/HotNewGoods/index?type=1',
                max: 999,
                pla: '选填',
              },
            ],
          },
        },
      },
      customerService: {
        defaultVal: {
          isShow: {
            val: true,
          },
          imgUrl: {
            title: '最多可添加1张图片，图片建议宽度128 * 45px',
            url: '',
          },
        },
        default: {
          isShow: {
            val: true,
          },
          imgUrl: {
            title: '最多可添加1张图片，图片建议宽度128 * 45px',
            url: '',
          },
        },
      },
      tabBar: {
        defaultVal: {
          isShow: {
            val: true,
          },
          tabBarList: {
            title: '图片建议宽度81*81px',
            list: [
              {
                name: '首页',
                imgList: [
                  'https://qiniu.crmeb.net/attach/2021/04/9ebdf202104251644215768.png',
                  'https://qiniu.crmeb.net/attach/2021/04/44bc420210425164421586.png',
                ],
                link: '/pages/index/index',
              },
              {
                name: '分类',
                imgList: [
                  'https://qiniu.crmeb.net/attach/2021/04/b62c8202104251644218412.png',
                  'https://qiniu.crmeb.net/attach/2021/04/9509c202104251644214836.png',
                ],
                link: '/pages/goods_cate/goods_cate',
              },
              // {
              //     name:'周边',
              //     imgList:[require('@/assets/images/foo3-01.png'),require('@/assets/images/foo3-02.png')],
              //     pagePath: ''
              // },
              {
                name: '购物车',
                imgList: [
                  'https://qiniu.crmeb.net/attach/2021/04/2e682202104251644216849.png',
                  'https://qiniu.crmeb.net/attach/2021/04/6b3cb202104251644218211.png',
                ],
                link: '/pages/order_addcart/order_addcart',
              },
              {
                name: '我的',
                imgList: [
                  'https://qiniu.crmeb.net/attach/2021/04/3329c20210425164421428.png',
                  'https://qiniu.crmeb.net/attach/2021/04/031ce202104251644215432.png',
                ],
                link: '/pages/user/index',
              },
            ],
          },
        },
        default: {
          isShow: {
            val: true,
          },
          tabBarList: {
            title: '图片建议宽度81*81px',
            list: [
              {
                name: '首页',
                imgList: [
                  'https://qiniu.crmeb.net/attach/2021/04/9ebdf202104251644215768.png',
                  'https://qiniu.crmeb.net/attach/2021/04/44bc420210425164421586.png',
                ],
                link: '/pages/index/index',
              },
              {
                name: '分类',
                imgList: [
                  'https://qiniu.crmeb.net/attach/2021/04/b62c8202104251644218412.png',
                  'https://qiniu.crmeb.net/attach/2021/04/9509c202104251644214836.png',
                ],
                link: '/pages/goods_cate/goods_cate',
              },
              // {
              //     name:'周边',
              //     imgList:[require('@/assets/images/foo3-01.png'),require('@/assets/images/foo3-02.png')],
              //     pagePath: ''
              // },
              {
                name: '购物车',
                imgList: [
                  'https://qiniu.crmeb.net/attach/2021/04/2e682202104251644216849.png',
                  'https://qiniu.crmeb.net/attach/2021/04/6b3cb202104251644218211.png',
                ],
                link: '/pages/order_addcart/order_addcart',
              },
              {
                name: '我的',
                imgList: [
                  'https://qiniu.crmeb.net/attach/2021/04/3329c20210425164421428.png',
                  'https://qiniu.crmeb.net/attach/2021/04/031ce202104251644215432.png',
                ],
                link: '/pages/user/index',
              },
            ],
          },
        },
      },
    },
    component: {
      headerSerch: {
        list: [
          {
            components: toolCom.c_is_show,
            configNme: 'isShow',
          },
          {
            components: toolCom.c_input_list,
            configNme: 'titleInfo',
          },
          {
            components: toolCom.c_upload_img,
            configNme: 'imgUrl',
          },
          {
            components: toolCom.c_hot_word,
            configNme: 'hotList',
          },
        ],
      },
      swiperBg: {
        list: [
          {
            components: toolCom.c_is_show,
            configNme: 'isShow',
          },
          {
            components: toolCom.c_upload_list,
            configNme: 'imgList',
          },
        ],
      },
      menus: {
        list: [
          {
            components: toolCom.c_is_show,
            configNme: 'isShow',
          },
          {
            components: toolCom.c_upload_list,
            configNme: 'imgList',
          },
        ],
      },
      news: {
        list: [
          {
            components: toolCom.c_is_show,
            configNme: 'isShow',
          },
          {
            components: toolCom.c_upload_img,
            configNme: 'imgUrl',
          },
          {
            components: toolCom.c_txt_list,
            configNme: 'newList',
          },
        ],
      },
      tabNav: {
        list: [
          {
            components: toolCom.c_is_show,
            configNme: 'isShow',
          },
        ],
      },
      activity: {
        list: [
          {
            components: toolCom.c_is_show,
            configNme: 'isShow',
          },
          {
            components: toolCom.c_upload_list,
            configNme: 'imgList',
          },
        ],
      },
      alive: {
        list: [
          {
            components: toolCom.c_is_show,
            configNme: 'isShow',
          },
          {
            components: toolCom.c_input_list,
            configNme: 'titleInfo',
          },
          {
            components: toolCom.c_input_number,
            configNme: 'numConfig',
          },
        ],
      },
      scrollBox: {
        list: [
          {
            components: toolCom.c_is_show,
            configNme: 'isShow',
          },
          {
            components: toolCom.c_input_list,
            configNme: 'titleInfo',
          },
          // {
          //     components: toolCom.c_tab,
          //     configNme: 'tabConfig'
          // },
          // {
          //     components: toolCom.c_select,
          //     configNme: 'selectConfig'
          // },
          // {
          //     components: toolCom.c_input_number,
          //     configNme: 'numConfig'
          // }
        ],
      },
      adsRecommend: {
        list: [
          {
            components: toolCom.c_is_show,
            configNme: 'isShow',
          },
          {
            components: toolCom.c_upload_list,
            configNme: 'imgList',
          },
        ],
      },
      coupon: {
        list: [
          {
            components: toolCom.c_is_show,
            configNme: 'isShow',
          },
          {
            components: toolCom.c_input_number,
            configNme: 'numConfig',
          },
        ],
      },
      seckill: {
        list: [
          {
            components: toolCom.c_is_show,
            configNme: 'isShow',
          },
          {
            components: toolCom.c_tab,
            configNme: 'tabConfig',
          },
          {
            components: toolCom.c_input_list,
            configNme: 'titleInfo',
          },
          {
            components: toolCom.c_select,
            configNme: 'selectConfig',
          },
          {
            components: toolCom.c_input_number,
            configNme: 'numConfig',
          },
          {
            components: toolCom.c_txt_tab,
            configNme: 'goodsSort',
          },
        ],
      },
      combination: {
        list: [
          {
            components: toolCom.c_is_show,
            configNme: 'isShow',
          },
          {
            components: toolCom.c_tab,
            configNme: 'tabConfig',
          },
          {
            components: toolCom.c_input_list,
            configNme: 'titleInfo',
          },
          {
            components: toolCom.c_select,
            configNme: 'selectConfig',
          },
          {
            components: toolCom.c_input_number,
            configNme: 'numConfig',
          },
          {
            components: toolCom.c_txt_tab,
            configNme: 'goodsSort',
          },
        ],
      },
      bargain: {
        list: [
          {
            components: toolCom.c_is_show,
            configNme: 'isShow',
          },
          {
            components: toolCom.c_tab,
            configNme: 'tabConfig',
          },
          {
            components: toolCom.c_input_list,
            configNme: 'titleInfo',
          },
          {
            components: toolCom.c_select,
            configNme: 'selectConfig',
          },
          {
            components: toolCom.c_input_number,
            configNme: 'numConfig',
          },
          {
            components: toolCom.c_txt_tab,
            configNme: 'goodsSort',
          },
        ],
      },
      goodList: {
        list: [
          {
            components: toolCom.c_is_show,
            configNme: 'isShow',
          },
          {
            components: toolCom.c_input_list,
            configNme: 'titleInfo',
          },
          {
            components: toolCom.c_tab,
            configNme: 'tabConfig',
          },
          {
            components: toolCom.c_select,
            configNme: 'selectSortConfig',
          },
          {
            components: toolCom.c_select,
            configNme: 'selectConfig',
          },
          {
            components: toolCom.c_input_number,
            configNme: 'numConfig',
          },
          {
            components: toolCom.c_txt_tab,
            configNme: 'goodsSort',
          },
        ],
      },
      picTxt: {
        list: [
          {
            components: toolCom.c_is_show,
            configNme: 'isShow',
          },
          {
            components: toolCom.c_page_ueditor,
            configNme: 'richText',
          },
        ],
      },
      titles: {
        list: [
          {
            components: toolCom.c_is_show,
            configNme: 'isShow',
          },
          {
            components: toolCom.c_input_list,
            configNme: 'titleInfo',
          },
        ],
      },
      customerService: {
        list: [
          {
            components: toolCom.c_is_show,
            configNme: 'isShow',
          },
          {
            components: toolCom.c_upload_img,
            configNme: 'imgUrl',
          },
        ],
      },
      tabBar: {
        list: [
          {
            components: toolCom.c_is_show,
            configNme: 'isShow',
          },
          {
            components: toolCom.c_tab_bar,
            configNme: 'tabBarList',
          },
        ],
      },
    },
  },
  mutations: {
    /**
     * @description 设置选中name
     * @param {Object} state vuex state
     * @param {String} name
     */
    setConfig(state, name) {
      state.activeName = name;
    },

    upDataName(state, data) {
      state.defaultConfig[state.activeName] = data;
    },

    upDataGoodList(state, data) {
      let list = [];
      if (data.type) {
        list = [
          {
            components: toolCom.c_is_show,
            configNme: 'isShow',
          },
          {
            components: toolCom.c_input_list,
            configNme: 'titleInfo',
          },
          {
            components: toolCom.c_tab,
            configNme: 'tabConfig',
          },
          {
            components: toolCom.c_goods,
            configNme: 'goodsList',
          },
        ];
      } else {
        list = [
          {
            components: toolCom.c_is_show,
            configNme: 'isShow',
          },
          {
            components: toolCom.c_input_list,
            configNme: 'titleInfo',
          },
          {
            components: toolCom.c_tab,
            configNme: 'tabConfig',
          },
          {
            components: toolCom.c_select,
            configNme: 'selectConfig',
          },
          {
            components: toolCom.c_input_number,
            configNme: 'numConfig',
          },
        ];
        let sort = {
          components: toolCom.c_txt_tab,
          configNme: 'goodsSort',
        };
        let type = {
          components: toolCom.c_select,
          configNme: 'selectSortConfig',
        };
        let fixed = {
          components: toolCom.c_input_list,
          configNme: 'titleInfo',
        };
        if (data.name === 'seckill' || data.name === 'combination' || data.name === 'bargain') {
          list.splice(2, 0, fixed);
          list.push(sort);
        }
        if (data.name === 'goodList') {
          list.splice(3, 0, type);
          list.push(sort);
        }
      }
      let recommend = {
        components: toolCom.c_upload_list,
        configNme: 'imgList',
      };
      if (data.name === 'recommend') {
        list.splice(1, 0, recommend);
      }
      switch (data.name) {
        case 'scrollBox':
          state.component.scrollBox.list = list;
          break;
        case 'popular':
          state.component.popular.list = list;
          break;
        case 'recommend':
          state.component.recommend.list = list;
          break;
        case 'seckill':
          state.component.seckill.list = list;
          break;
        case 'combination':
          state.component.combination.list = list;
          break;
        case 'bargain':
          state.component.bargain.list = list;
          break;
        case 'newGoods':
          state.component.newGoods.list = list;
          break;
        case 'promotion':
          state.component.promotion.list = list;
          break;
        case 'goodList':
          state.component.goodList.list = list;
          break;
        default:
      }
    },
    /**
     * @description 更新默认数据
     * @param {Object} state vuex state
     * @param {Object} data
     */
    updataConfig(state, data) {
      let value = state.defaultConfig;
      for (let i in data) {
        for (let j in value) {
          if (i === j) {
            value[j] = data[i];
          }
        }
      }
      state.defaultConfig = value;
    },
  },
  actions: {},
};
