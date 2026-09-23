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

const pre = 'driver_';

export default {
  path: routePre + '/driver',
  name: 'driver',
  header: 'driver',
  redirect: {
    name: `${pre}pay`,
  },
  component: LayoutMain,
  children: [
    {
      path: 'storage',
      name: `${pre}storage`,
      meta: {
        auth: ['driver-upload'],
        activeMenu: routePre + '/driver/upload',
        title: '储存配置',
      },
      component: () => import('@/pages/driver/storage'),
    },
    {
      path: 'pay',
      name: `${pre}pay`,
      meta: {
        auth: ['driver:pay:channel'],
        title: '支付管理',
      },
      component: () => import('@/pages/driver/pay/index'),
    },
    {
      path: 'invoiceDriver/index',
      name: `${pre}invoiceDriver`,
      meta: {
        auth: ['driver-invoice'],
        title: '发票驱动管理',
      },
      component: () => import('@/pages/driver/invoiceDriver/index'),
    },
    {
      path: 'upload',
      name: `${pre}upload`,
      meta: {
        auth: ['driver-upload'],
        title: '存储扩展管理',
      },
      component: () => import('@/pages/driver/uploadDriver/index'),
    },
    {
      path: 'copyDriver/index',
      name: `${pre}copy`,
      meta: {
        auth: ['driver-copydriver'],
        title: '采集驱动管理',
      },
      component: () => import('@/pages/driver/copyDriver/index'),
    },
    {
      path: 'logisticsDriver/index',
      name: `${pre}logistics`,
      meta: {
        auth: ['driver-logistics'],
        title: '物流查询驱动管理',
      },
      component: () => import('@/pages/driver/logisticsDriver/index'),
    },
    {
      path: 'smsDriver/index',
      name: `${pre}smsDriver`,
      meta: {
        auth: ['driver-sms-driver'],
        title: '短信驱动管理',
      },
      component: () => import('@/pages/driver/smsDriver/index'),
    },
    {
      path: 'botDriver/index',
      name: `${pre}botDriver`,
      meta: {
        auth: ['driver-bot'],
        title: '机器人通知驱动管理',
      },
      component: () => import('@/pages/driver/bot/index'),
    },
    {
      path: 'emailDriver/index',
      name: `${pre}emailDriver`,
      meta: {
        auth: ['driver-email'],
        title: '邮件驱动管理',
      },
      component: () => import('@/pages/driver/emailDriver/index'),
    },
    {
      path: 'expressDriver/index',
      name: `${pre}express`,
      meta: {
        auth: ['driver-express'],
        title: '电子面单驱动管理',
      },
      component: () => import('@/pages/driver/expressDriver/index'),
    },
    {
      path: 'printerDriver/index',
      name: `${pre}printerDriver`,
      meta: {
        auth: ['driver-printer'],
        title: '打印机驱动管理',
      },
      component: () => import('@/pages/driver/printerDriver/index'),
    },
    {
      path: 'printer',
      redirect: { name: 'setting_printer' },
    },
    {
      path: 'printer/content',
      redirect: (to) => ({ name: 'setting_printerContent', query: to.query }),
    },
  ],
};
