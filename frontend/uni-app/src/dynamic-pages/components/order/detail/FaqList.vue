<!--
  常见问题列表组件
  展示订单相关的常见问题和解答
  数据来源：section.props.items（每项含 {q, a}）
-->
<template>
  <view class="info-card faq-card" v-if="items && items.length > 0">
    <view class="card-title">{{ $t('常见问题') }}</view>
    <view
      v-for="(item, idx) in items"
      :key="idx"
      class="faq-item"
    >
      <view class="faq-q">Q：{{ item.q || '' }}</view>
      <view class="faq-a">A：{{ item.a || '' }}</view>
    </view>
  </view>
</template>

<script>
/**
 * 常见问题列表组件
 * 展示订单相关的 Q&A 列表
 * 通过 componentKey='faq_list' 匹配 section.component
 */
export default {
  name: 'FaqList',
  // 匹配 section.component='faq_list'
  componentKey: 'faq_list',
};
</script>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /** section 对象，含 props.items 和 _ctx 全局状态 */
  section: { type: Object, required: true },
});

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 引擎注入的全局状态（预留） */
const ctx = computed(() => (props.section && props.section._ctx) || {});
/** 问答数组 */
const items = computed(() => p.value.items || []);
</script>

<style scoped>
/* 统一卡片样式（与引擎其他组件一致） */
.info-card { margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 30rpx; }
.card-title { font-size: 30rpx; font-weight: bold; color: #333; margin-bottom: 20rpx; }

/* 常见问题专属样式 */
.faq-card { margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 30rpx; }
.faq-item { padding-bottom: 20rpx; border-bottom: 1rpx solid #f5f5f5; margin-bottom: 20rpx; }
.faq-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
.faq-q { font-size: 28rpx; color: #333; font-weight: 500; margin-bottom: 8rpx; }
.faq-a { font-size: 26rpx; color: #666; line-height: 1.6; }
</style>
