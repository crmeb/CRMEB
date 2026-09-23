/**
 * 微信小程序模块扩展路由（小程序设置/CI上传/链接 + 直播）
 * 自动被 router/routers.js 扫描加载（pages/plugin 目录下 routes.js 文件）
 */
import setting from '@/setting';
const routePre = setting.routePre;

export default [
  // ===== 小程序下载 =====
  {
    path: routePre + '/app/routine/download',
    section: 'wechat_mini',
    action: 'download',
    meta: { auth: ['routine-download'], title: '小程序下载' },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: 'routine_download',
        component: () => import('./pages/routine/download/index.vue'),
        meta: { auth: ['routine-download'], title: '小程序下载' },
      },
    ],
  },
  // ===== 小程序 CI 一键上传 =====
  {
    path: routePre + '/app/routine/ci_upload',
    section: 'wechat_mini',
    action: 'ci_upload',
    meta: { auth: ['routine-ci-upload'], title: '小程序上传' },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: 'routine_ci_upload',
        component: () => import('./pages/routine/ciUpload/index.vue'),
        meta: { auth: ['routine-ci-upload'], title: '小程序上传' },
      },
    ],
  },
  // ===== 小程序链接管理 =====
  {
    path: routePre + '/app/routine/link',
    section: 'wechat_mini',
    action: 'link',
    meta: { auth: ['routine-link'], title: '小程序链接' },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: 'routine_link',
        component: () => import('./pages/routine/link/index.vue'),
        meta: { auth: ['routine-link'], title: '小程序链接' },
      },
    ],
  },
  // ===== 直播间管理 =====
  {
    path: routePre + '/marketing/live/room',
    section: 'live',
    action: 'room_list',
    meta: { auth: true, title: '直播间管理' },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: 'live_room',
        component: () => import('./pages/live/room/index.vue'),
        meta: { auth: true, title: '直播间管理' },
      },
    ],
  },
  {
    path: routePre + '/marketing/live/add_live_room',
    section: 'live',
    action: 'room_create',
    meta: { auth: true, title: '添加直播间' },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: 'live_create_room',
        component: () => import('./pages/live/room/create.vue'),
        meta: { auth: true, title: '添加直播间', activeMenu: routePre + '/marketing/live/room' },
      },
    ],
  },
  // ===== 直播商品管理 =====
  {
    path: routePre + '/marketing/live/goods',
    section: 'live',
    action: 'goods_list',
    meta: { auth: true, title: '直播商品' },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: 'live_goods',
        component: () => import('./pages/live/goods/list.vue'),
        meta: { auth: true, title: '直播商品' },
      },
    ],
  },
  {
    path: routePre + '/marketing/live/add_live_goods',
    section: 'live',
    action: 'goods_add',
    meta: { auth: true, title: '添加直播商品' },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: 'live_add_goods',
        component: () => import('./pages/live/goods/add.vue'),
        meta: { auth: true, title: '添加直播商品', activeMenu: routePre + '/marketing/live/goods' },
      },
    ],
  },
  // ===== 主播管理 =====
  {
    path: routePre + '/marketing/live/anchor',
    section: 'live',
    action: 'anchor_list',
    meta: { auth: true, title: '主播管理' },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: 'live_anchor',
        component: () => import('./pages/live/anchor/list.vue'),
        meta: { auth: true, title: '主播管理' },
      },
    ],
  },
];
