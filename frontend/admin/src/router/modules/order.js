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

const pre = 'order_';

export default {
  path: routePre + '/order',
  name: 'order',
  header: 'order',
  redirect: {
    name: `${pre}center`,
  },
  component: LayoutMain,
  children: [
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
      path: 'invoice/list',
      name: `${pre}invoice`,
      meta: {
        auth: ['admin-order-startOrderInvoice-index'],
        title: '发票管理',
      },
      component: () => import('@/pages/order/invoice/index'),
    },
    {
      path: 'center',
      name: `${pre}center`,
      meta: {
        auth: ['admin-order-orderCenter-index'],
        title: '订单中台',
      },
      component: () => import('@/pages/order/orderCenter/index'),
    },
    {
      path: 'center/detail/:id',
      name: `${pre}centerDetail`,
      meta: {
        auth: ['admin-order-orderCenter-detail'],
        title: '订单中台详情',
        noCache: true,
      },
      component: () => import('@/pages/order/orderCenter/detail'),
    },
    {
      path: 'afterSale',
      name: `${pre}afterSale`,
      meta: {
        auth: ['admin-order-afterSale-index'],
        title: '售后订单',
      },
      component: () => import('@/pages/order/afterSale/index'),
    },
    {
      path: 'afterSale/detail/:id',
      name: `${pre}afterSaleDetail`,
      meta: {
        auth: ['admin-order-afterSale-detail'],
        title: '售后单详情',
        noCache: true,
        activeMenu: routePre + '/order/afterSale',
      },
      component: () => import('@/pages/order/afterSale/detail'),
    },
    {
      path: 'member/offline',
      name: `${pre}memberOffline`,
      meta: {
        auth: ['order-member-offline'],
        title: '线下收银',
      },
      component: () => import('@/pages/user/member/pages/offline/index'),
    },
  ],
};
