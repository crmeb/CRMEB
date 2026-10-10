<template>
  <div class="layout-navbars-breadcrumb">
    <!-- {{[...breadCrumbList,...crumbPast]}} -->
    <i
      v-if="collapseShow"
      class="layout-navbars-breadcrumb-icon"
      :class="getThemeConfig.isCollapse ? 'el-icon-s-unfold' : 'el-icon-s-fold'"
      v-db-click
      @click="onThemeConfigChange"
    ></i>
    <el-breadcrumb class="layout-navbars-breadcrumb-hide" v-if="isShowcrumb" :style="{ display: isShowBreadcrumb }">
      <transition-group name="breadcrumb">
        <el-breadcrumb-item v-for="(v, k) in crumbList" :key="v.path">
          <span v-if="k == 1" class="layout-navbars-breadcrumb-span">
            <Icon
              :type="v.icon"
              class="ivu-icon layout-navbars-breadcrumb-iconfont"
              v-if="getThemeConfig.isBreadcrumbIcon"
            />{{ $t(v.title) }}
          </span>
          <a v-else v-db-click @click.prevent="onBreadcrumbClick(v)">
            <Icon
              :type="v.icon"
              class="ivu-icon layout-navbars-breadcrumb-iconfont"
              v-if="getThemeConfig.isBreadcrumbIcon"
            />{{ $t(v.title) }}
          </a>
        </el-breadcrumb-item>
      </transition-group>
    </el-breadcrumb>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Local } from '@/utils/storage.js';
import { useMenusStore } from '@/store/module/menus';
import { useRoutesListStore } from '@/store/module/routesList';
import { useThemeConfigStore } from '@/store/module/themeConfig';

defineOptions({ name: 'layoutBreadcrumb' });

const route = useRoute();
const router = useRouter();
const menusStore = useMenusStore();
const routesListStore = useRoutesListStore();
const themeConfigStore = useThemeConfigStore();

const breadcrumbList = ref([]);
const routeSplit = ref([]);
const routeSplitFirst = ref('');
const routeSplitIndex = ref(1);

const breadCrumbList = computed(() => {
  let menuList = menusStore.menusName;
  // 优先使用 activeMenu 匹配（与原 getMenuopen 语义一致）
  const matchPath = (route.meta && route.meta.activeMenu) || route.path;
  // 在菜单树上递归查找目标节点并取其真实祖先链（不含自身），
  // 避免按 path 扁平匹配时空路径目录（path 为 ''）被误命中为祖先，导致面包屑分组错乱
  let ancestors = [];
  const find = (menus, chain) => {
    for (const menu of menus) {
      if (menu.path === matchPath) {
        ancestors = chain;
        return true;
      }
      if (menu.children && menu.children.length && find(menu.children, chain.concat(menu))) {
        return true;
      }
    }
    return false;
  };
  find(menuList, []);
  return ancestors;
});

const crumbPast = computed(() => {
  // 只收集 path 命中的菜单节点引用：原先整树扁平化 + 逐属性深克隆后过滤，
  // 每次面包屑重算都白付整棵树的克隆成本，而模板只读 path/icon/title/redirect
  const selectMenu = [];
  const walk = (menus) => {
    for (const menu of menus) {
      if (route.path === menu.path) selectMenu.push(menu);
      if (menu.children && menu.children.length) walk(menu.children);
    }
  };
  walk(menusStore.menusName);
  return selectMenu;
});

// 菜单数据中同一 path 可能挂多个目录，拼接去重避免 v-for key 重复
const crumbList = computed(() => {
  const seen = new Set();
  return [...breadCrumbList.value, ...crumbPast.value].filter((v) => {
    if (seen.has(v.path)) return false;
    seen.add(v.path);
    return true;
  });
});

// 获取布局配置信息
const getThemeConfig = computed(() => themeConfigStore.themeConfig);

// 动态设置经典、横向布局不显示
const isShowBreadcrumb = computed(() => {
  const { layout, isBreadcrumb } = themeConfigStore.themeConfig;
  if (layout === 'transverse' || layout === 'classic') {
    return 'none';
  } else {
    return isBreadcrumb ? '' : 'none';
  }
});

const isShowcrumb = computed(() => {
  const { layout } = themeConfigStore.themeConfig;
  if (layout === 'transverse' || layout === 'classic') {
    return false;
  } else {
    return true;
  }
});

const collapseShow = computed(() => ['defaults', 'columns'].includes(themeConfigStore.themeConfig.layout));

onMounted(() => {
  initRouteSplit(route.path);
});

// breadcrumb 当前项点击时
function onBreadcrumbClick(v) {
  const { redirect, path } = v;
  if (redirect) router.push(redirect);
  else router.push(path);
}

// breadcrumb icon 点击菜单展开与收起
function onThemeConfigChange() {
  if (
    themeConfigStore.themeConfig.layout == 'columns' &&
    !menusStore.childMenuList.length &&
    themeConfigStore.themeConfig.isCollapse
  ) {
    return;
  }
  themeConfigStore.themeConfig.isCollapse = !themeConfigStore.themeConfig.isCollapse;
  setLocalThemeConfig();
}

// 存储布局配置
function setLocalThemeConfig() {
  Local.remove('themeConfigPrev');
  Local.set('themeConfigPrev', themeConfigStore.themeConfig);
}

// 递归设置 breadcrumb
function getBreadcrumbList(arr) {
  arr.map((item) => {
    routeSplit.value.map((v, k, arrs) => {
      if (routeSplitFirst.value === item.path) {
        routeSplitFirst.value += `/${arrs[routeSplitIndex.value]}`;
        breadcrumbList.value.push(item);
        routeSplitIndex.value++;
        if (item.children) getBreadcrumbList(item.children);
      }
    });
  });
}

// 当前路由分割处理
function initRouteSplit(path) {
  breadcrumbList.value = [
    {
      path: '/',
      meta: {
        title: routesListStore.routesList[0].title,
        icon: routesListStore.routesList[0].icon,
      },
    },
  ];
  //   routeSplit.value = path.split('/');
  //   routeSplit.value.shift();
  routeSplitFirst.value = path;
  routeSplitIndex.value = 1;
  getBreadcrumbList(routesListStore.routesList);
}
</script>

<style scoped lang="scss">
.layout-navbars-breadcrumb {
  // flex: 1;
  height: inherit;
  display: flex;
  align-items: center;
  padding-left: 15px;
  .layout-navbars-breadcrumb-icon {
    cursor: pointer;
    font-size: 18px;
    margin-right: 15px;
    color: var(--prev-bg-topBarColor);
    opacity: 0.8;
    &:hover {
      opacity: 1;
    }
  }
  .layout-navbars-breadcrumb-span {
    opacity: 0.7;
    color: var(--prev-bg-topBarColor);
  }
  .layout-navbars-breadcrumb-iconfont {
    font-size: 14px;
    margin-right: 5px;
  }
}
</style>
