// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

import {
  getBreadCrumbList,
  setTagNavListInLocalstorage,
  getTagNavListFromLocalstorage,
  getHomeRoute,
  getNextRoute,
  routeHasExist,
  routeEqual,
  getRouteTitleHandled,
  localSave,
  localRead,
} from '@/libs/util';
import router from '@/router';
import config from '@/config';
import { useUserStore } from './user';

const { homeName } = config;

export const useAppStore = defineStore(
  'app',
  () => {
    const breadCrumbList = ref([]);
    const tagNavList = ref([]);
    const homeRoute = ref({});
    const local = ref(localRead('local'));
    const errorList = ref([]);
    const adminTitle = ref('');
    const hasReadErrorPage = ref(false);

    const errorCount = computed(() => errorList.value.length);

    // 关闭标签页并跳转下一个路由
    const closePage = (route) => {
      const nextRoute = getNextRoute(tagNavList.value, route);
      tagNavList.value = tagNavList.value.filter((item) => {
        return !routeEqual(item, route);
      });
      router.push(nextRoute);
    };

    function setBreadCrumb(route) {
      breadCrumbList.value = getBreadCrumbList(route, homeRoute.value);
    }
    function setAdminTitle(title) {
      adminTitle.value = title;
    }
    function setHomeRoute(routes) {
      homeRoute.value = getHomeRoute(routes, homeName);
    }
    function setTagNavList(list) {
      let tagList = [];
      if (list) {
        tagList = [...list];
      } else tagList = getTagNavListFromLocalstorage() || [];
      tagNavList.value = tagList;
      setTagNavListInLocalstorage([...tagList]);
    }
    function closeTag(route) {
      let tag = tagNavList.value.filter((item) => routeEqual(item, route));
      route = tag[0] ? tag[0] : null;
      if (!route) return;
      closePage(route);
    }
    function addTag({ route, type = 'unshift' }) {
      let router = getRouteTitleHandled(route);
      let i = tagNavList.value.findIndex((item) => item.path === route.path);

      if (!routeHasExist(tagNavList.value, router)) {
        if (type === 'push')
          if (i < 1) tagNavList.value.push(router);
          else {
            return;
          }
        setTagNavListInLocalstorage([...tagNavList.value]);
      }
    }
    function setLocal(lang) {
      localSave('local', lang);
      local.value = lang;
    }
    function addError(error) {
      errorList.value.push(error);
    }
    function setHasReadErrorLoggerStatus(status = true) {
      hasReadErrorPage.value = status;
    }
    function clearAll() {
      tagNavList.value = [];
    }

    function addErrorLog(info) {
      if (!window.location.href.includes('error_logger_page')) setHasReadErrorLoggerStatus(false);
      const { token, userId, userName } = useUserStore();
      let data = {
        ...info,
        time: Date.parse(new Date()),
        token,
        userId,
        userName,
      };
    }

    return {
      breadCrumbList,
      tagNavList,
      homeRoute,
      local,
      errorList,
      adminTitle,
      hasReadErrorPage,
      errorCount,
      setBreadCrumb,
      setAdminTitle,
      setHomeRoute,
      setTagNavList,
      closeTag,
      addTag,
      setLocal,
      addError,
      setHasReadErrorLoggerStatus,
      clearAll,
      addErrorLog,
    };
  },
  { persist: true },
);
