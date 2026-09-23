// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import index from './modules/index';
import product from './modules/product';
import order from './modules/order';
import user from './modules/user';
// import echarts from './modules/echarts'
import setting from './modules/setting';
import driver from './modules/driver';
import plugin from './modules/plugin';
import schemaPage from './modules/schema';
import distribution from './modules/distribution';
import finance from './modules/finance';
import cms from './modules/cms';
import marketing from './modules/marketing';
import app from './modules/app';
import system from './modules/system';
import LayoutMain from '@/layout';
import statistic from './modules/statistic';
import frameOut from './modules/frameOut';
import settings from '@/setting';
import crud from './modules/crud';

// 扫描 crud 增量路由（替代 require.context）
const crudModules = import.meta.glob('./modules/crud/**/*.js', { eager: true });
const routers = [];
// 将扫描到的路由信息加入路由数组
Object.keys(crudModules).forEach((modulePath) => {
  const value = crudModules[modulePath];
  routers.push(value.default);
});

// 加载各插件路由配置（扫描 pages/plugin/**/routes.js，仅含 component 的才是路由）
// 注意：新增插件 routes.js 后需重启 dev server 让 import.meta.glob 重新扫描
const pluginRouterModules = import.meta.glob('../pages/plugin/**/routes.js', { eager: true });
const pluginRoutes = [];
Object.keys(pluginRouterModules).forEach((modulePath) => {
  const value = pluginRouterModules[modulePath];
  const routes = value.default || value;
  if (Array.isArray(routes)) {
    routes.forEach((route) => {
      if (route.component) pluginRoutes.push(route);
    });
  } else if (routes && routes.component) {
    pluginRoutes.push(routes);
  }
});

let routePre = settings.routePre;
/**
 * 在主框架内显示
 */

const frameIn = [
  {
    path: '/',
    meta: {
      title: 'CRMEB',
    },
    redirect: {
      name: 'home_index',
    },
    component: LayoutMain,
    children: [
      {
        path: routePre + '/system/user',
        name: `systemUser`,
        meta: {
          auth: true,
          title: '个人中心',
        },
        component: () => import('@/pages/setting/user/index'),
      },
      {
        path: routePre + '/system/files',
        name: `systemFiles`,
        meta: {
          auth: ['admin-setting-files'],
          title: '文件管理',
        },
        component: () => import('@/pages/setting/userFile/index'),
      },
      // 刷新页面 必须保留
      {
        path: 'refresh',
        name: 'refresh',
        hidden: true,
        component: {
          beforeRouteEnter(to, from, next) {
            next((instance) => instance.$router.replace(from.fullPath));
          },
          render: () => null,
        },
      },
      // 页面重定向 必须保留
      {
        path: 'redirect/:route*',
        name: 'redirect',
        hidden: true,
        component: {
          beforeRouteEnter(to, from, next) {
            next((instance) => instance.$router.replace(JSON.parse(from.params.route)));
          },
          render: () => null,
        },
      },
    ],
  },
  {
    path: routePre,
    meta: {
      title: 'CRMEB',
    },
    redirect: {
      name: 'home_index',
    },
    component: LayoutMain,
  },
  {
    path: routePre + '/widget.images/index.html',
    name: `images`,
    meta: {
      auth: ['admin-user-user-index'],
      title: '上传图片',
    },
    component: () => import('@/components/uploadPictures/widgetImg'),
  },
  {
    path: routePre + '/widget.widgets/icon.html',
    name: `imagesIcon`,
    meta: {
      auth: ['admin-user-user-index'],
      title: '上传图标',
    },
    component: () => import('@/components/iconFrom/index'),
  },
  {
    path: routePre + '/store.StoreProduct/index.html',
    name: `storeProduct`,
    meta: {
      title: '选择商品',
    },
    component: () => import('@/components/goodsList/index'),
  },
  {
    path: routePre + '/system.User/list.html',
    name: `changeUser`,
    meta: {
      title: '选择用户',
    },
    component: () => import('@/components/customerInfo/index'),
  },
  {
    path: routePre + '/marketing.Coupon/list.html',
    name: `couponSelect`,
    meta: {
      title: '选择优惠券',
    },
    component: () => import('@/components/couponSelect/index'),
  },
  {
    path: routePre + '/widget.video/index.html',
    name: `video`,
    meta: {
      title: '上传视频',
    },
    component: () => import('@/components/uploadVideo/index'),
  },
  index,
  distribution,
  cms,
  product,
  ...pluginRoutes,
  marketing,
  order,
  user,
  finance,
  setting,
  driver,
  plugin,
  schemaPage,
  system,
  app,
  statistic,
  ...routers,
  crud,
];

/**
 * 在主框架之外显示
 */

const frameOuts = frameOut;

/**
 * 错误页面
 */

const errorPage = [
  {
    path: routePre + '/403',
    name: '403',
    meta: {
      title: '403',
    },
    component: () => import('@/pages/system/error/403'),
  },
  {
    path: routePre + '/500',
    name: '500',
    meta: {
      title: '500',
    },
    component: () => import('@/pages/system/error/500'),
  },
  {
    path: routePre + '/:pathMatch(.*)*',
    name: '404',
    meta: {
      title: '404',
    },
    component: () => import('@/pages/system/error/404'),
  },
];

// 导出需要显示菜单的
export const frameInRoutes = frameIn;

// 重新组织后导出
export default [...frameIn, ...frameOuts, ...errorPage];
