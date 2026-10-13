<template>
  <teleport to="body">
    <transition name="menu-search">
      <div v-if="isShowSearch" class="menu-search-overlay" @click.self="closeSearch">
        <div class="menu-search-panel" role="dialog" aria-modal="true" aria-label="菜单搜索">
          <div class="menu-search-header" @click="focusInput">
            <el-icon class="menu-search-logo"><Search /></el-icon>
            <input
              ref="inputRef"
              v-model="menuQuery"
              class="menu-search-input"
              type="text"
              :placeholder="$t('message.user.searchPlaceholder')"
              @keydown.down.prevent="moveActive(1)"
              @keydown.up.prevent="moveActive(-1)"
              @keydown.enter.prevent="onHandleSelect(resultList[activeIndex])"
              @keydown.esc.prevent="closeSearch"
            />
            <transition name="menu-search-fade">
              <span v-if="menuQuery" class="menu-search-clear" title="清空" @click.stop="clearQuery">
                <el-icon><Close /></el-icon>
              </span>
            </transition>
            <span class="menu-search-esc">Esc</span>
          </div>
          <div v-loading="loading" element-loading-background="transparent" class="menu-search-results">
            <div v-if="resultList.length" ref="listRef" class="menu-search-list">
              <div
                v-for="(item, index) in resultList"
                :key="item.path + index"
                class="menu-search-item"
                :data-active="index === activeIndex"
                @mousedown.prevent
                @mouseenter="activeIndex = index"
                @click="onHandleSelect(item)"
              >
                <span class="menu-search-item-icon"><menu-icon :icon="item.icon" /></span>
                <span class="menu-search-item-title">{{ $t(item.title) }}</span>
                <span class="menu-search-item-path line1">{{ item.path }}</span>
              </div>
            </div>
            <div v-else class="menu-search-empty">
              <el-icon><Search /></el-icon>
              <p>未找到相关菜单，换个关键词试试</p>
            </div>
          </div>
          <div class="menu-search-footer">
            <span><i>↑</i><i>↓</i> 切换</span>
            <span><i>Enter</i> 打开</span>
            <span><i>Esc</i> 关闭</span>
          </div>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
import { nextTick, onBeforeUnmount, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { menusSearch } from '@/api/setting';
import { getAllSiderMenu } from '@/libs/system';
import { useRoutesListStore } from '@/store/module/routesList';
import MenuIcon from '@/components/menuIcon/index.vue';

defineOptions({ name: 'layoutBreadcrumbSearch' });

const router = useRouter();
const routesListStore = useRoutesListStore();

const inputRef = ref(null);
const listRef = ref(null);
const isShowSearch = ref(false);
const menuQuery = ref('');
const activeIndex = ref(0);
const loading = ref(false);
const resultList = ref([]);
const tagsViewList = ref([]);

let searchTimer = null;
// 递增序号丢弃过期响应，避免快速输入时旧结果覆盖新结果
let requestSeq = 0;

// 搜索弹窗打开：重置状态并聚焦输入框
function openSearch() {
  menuQuery.value = '';
  activeIndex.value = 0;
  initTageView();
  resultList.value = tagsViewList.value;
  isShowSearch.value = true;
  nextTick(() => inputRef.value?.focus());
}

// 搜索弹窗关闭
function closeSearch() {
  isShowSearch.value = false;
}

function clearQuery() {
  menuQuery.value = '';
  focusInput();
}

function focusInput() {
  inputRef.value?.focus();
}

// 初始化本地菜单数据（空关键词时全量展示）
function initTageView() {
  if (tagsViewList.value.length > 0) return false;
  tagsViewList.value = getAllSiderMenu(routesListStore.routesList);
}

// 关键词搜索：空关键词回落本地全量菜单，有关键词走后端检索（200ms 防抖）
watch(menuQuery, (val) => {
  clearTimeout(searchTimer);
  if (!val) {
    requestSeq++;
    loading.value = false;
    resultList.value = tagsViewList.value;
    activeIndex.value = 0;
    return;
  }
  loading.value = true;
  searchTimer = setTimeout(() => {
    const seq = ++requestSeq;
    menusSearch({ keyword: val })
      .then((res) => {
        if (seq !== requestSeq) return;
        resultList.value = res.data || [];
        activeIndex.value = 0;
      })
      .finally(() => {
        if (seq === requestSeq) loading.value = false;
      });
  }, 200);
});

// 键盘高亮项变化时保持可见
watch(activeIndex, () => {
  nextTick(() => {
    listRef.value?.querySelector('[data-active="true"]')?.scrollIntoView({ block: 'nearest' });
  });
});

function moveActive(step) {
  const len = resultList.value.length;
  if (!len) return;
  activeIndex.value = (activeIndex.value + step + len) % len;
}

// 当前菜单选中时
function onHandleSelect(item) {
  if (!item) return;
  let { path, redirect } = item;
  if (item.isLink && !item.isIframe) window.open(item.isLink);
  else if (redirect) router.push(redirect);
  else router.push(path);
  closeSearch();
}

onBeforeUnmount(() => {
  clearTimeout(searchTimer);
});

defineExpose({ openSearch, closeSearch });
</script>

<style scoped lang="scss">
// 全屏遮罩：暗色压暗 + 背景虚化
.menu-search-overlay {
  position: fixed;
  inset: 0;
  z-index: 3000;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  background: rgba(0, 0, 0, 0.25);
  backdrop-filter: blur(10px) saturate(1.4);
  -webkit-backdrop-filter: blur(10px) saturate(1.4);
}

// 毛玻璃面板：半透明白（暗色主题自动跟随 --prev-bg-white）+ 高斯模糊
.menu-search-panel {
  display: flex;
  flex-direction: column;
  width: 640px;
  max-width: calc(100vw - 48px);
  max-height: min(68vh, 640px);
  margin-top: 13vh;
  overflow: hidden;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.35);
  background: color-mix(in srgb, var(--prev-bg-white) 86%, transparent);
  backdrop-filter: blur(28px) saturate(1.8);
  -webkit-backdrop-filter: blur(28px) saturate(1.8);
  box-shadow: 0 24px 64px rgba(0, 0, 0, 0.2), 0 2px 10px rgba(0, 0, 0, 0.06);
}

