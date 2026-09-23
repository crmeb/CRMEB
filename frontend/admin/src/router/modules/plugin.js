// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import LayoutMain from '@/layout';
import setting from '@/setting';
let routePre = setting.routePre;

const meta = {
  auth: true,
};

const pre = 'plugin_';

export default {
  path: routePre + '/plugin',
  name: 'plugin',
  header: 'plugin',
  redirect: {
    name: `${pre}list`,
  },
  component: LayoutMain,
  children: [
    {
      path: 'list',
      name: `${pre}list`,
      meta: {
        auth: ['plugin-list'],
        title: '插件管理',
      },
      component: () => import('@/pages/system/plugin/index'),
    },
  ],
};
