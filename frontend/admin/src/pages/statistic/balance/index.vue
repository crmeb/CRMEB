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
    <el-card class="ivu-mb-16" :bordered="false" shadow="never">
      <h3>余额使用趋势</h3>
      <echarts-new :option-data="optionData" :styles="style" height="100%" width="100%" v-if="optionData"></echarts-new>
    </el-card>
    <div class="code-row-bg">
      <el-card :bordered="false" shadow="never" class="ivu-mt mr8">
        <div class="acea-row row-between-wrapper">
          <h3 class="statics-header-title">余额来源分析</h3>
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
            <el-table-column type="index" label="序号" width="50"> </el-table-column>
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
      <el-card :bordered="false" shadow="never" class="ivu-mt ml8 pr10">
        <div class="acea-row row-between-wrapper">
          <h3 class="statics-header-title">余额消耗</h3>
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
            <el-table-column type="index" label="序号" width="50"> </el-table-column>
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
  getBalanceBasic as getBalanceBasicApi,
  getBalanceTrend as getBalanceTrendApi,
  getBalanceChannel as getBalanceChannelApi,
  getBalanceType as getBalanceTypeApi,
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
function getBalanceChannel() {
  loading.value = true;
  getBalanceChannelApi(formValidate.value).then((res) => {
    infoList.value = res.data;
    tabList.value = res.data.list;
    loading.value = false;
  });
}
function getBalanceType() {
  loading2.value = true;
  getBalanceTypeApi(formValidate.value).then((res) => {
    infoList2.value = res.data;
    tabList2.value = res.data.list;
    loading2.value = false;
  });
}
// 具体日期
function onchangeTime(e) {
  timeVal.value = e;
  formValidate.value.time = timeVal.value ? timeVal.value.join('-') : '';
  getBalanceBasic();
  getBalanceTrend();
}
// 统计图
function getBalanceTrend() {
  spinShow.value = true;
  getBalanceTrendApi(formValidate.value)
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
  flex-wrap: nowrap;
  justify-content: space-between;
}
.code-row-bg .ivu-mt {
  min-width: 50%;
}
.ech-box {
  margin-top: 10px;
}
.change-style {
  border: 1px solid #ccc;
  border-radius: 15px;
  padding: 0px 10px;
  cursor: pointer;
}
.percent-box {
  display: flex;
  align-items: center;
  padding-right: 10px;
}
.line {
  width: 100%;
  position: relative;
}
.bg {
  position: absolute;
  width: 100%;
  height: 8px;
  border-radius: 8px;
  background-color: #f2f2f2;
}
.percent {
  position: absolute;
  border-radius: 5px;
  height: 8px;
  background-color: var(--prev-color-primary);
  z-index: 999;
}
.num {
  white-space: nowrap;
  margin: 0 10px;
  width: 20px;
}
</style>
