<template>
  <div ref="tagsViews" class="layout-navbars-tagsview">
    <i v-if="scrollTagIcon" class="direction el-icon-arrow-left" v-db-click @click="scrollTag('left')"></i>
    <el-scrollbar ref="scrollbarRef" @wheel.prevent="onHandleScroll">
      <ul class="layout-navbars-tagsview-ul" :class="setTagsStyle" ref="tagsUlRef">
        <li
          v-for="(v, k) in tagsViewList"
          :key="k"
          class="layout-navbars-tagsview-ul-li"
          :data-name="v.name"
          :class="{ 'is-active': v.path === tagsRoutePath }"
          @contextmenu.prevent="onContextmenu(v, $event)"
          v-db-click
          @click="onTagsClick(v, k)"
          ref="tagsRefs"
        >
          <i
            class="layout-navbars-tagsview-ul-li-iconfont font14 is-tagsview-icon"
            :class="v.icon"
            v-if="v.path !== tagsRoutePath && getThemeConfig.isTagsviewIcon"
          ></i>
          <span>{{ $t(v.meta.title) }}</span>
          <!-- <i
            class="el-icon-refresh-right layout-navbars-tagsview-ul-li-icon ml5"
            v-if="v.path === tagsRoutePath"
            v-db-click @click.stop="refreshCurrentTagsView(v.path)"
          ></i> -->
          <i
            class="el-icon-close layout-navbars-tagsview-ul-li-icon ml5"
            v-if="!v.isAffix"
            v-db-click
            @click.stop="closeCurrentTagsView(v.path)"
          ></i>
        </li>
      </ul>
    </el-scrollbar>
    <i v-if="scrollTagIcon" class="direction el-icon-arrow-right" v-db-click @click="scrollTag('right')"></i>
    <el-dropdown @command="clickDropdown" v-if="tagsViewList.length > 2">
      <span class="setting-tag el-dropdown-link"><i class="el-icon-menu"></i></span>
      <template #dropdown
        ><el-dropdown-menu>
          <el-dropdown-item v-for="item in dropdownList" :command="item.id" :key="item.id">
            <i :class="item.icon"></i>
            {{ $t(item.txt) }}</el-dropdown-item
          >
        </el-dropdown-menu></template
      >
    </el-dropdown>
    <Contextmenu :dropdown="tagsDropdown" ref="tagsContextmenu" @currentContextmenuClick="onCurrentContextmenuClick" />
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Contextmenu from '@/layout/navBars/tagsView/contextmenu.vue';
import bus from '@/utils/bus';
import { useAppStore } from '@/store/module/app';
import { useMenusStore } from '@/store/module/menus';
import { useRoutesListStore } from '@/store/module/routesList';
import { useThemeConfigStore } from '@/store/module/themeConfig';

defineOptions({ name: 'tagsView' });

const route = useRoute();
const router = useRouter();
const appStore = useAppStore();
const menusStore = useMenusStore();
const routesListStore = useRoutesListStore();
const themeConfigStore = useThemeConfigStore();

const tagsViews = ref(null);
const scrollbarRef = ref(null);
const tagsUlRef = ref(null);
const tagsRefs = ref(null);
const tagsContextmenu = ref(null);

const userInfo = ref({});
const tagsDropdown = ref({
  x: '',
  y: '',
});
const tagsRefsIndex = ref(0);
const tagsRoutePath = ref(route.path);
const dropdownList = ref([
  { id: 0, txt: 'message.tagsView.refresh', affix: false, icon: 'el-icon-refresh-right' },
  { id: 1, txt: 'message.tagsView.close', affix: false, icon: 'el-icon-close' },
  { id: 2, txt: 'message.tagsView.closeOther', affix: false, icon: 'el-icon-circle-close' },
  { id: 3, txt: 'message.tagsView.closeAll', affix: false, icon: 'el-icon-folder-delete' },
]);
const scrollTagIcon = ref(false);

// 获取布局配置信息
const getThemeConfig = computed(() => themeConfigStore.themeConfig);

// 动态设置 tagsView 风格样式
const setTagsStyle = computed(() => themeConfigStore.themeConfig.tagsStyle);

const tagsViewList = computed(() => appStore.tagNavList);

const tagsViewRoutesList = computed(() => appStore.tagNavList);

