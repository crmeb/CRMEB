// +---------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +---------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +---------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +---------------------------------------------------------------------

const pre = 'kefu_';

export default [
  // 登录
  {
    path: '/admin/login',
    name: 'login',
    meta: {
      title: '登录',
      hideInMenu: true,
    },
    component: () => import('@/pages/account/login'),
  },
  // 客服
  {
    path: '/kefu',
    name: `${pre}index`,
    meta: {
      auth: true,
      title: '客服管理',
      kefu: true,
    },
    component: () => import('@/pages/kefu/index'),
  },
  {
    path: '/kefu/mobile_list',
    name: `${pre}mobile_list`,
    meta: {
      auth: true,
      title: '消息列表',
      kefu: true,
    },
    component: () => import('@/pages/kefu/mobile/chat_list'),
  },
  {
    path: '/kefu/mobile_chat',
    name: `${pre}mobile_chat`,
    meta: {
      auth: true,
      title: '对话详情',
      kefu: true,
    },
    component: () => import('@/pages/kefu/mobile/index'),
  },
  {
    path: '/kefu/pc_list',
    name: `${pre}pc_list`,
    meta: {
      auth: true,
      title: '客服',
      kefu: true,
    },
    component: () => import('@/pages/kefu/pc/index'),
  },
  {
    path: '/kefu/orderList/:type?/:toUid?',
    name: `${pre}order-list`,
    meta: {
      auth: true,
      title: '订单列表',
      kefu: true,
    },
    component: () => import('@/pages/kefu/mobile/orderList/index'),
  },
  {
    path: '/kefu/orderDetail/:id?/:goname?',
    name: `${pre}order-detail`,
    meta: {
      auth: true,
      title: '订单详情',
      kefu: true,
    },
    component: () => import('@/pages/kefu/mobile/orderList/orderDetail.vue'),
  },
  {
    path: '/kefu/orderDelivery/:id?/:orderId?',
    name: `${pre}order-delivery`,
    meta: {
      auth: true,
      title: '发货',
      kefu: true,
    },
    component: () => import('@/pages/kefu/mobile/orderList/orderDelivery.vue'),
  },
  {
    path: '/kefu/user/index/:uid?/:type?',
    name: `${pre}user-index`,
    meta: {
      auth: true,
      title: '客户信息',
      kefu: true,
    },
    component: () => import('@/pages/kefu/mobile/user/index'),
  },
  {
    path: '/kefu/goods/list',
    name: `${pre}goods-list`,
    meta: {
      auth: true,
      title: '商品列表',
      kefu: true,
    },
    component: () => import('@/pages/kefu/mobile/goods/list.vue'),
  },
  {
    path: '/kefu/goods/detail',
    name: `${pre}goods-detail`,
    meta: {
      auth: true,
      title: '商品列表',
      kefu: true,
    },
    component: () => import('@/pages/kefu/mobile/goods/detail.vue'),
  },
  {
    path: '/kefu/appChat',
    name: `${pre}app-chat`,
    meta: {
      auth: true,
      title: '客服',
      kefu: true,
    },
    component: () => import('@/pages/kefu/appChat/index'),
  },
  {
    path: '/kefu/mobile_user_chat',
    name: `${pre}app-mobile_user_chat`,
    meta: {
      auth: true,
      title: '用户客服',
      kefu: true,
    },
    component: () => import('@/pages/kefu/appChat/mobile/index'),
  },
  {
    path: '/kefu/mobile_feedback',
    name: `${pre}app-mobile_feedback`,
    meta: {
      auth: true,
      title: '用户反馈',
      kefu: true,
    },
    component: () => import('@/pages/kefu/appChat/mobile/feedback'),
  },
];
