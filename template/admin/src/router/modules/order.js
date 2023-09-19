// +---------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +---------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +---------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +---------------------------------------------------------------------

import LayoutMain from '@/layout';
import setting from '@/setting';
let routePre = setting.routePre;

const pre = 'order_';

export default {
  path: routePre + '/order',
  name: 'order',
  header: 'order',
  redirect: {
    name: `${pre}list`,
  },
  component: LayoutMain,
  children: [
    {
      path: 'list',
      name: `${pre}list`,
      meta: {
        auth: ['admin-order-storeOrder-index'],
        title: '订单管理',
      },
      component: () => import('@/pages/order/orderList/index'),
    },
    {
      path: 'offline',
      name: `${pre}offline`,
      meta: {
        auth: ['admin-order-offline'],
        title: '收银订单',
      },
      component: () => import('@/pages/order/offline/index'),
    },
    {
      path: 'refund',
      name: `${pre}refund`,
      meta: {
        auth: ['admin-order-refund'],
        title: '售后订单',
      },
      component: () => import('@/pages/order/refund/index'),
    },
    {
      path: 'invoice/list',
      name: `${pre}invoice`,
      meta: {
        auth: ['admin-order-startOrderInvoice-index'],
        title: '发票管理',
      },
      component: () => import('@/pages/order/invoice/index'),
    },
  ],
};
