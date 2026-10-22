<template>
  <div v-loading="spinShow">
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
    <cards-data :cardLists="cardLists" v-if="cardLists.length >= 0"></cards-data>
    <el-card class="ivu-mb-16" :bordered="false" shadow="never">
      <h4 class="statics-header-title">营业趋势</h4>
      <echarts-new :option-data="optionData" :styles="style" height="100%" width="100%" v-if="optionData"></echarts-new>
    </el-card>
    <div class="code-row-bg">
      <el-card :bordered="false" shadow="never" class="ivu-mt">
        <div class="acea-row row-between-wrapper">
          <h4 class="statics-header-title">订单来源分析</h4>
          <div class="change-style" v-db-click @click="echartLeft = !echartLeft">切换样式</div>
        </div>
        <div class="ech-box">
          <echarts-from v-if="echartLeft" ref="visitChart" :infoList="infoList" echartsTitle="circle"></echarts-from>
          <el-table
            v-show="!echartLeft"
            ref="selection"
            :data="tabList"
            v-loading="loading"
            empty-text="暂无数据"
            highlight-current-row
          >
            <el-table-column type="index" label="序号" width="80"> </el-table-column>
            <el-table-column label="来源" min-width="80">
              <template #default="scope">
                <span>{{ scope.row.name }}</span>
              </template>
            </el-table-column>
            <el-table-column label="订单数" min-width="130">
              <template #default="scope">
                <span>{{ scope.row.value }}</span>
              </template>
            </el-table-column>
            <el-table-column label="占比率" min-width="130">
              <template #default="scope">
                <div class="percent-box">
                  <div class="line">
                    <div class="bg"></div>
                    <div class="percent" :style="'width:' + scope.row.percent + '%;'"></div>
                  </div>
                  <div class="num">{{ scope.row.percent }}%</div>
                </div>
              </template>
            </el-table-column>
          </el-table>
        </div>
      </el-card>
      <el-card :bordered="false" shadow="never" class="ivu-mt">
        <div class="acea-row row-between-wrapper">
          <h4 class="statics-header-title">订单类型分析</h4>
          <div class="change-style" v-db-click @click="echartRight = !echartRight">切换样式</div>
        </div>
        <div class="ech-box">
          <echarts-from v-if="echartRight" ref="visitChart" :infoList="infoList2" echartsTitle="circle"></echarts-from>
          <el-table
            v-show="!echartRight"
            ref="selection"
            :data="tabList2"
            v-loading="loading2"
            empty-text="暂无数据"
            highlight-current-row
          >
            <el-table-column type="index" label="序号" width="80"> </el-table-column>
            <el-table-column label="来源" min-width="80">
              <template #default="scope">
                <span>{{ scope.row.name }}</span>
              </template>
            </el-table-column>
            <el-table-column label="金额" min-width="130">
              <template #default="scope">
                <span>{{ scope.row.value }}</span>
              </template>
            </el-table-column>
            <el-table-column label="占比率" min-width="130">
              <template #default="scope">
                <div class="percent-box">
                  <div class="line">
                    <div class="bg"></div>
                    <div class="percent" :style="'width:' + scope.row.percent + '%;'"></div>
                  </div>
                  <div class="num">{{ scope.row.percent }}%</div>
                </div>
              </template>
            </el-table-column>
          </el-table>
        </div>
      </el-card>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import cardsData from '@/components/cards/cards';
import echartsNew from '@/components/echartsNew/index';
import echartsFrom from '@/components/echarts/index';
import {
  getBasic as getBasicApi,
  getTrend as getTrendApi,
  getChannel as getChannelApi,
  getType as getTypeApi,
} from '@/api/statistic';
import { formatDate } from '@/utils/validate';

defineOptions({ name: 'index' });

const timeVal = ref([]);
const style = { height: '400px' };
const infoList = ref({});
const infoList2 = ref({});
const echartLeft = ref(true);
const echartRight = ref(false);
const loading = ref(false);
const loading2 = ref(false);
const formValidate = ref({
  time: '',
});
const cardLists = ref([
  {
    col: 6,
    count: 0,
    name: '订单量',
    className: 'icondingdanliang',
  },
  {
    col: 6,
    count: 0,
    name: '订单销售额',
    className: 'icondingdanjine',
  },
  {
    col: 6,
    count: 0,
    name: '退款订单数',
    className: 'icontuikuandingdanliang',
  },
  {
    col: 6,
    count: 0,
    name: '退款金额',
    className: 'icontuikuanjine',
  },
]);
const optionData = ref({});
const spinShow = ref(false);
const tabList = ref([]);
const tabList2 = ref([]);

