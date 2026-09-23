<!--
  关联订单列表组件
  展示拆单/换货的关联订单，点击跳转对应订单详情
  数据来源：section.props.items（每项含 {order_no, relation, title, status_text}）
-->
<template>
  <view class="info-card related-order-card" v-if="items && items.length > 0">
    <view class="card-title">{{ $t('关联订单') }}</view>
    <view
      v-for="(item, idx) in items"
      :key="idx"
      class="related-order-item acea-row row-between"
      @tap="$emit('click', item)"
    >
      <view class="related-order-info">
        <text class="related-order-title">{{ item.title || '' }}</text>
        <text class="related-order-no">{{ item.order_no || '' }}</text>
      </view>
      <view class="related-order-status">
        <text class="related-order-relation">{{ item.relation_text || item.relation || '' }}</text>
        <text class="related-order-status-text">{{ item.status_text || '' }}</text>
      </view>
    </view>
  </view>
</template>

<script>
/**
 * 关联订单列表组件
 * 展示拆单/换货的关联订单，点击 emit('click', item) 跳转
 * 通过 componentKey='related_order_list' 匹配 section.component
 */
export default {
  name: 'RelatedOrderList',
  // 匹配 section.component='related_order_list'
  componentKey: 'related_order_list',
};
</script>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /** section 对象，含 props.items 和 _ctx 全局状态 */
  section: { type: Object, required: true },
});

defineEmits(['click']);

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 引擎注入的全局状态（预留） */
const ctx = computed(() => (props.section && props.section._ctx) || {});
/** 关联订单数组 */
const items = computed(() => p.value.items || []);
</script>

<style scoped>
/* 统一卡片样式（与引擎其他组件一致） */
.info-card { margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 30rpx; }
.card-title { font-size: 30rpx; font-weight: bold; color: #333; margin-bottom: 20rpx; }

/* 关联订单列表专属样式 */
.related-order-card { margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 30rpx; }
.related-order-item { display: flex; justify-content: space-between; align-items: center; padding: 16rpx 0; border-bottom: 1rpx solid #f5f5f5; }
.related-order-item:last-child { border-bottom: none; }
.related-order-title { font-size: 28rpx; color: #333; display: block; }
.related-order-no { font-size: 24rpx; color: #999; display: block; margin-top: 4rpx; }
.related-order-status { text-align: right; }
.related-order-relation { font-size: 24rpx; color: var(--view-theme, #e93323); display: block; }
.related-order-status-text { font-size: 24rpx; color: #999; display: block; margin-top: 4rpx; }
</style>
