/**
 * 拼团扩展路由
 */
import setting from '@/setting';
const routePre = setting.routePre;
const pre = 'combination_';

export default [
  {
    path: routePre + '/marketing/combination/product',
    section: 'combination',
    action: 'product',
    meta: {
      auth: ['marketing-combination-product'],
      title: '拼团商品',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}product`,
        component: () => import('./pages/product/index.vue'),
        meta: {
          auth: ['marketing-combination-product'],
          title: '拼团商品',
        },
      },
    ],
  },
  {
    path: routePre + '/marketing/combination/create/:id?/:copy?',
    section: 'combination',
    action: 'create',
    meta: {
      auth: ['marketing-combination-create'],
      title: '添加/编辑拼团商品',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}create`,
        component: () => import('./pages/create/index.vue'),
        meta: {
          auth: ['marketing-combination-create'],
          title: '添加/编辑拼团商品',
          activeMenu: routePre + '/marketing/combination/product',
        },
      },
    ],
  },
  {
    path: routePre + '/marketing/combination/pink',
    section: 'combination',
    action: 'pink',
    meta: {
      auth: ['marketing-combination-pink'],
      title: '拼团列表',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}pink`,
        component: () => import('./pages/pink/index.vue'),
        meta: {
          auth: ['marketing-combination-pink'],
          title: '拼团列表',
        },
      },
    ],
  },
  {
    path: routePre + '/marketing/combination/statistics/:id',
    section: 'combination',
    action: 'statistics',
    meta: {
      auth: ['marketing-combination-statistics'],
      title: '拼团统计',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}statistics`,
        component: () => import('./pages/statistics/index.vue'),
        meta: {
          auth: ['marketing-combination-statistics'],
          title: '拼团统计',
          activeMenu: routePre + '/marketing/combination/product',
        },
      },
    ],
  },
  // 兼容旧路由
  {
    path: routePre + '/marketing/store_combination/index',
    section: 'combination',
    action: 'product',
    name: `${pre}legacyIndex`,
    meta: { title: '拼团商品' },
  },
  {
    path: routePre + '/marketing/store_combination/combina_list',
    section: 'combination',
    action: 'pink',
    name: `${pre}legacyPink`,
    meta: { title: '拼团列表' },
  },
  {
    path: routePre + '/marketing/store_combination/create/:id?/:copy?',
    section: 'combination',
    action: 'create',
    name: `${pre}legacyCreate`,
    meta: { title: '添加/编辑拼团商品' },
  },
  {
    path: routePre + '/marketing/store_combination/statistics/:id',
    section: 'combination',
    action: 'statistics',
    name: `${pre}legacyStatistics`,
    meta: { title: '拼团统计' },
  },
];
