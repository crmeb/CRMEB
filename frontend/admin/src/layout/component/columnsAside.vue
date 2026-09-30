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
            <menu-icon :icon="v.icon" />
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
              <menu-icon :icon="v.icon" />
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
const columnsAsideOffsetTopRefs = ref(null);
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
});

function onRoutesListChange() {
  setFilterRoutes();
}

// 设置菜单高亮位置移动
function setColumnsAsideMove(k) {
  if (k === undefined) return false;
  const els = columnsAsideOffsetTopRefs.value;
  // 菜单数据未就绪时 ref 列表可能为空，跳过本次位移避免报错
  if (!els || !els[k]) return false;
  liIndex.value = k;
  columnsAsideActiveRef.value.style.top = `${els[k].offsetTop + difference.value}px`;
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
  // routeSplit = path.split('/');
  // routeSplit.shift();
  // const routeFirst = `/${routeSplit[0]}`;
  const currentSplitRoute = columnsAsideList.value.find((v) => v.path === path);
  if (!currentSplitRoute) {
    // onColumnsAsideDown(0);
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
    // 圆角滑块高 44px（项高 50px），top 下移 3px 使滑块在项内垂直居中；卡片滑块顶满 50px 项高，无需偏移。
    // 设置面板 radio 下发 kebab-case（columns-round/columns-card），原驼峰比较 columnsRound 永不匹配，一并修正
    val === 'columns-round' ? (difference.value = 3) : (difference.value = 0);
  },
  { immediate: true },
);

// 监听路由的变化
watch(
  () => route.fullPath,
  () => {
    const to = route;
    setColumnsMenuHighlight(to.path);
    let HeadName = getHeaderName(to, columnsAsideList.value);
    let asideList = getMenuSider(columnsAsideList.value, HeadName)[0]?.children;
    const resData = setSendChildren(HeadName);
    if (resData.item) {
      onColumnsAsideDown(resData.item[0].k);
      bus.emit('oneCatName', resData.item[0].title);
    }
    // 无菜单归属的路由保持当前模块高亮，避免漂移后点击已激活模块无效
    menusStore.setChildMenuList(asideList || []);
  },
);
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
      /* 一级菜单图标统一 16px：iconfont 菜单图标组件默认 16px，EP 图标无 .el-menu 上下文会回落 14px */
      font-size: 16px;
      text-align: center;
      display: flex;
      cursor: pointer;
      position: relative;
      z-index: 1;
      .columns-vertical {
        margin: auto;

        /* 垂直一级菜单中图标单独占一行，不能继承行内菜单的右间距，否则视觉中心会向左偏移
           （SFC 样式块内不用 // 行注释：sass 之前的 postcss 解析会报错） */
        :deep(.menu-icon) {
          margin-right: 0;
        }

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
      /* 悬停反馈：以菜单文字色衍生 8% 半透明底，深/浅主题自动适配；不占用选中态主题蓝 */
      &:hover {
        background: color-mix(in srgb, var(--prev-bg-columnsMenuBarColor) 8%, transparent);
      }
    }
    .layout-columns {
      transition: 0.3s ease-in-out;
    }
    .layout-columns-active,
    .layout-columns-active a {
      color: var(--prev-bg-columnsMenuActiveColor);
      transition: 0.3s ease-in-out;
    }

    /* 圆角选中滑块：项高 50px 内上下各留 3px、左右各留 5px，圆角 6px 与全站控件圆角一致；卡片风格由 .columns-card 覆盖回全宽直角 */
    .columns-round {
      background: var(--prev-color-primary);
      position: absolute;
      left: 50%;
      top: 3px;
      height: 44px;
      width: calc(100% - 10px);
      transform: translateX(-50%);
      z-index: 0;
      transition: 0.3s ease-in-out;
      border-radius: 6px;
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
