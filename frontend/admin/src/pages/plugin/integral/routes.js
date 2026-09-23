/**
 * 积分商城扩展路由
 *
 * 说明：
 * - 仅保留插件特有的路由：积分商品、积分订单
 * - 积分记录、积分统计、积分配置为系统内置路由，已迁移至 router/modules/marketing.js
 * - 旧路由（store_integral/*、user_point/*）已移除
 */
import setting from '@/setting';
const routePre = setting.routePre;
const pre = 'integral_';

export default [
  {
    path: routePre + '/marketing/integral/product',
    section: 'integral',
    action: 'product',
    meta: {
      auth: ['marketing-integral-product'],
      title: '积分商品',
      keepAlive: true,
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}product`,
        name: 'integralProductList',
        component: () => import('./pages/product/index.vue'),
        meta: {
          auth: ['marketing-integral-product'],
          title: '积分商品',
          keepAlive: true,
        },
      },
    ],
  },
  {
    path: routePre + '/marketing/integral/product/create/:id?/:copy?',
    section: 'integral',
    action: 'product_create',
    meta: {
      auth: ['marketing-integral-save'],
      title: '添加/编辑积分商品',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}productCreate`,
        component: () => import('./pages/product/create.vue'),
        meta: {
          auth: ['marketing-integral-save'],
          title: '添加/编辑积分商品',
          activeMenu: routePre + '/marketing/integral/product',
        },
      },
    ],
  },
  {
    path: routePre + '/marketing/integral/order',
    section: 'integral',
    action: 'order',
    meta: {
      auth: ['marketing-integral-order'],
      title: '积分订单',
      keepAlive: true,
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}order`,
        name: 'integralOrderList',
        component: () => import('./pages/order/index.vue'),
        meta: {
          auth: ['marketing-integral-order'],
          title: '积分订单',
          keepAlive: true,
        },
      },
    ],
  },
  // 积分记录、积分统计、积分配置为系统内置路由，已移至 router/modules/marketing.js
];
