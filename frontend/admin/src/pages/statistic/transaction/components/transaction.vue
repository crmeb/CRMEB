<template>
  <el-card shadow="never" class="ivu-mt-16 trade-card" v-loading="spinShow">
    <div class="panel-head acea-row row-between-wrapper">
      <div class="panel-title acea-row row-middle">
        <span class="panel-icon">
          <el-icon :size="16"><DataAnalysis /></el-icon>
        </span>
        <h4>交易概况</h4>
        <el-tooltip placement="bottom-start" :show-after="200">
          <el-icon class="panel-help"><QuestionFilled /></el-icon>
          <template #content>
            <div class="tip-box">
              <div class="tip-title">营业额</div>
              <div>商品支付金额、充值金额、购买付费会员金额、线下收银金额</div>
              <div class="tip-title">商品支付金额</div>
              <div>
                选定条件下，用户购买商品的实际支付金额，包括微信支付、余额支付、支付宝支付、线下支付金额（拼团商品在成团之后计入，线下支付订单在后台确认支付后计入）
              </div>
              <div class="tip-title">购买会员金额</div>
              <div>选定条件下，用户成功购买付费会员的金额</div>
              <div class="tip-title">充值金额</div>
              <div>选定条件下，用户成功充值的金额</div>
              <div class="tip-title">线下收银金额</div>
              <div>选定条件下，用户在线下扫码支付的金额</div>
              <div class="tip-title">支出金额</div>
              <div>余额支付金额、商品退款金额</div>
              <div class="tip-title">余额支付金额</div>
              <div>用户下单时使用余额实际支付的金额</div>
              <div class="tip-title">商品退款金额</div>
              <div>用户成功退款的商品金额</div>
            </div>
          </template>
        </el-tooltip>
      </div>
      <div class="acea-row row-middle panel-actions">
        <date-range-picker
          width="250px"
          clearable
          v-model="timeVal"
          :editable="false"
          @change="onchangeTime"
          format="YYYY/MM/DD"
          value-format="YYYY/MM/DD"
        />
        <el-button type="primary" v-db-click @click="onSeach">查询</el-button>
        <el-button v-db-click @click="excel">导出</el-button>
      </div>
    </div>

    <div class="stat-grid">
      <div v-for="(item, index) in list" :key="index" class="stat-card" :class="`tone-${index % 4}`">
        <div class="stat-card__icon">
          <i class="iconfont" :class="item.icon"></i>
        </div>
        <div class="stat-card__body">
          <div class="stat-card__name">{{ item.name }}</div>
          <div class="stat-card__value">
            {{ formatMoney(item.money, index === list.length - 1) }}
          </div>
          <div class="stat-card__ratio" :class="Number(item.rate) >= 0 ? 'is-up' : 'is-down'">
            环比增长 {{ item.rate }}%
            <el-icon>
              <CaretTop v-if="Number(item.rate) >= 0" />
              <CaretBottom v-else />
            </el-icon>
          </div>
        </div>
      </div>
    </div>

    <echarts-new
      v-if="optionData"
      :option-data="optionData"
      :styles="style"
      height="100%"
      width="100%"
    />
  </el-card>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { ElMessage } from 'element-plus';
import { statisticBottomTradeApi } from '@/api/statistic';
import echartsNew from '@/components/echartsNew/index';
import { formatDate } from '@/utils/validate';

defineOptions({ name: 'transaction' });

const timeVal = ref([]);
const dataTime = ref('');
const list = ref([]);
const optionData = ref(null);
const style = { height: '360px' };
const spinShow = ref(false);

const end = new Date();
const start = new Date();
start.setTime(start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 29)));
timeVal.value = [start, end];
dataTime.value = formatDate(start, 'yyyy/MM/dd') + '-' + formatDate(end, 'yyyy/MM/dd');

function token(name, fallback) {
  return getComputedStyle(document.documentElement).getPropertyValue(name).trim() || fallback;
}

function formatMoney(money, forceFixed) {
  if (!money && money !== 0) return '0.00';
  if (forceFixed) return (Math.floor(Number(money) * 100) / 100).toFixed(2);
  return money;
}

function onSeach() {
  getStatistics();
}

function onchangeTime(e) {
  timeVal.value = e;
  dataTime.value = timeVal.value ? timeVal.value.join('-') : '';
}

function getStatistics() {
  spinShow.value = true;
  statisticBottomTradeApi({ data: dataTime.value })
    .then((res) => {
      const cardLists = res.data;
      const icons = [
        'iconyingyee',
        'iconshangpinzhifujine',
        'icongoumaihuiyuanjine',
        'iconchongzhijianshu',
        'iconxianxiashouyinjine',
        'iconzhichujine',
        'iconyuezhifujine',
        'iconshangpintuikuanjine',
      ];
      for (let i = 0; i < cardLists.series.length; i++) {
        cardLists.series[i].icon = icons[i];
      }
      list.value = cardLists.series;
      get(cardLists);
    })
    .catch((res) => {
      ElMessage.error(res?.msg || '数据加载失败');
    })
    .finally(() => {
      spinShow.value = false;
    });
}

