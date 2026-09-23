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

const pre = 'app_';

export default {
  path: routePre + '/app',
  name: 'app',
  header: 'app',
  redirect: {
    name: `${pre}version`,
  },
  meta: {
    auth: ['admin-app'],
  },
  component: LayoutMain,
  children: [
    {
      path: 'app/version',
      name: `${pre}version`,
      meta: {
        auth: ['admin-app-version'],
        title: 'APP版本管理',
      },
      component: () => import('@/pages/app/version/index'),
    },
    {
      path: 'app/agreement',
      name: `${pre}agreement `,
      meta: {
        auth: ['admin-app-agreement'],
        title: '隐私协议',
      },
      component: () => import('@/pages/app/app/index'),
    },
  ],
};