// created 时机
// 监听非本页面调用 0 刷新当前，1 关闭当前，2 关闭其它，3 关闭全部
bus.on('onCurrentContextmenuClick', onBusCurrentContextmenuClick);

onMounted(() => {
  if (!appStore.tagNavList.length) {
    getTagsViewRoutes();
  }
  if (tagsViews.value?.offsetWidth < scrollbarRef.value.wrapRef.scrollWidth) {
    scrollTagIcon.value = true;
  }
  window.addEventListener('resize', onWindowResize);
});

onUnmounted(() => {
  // 取消非本页面调用监听（fun/tagsView）
  bus.off('onCurrentContextmenuClick', onBusCurrentContextmenuClick);
  window.removeEventListener('resize', onWindowResize);
});

function onBusCurrentContextmenuClick(data) {
  onCurrentContextmenuClick(data);
}

function onWindowResize() {
  if (tagsViews.value?.offsetWidth < scrollbarRef.value.wrapRef.scrollWidth) {
    scrollTagIcon.value = true;
  } else {
    scrollTagIcon.value = false;
  }
}

function clickDropdown(e) {
  let data = { id: e, path: route.path };
  onCurrentContextmenuClick(data);
}

// 获取路由信息
function getRoutesList() {
  return routesListStore.routesList;
}

// 当前的 tagsView 项点击时
function onTagsClick(v, k) {
  tagsRoutePath.value = v.path;
  tagsRefsIndex.value = k;
  router.push(v);
}

// 获取 tagsView 的下标：用于处理 tagsView 点击时的横向滚动
function getTagsRefsIndex(path) {
  if (tagsViewList.value.length > 0) {
    tagsRefsIndex.value = tagsViewList.value.findIndex((item) => item.path === path);
  }
}

// 鼠标滚轮滚动
function onHandleScroll(e) {
  scrollbarRef.value.wrapRef.scrollLeft += e.wheelDelta / 4;
}

function scrollTag(production) {
  let scrollRefs = scrollbarRef.value.wrapRef.scrollWidth;
  let scrollLeft = scrollbarRef.value.wrapRef.scrollLeft;
  if (production === 'left') {
    scrollbarRef.value.wrapRef.scrollLeft = scrollLeft - 300 <= 0 ? 0 : scrollLeft - 300;
  } else {
    scrollbarRef.value.wrapRef.scrollLeft = scrollLeft + 300 >= scrollRefs ? scrollRefs : scrollLeft + 300;
  }
}

// tagsView 横向滚动
function tagsViewmoveToCurrentTag() {
  nextTick(() => {
    const tagsRefsList = tagsRefs.value;
    if (!tagsRefsList) return;
    if (tagsRefsList.length <= 0) return false;
    // 当前 li 元素
    let liDom = tagsRefsList[tagsRefsIndex.value];
    // 当前 li 元素下标
    let liIndex = tagsRefsIndex.value;
    // 当前 ul 下 li 元素总长度
    let liLength = tagsRefsList.length;
    // 最前 li
    let liFirst = tagsRefsList[0];
    // 最后 li
    let liLast = tagsRefsList[tagsRefsList.length - 1];
    // 当前滚动条的值
    let scrollRefs = scrollbarRef.value.wrapRef;
    // 当前滚动条滚动宽度
    let scrollS = scrollRefs.scrollWidth;
    // 当前滚动条偏移宽度
    let offsetW = scrollRefs.offsetWidth;
    // 当前滚动条偏移距离
    let scrollL = scrollRefs.scrollLeft;
    // 上一个 tags li dom
    let liPrevTag = tagsRefsList[tagsRefsIndex.value - 1];
    // 下一个 tags li dom
    let liNextTag = tagsRefsList[tagsRefsIndex.value + 1];
    // 上一个 tags li dom 的偏移距离
    let beforePrevL = '';
    // 下一个 tags li dom 的偏移距离
    let afterNextL = '';
    if (liDom === liFirst) {
      // 头部
      scrollRefs.scrollLeft = 0;
    } else if (liDom === liLast) {
      // 尾部
      scrollRefs.scrollLeft = scrollS - offsetW;
    } else {
      // 非头/尾部
      if (liIndex === 0) beforePrevL = liFirst?.offsetLeft - 5;
      else beforePrevL = liPrevTag?.offsetLeft - 5;
      if (liIndex === liLength) afterNextL = liLast?.offsetLeft + liLast.offsetWidth + 5;
      else afterNextL = liNextTag?.offsetLeft + liNextTag.offsetWidth + 5;
      if (afterNextL > scrollL + offsetW) {
        scrollRefs.scrollLeft = afterNextL - offsetW;
      } else if (beforePrevL < scrollL) {
        scrollRefs.scrollLeft = beforePrevL;
      }
    }
    // 更新滚动条，防止不出现
    updateScrollbar();
  });
}

