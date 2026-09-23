/**
 * 卡密商品路由（UMD 扩展模式）
 * 页面通过 PageContainer 运行时加载 UMD 扩展包（/admin/static/extensions/cardkey/），
 * 主构建不再打包页面源码；扩展未安装（UMD 缺失）时页面显示"扩展未安装"。
 * 路由参数（:id? / :orderNo）由子页面从 $route.params 自行读取。
 */
import setting from '@/setting';
const routePre = setting.routePre;
const pre = 'product_';

const pageContainer = () => import('@/pages/system/plugin/PageContainer');

export default [
  {
    path: routePre + '/product/cardkey/list',
    meta: {
      auth: ['admin-cardkey-product-list'],
      title: '卡密商品',
      keepAlive: true,
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}cardkeyProductList`,
        component: pageContainer,
        props: { typeKey: 'cardkey', section: 'cardkey', action: 'list' },
        meta: {
          auth: ['admin-cardkey-product-list'],
          title: '卡密商品',
          keepAlive: true,
        },
      },
    ],
  },
  {
    path: routePre + '/product/cardkey/add',
    name: `${pre}cardkeyProductAdd`,
    meta: {
      auth: ['admin-cardkey-product-add'],
      title: '添加商品',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: ':id?',
        name: `${pre}cardkeyProductAddId`,
        component: pageContainer,
        props: { typeKey: 'cardkey', section: 'cardkey', action: 'add' },
        meta: {
          auth: ['admin-cardkey-product-add'],
          title: '添加商品',
          activeMenu: routePre + '/product/cardkey/list',
        },
      },
    ],
  },
  {
    path: routePre + '/product/cardkey/stock/list',
    meta: {
      auth: ['admin-cardkey-stock-list'],
      title: '卡密库存',
      keepAlive: true,
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}cardkeyStockList`,
        component: pageContainer,
        props: { typeKey: 'cardkey', section: 'cardkey', action: 'stock' },
        meta: {
          auth: ['admin-cardkey-stock-list'],
          title: '卡密库存',
          keepAlive: true,
        },
      },
    ],
  },
  {
    path: routePre + '/product/cardkey/order/list',
    meta: {
      auth: ['admin-cardkey-order-list'],
      title: '订单管理',
      keepAlive: true,
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}cardkeyOrderList`,
        component: pageContainer,
        props: { typeKey: 'cardkey', section: 'cardkey', action: 'order' },
        meta: {
          auth: ['admin-cardkey-order-list'],
          title: '订单管理',
          keepAlive: true,
        },
      },
    ],
  },
  {
    path: routePre + '/product/cardkey/order/detail/:orderNo',
    meta: {
      auth: ['admin-cardkey-order-list'],
      title: '订单详情',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}cardkeyOrderDetail`,
        component: pageContainer,
        props: { typeKey: 'cardkey', section: 'cardkey', action: 'order_detail' },
        meta: {
          auth: ['admin-cardkey-order-list'],
          title: '订单详情',
          activeMenu: routePre + '/product/cardkey/order/list',
        },
      },
    ],
  },
];
