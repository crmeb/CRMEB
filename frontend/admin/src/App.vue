<template>
  <div id="app">
    <el-config-provider :locale="elementLocale">
      <router-view />
      <Setings ref="setingsRef" />
    </el-config-provider>
    <!-- 检测版本更新 -->
    <!-- <Upgrade v-if="isVersion" /> -->
  </div>
</template>

<script setup>
import { ref, computed, provide, onMounted, onUnmounted, nextTick } from 'vue';
import { useRoute } from 'vue-router';
import zhCn from 'element-plus/es/locale/lang/zh-cn';
import en from 'element-plus/es/locale/lang/en';
import zhTw from 'element-plus/es/locale/lang/zh-tw';
import Setings from '@/layout/navBars/breadcrumb/setings.vue';
import setting from '@/setting';
import { Local } from '@/utils/storage.js';
import config from '../package.json';
import bus from '@/utils/bus';
import { useMediaStore } from '@/store/module/media';
import { useThemeConfigStore } from '@/store/module/themeConfig';

defineOptions({ name: 'app' });

const route = useRoute();
const setingsRef = ref(null);
const mediaStore = useMediaStore();
const themeConfigStore = useThemeConfigStore();

const isVersion = ref(false);
const isRouterAlive = ref(true);

const localeMap = { 'zh-cn': zhCn, en, 'zh-tw': zhTw };
const elementLocale = computed(() => localeMap[themeConfigStore.themeConfig.globalI18n] || zhCn);

provide('reload', reload);

function handleMatchMedia() {
  const matchMedia = window.matchMedia;

  if (matchMedia('(max-width: 600px)').matches) {
    var deviceWidth = document.documentElement.clientWidth || window.innerWidth;
    let css = 'calc(100vw/7.5)';
    document.documentElement.style.fontSize = css;
    mediaStore.setDevice('Mobile');
  } else if (matchMedia('(max-width: 992px)').matches) {
    mediaStore.setDevice('Tablet');
  } else {
    mediaStore.setDevice('Desktop');
  }
}

function reload() {
  isRouterAlive.value = false;
  nextTick(() => {
    isRouterAlive.value = true;
  });
}

// 布局配置弹窗打开
function onOpenSetingsDrawer() {
  setingsRef.value && setingsRef.value.openDrawer();
}

// 获取缓存中的布局配置
function getLayoutThemeConfig() {
  if (Local.get('themeConfigPrev')) {
    themeConfigStore.setThemeConfig(Local.get('themeConfigPrev'));
    document.documentElement.style.cssText = Local.get('themeConfigStyle');
  } else {
    Local.set('themeConfigPrev', themeConfigStore.themeConfig);
  }
}

function getVersion() {
  isVersion.value = false;
  if (route.path !== `${setting.routePre}/login` && route.path !== '/') {
    if ((Local.get('version') && Local.get('version') !== config.version) || !Local.get('version'))
      isVersion.value = true;
  }
}

onMounted(() => {
  handleMatchMedia();
  bus.on('openSetingsDrawer', onOpenSetingsDrawer);
  getLayoutThemeConfig();
  nextTick((e) => {
    // getVersion();
  });
});

onUnmounted(() => {
  bus.off('openSetingsDrawer', onOpenSetingsDrawer);
});
</script>
<style type="text/css">
.icon {
  width: 1em;
  height: 1em;
  vertical-align: -0.15em;
  fill: currentColor;
  overflow: hidden;
}
</style>
<style lang="scss">
html,
body {
  width: 100%;
  height: 100%;
  overflow: hidden;
  margin: 0;
  padding: 0;
}
#app {
  width: 100%;
  height: 100%;
  font-family: PingFang SC, Arial, Microsoft YaHei, sans-serif;
}
.right-box .ivu-color-picker .ivu-select-dropdown {
  position: absolute;
  // width: 300px !important;
  left: -73px !important;
}
</style>
