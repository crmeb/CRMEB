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

const pre = 'echarts_';

export default {
  path: '/admin/echarts',
  name: 'echarts',
  header: 'echarts',
  redirect: {
    name: `${pre}/trade/order`,
  },
  component: BasicLayout,
  children: [
    {
      path: 'trade/order',
      name: `${pre}/trade/order`,
      meta: {
        auth: ['admin-order-storeOrder-index'],
        title: '交易统计',
      },
      component: () => import('@/pages/echarts/trade/order'),
    },
    {
      path: 'trade/product',
      name: `${pre}/trade/product`,
      meta: {
        auth: ['admin-order-storeOrder-index'],
        title: '商品统计',
      },
      component: () => import('@/pages/echarts/trade/product'),
    },
  ],
};
