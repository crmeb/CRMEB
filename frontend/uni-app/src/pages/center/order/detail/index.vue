<template>
  <view>
    <OrderDetailEngine ref="engine" :order-no="orderNo" />
    <!-- 系统悬浮菜单（小程序端不显示） -->
    <!-- #ifndef MP -->
    <home></home>
    <!-- #endif -->
  </view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad, onShow } from '@dcloudio/uni-app';
import OrderDetailEngine from '@/dynamic-pages/engines/OrderDetailEngine.vue';
import home from '@/components/home';

const orderNo = ref('');
const engine = ref(null);

onLoad((options) => {
  orderNo.value = options.order_no || '';
});

// 首次 onShow 与 onLoad 引擎的首次加载重复，跳过；此后每次返回本页（评价完成 navigateBack 等）刷新详情数据
let showed = false;
onShow(() => {
  if (!showed) {
    showed = true;
    return;
  }
  engine.value?.refresh();
});
</script>
