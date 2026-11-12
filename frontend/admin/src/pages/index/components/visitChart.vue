<template>
  <div>
    <el-card shadow="never" class="dashboard-panel ivu-mb">
      <div class="panel-head acea-row row-between-wrapper">
        <div class="panel-title acea-row row-middle">
          <span class="panel-icon">
            <el-icon :size="16"><DataAnalysis /></el-icon>
          </span>
          <h4>订单趋势</h4>
        </div>
        <el-radio-group v-model="visitDate" @change="getStatistics">
          <el-radio-button label="thirtyday" value="thirtyday">30天</el-radio-button>
          <el-radio-button label="week" value="week">周</el-radio-button>
          <el-radio-button label="month" value="month">月</el-radio-button>
          <el-radio-button label="year" value="year">年</el-radio-button>
        </el-radio-group>
      </div>

      <div v-if="summary" class="summary-row">
        <div class="summary-item">
          <div class="summary-label">本期订单金额</div>
          <div class="summary-value">¥{{ formatNum(summary.price.data) }}</div>
          <div class="summary-ratio" :class="ratioClass(summary.price.is_plus)">
            环比 {{ summary.price.percent }}%
            <el-icon>
              <CaretTop v-if="summary.price.is_plus >= 0" />
              <CaretBottom v-else />
            </el-icon>
          </div>
        </div>
        <div class="summary-item">
          <div class="summary-label">本期订单数</div>
          <div class="summary-value">{{ summary.count.data }}</div>
          <div class="summary-ratio" :class="ratioClass(summary.count.is_plus)">
            环比 {{ summary.count.percent }}%
            <el-icon>
              <CaretTop v-if="summary.count.is_plus >= 0" />
              <CaretBottom v-else />
            </el-icon>
          </div>
        </div>
      </div>

      <echarts-new
        v-if="chartOption"
        ref="chartRef"
        :option-data="chartOption"
        :styles="chartStyles"
      />
    </el-card>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import { orderApi } from '@/api/index';
import echartsNew from '@/components/echartsNew/index.vue';
import * as echarts from '@/libs/echarts';

defineOptions({ name: 'visitChart' });

const chartRef = ref(null);
const visitDate = ref('thirtyday');
const summary = ref(null);
const chartOption = ref(null);
const chartStyles = { width: '100%', height: '320px' };

function token(name, fallback) {
  const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
  return value || fallback;
}

function hexToRgba(hex, alpha) {
  const raw = String(hex || '').replace('#', '');
  const full = raw.length === 3 ? raw.split('').map((c) => c + c).join('') : raw;
  if (full.length !== 6) return `rgba(2, 86, 255, ${alpha})`;
  const num = parseInt(full, 16);
  const r = (num >> 16) & 255;
  const g = (num >> 8) & 255;
  const b = num & 255;
  return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

function formatNum(val) {
  const num = Number(val || 0);
  return Number.isFinite(num) ? num.toFixed(2) : '0.00';
}

function ratioClass(isPlus) {
  return Number(isPlus) >= 0 ? 'is-up' : 'is-down';
}

function buildSeries(list, primary, success) {
  return (list || []).map((item) => {
    if (item.type === 'bar') {
      return {
        ...item,
        barMaxWidth: 22,
        barGap: '40%',
        itemStyle: {
          borderRadius: [4, 4, 0, 0],
          color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
            { offset: 0, color: primary },
            { offset: 1, color: hexToRgba(primary, 0.35) },
          ]),
        },
        emphasis: {
          focus: 'series',
        },
      };
    }
    if (item.type === 'line') {
      return {
        ...item,
        smooth: true,
        showSymbol: false,
        symbol: 'circle',
        symbolSize: 6,
        lineStyle: {
          width: 2.5,
          color: success,
        },
        itemStyle: {
          color: success,
        },
        areaStyle: {
          color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
            { offset: 0, color: hexToRgba(success, 0.18) },
            { offset: 1, color: hexToRgba(success, 0.02) },
          ]),
        },
        emphasis: {
          focus: 'series',
        },
      };
    }
    return item;
  });
}