// 输入区
.menu-search-header {
  display: flex;
  align-items: center;
  gap: 12px;
  height: 60px;
  padding: 20px 20px;
  border-bottom: 1px solid color-mix(in srgb, var(--prev-border-color-lighter) 72%, transparent);
  cursor: text;
}
.menu-search-logo {
  font-size: 20px;
  color: var(--prev-color-text-secondary);
}
.menu-search-input {
  flex: 1;
  height: 100%;
  border: none;
  outline: none;
  background: transparent;
  font-size: 16px;
  color: var(--prev-color-text-regular);
  caret-color: var(--prev-color-primary);
  &::placeholder {
    color: var(--prev-color-text-placeholder);
  }
}
.menu-search-clear {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  font-size: 12px;
  color: var(--prev-color-text-secondary);
  cursor: pointer;
  transition: background-color 0.12s ease;
  &:hover {
    background: var(--prev-color-hover);
    color: var(--prev-color-text-regular);
  }
}
.menu-search-esc {
  flex-shrink: 0;
  padding: 1px 7px;
  font-size: 11px;
  line-height: 16px;
  color: var(--prev-color-text-secondary);
  border: 1px solid var(--prev-border-color-base);
  border-bottom-width: 2px;
  border-radius: 4px;
  background: color-mix(in srgb, var(--prev-bg-white) 60%, transparent);
}

// 结果区
.menu-search-results {
  flex: 1;
  min-height: 140px;
  padding: 8px;
  overflow: hidden auto;
}
.menu-search-list {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.menu-search-item {
  display: flex;
  align-items: center;
  gap: 12px;
  height: 46px;
  padding: 0 12px;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.12s ease;
  &[data-active='true'] {
    background: var(--prev-color-primary-light-9);
    .menu-search-item-title {
      color: var(--prev-color-primary);
      font-weight: 500;
    }
    .menu-search-item-icon {
      color: var(--prev-color-primary);
      background: var(--prev-bg-white);
    }
    .menu-search-item-path {
      color: var(--prev-color-primary);
      opacity: 0.65;
    }
  }
}
.menu-search-item-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 30px;
  height: 30px;
  flex-shrink: 0;
  border-radius: 6px;
  font-size: 16px;
  color: var(--prev-color-text-regular);
  background: var(--prev-bg-color);
  transition: color 0.12s ease, background-color 0.12s ease;
  :deep(.menu-icon),
  :deep(.el-icon) {
    margin-right: 0;
    font-size: 16px;
  }
}
.menu-search-item-title {
  flex-shrink: 0;
  font-size: 14px;
  color: var(--prev-color-text-regular);
  transition: color 0.12s ease;
}
.menu-search-item-path {
  max-width: 45%;
  margin-left: auto;
  font-size: 12px;
  color: var(--prev-color-text-placeholder);
  transition: color 0.12s ease;
}

// 空状态
.menu-search-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 32px 0 40px;
  color: var(--prev-color-text-placeholder);
  .el-icon {
    font-size: 34px;
  }
  p {
    font-size: 13px;
  }
}

// 底部快捷键提示
.menu-search-footer {
  display: flex;
  justify-content: flex-end;
  gap: 16px;
  padding: 10px 20px;
  font-size: 12px;
  color: var(--prev-color-text-secondary);
  border-top: 1px solid color-mix(in srgb, var(--prev-border-color-lighter) 72%, transparent);
  i {
    display: inline-block;
    min-width: 18px;
    margin-right: 3px;
    padding: 0 4px;
    font-style: normal;
    font-size: 11px;
    line-height: 18px;
    text-align: center;
    border: 1px solid var(--prev-border-color-base);
    border-bottom-width: 2px;
    border-radius: 4px;
    background: color-mix(in srgb, var(--prev-bg-white) 60%, transparent);
  }
}

// 进出场动画：遮罩淡入淡出 + 面板轻微上浮缩放
.menu-search-enter-active {
  transition: opacity 0.2s ease;
  .menu-search-panel {
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease;
  }
}
.menu-search-leave-active {
  transition: opacity 0.15s ease;
  .menu-search-panel {
    transition: transform 0.15s ease, opacity 0.15s ease;
  }
}
.menu-search-enter-from,
.menu-search-leave-to {
  opacity: 0;
}
.menu-search-enter-from .menu-search-panel {
  transform: translateY(-16px) scale(0.97);
}
.menu-search-leave-to .menu-search-panel {
  transform: translateY(-8px) scale(0.98);
  opacity: 0;
}
.menu-search-fade-enter-active,
.menu-search-fade-leave-active {
  transition: opacity 0.12s ease;
}
.menu-search-fade-enter-from,
.menu-search-fade-leave-to {
  opacity: 0;
}
</style>
