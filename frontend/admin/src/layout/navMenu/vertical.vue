<template>
  <div>
    <el-menu
      router
      :class="setColumnsAsideStyle"
      background-color="transparent"
      :default-active="activePath || defaultActive"
      :collapse="setIsCollapse"
      :unique-opened="getThemeConfig.isUniqueOpened"
      :collapse-transition="false"
    >
      <template v-for="val in menuList" :key="val.path">
        <el-sub-menu :index="val.path" v-if="val.is_show && val.children && val.children.length > 0">
          <template #title>
            <menu-icon :icon="val.icon" />
            <span>{{ $t(val.title) }}</span>
          </template>
          <SubItem :chil="val.children" />
        </el-sub-menu>
        <template v-else-if="val.is_show">
          <el-menu-item :index="val.path">
            <menu-icon :icon="val.icon" />
            <template v-if="!val.isLink || (val.isLink && val.isIframe)" #title>
              <span>{{ $t(val.title) }}</span>
            </template>
            <template v-else #title>
              <a :href="val.isLink" target="_blank">{{ $t(val.title) }}</a>
            </template>
          </el-menu-item>
        </template>
      </template>
    </el-menu>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import { storeToRefs } from 'pinia';
import SubItem from '@/layout/navMenu/subItem.vue';
import MenuIcon from '@/components/menuIcon/index.vue';
import { useMenuStore } from '@/store/module/menu';
import { useThemeConfigStore } from '@/store/module/themeConfig';

defineOptions({ name: 'navMenuVertical' });

const props = defineProps({
  menuList: {
    type: Array,
    default() {
      return [];
    },
  },
});

const route = useRoute();
const menuStore = useMenuStore();
const themeConfigStore = useThemeConfigStore();

const { activePath } = storeToRefs(menuStore);

const defaultActive = ref(route.path);
const onRoutes = ref('');

// 设置分栏高亮风格
const setColumnsAsideStyle = computed(() => themeConfigStore.themeConfig.columnsAsideStyle);

// 获取布局配置信息
const getThemeConfig = computed(() => themeConfigStore.themeConfig);

// 设置左侧菜单是否展开/收起
const setIsCollapse = computed(() => {
  return document.body.clientWidth < 1000 ? false : themeConfigStore.themeConfig.isCollapse;
});

// 监听路由的变化
watch(
  () => route.fullPath,
  () => {
    defaultActive.value = route.path;
    const clientWidth = document.body.clientWidth;
    if (clientWidth < 1000) themeConfigStore.themeConfig.isCollapse = false;
  },
);
</script>
<style lang="scss" scoped>
:deep(.center) {
  text-align: center;
  margin-right: 0 !important;
  margin-left: 5px;
}
// :deep(.el-submenu__title){
//   display: flex;
//   justify-content: center;
//   align-items: center;
// }
</style>
