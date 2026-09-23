/**
 * 微信公众号模块扩展路由（微信菜单/自动回复/图文管理/用户标签分组/行为记录）
 * 自动被 router/routers.js 扫描加载（pages/plugin 目录下 routes.js 文件）
 */
import setting from '@/setting';
const routePre = setting.routePre;

export default [
  // ===== 微信菜单 =====
  {
    path: routePre + '/app/wechat/setting/menus/index',
    section: 'wechat_mp',
    action: 'menus',
    meta: { auth: ['application-wechat-menus'], title: '微信菜单' },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: 'wechat_menus',
        component: () => import('./pages/menus/index.vue'),
        meta: { auth: ['application-wechat-menus'], title: '微信菜单' },
      },
    ],
  },
  // ===== 用户标签 =====
  {
    path: routePre + '/app/wechat/wechat_user/user/tag',
    section: 'wechat_mp',
    action: 'user_tag',
    meta: { auth: ['wechat-wechat-user-tag'], title: '用户标签' },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: 'wechat_tag',
        component: () => import('./pages/user/tag.vue'),
        meta: { auth: ['wechat-wechat-user-tag'], title: '用户标签' },
      },
    ],
  },
  // ===== 用户分组 =====
  {
    path: routePre + '/app/wechat/wechat_user/user/group',
    section: 'wechat_mp',
    action: 'user_group',
    meta: { auth: ['wechat-wechat-user-group'], title: '用户分组' },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: 'wechat_group',
        component: () => import('./pages/user/tag.vue'),
        meta: { auth: ['wechat-wechat-user-group'], title: '用户分组' },
      },
    ],
  },
  // ===== 用户行为记录 =====
  {
    path: routePre + '/app/wechat/wechat_user/user/message',
    section: 'wechat_mp',
    action: 'user_message',
    meta: { auth: ['wechat-wechat-user-message'], title: '用户行为记录' },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: 'wechat_message',
        component: () => import('./pages/user/message.vue'),
        meta: { auth: ['wechat-wechat-user-message'], title: '用户行为记录' },
      },
    ],
  },
  // ===== 图文管理 =====
  {
    path: routePre + '/app/wechat/news_category/index',
    section: 'wechat_mp',
    action: 'news_category_list',
    meta: { auth: ['wechat-wechat-news-category-index'], title: '图文管理' },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: 'wechat_news_category',
        component: () => import('./pages/newsCategory/index.vue'),
        meta: { auth: ['wechat-wechat-news-category-index'], title: '图文管理' },
      },
    ],
  },
  // ===== 图文添加/编辑 =====
  {
    path: routePre + '/app/wechat/news_category/save/:id?',
    section: 'wechat_mp',
    action: 'news_category_save',
    meta: { auth: ['wechat-wechat-news-category-save'], title: '图文添加' },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: 'wechat_news_category_save',
        component: () => import('./pages/newsCategory/save.vue'),
        meta: {
          auth: ['wechat-wechat-news-category-save'],
          title: '图文添加',
          activeMenu: routePre + '/app/wechat/news_category/index',
        },
      },
    ],
  },
  // ===== 关注回复 =====
  {
    path: routePre + '/app/wechat/reply/follow/:key',
    section: 'wechat_mp',
    action: 'reply_follow',
    meta: { auth: ['wechat-wechat-reply-subscribe'], title: '微信关注回复' },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: 'wechat_reply_follow',
        component: () => import('./pages/reply/follow.vue'),
        meta: { auth: ['wechat-wechat-reply-subscribe'], title: '微信关注回复' },
      },
    ],
  },
  // ===== 关键字回复 =====
  {
    path: routePre + '/app/wechat/reply/keyword',
    section: 'wechat_mp',
    action: 'reply_keyword',
    meta: { auth: ['wechat-wechat-reply-keyword'], title: '关键字回复' },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: 'wechat_reply_keyword',
        component: () => import('./pages/reply/keyword.vue'),
        meta: { auth: ['wechat-wechat-reply-keyword'], title: '关键字回复' },
      },
    ],
  },
  // ===== 关键字添加 =====
  {
    path: routePre + '/app/wechat/reply/keyword/save/:id?',
    section: 'wechat_mp',
    action: 'reply_keyword_save',
    meta: { auth: ['wechat-wechat-reply-save'], title: '关键字添加' },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: 'wechat_reply_keyword_save',
        component: () => import('./pages/reply/follow.vue'),
        meta: {
          auth: ['wechat-wechat-reply-save'],
          title: '关键字添加',
          activeMenu: routePre + '/app/wechat/reply/keyword',
        },
      },
    ],
  },
  // ===== 无效关键词回复 =====
  {
    path: routePre + '/app/wechat/reply/index/:key',
    section: 'wechat_mp',
    action: 'reply_index',
    meta: { auth: ['wechat-wechat-reply-default'], title: '无效关键词回复' },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: 'wechat_reply_index',
        component: () => import('./pages/reply/follow.vue'),
        meta: { auth: ['wechat-wechat-reply-default'], title: '无效关键词回复' },
      },
    ],
  },
];
