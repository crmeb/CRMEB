// +---------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +---------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +---------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +---------------------------------------------------------------------

import LayoutMain from '@/layout';
import setting from '@/setting';
let routePre = setting.routePre;

const pre = 'product_';

export default {
  path: routePre + '/product',
  name: 'product',
  header: 'product',
  meta: {
    title: '商品',
    // 授权标识
    auth: ['admin-store-index'],
  },
  redirect: {
    name: `${pre}normalProductList`,
  },
  component: LayoutMain,
  children: [
    {
      // 兼容旧版（v4/v5）路径：商品列表已重构至 normal 插件
      path: 'product_list',
      redirect: routePre + '/product/normal/list',
    },
    {
      path: 'product_classify',
      name: `${pre}productClassify`,
      meta: {
        title: '商品分类',
        auth: ['admin-store-storeCategory-index'],
      },
      component: () => import('@/pages/product/productClassify'),
    },
    {
      path: 'product_reply/:id?',
      name: `${pre}productEvaluate`,
      meta: {
        auth: ['admin-store-storeProuduct-index'],
        title: '商品评论',
      },
      component: () => import('@/pages/product/productReply'),
    },
    {
      path: 'product_attr',
      name: `${pre}productAttr`,
      meta: {
        auth: ['admin-store-storeProuduct-index'],
        title: '商品规格',
      },
      component: () => import('@/pages/product/productAttr'),
    },
    {
      path: 'param/list',
      name: `${pre}paramList`,
      meta: {
        auth: ['admin-product-param-list'],
        title: '商品参数',
      },
      component: () => import('@/pages/product/paramList'),
    },
    {
      path: 'label/list',
      name: `${pre}labelList`,
      meta: {
        auth: ['admin-product-label-list'],
        title: '商品标签',
      },
      component: () => import('@/pages/product/labelList'),
    },
    {
      path: 'protection/list',
      name: `${pre}protectionList`,
      meta: {
        auth: ['admin-product-protection-list'],
        title: '商品保障',
      },
      component: () => import('@/pages/product/protectionList'),
    },
    {
      path: 'center/list',
      name: `${pre}centerProductList`,
      meta: {
        auth: ['admin-center-product-index'],
        title: '中台商品管理',
      },
      component: () => import('@/pages/center/product/list'),
    },
  ],
};
