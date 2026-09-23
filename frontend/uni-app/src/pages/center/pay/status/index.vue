<template>
  <PayStatusEngine ref="engineRef" />
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { onLoad, onShow } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import PayStatusEngine from '@/dynamic-pages/engines/PayStatusEngine.vue';
import { toLogin } from '@/libs/login.js';
import { useAppStore } from '@/store/modules/app';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';

/**
 * 支付结果页（薄包装）
 * 引擎组件的 onLoad/onShow 不会在子组件上触发，且小程序端引擎创建早于页面 onLoad
 * （props 传入的参数在引擎 setup 时尚未就绪），因此由本页面暂存 onLoad 参数，
 * 待引擎挂载后通过模板 ref 交给引擎初始化。
 */
const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

// 暂存 onLoad 参数，待子组件挂载后传入引擎
const loadOptions = ref(null);
const engineRef = ref(null);
// 首次 onShow 在 mounted 之前触发，跳过以避免与 initFromOptions 的首查重复
const firstShow = ref(true);

onLoad((options) => {
  if (!options.order_no) {
    return util.Tips(
      { title: i18n.t('缺少参数无法查看订单支付状态') },
      { tab: 3, url: 1 }
    );
  }
  loadOptions.value = options;
});

onMounted(() => {
  // 引擎挂载完成后，把 onLoad 参数交给引擎初始化并触发首查
  if (loadOptions.value && engineRef.value) {
    engineRef.value.initFromOptions(loadOptions.value);
  }
});

onShow(() => {
  if (!isLogin.value) {
    toLogin();
    return;
  }
  // 首次 onShow 跳过（首查由 initFromOptions 触发），后续 onShow 触发刷新
  if (firstShow.value) {
    firstShow.value = false;
    return;
  }
  engineRef.value?.refresh();
});
</script>
