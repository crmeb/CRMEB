import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useTagsViewRoutesStore = defineStore('tagsViewRoutes', () => {
  const tagsViewRoutes = ref([]);

  // 设置 TagsView 路由
  function getTagsViewRoutes(data) {
    tagsViewRoutes.value = data;
  }

  // 设置 TagsView 路由
  async function setTagsViewRoutes(data) {
    getTagsViewRoutes(data);
  }

  return { tagsViewRoutes, getTagsViewRoutes, setTagsViewRoutes };
});
