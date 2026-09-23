<template>
  <OrderCreateEngine ref="engineRef" />
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { onLoad } from '@dcloudio/uni-app';
import OrderCreateEngine from '@/dynamic-pages/engines/OrderCreateEngine.vue';

/**
 * 订单确认页（薄包装）
 * 引擎组件不会自动收到页面 onLoad 参数，由页面接收后通过 ref 传入。
 */
const engineRef = ref(null);
const loadOptions = ref(null);
const initialized = ref(false);

function initEngine() {
  if (!engineRef.value || !loadOptions.value || initialized.value) return;
  initialized.value = true;
  engineRef.value.initFromOptions(loadOptions.value);
}

onLoad((options) => {
  loadOptions.value = options || {};
  initEngine();
});

onMounted(() => {
  // 页面 onLoad 和组件 mounted 的先后顺序在不同平台可能不同。
  initEngine();
});
</script>
