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

const meta = {
  auth: true,
};

const pre = 'user_';

export default {
  path: routePre + '/user',
  name: 'user',
  header: 'user',
  redirect: {
    name: `${pre}list`,
  },
  meta,
  component: LayoutMain,
  children: [
    {
      path: 'list',
      name: `${pre}list`,
      meta: {
        auth: ['admin-user-user-index'],
        title: '用户管理',
      },
      component: () => import('@/pages/user/list/index'),
    },
    {
      path: 'level',
      name: `${pre}level`,
      meta: {
        auth: ['user-user-level'],
        footer: true,
        title: '用户等级',
      },
      component: () => import('@/pages/user/level/index'),
    },
    {
      path: 'group',
      name: `${pre}group`,
      meta: {
        auth: ['user-user-group'],
        footer: true,
        title: '用户分组',
      },
      component: () => import('@/pages/user/group/index'),
    },
    {
      path: 'label',
      name: `${pre}label`,
      meta: {
        auth: ['user-user-label'],
        footer: true,
        title: '用户标签',
      },
      component: () => import('@/pages/user/label/index'),
    },
    {
      path: 'cancel',
      name: `${pre}cancel`,
      meta: {
        auth: ['user-user-cancel'],
        footer: true,
        title: '用户注销',
      },
      component: () => import('@/pages/user/cancel/index'),
    },
    {
      path: 'recharge/:id',
      name: `${pre}recharge`,
      meta: {
        auth: ['user-user-recharge'],
        footer: true,
        title: '充值配置',
      },
      component: () => import('@/pages/system/group/list'),
    },
    {
      path: 'member/type',
      name: `${pre}memberType`,
      meta: {
        auth: ['admin-user-member-type'],
        title: '会员类型',
      },
      component: () => import('@/pages/user/member/pages/type/index'),
    },
    {
      path: 'member/card',
      name: `${pre}memberCard`,
      meta: {
        auth: ['admin-user-grade-card'],
        title: '卡密会员',
      },
      component: () => import('@/pages/user/member/pages/card/index'),
    },
    {
      path: 'member/record',
      name: `${pre}memberRecord`,
      meta: {
        auth: ['admin-user-grade-record'],
        title: '会员记录',
      },
      component: () => import('@/pages/user/member/pages/record/index'),
    },
    {
      path: 'member/right',
      name: `${pre}memberRight`,
      meta: {
        auth: ['admin-user-grade-right'],
        title: '会员权益',
      },
      component: () => import('@/pages/user/member/pages/right/index'),
    },
    {
      path: 'member/agreement',
      name: `${pre}memberAgreement`,
      meta: {
        auth: ['admin-user-grade-agreement'],
        title: '会员协议',
      },
      component: () => import('@/pages/user/member/pages/agreement/index'),
    },
  ],
};
