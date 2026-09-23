<template>
  <el-main class="layout-main">
    <el-scrollbar
      class="layout-scrollbar"
      ref="layoutScrollbarRef"
      v-show="!currentRouteMeta.isLink && !currentRouteMeta.isIframe"
      :style="{ minHeight: `calc(100vh - ${headerHeight}` }"
    >
      <LayoutParentView />
      <Footers v-if="getThemeConfig.isFooter" />
    </el-scrollbar>
    <Links
      :style="{ height: `calc(100vh - ${headerHeight}` }"
      :meta="currentRouteMeta"
      v-if="currentRouteMeta.isLink && !currentRouteMeta.isIframe"
    />
    <Iframes
      :style="{ height: `calc(100vh - ${headerHeight}` }"
      :meta="currentRouteMeta"
      v-if="currentRouteMeta.isLink && currentRouteMeta.isIframe && isShowLink"
      @getCurrentRouteMeta="onGetCurrentRouteMeta"
    />
  </el-main>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import LayoutParentView from '@/layout/routerView/parent.vue';
import Footers from '@/layout/footer/index.vue';
import Links from '@/layout/routerView/link.vue';
import Iframes from '@/layout/routerView/iframes.vue';
import { useThemeConfigStore } from '@/store/module/themeConfig';

defineOptions({ name: 'layoutMain' });

const emit = defineEmits(['getCurrentRouteMeta']);

const route = useRoute();
const themeConfigStore = useThemeConfigStore();

const layoutScrollbarRef = ref(null);
const headerHeight = ref('');
const currentRouteMeta = ref({});
const isShowLink = ref(false);

// 获取布局配置信息
const getThemeConfig = computed(() => themeConfigStore.themeConfig);

onMounted(() => {
  initHeaderHeight();
  initCurrentRouteMeta(route.meta);
});

// 初始化当前路由 meta 信息
function initCurrentRouteMeta(meta) {
  isShowLink.value = false;
  currentRouteMeta.value = meta;
  setTimeout(() => {
    isShowLink.value = true;
  }, 100);
}

// 设置 main 的高度
function initHeaderHeight() {
  let { isTagsview } = themeConfigStore.themeConfig;
  if (isTagsview) return (headerHeight.value = `84px`);
  else return (headerHeight.value = `50px`);
}

// 子组件触发更新
function onGetCurrentRouteMeta() {
  initCurrentRouteMeta(route.meta);
}

// 监听 store 数据变化
watch(
  () => themeConfigStore.themeConfig,
  (val) => {
    headerHeight.value = val.isTagsview ? '84px' : '50px';
    if (val.isFixedHeaderChange !== val.isFixedHeader) {
      if (!layoutScrollbarRef.value) return false;
      layoutScrollbarRef.value.update();
    }
  },
  { deep: true },
);

// 监听路由的变化
watch(
  () => route.fullPath,
  () => {
    initCurrentRouteMeta(route.meta);
    if (layoutScrollbarRef.value?.wrapRef) layoutScrollbarRef.value.wrapRef.scrollTop = 0;
  },
);
</script>
