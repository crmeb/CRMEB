<template>
  <div @resize="handleResize">
    <el-row :gutter="16">
      <el-col :xl="16" :lg="12" :md="24" :sm="24" :xs="24" class="ivu-mb">
        <el-card shadow="never" class="dashboard-panel">
          <div class="panel-title acea-row row-middle">
            <span class="panel-icon">
              <el-icon :size="16"><UserFilled /></el-icon>
            </span>
            <h4>用户</h4>
          </div>
          <echarts-from
            ref="userChart"
            :echartsTitle="line"
            :infoList="infoList"
            :series="series"
            v-if="infoList && series.length !== 0"
          ></echarts-from>
        </el-card>
      </el-col>
      <el-col :xl="8" :lg="12" :md="24" :sm="24" :xs="24" class="ivu-mb">
        <el-card shadow="never" class="dashboard-panel buy-panel">
          <div class="panel-title acea-row row-middle">
            <span class="panel-icon">
              <el-icon :size="16"><PieChart /></el-icon>
            </span>
            <h4>购买用户统计</h4>
          </div>

          <div v-if="buyStats.length" class="buy-body">
            <div class="buy-chart">
              <echarts-new
                v-if="buyOption"
                :option-data="buyOption"
                :styles="buyChartStyles"
              />
              <div class="buy-center">
                <div class="buy-center__num">{{ buyTotal }}</div>
                <div class="buy-center__label">用户总数</div>
              </div>
            </div>
            <div class="buy-legend">
              <div v-for="item in buyStats" :key="item.name" class="buy-legend__item">
                <span class="buy-legend__dot" :style="{ background: item.color }"></span>
                <div class="buy-legend__main">
                  <div class="buy-legend__name">{{ item.name }}</div>
                  <div class="buy-legend__meta">
                    <span>{{ item.value }} 人</span>
                    <span>{{ item.percent }}%</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <el-empty v-else description="暂无统计数据" :image-size="64" />
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue';
import { ElMessage } from 'element-plus';
import { userApi } from '@/api/index';
import echartsFrom from '@/components/echarts/index';
import echartsNew from '@/components/echartsNew/index.vue';

defineOptions({ name: 'userChart' });

const userChart = ref(null);
const line = 'line';
const infoList = ref({});
const series = ref([]);
const buyStats = ref([]);
const buyOption = ref(null);
const buyChartStyles = { width: '100%', height: '220px' };

const buyColors = ['#5B8FF9', '#B37FEB', '#5AD8A6', '#F6BD16'];

const buyTotal = computed(() => buyStats.value.reduce((sum, item) => sum + item.value, 0));

function token(name, fallback) {
  const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
  return value || fallback;
}

function buildBuyStats(bingData) {
  const rows = (bingData || []).map((item, index) => ({
    name: item.name,
    value: Number(item.value) || 0,
    color: item.itemStyle?.color || buyColors[index % buyColors.length],
  }));
  const total = rows.reduce((sum, item) => sum + item.value, 0) || 0;
  return rows.map((item) => ({
    ...item,
    percent: total ? Math.round((item.value / total) * 1000) / 10 : 0,
  }));
}

function buildBuyOption(rows) {
  const textRegular = token('--prev-color-text-regular', '#303133');
  const hasValue = rows.some((item) => item.value > 0);
  const data = hasValue
    ? rows.filter((item) => item.value > 0)
    : rows.map((item) => ({ ...item, value: 1 }));

  return {
    color: data.map((item) => item.color),
    tooltip: {
      trigger: 'item',
      formatter: (params) => {
        if (!hasValue) return `${params.name}<br/>0 人（0%）`;
        return `${params.name}<br/>${params.value} 人（${params.percent}%）`;
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
      // 内圈装饰环，增强环形质感
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
    // 避免 unused warning
    textStyle: { color: textRegular },
  };
}

function getStatistics() {
  userApi()
    .then((res) => {
      infoList.value = res.data || {};
      series.value = [
        {
          data: infoList.value.series || [],
          name: '人数（人）',
          type: 'line',
          tooltip: true,
          smooth: true,
          symbol: 'none',
          areaStyle: {
            normal: {
              opacity: 0.2,
            },
          },
        },
      ];
      buyStats.value = buildBuyStats(infoList.value.bing_data);
      buyOption.value = buyStats.value.length ? buildBuyOption(buyStats.value) : null;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

function handleResize() {
  if (infoList.value && series.value.length !== 0) userChart.value?.handleResize();
}

onMounted(() => {
  getStatistics();
});
</script>

<style scoped lang="scss">
.dashboard-panel {
  :deep(.el-card__body) {
    padding: 16px 20px 12px;
  }
}

.buy-panel {
  min-height: 100%;
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

.buy-body {
  display: grid;
  grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
  gap: 8px 12px;
  align-items: center;
  min-height: 240px;
}

.buy-chart {
  position: relative;
  min-width: 0;
}

.buy-center {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  pointer-events: none;
}

.buy-center__num {
  font-size: 28px;
  font-weight: 600;
  line-height: 34px;
  color: var(--prev-color-text-regular);
}

.buy-center__label {
  margin-top: 2px;
  font-size: 12px;
  color: var(--prev-color-text-secondary);
}

.buy-legend {
  display: flex;
  flex-direction: column;
  gap: 12px;
  min-width: 0;
}

.buy-legend__item {
  display: flex;
  align-items: flex-start;
  gap: 8px;
}

.buy-legend__dot {
  flex: none;
  width: 8px;
  height: 8px;
  margin-top: 5px;
  border-radius: 50%;
}

.buy-legend__main {
  flex: 1;
  min-width: 0;
}

.buy-legend__name {
  font-size: 13px;
  line-height: 18px;
  color: var(--prev-color-text-regular);
}

.buy-legend__meta {
  display: flex;
  justify-content: space-between;
  gap: 8px;
  margin-top: 2px;
  font-size: 12px;
  color: var(--prev-color-text-secondary);
}

@media (max-width: 576px) {
  .buy-body {
    grid-template-columns: 1fr;
  }
}
</style>
