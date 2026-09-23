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
 * 布局菜单配置
 * */
import { defineStore } from 'pinia';
import { ref } from 'vue';
import { menusApi } from '@/api/account';

function getMenusName() {
  let storage = window.localStorage;
  let menuList = JSON.parse(storage.getItem('menuList'));
  if (typeof menuList !== 'object' || menuList === null) {
    menuList = [];
  }
  return menuList;
}

export const useMenusStore = defineStore(
  'menus',
  () => {
    const menusName = ref(getMenusName());
    const openMenus = ref([]);
    const childMenuList = ref([]);
    const oneLvMenus = ref([]);
    const oneLvRoutes = ref([]);

    function getmenusNav(menuList) {
      menusName.value = menuList;
    }
    function setopenMenus(openList) {
      openMenus.value = openList;
    }
    function setOneLvMenus(list) {
      oneLvMenus.value = list;
    }
    function setOneLvRoute(list) {
      oneLvRoutes.value = list;
    }
    // 原 mutation 名 childMenuList 与 state 字段同名，setup store 内冲突，已改名 setChildMenuList
    function setChildMenuList(list) {
      childMenuList.value = list;
    }

    function getMenusNavList() {
      return new Promise((resolve, reject) => {
        menusApi()
          .then(async (res) => {
            resolve(res);
            getmenusNav(res.data.menus);
          })
          .catch((res) => {
            reject(res);
          });
      });
    }

    return {
      menusName,
      openMenus,
      childMenuList,
      oneLvMenus,
      oneLvRoutes,
      getmenusNav,
      setopenMenus,
      setOneLvMenus,
      setOneLvRoute,
      setChildMenuList,
      getMenusNavList,
    };
  },
  { persist: true },
);
