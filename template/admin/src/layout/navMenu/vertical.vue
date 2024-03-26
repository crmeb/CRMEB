<template>
  <div>
    <el-menu
      router
      :class="setColumnsAsideStyle"
      background-color="transparent"
      :default-active="activePath || defaultActive"
      :collapse="setIsCollapse"
      :unique-opened="getThemeConfig.isUniqueOpened"
      :collapse-transition="true"
    >
      <template v-for="val in menuList">
        <el-submenu :index="val.path" v-if="val.is_show && val.children && val.children.length > 0" :key="val.path">
          <template slot="title">
            <i class="ivu-icon" :class="val.icon ? 'el-icon-' + val.icon : ''"></i>
            <span>{{ $t(val.title) }}</span>
          </template>
          <SubItem :chil="val.children" />
        </el-submenu>
        <template v-else-if="val.is_show">
          <el-menu-item :index="val.path" :key="val.path">
            <i class="ivu-icon" :class="val.icon ? 'el-icon-' + val.icon : ''"></i>
            <template slot="title" v-if="!val.isLink || (val.isLink && val.isIframe)">
              <span>{{ $t(val.title) }}</span>
            </template>
            <template slot="title" v-else>
              <a :href="val.isLink" target="_blank">{{ $t(val.title) }}</a>
            </template>
          </el-menu-item>
        </template>
      </template>
    </el-menu>
  </div>
</template>

<script>
import SubItem from '@/layout/navMenu/subItem.vue';
import { mapState } from 'vuex';

export default {
  name: 'navMenuVertical',
  components: { SubItem },
  props: {
    menuList: {
      type: Array,
      default() {
        return [];
      },
    },
  },
  data() {
    return {
      defaultActive: this.$route.path,
      onRoutes: '',
    };
  },
  computed: {
    ...mapState('menu', ['activePath']),
    // 设置分栏高亮风格
    setColumnsAsideStyle() {
      return this.$store.state.themeConfig.themeConfig.columnsAsideStyle;
    },
    // 获取布局配置信息
    getThemeConfig() {
      return this.$store.state.themeConfig.themeConfig;
    },
    // 设置左侧菜单是否展开/收起
    setIsCollapse() {
      return document.body.clientWidth < 1000 ? false : this.$store.state.themeConfig.themeConfig.isCollapse;
    },
  },
  watch: {
    // 监听路由的变化
    $route: {
      handler(to) {
        this.defaultActive = to.path;
        const clientWidth = document.body.clientWidth;
        if (clientWidth < 1000) this.$store.state.themeConfig.themeConfig.isCollapse = false;
      },
      deep: true,
    },
  },
  created() {},
};
</script>
<style lang="scss" scoped>
::v-deep .center {
  text-align: center;
  margin-right: 0 !important;
  margin-left: 5px;
}
// ::v-deep .el-submenu__title {
//   display: flex;
//   justify-content: center;
//   align-items: center;
// }
</style>
