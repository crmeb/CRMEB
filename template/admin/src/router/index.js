// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import Vue from 'vue';
import Router from 'vue-router';
import routes from './routers';
import Setting from '@/setting';
import store from '@/store';
import { removeCookies, getCookies, setTitle } from '@/libs/util';
import { includeArray } from '@/libs/auth';
import { PrevLoading } from '@/utils/loading.js';

Vue.use(Router);
// 解决 `element ui` 导航栏重复点菜单报错问题
const originalPush = Router.prototype.push;
Router.prototype.push = function push(location) {
  return originalPush.call(this, location).catch((err) => err);
};

const router = new Router({
  routes,
  mode: Setting.routerMode,
});

// 判断路由 meta.roles 中是否包含当前登录用户权限字段
export function hasAuth(roles, route) {
  if (route.meta && route.meta.auth) return roles.some((role) => route.meta.auth.includes(role));
  else return true;
}

// 递归过滤有权限的路由
export function setFilterMenuFun(routes, role) {
  const menu = [];
  routes.forEach((route) => {
    const item = { ...route };
    if (hasAuth(role, item)) {
      if (item.children) item.children = setFilterMenuFun(item.children, role);
      menu.push(item);
    }
  });
  return menu;
}

// 递归处理多余的 layout : <router-view>，让需要访问的组件保持在第一层 layout 层。
// 因为 `keep-alive` 只能缓存二级路由
// 默认初始化时就执行
export function keepAliveSplice(to) {
  if (to.matched && to.matched.length > 2) {
    to.matched.map((v, k) => {
      if (v.components.default instanceof Function) {
        v.components.default().then((components) => {
          if (components.default.name === 'parent') {
            to.matched.splice(k, 1);
            router.push({ path: to.path, query: to.query });
            keepAliveSplice(to);
          }
        });
      } else {
        if (v.components.default.name === 'parent') {
          to.matched.splice(k, 1);
          keepAliveSplice(to);
        }
      }
    });
  }
}

// 编辑模块
export function editRouterFun(to, from) {
  const onRoutes = to.meta.activeMenu ? to.meta.activeMenu : to.meta.path;
  store.commit('menu/setActivePath', onRoutes);
  if (to.name == 'crud_crud') {
    store.state.menus.oneLvRoutes.map((e) => {
      if (e.path === to.path) {
        to.meta.title = e.title;
      }
    });
  }
  if (
    [
      'product_productAdd',
      'marketing_bargainCreate',
      'marketing_storeSeckillCreate',
      'marketing_storeIntegralCreate',
      'marketing_storeCouponCreate',
    ].includes(to.name)
  ) {
    let route = to.matched[1].path.split(':')[0];
    store.state.menus.oneLvRoutes.map((e) => {
      if (route.indexOf(e.path) != -1) {
        to.meta.title = `${to.params.id ? e.title + 'ID: ' + to.params.id : '添加' + e.title}`;
      }
    });
  }
}

// 延迟关闭进度条
export function delayNProgressDone(time = 300) {
  setTimeout(() => {
    NProgress.done();
  }, time);
}

/**
 * 路由拦截
 * 权限验证
 */

router.beforeEach(async (to, from, next) => {
  // PrevLoading.start();
  keepAliveSplice(to);
  editRouterFun(to, from);
  if (to.fullPath.indexOf('kefu') != -1 || to.name == 'mobile_upload') {
    return next();
  }
  // 判断是否需要登录才可以进入
  if (to.matched.some((_) => _.meta.auth)) {
    // 这里依据 token 判断是否登录，可视情况修改
    const token = getCookies('token');
    if (token && token !== 'undefined') {
      const access = store.state.userInfo.uniqueAuth;
      const isPermission = includeArray(to.meta.auth, access); //  判断是否有权限  TODO
      if (access.length) {
        next();
      } else {
        if (access.length == 0) {
          next({
            name: 'login',
            query: {
              redirect: to.fullPath,
            },
          });
          localStorage.clear();
          removeCookies('token');
          removeCookies('expires_time');
          removeCookies('uuid');
        } else {
          next({
            name: '403',
          });
        }
      }
      // next();
    } else {
      // 没有登录的时候跳转到登录界面
      // 携带上登录成功之后需要跳转的页面完整路径
      next({
        name: 'login',
        query: {
          redirect: to.fullPath,
        },
      });
      localStorage.clear();
      removeCookies('token');
      removeCookies('expires_time');
      removeCookies('uuid');
    }
  } else {
    // 不需要身份校验 直接通过
    next();
  }
});
router.afterEach((to) => {
  // 更改标题
  setTitle(to, router.app);
  // 返回页面顶端
  window.scrollTo(0, 0);
  PrevLoading.done();
});
export default router;
