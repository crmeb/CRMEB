<template>
  <div v-loading="spinShow">
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form :model="formValidate" label-width="80px" @submit.prevent inline>
          <el-form-item label="时间选择：">
            <date-range-picker
              width="250px"
              clearable
              v-model="timeVal"
              :editable="false"
              @change="onchangeTime"
              format="YYYY/MM/DD"
              value-format="YYYY/MM/DD"
            ></date-range-picker>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <cards-data :cardLists="cardLists" v-if="cardLists.length >= 0"></cards-data>
    <el-card :bordered="false" shadow="never">
      <h4 class="statics-header-title">积分使用趋势</h4>
      <echarts-new :option-data="optionData" :styles="style" height="100%" width="100%" v-if="optionData"></echarts-new>
    </el-card>
    <div class="code-row-bg">
      <el-card :bordered="false" shadow="never">
        <div class="acea-row row-between-wrapper">
          <h4 class="statics-header-title">积分来源分析</h4>
          <div class="change-style" v-db-click @click="echartLeft = !echartLeft">切换样式</div>
        </div>
        <div class="ech-box">
          <echarts-from v-if="echartLeft" :infoList="infoList" echartsTitle="circle"></echarts-from>
          <el-table
            v-show="!echartLeft"
            ref="selection"
            :data="tabList"
            v-loading="loading"
            empty-text="暂无数据"
            highlight-current-row
          >
            <el-table-column type="index" width="60"></el-table-column>
            <el-table-column label="来源" min-width="80">
              <template #default="scope">
                <span>{{ scope.row.name }}</span>
              </template>
            </el-table-column>
            <el-table-column label="金额" width="100">
              <template #default="scope">
                <span>{{ scope.row.value }}</span>
              </template>
            </el-table-column>
            <el-table-column label="占比率" min-width="140">
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
      <el-card :bordered="false" shadow="never">
        <div class="acea-row row-between-wrapper">
          <h4 class="statics-header-title">积分消耗</h4>
          <div class="change-style" v-db-click @click="echartRight = !echartRight">切换样式</div>
        </div>
        <div class="ech-box">
          <echarts-from v-if="echartRight" :infoList="infoList2" echartsTitle="circle"></echarts-from>
          <el-table
            v-show="!echartRight"
            ref="selection"
            :data="tabList2"
            v-loading="loading"
            empty-text="暂无数据"
            highlight-current-row
          >
            <el-table-column type="index" width="60"></el-table-column>
            <el-table-column label="来源" min-width="80">
              <template #default="scope">
                <span>{{ scope.row.name }}</span>
              </template>
            </el-table-column>
            <el-table-column label="金额" width="100">
              <template #default="scope">
                <span>{{ scope.row.value }}</span>
              </template>
            </el-table-column>
            <el-table-column label="占比率" min-width="140">
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
import { ref, reactive } from 'vue';
import { ElMessage } from 'element-plus';
import cardsData from '@/components/cards/cards';
import echartsNew from '@/components/echartsNew/index';
import { getPointBasic, getPointTrend, getChannel, getType } from '@/api/marketing';
import { formatDate } from '@/utils/validate';
import echartsFrom from '@/components/echarts/index';

defineOptions({ name: 'integralStatistics' });

const timeVal = ref([]);
const style = { height: '400px' };
const infoList = ref({});
const infoList2 = ref({});
const echartLeft = ref(true);
const echartRight = ref(false);
const loading = ref(false);
const formValidate = reactive({ time: '' });
const cardLists = reactive([
  { col: 8, count: 0, name: '当前积分', className: 'icondangqianjifen' },
  { col: 8, count: 0, name: '累计总积分', className: 'iconleijijifen' },
  { col: 8, count: 0, name: '累计消耗积分', className: 'iconxiaohaojifen' },
]);
const optionData = ref({});
const spinShow = ref(false);
const tabList = ref([]);
const tabList2 = ref([]);

