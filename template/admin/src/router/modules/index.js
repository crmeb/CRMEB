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

const meta = {
  auth: true,
};

const pre = 'home_';

export default {
  path: routePre + '/home',
  name: 'home',
  header: 'home',
  redirect: {
    name: `${pre}index`,
  },
  meta,
  component: LayoutMain,
  children: [
    {
      path: routePre + '/index',
      name: `${pre}index`,
      header: 'home',
      meta: {
        auth: ['admin-index-index'],
        title: '主页',
        isAffix: false,
      },
      component: () => import('@/pages/index/index'),
    },
  ],
};
