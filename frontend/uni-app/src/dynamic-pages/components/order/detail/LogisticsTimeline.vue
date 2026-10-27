<!--
  配送信息卡片（timeline:logistics）
  express：快递公司 + 快递单号；send：送货人 + 联系电话；fictitious：发货方式 + 发货备注
-->
<template>
  <view v-if="deliveryName || deliveryId" class="info-card delivery-info-card">
    <view class="card-title">{{ $t('配送信息') }}</view>
    <view v-if="deliveryName" class="info-row acea-row row-between">
      <text class="info-label">{{ nameLabel }}</text>
      <text class="info-value">{{ deliveryName }}</text>
    </view>
    <view v-if="deliveryId" class="info-row acea-row row-between">
      <text class="info-label">{{ idLabel }}</text>
      <view class="info-value acea-row row-middle row-right">
        <text class="express-no">{{ deliveryId }}</text>
        <text class="copy" @tap="onCopy">{{ $t('复制') }}</text>
      </view>
    </view>
  </view>
</template>

<script>
/**
 * 配送信息卡片
 * 通过复合 componentKey='timeline:logistics' 匹配 section
 */
export default {
  name: 'LogisticsTimeline',
  componentKey: 'timeline:logistics',
};
</script>

<script setup>
import { computed } from 'vue';
import i18n from '@/utils/lang.js';

const props = defineProps({
  /** section：props.delivery_type / delivery_name / delivery_id */
  section: { type: Object, required: true },
});

const p = computed(() => (props.section && props.section.props) || {});
const deliveryType = computed(() => p.value.delivery_type || 'express');
const deliveryName = computed(() => p.value.delivery_name || '');
const deliveryId = computed(() => p.value.delivery_id || '');

const nameLabel = computed(() => {
  if (deliveryType.value === 'send') return i18n.t('送货人');
  if (deliveryType.value === 'fictitious') return i18n.t('发货方式');
  return i18n.t('快递公司');
});

const idLabel = computed(() => {
  if (deliveryType.value === 'send') return i18n.t('联系电话');
  if (deliveryType.value === 'fictitious') return i18n.t('发货备注');
  return i18n.t('快递单号');
});

/** 复制单号 / 电话 / 备注 */
function onCopy() {
  const no = deliveryId.value;
  if (!no) return;
  // #ifdef H5
  if (typeof navigator !== 'undefined' && navigator.clipboard) {
    navigator.clipboard.writeText(no);
    uni.showToast({ title: i18n.t('已复制'), icon: 'none' });
    return;
  }
  // #endif
  uni.setClipboardData({
    data: no,
    success: () => {
      uni.showToast({ title: i18n.t('已复制'), icon: 'none' });
    },
  });
}
</script>

<style scoped>
.delivery-info-card {
  margin: 20rpx;
  background: #fff;
  border-radius: 16rpx;
  padding: 30rpx;
}
.card-title {
  font-size: 30rpx;
  font-weight: bold;
  color: #333;
  margin-bottom: 20rpx;
}
.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12rpx 0;
  font-size: 26rpx;
}
.info-label {
  color: #999;
  flex-shrink: 0;
}
.info-value {
  color: #333;
  text-align: right;
  max-width: 70%;
}
.express-no {
  word-break: break-all;
}
.copy {
  margin-left: 12rpx;
  padding: 2rpx 16rpx;
  font-size: 22rpx;
  color: var(--view-theme, #e93323);
  border: 1rpx solid var(--view-theme, #e93323);
  border-radius: 6rpx;
  flex-shrink: 0;
}
</style>
