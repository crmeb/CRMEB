<!--
  通用信息列表基元（万能降级组件）
  兜底匹配所有 info_list:* 未命中的 section，以及任何未注册 component 的 section
  兼容两种 fields 格式：
  1. 数组 [{label, value}]（推荐，后端直填标签）
  2. 对象 {key: value}（旧格式兜底，用 key 作为标签）
-->
<template>
  <view v-if="rows.length > 0" class="info-card">
    <view v-if="title" class="card-title">{{ title }}</view>
    <view
      v-for="(row, idx) in rows"
      :key="idx"
      class="info-row acea-row row-between"
    >
      <text class="info-label">{{ row.label }}</text>
      <text class="info-value">{{ row.value }}</text>
    </view>
  </view>
</template>

<script>
/**
 * 通用信息列表组件
 * 作为 section 未命中专用组件时的降级渲染，把 fields 渲染成键值对列表
 */
// 兜底匹配所有 info_list:* 未命中的 section（复合 key 优先，单 key 兜底）
export default {
  name: 'InfoList',
  componentKey: 'info_list',
};
</script>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /** section 对象，含 props.fields / props.title */
  section: { type: Object, required: true },
});

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 卡片标题：后端下发 title 才显示，默认不显示 */
const title = computed(() => p.value.title || '');
/** 待展示行列表 */
const rows = computed(() => buildRows(p.value.fields));

/**
 * 把 fields 转为行数据数组
 * 迁移自引擎 getInfoListRows，兼容数组+对象两种格式
 * @param {array|object} fields 字段数据
 * @return {Array<{label:string, value:string}>} 行数据
 */
function buildRows(fields) {
  if (!fields) return [];
  // 数组格式：后端直填 label，前端直接渲染
  if (Array.isArray(fields)) {
    return fields
      .filter(function (f) {
        return f && f.value !== null && f.value !== undefined && f.value !== '';
      })
      .map(function (f) {
        return { label: f.label || '', value: String(f.value) };
      });
  }
  // 兼容旧对象格式：用 key 作为标签
  if (typeof fields !== 'object') return [];
  var rows = [];
  for (var key in fields) {
    if (!Object.prototype.hasOwnProperty.call(fields, key)) continue;
    var val = fields[key];
    // 跳过复杂对象，仅展示标量
    if (val !== null && val !== undefined && typeof val === 'object') continue;
    if (val === null || val === undefined || val === '') continue;
    rows.push({ label: key, value: String(val) });
  }
  return rows;
}
</script>

<style scoped>
.info-card { margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 30rpx; }
.card-title { font-size: 30rpx; font-weight: bold; color: #333; margin-bottom: 20rpx; }
.info-row { display: flex; justify-content: space-between; align-items: center; padding: 12rpx 0; font-size: 26rpx; }
.info-label { color: #999; flex-shrink: 0; }
.info-value { color: #333; text-align: right; }
</style>
