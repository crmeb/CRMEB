<template>
  <el-card :bordered="false" shadow="never" class="ivu-mt-16" v-loading="spinShow">
    <div class="acea-row row-between-wrapper mb20">
      <div class="statics-header-title">
        <h4>交易概况</h4>
        <el-tooltip placement="right-start">
          <i class="el-icon-question ml10"></i>
          <template #content
            ><div>
              <div>营业额</div>
              <div>商品支付金额、充值金额、购买付费会员金额、线下收银金额</div>
              <br />
              <div>商品支付金额</div>
              <div>
                选定条件下，用户购买商品的实际支付金额，包括微信支付、余额支付、支付宝支付、线下支付金额（拼团商品在成团之后计入，线下支付订单在后台确认支付后计入）
              </div>
              <br />
              <div>购买会员金额</div>
              <div>选定条件下，用户成功购买付费会员的金额</div>
              <br />
              <div>充值金额</div>
              <div>选定条件下，用户成功充值的金额</div>
              <br />
              <div>线下收银金额</div>
              <div>选定条件下，用户在线下扫码支付的金额</div>
              <br />
              <div>支出金额</div>
              <div>余额支付金额、商品退款金额</div>
              <br />
              <div>余额支付金额</div>
              <div>用户下单时使用余额实际支付的金额</div>
              <br />
              <div>商品退款金额</div>
              <div>用户成功退款的商品金额</div>
            </div></template
          >
        </el-tooltip>
      </div>
      <div class="acea-row">
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
        <el-button type="primary" v-db-click @click="onSeach">查询</el-button>
        <el-button type="primary" v-db-click @click="excel">导出</el-button>
      </div>
    </div>
    <div class="acea-row mb20">
      <div class="infoBox acea-row mb30" v-for="(item, index) in list" :key="index">
        <div
          class="iconCrl mr15"
          :class="{
            one: index % 4 == 0,
            two: index % 4 == 1,
            three: index % 4 == 2,
            four: index % 4 == 3,
          }"
        >
          <i class="iconfont" :class="item.icon"></i>
        </div>
        <div class="info">
          <span class="sp1" v-text="item.name"></span>
          <span
            class="sp2"
            v-if="index === list.length - 1"
            v-text="item.money ? (parseInt(item.money * 100) / 100).toFixed(2) : '0.00'"
          ></span>
          <span class="sp2" v-else v-text="item.money ? item.money : '0.00'"></span>
          <span class="content-time spBlock"
            >环比增长：<i class="content-is" :class="Number(item.rate) >= 0 ? 'up' : 'down'">{{ item.rate }}%</i
            ><i
              :style="{ color: Number(item.rate) >= 0 ? '#F5222D' : '#39C15B' }"
              :class="[Number(item.rate) >= 0 ? 'el-icon-caret-top' : 'el-icon-caret-bottom']"
          /></span>
        </div>
      </div>
    </div>
    <echarts-new :option-data="optionData" :styles="style" height="100%" width="100%" v-if="optionData"></echarts-new>
  </el-card>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { ElMessage } from 'element-plus';
import { statisticBottomTradeApi } from '@/api/statistic';
import echartsNew from '@/components/echartsNew/index';
import { formatDate } from '@/utils/validate';

defineOptions({ name: 'transaction' });

const grid = {
  xl: 8,
  lg: 8,
  md: 8,
  sm: 24,
  xs: 24,
};
const name = ref('近30天');
const timeVal = ref([]);
const dataTime = ref('');
const list = ref({});
const optionData = ref({});
const style = { height: '400px' };
const spinShow = ref(false);

// created
const end = new Date();
const start = new Date();
start.setTime(start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 29)));
timeVal.value = [start, end];
dataTime.value = formatDate(start, 'yyyy/MM/dd') + '-' + formatDate(end, 'yyyy/MM/dd');

onMounted(() => {
  getStatistics();
});

