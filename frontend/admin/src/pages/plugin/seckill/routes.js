/**
 * 秒杀扩展路由
 * 兼容模式：既是 Vue Router 路由配置，也包含 section/action 元数据供扩展机制使用
 */
import setting from '@/setting';
const routePre = setting.routePre;
const pre = 'seckill_';

export default [
  {
    path: routePre + '/marketing/seckill/activity',
    section: 'seckill',
    action: 'activity',
    meta: {
      auth: ['marketing-seckill-activity'],
      title: '秒杀活动',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}activity`,
        component: () => import('./pages/activity/index.vue'),
        meta: {
          auth: ['marketing-seckill-activity'],
          title: '秒杀活动',
        },
      },
    ],
  },
  {
    path: routePre + '/marketing/seckill/activity/create/:id?',
    section: 'seckill',
    action: 'activity_create',
    meta: {
      auth: ['marketing-seckill-save'],
      title: '添加/编辑秒杀活动',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}activityCreate`,
        component: () => import('./pages/activity/create.vue'),
        meta: {
          auth: ['marketing-seckill-save'],
          title: '添加/编辑秒杀活动',
          activeMenu: routePre + '/marketing/seckill/activity',
        },
      },
    ],
  },
  {
    path: routePre + '/marketing/seckill/product',
    section: 'seckill',
    action: 'product',
    meta: {
      auth: ['marketing-seckill-product'],
      title: '秒杀商品',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}product`,
        component: () => import('./pages/product/index.vue'),
        meta: {
          auth: ['marketing-seckill-product'],
          title: '秒杀商品',
        },
      },
    ],
  },
  {
    path: routePre + '/marketing/seckill/product/create/:id?/:copy?',
    section: 'seckill',
    action: 'product_create',
    meta: {
      auth: ['marketing-seckill-save'],
      title: '添加/编辑秒杀商品',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}productCreate`,
        component: () => import('./pages/product/create.vue'),
        meta: {
          auth: ['marketing-seckill-save'],
          title: '添加/编辑秒杀商品',
          activeMenu: routePre + '/marketing/seckill/product',
        },
      },
    ],
  },
  {
    path: routePre + '/marketing/seckill/statistics/:id',
    section: 'seckill',
    action: 'statistics',
    meta: {
      auth: ['marketing-seckill-statistics'],
      title: '秒杀统计',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}statistics`,
        component: () => import('./pages/statistics/index.vue'),
        meta: {
          auth: ['marketing-seckill-statistics'],
          title: '秒杀统计',
        },
      },
    ],
  },
  // 秒杀配置（管理秒杀时间段配置）
  {
    path: routePre + '/marketing/seckill/config',
    section: 'seckill',
    action: 'config',
    meta: {
      auth: ['marketing-seckill-config'],
      title: '秒杀配置',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}config`,
        component: () => import('./pages/config/index.vue'),
        meta: {
          auth: ['marketing-seckill-config'],
          title: '秒杀配置',
        },
      },
    ],
  },
  // 兼容旧路由：指向主程序 marketing 模块中的已有页面
  {
    path: routePre + '/marketing/store_seckill/index',
    section: 'seckill',
    action: 'product',
    name: `${pre}legacyIndex`,
    meta: {
      title: '秒杀商品',
    },
  },
  {
    path: routePre + '/marketing/store_seckill/list',
    section: 'seckill',
    action: 'activity',
    name: `${pre}legacyList`,
    meta: {
      title: '秒杀活动',
    },
  },
  {
    path: routePre + '/marketing/store_seckill/statistics/:id',
    section: 'seckill',
    action: 'statistics',
    name: `${pre}legacyStatistics`,
    meta: {
      title: '秒杀统计',
    },
  },
];
