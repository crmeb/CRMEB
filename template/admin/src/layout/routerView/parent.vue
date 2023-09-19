<template>
  <div :class="isTagHistory ? 'h100' : 'h101'">
    <transition :name="setTransitionName" mode="out-in">
      <keep-alive :include="keepAliveNameList">
        <router-view :key="refreshRouterViewKey" />
      </keep-alive>
    </transition>
  </div>
</template>

<script>
export default {
  name: 'parent',
  data() {
    return {
      refreshRouterViewKey: null,
      keepAliveNameList: [],
      keepAliveNameNewList: [],
    };
  },
  computed: {
    // 设置主界面切换动画
    setTransitionName() {
      return this.$store.state.themeConfig.themeConfig.animation;
    },
    isTagHistory() {
      return this.$store.state.themeConfig.themeConfig.isTagsview;
    },
  },
  created() {
    this.keepAliveNameList = this.getKeepAliveNames();
    this.bus.$on('onTagsViewRefreshRouterView', (path) => {
      if (this.$route.path !== path) return false;
      this.keepAliveNameList = this.getKeepAliveNames().filter((name) => this.$route.name !== name);
      this.refreshRouterViewKey = this.$route.path;
      this.$nextTick(() => {
        this.refreshRouterViewKey = null;
        this.keepAliveNameList = this.getKeepAliveNames();
      });
    });
  },

  methods: {
    // 获取路由缓存列表（name），默认路由全部缓存
    getKeepAliveNames() {
      return this.$store.state.keepAliveNames.keepAliveNames;
    },
  },
};
</script>
