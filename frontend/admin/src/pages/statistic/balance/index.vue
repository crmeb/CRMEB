<template>
  <div v-loading="spinShow">
    <cards-data :cardLists="cardLists" v-if="cardLists.length >= 0"></cards-data>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16">
      <div class="acea-row row-middle">
        <span class="label_text">时间选择：</span>
        <date-range-picker
          width="250px"
          clearable
          v-model="timeVal"
          :editable="false"
          @change="onchangeTime"
          format="YYYY/MM/DD"
          value-format="YYYY/MM/DD"
          class="mr20"
        ></date-range-picker>
      </div>
    </el-card>
    <el-card class="ivu-mb-16 dashboard-panel" shadow="never">
      <div class="panel-title acea-row row-middle">
        <span class="panel-icon">
          <el-icon :size="16"><TrendCharts /></el-icon>
        </span>
        <h4>余额使用趋势</h4>
      </div>
      <echarts-new :option-data="optionData" :styles="style" v-if="optionData"></echarts-new>
    </el-card>
    <el-row :gutter="16">
      <el-col v-for="panel in ringPanels" :key="panel.key" :xl="12" :lg="12" :md="24" :sm="24" :xs="24" class="ivu-mb">
        <el-card v-loading="panel.loading" shadow="never" class="dashboard-panel">
          <div class="panel-title acea-row row-middle">
            <span class="panel-icon">
              <el-icon :size="16"><component :is="panel.icon" /></el-icon>
            </span>
            <h4>{{ panel.title }}</h4>
          </div>
          <div v-if="panel.stats.length" class="ring-body">
            <div class="ring-chart">
              <echarts-new v-if="panel.option" :option-data="panel.option" :styles="ringChartStyles" />
              <div class="ring-center">
                <div class="ring-center__num">{{ panel.totalText }}</div>
                <div class="ring-center__label">{{ panel.centerLabel }}</div>
              </div>
            </div>
            <div class="ring-legend">
              <div v-for="item in panel.stats" :key="item.name" class="ring-legend__item">
                <span class="ring-legend__dot" :style="{ background: item.color }"></span>
                <div class="ring-legend__main">
                  <div class="ring-legend__name">{{ item.name }}</div>
                  <div class="ring-legend__meta">
                    <span>{{ item.valueText }}</span>
                    <span>{{ item.percent }}%</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <el-empty v-else-if="!panel.loading" description="暂无统计数据" :image-size="64" />
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { ElMessage } from 'element-plus';
import cardsData from '@/components/cards/cards';
import echartsNew from '@/components/echartsNew/index';
import * as echarts from '@/libs/echarts';
import {
  getBalanceBasic as getBalanceBasicApi,
  getBalanceTrend as getBalanceTrendApi,
  getBalanceChannel as getBalanceChannelApi,
  getBalanceType as getBalanceTypeApi,
} from '@/api/statistic';
import { formatDate } from '@/utils/validate';

defineOptions({ name: 'index' });

const ringColors = ['#5B8FF9', '#B37FEB', '#5AD8A6', '#F6BD16', '#E8684A'];
const ringChartStyles = { width: '100%', height: '220px' };

const timeVal = ref([]);
const style = { height: '400px' };
const sourceLoading = ref(false);
const consumeLoading = ref(false);
const sourceStats = ref([]);
const sourceOption = ref(null);
const consumeStats = ref([]);
const consumeOption = ref(null);
const formValidate = ref({
  time: '',
});
const cardLists = ref([
  {
    col: 8,
    count: 0,
    name: '当前余额',
    className: 'iconyuexiaohaojine',
  },
  {
    col: 8,
    count: 0,
    name: '累计余额',
    className: 'iconyuechongzhi',
  },
  {
    col: 8,
    count: 0,
    name: '累计消耗余额',
    className: 'iconyuexiaohaojine',
  },
]);
const optionData = ref({});
const spinShow = ref(false);

const ringPanels = computed(() => [
  {
    key: 'source',
    title: '余额来源分析',
    icon: 'Coin',
    centerLabel: '来源合计',
    stats: sourceStats.value,
    option: sourceOption.value,
    loading: sourceLoading.value,
    totalText: formatAmount(sourceStats.value.reduce((sum, item) => sum + item.value, 0)),
  },
  {
    key: 'consume',
    title: '余额消耗',
    icon: 'Wallet',
    centerLabel: '消耗合计',
    stats: consumeStats.value,
    option: consumeOption.value,
    loading: consumeLoading.value,
    totalText: formatAmount(consumeStats.value.reduce((sum, item) => sum + item.value, 0)),
  },
]);

