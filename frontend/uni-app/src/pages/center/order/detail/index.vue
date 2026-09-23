<template>
  <view>
    <OrderDetailEngine :order-no="orderNo" />
    <!-- 系统悬浮菜单（小程序端不显示） -->
    <!-- #ifndef MP -->
    <home></home>
    <!-- #endif -->
  </view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad } from '@dcloudio/uni-app';
import OrderDetailEngine from '@/dynamic-pages/engines/OrderDetailEngine.vue';
import home from '@/components/home';

const orderNo = ref('');

onLoad((options) => {
  orderNo.value = options.order_no || '';
});
</script>
