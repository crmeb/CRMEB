<template>
  <div class="normal-statistic" v-loading="loading">
    <el-card shadow="never" :bordered="false" class="filter-card">
      <div class="filter-row">
        <date-range-picker
          width="250px"
          v-model="timeValue"
          format="YYYY/MM/DD"
          value-format="YYYY/MM/DD"
          @change="changeTime"
        />
        <el-button v-auth="['admin-normal-statistics-basic']" type="primary" v-db-click @click="loadData"
          >查询</el-button
        >
        <el-button v-auth="['admin-normal-statistics-excel']" v-db-click @click="exportData">导出</el-button>
      </div>
    </el-card>

    <el-card shadow="never" :bordered="false" class="stat-card">
      <div class="card-title">实物商品概况</div>
      <el-row :gutter="20">
        <el-col v-for="item in cards" :key="item.key" :xl="4" :lg="8" :md="8" :sm="12" :xs="24">
          <div class="metric">
            <div class="metric-name">{{ item.name }}</div>
            <div class="metric-value">{{ item.value }}</div>
            <div class="metric-percent" :class="item.percent >= 0 ? 'up' : 'down'">环比 {{ item.percent }}%</div>
          </div>
        </el-col>
      </el-row>
      <echarts-new
        v-if="chartOption"
        :option-data="chartOption"
        :styles="{ height: '380px' }"
        height="100%"
        width="100%"
      />
    </el-card>

    <el-card shadow="never" :bordered="false" class="ranking-card">
      <div class="ranking-header">
        <span class="card-title">实物商品排行</span>
        <el-select v-model="rankingSort" style="width: 150px" @change="loadRanking">
          <el-option v-for="item in rankingOptions" :key="item.value" :label="item.label" :value="item.value" />
        </el-select>
      </div>
      <el-table :data="ranking" empty-text="暂无数据">
        <el-table-column prop="product_id" label="ID" width="80" />
        <el-table-column label="商品名称" min-width="180">
          <template #default="scope">{{ scope.row.store_name || '--' }}</template>
        </el-table-column>
        <el-table-column prop="visit" label="浏览量" min-width="100" />
        <el-table-column prop="user" label="访客数" min-width="100" />
        <el-table-column prop="cart" label="加购件数" min-width="100" />
        <el-table-column prop="orders" label="下单件数" min-width="100" />
        <el-table-column prop="pay" label="支付件数" min-width="100" />
        <el-table-column prop="price" label="支付金额" min-width="110" />
        <el-table-column prop="collect" label="收藏数" min-width="90" />
      </el-table>
    </el-card>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { formatDate } from '@/utils/validate';
import echartsNew from '@/components/echartsNew/index';
import {
  normalStatisticBasic,
  normalStatisticExcel,
  normalStatisticRanking,
  normalStatisticTrend,
} from '../normalStatistic';

defineOptions({ name: 'normalStatisticIndex' });

const loading = ref(false);
const timeValue = ref([]);
const time = ref('');
const basic = ref({});
const chartOption = ref(null);
const ranking = ref([]);
const rankingSort = ref('pay');
const rankingOptions = [
  { value: 'visit', label: '浏览量' },
  { value: 'user', label: '访客数' },
  { value: 'cart', label: '加购件数' },
  { value: 'orders', label: '下单件数' },
  { value: 'pay', label: '支付件数' },
  { value: 'price', label: '支付金额' },
  { value: 'collect', label: '收藏数' },
];

const cards = computed(() =>
  [
    ['browse', '商品浏览量'],
    ['user', '商品访客数'],
    ['pay', '支付件数'],
    ['payPrice', '支付金额'],
    ['refund', '退款件数'],
    ['refundPrice', '退款金额'],
  ].map(([key, name]) => ({
    key,
    name,
    value: basic.value[key]?.num ?? 0,
    percent: basic.value[key]?.percent ?? 0,
  })),
);

const end = new Date();
const start = new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 29);
timeValue.value = [start, end];
time.value = `${formatDate(start, 'yyyy/MM/dd')}-${formatDate(end, 'yyyy/MM/dd')}`;

function changeTime(value) {
  time.value = value ? value.join('-') : '';
}

async function loadData() {
  if (!time.value) return ElMessage.warning('请选择时间');
  loading.value = true;
  try {
    const [basicRes, trendRes] = await Promise.all([
      normalStatisticBasic({ data: time.value }),
      normalStatisticTrend({ data: time.value }),
    ]);
    basic.value = basicRes.data || {};
    chartOption.value = makeChartOption(trendRes.data || {});
    await loadRanking();
  } catch (error) {
    ElMessage.error(error.msg || '统计数据加载失败');
  } finally {
    loading.value = false;
  }
}

async function loadRanking() {
  try {
    const res = await normalStatisticRanking({ data: time.value, sort: rankingSort.value });
    ranking.value = res.data || [];
  } catch (error) {
    ElMessage.error(error.msg || '排行数据加载失败');
  }
}

async function exportData() {
  try {
    const res = await normalStatisticExcel({ data: time.value });
    const urls = res.data?.url || [];
    (Array.isArray(urls) ? urls : [urls]).filter(Boolean).forEach((url) => {
      window.location.href = url;
    });
  } catch (error) {
    ElMessage.error(error.msg || '导出失败');
  }
}

function makeChartOption(data) {
  const series = (data.series || []).map((item, index) => ({
    ...item,
    itemStyle: { normal: { color: chartColor(index) } },
  }));
  return {
    tooltip: { trigger: 'axis' },
    legend: { x: 'center', data: series.map((item) => item.name) },
    grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
    toolbox: { feature: { saveAsImage: {} } },
    xAxis: { type: 'category', data: data.xAxis || [], axisLabel: { interval: 0, rotate: 40 } },
    yAxis: [
      { type: 'value', name: '金额' },
      { type: 'value', name: '数量' },
    ],
    series,
  };
}

function chartColor(index) {
  const tokens = ['--prev-color-primary', '--prev-color-success', '--prev-color-warning', '--prev-color-info'];
  return getComputedStyle(document.documentElement)
    .getPropertyValue(tokens[index % tokens.length])
    .trim();
}

onMounted(loadData);
</script>

<style scoped lang="scss">
.normal-statistic {
  padding-bottom: 20px;
}
.filter-card,
.stat-card,
.ranking-card {
  margin-bottom: 16px;
}
.filter-row,
.ranking-header {
  display: flex;
  align-items: center;
  gap: 12px;
}
.ranking-header {
  justify-content: space-between;
  margin-bottom: 16px;
}
.card-title {
  color: var(--prev-color-text-regular);
  font-size: 16px;
  font-weight: 600;
}
.metric {
  min-height: 98px;
  padding: 16px 0;
}
.metric-name {
  color: var(--prev-color-text-primary);
  font-size: 14px;
}
.metric-value {
  margin: 8px 0;
  color: var(--prev-color-text-regular);
  font-size: 24px;
  font-weight: 600;
}
.metric-percent {
  font-size: 12px;
}
.up {
  color: var(--prev-color-danger);
}
.down {
  color: var(--prev-color-success);
}
@media screen and (max-width: 640px) {
  .filter-row {
    align-items: stretch;
    flex-wrap: wrap;
  }
  // !important 压过 dateRangePicker 的内联固定宽度，窄屏占满一行
  .filter-row :deep(.el-date-editor) {
    width: 100% !important;
  }
}
</style>
