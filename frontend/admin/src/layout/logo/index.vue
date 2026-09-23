<template>
  <div
    class="layout-logo"
    v-if="getThemeConfig.layout !== 'columns' && !getThemeConfig.isCollapse"
    v-db-click
    @click="onThemeConfigChange"
  >
    <img v-if="maxLogo" class="layout-logo-medium-img" :src="maxLogo" />
  </div>
  <div class="layout-logo-size" v-else v-db-click @click="onThemeConfigChange">
    <img v-if="minLogo" class="layout-logo-size-img" :src="minLogo" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { getLogo } from '@/api/common';
import { useMenusStore } from '@/store/module/menus';
import { useThemeConfigStore } from '@/store/module/themeConfig';

defineOptions({ name: 'layoutLogo' });

const menusStore = useMenusStore();
const themeConfigStore = useThemeConfigStore();

const minLogo = ref('');
const maxLogo = ref('');

// 获取布局配置信息
const getThemeConfig = computed(() => themeConfigStore.themeConfig);

// 设置 logo 是否显示
const setShowLogo = computed(() => {
  let { isCollapse, layout } = themeConfigStore.themeConfig;
  return !isCollapse || layout === 'classic' || document.body.clientWidth < 1000;
});

onMounted(() => {
  fetchLogo();
});

// logo 点击实现菜单展开/收起
function onThemeConfigChange() {
  if (
    themeConfigStore.themeConfig.layout == 'columns' &&
    !menusStore.childMenuList.length &&
    themeConfigStore.themeConfig.isCollapse
  )
    return;
  if (themeConfigStore.themeConfig.layout === 'transverse' || themeConfigStore.themeConfig.layout === 'classic')
    return false;
  themeConfigStore.themeConfig.isCollapse = !themeConfigStore.themeConfig.isCollapse;
}

function fetchLogo() {
  getLogo().then((res) => {
    minLogo.value = res.data.logo_square;
    maxLogo.value = res.data.logo;
  });
}
</script>

<style scoped lang="scss">
.layout-logo {
  width: 180px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  //   box-shadow: 0px 1px 4px rgba(0, 21, 41, 2%);
  color: var(--prev-color-primary);
  font-size: 16px;
  cursor: pointer;
  animation: logoAnimation 0.3s ease-in-out;
  &:hover {
    span {
      opacity: 0.9;
    }
  }
  &-medium-img {
    width: 100%;
    height: 50px;
    margin-right: 5px;
    position: relative;
    top: 2px;
  }
}
.layout-logo-size {
  width: 50px;
  height: 50px;
  display: flex;
  cursor: pointer;
  margin: auto;

  &-img {
    width: 50px;
    height: 50px;
    margin: auto;
    animation: logoAnimation 0.3s ease-in-out;
  }
}
</style>
