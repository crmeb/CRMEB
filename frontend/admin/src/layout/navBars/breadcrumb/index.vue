<template>
  <div class="layout-navbars-breadcrumb-index">
    <Logo v-if="setIsShowLogo" />
    <Breadcrumb />
    <Horizontal :menuList="menuList" v-if="isLayoutTransverse" />
    <transverseAside v-if="isLayoutClassic" />
    <User />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import Breadcrumb from '@/layout/navBars/breadcrumb/breadcrumb.vue';
import User from '@/layout/navBars/breadcrumb/user.vue';
import Logo from '@/layout/logo/index.vue';
import Horizontal from '@/layout/navMenu/horizontal.vue';
import transverseAside from '@/layout/component/transverseAside.vue';
import bus from '@/utils/bus';
import { useRoutesListStore } from '@/store/module/routesList';
import { useThemeConfigStore } from '@/store/module/themeConfig';

defineOptions({ name: 'layoutNavBars' });

const routesListStore = useRoutesListStore();
const themeConfigStore = useThemeConfigStore();

const menuList = ref([]);

// 设置 logo 是否显示
const setIsShowLogo = computed(() => {
  let { isShowLogo, layout } = themeConfigStore.themeConfig;
  return (isShowLogo && layout === 'classic') || (isShowLogo && layout === 'transverse');
});

// 设置是否显示横向菜单
const isLayoutTransverse = computed(() => {
  let { layout, isClassicSplitMenu } = themeConfigStore.themeConfig;
  return layout === 'transverse' || (isClassicSplitMenu && layout === 'classic');
});

const isLayoutClassic = computed(() => {
  let { layout } = themeConfigStore.themeConfig;
  return layout === 'classic';
});

onMounted(() => {
  setFilterRoutes();
  bus.on('routesListChange', onRoutesListChange);
});

onBeforeUnmount(() => {
  bus.off('routesListChange', onRoutesListChange);
});

function onRoutesListChange() {
  setFilterRoutes();
}

// 设置路由的过滤
function setFilterRoutes() {
  menuList.value = filterRoutesFun(routesListStore.routesList);
}

// 设置路由的过滤递归函数
function filterRoutesFun(arr) {
  return arr
    .filter((item) => item.path)
    .map((item) => {
      item = Object.assign({}, item);
      if (item.children) item.children = filterRoutesFun(item.children);
      return item;
    });
}

// 监听 store 数据变化（原 watch '$store.state'：路由列表与本地菜单长度不一致时重新过滤）
watch(
  () => routesListStore.routesList,
  (val) => {
    if (val.length === menuList.value.length) return false;
    setFilterRoutes();
  },
);
</script>

<style scoped lang="scss">
.layout-navbars-breadcrumb-index {
  height: 64px;
  display: flex;
  align-items: center;
  // padding-right: 15px;
  overflow: hidden;
  background: var(--prev-bg-topBar);
  border-bottom: 1px solid var(--prev-border-color-lighter);
}
</style>
