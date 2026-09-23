<template>
  <div class="el-menu-horizontal-warp">
    <el-scrollbar @wheel.prevent="onElMenuHorizontalScroll" ref="elMenuHorizontalScrollRef">
      <el-menu
        router
        :default-active="activePath || defaultActive"
        background-color="transparent"
        mode="horizontal"
        @select="onHorizontalSelect"
      >
        <template v-for="val in menuList" :key="val.path">
          <el-sub-menu :index="val.path" v-if="val.is_show && val.children && val.children.length > 0">
            <template #title>
              <MenuIcon :icon="val.icon" />
              <span>{{ $t(val.title) }}</span>
            </template>
            <SubItem :chil="val.children" />
          </el-sub-menu>
          <template v-else-if="val.is_show">
            <el-menu-item :index="val.path">
              <template v-if="!val.isLink || (val.isLink && val.isIframe)" #title>
                <MenuIcon :icon="val.icon" />
                {{ $t(val.title) }}
              </template>
              <template v-else #title>
                <a :href="val.isLink" target="_blank">
                  <MenuIcon :icon="val.icon" />
                  {{ $t(val.title) }}
                </a>
              </template>
            </el-menu-item>
          </template>
        </template>
      </el-menu>
    </el-scrollbar>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import SubItem from '@/layout/navMenu/subItem.vue';
import MenuIcon from '@/components/menuIcon/index.vue';
import bus from '@/utils/bus';
import { storeToRefs } from 'pinia';
import { useMenuStore } from '@/store/module/menu';
import { useRoutesListStore } from '@/store/module/routesList';
import { useThemeConfigStore } from '@/store/module/themeConfig';

defineOptions({ name: 'navMenuHorizontal' });

const props = defineProps({
  menuList: {
    type: Array,
    default: () => [],
  },
});

const route = useRoute();
const menuStore = useMenuStore();
const routesListStore = useRoutesListStore();
const themeConfigStore = useThemeConfigStore();

const { activePath } = storeToRefs(menuStore);

const elMenuHorizontalScrollRef = ref(null);
const defaultActive = ref(null);

onMounted(() => {
  initElMenuOffsetLeft();
  setCurrentRouterHighlight(route.path);
});

// 设置横向滚动条可以鼠标滚轮滚动
function onElMenuHorizontalScroll(e) {
  const eventDelta = e.wheelDelta || -e.deltaY * 40;
  elMenuHorizontalScrollRef.value.wrapRef.scrollLeft =
    elMenuHorizontalScrollRef.value.wrapRef.scrollLeft + eventDelta / 4;
}

// 初始化数据，页面刷新时，滚动条滚动到对应位置
function initElMenuOffsetLeft() {
  nextTick(() => {
    let els = document.querySelector('.el-menu.el-menu--horizontal li.is-active');
    if (!els) return false;
    elMenuHorizontalScrollRef.value.wrapRef.scrollLeft = els.offsetLeft;
  });
}

// 路由过滤递归函数
function filterRoutesFun(arr) {
  return arr
    .filter((item) => !item.isHide)
    .map((item) => {
      item = Object.assign({}, item);
      if (item.children) item.children = filterRoutesFun(item.children);
      return item;
    });
}

// 传送当前子级数据到菜单中
function setSendClassicChildren(path) {
  const currentPathSplit = path.split('/');
  let currentData = {};
  filterRoutesFun(routesListStore.routesList).map((v, k) => {
    if (v.path === `/${currentPathSplit[1]}`) {
      v['k'] = k;
      currentData['item'] = [{ ...v }];
      currentData['children'] = [{ ...v }];
      if (v.children) currentData['children'] = v.children;
    }
  });
  return currentData;
}

// 菜单激活回调
function onHorizontalSelect(path) {
  bus.emit('setSendClassicChildren', setSendClassicChildren(path));
}

// 设置页面当前路由高亮
function setCurrentRouterHighlight(path) {
  const currentPathSplit = path.split('/');
  if (themeConfigStore.themeConfig.layout === 'classic') {
    defaultActive.value = `/${currentPathSplit[1]}`;
  } else {
    defaultActive.value = path;
  }
}

// 监听路由的变化
watch(
  () => route.fullPath,
  () => {
    setCurrentRouterHighlight(route.path);
  },
);
</script>

<style scoped lang="scss">
:deep(.el-scrollbar__bar.is-horizontal) {
  height: 0;
}
.el-menu-horizontal-warp {
  flex: 1;
  overflow: hidden;
  margin-right: 30px;
  :deep(.el-scrollbar__bar.is-vertical) {
    display: none;
  }
  :deep(.el-scrollbar__wrap) {
    overflow-y: hidden !important;
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
</style>
