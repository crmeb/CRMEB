import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useKeepAliveNamesStore = defineStore(
  'keepAliveNames',
  () => {
    const keepAliveNames = ref([]);

    // 设置路由缓存（name字段）
    function getCacheKeepAlive(data) {
      keepAliveNames.value = data;
    }

    // 设置路由缓存（name字段）
    async function setCacheKeepAlive(data) {
      getCacheKeepAlive(data);
    }

    return { keepAliveNames, getCacheKeepAlive, setCacheKeepAlive };
  },
  { persist: true },
);
