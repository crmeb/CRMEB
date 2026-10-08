// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import { createRouter, createWebHistory } from 'vue-router';
import routes from './routers';
import Setting from '@/setting';
import { useRoutesListStore } from '@/store/module/routesList';
import { useMenuStore } from '@/store/module/menu';
import { useMenusStore } from '@/store/module/menus';
import { useUserInfoStore } from '@/store/module/userInfo';
import { removeCookies, getCookies, setTitle } from '@/libs/util';
import { includeArray } from '@/libs/auth';
import { PrevLoading } from '@/utils/loading.js';
import { i18n } from '@/i18n/index.js';

// 路由路径已含 /admin 前缀（routePre），故 base 为根路径
const router = createRouter({
  routes,
  history: createWebHistory(Setting.routerMode === 'hash' ? '/admin/' : '/'),
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

// 说明：原 Vue2 版本中的 keepAliveSplice（直接 splice to.matched 移除 name 为 parent 的
// 中间层路由）在 vue-router 4 下不可用（matched 只读）。keep-alive 缓存改为完全依赖
// layout 中 <keep-alive :include="keepAliveNames"> 按组件名匹配，中间层路由组件不再展开。

/**
 * 递归查找菜单树中匹配 path 的菜单标题
 */
function findMenuTitleByPath(path, menuList) {
  if (!menuList || !menuList.length) return null;
  for (const item of menuList) {
    if (item.path === path) return item.menu_name || item.title;
    if (item.children && item.children.length) {
      const found = findMenuTitleByPath(path, item.children);
      if (found) return found;
    }
  }
  return null;
}

/**
 * 从动态菜单树中查找与当前路由最佳匹配的菜单路径
 * 先精确匹配，再逐级截取父级路径匹配
 */
function findBestMenuPath(currentPath, menuList) {
  if (!menuList || !menuList.length) return null;
  const flatPaths = [];
  (function collect(list) {
    for (const item of list) {
      if (item.path) flatPaths.push(item.path);
      if (item.children && item.children.length) collect(item.children);
    }
  })(menuList);
  if (flatPaths.includes(currentPath)) return currentPath;
  const parts = currentPath.split('/').filter(Boolean);
  for (let i = parts.length - 1; i > 0; i--) {
    const parentPath = '/' + parts.slice(0, i).join('/');
    if (flatPaths.includes(parentPath)) return parentPath;
  }
  return null;
}

// 编辑模块
export function editRouterFun(to, from) {
  const menuStore = useMenuStore();
  const menusStore = useMenusStore();
  let onRoutes = to.meta.activeMenu || to.meta.path;
  // 未显式配置 activeMenu 时，从动态菜单树自动匹配
  if (!onRoutes) {
    onRoutes = findBestMenuPath(to.path, useRoutesListStore().routesList) || to.path;
  }
  menuStore.setActivePath(onRoutes);
  if (to.name == 'crud_crud') {
    menusStore.oneLvRoutes.map((e) => {
      if (e.path === to.path) {
        to.meta.title = e.title;
      }
    });
  }
  if (
    [
      'marketing_bargainCreate',
      'marketing_storeSeckillCreate',
      'marketing_storeIntegralCreate',
      'marketing_storeCouponCreate',
    ].includes(to.name)
  ) {
    // 创建态（无 id）保留路由定义的 meta.title；编辑态用最具体菜单名 + ID 便于多标签区分。
    // 空匹配保护：目录菜单 path 为空时 indexOf 恒成立，曾把标题污染成任意"添加+目录名"
    if (to.params.id) {
      // 用 activeMenu（列表页路径）匹配菜单树，比 create 路径能命中更具体的菜单名
      const route = (to.meta.activeMenu || to.matched[1].path.split(':')[0]).replace(/\/$/, '');
      let best = null;
      menusStore.oneLvRoutes.map((e) => {
        const p = (e.path || '').replace(/\/$/, '');
        if (!p || route.indexOf(p) === -1) return;
        if (!best || p.length > best.p.length) best = { p, title: e.title };
      });
      if (best) to.meta.title = `${best.title}ID: ${to.params.id}`;
    }
  }
  // 产品类型扩展路由：从系统菜单树查找菜单名称作为标签页标题
  if (to.name === 'product_extPage') {
    const menuTitle = findMenuTitleByPath(to.path, useRoutesListStore().routesList);
    if (menuTitle) {
      to.meta.title = menuTitle;
    }
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
  editRouterFun(to, from);
  if (to.fullPath.indexOf('kefu') != -1 || to.name == 'mobile_upload') {
    return next();
  }
  // 判断是否需要登录才可以进入
  if (to.matched.some((_) => _.meta.auth)) {
    // 这里依据 token 判断是否登录，可视情况修改
    const token = getCookies('token');
    if (token && token !== 'undefined') {
      const access = useUserInfoStore().uniqueAuth;
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
  // 更改标题（showTitle 仅在 config.useI18n 时使用 vm.$t，传入 t 桥接以兼容）
  setTitle(to, { $t: (key, value) => i18n.global.t(key, value) });
  // 返回页面顶端
  window.scrollTo(0, 0);
  PrevLoading.done();
});
export default router;
