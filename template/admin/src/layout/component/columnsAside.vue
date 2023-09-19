<template>
  <div class="layout-columns-aside">
    <el-scrollbar>
      <Logo />
      <ul>
        <li
          v-for="(v, k) in columnsAsideList"
          :key="k"
          @click="onColumnsAsideMenuClick(v)"
          ref="columnsAsideOffsetTopRefs"
          class="layout-columns"
          :class="{ 'layout-columns-active': v.k === liIndex }"
          :title="$t(v.title)"
        >
          <div :class="setColumnsAsidelayout" v-if="!v.isLink || (v.isLink && v.isIframe)">
            <i :class="'el-icon-' + v.icon"></i>
            <div class="font12">
              {{
                $t(v.title) && $t(v.title).length >= 4
                  ? $t(v.title).substr(0, setColumnsAsidelayout === 'columns-vertical' ? 4 : 3)
                  : $t(v.title)
              }}
            </div>
          </div>
          <div :class="setColumnsAsidelayout" v-else>
            <a :href="v.isLink" target="_blank">
              <i :class="'el-icon-' + v.icon"></i>
              <div class="font12">
                {{
                  $t(v.title) && $t(v.title).length >= 4
                    ? $t(v.title).substr(0, setColumnsAsidelayout === 'columns-vertical' ? 4 : 3)
                    : $t(v.title)
                }}
              </div>
            </a>
          </div>
        </li>
        <div ref="columnsAsideActiveRef" :class="setColumnsAsideStyle"></div>
      </ul>
    </el-scrollbar>
  </div>
</template>

<script>
import { getMenuSider, getHeaderName, findFirstNonNullChildren } from '@/libs/system';
import Logo from '@/layout/logo/index.vue';
import { mapState } from 'vuex';

export default {
  name: 'layoutColumnsAside',
  components: { Logo },
  data() {
    return {
      columnsAsideList: [],
      liIndex: 0,
      difference: 0,
      routeSplit: [],
    };
  },
  computed: {
    // 设置分栏高亮风格
    setColumnsAsideStyle() {
      return this.$store.state.themeConfig.themeConfig.columnsAsideStyle;
    },
    // 设置分栏布局风格
    setColumnsAsidelayout() {
      return this.$store.state.themeConfig.themeConfig.columnsAsideLayout;
    },
    Layout() {
      return this.$store.state.themeConfig.themeConfig.Layout;
    },
    routesList() {
      this.$store.state.routesList.routesList;
    },
    ...mapState('menu', ['activePath']),
  },
  beforeDestroy() {
    this.bus.$off('routesListChange');
  },
  mounted() {
    this.bus.$on('routesListChange', () => {
      this.setFilterRoutes();
    });
    this.setFilterRoutes();
  },
  methods: {
    // 设置菜单高亮位置移动
    setColumnsAsideMove(k) {
      if (k === undefined) return false;
      const els = this.$refs.columnsAsideOffsetTopRefs;
      this.liIndex = k;
      this.$refs.columnsAsideActiveRef.style.top = `${els[k].offsetTop + this.difference}px`;
    },
    // 菜单高亮点击事件
    onColumnsAsideMenuClick(v) {
      let { path, redirect } = v;
      if (v.children) {
        this.$router.push(findFirstNonNullChildren(v.children).path);
      } else {
        this.$router.push(path);
      }
      // 一个路由设置自动收起菜单
      if (!v.children || v.children.length <= 1) this.$store.state.themeConfig.themeConfig.isCollapse = true;
      else if (v.children.length > 1) this.$store.state.themeConfig.themeConfig.isCollapse = false;
      // this.bus.$emit('setSendColumnsChildren', getMenuSider(this.columnsAsideList, path));
    },
    // 设置高亮动态位置
    onColumnsAsideDown(k) {
      this.$nextTick(() => {
        this.setColumnsAsideMove(k);
      });
    },
    // 设置/过滤路由（非静态路由/是否显示在菜单中）
    setFilterRoutes() {
      if (this.$store.state.routesList.routesList.length <= 0) return false;
      this.columnsAsideList = this.filterRoutesFun(this.$store.state.routesList.routesList);
      //   const resData = getHeaderName(this.$route.path, this.columnsAsideList);
      const resData = this.setSendChildren(getHeaderName(this.$route, this.columnsAsideList));
      if (!resData.children) {
        this.bus.$emit('setSendColumnsChildren', []);
        this.$store.commit('menus/childMenuList', []);

        this.$store.state.themeConfig.themeConfig.isCollapse = true;
        return false;
      }
      this.bus.$emit('oneCatName', resData.item[0].title);
      this.onColumnsAsideDown(resData.item[0].k);
      // 刷新时，初始化一个路由设置自动收起菜单
      resData.children.length > 0
        ? (this.$store.state.themeConfig.themeConfig.isCollapse = false)
        : (this.$store.state.themeConfig.themeConfig.isCollapse = true);
      this.bus.$emit('setSendColumnsChildren', resData?.children || []);
      this.$store.commit('menus/childMenuList', resData?.children || []);
    },
    // 传送当前子级数据到菜单中
    setSendChildren(path) {
      let currentData = {};
      this.columnsAsideList.map((v, k) => {
        v['k'] = k;
        if (v.path === path) {
          currentData['item'] = [{ ...v }];
          //   currentData['children'] = [{ ...v }];
          if (v.children) currentData['children'] = v.children;
        }
      });
      return currentData;
    },
    // 路由过滤递归函数
    filterRoutesFun(arr) {
      return arr
        .filter((item) => item.path)
        .map((item) => {
          item = Object.assign({}, item);
          if (item.children) item.children = this.filterRoutesFun(item.children);
          return item;
        });
    },
    // tagsView 点击时，根据路由查找下标 columnsAsideList，实现左侧菜单高亮
    setColumnsMenuHighlight(path) {
      // this.routeSplit = path.split('/');
      // this.routeSplit.shift();
      // const routeFirst = `/${this.routeSplit[0]}`;
      const currentSplitRoute = this.columnsAsideList.find((v) => v.path === path);
      if (!currentSplitRoute) {
        // this.onColumnsAsideDown(0);
        return false;
      }
      // 延迟拿值，防止取不到
      setTimeout(() => {
        this.onColumnsAsideDown(currentSplitRoute.k);
      }, 0);
    },
  },
  watch: {
    // 监听 vuex 数据变化
    '$store.state': {
      handler(val) {
        val.themeConfig.themeConfig.columnsAsideStyle === 'columnsRound'
          ? (this.difference = 3)
          : (this.difference = 0);
        if (val.routesList.routesList.length === this.columnsAsideList.length) return false;
      },
      deep: true,
    },
    // 监听路由的变化
    $route: {
      handler(to) {
        this.setColumnsMenuHighlight(to.path);
        let HeadName = getHeaderName(to, this.columnsAsideList);
        let asideList = getMenuSider(this.columnsAsideList, HeadName)[0]?.children;
        const resData = this.setSendChildren(HeadName);
        if (resData.item) {
          this.onColumnsAsideDown(resData.item[0].k);
          this.bus.$emit('oneCatName', resData.item[0].title);
        } else {
          this.onColumnsAsideDown(0);
        }
        this.$store.commit('menus/childMenuList', asideList || []);
      },
      deep: true,
    },
  },
};
</script>

