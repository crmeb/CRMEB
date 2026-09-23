<!--
  售后进度组件
  展示售后类型、状态、金额、原因、售后时间线、退货物流信息
  数据来源：section.props.afterSale（含 type/status/status_text/amount/reason/timeline[]）
-->
<template>
  <view class="info-card after-sale-card" v-if="afterSale">
    <view class="card-title">{{ $t('售后进度') }}</view>
    <!-- 售后状态行 -->
    <view class="info-row acea-row row-between">
      <text class="info-label">{{ $t('售后状态') }}</text>
      <text class="info-value" :class="afterSale.status === 'completed' ? 'status-done' : 'status-pending'">
        {{ afterSale.status_text || afterSale.status || '' }}
      </text>
    </view>
    <!-- 售后类型 -->
    <view v-if="afterSale.type" class="info-row acea-row row-between">
      <text class="info-label">{{ $t('售后类型') }}</text>
      <text class="info-value">{{ afterSale.type_text || afterSale.type || '' }}</text>
    </view>
    <!-- 退款金额 -->
    <view v-if="afterSale.amount" class="info-row acea-row row-between">
      <text class="info-label">{{ $t('退款金额') }}</text>
      <text class="info-value refund-amount">¥{{ afterSale.amount }}</text>
    </view>
    <!-- 申请原因 -->
    <view v-if="afterSale.reason" class="info-row acea-row row-between">
      <text class="info-label">{{ $t('申请原因') }}</text>
      <text class="info-value">{{ afterSale.reason }}</text>
    </view>
    <!-- 售后时间线 -->
    <view v-if="afterSale.timeline && afterSale.timeline.length" class="after-sale-timeline">
      <view
        v-for="(node, idx) in afterSale.timeline"
        :key="idx"
        class="timeline-item"
        :class="{ active: idx === 0 }"
      >
        <view class="timeline-dot" />
        <view class="timeline-content">
          <text class="timeline-title">{{ node.title }}</text>
          <text class="timeline-time">{{ node.time }}</text>
          <text v-if="node.desc" class="timeline-desc">{{ node.desc }}</text>
        </view>
      </view>
    </view>
  </view>
</template>

<script>
/**
 * 售后进度组件
 * 展示售后申请的处理进度和退货物流信息
 * 通过 componentKey='after_sale_progress' 匹配 section.component
 */
export default {
  name: 'AfterSaleProgress',
  // 匹配 section.component='after_sale_progress'
  componentKey: 'after_sale_progress',
};
</script>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /** section 对象，含 props.afterSale 和 _ctx 全局状态 */
  section: { type: Object, required: true },
});

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 引擎注入的全局状态（预留） */
const ctx = computed(() => (props.section && props.section._ctx) || {});
/** 售后信息对象 */
const afterSale = computed(() => p.value.afterSale || null);
</script>

<style scoped>
/* 统一卡片样式（与引擎其他组件一致） */
.info-card { margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 30rpx; }
.card-title { font-size: 30rpx; font-weight: bold; color: #333; margin-bottom: 20rpx; }
.info-row { display: flex; justify-content: space-between; align-items: center; padding: 12rpx 0; font-size: 26rpx; }
.info-label { color: #999; flex-shrink: 0; }
.info-value { color: #333; text-align: right; }

/* 售后进度专属样式 */
.after-sale-card { margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 30rpx; }
.status-done { color: #07c160; font-weight: 500; }
.status-pending { color: #ff976a; }
.refund-amount { color: var(--view-theme, #e93323); font-weight: 600; }
.after-sale-timeline { margin-top: 20rpx; padding-top: 20rpx; border-top: 1rpx solid #f5f5f5; }
.timeline-item { display: flex; padding-bottom: 24rpx; position: relative; }
.timeline-item:last-child { padding-bottom: 0; }
.timeline-dot { width: 16rpx; height: 16rpx; border-radius: 50%; background: #ddd; margin-top: 8rpx; margin-right: 16rpx; flex-shrink: 0; }
.timeline-item.active .timeline-dot { background: var(--view-theme, #e93323); }
.timeline-title { font-size: 28rpx; color: #333; display: block; }
.timeline-item.active .timeline-title { color: var(--view-theme, #e93323); font-weight: bold; }
.timeline-time { font-size: 24rpx; color: #999; display: block; margin-top: 4rpx; }
.timeline-desc { font-size: 24rpx; color: #666; display: block; margin-top: 4rpx; }
</style>
