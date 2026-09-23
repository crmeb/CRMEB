<!--
  订单时间线基元
  双模式：
  1. 引擎直渲染模式（section prop）：匹配 section.component='timeline'，从 section.props.items 取时间线节点
  2. 内部复用模式（timeline prop）：LogisticsTimeline 通过 :timeline="tracks" 传入节点数组
 两种模式自动切换，items computed 优先从 section 取，回退到 timeline prop
-->
<template>
  <view v-if="items && items.length > 0" class="order-timeline">
    <view v-for="(item, idx) in items" :key="idx" class="timeline-item" :class="{ active: idx === 0 }">
      <view class="timeline-dot" />
      <view class="timeline-content">
        <text class="timeline-title">{{ item.title }}</text>
        <text class="timeline-time">{{ item.time }}</text>
        <text v-if="item.desc" class="timeline-desc">{{ item.desc }}</text>
      </view>
    </view>
  </view>
</template>

<script>
/**
 * 订单时间线基元组件
 * 两种使用方式：
 * 1. 引擎直接渲染：传 section prop，从 section.props.items 取数据
 * 2. 内部复用：传 timeline prop，由父组件（如 LogisticsTimeline）传入节点数组
 * 通过 componentKey='timeline' 匹配 section.component='timeline'（非 logistics 的时间线）
 */
export default {
  name: 'PrimitiveOrderTimeline',
  // 匹配 section.component='timeline'（非 logistics 的时间线，logistics 由 LogisticsTimeline 复合 key 接管）
  componentKey: 'timeline',
};
</script>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /** section 对象，引擎直渲染模式下传入，含 props.items 和 _ctx 全局状态 */
  section: { type: Object, default: null },
  /** 时间线节点数组，内部复用模式下由父组件传入（每项含 {title, time, desc}） */
  timeline: { type: Array, default: () => [] },
});

/** 后端直填 props（仅 section 模式下有值） */
const p = computed(() => (props.section && props.section.props) || {});
/** 引擎注入的全局状态（预留） */
const ctx = computed(() => (props.section && props.section._ctx) || {});
/**
 * 时间线节点列表
 * 优先从 section.props.items 取（引擎直渲染模式）
 * 回退到 timeline prop（内部复用模式，如 LogisticsTimeline 嵌套调用）
 */
const items = computed(() => {
  if (props.section && props.section.props) return props.section.props.items || [];
  return props.timeline;
});
</script>

<style scoped>
/* 时间线专属样式（自包含） */
.order-timeline { margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 30rpx; }
.timeline-item { display: flex; padding-bottom: 30rpx; position: relative; }
.timeline-item:last-child { padding-bottom: 0; }
.timeline-dot { width: 16rpx; height: 16rpx; border-radius: 50%; background: #ddd; margin-top: 8rpx; margin-right: 20rpx; flex-shrink: 0; }
.timeline-item.active .timeline-dot { background: var(--view-theme, #e93323); }
.timeline-title { font-size: 28rpx; color: #333; display: block; }
.timeline-item.active .timeline-title { color: var(--view-theme, #e93323); font-weight: bold; }
.timeline-time { font-size: 24rpx; color: #999; display: block; margin-top: 4rpx; }
.timeline-desc { font-size: 24rpx; color: #666; display: block; margin-top: 4rpx; }
</style>