// 更新滚动条显示
function updateScrollbar() {
  scrollbarRef.value.update();
}

// 递归查找当前路径下的组件信息
function filterCurrentMenu(arr, currentPath, callback) {
  arr.map((item) => {
    if (item.path === currentPath) {
      callback(item);
      return false;
    }
    item = Object.assign({}, item);
    if (item.children) {
      item.children = filterCurrentMenu(item.children, currentPath, callback);
    }
  });
}

// 数组对象去重
function duplicate(arr) {
  let newobj = {};
  arr = arr.reduce((preVal, curVal) => {
    newobj[curVal.path] ? '' : (newobj[curVal.path] = preVal.push(curVal));
    return preVal;
  }, []);
  return arr;
}

// 获取 store 中的 tagsViewRoutes 列表
function getTagsViewRoutes() {
  tagsRoutePath.value = route.path;
  appStore.setTagNavList(menusStore.oneLvMenus);

  initTagsViewList();
}

// 存储 tagsViewList 到浏览器临时缓存中，页面刷新时，保留记录
function addBrowserSetSession(tagNavList) {
  appStore.setTagNavList(tagNavList);
}

// 初始化设置了 tagsView 数据
function initTagsViewList() {
  // if (Session.get('tagsViewList') && themeConfigStore.themeConfig.isCacheTagsView) {
  //   tagsViewList.value = Session.get('tagsViewList');
  // } else {
  let arr = [];
  tagsViewRoutesList.value.map((v) => {
    if (v.meta && v.meta.isAffix) arr.push({ ...v });
  });
  // }
  appStore.setTagNavList(arr);
  // 初始化当前元素(li)的下标
  getTagsRefsIndex(route.path);
  // 添加初始化横向滚动条移动到对应位置
  tagsViewmoveToCurrentTag();
}

// 添加 tagsView：未设置隐藏（isHide）也添加到在 tagsView 中
function addTagsView(path, to) {
  if (tagsViewList.value.some((v) => v.path === path)) return false;
  const item = tagsViewRoutesList.value.find((v) => v.path === path);
  if (item.isLink && !item.isIframe) return false;
  item.query = to?.query ? to?.query : route.query;
  tagsViewList.value.push({ ...item });
  addBrowserSetSession(tagsViewList.value);
}

// 右键菜单点击时显示菜单列表
function onContextmenu(v, e) {
  let { clientX, clientY } = e;
  tagsDropdown.value.x = clientX;
  tagsDropdown.value.y = clientY;
  tagsContextmenu.value.openContextmenu(v);
}

function onContextmenuIcon(e) {}

// 当前项右键菜单点击
function onCurrentContextmenuClick(data) {
  let { id, path } = data;
  let currentTag = tagsViewList.value.find((v) => v.path === path);
  switch (id) {
    case 0:
      refreshCurrentTagsView(path);
      router.push({ path, query: currentTag.query });
      break;
    case 1:
      closeCurrentTagsView(path);
      break;
    case 2:
      closeOtherTagsView(path, currentTag.query);
      break;
    case 3:
      closeAllTagsView(path);
      break;
  }
}

function refreshIcon() {
  nextTick((e) => {
    if (tagsViews.value?.offsetWidth < scrollbarRef.value.wrapRef.scrollWidth) {
      scrollTagIcon.value = true;
    } else {
      scrollTagIcon.value = false;
    }
  });
}

// 1、刷新当前 tagsView：
function refreshCurrentTagsView(path) {
  bus.emit('onTagsViewRefreshRouterView', path);
}

