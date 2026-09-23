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
  <el-drawer
    v-model="getThemeConfig.isCollapse"
    :with-header="false"
    direction="ltr"
    size="180px"
    class="layout-aside-drawer"
    v-else
  >
    <el-aside class="layout-aside w100 h100">
      <Logo v-if="setShowLogo && menuList.length" />
      <el-scrollbar class="flex-auto" ref="layoutAsideRef">
        <Vertical :menuList="menuList" />
      </el-scrollbar>
    </el-aside>
  </el-drawer>
</template>

<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue';
import Vertical from '@/layout/navMenu/vertical.vue';
import Logo from '@/layout/logo/index.vue';
import bus from '@/utils/bus';
import { useAppStore } from '@/store/module/app';
import { useMenusStore } from '@/store/module/menus';
import { useRoutesListStore } from '@/store/module/routesList';
import { useThemeConfigStore } from '@/store/module/themeConfig';

defineOptions({ name: 'layoutAside' });

const emit = defineEmits(['routesListChange']);

const appStore = useAppStore();
const menusStore = useMenusStore();
const routesListStore = useRoutesListStore();
const themeConfigStore = useThemeConfigStore();

const layoutAsideRef = ref(null);
const clientWidth = ref('');
const catName = ref('');

const adminTitle = computed(() => appStore.adminTitle || '');

// 设置左侧菜单的具体宽度
const menuList = computed(() => menusStore.childMenuList);

// 侧栏菜单有无联动折叠状态（原写在 computed 内的副作用移至 watch）
watch(
  () => menusStore.childMenuList.length,
  (len) => {
    themeConfigStore.themeConfig.isCollapse = len <= 0;
  },
  { immediate: true },
);

const setCollapseWidth = computed(() => {
  let { layout, isCollapse } = themeConfigStore.themeConfig;
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
    // 其它布局收起时保留一级菜单图标，宽度收窄为 64px，悬停弹出二三级菜单；
    // 菜单列表为空时（加载中/联动收起）保持隐藏
    if (isCollapse && menuList.value.length) {
      return ['layout-aside-width64', asideBrColor];
    }
    if (isCollapse) {
      return ['layout-aside-width1', asideBrColor];
    }
    return ['layout-aside-width-default', asideBrColor, layout === 'classic' ? 'pt8' : ''];
  }
});

// 设置 logo 是否显示
const setShowLogo = computed(() => {
  let { isShowLogo, layout } = themeConfigStore.themeConfig;
  return (isShowLogo && layout === 'defaults') || (isShowLogo && layout === 'columns');
});

// 获取布局配置信息
const getThemeConfig = computed(() => themeConfigStore.themeConfig);

// created 时机
initMenuFixed(document.body.clientWidth);
setFilterRoutes();
bus.on('layoutMobileResize', onLayoutMobileResize);
bus.on('oneCatName', onOneCatName);
// 菜单滚动条监听
bus.on('updateElScrollBar', onUpdateElScrollBar);
if (themeConfigStore.themeConfig.layout !== 'columns') {
  bus.on('routesListChange', onRoutesListChange);
}

// 页面销毁时：取消菜单滚动条等事件监听
onBeforeUnmount(() => {
  bus.off('routesListChange', onRoutesListChange);
  bus.off('layoutMobileResize', onLayoutMobileResize);
  bus.off('oneCatName', onOneCatName);
  bus.off('updateElScrollBar', onUpdateElScrollBar);
});

function onLayoutMobileResize(res) {
  initMenuFixed(res.clientWidth);
}

function onOneCatName(name) {
  catName.value = name;
}

function onUpdateElScrollBar() {
  setTimeout(() => {
    layoutAsideRef.value?.update?.();
  }, 300);
}

function onRoutesListChange() {
  setFilterRoutes();
}

// 设置/过滤路由（非静态路由/是否显示在菜单中）
function setFilterRoutes() {
  if (themeConfigStore.themeConfig.layout === 'columns') return false;
  menusStore.setChildMenuList(filterRoutesFun(routesListStore.routesList));
}

// 设置/过滤路由 递归函数
function filterRoutesFun(arr) {
  return arr
    .filter((item) => item.path)
    .map((item) => {
      item = Object.assign({}, item);
      if (item.children) item.children = filterRoutesFun(item.children);
      return item;
    });
}

// 设置菜单导航是否固定（移动端）
function initMenuFixed(width) {
  clientWidth.value = width;
  emit('routesListChange');
}
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
