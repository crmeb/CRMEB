// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
/**
 * 菜单
 * */
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { cloneDeep } from 'lodash';
import { includeArray } from '@/libs/system';
import { useUserStore } from './user';

// 根据 menu 配置的权限，过滤菜单
function filterMenu(menuList, access, lastList) {
  menuList.forEach((menu) => {
    let menuAccess = menu.auth;

    if (!menuAccess || includeArray(menuAccess, access)) {
      // 整节点剔除 children 后一次 cloneDeep，替代逐属性深克隆
      const { children, ...rest } = menu;
      const newMenu = cloneDeep(rest);
      if (children && children.length) newMenu.children = [];

      lastList.push(newMenu);
      children && filterMenu(children, access, newMenu.children);
    }
  });
  return lastList;
}
// 递归处理顶部菜单问题
function getChilden(data) {
  if (data.children) {
    return getChilden(data.children[0]);
  }
  return data.path;
}

export const useMenuStore = defineStore(
  'menu',
  () => {
    // 顶部菜单
    const header = ref([]);
    // 一级菜单名称
    const oneMenuName = ref('');
    // 侧栏菜单
    const sider = ref([]);
    // 当前顶栏菜单的 name
    const headerName = ref('');
    // 当前所在菜单的 path
    const activePath = ref('');
    // 展开的子菜单 name 集合
    const openNames = ref([]);

    /**
     * @description 根据 user 里登录用户权限，对侧边菜单进行鉴权过滤
     * */
    const filterSider = computed(() => {
      const userInfo = useUserStore().info;
      // @权限
      const access = userInfo.access;
      if (access && access.length) {
        return filterMenu(sider.value, access, []);
      } else {
        return filterMenu(sider.value, [], []);
      }
    });
    // 处理顶部路由递归

    /**
     * @description 根据 user 里登录用户权限，对顶栏菜单进行鉴权过滤
     * 历史笔误：原 Vuex 引用 rootState.admin.user（admin 模块并不存在），实际等价 rootState.user，这里统一改为 useUserStore()
     * */
    const filterHeader = computed(() => {
      //  调用递归函数
      header.value.forEach((item) => {
        item.path = getChilden(item);
      });

      // @权限
      const userInfo = useUserStore().info;
      const access = userInfo.access;
      if (access && access.length) {
        return header.value.filter((item) => {
          let state = true;
          if (item.auth && !includeArray(item.auth, access)) state = false;
          return state;
        });
      } else {
        return header.value.filter((item) => {
          let state = true;
          if (item.auth && item.auth.length) state = false;
          return state;
        });
      }
    });
    /**
     * @description 当前 header 的全部信息
     * */
    const currentHeader = computed(() => {
      return header.value.find((item) => item.name === headerName.value);
    });
    /**
     * @description 在当前 header 下，是否隐藏 sider（及折叠按钮）
     * */
    const hideSider = computed(() => {
      let visible = false;
      if (currentHeader.value && 'hideSider' in currentHeader.value) visible = currentHeader.value.hideSider;
      return visible;
    });

    /**
     * @description 设置侧边栏菜单
     * @param {Array} menu menu
     */
    function setSider(menu) {
      sider.value = menu;
    }
    /**
     * @description 设置侧边栏菜单
     * @param {Array} menu menu
     */
    function setOpenMenuName(menu) {
      oneMenuName.value = menu;
    }
    /**
     * @description 设置顶栏菜单
     * @param {Array} menu menu
     */
    function setHeader(menu) {
      header.value = menu;
    }
    /**
     * @description 设置当前顶栏菜单 name
     * @param {Array} name headerName
     */
    function setHeaderName(name) {
      headerName.value = name;
    }
    /**
     * @description 设置当前所在菜单的 path，用于侧栏菜单高亮当前项
     * @param {Array} path fullPath
     */
    function setActivePath(path) {
      activePath.value = path;
    }
    /**
     * @description 设置当前所在菜单的全部展开父菜单的 names 集合
     * @param {Array} names openNames
     */
    function setOpenNames(names) {
      openNames.value = names;
    }

    return {
      header,
      oneMenuName,
      sider,
      headerName,
      activePath,
      openNames,
      filterSider,
      filterHeader,
      currentHeader,
      hideSider,
      setSider,
      setOpenMenuName,
      setHeader,
      setHeaderName,
      setActivePath,
      setOpenNames,
    };
  },
  { persist: true },
);
