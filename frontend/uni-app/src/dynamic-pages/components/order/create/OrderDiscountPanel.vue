<!--
  优惠信息面板
  包含：优惠券选择入口、积分抵扣开关
-->
<template>
  <view class="wrapper">
    <!-- 优惠券 -->
    <view class="item acea-row row-between-wrapper" @tap="onCouponTap" v-if="features.coupon">
      <view>{{ $t('优惠券') }}</view>
      <view class="discount">
        {{ couponTitle || $t('请选择') }}
        <text class="iconfont icon-jiantou"></text>
      </view>
    </view>

    <!-- 积分抵扣 -->
    <view class="item acea-row row-between-wrapper" v-if="features.integral">
      <view>{{ $t('积分抵扣') }}</view>
      <view class="discount acea-row row-middle" @tap="onToggleIntegral">
        <view>
          {{ useIntegral ? $t('剩余积分') : $t('当前积分') }}
          <text class="num font-color">{{ usableIntegralDisplay || 0 }}</text>
        </view>
        <view class="integral-switch" :class="{ 'is-checked': useIntegral }">
          <view class="integral-switch-knob"></view>
        </view>
      </view>
    </view>

    <!-- 积分兑换：展示可用积分 -->
    <view class="item acea-row row-between-wrapper" v-if="isIntegral">
      <view>{{ $t('可用积分') }}</view>
      <view class="discount">
        <text class="num font-color">{{ usableIntegralDisplay || 0 }}</text>
      </view>
    </view>
  </view>
</template>

<script setup>
defineOptions({ name: 'OrderDiscountPanel' });

defineProps({
  /** 功能开关 */
  features: { type: Object, default: () => ({}) },
  /** 优惠券标题文案（空值时模板兜底显示"请选择"） */
  couponTitle: { type: String, default: '' },
  /** 是否使用积分 */
  useIntegral: { type: Boolean, default: false },
  /** 展示用的积分数量 */
  usableIntegralDisplay: { type: [Number, String], default: 0 },
  /** 是否为积分兑换模式 */
  isIntegral: { type: Boolean, default: false },
});

const emit = defineEmits(['coupon-tap', 'toggle-integral']);

/** 点击优惠券入口 */
function onCouponTap() {
  emit('coupon-tap');
}
/** 切换积分抵扣 */
function onToggleIntegral() {
  emit('toggle-integral');
}
</script>

<style scoped>
/* 优惠信息区域 */
.wrapper {
  background-color: #fff;
  margin: 20rpx;
  border-radius: 12rpx;
  overflow: hidden;
}
.wrapper .item {
  padding: 27rpx 30rpx;
  font-size: 30rpx;
  color: #282828;
  border-bottom: 1px solid #f0f0f0;
}
.wrapper .item:last-child {
  border-bottom: none;
}
.wrapper .item .discount {
  font-size: 28rpx;
  color: #999;
}
.wrapper .item .discount .iconfont {
  color: #515151;
  font-size: 30rpx;
  margin-left: 15rpx;
}
.wrapper .item .discount .num {
  font-size: 32rpx;
  margin-right: 20rpx;
  color: var(--view-theme, #e93323);
}

/* 自定义积分开关 */
.integral-switch {
  position: relative;
  width: 80rpx;
  height: 44rpx;
  background: #ddd;
  border-radius: 22rpx;
  transition: background 0.2s;
  margin-left: 20rpx;
  flex-shrink: 0;
}
.integral-switch.is-checked {
  background: var(--view-theme, #e93323);
}
.integral-switch-knob {
  position: absolute;
  top: 3rpx;
  left: 3rpx;
  width: 38rpx;
  height: 38rpx;
  background: #fff;
  border-radius: 50%;
  transition: left 0.2s;
  box-shadow: 0 2rpx 4rpx rgba(0, 0, 0, 0.2);
}
.integral-switch.is-checked .integral-switch-knob {
  left: 39rpx;
}
</style>
