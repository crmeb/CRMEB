/**
 * 砍价扩展路由
 */
import setting from '@/setting';
const routePre = setting.routePre;
const pre = 'bargain_';

export default [
  // ===== 新路径 =====
  {
    path: routePre + '/marketing/bargain/product',
    section: 'bargain',
    action: 'product',
    meta: {
      auth: ['marketing-store_bargain'],
      title: '砍价商品',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}product`,
        component: () => import('./pages/product/index.vue'),
        meta: {
          auth: ['marketing-store_bargain'],
          title: '砍价商品',
        },
      },
    ],
  },
  {
    path: routePre + '/marketing/bargain/create/:id?/:copy?',
    section: 'bargain',
    action: 'create',
    meta: {
      auth: ['marketing-store_bargain-create'],
      title: '添加/编辑砍价商品',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}create`,
        component: () => import('./pages/create/index.vue'),
        meta: {
          auth: ['marketing-store_bargain-create'],
          title: '添加/编辑砍价商品',
          activeMenu: routePre + '/marketing/bargain/product',
        },
      },
    ],
  },
  {
    path: routePre + '/marketing/bargain/pink',
    section: 'bargain',
    action: 'pink',
    meta: {
      auth: ['marketing-store_bargain-bargain_list'],
      title: '砍价列表',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}pink`,
        component: () => import('./pages/pink/index.vue'),
        meta: {
          auth: ['marketing-store_bargain-bargain_list'],
          title: '砍价列表',
        },
      },
    ],
  },
  {
    path: routePre + '/marketing/bargain/statistics/:id',
    section: 'bargain',
    action: 'statistics',
    meta: {
      auth: ['marketing-store_bargain-statistics'],
      title: '砍价统计',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}statistics`,
        component: () => import('./pages/statistics/index.vue'),
        meta: {
          auth: ['marketing-store_bargain-statistics'],
          title: '砍价统计',
          activeMenu: routePre + '/marketing/bargain/product',
        },
      },
    ],
  },
  // ===== 兼容旧路由（无组件，仅用于菜单选中） =====
  {
    path: routePre + '/marketing/store_bargain/index',
    section: 'bargain',
    action: 'product',
    name: `${pre}legacyIndex`,
    meta: { title: '砍价商品' },
  },
  {
    path: routePre + '/marketing/store_bargain/bargain_list',
    section: 'bargain',
    action: 'pink',
    name: `${pre}legacyPink`,
    meta: { title: '砍价列表' },
  },
  {
    path: routePre + '/marketing/store_bargain/create/:id?/:copy?',
    section: 'bargain',
    action: 'create',
    name: `${pre}legacyCreate`,
    meta: { title: '添加/编辑砍价商品' },
  },
  {
    path: routePre + '/marketing/store_bargain/statistics/:id',
    section: 'bargain',
    action: 'statistics',
    name: `${pre}legacyStatistics`,
    meta: { title: '砍价统计' },
  },
];
