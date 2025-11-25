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
    /**
     * 获取需要保持活动状态的组件名称列表
     */
    this.keepAliveNameList = this.getKeepAliveNames();
    // 监听标签页视图刷新路由视图事件
    this.bus.$on('onTagsViewRefreshRouterView', (path) => {
      // 如果当前路由路径不等于传入的路径，则直接返回false
      if (this.$route.path !== path) return false;
      // 过滤掉当前路由对应的组件名称，并重新设置keepAliveNameList
      this.keepAliveNameList = this.getKeepAliveNames().filter((name) => this.$route.name !== name);
      // 刷新路由视图key
      this.refreshRouterViewKey = this.$route.path;
      // 在下一个tick中重新设置keepAliveNameList
      this.$nextTick(() => {
        this.refreshRouterViewKey = null;
        /**
         * 获取需要保持活动状态的组件名称列表
         */
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