<style scoped lang="scss">
.layout-columns-aside {
  width: 70px;
  height: 100%;
  background: var(--prev-bg-columnsMenuBar);
  box-shadow: 0 1px 4px rgba(0, 21, 41, 0.08);
  border-right: 1px solid var(--prev-border-color-lighter);

  ul {
    position: relative;
    li {
      color: var(--prev-bg-columnsMenuBarColor);
      width: 100%;
      height: 50px;
      text-align: center;
      display: flex;
      cursor: pointer;
      position: relative;
      z-index: 1;
      .columns-vertical {
        margin: auto;
        .columns-vertical-title {
          padding-top: 1px;
        }
      }
      .columns-horizontal {
        display: flex;
        height: 50px;
        width: 100%;
        align-items: center;
        justify-content: center;
        padding: 0 5px;
        i {
          margin-right: 3px;
        }
        a {
          display: flex;
          .columns-horizontal-title {
            padding-top: 1px;
          }
        }
      }
      a {
        text-decoration: none;
        color: var(--prev-bg-columnsMenuBarColor);
      }
    }
    // li:hover {
    //   background: var(--prev-color-primary);
    //   color: var(--prev-bg-columnsMenuBarColor);
    // }
    .layout-columns {
      transition: 0.3s ease-in-out;
    }
    .layout-columns-active,
    .layout-columns-active a {
      color: var(--prev-bg-columnsMenuActiveColor);
      transition: 0.3s ease-in-out;
    }

    .columns-round {
      background: var(--prev-color-primary);
      // color: var(--prev-color-text-white);
      position: absolute;
      left: 50%;
      top: 2px;
      height: 50px;
      width: 65px;
      transform: translateX(-50%);
      z-index: 0;
      transition: 0.3s ease-in-out;
      border-radius: 5px;
    }
    .columns-card {
      @extend .columns-round;
      top: 0;
      height: 50px;
      width: 100%;
      border-radius: 0;
    }
  }
}
</style>