// 2、关闭当前 tagsView：当前项 `tags-view` icon 关闭时点击，如果是设置了固定的（isAffix），不可以关闭
function closeCurrentTagsView(path) {
  tagsViewList.value.map((v, k, arr) => {
    if (!v.meta.isAffix) {
      if (v.path === path) {
        tagsViewList.value.splice(k, 1);
        setTimeout(() => {
          // 最后一个
          if (tagsViewList.value.length === k)
            router.push({ path: arr[arr.length - 1].path, query: arr[arr.length - 1].query });
          // 否则，跳转到下一个
          else router.push({ path: arr[k].path, query: arr[k].query });
        }, 0);
      }
    }
  });
  appStore.setTagNavList(tagsViewList.value);
  //   addBrowserSetSession(tagsViewList.value);
}

// 3、关闭其它 tagsView：如果是设置了固定的（isAffix），不进行关闭
function closeOtherTagsView(path, query) {
  let newTagsViewList = [];
  tagsViewRoutesList.value.map((v) => {
    if ((v.meta && v.meta.isAffix) || v.path === path) {
      newTagsViewList.push({ ...v });
    }
  });
  addBrowserSetSession(newTagsViewList);
  router.push({ path, query });

  // addTagsView(path);
}

// 4、关闭全部 tagsView：如果是设置了固定的（isAffix），不进行关闭
function closeAllTagsView(path) {
  let newTagsViewList = [];
  tagsViewRoutesList.value.map((v) => {
    if (v.meta.isAffix) {
      newTagsViewList.push({ ...v });
      if (newTagsViewList.some((v) => v.path === path)) router.push({ path, query: route.query });
      else router.push({ path: v.path, query: route.query });
    }
  });
  addBrowserSetSession(newTagsViewList);
}

// 监听路由变化
watch(
  () => route.fullPath,
  () => {
    const to = route;
    tagsRoutePath.value = to.path;
    addTagsView(to.path, to);
    getTagsRefsIndex(to.path);
    tagsViewmoveToCurrentTag();
    refreshIcon();
  },
);
</script>

