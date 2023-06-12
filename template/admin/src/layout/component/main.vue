<template>
  <el-main class="layout-main">
    <el-scrollbar
      class="layout-scrollbar"
      ref="layoutScrollbarRef"
      v-show="!currentRouteMeta.isLink && !currentRouteMeta.isIframe"
      :style="{ minHeight: `calc(100vh - ${headerHeight}` }"
    >
      <LayoutParentView />
      <Footers v-if="getThemeConfig.isFooter" />
    </el-scrollbar>
    <Links
      :style="{ height: `calc(100vh - ${headerHeight}` }"
      :meta="currentRouteMeta"
      v-if="currentRouteMeta.isLink && !currentRouteMeta.isIframe"
    />
    <Iframes
      :style="{ height: `calc(100vh - ${headerHeight}` }"
      :meta="currentRouteMeta"
      v-if="currentRouteMeta.isLink && currentRouteMeta.isIframe && isShowLink"
      @getCurrentRouteMeta="onGetCurrentRouteMeta"
    />
  </el-main>
</template>

<script>
import LayoutParentView from '@/layout/routerView/parent.vue';
import Footers from '@/layout/footer/index.vue';
import Links from '@/layout/routerView/link.vue';
import Iframes from '@/layout/routerView/iframes.vue';
export default {
  name: 'layoutMain',
  components: { LayoutParentView, Footers, Links, Iframes },
  data() {
    return {
      headerHeight: '',
      currentRouteMeta: {},
      isShowLink: false,
    };
  },
  computed: {
    // 获取布局配置信息
    getThemeConfig() {
      return this.$store.state.themeConfig.themeConfig;
    },
  },
  mounted() {
    this.initHeaderHeight();
    this.initCurrentRouteMeta(this.$route.meta);
  },
  methods: {
    // 初始化当前路由 meta 信息
    initCurrentRouteMeta(meta) {
      this.isShowLink = false;
      this.currentRouteMeta = meta;
      setTimeout(() => {
        this.isShowLink = true;
      }, 100);
    },
    // 设置 main 的高度
    initHeaderHeight() {
      let { isTagsview } = this.$store.state.themeConfig.themeConfig;
      if (isTagsview) return (this.headerHeight = `84px`);
      else return (this.headerHeight = `50px`);
    },
    // 子组件触发更新
    onGetCurrentRouteMeta() {
      this.initCurrentRouteMeta(this.$route.meta);
    },
  },
  watch: {
    // 监听 vuex 数据变化
    '$store.state.themeConfig.themeConfig': {
      handler(val) {
        this.headerHeight = val.isTagsview ? '84px' : '50px';
        if (val.isFixedHeaderChange !== val.isFixedHeader) {
          if (!this.$refs.layoutScrollbarRef) return false;
          this.$refs.layoutScrollbarRef.update();
        }
      },
      deep: true,
    },
    // 监听路由的变化
    $route: {
      handler(to) {
        this.initCurrentRouteMeta(to.meta);
        this.$refs.layoutScrollbarRef.wrap.scrollTop = 0;
      },
      deep: true,
    },
  },
};
</script>
