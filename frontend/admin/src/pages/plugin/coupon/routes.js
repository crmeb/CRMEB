/**
 * 优惠券商品路由
 */
import setting from '@/setting';
const routePre = setting.routePre;
const pre = 'product_';

export default [
  {
    path: routePre + '/product/coupon/list',
    name: `${pre}couponProductList`,
    meta: {
      auth: ['admin-coupon-product-list'],
      title: '优惠券商品',
    },
    component: () => import('@/pages/plugin/coupon/index'),
  },
  {
    path: routePre + '/product/coupon/add/:id?',
    name: `${pre}couponProductAdd`,
    meta: {
      auth: ['admin-coupon-product-add'],
      title: '优惠券商品添加',
      activeMenu: routePre + '/product/coupon/list',
    },
    component: () => import('@/pages/plugin/coupon/add'),
  },
];
