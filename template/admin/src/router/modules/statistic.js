// +---------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +---------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +---------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +---------------------------------------------------------------------

import BasicLayout from '@/components/main';

const meta = {
  auth: true,
};

const pre = 'statistic_';

export default {
  path: '/admin/statistic',
  name: 'statistic',
  header: 'statistic',
  redirect: {
    name: `${pre}product`,
  },
  component: BasicLayout,
  children: [
    {
      path: 'product',
      name: `${pre}product`,
      meta: {
        // auth: ['setting-system-role'],
        title: '商品统计',
      },
      component: () => import('@/pages/statistic/product/index'),
    },
    {
      path: 'user',
      name: `${pre}user`,
      meta: {
        // auth: ['setting-system-role'],
        title: '用户统计',
      },
      component: () => import('@/pages/statistic/user/index'),
    },
    {
      path: 'transaction',
      name: `${pre}transaction`,
      meta: {
        // auth: ['setting-system-role'],
        title: '交易统计',
      },
      component: () => import('@/pages/statistic/transaction/index'),
    },
    {
      path: 'integral',
      name: `${pre}integral`,
      meta: {
        // auth: ['setting-system-role'],
        title: '积分统计',
      },
      component: () => import('@/pages/statistic/integral/index'),
    },
    {
      path: 'order',
      name: `${pre}order`,
      meta: {
        // auth: ['setting-system-role'],
        title: '订单统计',
      },
      component: () => import('@/pages/statistic/order/index'),
    },
    {
      path: 'balance',
      name: `${pre}balance`,
      meta: {
        // auth: ['setting-system-role'],
        title: '余额统计',
      },
      component: () => import('@/pages/statistic/balance/index'),
    },
  ],
};