<style scoped lang="scss">
:deep(.el-scrollbar__bar.is-horizontal) {
  height: 0;
}
.el-dropdown-menu {
  width: 130px;
}
.setting-tag {
  padding: 0 10px;
  cursor: pointer;
}
.direction {
  padding: 0 3px;
}
.direction:hover {
  line-height: 34px;
  background-color: var(--prev-color-hover);
  cursor: pointer;
  transition: all 0.3s;
}
.layout-navbars-tagsview {
  flex: 1;
  z-index: 10;
  background-color: var(--prev-bg-white);
  -webkit-box-shadow: 0 1px 4px rgba(113, 128, 165, 0.1);
  box-shadow: 0 1px 4px rgba(113, 128, 165, 0.1);
  display: flex;
  align-items: center;
  & :deep(.is-vertical) {
    display: none !important;
  }
  &-ul {
    list-style: none;
    margin: 0;
    padding: 0;
    // width: 100%;
    height: 34px;
    display: flex;
    align-items: center;
    white-space: nowrap;
    color: var(--prev-color-text-regular);
    font-size: 12px;
    padding: 0 15px;
    &-li {
      height: 26px;
      line-height: 26px;
      display: flex;
      align-items: center;
      border: 1px solid #ebeef5;
      padding: 0 12px 0 15px;
      margin-right: 5px;
      border-radius: 2px;
      position: relative;
      z-index: 0;
      cursor: pointer;
      justify-content: space-between;
      transition: all 0.3s cubic-bezier(0.2, 1, 0.3, 1);
      &::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background: var(--prev-tag-active-color);
        z-index: -1;
        opacity: 0;
        // transform: scale3d(0.7, 1, 1);
        // transition: transform 0.3s, opacity 0.3s;
        // transition-timing-function: cubic-bezier(0.2, 1, 0.3, 1);
      }
      &:hover {
        color: var(--prev-color-primary-light-9);
        transition: all 0.3s cubic-bezier(0.2, 1, 0.3, 1);
        border-color: transparent;
        &::before {
          opacity: 1;
          transform: translate3d(0, 0, 0);
          border-radius: 2px;
        }
        .is-tagsview-icon {
          color: var(--prev-color-primary-light-9);

          transition: all 0.3s cubic-bezier(0.2, 1, 0.3, 1);
        }
      }
      &-iconfont {
        position: relative;
        left: -5px;
        top: 1px;
        color: var(--prev-color-primary-light-9);
      }
      &-icon {
        border-radius: 100%;
        position: relative;
        height: 14px;
        width: 14px;
        text-align: center;
        line-height: 14px;
        top: 0px;
      }
      .is-tagsview-icon {
        color: var(--prev-color-text-regular);
        transition: all 0.3s cubic-bezier(0.2, 1, 0.3, 1);
      }
    }
    .is-active {
      color: var(--prev-color-primary-light-3);
      transition: all 0.3s cubic-bezier(0.2, 1, 0.3, 1);
      border-color: transparent;
      &::before {
        opacity: 1;
        transform: translate3d(0, 0, 0);
        border-radius: 2px;
      }
    }
  }
  & ::-webkit-scrollbar {
    display: none !important;
  }
  // // 风格2
  // .tags-style-two {
  // }
  // // 风格3
  // .tags-style-three {
  // }
  // // 风格4
  // 风格1
  .tags-style-one {
    .is-active {
      background: none !important;
      color: #fff !important;
    }
  }
  // 风格4
  .tags-style-four {
    .layout-navbars-tagsview-ul-li {
      margin-right: 0 !important;
      border: none !important;
      position: relative;
      border-radius: 3px !important;

      .layout-icon-active {
        display: none;
      }
      .layout-icon-three {
        display: block;
      }
      &:hover {
        background: none !important;
      }
    }
    .is-active {
      background: none !important;
      color: #fff !important;
    }
  }
  // 风格5
  .tags-style-five {
    align-items: flex-end;
    .tags-style-five-svg {
      -webkit-mask-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNzAiIGhlaWdodD0iNzAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgZmlsbD0ibm9uZSI+CgogPGc+CiAgPHRpdGxlPkxheWVyIDE8L3RpdGxlPgogIDxwYXRoIHRyYW5zZm9ybT0icm90YXRlKC0wLjEzMzUwNiA1MC4xMTkyIDUwKSIgaWQ9InN2Z18xIiBkPSJtMTAwLjExOTE5LDEwMGMtNTUuMjI4LDAgLTEwMCwtNDQuNzcyIC0xMDAsLTEwMGwwLDEwMGwxMDAsMHoiIG9wYWNpdHk9InVuZGVmaW5lZCIgc3Ryb2tlPSJudWxsIiBmaWxsPSIjRjhFQUU3Ii8+CiAgPHBhdGggZD0ibS0wLjYzNzY2LDcuMzEyMjhjMC4xMTkxOSwwIDAuMjE3MzcsMC4wNTc5NiAwLjQ3Njc2LDAuMTE5MTljMC4yMzIsMC4wNTQ3NyAwLjI3MzI5LDAuMDM0OTEgMC4zNTc1NywwLjExOTE5YzAuMDg0MjgsMC4wODQyOCAwLjM1NzU3LDAgMC40NzY3NiwwbDAuMTE5MTksMGwwLjIzODM4LDAiIGlkPSJzdmdfMiIgc3Ryb2tlPSJudWxsIiBmaWxsPSJub25lIi8+CiAgPHBhdGggZD0ibTI4LjkyMTM0LDY5LjA1MjQ0YzAsMC4xMTkxOSwwLDAuMjM4MzgsMCwwLjM1NzU3bDAuMTE5MTlsMCwwLjExOTE5IiBpZD0ic3ZnXzMiIHN0cm9rZT0ibnVsbCIgZmlsbD0ibm9uZSIvPgogIDxyZWN0IGlkPSJzdmdfNCIgaGVpZ2h0PSIwIiB3aWR0aD0iMS4zMTEwOCIgeT0iNi44MzU1MiIgeD0iLTAuMDQxNzEiIHN0cm9rZT0ibnVsbCIgZmlsbD0ibm9uZSIvPgogIDxyZWN0IGlkPSJzdmdfNSIgaGVpZ2h0PSIxLjc4Nzg0IiB3aWR0aD0iMC4xMTkxOSIgeT0iNjguNDU2NSIgeD0iMjguOTIxMzQiIHN0cm9rZT0ibnVsbCIgZmlsbD0ibm9uZSIvPgogIDxyZWN0IGlkPSJzdmdfNiIgaGVpZ2h0PSI0Ljg4Njc3IiB3aWR0aD0iMTkuMDcwMzIiIHk9IjUxLjI5MzIxIiB4PSIzNi42Njg2NiIgc3Ryb2tlPSJudWxsIiBmaWxsPSJub25lIi8+CiA8L2c+Cjwvc3ZnPg=='),
        url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNzAiIGhlaWdodD0iNzAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgZmlsbD0ibm9uZSI+CiA8Zz4KICA8dGl0bGU+TGF5ZXIgMTwvdGl0bGU+CiAgPHBhdGggdHJhbnNmb3JtPSJyb3RhdGUoLTg5Ljc2MjQgNy4zMzAxNCA1NS4xMjUyKSIgc3Ryb2tlPSJudWxsIiBpZD0ic3ZnXzEiIGZpbGw9IiNGOEVBRTciIGQ9Im02Mi41NzQ0OSwxMTcuNTIwODZjLTU1LjIyOCwwIC0xMDAsLTQ0Ljc3MiAtMTAwLC0xMDBsMCwxMDBsMTAwLDB6IiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGZpbGwtcnVsZT0iZXZlbm9kZCIvPgogIDxwYXRoIGQ9Im0tMC42Mzc2Niw3LjMxMjI4YzAuMTE5MTksMCAwLjIxNzM3LDAuMDU3OTYgMC40NzY3NiwwLjExOTE5YzAuMjMyLDAuMDU0NzcgMC4yNzMyOSwwLjAzNDkxIDAuMzU3NTcsMC4xMTkxOWMwLjA4NDI4LDAuMDg0MjggMC4zNTc1NywwIDAuNDc2NzYsMGwwLjExOTE5LDBsMC4yMzgzOCwwIiBpZD0ic3ZnXzIiIHN0cm9rZT0ibnVsbCIgZmlsbD0ibm9uZSIvPgogIDxwYXRoIGQ9Im0yOC45MjEzNCw2OS4wNTI0NGMwLDAuMTE5MTkgMCwwLjIzODM4IDAsMC4zNTc1N2wwLDAuMTE5MTlsMCwwLjExOTE5IiBpZD0ic3ZnXzMiIHN0cm9rZT0ibnVsbCIgZmlsbD0ibm9uZSIvPgogIDxyZWN0IGlkPSJzdmdfNCIgaGVpZ2h0PSIwIiB3aWR0aD0iMS4zMTEwOCIgeT0iNi44MzU1MiIgeD0iLTAuMDQxNzEiIHN0cm9rZT0ibnVsbCIgZmlsbD0ibm9uZSIvPgogIDxyZWN0IGlkPSJzdmdfNSIgaGVpZ2h0PSIxLjc4Nzg0IiB3aWR0aD0iMC4xMTkxOSIgeT0iNjguNDU2NSIgeD0iMjguOTIxMzQiIHN0cm9rZT0ibnVsbCIgZmlsbD0ibm9uZSIvPgogIDxyZWN0IGlkPSJzdmdfNiIgaGVpZ2h0PSI0Ljg4Njc3IiB3aWR0aD0iMTkuMDcwMzIiIHk9IjUxLjI5MzIxIiB4PSIzNi42Njg2NiIgc3Ryb2tlPSJudWxsIiBmaWxsPSJub25lIi8+CiA8L2c+Cjwvc3ZnPg=='),
        url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg'><rect rx='8' width='100%' height='100%' fill='%23F8EAE7'/></svg>");
      -webkit-mask-size: 18px 30px, 20px 30px, calc(100% - 30px) calc(100% + 17px);
      -webkit-mask-position: right bottom, left bottom, center top;
      -webkit-mask-repeat: no-repeat;
    }
    .layout-navbars-tagsview-ul-li {
      padding: 0 5px;
      border-width: 15px 27px 15px;
      border-style: solid;
      border-color: transparent;
      margin: 0 -15px;
      .layout-icon-active,
      .layout-navbars-tagsview-ul-li-iconfont,
      .layout-navbars-tagsview-ul-li-refresh {
        display: none;
      }
      .layout-icon-three {
        display: block;
      }
      &:hover {
        @extend .tags-style-five-svg;
        background: var(--prev-color-primary-light-9);
        color: unset;
      }
    }
    .is-active {
      @extend .tags-style-five-svg;
      background: var(--prev-color-primary-light-9) !important;
      color: var(--prev-color-primary) !important;
      z-index: 1;
    }
  }
}
</style>