// created
const end = new Date();
const start = new Date();
start.setTime(start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 29)));
timeVal.value = [formatDate(start, 'yyyy/MM/dd'), formatDate(end, 'yyyy/MM/dd')];
formValidate.value.time = timeVal.value.join('-');
onInit();

function onInit() {
  getBalanceBasic();
  getBalanceTrend();
  getBalanceChannel();
  getBalanceType();
}
function onSelectDate(e) {
  formValidate.value.time = e;
  onInit();
}
function getBalanceBasic() {
  getBalanceBasicApi(formValidate.value).then((res) => {
    let arr = ['now_balance', 'add_balance', 'sub_balance'];
    cardLists.value.map((i, index) => {
      i.count = res.data[arr[index]];
    });
  });
}
function token(name, fallback) {
  const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
  return value || fallback;
}
function formatAmount(value) {
  const num = Number(value) || 0;
  return num.toLocaleString('zh-CN', { maximumFractionDigits: 2 });
}
function hexToRgba(hex, alpha) {
  const raw = String(hex || '').replace('#', '');
  const full =
    raw.length === 3
      ? raw
          .split('')
          .map((char) => char + char)
          .join('')
      : raw;
  if (full.length !== 6) return `rgba(2, 86, 255, ${alpha})`;
  const num = parseInt(full, 16);
  return `rgba(${(num >> 16) & 255}, ${(num >> 8) & 255}, ${num & 255}, ${alpha})`;
}
function buildTrendSeries(list) {
  const primary = token('--prev-color-primary', '#0256FF');
  const palette = [primary, '#5AD8A6', '#F6BD16', '#E8684A'];
  return (list || []).map((item, index) => {
    const color = palette[index] || ringColors[index % ringColors.length];
    return {
      name: item.name,
      type: 'line',
      data: item.data,
      smooth: true,
      showSymbol: false,
      symbol: 'circle',
      symbolSize: 7,
      lineStyle: { width: 2.5, color },
      itemStyle: { color, borderColor: token('--prev-bg-white', '#fff'), borderWidth: 2 },
      areaStyle: {
        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
          { offset: 0, color: hexToRgba(color, 0.22) },
          { offset: 1, color: hexToRgba(color, 0.02) },
        ]),
      },
      emphasis: { focus: 'series' },
    };
  });
}
function buildTrendOption(xAxis, series) {
  const textSecondary = token('--prev-color-text-secondary', '#909399');
  const border = token('--prev-border-color-extra-light', '#f2f6fc');
  const primary = token('--prev-color-primary', '#0256FF');
  const xLen = (xAxis || []).length;
  const interval = xLen > 12 ? Math.ceil(xLen / 8) - 1 : 0;
  return {
    tooltip: {
      trigger: 'axis',
      axisPointer: {
        type: 'line',
        lineStyle: { color: hexToRgba(primary, 0.35), type: 'dashed' },
      },
      formatter: (params) => {
        const rows = Array.isArray(params) ? params : [params];
        const title = rows[0]?.axisValueLabel || rows[0]?.axisValue || '';
        const lines = rows.map((item) => `${item.marker}${item.seriesName}　${formatAmount(item.value)}`);
        return [title, ...lines].join('<br/>');
      },
    },
    legend: {
      top: 0,
      left: 'center',
      itemWidth: 10,
      itemHeight: 10,
      icon: 'roundRect',
      textStyle: { color: textSecondary, fontSize: 12 },
    },
    grid: { left: 8, right: 16, top: 40, bottom: 4, containLabel: true },
    xAxis: {
      type: 'category',
      boundaryGap: false,
      data: xAxis,
      axisLine: { lineStyle: { color: border } },
      axisTick: { show: false },
      axisLabel: {
        color: textSecondary,
        fontSize: 11,
        interval,
        hideOverlap: true,
        margin: 12,
      },
    },
    yAxis: {
      type: 'value',
      axisLine: { show: false },
      axisTick: { show: false },
      axisLabel: { color: textSecondary, fontSize: 11 },
      splitLine: {
        lineStyle: { color: border, type: 'dashed' },
      },
    },
    series,
  };
}
function buildRingStats(bingData) {
  const rows = (bingData || []).map((item, index) => ({
    name: item.name,
    value: Number(item.value) || 0,
    color: item.itemStyle?.color || ringColors[index % ringColors.length],
  }));
  const total = rows.reduce((sum, item) => sum + item.value, 0);
  return rows.map((item) => ({
    ...item,
    valueText: formatAmount(item.value),
    percent: total ? Math.round((item.value / total) * 1000) / 10 : 0,
  }));
}
function buildRingOption(rows) {
  const hasValue = rows.some((item) => item.value > 0);
  const data = hasValue ? rows.filter((item) => item.value > 0) : rows.map((item) => ({ ...item, value: 1 }));
  return {
    color: data.map((item) => item.color),
    tooltip: {
      trigger: 'item',
      formatter: (params) => {
        if (!hasValue) return `${params.name}<br/>0（0%）`;
        return `${params.name}<br/>${formatAmount(params.value)}（${params.percent}%）`;
      },
    },
    series: [
      {
        type: 'pie',
        radius: ['58%', '78%'],
        center: ['50%', '50%'],
        avoidLabelOverlap: true,
        padAngle: 3,
        itemStyle: {
          borderRadius: 6,
          borderColor: token('--prev-bg-white', '#fff'),
          borderWidth: 2,
        },
        label: { show: false },
        labelLine: { show: false },
        emphasis: {
          scale: true,
          scaleSize: 6,
          itemStyle: {
            shadowBlur: 12,
            shadowColor: 'rgba(0, 0, 0, 0.08)',
          },
        },
        data: data.map((item) => ({
          name: item.name,
          value: hasValue ? item.value : 1,
          itemStyle: {
            color: hasValue ? item.color : 'rgba(192, 196, 204, 0.35)',
          },
        })),
        silent: !hasValue,
      },
      {
        type: 'pie',
        radius: ['50%', '51%'],
        center: ['50%', '50%'],
        silent: true,
        label: { show: false },
        labelLine: { show: false },
        data: [
          {
            value: 1,
            itemStyle: {
              color: token('--prev-border-color-extra-light', '#f2f6fc'),
            },
          },
        ],
      },
    ],
  };
}
function applyRing(target, bingData) {
  const stats = buildRingStats(bingData);
  const option = stats.length ? buildRingOption(stats) : null;
  if (target === 'source') {
    sourceStats.value = stats;
    sourceOption.value = option;
    return;
  }
  consumeStats.value = stats;
  consumeOption.value = option;
}
function getBalanceChannel() {
  sourceLoading.value = true;
  getBalanceChannelApi(formValidate.value)
    .then((res) => {
      applyRing('source', res.data?.bing_data);
      sourceLoading.value = false;
    })
    .catch((res) => {
      sourceLoading.value = false;
      ElMessage.error(res.msg);
    });
}
function getBalanceType() {
  consumeLoading.value = true;
  getBalanceTypeApi(formValidate.value)
    .then((res) => {
      applyRing('consume', res.data?.bing_data);
      consumeLoading.value = false;
    })
    .catch((res) => {
      consumeLoading.value = false;
      ElMessage.error(res.msg);
    });
}
// 具体日期
function onchangeTime(e) {
  timeVal.value = e;
  formValidate.value.time = timeVal.value ? timeVal.value.join('-') : '';
  getBalanceBasic();
  getBalanceTrend();
  getBalanceChannel();
  getBalanceType();
}
// 统计图
function getBalanceTrend() {
  spinShow.value = true;
  getBalanceTrendApi(formValidate.value)
    .then((res) => {
      const data = res.data || {};
      optionData.value = buildTrendOption(data.xAxis || [], buildTrendSeries(data.series));
      spinShow.value = false;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
      spinShow.value = false;
    });
}
</script>

