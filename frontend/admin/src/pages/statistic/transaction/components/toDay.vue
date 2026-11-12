<template>
  <el-card shadow="never" class="today-card" v-loading="listLoading">
    <template v-if="statisticsData">
      <el-row :gutter="16">
        <el-col v-bind="grid" class="today-left">
          <div class="panel-head acea-row row-middle row-between">
            <div>
              <div class="panel-label">今日订单金额</div>
              <div class="panel-value">¥{{ leftMoney }}</div>
            </div>
            <div class="panel-legend acea-row row-middle">
              <span class="legend-item is-today">今天</span>
              <span class="legend-item is-yesterday">昨天</span>
            </div>
          </div>
          <echarts-new
            v-if="optionData"
            key="today-amount"
            :option-data="optionData"
            :styles="style"
          />
        </el-col>

        <el-col v-bind="grid">
          <div class="metric-grid">
            <div v-for="item in metricCards" :key="item.key" class="metric-card">
              <div class="metric-label">{{ item.title }}</div>
              <div class="metric-value">{{ item.now }}</div>
              <div class="metric-compare">{{ item.compareLabel }}：{{ item.last }}</div>
              <div class="metric-ratio" :class="Number(item.rate) >= 0 ? 'is-up' : 'is-down'">
                {{ item.ratioLabel }}
                <span>{{ Math.floor(Number(item.rate) || 0) }}%</span>
                <el-icon>
                  <CaretTop v-if="Number(item.rate) >= 0" />
                  <CaretBottom v-else />
                </el-icon>
              </div>
              <echarts-new
                v-if="item.option"
                :key="item.key"
                :option-data="item.option"
                :styles="styleToday"
              />
            </div>
          </div>
        </el-col>
      </el-row>
    </template>
  </el-card>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue';
import { ElMessage } from 'element-plus';
import { statisticTopTradeApi } from '@/api/statistic';
import echartsNew from '@/components/echartsNew/index';
import * as echarts from '@/libs/echarts';

defineOptions({ name: 'ToDay' });

const style = { height: '220px', width: '100%' };
const styleToday = { height: '72px', width: '100%' };
const grid = { xl: 12, lg: 12, md: 12, sm: 24, xs: 24 };
const statisticsData = ref(null);
const optionData = ref(null);
const optionTodatOrder = ref(null);
const optionOrderUser = ref(null);
const listLoading = ref(false);

const leftMoney = computed(() => statisticsData.value?.left?.series?.[0]?.money ?? '0');

const metricCards = computed(() => {
  const right = statisticsData.value?.right;
  if (!right) return [];
  return [
    {
      key: 'today-order',
      title: '今日订单数',
      now: right.today.series[0].now_money,
      last: right.today.series[0].last_money,
      rate: right.today.series[0].rate,
      compareLabel: '昨日',
      ratioLabel: '日环比',
      option: optionTodatOrder.value,
    },
    {
      key: 'today-pay',
      title: '今日支付人数',
      now: right.today.series[1].now_money,
      last: right.today.series[1].last_money,
      rate: right.today.series[1].rate,
      compareLabel: '昨日',
      ratioLabel: '日环比',
      option: optionOrderUser.value,
    },
    {
      key: 'month-order',
      title: '本月订单数',
      now: right.month[0].now_money,
      last: right.month[0].last_money,
      rate: right.month[0].rate,
      compareLabel: '上月',
      ratioLabel: '月环比',
      option: null,
    },
    {
      key: 'month-pay',
      title: '本月支付人数',
      now: right.month[1].now_money,
      last: right.month[1].last_money,
      rate: right.month[1].rate,
      compareLabel: '上月',
      ratioLabel: '月环比',
      option: null,
    },
  ];
});

function token(name, fallback) {
  return getComputedStyle(document.documentElement).getPropertyValue(name).trim() || fallback;
}

function hexToRgba(hex, alpha) {
  const raw = String(hex || '').replace('#', '');
  const full = raw.length === 3 ? raw.split('').map((c) => c + c).join('') : raw;
  if (full.length !== 6) return `rgba(91, 143, 249, ${alpha})`;
  const num = parseInt(full, 16);
  return `rgba(${(num >> 16) & 255}, ${(num >> 8) & 255}, ${num & 255}, ${alpha})`;
}

