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

const pre = 'crud_';

export default {
  path: routePre + '/crud',
  name: 'crud',
  header: 'crud',
  redirect: {
    name: `${pre}crud`,
  },
  meta: {
    auth: true,
  },
  component: LayoutMain,
  children: [
    {
      path: ':table_name',
      name: `${pre}crud`,
      meta: {
        auth: true,
        title: '增删改查',
      },
      component: () => import('@/pages/crud/index'),
    },
  ],
};
