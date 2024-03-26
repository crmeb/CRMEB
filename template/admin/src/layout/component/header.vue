<template>
  <el-header class="layout-header" :height="setHeaderHeight">
    <NavBarsIndex />
  </el-header>
</template>

<script>
import NavBarsIndex from '@/layout/navBars/index.vue';
export default {
  name: 'layoutHeader',
  components: { NavBarsIndex },
  data() {
    return {};
  },
  computed: {
    // 设置顶部 header 的具体高度
    setHeaderHeight() {
      let { isTagsview, layout } = this.$store.state.themeConfig.themeConfig;
      if (layout == 'classic') return '66px';
      if (isTagsview && layout !== 'classic') return '84px';
      else return '50px';
    },
  },
};
</script>
