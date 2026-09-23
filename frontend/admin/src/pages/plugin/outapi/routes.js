/**
 * 对外接口(outapi)插件前端路由
 *
 * 后端菜单（Installer.php 认领系统预置菜单）：
 *   维护(25) > 对外接口(menu_path='/setting/other_out_config')
 *   维护(25) > 对外接口 > 账号管理(menu_path='/setting/system_out_account/index')
 *
 * 前端路由（path 必须与菜单 menu_path 拼接 routePre 后一致）：
 *   {routePre}/setting/system_out_account/index   → 账号列表
 *   {routePre}/setting/system_out_interface/index → 接口文档
 */
import setting from '@/setting';
const routePre = setting.routePre;
const pre = 'setting_';

export default [
  {
    path: routePre + '/setting/system_out_account/index',
    meta: {
      auth: ['setting-system-out-account-index'],
      title: '账号列表',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}systemOutAccount`,
        component: () => import('./index'),
        meta: {
          auth: ['setting-system-out-account-index'],
          title: '账号列表',
        },
      },
    ],
  },
  {
    path: routePre + '/setting/system_out_interface/index',
    meta: {
      auth: ['setting-system-out-interface-index'],
      title: '接口文档',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}systemInterface`,
        component: () => import('./interface/index'),
        meta: {
          auth: ['setting-system-out-interface-index'],
          title: '接口文档',
        },
      },
    ],
  },
];
