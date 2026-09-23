<!--
  商品参数列表
  展示商品规格参数（品牌/材质等键值对）
-->
<template>
  <view class="params-section" v-if="visible && list.length > 0">
    <view class="section-title">{{ $t('商品参数') }}</view>
    <view class="params-list">
      <view class="params-item" v-for="(item, index) in list" :key="index">
        <view class="params-label">{{ item.label }}</view>
        <view class="params-value">{{ item.value }}</view>
      </view>
    </view>
  </view>
</template>

<script setup>
defineOptions({ name: 'OrderParamsList' });

defineProps({
  /** 参数列表 [{ label, value }] */
  list: { type: Array, default: () => [] },
  /** 是否显示（受 features.params_list 控制） */
  visible: { type: Boolean, default: true },
});
</script>

<style scoped>
/* 商品参数 */
.params-section {
  margin: 20rpx;
  background: #fff;
  border-radius: 12rpx;
  padding: 24rpx 30rpx;
}
.section-title {
  font-size: 30rpx;
  font-weight: bold;
  color: #282828;
  margin-bottom: 20rpx;
}
.params-list {
  border-top: 1px dashed #eee;
  padding-top: 16rpx;
}
.params-item {
  display: flex;
  padding: 12rpx 0;
}
.params-label {
  width: 180rpx;
  font-size: 26rpx;
  color: #999;
  flex-shrink: 0;
}
.params-value {
  flex: 1;
  font-size: 26rpx;
  color: #333;
}
</style>
