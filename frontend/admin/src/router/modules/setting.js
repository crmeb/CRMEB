// +---------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +---------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +---------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +---------------------------------------------------------------------

import LayoutMain from '@/layout';
import setting from '@/setting';
let routePre = setting.routePre;

const meta = {
  auth: true,
};

const pre = 'setting_';

export default {
  path: routePre + '/setting',
  name: 'setting',
  header: 'setting',
  redirect: {
    name: `${pre}setSystem`,
  },
  component: LayoutMain,
  children: [
    {
      path: 'system_role/index',
      name: `${pre}systemRole`,
      meta: {
        auth: ['setting-system-role'],
        title: '身份管理',
      },
      component: () => import('@/pages/setting/systemRole/index'),
    },
    {
      path: 'system_admin/index',
      name: `${pre}systemAdmin`,
      meta: {
        auth: ['setting-system-list'],
        title: '管理员列表',
      },
      component: () => import('@/pages/setting/systemAdmin/index'),
    },
    {
      path: 'system_menus/index',
      name: `${pre}systemMenus`,
      meta: {
        auth: ['setting-system-menus'],
        title: '权限规则',
      },
      component: () => import('@/pages/setting/systemMenus/index'),
    },
    {
      path: 'system_config',
      name: `${pre}setSystem`,
      meta: {
        auth: ['setting-system-config'],
        title: '系统设置',
      },
      component: () => import('@/pages/setting/setSystem/index'),
    },
    {
      path: 'system_config/:tab_id?',
      name: `${pre}setApp`,
      meta: {
        title: '系统设置',
      },
      component: () => import('@/pages/setting/setSystem/index'),
    },
    {
      path: 'elec_invoice',
      name: `${pre}elecInvoice`,
      meta: {
        auth: ['setting-elec-invoice'],
        title: '电子发票',
      },
      component: () => import('@/pages/setting/elecInvoice/index'),
    },
    {
      path: 'printer',
      name: `${pre}printer`,
      meta: {
        auth: ['driver:printer:channel'],
        title: '打印机管理',
      },
      component: () => import('@/pages/driver/printer/index'),
    },
    {
      path: 'printer/content',
      name: `${pre}printerContent`,
      meta: {
        auth: ['driver:printer:channel'],
        title: '小票配置',
        activeMenu: routePre + '/setting/printer',
      },
      component: () => import('@/pages/driver/printer/content'),
    },
    {
      path: 'express',
      name: `${pre}express`,
      meta: {
        auth: ['driver:express:setting'],
        title: '电子面单设置',
      },
      component: () => import('@/pages/setting/express/index'),
    },
    {
      path: 'membership_level/index',
      name: `${pre}membershipLevel`,
      meta: {
        ...meta,
        title: '分销等级',
      },
      component: () => import('@/pages/setting/membershipLevel/index'),
    },
    {
      path: 'notification/member',
      name: `${pre}notificationMember`,
      meta: {
        auth: ['setting-notification'],
        title: '会员通知',
      },
      component: () => import('@/pages/setting/notification/member'),
    },
    {
      path: 'notification/platform',
      name: `${pre}notificationPlatform`,
      meta: {
        auth: ['setting-notification'],
        title: '平台通知',
      },
      component: () => import('@/pages/setting/notification/platform'),
    },
    {
      path: 'notification/channel',
      name: `${pre}notificationChannel`,
      meta: {
        auth: ['setting-notification'],
        title: '渠道管理',
      },
      component: () => import('@/pages/setting/notification/channel/index'),
    },
    {
      path: 'notification/log',
      name: `${pre}notificationLog`,
      meta: {
        auth: ['setting-notification'],
        title: '发送记录',
      },
      component: () => import('@/pages/setting/notification/log/index'),
    },
    {
      path: 'notification/notificationEdit',
      name: `${pre}notificationEdit`,
      meta: {
        auth: ['setting-notification'],
        title: '消息编辑',
        activeMenu: routePre + '/setting/notification/member',
      },
      component: () => import('@/pages/setting/notification/notificationEdit'),
    },
    {
      path: 'yihaotong/index',
      name: `${pre}config`,
      meta: {
        auth: ['setting-yihaotong'],
        title: '一号通账户',
      },
      component: () => import('@/pages/yihaotong/index'),
    },
    {
      path: 'ai_model/index',
      name: `${pre}aiModel`,
      meta: {
        auth: ['setting-ai-model'],
        title: '模型管理',
      },
      component: () => import('@/pages/setting/aiModel/index'),
    },
    {
      path: 'system_group_data/index/:id',
      name: `${pre}groupDataIndex`,
      meta: {
        auth: ['setting-system-group_data-index'],
        title: '首页导航按钮',
      },
      component: () => import('@/pages/system/group/list'),
    },
    {
      path: 'system_group_data/slide/:id',
      name: `${pre}groupDataSlide`,
      meta: {
        auth: ['setting-system-group_data-slide'],
        title: '首页幻灯片',
      },
      component: () => import('@/pages/system/group/list'),
    },
    {
      path: 'system_group_data/sign/:id',
      name: `${pre}groupDataSign`,
      meta: {
        auth: ['setting-system-group_data-sign'],
        title: '签到天数配置',
      },
      component: () => import('@/pages/system/group/list'),
    },
    // {
    //   path: 'system_group_data/order/:id',
    //   name: `${pre}groupDataOrder`,
    //   meta: {
    //     auth: ['setting-system-group_data-order'],
    //     title: '订单详情动态图'
    //   },
    //   component: () => import('@/pages/system/group/list')
    // },
    // {
    //   path: 'system_group_data/user/:id',
    //   name: `${pre}groupDataUser`,
    //   meta: {
    //     auth: ['setting-system-group_data-user'],
    //     title: '个人中心菜单'
    //   },
    //   component: () => import('@/pages/system/group/list')
    // },
    {
      path: 'system_group_data/new/:id',
      name: `${pre}groupDataNew`,
      meta: {
        auth: ['setting-system-group_data-new'],
        title: '首页滚动新闻',
      },
      component: () => import('@/pages/system/group/list'),
    },
    {
      path: 'system_group_data/search/:id',
      name: `${pre}groupDataSearch`,
      meta: {
        auth: ['setting-system-group_data-search'],
        title: '热门搜索',
      },
      component: () => import('@/pages/system/group/list'),
    },
    {
      path: 'system_group_data/poster/:id',
      name: `${pre}groupDataPoster`,
      meta: {
        auth: ['setting-system-group_data-poster'],
        title: '个人中心分销海报',
      },
      component: () => import('@/pages/system/group/list'),
    },
    {
      path: 'system_group_data/activity/:id',
      name: `${pre}groupDataActivity`,
      meta: {
        auth: ['setting-system-group_data-activity'],
        title: '首页活动区域图片',
      },
      component: () => import('@/pages/system/group/list'),
    },
    {
      path: 'system_group_data/system/:id',
      name: `${pre}groupDataSystem`,
      meta: {
        auth: ['setting-system-group_data-system'],
        title: '首页配置',
      },
      component: () => import('@/pages/system/group/list'),
    },
    {
      path: 'system_group_data/hot_money/:id',
      name: `${pre}groupDataHotMoney`,
      meta: {
        auth: ['admin-setting-system_group_data-hot_money'],
        title: '首页超值爆款',
      },
      component: () => import('@/pages/system/group/list'),
    },
    {
      path: 'merchant/system_store/index',
      name: `${pre}systemStore`,
      meta: {
        auth: ['setting-system-config-merchant'],
        title: '门店设置',
      },
      component: () => import('@/pages/setting/systemStore/index'),
    },
    {
      path: 'freight/express/index',
      name: `${pre}freight`,
      meta: {
        auth: ['setting-freight-express'],
        title: '物流公司',
      },
      component: () => import('@/pages/setting/freight/index'),
    },
    {
      path: 'freight/city/list',
      name: `${pre}dada`,
      meta: {
        auth: ['setting-system-city'],
        title: '城市数据',
      },
      component: () => import('@/pages/setting/cityDada/index'),
    },
    {
      path: 'freight/shipping_templates/list',
      name: `${pre}templates`,
      meta: {
        auth: ['setting-shipping-templates'],
        title: '运费模板',
      },
      component: () => import('@/pages/freight/shippingTemplates/index'),
    },
    {
      path: 'merchant/system_store/list',
      name: `${pre}store`,
      meta: {
        auth: ['setting-merchant-system-store'],
        title: '门店',
      },
      component: () => import('@/pages/setting/storeList/index'),
    },
    {
      path: 'merchant/system_store_staff/index',
      name: `${pre}staff`,
      meta: {
        auth: ['setting-merchant-system-store-staff'],
        title: '核销员',
      },
      component: () => import('@/pages/setting/clerkList/index'),
    },
    {
      path: 'theme_style',
      name: `${pre}themeStyle`,
      meta: {
        auth: ['admin-setting-theme_style'],
        title: '主题风格',
      },
      component: () => import('@diy/pages/legacy/themeStyle/index'),
    },
    {
      path: 'theme/micro_page',
      name: `${pre}microPage`,
      meta: {
        auth: ['setting-theme-micro_page'],
        title: '微页面',
      },
      component: () => import('@diy/pages/theme/micro_page/index'),
    },
    {
      path: 'pages',
      name: `${pre}page`,
      header: 'setting',
      redirect: {
        name: `${pre}devise`,
      },
    },
    {
      path: 'pages/devise/:type',
      name: `${pre}devise`,
      meta: {
        auth: ['admin-setting-pages-devise'],
        title: '店铺装修',
      },
      component: () => import('@diy/pages/devise/list'),
    },
    {
      path: 'pages/user_page/:type',
      name: `${pre}user`,
      meta: {
        auth: ['admin-setting-pages-user'],
        title: '个人中心',
      },
      component: () => import('@diy/pages/devise/list'),
    },
    {
      path: 'pages/link',
      name: `${pre}link`,
      meta: {
        auth: ['admin-setting-pages-link'],
        title: '链接管理',
      },
      component: () => import('@/pages/setting/link'),
    },
    {
      path: 'pages/cate_page/:type',
      name: `${pre}cate`,
      meta: {
        auth: ['admin-setting-pages-cate'],
        title: '商品分类',
      },
      component: () => import('@diy/pages/devise/list'),
    },
    {
      path: 'pages/diy',
      name: `${pre}diy`,
      meta: {
        auth: ['admin-setting-pages-diy'],
        title: '页面设计',
        activeMenu: routePre + '/setting/pages/devise',
      },
      component: () => import('@diy/pages/legacy/devisePage/index'),
    },
    {
      path: 'pages/diy_index',
      name: `${pre}index_diy`,
      meta: {
        auth: ['admin-setting-pages-diy'],
        title: '首页设计',
        fullScreen: true, //是否全屏显示main区域
      },
      component: () => import('@diy/pages/devise/diyIndex'),
    },
    {
      path: 'pages/links',
      name: `${pre}links`,
      meta: {
        auth: ['admin-setting-pages-links'],
        title: '页面链接',
      },
      component: () => import('@diy/pages/devise/links'),
    },
    {
      path: 'service_personnel/index',
      name: `${pre}servicePersonnel`,
      meta: {
        auth: ['setting-service-personnel'],
        title: '服务人员列表',
      },
      component: () => import('@/pages/personnel/index'),
    },
    {
      path: 'system_visualization_data',
      name: `${pre}systemGroupData`,
      meta: {
        auth: ['admin-setting-system_visualization_data'],
        title: '数据配置',
      },
      component: () => import('@/pages/system/group/visualization'),
    },
    {
      path: 'agreement',
      name: `${pre}notification`,
      meta: {
        auth: ['setting-agreement'],
        title: '协议设置',
      },
      component: () => import('@/pages/setting/agreement/index'),
    },
    {
      path: 'lang/list',
      name: `${pre}langList`,
      meta: {
        auth: ['admin-lang-list'],
        title: '语言列表',
      },
      component: () => import('@/pages/setting/multiLanguage/list'),
    },
    {
      path: 'lang/info',
      name: `${pre}langInfo`,
      meta: {
        auth: ['admin-lang-info'],
        title: '语言详情',
      },
      component: () => import('@/pages/setting/multiLanguage/langList'),
    },
    {
      path: 'lang/country',
      name: `${pre}langCountry`,
      meta: {
        auth: ['admin-lang-country'],
        title: '地区关联语言',
      },
      component: () => import('@/pages/setting/multiLanguage/country'),
    },
    {
      path: 'my_theme',
      name: `${pre}myTheme`,
      meta: {
        title: '我的主题',
      },
      component: () => import('@diy/pages/theme/myTheme/index'),
    },
    {
      path: 'mall_theme',
      name: `${pre}mallTheme`,
      meta: {
        title: '商城主题',
      },
      component: () => import('@diy/pages/theme/mallTheme/index'),
    },
    {
      path: 'edit_theme',
      name: `${pre}editTheme`,
      meta: {
        title: '主题风格',
        fullScreen: true, //是否全屏显示main区域
      },
      component: () => import('@diy/pages/theme/editTheme/index'),
    },
  ],
};
