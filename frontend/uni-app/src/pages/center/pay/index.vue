<template>
  <CashierEngine ref="engineRef" />
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { onLoad, onShow } from '@dcloudio/uni-app';
import CashierEngine from '@/dynamic-pages/engines/CashierEngine.vue';

/**
 * 收银台页面（薄包装）
 * 引擎组件的 onLoad/onShow 不会在子组件上触发，
 * 因此由本页面统一接收页面生命周期，再通过模板 ref 调用引擎方法。
 */
// 暂存 onLoad 参数，待子组件挂载后传入引擎
const loadOptions = ref(null);
const engineRef = ref(null);

onLoad((options) => {
  loadOptions.value = options;
});

onMounted(() => {
  // 子组件挂载完成后，把 onLoad 参数交给引擎初始化
  if (loadOptions.value && engineRef.value) {
    engineRef.value.initFromOptions(loadOptions.value);
  }
});

onShow(() => {
  // 首次 onShow 在 mounted 之前触发，此时 engineRef 可能不存在；
  // 且 initIn 初始为 false，跳过即可。后续 onShow 正常委托给引擎。
  if (engineRef.value) {
    engineRef.value.handlePageShow();
  }
});
</script>
