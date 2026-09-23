/**
 * 实物商品路由
 * 兼容模式：既是 Vue Router 路由配置，也包含 section/action 元数据供扩展机制使用
 */
import setting from '@/setting';
const routePre = setting.routePre;
const pre = 'product_';

export default [
  {
    path: routePre + '/product/normal/list',
    meta: {
      auth: ['admin-normal-product-list'],
      title: '商品管理',
      keepAlive: true,
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}normalProductList`,
        section: 'product',
        action: 'list',
        component: () => import('@/pages/plugin/normal/App'),
        props: { section: 'product', action: 'list' },
        meta: {
          auth: ['admin-normal-product-list'],
          title: '商品管理',
          keepAlive: true,
        },
      },
    ],
  },
  {
    path: routePre + '/product/normal/add/:id?',
    meta: {
      auth: ['admin-normal-product-add'],
      title: '实物商品添加',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}normalProductAdd`,
        section: 'product',
        action: 'add',
        component: () => import('@/pages/plugin/normal/App'),
        props: (route) => ({
          section: 'product',
          action: 'add',
          params: { id: route.params.id ? parseInt(route.params.id) : 0 },
        }),
        meta: {
          auth: ['admin-normal-product-add'],
          title: '实物商品添加',
          activeMenu: routePre + '/product/normal/list',
        },
      },
    ],
  },
  {
    path: routePre + '/product/normal/statistics',
    meta: {
      auth: ['admin-normal-statistics'],
      title: '商品统计',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}normalStatistics`,
        section: 'statistic',
        action: 'index',
        component: () => import('@/pages/plugin/normal/App'),
        props: { section: 'statistic', action: 'index' },
        meta: {
          auth: ['admin-normal-statistics'],
          title: '商品统计',
        },
      },
    ],
  },
  {
    path: routePre + '/product/normal/order/list',
    meta: {
      auth: ['admin-normal-order-list'],
      title: '实物订单',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}normalOrderList`,
        section: 'order',
        action: 'list',
        component: () => import('@/pages/plugin/normal/App'),
        props: { section: 'order', action: 'list' },
        meta: {
          auth: ['admin-normal-order-list'],
          title: '实物订单',
        },
      },
    ],
  },
  {
    path: routePre + '/product/normal/order/detail/:orderNo',
    meta: {
      auth: ['admin-normal-order-list'],
      title: '订单详情',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}normalOrderDetail`,
        section: 'order',
        action: 'detail',
        component: () => import('@/pages/plugin/normal/App'),
        props: (route) => ({ section: 'order', action: 'detail', params: { orderNo: route.params.orderNo } }),
        meta: {
          auth: ['admin-normal-order-list'],
          title: '订单详情',
          activeMenu: routePre + '/product/normal/order/list',
        },
      },
    ],
  },
];
