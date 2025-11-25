<template>
  <!-- 判断显示哪个组件 -->
  <Mains v-if="headMenuNoShow" />
  <!-- 根据主题配置中的布局类型来判断显示哪个组件 -->
  <Defaults v-else-if="getThemeConfig.layout === 'defaults'" />
  <Classic v-else-if="getThemeConfig.layout === 'classic'" />
  <Transverse v-else-if="getThemeConfig.layout === 'transverse'" />
  <Columns v-else-if="getThemeConfig.layout === 'columns'" />
</template>

<script>
import { Local } from '@/utils/storage.js';
import { mapMutations } from 'vuex';
import { getNewTagList } from '@/libs/util';

export default {
  name: 'layout',
  components: {
    Defaults: () => import('@/layout/main/defaults.vue'),
    Classic: () => import('@/layout/main/classic.vue'),
    Transverse: () => import('@/layout/main/transverse.vue'),
    Columns: () => import('@/layout/main/columns.vue'),
    Mains: () => import('@/layout/component/main.vue'),
  },
  data() {
    return {
      headMenuNoShow: false,
    };
  },
  computed: {
    // 获取布局配置信息
    getThemeConfig() {
      return this.$store.state.themeConfig.themeConfig;
    },
    tagNavList() {
      return this.$store.state.app.tagNavList;
    },
  },
  watch: {
    $route(newRoute) {
      this.headMenuNoShow = this.$route.meta.fullScreen;
      const { name, query, params, meta, path } = newRoute;
      this.addTag({
        route: { name, query, params, meta, path },
        type: 'push',
      });
      this.setBreadCrumb(newRoute);
      this.setTagNavList(getNewTagList(this.tagNavList, newRoute));
    },
  },
  created() {
    this.headMenuNoShow = this.$route.meta.fullScreen;
    this.onLayoutResize();
    window.addEventListener('resize', this.onLayoutResize);
  },
  methods: {
    ...mapMutations(['setBreadCrumb', 'setTagNavList', 'addTag', 'setLocal', 'setHomeRoute', 'closeTag']),

    // 窗口大小改变时(适配移动端)
    onLayoutResize() {
      if (!Local.get('oldLayout')) Local.set('oldLayout', this.$store.state.themeConfig.themeConfig.layout);
      const clientWidth = document.body.clientWidth;
      if (clientWidth < 1000) {
        this.$store.state.themeConfig.themeConfig.isCollapse = false;
        this.bus.$emit('layoutMobileResize', {
          layout: 'defaults',
          clientWidth,
        });
      } else {
        this.bus.$emit('layoutMobileResize', {
          layout: Local.get('oldLayout') ? Local.get('oldLayout') : this.$store.state.themeConfig.themeConfig.layout,
          clientWidth,
        });
      }
    },
  },
  distroyed() {
    window.removeEventListener('resize', this.onLayoutResize);
  },
};
</script>
