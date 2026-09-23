/**
 * 渠道码扩展路由
 * 兼容模式：既是 Vue Router 路由配置，也包含 section/action 元数据供扩展机制使用
 */
import setting from '@/setting';
const routePre = setting.routePre;
const pre = 'channel_code_';

export default [
  {
    path: routePre + '/marketing/channel_code/channelCodeIndex',
    section: 'channel_code',
    action: 'channelCodeIndex',
    meta: {
      auth: true,
      title: '公众号渠道码',
      keepAlive: true,
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}channelCodeIndex`,
        component: () => import('./pages/channelCodeIndex'),
        meta: {
          auth: true,
          title: '公众号渠道码',
          keepAlive: true,
        },
      },
    ],
  },
  {
    path: routePre + '/marketing/channel_code/create',
    section: 'channel_code',
    action: 'createCode',
    meta: {
      auth: ['marketing-channel_code-create'],
      title: '渠道码',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}createCode`,
        component: () => import('./pages/createCode'),
        meta: {
          auth: ['marketing-channel_code-create'],
          title: '渠道码',
          activeMenu: routePre + '/marketing/channel_code/channelCodeIndex',
        },
      },
    ],
  },
  {
    path: routePre + '/marketing/channel_code/code_statistic',
    section: 'channel_code',
    action: 'codeStatistic',
    meta: {
      auth: ['marketing-channel_code-statistic'],
      title: '二维码统计',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}codeStatistic`,
        component: () => import('./pages/codeStatistic'),
        meta: {
          auth: ['marketing-channel_code-statistic'],
          title: '二维码统计',
          activeMenu: routePre + '/marketing/channel_code/channelCodeIndex',
        },
      },
    ],
  },
];
