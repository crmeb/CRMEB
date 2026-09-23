<!--
  订单确认页 - display_sections block 选中信息展示
  通用组件：适配门票(日期/场次/票种)、酒店(入住/离店/房型)、预约(时段/项目)等所有产品类型
  数据来源：详情页 specPreview blocks 选中后通过 URL block_data 参数透传到下单页
-->
<template>
  <view class="block-info-card" v-if="visible && items.length">
    <view class="block-info-card__title">{{ $t('选购信息') }}</view>
    <view class="block-info-card__row" v-for="(info, i) in items" :key="i">
      <text class="block-info-card__label">{{ info.label }}</text>
      <text class="block-info-card__value">{{ info.value }}</text>
    </view>
  </view>
</template>

<script setup>
import { computed } from 'vue';
import i18n from '@/utils/lang.js';

defineOptions({ name: 'OrderBlockInfo' });

const props = defineProps({
  /** 详情页 display_sections block 选中数据数组 */
  blockData: { type: Array, default: () => [] },
  /** 是否显示 */
  visible: { type: Boolean, default: true },
});

/** 将 blockData 格式化为 {label, value} 列表 */
const items = computed(() => {
  if (!Array.isArray(props.blockData) || !props.blockData.length) return [];
  var result = [];
  for (var i = 0; i < props.blockData.length; i++) {
    var block = props.blockData[i];
    if (!block || !block.type) continue;
    var formatted = formatBlock(block);
    if (formatted) result.push(formatted);
  }
  return result;
});

/**
 * 按 block type 格式化选中项为 {label, value}
 * 新增 block 类型时在此追加分支即可
 * @param {Object} block {type, item}
 * @returns {Object|null} {label, value}
 */
function formatBlock(block) {
  var item = block.item || {};
  switch (block.type) {
    case 'calendar':
      // 日历：日期选择（门票游览日期 / 酒店入住日期范围）
      // 酒店预定返回 check_in + check_out，门票返回 date
      if (item.check_in && item.check_out) {
        return {
          label: i18n.t('入住日期'),
          value: item.check_in + ' ~ ' + item.check_out
        };
      }
      return { label: i18n.t('日期'), value: item.date || '' };
    case 'time_slots':
      // 时段：场次/预约时段
      return {
        label: i18n.t('时段'),
        value: (item.name || '') + (item.time ? ' ' + item.time : ''),
      };
    case 'price_list':
      // 起价列表：票种/房型/套餐
      return {
        label: i18n.t('类型'),
        value: (item.name || '') + (item.price_from ? ' ¥' + item.price_from : ''),
      };
    case 'chips':
      // 属性标签
      return { label: i18n.t('属性'), value: item.name || item.value || '' };
    case 'matrix':
      // 规格矩阵
      return { label: i18n.t('规格'), value: item.suk || item.name || '' };
    default:
      // 未知 block 类型：尝试通用字段兜底
      return item.name
        ? { label: i18n.t('选项'), value: item.name }
        : null;
  }
}
</script>

<style scoped>
.block-info-card {
  margin: 20rpx 24rpx;
  padding: 24rpx;
  background: #fff;
  border-radius: 16rpx;
  border-left: 6rpx solid var(--view-theme, #e93323);
}
.block-info-card__title {
  font-size: 28rpx;
  font-weight: bold;
  color: #333;
  margin-bottom: 16rpx;
}
.block-info-card__row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8rpx 0;
}
.block-info-card__label {
  font-size: 26rpx;
  color: #999;
  flex-shrink: 0;
}
.block-info-card__value {
  font-size: 26rpx;
  color: #333;
  font-weight: 500;
  text-align: right;
}
</style>
