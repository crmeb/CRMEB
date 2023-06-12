/*
 * @Author: From-wh from-wh@hotmail.com
 * @Date: 2023-02-21 09:14:27
 * @FilePath: /admin/src/router/modules/echarts.js
 * @Description:
 */
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

const pre = 'echarts_';

export default {
  path: routePre + '/echarts',
  name: 'echarts',
  header: 'echarts',
  redirect: {
    name: `${pre}/trade/order`,
  },
  component: LayoutMain,
  children: [
    // {
    //   path: 'trade/order',
    //   name: `${pre}/trade/order`,
    //   meta: {
    //     auth: ['admin-order-storeOrder-index'],
    //     title: '交易统计',
    //   },
    //   component: () => import('@/pages/echarts/trade/order'),
    // },
    // {
    //   path: 'trade/product',
    //   name: `${pre}/trade/product`,
    //   meta: {
    //     auth: ['admin-order-storeOrder-index'],
    //     title: '商品统计',
    //   },
    //   component: () => import('@/pages/echarts/trade/product'),
    // },
  ],
};
