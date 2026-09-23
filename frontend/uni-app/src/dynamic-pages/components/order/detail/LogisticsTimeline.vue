<!--
  物流轨迹组件（timeline:logistics 专用）
  自包含：卡片标题"物流轨迹" + 物流头部（快递公司+单号） + 内嵌 OrderTimeline 时间线
  复用 OrderTimeline 基元渲染时间线节点
-->
<template>
  <view v-if="tracks.length > 0 || deliveryName || deliveryId" class="info-card">
    <view class="card-title">{{ $t('物流轨迹') }}</view>
    <!-- 物流头部：快递公司 + 快递单号 -->
    <view v-if="deliveryName || deliveryId" class="logistics-header">
      <text>{{ deliveryName }} {{ deliveryId }}</text>
    </view>
    <!-- 时间线节点（复用 OrderTimeline 基元） -->
    <order-timeline :timeline="tracks" />
  </view>
</template>

<script>
/**
 * 物流轨迹组件
 * 展示快递公司、单号、物流轨迹时间线
 * 复用 OrderTimeline 基元渲染时间线节点
 */
export default {
  name: 'LogisticsTimeline',
  // 复合 key，匹配 section.component='timeline' + section.key='logistics'
  componentKey: 'timeline:logistics',
};
</script>

<script setup>
import { computed } from 'vue';
import OrderTimeline from './OrderTimeline.vue';

const props = defineProps({
  /** section 对象，含 props.delivery_name / delivery_id / tracks */
  section: { type: Object, required: true },
});

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 快递公司名称 */
const deliveryName = computed(() => p.value.delivery_name || '');
/** 快递单号 */
const deliveryId = computed(() => p.value.delivery_id || '');
/** 物流轨迹节点数组 */
const tracks = computed(() => p.value.tracks || []);
</script>

<style scoped>
.info-card { margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 30rpx; }
.card-title { font-size: 30rpx; font-weight: bold; color: #333; margin-bottom: 20rpx; }
/* 物流头部：快递公司 + 单号 */
.logistics-header { padding: 12rpx 0; font-size: 26rpx; color: #666; border-bottom: 1rpx solid #f5f5f5; margin-bottom: 16rpx; }
</style>
