// +---------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +---------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +---------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +---------------------------------------------------------------------

import setting from '@/setting';
let routePre = setting.routePre;

export default [
  // 登录
  {
    path: routePre + '/login',
    name: 'login',
    meta: {
      title: '登录',
      hideInMenu: true,
    },
    component: () => import('@/pages/account/login'),
  },
  {
    path: '/app/upload',
    name: `mobile_upload`,
    meta: {
      auth: true,
      title: '手机端扫码上传',
      kefu: true,
    },
    component: () => import('@/pages/app/upload'),
  },
];
