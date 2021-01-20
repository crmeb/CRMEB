import BasicLayout from '@/components/main'

const meta = {
  auth: true
}

const pre = 'setting_'

export default {
  path: '/admin/setting',
  name: 'setting',
  header: 'setting',
  redirect: {
    name: `${pre}systemRole`
  },
  component: BasicLayout,
  children: [
    {
      path: 'system_role/index',
      name: `${pre}systemRole`,
      meta: {
        auth: ['setting-system-role'],
        title: '身份管理'
      },
      component: () => import('@/pages/setting/systemRole/index')
    },
    {
      path: 'system_admin/index',
      name: `${pre}systemAdmin`,
      meta: {
        auth: ['setting-system-list'],
        title: '管理员列表'
      },
      component: () => import('@/pages/setting/systemAdmin/index')
    },
    {
      path: 'system_menus/index',
      name: `${pre}systemMenus`,
      meta: {
        auth: ['setting-system-menus'],
        title: '权限规则'
      },
      component: () => import('@/pages/setting/systemMenus/index')
    },
    {
      path: 'system_config',
      name: `${pre}setSystem`,
      meta: {
        auth: ['setting-system-config'],
        title: '系统设置'
      },
      component: () => import('@/pages/setting/setSystem/index')
    },
    {
      path: 'system_config/:type?/:tab_id?',
      name: `${pre}setApp`,
      meta: {
        ...meta,
        title: '应用设置'
      },
      component: () => import('@/pages/setting/setSystem/index')
    },
    {
      path: 'system_config/payment/:type?/:tab_id?',
      name: `${pre}payment`,
      meta: {
        ...meta,
        title: '支付配置'
      },
      component: () => import('@/pages/setting/setSystem/index')
    },
    {
      path: 'system_config_message/:type?/:tab_id?',
      name: `${pre}message`,
      meta: {
        auth: ['setting-system-config-message'],
        title: '短信开关'
      },
      component: () => import('@/pages/setting/setSystem/index')
    },
    {
      path: 'system_config_logistics/:type?/:tab_id?',
      name: `${pre}logistics`,
      meta: {
        auth: ['setting-system-config-logistics'],
        title: '物流配置'
      },
      component: () => import('@/pages/setting/setSystem/index')
    },
    {
      path: 'sms/sms_config/index',
      name: `${pre}config`,
      meta: {
        auth: ['setting-sms-sms-config'],
        title: '短信账户'
      },
      component: () => import('@/pages/notify/smsConfig/index')
    },
    {
      path: 'sms/sms_template_apply/index',
      name: `${pre}smsTemplateApply`,
      meta: {
        auth: ['setting-sms-config-template'],
        title: '短信模板'
      },
      component: () => import('@/pages/notify/smsTemplateApply/index')
    },
    {
      path: 'sms/sms_pay/index',
      name: `${pre}smsPay`,
      meta: {
        auth: ['setting-sms-sms-template'],
        title: '短信购买'
      },
      component: () => import('@/pages/notify/smsPay/index')
    },
    {
      path: 'sms/sms_template_apply/commons',
      name: `${pre}commons`,
      meta: {
        ...meta,
        title: '公共短信模板'
      },
      component: () => import('@/pages/notify/smsTemplateApply/index')
    },
    {
      path: 'system_group_data/index/:id',
      name: `${pre}groupDataIndex`,
      meta: {
        auth: ['setting-system-group_data-index'],
        title: '首页导航按钮'
      },
      component: () => import('@/pages/system/group/list')
    },
    {
      path: 'system_group_data/slide/:id',
      name: `${pre}groupDataSlide`,
      meta: {
        auth: ['setting-system-group_data-slide'],
        title: '首页幻灯片'
      },
      component: () => import('@/pages/system/group/list')
    },
    {
      path: 'system_group_data/new/:id',
      name: `${pre}groupDataNew`,
      meta: {
        auth: ['setting-system-group_data-new'],
        title: '首页滚动新闻'
      },
      component: () => import('@/pages/system/group/list')
    },
    {
      path: 'system_group_data/search/:id',
      name: `${pre}groupDataNew`,
      meta: {
        auth: ['setting-system-group_data-search'],
        title: '热门搜索'
      },
      component: () => import('@/pages/system/group/list')
    },
    {
      path: 'system_group_data/hot/:id',
      name: `${pre}groupDataHot`,
      meta: {
        auth: ['setting-system-group_data-hot'],
        title: '热门榜单推荐'
      },
      component: () => import('@/pages/system/group/list')
    },
    {
      path: 'system_group_data/new_product/:id',
      name: `${pre}groupDataNewProduct`,
      meta: {
        auth: ['setting-system-group_data-new_product'],
        title: '首发新品推荐'
      },
      component: () => import('@/pages/system/group/list')
    },
    {
      path: 'system_group_data/promotion/:id',
      name: `${pre}groupDataPromotion`,
      meta: {
        auth: ['setting-system-group_data-promotion'],
        title: '促销单品推荐'
      },
      component: () => import('@/pages/system/group/list')
    },
    {
      path: 'system_group_data/best/:id',
      name: `${pre}groupDataBest`,
      meta: {
        auth: ['setting-system-group_data-best'],
        title: '精品推荐'
      },
      component: () => import('@/pages/system/group/list')
    },
    {
      path: 'system_group_data/activity/:id',
      name: `${pre}groupDataActivity`,
      meta: {
        auth: ['setting-system-group_data-activity'],
        title: '首页活动区域图片'
      },
      component: () => import('@/pages/system/group/list')
    },
    {
      path: 'system_group_data/system/:id',
      name: `${pre}groupDataSystem`,
      meta: {
        auth: ['setting-system-group_data-system'],
        title: '首页配置'
      },
      component: () => import('@/pages/system/group/list')
    },
    {
      path: 'system_group_data/hot_money/:id',
      name: `${pre}groupDataHotMoney`,
      meta: {
        auth: ['admin-setting-system_group_data-hot_money'],
        title: '首页超值爆款'
      },
      component: () => import('@/pages/system/group/list')
    },
    {
      path: 'freight/express/index',
      name: `${pre}freight`,
      meta: {
        auth: ['setting-freight-express'],
        title: '物流公司'
      },
      component: () => import('@/pages/setting/freight/index')
    },
    {
      path: 'freight/city/list',
      name: `${pre}dada`,
      meta: {
        auth: ['setting-system-city'],
        title: '城市数据'
      },
      component: () => import('@/pages/setting/cityDada/index')
    },
    {
      path: 'freight/shipping_templates/list',
      name: `${pre}templates`,
      meta: {
        auth: ['setting-shipping-templates'],
        title: '运费模板'
      },
      component: () => import('@/pages/setting/shippingTemplates/index')
    },
    {
      path: 'pages/devise',
      name: `${pre}devise`,
      meta: {
        auth: ['admin-setting-pages-devise'],
        title: '页面设计列表'
      },
      component: () => import('@/pages/setting/devise/list')
    },
    {
      path: 'pages/diy',
      name: `${pre}diy`,
      meta: {
        auth: ['admin-setting-pages-diy'],
        title: '页面设计'
      },
      component: () => import('@/pages/setting/devise/index')
    },
    {
      path: 'pages/links',
      name: `${pre}links`,
      meta: {
        auth: ['admin-setting-pages-links'],
        title: '页面链接'
      },
      component: () => import('@/pages/setting/devise/links')
    },
    {
      path: 'system_group_data',
      name: `${pre}systemGroupData`,
      meta: {
              auth: ['admin-setting-pages-links'],
              title: '数据配置'
          },
          component: () => import('@/pages/system/group/list')
      },
    {
      path: 'delivery_service/index',
      name: `${pre}deliveryService`,
      meta: {
        auth: ['setting-delivery-service'],
        title: '配送员列表'
      },
      component: () => import('@/pages/setting/deliveryService/index')
    },
    {
      path: 'system_group_data/kf_adv',
      name: `${pre}kfAdv`,
      meta: {
        auth: ['setting-system-group_data-kf_adv'],
        title: '客服页面广告'
      },
      component: () => import('@/pages/system/group/kfAdv')
    },
    {
      path: 'store_service/speechcraft',
      name: `${pre}speechcraft`,
      meta: {
        auth: ['admin-setting-store_service-speechcraft'],
        title: '客服话术'
      },
      component: () => import('@/pages/setting/storeService/speechcraft')
    },
    {
      path: 'store_service/feedback',
      name: `${pre}feedback`,
      meta: {
        auth: ['admin-setting-store_service-feedback'],
        title: '用户留言'
      },
      component: () => import('@/pages/setting/storeService/feedback')
    },
    {
      path: 'store_service/index',
      name: `${pre}service`,
      meta: {
        auth: ['setting-store-service'],
        title: '客服管理'
      },
      component: () => import('@/pages/setting/storeService/index')
    },
  ]
}
