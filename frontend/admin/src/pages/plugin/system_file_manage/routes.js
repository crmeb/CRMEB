/**
 * 文件管理扩展路由
 */
import setting from '@/setting';
const routePre = setting.routePre;
const pre = 'system_file_';

export default [
  // 文件校验
  {
    path: routePre + '/system/maintain/system_file/index',
    section: 'system',
    action: 'system_file',
    meta: {
      auth: ['system-maintain-system-file'],
      title: '文件校验',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}systemFile`,
        component: () => import('./index.vue'),
        meta: {
          auth: ['system-maintain-system-file'],
          title: '文件校验',
        },
      },
    ],
  },
  // 文件管理
  {
    path: routePre + '/system/maintain/system_file/opendir',
    section: 'system',
    action: 'system_file',
    meta: {
      auth: ['system-maintain-system-file'],
      title: '文件管理',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}opendir`,
        component: () => import('./opendir.vue'),
        meta: {
          auth: ['system-maintain-system-file'],
          title: '文件管理',
        },
      },
    ],
  },
  // 文件管理入口（登录）
  {
    path: routePre + '/system/maintain/system_file/login',
    section: 'system',
    action: 'system_file',
    meta: {
      auth: ['system-maintain-system-file'],
      title: '文件管理入口',
    },
    component: () => import('@/layout'),
    children: [
      {
        path: '',
        name: `${pre}opendir_login`,
        component: () => import('./login.vue'),
        meta: {
          auth: ['system-maintain-system-file'],
          title: '文件管理入口',
          activeMenu: routePre + '/system/maintain/system_file/opendir',
        },
      },
    ],
  },
];
