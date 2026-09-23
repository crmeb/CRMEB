<template>
  <el-card v-if="cards.length && hasData" shadow="never" class="schema-cards">
    <div class="schema-cards__row">
      <div v-for="card in cards" :key="card.field" class="schema-cards__item">
        <div class="schema-cards__label">{{ card.label }}</div>
        <div class="schema-cards__value">{{ formatValue(card) }}</div>
      </div>
    </div>
  </el-card>
</template>

<script setup>
/**
 * 通用页面统计卡片行
 *
 * 渲染 config.cards 声明的指标卡片，数据来自 list 响应的 summary 键（按 field 取值）；
 * type 仅影响展示格式：amount 金额（¥ 两位小数）/ number 千分位 / text 原样。
 */
import { computed } from 'vue';
import { formatNumber } from '../types';

defineOptions({ name: 'SchemaCards' });

const props = defineProps({
  /** 卡片声明列表 [{field, label, type}] */
  cards: {
    type: Array,
    default: () => [],
  },
  /** 指标数据（list 响应 summary 键，field => 值） */
  data: {
    type: Object,
    default: null,
  },
});

/** 指标数据是否已就绪（未返回前不渲染，避免闪烁空卡片） */
const hasData = computed(() => !!props.data);

/**
 * 按声明类型格式化卡片值
 * @param {object} card 卡片声明
 * @returns {string}
 */
function formatValue(card) {
  const value = props.data?.[card.field];
  if (value === undefined || value === null || value === '') return '-';
  if (card.type === 'amount') {
    const num = Number(value);
    return Number.isFinite(num) ? `¥${num.toFixed(2)}` : String(value);
  }
  if (card.type === 'number') {
    return formatNumber(value);
  }
  return String(value);
}
</script>

<style lang="scss" scoped>
.schema-cards {
  margin-bottom: 12px;

  :deep(.el-card__body) {
    padding: 16px 20px;
  }

  &__row {
    display: flex;
    flex-wrap: wrap;
    gap: 12px 48px;
  }

  &__item {
    min-width: 120px;
  }

  &__label {
    font-size: 13px;
    color: var(--el-text-color-secondary);
    margin-bottom: 6px;
  }

  &__value {
    font-size: 22px;
    font-weight: 600;
    color: var(--el-text-color-primary);
    line-height: 1.2;
  }
}
</style>
