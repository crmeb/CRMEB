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

const pre = 'distribution_';
const meta = {
  auth: true,
};
export default {
  path: `${routePre}/distribution`,
  name: 'distribution',
  header: 'distribution',
  redirect: {
    name: `${pre}agentManage`,
  },
  meta,
  component: LayoutMain,
  children: [
    {
      path: 'agent_manage/index',
      name: `${pre}agentManage`,
      meta: {
        auth: ['agent-agent-manage'],
        title: '分销员管理',
      },
      component: () => import('@/pages/system/plugin/PageContainer'),
      props: { typeKey: 'distribution', section: 'distribution', action: 'manage' },
    },
    {
      path: 'spread/apply',
      name: `${pre}spreadApply`,
      meta: {
        auth: ['admin-agent-spread-apply'],
        title: '分销员申请',
      },
      component: () => import('@/pages/system/plugin/PageContainer'),
      props: { typeKey: 'distribution', section: 'distribution', action: 'apply' },
    },
  ],
};
