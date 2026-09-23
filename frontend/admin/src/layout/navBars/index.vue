<template>
  <div class="layout-navbars-container">
    <BreadcrumbIndex />
    <TagsView v-if="setShowTagsView" />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import BreadcrumbIndex from '@/layout/navBars/breadcrumb/index.vue';
import TagsView from '@/layout/navBars/tagsView/tagsView.vue';
import { useThemeConfigStore } from '@/store/module/themeConfig';

defineOptions({ name: 'layoutNavBars' });

const themeConfigStore = useThemeConfigStore();

// 设置是否显示 tagsView
const setShowTagsView = computed(() => {
  let { layout, isTagsview } = themeConfigStore.themeConfig;
  return layout !== 'classic' && isTagsview;
});
</script>

<style scoped lang="scss">
.layout-navbars-container {
  display: flex;
  flex-direction: column;
  width: 100%;
  height: 100%;
  box-shadow: 0 1px 4px rgba(0, 21, 41, 0.08);
}
</style>