function buildAreaSeries(name, data, color, withArea = true) {
  return {
    name,
    type: 'line',
    data,
    symbol: 'none',
    smooth: true,
    lineStyle: { width: 2, color },
    itemStyle: { color },
    areaStyle: withArea
      ? {
          color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
            { offset: 0, color: hexToRgba(color, 0.28) },
            { offset: 1, color: hexToRgba(color, 0.02) },
          ]),
        }
      : undefined,
  };
}

function buildSparkOption(xData, seriesData) {
  const primary = token('--prev-color-primary', '#0256FF');
  return {
    tooltip: { trigger: 'axis', show: false },
    grid: { left: 0, right: 0, top: 8, bottom: 0 },
    xAxis: {
      type: 'category',
      boundaryGap: false,
      data: xData,
      show: false,
    },
    yAxis: { show: false, type: 'value' },
    series: [buildAreaSeries('今天', seriesData, primary)],
  };
}

function buildMainOption(xData, todayData, yesterdayData) {
  const primary = token('--prev-color-primary', '#0256FF');
  const muted = token('--prev-color-text-placeholder', '#c0c4cc');
  return {
    tooltip: { trigger: 'axis' },
    legend: { show: false },
    grid: { left: 0, right: 8, top: 12, bottom: 0, containLabel: true },
    xAxis: {
      type: 'category',
      boundaryGap: false,
      data: xData,
      axisLine: { show: false },
      axisTick: { show: false },
      axisLabel: { show: false },
    },
    yAxis: {
      type: 'value',
      show: false,
      splitLine: { show: false },
    },
    series: [
      buildAreaSeries('今天', todayData, primary),
      buildAreaSeries('昨天', yesterdayData, muted, true),
    ],
  };
}

function mapSeriesValues(seriesItem) {
  return Object.keys(seriesItem?.value || {}).map((key) => Number(seriesItem.value[key]) || 0);
}

function getList() {
  listLoading.value = true;
  statisticTopTradeApi({ time: 'today' })
    .then((res) => {
      statisticsData.value = res.data;
      const leftOrder = res.data.left;
      const rightOrder = res.data.right;
      const leftToday = mapSeriesValues(leftOrder.series[0]);
      const leftYesterday = mapSeriesValues(leftOrder.series[1]);
      optionData.value = buildMainOption(leftOrder.x, leftToday, leftYesterday);
      optionTodatOrder.value = buildSparkOption(rightOrder.today.x, mapSeriesValues(rightOrder.today.series[0]));
      optionOrderUser.value = buildSparkOption(rightOrder.today.x, mapSeriesValues(rightOrder.today.series[1]));
    })
    .catch((res) => {
      ElMessage.error(res?.msg || '数据加载失败');
    })
    .finally(() => {
      listLoading.value = false;
    });
}

onMounted(() => {
  getList();
});
</script>

<style lang="scss" scoped>
.today-card {
  :deep(.el-card__body) {
    padding: 16px 20px 12px;
  }
}

.today-left {
  @media (min-width: 992px) {
    border-right: 1px solid var(--prev-border-color-extra-light);
    padding-right: 8px;
  }
}

.panel-head {
  margin-bottom: 8px;
}

.panel-label {
  font-size: 14px;
  font-weight: 500;
  color: var(--prev-color-text-regular);
}

.panel-value {
  margin-top: 6px;
  font-size: 28px;
  font-weight: 600;
  line-height: 36px;
  color: var(--prev-color-text-regular);
}

.panel-legend {
  gap: 14px;
}

.legend-item {
  position: relative;
  padding-left: 14px;
  font-size: 12px;
  color: var(--prev-color-text-secondary);

  &::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    transform: translateY(-50%);
  }

  &.is-today::before {
    background: var(--prev-color-primary);
  }

  &.is-yesterday::before {
    background: var(--prev-color-text-placeholder);
  }
}

.metric-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.metric-card {
  padding: 14px 14px 8px;
  border-radius: 8px;
  background: var(--prev-bg-color);
}

.metric-label {
  font-size: 12px;
  color: var(--prev-color-text-secondary);
}

.metric-value {
  margin-top: 6px;
  font-size: 22px;
  font-weight: 500;
  line-height: 30px;
  color: var(--prev-color-text-regular);
}

.metric-compare {
  margin-top: 2px;
  font-size: 12px;
  color: var(--prev-color-text-secondary);
}

.metric-ratio {
  display: inline-flex;
  align-items: center;
  gap: 2px;
  margin-top: 4px;
  font-size: 12px;

  &.is-up {
    color: var(--el-color-success);
  }

  &.is-down {
    color: var(--el-color-danger);
  }
}
</style>