// created
const end = new Date();
const start = new Date();
start.setTime(start.getTime() - 29 * 24 * 60 * 60 * 1000);
// value-format 为 'YYYY/MM/DD'，组件要求字符串数组，传 Date 对象会导致回显空白
timeVal.value = [formatDate(start, 'yyyy/MM/dd'), formatDate(end, 'yyyy/MM/dd')];
formValidate.time = formatDate(start, 'yyyy/MM/dd') + '-' + formatDate(end, 'yyyy/MM/dd');
onInit();

function onInit() {
  getPointBasicFn();
  getPointTrendFn();
  getChannelFn();
  getTypeFn();
}
function getPointBasicFn() {
  getPointBasic(formValidate).then((res) => {
    let arr = ['now_point', 'all_point', 'pay_point'];
    cardLists.map((i, index) => {
      i.count = res.data[arr[index]];
    });
  });
}
function getChannelFn() {
  loading.value = true;
  getChannel(formValidate).then((res) => {
    infoList.value = res.data;
    tabList.value = res.data.list;
    loading.value = false;
  });
}
function getTypeFn() {
  getType(formValidate).then((res) => {
    infoList2.value = res.data;
    tabList2.value = res.data.list;
  });
}
function onchangeTime(e) {
  timeVal.value = e;
  formValidate.time = timeVal.value ? timeVal.value.join('-') : '';
  getPointBasicFn();
  getPointTrendFn();
}
function getPointTrendFn() {
  spinShow.value = true;
  getPointTrend(formValidate)
    .then(async (res) => {
      let legend = res.data.series.map((item) => item.name);
      let xAxis = res.data.xAxis;
      let col = ['#5B8FF9', '#5AD8A6', '#FFAB2B', '#5D7092'];
      let series = res.data.series.map((item, index) => ({
        name: item.name,
        type: 'line',
        data: item.data,
        itemStyle: { normal: { color: col[index] } },
        smooth: 0,
      }));
      optionData.value = {
        tooltip: { trigger: 'axis', axisPointer: { type: 'cross', label: { backgroundColor: '#6a7985' } } },
        legend: { x: 'center', data: legend },
        grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
        toolbox: { feature: { saveAsImage: {} } },
        xAxis: {
          type: 'category',
          boundaryGap: true,
          axisLabel: { interval: 0, rotate: 40, textStyle: { color: '#000000' } },
          data: xAxis,
        },
        yAxis: {
          type: 'value',
          axisLine: { show: false },
          axisTick: { show: false },
          axisLabel: { textStyle: { color: '#7F8B9C' } },
          splitLine: { show: true, lineStyle: { color: '#F5F7F9' } },
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
/* 双卡并排：等宽 + 16px 间距，避免用 .ivu-mt 手工 margin 造成错位 */
.code-row-bg {
  display: flex;
  gap: 16px;
  margin-top: 16px;
}
.code-row-bg > .el-card {
  flex: 1;
  min-width: 0;
}
.ech-box {
  margin-top: 10px;
}
/* 切换样式：对齐全局按钮体系（12px / 4px 圆角 / 令牌边框） */
.change-style {
  height: 28px;
  line-height: 26px;
  padding: 0 12px;
  font-size: 12px;
  color: var(--prev-color-primary);
  border: 1px solid var(--prev-border-color-base);
  border-radius: 4px;
  cursor: pointer;
  user-select: none;
  transition: all 0.2s;
}
.change-style:hover {
  border-color: var(--prev-color-primary);
}
.percent-box {
  display: flex;
  align-items: center;
}
.line {
  flex: 1;
  min-width: 0;
  height: 8px;
  position: relative;
}
.bg {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 8px;
  border-radius: 4px;
  background-color: var(--prev-bg-color);
}
.percent {
  position: absolute;
  top: 0;
  left: 0;
  border-radius: 4px;
  height: 8px;
  background-color: var(--prev-color-primary);
}
.num {
  flex-shrink: 0;
  min-width: 40px;
  margin-left: 10px;
  text-align: right;
}
</style>
