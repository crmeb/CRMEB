<template>
  <div>
    <div class="layout-view-bg-white flex h100" v-loading="iframeLoading">
      <iframe :src="meta.isLink" frameborder="0" height="100%" width="100%" id="iframe"></iframe>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import bus from '@/utils/bus';

defineOptions({ name: 'layoutIfameView' });

const props = defineProps({
  meta: {
    type: Object,
    default: () => {},
  },
});

const emit = defineEmits(['getCurrentRouteMeta']);

const route = useRoute();

const iframeLoading = ref(true);

// created 时机
bus.on('onTagsViewRefreshRouterView', onTagsViewRefreshRouterView);

onMounted(() => {
  initIframeLoad();
});

onUnmounted(() => {
  bus.off('onTagsViewRefreshRouterView', onTagsViewRefreshRouterView);
});

function onTagsViewRefreshRouterView(path) {
  if (route.path !== path) return false;
  emit('getCurrentRouteMeta');
}

// 初始化页面加载 loading
function initIframeLoad() {
  nextTick(() => {
    iframeLoading.value = true;
    const iframe = document.getElementById('iframe');
    if (!iframe) return false;
    iframe.onload = () => {
      iframeLoading.value = false;
    };
  });
}
</script>
