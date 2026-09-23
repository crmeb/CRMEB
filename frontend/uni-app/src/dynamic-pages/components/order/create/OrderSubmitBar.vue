<!--
  底部提交栏
  展示合计金额与「确认下单」按钮
-->
<template>
  <view class="submit-bar">
    <view class="submit-left">
      <text class="total-label">{{ $t('合计：') }}</text>
      <text class="total-price" v-if="!integralTotal">￥{{ payPrice }}</text>
      <text class="total-price" v-else>{{ integralTotal }}{{ $t('积分') }}</text>
    </view>
    <view
      class="submit-btn"
      :class="{ disabled: submitting || disabled }"
      @click="onClick"
    >
      {{ submitting ? $t('提交中...') : (integralTotal ? $t('立即兑换') : $t('确认下单')) }}
    </view>
  </view>
</template>

<script setup>
defineOptions({ name: 'OrderSubmitBar' });

const props = defineProps({
  /** 应付金额 */
  payPrice: { type: [Number, String], default: '0.00' },
  /** 是否提交中 */
  submitting: { type: Boolean, default: false },
  /** 是否禁用提交（如未选地址） */
  disabled: { type: Boolean, default: false },
  /** 积分兑换总额（非0时替换金额显示） */
  integralTotal: { type: [Number, String], default: 0 },
});

const emit = defineEmits(['submit']);

/** 点击提交按钮，禁用状态下不触发 */
function onClick() {
  if (props.submitting || props.disabled) return;
  emit('submit');
}
</script>

<style scoped>
/* 底部提交栏 */
.submit-bar {
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16rpx 30rpx;
  padding-bottom: calc(16rpx + constant(safe-area-inset-bottom));
  padding-bottom: calc(16rpx + env(safe-area-inset-bottom));
  background: #fff;
  box-shadow: 0 -2rpx 10rpx rgba(0, 0, 0, 0.05);
}
.submit-left {
  display: flex;
  align-items: baseline;
}
.total-label {
  font-size: 28rpx;
  color: #333;
}
.total-price {
  font-size: 36rpx;
  font-weight: bold;
  color: var(--view-theme, #e93323);
}
.submit-btn {
  width: 240rpx;
  height: 80rpx;
  line-height: 80rpx;
  background: var(--view-theme, #e93323);
  color: #fff;
  font-size: 30rpx;
  border-radius: 40rpx;
  text-align: center;
}
.submit-btn.disabled {
  background: #ccc;
}
</style>
