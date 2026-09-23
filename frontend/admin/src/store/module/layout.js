import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useLayoutStore = defineStore(
  'layout',
  () => {
    const menuCollapse = ref(false);

    /**
     * @description 设置侧边栏展开关闭
     * @param {Boolean} status status
     */
    function changeCol(status) {
      menuCollapse.value = status;
    }

    return { menuCollapse, changeCol };
  },
  { persist: true },
);
