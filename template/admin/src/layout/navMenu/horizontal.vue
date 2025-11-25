<template>
  <div class="el-menu-horizontal-warp">
    <el-scrollbar @wheel.native.prevent="onElMenuHorizontalScroll" ref="elMenuHorizontalScrollRef">
      <el-menu
        router
        :default-active="activePath || defaultActive"
        background-color="transparent"
        mode="horizontal"
        @select="onHorizontalSelect"
      >
        <template v-for="val in menuList">
          <el-submenu :index="val.path" v-if="val.is_show && val.children && val.children.length > 0" :key="val.path">
            <template slot="title">
              <!-- <i class="ivu-icon" :class="val.icon ? 'el-icon-' + val.icon : ''"></i> -->
              <span>{{ $t(val.title) }}</span>
            </template>
            <SubItem :chil="val.children" />
          </el-submenu>
          <template v-else-if="val.is_show">
            <el-menu-item :index="val.path" :key="val.path">
              <template slot="title" v-if="!val.isLink || (val.isLink && val.isIframe)">
                <!-- <i class="ivu-icon" :class="val.icon ? 'el-icon-' + val.icon : ''"></i> -->
                {{ $t(val.title) }}
              </template>
              <template slot="title" v-else>
                <a :href="val.isLink" target="_blank">
                  <Icon :type="val.icon ? val.icon : ''" />
                  {{ $t(val.title) }}
                </a>
              </template>
            </el-menu-item>
          </template>
        </template>
      </el-menu>
    </el-scrollbar>
  </div>
</template>

<script>
import SubItem from '@/layout/navMenu/subItem.vue';
import { mapState } from 'vuex';
export default {
  name: 'navMenuHorizontal',
  components: { SubItem },
  props: {
    menuList: {
      type: Array,
      default: () => [],
    },
  },
  computed: {
    ...mapState('menu', ['activePath']),
  },
  data() {
    return {
      defaultActive: null,
    };
  },
  mounted() {
    this.initElMenuOffsetLeft();
    this.setCurrentRouterHighlight(this.$route.path);
  },
  methods: {
    // 设置横向滚动条可以鼠标滚轮滚动
    onElMenuHorizontalScroll(e) {
      const eventDelta = e.wheelDelta || -e.deltaY * 40;
      this.$refs.elMenuHorizontalScrollRef.$refs.wrap.scrollLeft =
        this.$refs.elMenuHorizontalScrollRef.$refs.wrap.scrollLeft + eventDelta / 4;
    },
    // 初始化数据，页面刷新时，滚动条滚动到对应位置
    initElMenuOffsetLeft() {
      this.$nextTick(() => {
        let els = document.querySelector('.el-menu.el-menu--horizontal li.is-active');
        if (!els) return false;
        this.$refs.elMenuHorizontalScrollRef.$refs.wrap.scrollLeft = els.offsetLeft;
      });
    },
    // 路由过滤递归函数
    filterRoutesFun(arr) {
      return arr
        .filter((item) => !item.isHide)
        .map((item) => {
          item = Object.assign({}, item);
          if (item.children) item.children = this.filterRoutesFun(item.children);
          return item;
        });
    },
    // 传送当前子级数据到菜单中
    setSendClassicChildren(path) {
      const currentPathSplit = path.split('/');
      let currentData = {};
      this.filterRoutesFun(this.$store.state.routesList.routesList).map((v, k) => {
        if (v.path === `/${currentPathSplit[1]}`) {
          v['k'] = k;
          currentData['item'] = [{ ...v }];
          currentData['children'] = [{ ...v }];
          if (v.children) currentData['children'] = v.children;
        }
      });
      return currentData;
    },
    // 菜单激活回调
    onHorizontalSelect(path) {
      this.bus.$emit('setSendClassicChildren', this.setSendClassicChildren(path));
    },
    // 设置页面当前路由高亮
    setCurrentRouterHighlight(path) {
      const currentPathSplit = path.split('/');
      if (this.$store.state.themeConfig.themeConfig.layout === 'classic') {
        this.defaultActive = `/${currentPathSplit[1]}`;
      } else {
        this.defaultActive = path;
      }
    },
  },
  watch: {
    // 监听路由的变化
    $route: {
      handler(to) {
        this.setCurrentRouterHighlight(to.path);
      },
      deep: true,
    },
  },
};
</script>

<style scoped lang="scss">
::v-deep .el-scrollbar__bar.is-horizontal {
  height: 0;
}
.el-menu-horizontal-warp {
  flex: 1;
  overflow: hidden;
  margin-right: 30px;
  ::v-deep .el-scrollbar__bar.is-vertical {
    display: none;
  }
  ::v-deep .el-scrollbar__wrap {
    overflow-y: hidden !important;
  }
  ::v-deepa {
    width: 100%;
  }
  .el-menu.el-menu--horizontal {
    display: flex;
    height: 100%;
    width: 100%;
    box-sizing: border-box;
  }
}
</style>