<style scoped lang="scss">
.dashboard-panel {
  height: 100%;

  :deep(.el-card__body) {
    padding: 16px 20px 12px;
  }
}

.panel-title {
  margin-bottom: 8px;

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

.ring-body {
  display: grid;
  grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
  gap: 8px 12px;
  align-items: center;
  min-height: 240px;
}

.ring-chart {
  position: relative;
  min-width: 0;
}

.ring-center {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  pointer-events: none;
}

.ring-center__num {
  font-size: 28px;
  font-weight: 600;
  line-height: 34px;
  color: var(--prev-color-text-regular);
}

.ring-center__label {
  margin-top: 2px;
  font-size: 12px;
  color: var(--prev-color-text-secondary);
}

.ring-legend {
  display: flex;
  flex-direction: column;
  gap: 12px;
  min-width: 0;
}

.ring-legend__item {
  display: flex;
  align-items: flex-start;
  gap: 8px;
}

.ring-legend__dot {
  flex: none;
  width: 8px;
  height: 8px;
  margin-top: 5px;
  border-radius: 50%;
}

.ring-legend__main {
  flex: 1;
  min-width: 0;
}

.ring-legend__name {
  font-size: 13px;
  line-height: 18px;
  color: var(--prev-color-text-regular);
}

.ring-legend__meta {
  display: flex;
  justify-content: space-between;
  gap: 8px;
  margin-top: 2px;
  font-size: 12px;
  color: var(--prev-color-text-secondary);
}

@media (max-width: 576px) {
  .ring-body {
    grid-template-columns: 1fr;
  }
}
</style>