function onSeach() {
  getStatistics();
}
// 具体日期
function onchangeTime(e) {
  timeVal.value = e;
  dataTime.value = timeVal.value ? timeVal.value.join('-') : '';
  name.value = dataTime.value;
}
// 统计
function getStatistics() {
  spinShow.value = true;
  statisticBottomTradeApi({ data: dataTime.value })
    .then(async (res) => {
      const cardLists = res.data;
      // 图标顺序与后端 series 卡片顺序（营业额/商品支付/购买会员/充值/线下收银/支出/余额支付/商品退款）一致，
      // 支付佣金卡片已随佣金体系移除
      const incons = [
        'iconyingyee',
        'iconshangpinzhifujine',
        'icongoumaihuiyuanjine',
        'iconchongzhijianshu',
        'iconxianxiashouyinjine',
        'iconzhichujine',
        'iconyuezhifujine',
        'iconshangpintuikuanjine',
      ];
      for (var i = 0; i < cardLists.series.length; i++) {
        cardLists.series[i]['icon'] = incons[i];
      }
      list.value = cardLists.series;
      get(cardLists);
      spinShow.value = false;
    })
    .catch((res) => {
      ElMessage.error(res?.msg || '数据加载失败');
      spinShow.value = false;
    });
}
function get(extract) {
  let dataList = extract.series.filter((item) => {
    return item.type === 1;
  });
  let legend = dataList.map((item) => {
    return item.name;
  });
  let col = ['#5B8FF9', '#5AD8A6', '#5D7092', '#F5222D', '#FFAB2B', '#B37FEB'];
  let seriesData = [];
  dataList.map((item, index) => {
    let series = [];
    Object.keys(item.value).forEach((key) => {
      series.push(Number(item.value[key]));
    });
    seriesData.push({
      name: item.name,
      type: 'line',
      data: series,
      itemStyle: {
        normal: {
          color: col[index],
        },
      },
      smooth: true,
    });
  });
  optionData.value = {
    tooltip: {
      trigger: 'axis',
      axisPointer: {
        type: 'cross',
        label: {
          backgroundColor: '#6a7985',
        },
      },
    },
    legend: {
      x: 'center',
      data: legend,
    },
    grid: {
      left: '3%',
      right: '4%',
      bottom: '3%',
      containLabel: true,
    },
    toolbox: {
      feature: {
        saveAsImage: {},
      },
    },
    xAxis: {
      type: 'category',
      boundaryGap: true,
      axisLabel: {
        interval: 0,
        rotate: 40,
        textStyle: {
          color: '#000000',
        },
      },
      data: extract.x,
    },
    yAxis: {
      type: 'value',
      axisLine: {
        show: false,
      },
      axisTick: {
        show: false,
      },
      axisLabel: {
        textStyle: {
          color: '#7F8B9C',
        },
      },
      splitLine: {
        show: true,
        lineStyle: {
          color: '#F5F7F9',
        },
      },
    },
    series: seriesData,
  };
}
// 导出：携带 export 参数请求后端生成 Excel，拿到地址后触发下载
function excel() {
  spinShow.value = true;
  statisticBottomTradeApi({ data: dataTime.value, export: 1 })
    .then((res) => {
      spinShow.value = false;
      if (res.data.export) {
        window.location.href = res.data.export;
      } else {
        ElMessage.error('导出文件生成失败');
      }
    })
    .catch((res) => {
      ElMessage.error(res.msg);
      spinShow.value = false;
    });
}
</script>

<style scoped lang="scss">
.one {
  background: var(--prev-color-primary);
}
.two {
  background: #00c050;
}
.three {
  background: #ffab2b;
}
.four {
  background: #b37feb;
}
.up,
.el-icon-caret-top {
  color: #f5222d;
  font-size: 12px;
  opacity: 1 !important;
}

.down,
.el-icon-caret-bottom {
  color: #39c15b;
  font-size: 12px;
}
.curP {
  cursor: pointer;
}
.header {
  &-title {
    font-size: 16px;
    color: var(--prev-color-text-regular);
  }
  &-time {
    font-size: 12px;
    color: var(--prev-color-text-secondary);
    opacity: 0.45;
  }
}

.iconfont {
  font-size: 16px;
  color: #fff;
}

.iconCrl {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  text-align: center;
  line-height: 32px;
  opacity: 0.7;
}

.lan {
  background: var(--prev-color-primary);
}

.iconshangpinliulanliang {
  color: #fff;
}

.infoBox {
  width: 20%;
  @media screen and (max-width: 1300px) {
    width: 25%;
  }
  @media screen and (max-width: 1200px) {
    width: 33%;
  }
  @media screen and (max-width: 900px) {
    width: 50%;
  }
}

.info {
  .sp1 {
    color: var(--prev-color-text-primary);
    font-size: 14px;
    display: block;
  }
  .sp2 {
    font-weight: 400;
    font-size: 30px;
    color: var(--prev-color-text-regular);
    display: block;
  }
  .sp3 {
    font-size: 12px;
    font-weight: 400;
    color: var(--prev-color-text-secondary);
    display: block;
  }
}
</style>
