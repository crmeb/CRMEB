<template>
  <div class="layout-navbars-container">
    <BreadcrumbIndex />
    <TagsView v-if="setShowTagsView" />
  </div>
</template>

<script>
import BreadcrumbIndex from '@/layout/navBars/breadcrumb/index.vue';
import TagsView from '@/layout/navBars/tagsView/tagsView.vue';
export default {
  name: 'layoutNavBars',
  components: { BreadcrumbIndex, TagsView },
  data() {
    return {};
  },
  computed: {
    // 设置是否显示 tagsView
    setShowTagsView() {
      let { layout, isTagsview } = this.$store.state.themeConfig.themeConfig;
      return layout !== 'classic' && isTagsview;
    },
  },
};
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
