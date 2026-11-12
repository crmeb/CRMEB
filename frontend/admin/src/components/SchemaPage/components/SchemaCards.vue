<template>
  <el-row v-if="split && hasData" :gutter="20" class="schema-stats">
    <el-col v-for="card in cards" :key="card.field" :span="statSpan">
      <el-card shadow="never" class="schema-stats__card">
        <div class="schema-stats__item">
          <div class="schema-stats__icon" :class="themeClass(card)">
            <i :class="card.icon"></i>
          </div>
          <div class="schema-stats__info">
            <div class="schema-stats__value">{{ formatValue(card) }}</div>
            <div class="schema-stats__label">{{ card.label }}</div>
          </div>
        </div>
      </el-card>
    </el-col>
  </el-row>
  <el-card v-else-if="cards.length && hasData" shadow="never" class="schema-cards">
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
 * 任一项声明 icon 时改为订单中台式分卡：每项独立成框，左侧图标、右侧数值与标题。
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

/** 声明了图标的卡片按订单中台统计卡分框展示 */
const split = computed(() => props.cards.some((card) => !!card.icon));

/** 分卡时按张数铺满一行（两张各占一半，四张与订单中台一致各占四分之一） */
const statSpan = computed(() => {
  const count = props.cards.length || 1;
  const span = Math.floor(24 / count);
  return span >= 6 ? span : 6;
});

const CARD_THEMES = ['primary', 'success', 'warning', 'danger'];

/**
 * 图标底色主题，未知值回落主色
 * @param {object} card 卡片声明
 * @returns {string}
 */
function themeClass(card) {
  const theme = CARD_THEMES.includes(card.theme) ? card.theme : 'primary';
  return `is-${theme}`;
}

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
.schema-stats {
  margin-bottom: 16px;

  &__item {
    display: flex;
    align-items: center;
  }

  &__icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-right: 15px;
    color: var(--prev-color-text-white);
    flex-shrink: 0;

    &.is-primary {
      background: linear-gradient(135deg, var(--prev-color-primary) 0%, var(--prev-color-primary-light-3) 100%);
    }

    &.is-success {
      background: linear-gradient(135deg, var(--prev-color-success) 0%, var(--prev-color-primary-light-3) 100%);
    }

    &.is-warning {
      background: linear-gradient(135deg, var(--prev-color-warning) 0%, var(--prev-color-danger) 100%);
    }

    &.is-danger {
      background: linear-gradient(135deg, var(--prev-color-danger) 0%, var(--prev-color-warning) 100%);
    }
  }

  &__value {
    font-size: 24px;
    font-weight: 700;
    color: var(--prev-color-text-regular);
    line-height: 1.2;
  }

  &__label {
    font-size: 14px;
    color: var(--prev-color-text-secondary);
    margin-top: 5px;
  }
}

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
