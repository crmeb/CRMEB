<template>
  <div :class="isTagHistory ? 'h100' : 'h101'">
    <!-- vue-router 4：router-view 不能直接嵌在 transition/keep-alive 内，须用 slot props；
         key 基于 fullPath 保证每次导航生成新 vnode（避免 transition+keep-alive 复用竞态），
         刷新时叠加递增计数强制重建 -->
    <router-view v-slot="{ Component, route }">
      <transition :name="setTransitionName" mode="out-in">
        <keep-alive :include="keepAliveNameList">
          <component
            :is="Component"
            :key="refreshRouterViewKey ? route.fullPath + '__r' + refreshRouterViewKey : route.fullPath"
          />
        </keep-alive>
      </transition>
    </router-view>
  </div>
</template>

<script setup>
import { ref, computed, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import bus from '@/utils/bus';
import { useThemeConfigStore } from '@/store/module/themeConfig';
import { useKeepAliveNamesStore } from '@/store/module/keepAliveNames';

defineOptions({ name: 'parent' });

const route = useRoute();
const themeConfigStore = useThemeConfigStore();
const keepAliveNamesStore = useKeepAliveNamesStore();

const refreshRouterViewKey = ref(0);
const keepAliveNameList = ref([]);

// 设置主界面切换动画
const setTransitionName = computed(() => themeConfigStore.themeConfig.animation);

const isTagHistory = computed(() => themeConfigStore.themeConfig.isTagsview);

// created 时机
/**
 * 获取需要保持活动状态的组件名称列表
 */
keepAliveNameList.value = getKeepAliveNames();
// 监听标签页视图刷新路由视图事件
bus.on('onTagsViewRefreshRouterView', onTagsViewRefreshRouterView);

onUnmounted(() => {
  bus.off('onTagsViewRefreshRouterView', onTagsViewRefreshRouterView);
});

function onTagsViewRefreshRouterView(path) {
  // 如果当前路由路径不等于传入的路径，则直接返回false
  if (route.path !== path) return false;
  // 递增计数改变组件 key，强制销毁重建当前视图（onMounted 重跑 → 接口重新请求）
  refreshRouterViewKey.value += 1;
}

// 获取路由缓存列表（name），默认路由全部缓存
function getKeepAliveNames() {
  return keepAliveNamesStore.keepAliveNames;
}
</script>
