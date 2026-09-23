import setting from '@/setting';
const routePre = setting.routePre;
const pre = 'lottery_';

/**
 * 抽奖活动插件路由配置
 *
 * 所有抽奖路由共用一个父级布局容器，子路由使用非空 path，
 * 避免面包屑组件因 path 重复而报 "Duplicate keys detected" 警告。
 * 带 section/action 的条目注册为 UMD 元数据（菜单指向）。
 */
export default [
  {
    path: routePre + '/marketing/lottery',
    component: () => import('@/layout'),
    children: [
      // 营销 > 抽奖管理 > 抽奖列表（菜单入口）
      {
        path: 'list',
        section: 'lottery',
        action: 'list',
        name: `${pre}list`,
        meta: { auth: ['admin-marketing-lottery-list'], title: '抽奖列表' },
        component: () => import('./pages/lotteryList'),
      },
      // 营销 > 抽奖管理 > 抽奖配置（菜单入口）
      {
        path: 'config',
        section: 'lottery',
        action: 'config',
        name: `${pre}config`,
        meta: { auth: ['admin-marketing-lottery-config'], title: '抽奖配置' },
        component: () => import('./pages/config'),
      },
      // 抽奖活动列表（内部导航）
      {
        path: 'index',
        name: `${pre}index`,
        meta: { auth: true, title: '抽奖列表' },
        component: () => import('./pages/index'),
      },
      // 创建/编辑抽奖（内部导航）
      {
        path: 'create',
        name: `${pre}create`,
        meta: { auth: true, title: '创建抽奖', activeMenu: routePre + '/marketing/lottery/list' },
        component: () => import('./pages/create'),
      },
      // 抽奖记录（内部导航）
      {
        path: 'recording_list',
        name: `${pre}recording_list`,
        meta: { auth: true, title: '抽奖记录', activeMenu: routePre + '/marketing/lottery/list' },
        component: () => import('./pages/recordingList'),
      },
    ],
  },
];
