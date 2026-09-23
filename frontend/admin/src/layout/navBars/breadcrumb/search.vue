<template>
  <div class="layout-search-dialog">
    <el-dialog
      v-model="isShowSearch"
      fullscreen
      :modal="false"
      destroy-on-close
      :show-close="true"
      @opened="onDialogOpened"
    >
      <el-autocomplete
        v-model="menuQuery"
        :fetch-suggestions="menuSearch"
        :placeholder="$t('message.user.searchPlaceholder')"
        prefix-icon="Search"
        ref="layoutMenuAutocompleteRef"
        @select="onHandleSelect"
        @blur="onSearchBlur"
      >
        <template #default="{ item }">
          <div><i :class="item.icon" class="mr10"></i>{{ $t(item.title) }}</div>
        </template>
      </el-autocomplete>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { menusSearch } from '@/api/setting';
import { getAllSiderMenu } from '@/libs/system';
import { i18n } from '@/i18n/index.js';
import { useRoutesListStore } from '@/store/module/routesList';

defineOptions({ name: 'layoutBreadcrumbSearch' });

const router = useRouter();
const routesListStore = useRoutesListStore();

const layoutMenuAutocompleteRef = ref(null);
const isShowSearch = ref(false);
const menuQuery = ref('');
const tagsViewList = ref([]);

// 搜索弹窗打开
function openSearch() {
  menuQuery.value = '';
  isShowSearch.value = true;
  initTageView();
}

// 弹窗打开动画结束后聚焦输入框（EP 的 focus-trap 会覆盖更早的 focus 调用）
function onDialogOpened() {
  layoutMenuAutocompleteRef.value?.focus?.();
}

// 搜索弹窗关闭
function closeSearch() {
  setTimeout(() => {
    isShowSearch.value = false;
  }, 150);
}

// 菜单搜索数据过滤
function menuSearch(queryString, cb) {
  if (!queryString) {
    let results = queryString ? tagsViewList.value.filter(createFilter(queryString)) : tagsViewList.value;
    cb(results);
  } else {
    let queryData = {
      keyword: queryString,
    };
    menusSearch(queryData).then((res) => {
      cb(res.data);
    });
  }
}

// 菜单搜索过滤
function createFilter(queryString) {
  return (restaurant) => {
    return (
      restaurant.path.toLowerCase().indexOf(queryString.toLowerCase()) > -1 ||
      restaurant.title.toLowerCase().indexOf(queryString.toLowerCase()) > -1 ||
      i18n.global.t(restaurant.title).toLowerCase().indexOf(queryString.toLowerCase()) > -1
    );
  };
}

// 初始化菜单数据
function initTageView() {
  if (tagsViewList.value.length > 0) return false;
  tagsViewList.value = getAllSiderMenu(routesListStore.routesList);
}

// 当前菜单选中时
function onHandleSelect(item) {
  let { path, redirect } = item;
  if (item.isLink && !item.isIframe) window.open(item.isLink);
  else if (redirect) router.push(redirect);
  else router.push(path);
  closeSearch();
}

// input 失去焦点时
function onSearchBlur() {
  closeSearch();
}

defineExpose({ openSearch, closeSearch });
</script>

<style scoped lang="scss">
.layout-search-dialog {
  :deep(.el-dialog) {
    box-shadow: unset !important;
    border-radius: 0 !important;
    background: rgba(0, 0, 0, 0.5);
  }
  :deep(.el-autocomplete) {
    width: 560px;
    position: absolute;
    top: 100px;
    left: 50%;
    transform: translateX(-50%);
  }
}
:deep(.el-dialog__header) {
  border: none !important;
}
:deep(.el-input--small .el-input__inner) {
  height: 36px;
  line-height: 36px;
}
</style>
