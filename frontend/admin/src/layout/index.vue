<template>
  <!-- 判断显示哪个组件 -->
  <Mains v-if="headMenuNoShow" />
  <!-- 根据主题配置中的布局类型来判断显示哪个组件 -->
  <Defaults v-else-if="getThemeConfig.layout === 'defaults'" />
  <Classic v-else-if="getThemeConfig.layout === 'classic'" />
  <Transverse v-else-if="getThemeConfig.layout === 'transverse'" />
  <Columns v-else-if="getThemeConfig.layout === 'columns'" />
</template>

<script setup>
import { ref, computed, watch, onUnmounted, defineAsyncComponent } from 'vue';
import { useRoute } from 'vue-router';
import { Local } from '@/utils/storage.js';
import bus from '@/utils/bus';
import { getNewTagList } from '@/libs/util';
import { useAppStore } from '@/store/module/app';
import { useThemeConfigStore } from '@/store/module/themeConfig';

defineOptions({ name: 'layout' });

const Defaults = defineAsyncComponent(() => import('@/layout/main/defaults.vue'));
const Classic = defineAsyncComponent(() => import('@/layout/main/classic.vue'));
const Transverse = defineAsyncComponent(() => import('@/layout/main/transverse.vue'));
const Columns = defineAsyncComponent(() => import('@/layout/main/columns.vue'));
const Mains = defineAsyncComponent(() => import('@/layout/component/main.vue'));

const route = useRoute();
const appStore = useAppStore();
const themeConfigStore = useThemeConfigStore();

const headMenuNoShow = ref(false);

// 获取布局配置信息
const getThemeConfig = computed(() => themeConfigStore.themeConfig);

const tagNavList = computed(() => appStore.tagNavList);

watch(
  () => route.fullPath,
  () => {
    const newRoute = route;
    headMenuNoShow.value = route.meta.fullScreen;
    const { name, query, params, meta, path } = newRoute;
    appStore.addTag({
      route: { name, query, params, meta, path },
      type: 'push',
    });
    appStore.setBreadCrumb(newRoute);
    appStore.setTagNavList(getNewTagList(tagNavList.value, newRoute));
  },
);

// created 时机
headMenuNoShow.value = route.meta.fullScreen;
onLayoutResize();
window.addEventListener('resize', onLayoutResize);

// 窗口大小改变时(适配移动端)
function onLayoutResize() {
  if (!Local.get('oldLayout')) Local.set('oldLayout', themeConfigStore.themeConfig.layout);
  const clientWidth = document.body.clientWidth;
  if (clientWidth < 1000) {
    themeConfigStore.themeConfig.isCollapse = false;
    bus.emit('layoutMobileResize', {
      layout: 'defaults',
      clientWidth,
    });
  } else {
    bus.emit('layoutMobileResize', {
      layout: Local.get('oldLayout') ? Local.get('oldLayout') : themeConfigStore.themeConfig.layout,
      clientWidth,
    });
  }
}

// 原代码此钩子名拼写为 distroyed（未生效），按意图在卸载时移除窗口监听
onUnmounted(() => {
  window.removeEventListener('resize', onLayoutResize);
});
</script>
