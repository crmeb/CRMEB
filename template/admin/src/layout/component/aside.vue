<template>
  <el-aside class="layout-aside" :class="setCollapseWidth" v-if="clientWidth > 1000">
    <Logo v-if="setShowLogo && menuList.length && getThemeConfig.layout !== 'columns'" />
    <div v-if="menuList.length && getThemeConfig.layout == 'columns'" class="cat-name">
      {{ adminTitle || catName }}
    </div>
    <el-scrollbar class="flex-auto" ref="layoutAsideRef">
      <Vertical :menuList="menuList" :class="setCollapseWidth" />
    </el-scrollbar>
  </el-aside>
  <el-drawer :visible.sync="getThemeConfig.isCollapse" :with-header="false" direction="ltr" size="180px" v-else>
    <el-aside class="layout-aside w100 h100">
      <Logo v-if="setShowLogo && menuList.length" />
      <el-scrollbar class="flex-auto" ref="layoutAsideRef">
        <Vertical :menuList="menuList" />
      </el-scrollbar>
    </el-aside>
  </el-drawer>
</template>

<script>
import Vertical from '@/layout/navMenu/vertical.vue';
import Logo from '@/layout/logo/index.vue';
export default {
  name: 'layoutAside',
  components: { Vertical, Logo },
  data() {
    return {
      // menuList: [],
      clientWidth: '',
      catName: '',
    };
  },
  computed: {
    adminTitle() {
      return this.$store.state.app.adminTitle || '';
    },
    // 设置左侧菜单的具体宽度
    menuList() {
      this.$store.state.menus.childMenuList.length > 0
        ? (this.$store.state.themeConfig.themeConfig.isCollapse = false)
        : (this.$store.state.themeConfig.themeConfig.isCollapse = true);
      return this.$store.state.menus.childMenuList;
    },
    setCollapseWidth() {
      let { layout, isCollapse } = this.$store.state.themeConfig.themeConfig;
      let asideBrColor = '';
      layout === 'classic' || layout === 'columns' ? (asideBrColor = 'layout-el-aside-br-color') : '';

      if (layout === 'columns') {
        // 分栏布局，菜单收起时宽度给 1px / 暂为0px
        if (isCollapse) {
          return ['layout-aside-width1', asideBrColor];
        } else {
          return ['layout-aside-width-default', asideBrColor];
        }
      } else {
        // 其它布局给 64px
        if (isCollapse) {
          return ['layout-aside-width1', asideBrColor];
        } else {
          return ['layout-aside-width-default', asideBrColor, layout === 'classic' ? 'pt8' : ''];
        }
      }
    },
    // 设置 logo 是否显示
    setShowLogo() {
      let { layout, isShowLogo } = this.$store.state.themeConfig.themeConfig;
      return (isShowLogo && layout === 'defaults') || (isShowLogo && layout === 'columns');
    },
    // 获取布局配置信息
    getThemeConfig() {
      return this.$store.state.themeConfig.themeConfig;
    },
  },
  created() {
    this.initMenuFixed(document.body.clientWidth);
    this.setFilterRoutes();
    // this.bus.$on('setSendColumnsChildren', (res) => {
    //   this.menuList = res || [];
    //   this.menuList.length > 0
    //     ? (this.$store.state.themeConfig.themeConfig.isCollapse = false)
    //     : (this.$store.state.themeConfig.themeConfig.isCollapse = true);
    // });
    this.bus.$on('layoutMobileResize', (res) => {
      this.initMenuFixed(res.clientWidth);
    });
    this.bus.$on('oneCatName', (name) => {
      this.catName = name;
    });
    // 菜单滚动条监听
    this.bus.$on('updateElScrollBar', () => {
      setTimeout(() => {
        this.$refs.layoutAsideRef.update();
      }, 300);
    });
    if (this.$store.state.themeConfig.themeConfig.layout !== 'columns') {
      this.bus.$on('routesListChange', () => {
        this.setFilterRoutes();
      });
    }
  },
  beforeDestroy() {
    this.bus.$off('routesListChange');
  },
  methods: {
    // 设置/过滤路由（非静态路由/是否显示在菜单中）
    setFilterRoutes() {
      if (this.$store.state.themeConfig.themeConfig.layout === 'columns') return false;
      this.$store.commit('menus/childMenuList', this.filterRoutesFun(this.$store.state.routesList.routesList));
      // this.menuList = this.filterRoutesFun(this.$store.state.routesList.routesList);
    },
    // 设置/过滤路由 递归函数
    filterRoutesFun(arr) {
      return arr
        .filter((item) => item.path)
        .map((item) => {
          item = Object.assign({}, item);
          if (item.children) item.children = this.filterRoutesFun(item.children);
          return item;
        });
    },
    // 设置菜单导航是否固定（移动端）
    initMenuFixed(clientWidth) {
      this.clientWidth = clientWidth;
      this.$emit('routesListChange');
    },
  },
  // 页面销毁时
  destroyed() {
    // 取消菜单滚动条监听
    this.bus.$off('updateElScrollBar', () => {});
  },
};
</script>
<style lang="scss" scoped>
.cat-name {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 50px;
  border-bottom: 1px solid var(--prev-border-color-lighter);
  font-weight: 500;
  font-size: 15px;
}
</style>
