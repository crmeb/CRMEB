import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useRoutesListStore = defineStore(
  'routesList',
  () => {
    const routesList = ref([]);

    // 设置路由，菜单中使用到
    function getRoutesList(data) {
      routesList.value = data;
    }

    // 设置路由，菜单中使用到
    async function setRoutesList(data) {
      getRoutesList(data);
    }

    return { routesList, getRoutesList, setRoutesList };
  },
  { persist: true },
);
