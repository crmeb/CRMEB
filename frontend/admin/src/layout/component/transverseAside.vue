<template>
  <div class="layout-columns-tra-aside el-menu-horizontal-warp">
    <el-scrollbar ref="elMenuHorizontalScrollRef" @wheel.prevent="onElMenuHorizontalScroll">
      <ul>
        <li
          v-for="(v, k) in columnsAsideList"
          :key="k"
          @click="onColumnsAsideMenuClick(v)"
          ref="columnsAsideOffsetLeftRefs"
          class="layout-columns"
          :class="{ 'layout-columns-active': v.k === liIndex }"
          :title="$t(v.title)"
        >
          <div :class="setColumnsAsidelayout" v-if="!v.isLink || (v.isLink && v.isIframe)">
            <MenuIcon :icon="v.icon" />
            <div class="font14">
              {{
                $t(v.title) && $t(v.title).length >= 4
                  ? $t(v.title).substr(0, setColumnsAsidelayout === 'columns-vertical' ? 4 : 3)
                  : $t(v.title)
              }}
            </div>
          </div>
          <div :class="setColumnsAsidelayout" v-else>
            <a :href="v.isLink" target="_blank">
              <MenuIcon :icon="v.icon" />
              <div class="font14">
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

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { getMenuSider, getHeaderName, findFirstNonNullChildren } from '@/libs/system';
import Logo from '@/layout/logo/index.vue';
import MenuIcon from '@/components/menuIcon/index.vue';
import bus from '@/utils/bus';
import { useMenusStore } from '@/store/module/menus';
import { useRoutesListStore } from '@/store/module/routesList';
import { useThemeConfigStore } from '@/store/module/themeConfig';

defineOptions({ name: 'layoutColumnsAside' });

const route = useRoute();
const router = useRouter();
const menusStore = useMenusStore();
const routesListStore = useRoutesListStore();
const themeConfigStore = useThemeConfigStore();

const columnsAsideList = ref([]);
const liIndex = ref(0);
const difference = ref(0);
const elMenuHorizontalScrollRef = ref(null);
const columnsAsideOffsetLeftRefs = ref(null);
const columnsAsideActiveRef = ref(null);

// 设置分栏高亮风格
const setColumnsAsideStyle = computed(() => themeConfigStore.themeConfig.columnsAsideStyle);

// 设置分栏布局风格
const setColumnsAsidelayout = computed(() => themeConfigStore.themeConfig.columnsAsideLayout);

onBeforeUnmount(() => {
  bus.off('routesListChange', onRoutesListChange);
});

onMounted(() => {
  bus.on('routesListChange', onRoutesListChange);
  setFilterRoutes();
  nextTick((e) => {
    initElMenuOffsetLeft();
  });
});

function onRoutesListChange() {
  setFilterRoutes();
}

// 设置横向滚动条可以鼠标滚轮滚动
function onElMenuHorizontalScroll(e) {
  const eventDelta = e.wheelDelta || -e.deltaY * 40;
  elMenuHorizontalScrollRef.value.wrapRef.scrollLeft =
    elMenuHorizontalScrollRef.value.wrapRef.scrollLeft + eventDelta / 4;
}

// 初始化数据，页面刷新时，滚动条滚动到对应位置
function initElMenuOffsetLeft() {
  nextTick(() => {
    let els = document.querySelector('.layout-columns.layout-columns-active');
    if (!els) return false;
    elMenuHorizontalScrollRef.value.wrapRef.scrollLeft = els.offsetLeft;
  });
}

// 设置菜单高亮位置移动
function setColumnsAsideMove(k) {
  if (k === undefined) return false;
  const els = columnsAsideOffsetLeftRefs.value;
  // 菜单数据未就绪时 ref 列表可能为空，跳过本次位移避免报错
  if (!els || !els[k]) return false;
  liIndex.value = k;
  columnsAsideActiveRef.value.style.left = `${els[k].offsetLeft + difference.value}px`;
}

// 菜单高亮点击事件
function onColumnsAsideMenuClick(v) {
  let { path, redirect } = v;
  if (v.children && v.children.length) {
    // children 递归可能返回 null（首项 children 为空数组），fallback 到父级 path 避免点击报错失效
    const first = findFirstNonNullChildren(v.children);
    router.push((first && first.path) || path);
  } else {
    router.push(path);
  }
  // 一个路由设置自动收起菜单
  if (!v.children || v.children.length <= 1) themeConfigStore.themeConfig.isCollapse = true;
  else if (v.children.length > 1) themeConfigStore.themeConfig.isCollapse = false;
  // bus.emit('setSendColumnsChildren', getMenuSider(columnsAsideList.value, path));
}

// 设置高亮动态位置
function onColumnsAsideDown(k) {
  nextTick(() => {
    setColumnsAsideMove(k);
  });
}

// 设置/过滤路由（非静态路由/是否显示在菜单中）
function setFilterRoutes() {
  if (routesListStore.routesList.length <= 0) return false;
  columnsAsideList.value = filterRoutesFun(routesListStore.routesList);
  //   const resData = getHeaderName(route.path, columnsAsideList.value);
  const resData = setSendChildren(getHeaderName(route, columnsAsideList.value));
  if (!resData.children) {
    bus.emit('setSendColumnsChildren', []);
    menusStore.setChildMenuList([]);

    themeConfigStore.themeConfig.isCollapse = true;
    return false;
  }
  bus.emit('oneCatName', resData.item[0].title);
  onColumnsAsideDown(resData.item[0].k);
  // 刷新时，初始化一个路由设置自动收起菜单
  resData.children.length > 0
    ? (themeConfigStore.themeConfig.isCollapse = false)
    : (themeConfigStore.themeConfig.isCollapse = true);
  bus.emit('setSendColumnsChildren', resData?.children || []);
  menusStore.setChildMenuList(resData?.children || []);
}