// created
const end = new Date();
const start = new Date();
start.setTime(start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 29)));
timeVal.value = [formatDate(start, 'yyyy/MM/dd'), formatDate(end, 'yyyy/MM/dd')];
formValidate.value.time = timeVal.value.join('-');
onInit();

function onInit() {
  getBasic();
  getTrend();
  getChannel();
  getType();
}
function onSelectDate(e) {
  formValidate.value.time = e;
  onInit();
}
function timeG(dd) {
  var d = new Date(dd);
  var datetime =
    d.getFullYear() +
    '-' +
    (d.getMonth() + 1) +
    '-' +
    d.getDate() +
    ' ' +
    d.getHours() +
    ':' +
    d.getMinutes() +
    ':' +
    d.getSeconds();
  return datetime;
}
function getBasic() {
  getBasicApi(formValidate.value).then((res) => {
    let arr = ['pay_count', 'pay_price', 'refund_count', 'refund_price'];
    cardLists.value.map((i, index) => {
      i.count = res.data[arr[index]];
    });
  });
}
function getChannel() {
  loading.value = true;
  getChannelApi(formValidate.value).then((res) => {
    infoList.value = res.data;
    tabList.value = res.data.list;
    loading.value = false;
  });
}
function getType() {
  loading2.value = true;
  getTypeApi(formValidate.value).then((res) => {
    infoList2.value = res.data;
    tabList2.value = res.data.list;
    loading2.value = false;
  });
}
function selectChange(e) {}
// 具体日期
function onchangeTime(e) {
  timeVal.value = e;
  formValidate.value.time = timeVal.value ? timeVal.value.join('-') : '';
  getBasic();
  getTrend();
  getChannel();
  getType();
}
// 统计图
function getTrend() {
  spinShow.value = true;
  getTrendApi(formValidate.value)
    .then(async (res) => {
      let legend = res.data.series.map((item) => {
        return item.name;
      });
      let xAxis = res.data.xAxis;
      let col = ['#5B8FF9', '#5AD8A6', '#FFAB2B', '#5D7092'];
      let series = [];
      res.data.series.map((item, index) => {
        series.push({
          name: item.name,
          type: 'line',
          data: item.data,
          itemStyle: {
            normal: {
              color: col[index],
            },
          },
          smooth: 0,
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
          // axisTick:{
          //     show:false
          // },
          // axisLine:{
          //     show:false
          // },
          // splitLine: {
          //     show: false
          // },
          axisLabel: {
            interval: 0,
            rotate: 40,
            textStyle: {
              color: '#000000',
            },
          },
          data: xAxis,
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
        series: series,
      };
      spinShow.value = false;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
      spinShow.value = false;
    });
}
</script>

<style scoped>
.cl {
  margin-right: 20px;
}
.code-row-bg {
  display: flex;
  align-items: stretch;
  gap: 16px;
}
.code-row-bg .ivu-mt {
  flex: 1;
  min-width: 0;
  width: auto;
  /* .ivu-mt 的全局 margin-top 只作用于第二张卡，导致左右卡片顶部错位 */
  margin-top: 0 !important;
}
.ech-box {
  margin-top: 10px;
}
.change-style {
  border: 1px solid var(--prev-border-color-base, #dcdfe6);
  border-radius: 15px;
  padding: 0 10px;
  cursor: pointer;
  color: var(--prev-color-text-regular, #606266);
}
.percent-box {
  display: flex;
  align-items: center;
  gap: 8px;
}
.line {
  flex: 1;
  min-width: 40px;
  height: 8px;
  border-radius: 8px;
  background-color: #f2f2f2;
  overflow: hidden;
}
.bg {
  display: none;
}
.percent {
  height: 100%;
  border-radius: 8px;
  background-color: var(--prev-color-primary);
}
.num {
  flex: 0 0 52px;
  width: 52px;
  margin: 0;
  text-align: right;
  white-space: nowrap;
  color: var(--prev-color-text-regular, #606266);
  font-size: 12px;
}
@media (max-width: 1200px) {
  .code-row-bg {
    flex-wrap: wrap;
  }
  .code-row-bg .ivu-mt {
    flex-basis: 100%;
  }
}
</style>
