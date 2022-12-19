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

const pre = 'division_';
const meta = {
  auth: true,
};
export default {
  path: '/admin/division',
  name: 'division',
  header: 'division',
  redirect: {
    name: `${pre}division`,
  },
  meta,
  component: BasicLayout,
  children: [
    {
      path: 'index',
      name: `${pre}division`,
      meta: {
        auth: ['agent-division-index'],
        title: '事业部列表',
      },
      component: () => import('@/pages/division/list/index'),
    },
    {
      path: 'agent/index',
      name: `${pre}agent`,
      meta: {
        auth: ['agent-division-agent-index'],
        title: '代理商列表',
      },
      component: () => import('@/pages/division/agent/index'),
    },
    {
      path: 'agent/applyList',
      name: `${pre}agent`,
      meta: {
        auth: ['agent-division-agent-applyList'],
        title: '代理商申请',
      },
      component: () => import('@/pages/division/agent/applyList'),
    },
    {
      path: 'agent/agreement',
      name: `${pre}agent`,
      meta: {
        auth: ['agent-division-agent-agreement'],
        title: '代理商规则',
      },
      component: () => import('@/pages/division/agent/agreement'),
    },
  ],
};
