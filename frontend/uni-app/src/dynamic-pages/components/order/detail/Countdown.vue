<!--
  倒计时组件（DIY 通用化版）
  展示待支付订单的倒计时提示
  数据来源：section.props（text/remaining）
-->
<template>
  <view class="countdown-bar" v-if="text">
    <text class="iconfont icon-shijian"></text>
    <text class="countdown-text">{{ text }}</text>
  </view>
</template>

<script>
/**
 * 倒计时组件
 * 后端算好 remaining 和 text，前端只做展示
 */
export default {
  name: 'Countdown',
  // DIY 通用化契约：组件唯一标识
  componentKey: 'countdown',
};
</script>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /** section 对象，含 props.text/remaining */
  section: { type: Object, required: true },
});

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 引擎注入的全局状态（预留，当前组件不依赖） */
const ctx = computed(() => (props.section && props.section._ctx) || {});
/** 倒计时文案（后端组装，如"请在 15:00 内完成支付"） */
const text = computed(() => p.value.text || '');
/** 剩余秒数（备用，后端已算好文案） */
const remaining = computed(() => p.value.remaining || 0);
</script>

<style scoped>
.countdown-bar { display: flex; align-items: center; margin: 20rpx; padding: 16rpx 24rpx; background: #fff4e5; border-radius: 12rpx; }
.iconfont { font-size: 28rpx; color: #ff976a; margin-right: 10rpx; }
.countdown-text { font-size: 26rpx; color: #ff976a; }
</style>