// 传送当前子级数据到菜单中
function setSendChildren(path) {
  // const currentPathSplit = path.split('/');
  let currentData = {};
  columnsAsideList.value.map((v, k) => {
    v['k'] = k;
    if (v.path === path) {
      currentData['item'] = [{ ...v }];
      //   currentData['children'] = [{ ...v }];
      if (v.children) currentData['children'] = v.children;
    }
  });
  return currentData;
}

// 路由过滤递归函数
function filterRoutesFun(arr) {
  return arr
    .filter((item) => item.path)
    .map((item) => {
      item = Object.assign({}, item);
      if (item.children) item.children = filterRoutesFun(item.children);
      return item;
    });
}

// tagsView 点击时，根据路由查找下标 columnsAsideList，实现左侧菜单高亮
function setColumnsMenuHighlight(path) {
  const currentSplitRoute = columnsAsideList.value.find((v) => v.path === path);
  if (!currentSplitRoute) {
    // 无菜单归属的路由保持当前模块高亮，避免漂移后点击已激活模块无效
    return false;
  }
  // 延迟拿值，防止取不到
  setTimeout(() => {
    onColumnsAsideDown(currentSplitRoute.k);
  }, 0);
}

// 监听 store 数据变化（原 watch '$store.state' 的净效果：分栏高亮风格切换时调整偏移量）
watch(
  () => themeConfigStore.themeConfig.columnsAsideStyle,
  (val) => {
    val === 'columnsRound' ? (difference.value = 3) : (difference.value = 0);
  },
);

// 监听路由的变化
watch(
  () => route.fullPath,
  () => {
    const to = route;
    setColumnsMenuHighlight(to.path);
    // setColumnsAsideMove();
    let HeadName = getHeaderName(to, columnsAsideList.value);
    let asideList = getMenuSider(columnsAsideList.value, HeadName)[0]?.children;
    const resData = setSendChildren(HeadName);
    if (resData.item) {
      onColumnsAsideDown(resData.item[0].k);
      bus.emit('oneCatName', resData.item[0].title);
    }
    // 无菜单归属的路由保持当前模块高亮（原 resData.length 判断为死代码，且 resData.item[0] 会抛错中断 watch）
    bus.emit('setSendColumnsChildren', asideList || []);
    menusStore.setChildMenuList(asideList || []);
  },
);
</script>

<style scoped lang="scss">
:deep(.el-scrollbar__bar.is-horizontal) {
  height: 0;
}
.el-menu-horizontal-warp {
  :deep(.el-scrollbar__bar.is-vertical) {
    display: none;
  }
  :deep(.el-scrollbar__wrap) {
    overflow-y: hidden !important;
    overflow-x: scroll !important;
  }
  :deep(a) {
    width: 100%;
  }
  .el-menu.el-menu--horizontal {
    display: flex;
    height: 100%;
    width: 100%;
    box-sizing: border-box;
  }
}

.layout-columns-tra-aside {
  height: 100%;
  background: var(--prev-bg-columnsMenuBar);
  // box-shadow: 0 1px 4px rgba(0, 21, 41, 0.08);
  overflow-y: hidden;
  // flex: 1;
  ul {
    position: relative;
    display: flex;
    li {
      color: var(--prev-bg-columnsMenuBarColor);
      width: 80px;
      height: 66px;
      text-align: center;
      display: flex;
      cursor: pointer;
      position: relative;
      z-index: 1;
      .columns-vertical {
        margin: auto;

        // 垂直一级菜单中图标单独占一行，不能继承行内菜单的右间距，否则视觉中心会向左偏移
        :deep(.menu-icon) {
          margin-right: 0;
        }

        .columns-vertical-title {
          //   padding-top: 1px;
        }
      }
      .columns-horizontal {
        display: flex;
        height: 50px;
        width: 80px;
        align-items: center;
        justify-content: center;
        padding: 0 5px;
        i {
          margin-right: 5px;
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
    //   background: var(--prev-bg-menu-hover-ba-color);
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
    // 高亮指示条：本体与菜单项同宽（JS left 按项定位），实际色条由伪元素在项内居中，
    // 避免整项宽下划线过长，且不依赖 transform 定值居中（受实际项宽/缩放影响会偏移）
    .columns-round {
      position: absolute;
      left: 0;
      height: 4px;
      width: 80px;
      margin-top: 59px;
      z-index: 0;
      transition: 0.2s ease-in-out;

      &::before {
        content: '';
        display: block;
        width: 48px;
        height: 4px;
        margin: 0 auto;
        background: var(--prev-color-primary);
      }
    }
    .columns-card {
      @extend .columns-round;
      top: 0;
      height: 4px;
      border-radius: 0;
      margin-top: 59px;
    }
  }
}
:deep(.el-scrollbar) {
  height: 66px;
}
:deep(.el-scrollbar__bar.is-horizontal) {
  display: none;
}
:deep(.el-scrollbar__thumb) {
  display: none;
}
</style>
