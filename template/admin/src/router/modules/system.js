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

const pre = 'system_';

export default {
  path: '/admin/system',
  name: 'system',
  header: 'system',
  redirect: {
    name: `${pre}configTab`,
  },
  meta: {
    auth: ['admin-system'],
  },
  component: BasicLayout,
  children: [
    {
      path: 'file',
      name: `${pre}file`,
      meta: {
        auth: ['system-file'],
        title: '附件管理',
      },
      component: () => import('@/pages/system/file/index'),
    },
    {
      path: 'maintain/clear/index',
      name: `${pre}clear`,
      meta: {
        auth: ['system-clear'],
        title: '刷新缓存',
      },
      component: () => import('@/pages/system/clear/index'),
    },
    {
      path: 'maintain/system_log/index',
      name: `${pre}systemLog`,
      meta: {
        auth: ['system-maintain-system-log'],
        title: '系统日志',
      },
      component: () => import('@/pages/system/maintain/systemLog/index'),
    },
    {
      path: 'maintain/system_file/index',
      name: `${pre}systemFile`,
      meta: {
        auth: ['system-maintain-system-file'],
        title: '文件校验',
      },
      component: () => import('@/pages/system/maintain/systemFile/index'),
    },
    {
      path: 'maintain/system_cleardata/index',
      name: `${pre}systemCleardata`,
      meta: {
        auth: ['system-maintain-system-cleardata'],
        title: '清除数据',
      },
      component: () => import('@/pages/system/maintain/systemCleardata/index'),
    },
    {
      path: 'maintain/system_databackup/index',
      name: `${pre}systemDatabackup`,
      meta: {
        auth: ['system-maintain-system-databackup'],
        title: '数据备份',
      },
      component: () => import('@/pages/system/maintain/systemDatabackup/index'),
    },
    {
      path: 'maintain/system_file/opendir',
      name: `${pre}opendir`,
      meta: {
        auth: ['system-maintain-system-file'],
        title: '文件管理',
      },
      component: () => import('@/pages/system/maintain/systemFile/opendir'),
    },
    {
      path: 'maintain/system_file/login',
      name: `${pre}opendir_login`,
      meta: {
        auth: ['system-maintain-system-file'],
        title: '文件管理入口',
      },
      component: () => import('@/pages/system/maintain/systemFile/login'),
    },
    {
      path: 'config/system_config_tab/index',
      name: `${pre}configTab`,
      meta: {
        auth: ['system-config-system_config-tab'],
        title: '配置分类',
      },
      component: () => import('@/pages/system/configTab/index'),
    },
    {
      path: 'config/system_config_tab/list/:id?',
      name: `${pre}configTabList`,
      meta: {
        auth: ['system-config-system_config_tab-list'],
        title: '配置列表',
      },
      component: () => import('@/pages/system/configTab/list'),
    },
    {
      path: 'config/system_group/index',
      name: `${pre}group`,
      meta: {
        auth: ['system-config-system_config-group'],
        title: '组合数据',
      },
      component: () => import('@/pages/system/group/index'),
    },
    {
      path: 'config/system_group/list/:id?',
      name: `${pre}groupList`,
      meta: {
        auth: ['system-config-system_config-list'],
        title: '组合数据列表',
      },
      component: () => import('@/pages/system/group/list'),
    },
    {
      path: 'maintain/auth',
      name: `${pre}auth`,
      meta: {
        auth: ['system-maintain-auth'],
        title: '商业授权',
      },
      component: () => import('@/pages/system/auth/index'),
    },
    {
      path: 'onlineUpgrade/index',
      name: `${pre}upgradeclient`,
      meta: {
        auth: ['system-onlineUpgrade-index'],
        title: '在线升级',
      },
      component: () => import('@/pages/system/onlineUpgrade/index'),
    },
    {
      path: 'crontab',
      name: `${pre}crontab`,
      meta: {
        auth: ['system-crontab-index'],
        title: '定时任务',
      },
      component: () => import('@/pages/system/crontab/index'),
    },
  ],
};