function seriesMax(list, type) {
  return (list || [])
    .filter((item) => item.type === type)
    .reduce((max, item) => Math.max(max, ...(item.data || []).map((n) => Number(n) || 0)), 0);
}

function buildOption(data) {
  const primary = token('--prev-color-primary', '#0256FF');
  const textSecondary = token('--prev-color-text-secondary', '#909399');
  const border = token('--prev-border-color-extra-light', '#f2f6fc');
  const success = token('--el-color-success', '#67c23a');
  const xLen = (data.xAxis || []).length;
  const interval = xLen > 14 ? Math.ceil(xLen / 7) - 1 : 0;
  const maxPrice = seriesMax(data.series, 'bar');
  const maxCount = seriesMax(data.series, 'line');

  return {
    color: [primary, success],
    tooltip: {
      trigger: 'axis',
      axisPointer: {
        type: 'shadow',
        shadowStyle: {
          color: hexToRgba(primary, 0.06),
        },
      },
    },
    legend: {
      top: 0,
      left: 'center',
      itemWidth: 10,
      itemHeight: 10,
      icon: 'roundRect',
      textStyle: {
        color: textSecondary,
        fontSize: 12,
      },
      data: data.legend || [],
    },
    grid: {
      left: 8,
      right: 12,
      top: 48,
      bottom: 4,
      containLabel: true,
    },
    xAxis: {
      type: 'category',
      boundaryGap: true,
      data: data.xAxis || [],
      axisLine: {
        lineStyle: { color: border },
      },
      axisTick: { show: false },
      axisLabel: {
        color: textSecondary,
        fontSize: 11,
        interval,
        rotate: 0,
        hideOverlap: true,
        margin: 12,
      },
    },
    yAxis: [
      {
        type: 'value',
        name: '金额',
        min: 0,
        max: maxPrice > 0 ? undefined : 5,
        splitNumber: 5,
        nameTextStyle: { color: textSecondary, fontSize: 11, padding: [0, 0, 4, 0] },
        axisLine: { show: false },
        axisTick: { show: false },
        axisLabel: { color: textSecondary, fontSize: 11 },
        splitLine: {
          lineStyle: {
            color: border,
            type: 'dashed',
          },
        },
      },
      {
        type: 'value',
        name: '数量',
        min: 0,
        max: maxCount > 0 ? undefined : 5,
        minInterval: 1,
        splitNumber: 5,
        nameTextStyle: { color: textSecondary, fontSize: 11, padding: [0, 0, 4, 0] },
        axisLine: { show: false },
        axisTick: { show: false },
        axisLabel: { color: textSecondary, fontSize: 11 },
        splitLine: { show: false },
      },
    ],
    series: buildSeries(data.series, primary, success),
  };
}

function getStatistics() {
  orderApi({ cycle: visitDate.value })
    .then((res) => {
      const data = res.data || {};
      summary.value = data.cycle || null;
      chartOption.value = buildOption(data);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

getStatistics();
</script>

<style lang="scss" scoped>
.dashboard-panel {
  :deep(.el-card__body) {
    padding: 16px 20px 12px;
  }
}

.panel-head {
  margin-bottom: 14px;
}

.panel-title {
  h4 {
    margin: 0;
    font-size: 14px;
    font-weight: 500;
    color: var(--prev-color-text-regular);
  }
}

.panel-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  margin-right: 8px;
  border-radius: 6px;
  color: var(--prev-color-primary);
  background: var(--prev-color-primary-light-9);
}

.summary-row {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
  margin-bottom: 12px;
}

.summary-item {
  padding: 12px 14px;
  border-radius: 6px;
  background: var(--prev-bg-color);
}

.summary-label {
  font-size: 12px;
  color: var(--prev-color-text-secondary);
}

.summary-value {
  margin-top: 4px;
  font-size: 22px;
  font-weight: 500;
  line-height: 30px;
  color: var(--prev-color-text-regular);
}

.summary-ratio {
  display: inline-flex;
  align-items: center;
  gap: 2px;
  margin-top: 2px;
  font-size: 12px;

  &.is-up {
    color: var(--el-color-success);
  }

  &.is-down {
    color: var(--el-color-danger);
  }
}
</style>