function get(extract) {
  const dataList = extract.series.filter((item) => item.type === 1);
  const legend = dataList.map((item) => item.name);
  const colors = ['#5B8FF9', '#5AD8A6', '#5D7092', '#F5222D', '#FFAB2B', '#B37FEB'];
  const textSecondary = token('--prev-color-text-secondary', '#909399');
  const border = token('--prev-border-color-extra-light', '#f2f6fc');
  const xLen = (extract.x || []).length;
  const interval = xLen > 14 ? Math.ceil(xLen / 7) - 1 : 0;

  const seriesData = dataList.map((item, index) => {
    const series = Object.keys(item.value || {}).map((key) => Number(item.value[key]));
    return {
      name: item.name,
      type: 'line',
      data: series,
      smooth: true,
      showSymbol: false,
      symbol: 'circle',
      symbolSize: 6,
      lineStyle: { width: 2, color: colors[index % colors.length] },
      itemStyle: { color: colors[index % colors.length] },
    };
  });

  optionData.value = {
    color: colors,
    tooltip: {
      trigger: 'axis',
      axisPointer: {
        type: 'line',
        lineStyle: { color: border },
      },
    },
    legend: {
      top: 0,
      left: 'center',
      itemWidth: 10,
      itemHeight: 10,
      icon: 'roundRect',
      textStyle: { color: textSecondary, fontSize: 12 },
      data: legend,
    },
    grid: {
      left: 8,
      right: 12,
      top: 48,
      bottom: 8,
      containLabel: true,
    },
    xAxis: {
      type: 'category',
      boundaryGap: false,
      data: extract.x,
      axisLine: { lineStyle: { color: border } },
      axisTick: { show: false },
      axisLabel: {
        color: textSecondary,
        fontSize: 11,
        interval,
        rotate: 0,
        hideOverlap: true,
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
    series: seriesData,
  };
}

function excel() {
  spinShow.value = true;
  statisticBottomTradeApi({ data: dataTime.value, export: 1 })
    .then((res) => {
      if (res.data.export) {
        window.location.href = res.data.export;
      } else {
        ElMessage.error('导出文件生成失败');
      }
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    })
    .finally(() => {
      spinShow.value = false;
    });
}

onMounted(() => {
  getStatistics();
});
</script>

<style scoped lang="scss">
.trade-card {
  :deep(.el-card__body) {
    padding: 16px 20px 12px;
  }
}

.panel-head {
  margin-bottom: 16px;
  gap: 12px;
  flex-wrap: wrap;
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

.panel-help {
  margin-left: 6px;
  color: var(--prev-color-text-secondary);
  cursor: pointer;
}

.panel-actions {
  gap: 12px;
}

.tip-box {
  max-width: 360px;
  line-height: 1.6;

  .tip-title {
    margin-top: 8px;
    font-weight: 500;
  }

  .tip-title:first-child {
    margin-top: 0;
  }
}

.stat-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 12px;
  margin-bottom: 16px;

  @media screen and (max-width: 1400px) {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }

  @media screen and (max-width: 992px) {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  @media screen and (max-width: 576px) {
    grid-template-columns: 1fr;
  }
}

.stat-card {
  display: flex;
  gap: 12px;
  padding: 14px 16px;
  border-radius: 8px;
  background: var(--prev-bg-color);

  &.tone-0 .stat-card__icon {
    color: var(--prev-color-primary);
    background: var(--prev-color-primary-light-9);
  }

  &.tone-1 .stat-card__icon {
    color: var(--el-color-success);
    background: var(--el-color-success-light-9);
  }

  &.tone-2 .stat-card__icon {
    color: var(--el-color-warning);
    background: var(--el-color-warning-light-9);
  }

  &.tone-3 .stat-card__icon {
    color: var(--el-color-danger);
    background: var(--el-color-danger-light-9);
  }
}

.stat-card__icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex: none;
  width: 36px;
  height: 36px;
  border-radius: 50%;

  .iconfont {
    font-size: 16px;
  }
}

.stat-card__body {
  min-width: 0;
}

.stat-card__name {
  font-size: 12px;
  color: var(--prev-color-text-secondary);
}

.stat-card__value {
  margin-top: 4px;
  font-size: 22px;
  font-weight: 500;
  line-height: 28px;
  color: var(--prev-color-text-regular);
}

.stat-card__ratio {
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
