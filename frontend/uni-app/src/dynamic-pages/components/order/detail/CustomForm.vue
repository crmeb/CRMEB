<!--
  自定义表单回显组件（DIY 通用化契约版）
  展示用户下单时填写的自定义字段（如观众姓名、联系方式等）
  数据来源：section.props.items（表单字段数组）
-->
<template>
  <view class="info-card custom-form-card" v-if="items && items.length > 0">
    <view class="card-title">{{ $t('自定义信息') }}</view>
    <view
      class="info-row acea-row row-between"
      v-for="(item, idx) in items"
      :key="idx"
    >
      <text class="info-label">{{ item.title || item.label }}：</text>
      <text class="info-value">{{ item.value }}</text>
    </view>
  </view>
</template>

<script>
/**
 * 自定义表单回显组件
 * 展示用户下单时填写的自定义字段
 * 字段结构：{title/label, value}
 * 通过 componentKey='custom_form' 匹配 section.component
 */
export default {
  name: 'CustomForm',
  // DIY 引擎 componentKey 标识
  componentKey: 'custom_form',
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
/** 引擎注入的全局状态（预留，当前 CustomForm 不依赖全局状态） */
const ctx = computed(() => (props.section && props.section._ctx) || {});
/** 表单字段数组（后端下发在 section.props.items） */
const items = computed(() => p.value.items || []);
</script>

<style scoped>
.custom-form-card { margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 30rpx; }
.card-title { font-size: 30rpx; font-weight: bold; color: #333; margin-bottom: 20rpx; }
.info-row { display: flex; justify-content: space-between; align-items: center; padding: 12rpx 0; font-size: 26rpx; }
.info-label { color: #999; flex-shrink: 0; }
.info-value { color: #333; text-align: right; }
</style>
