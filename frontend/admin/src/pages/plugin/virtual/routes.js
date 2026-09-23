/**
 * 网盘商品路由
 * 兼容模式：既是 Vue Router 路由配置，也包含 section/action 元数据供扩展机制使用
 */
import setting from '@/setting';
const routePre = setting.routePre;
const pre = 'product_';

export default [
  {
    path: routePre + '/product/virtual/list',
    meta: {
      auth: ['admin-virtual-product-list'],
      title: '网盘商品',
      keepAlive: true,
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}virtualProductList`,
        section: 'product',
        action: 'list',
        component: () => import('@/pages/plugin/virtual/App'),
        props: { section: 'product', action: 'list' },
        meta: {
          auth: ['admin-virtual-product-list'],
          title: '网盘商品',
          keepAlive: true,
        },
      },
    ],
  },
  {
    path: routePre + '/product/virtual/add/:id?',
    meta: {
      auth: ['admin-virtual-product-add'],
      title: '网盘商品添加',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}virtualProductAdd`,
        section: 'product',
        action: 'add',
        component: () => import('@/pages/plugin/virtual/App'),
        props: (route) => ({
          section: 'product',
          action: 'add',
          params: { id: route.params.id ? parseInt(route.params.id) : 0 },
        }),
        meta: {
          auth: ['admin-virtual-product-add'],
          title: '网盘商品添加',
          activeMenu: routePre + '/product/virtual/list',
        },
      },
    ],
  },
  {
    path: routePre + '/product/virtual/order/list',
    meta: {
      auth: ['admin-virtual-order-list'],
      title: '网盘订单',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}virtualOrderList`,
        section: 'order',
        action: 'list',
        component: () => import('@/pages/plugin/virtual/App'),
        props: { section: 'order', action: 'list' },
        meta: {
          auth: ['admin-virtual-order-list'],
          title: '网盘订单',
        },
      },
    ],
  },
  {
    path: routePre + '/product/virtual/order/detail/:orderNo',
    meta: {
      auth: ['admin-virtual-order-list'],
      title: '订单详情',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}virtualOrderDetail`,
        section: 'order',
        action: 'detail',
        component: () => import('@/pages/plugin/virtual/App'),
        props: (route) => ({ section: 'order', action: 'detail', params: { orderNo: route.params.orderNo } }),
        meta: {
          auth: ['admin-virtual-order-list'],
          title: '订单详情',
          activeMenu: routePre + '/product/virtual/order/list',
        },
      },
    ],
  },
];
