<template>
  <div v-loading="spinShow">
    <el-card :bordered="false" shadow="never" class="ivu-mb-16">
      <div class="acea-row row-middle">
        <span class="label_text">时间选择：</span>
        <el-date-picker
          clearable
          v-model="timeVal"
          type="daterange"
          :editable="false"
          @change="onchangeTime"
          format="yyyy/MM/dd"
          value-format="yyyy/MM/dd"
          start-placeholder="开始日期"
          end-placeholder="结束日期"
          :picker-options="pickerOptions"
          style="width: 250px"
          class="mr20"
        ></el-date-picker>
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
            <el-table-column type="index" label="序号" width="50"> </el-table-column>
            <el-table-column label="来源" min-width="80">
              <template slot-scope="scope">
                <span>{{ scope.row.name }}</span>
              </template>
            </el-table-column>
            <el-table-column label="订单数" min-width="130">
              <template slot-scope="scope">
                <span>{{ scope.row.value }}</span>
              </template>
            </el-table-column>
            <el-table-column label="占比率" min-width="130">
              <template slot-scope="scope">
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
            <el-table-column type="index" label="序号" width="50"> </el-table-column>
            <el-table-column label="来源" min-width="80">
              <template slot-scope="scope">
                <span>{{ scope.row.name }}</span>
              </template>
            </el-table-column>
            <el-table-column label="金额" min-width="130">
              <template slot-scope="scope">
                <span>{{ scope.row.value }}</span>
              </template>
            </el-table-column>
            <el-table-column label="占比率" min-width="130">
              <template slot-scope="scope">
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

<script>
import cardsData from '@/components/cards/cards';
import echartsNew from '@/components/echartsNew/index';
import { getBasic, getTrend, getChannel, getType } from '@/api/statistic';
import { formatDate } from '@/utils/validate';
import echartsFrom from '@/components/echarts/index';
import timeOptions from '@/libs/timeOptions';

export default {
  name: 'index',
  components: { cardsData, echartsNew, echartsFrom },
  data() {
    return {
      timeVal: [],
      style: { height: '400px' },
      infoList: {},
      infoList2: {},
      echartLeft: true,
      echartRight: false,
      loading: false,
      loading2: false,
      formValidate: {
        time: '',
      },
      cardLists: [
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
      ],
      optionData: {},
      spinShow: false,
      tabList: [],
      tabList2: [],
      pickerOptions: timeOptions,
    };
  },
  created() {
    const end = new Date();
    const start = new Date();
    start.setTime(start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 29)));
    this.timeVal = [start, end];
    this.formValidate.time = formatDate(start, 'yyyy/MM/dd') + '-' + formatDate(end, 'yyyy/MM/dd');
    this.onInit();
  },
  methods: {
    onInit() {
      this.getBasic();
      this.getTrend();
      this.getChannel();
      this.getType();
    },
    onSelectDate(e) {
      this.formValidate.time = e;
      this.onInit();
    },
    timeG(dd) {
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
    },
    getBasic() {
      getBasic(this.formValidate).then((res) => {
        let arr = ['pay_count', 'pay_price', 'refund_count', 'refund_price'];
        this.cardLists.map((i, index) => {
          i.count = res.data[arr[index]];
        });
      });
    },
    getChannel() {
      this.loading = true;
      getChannel(this.formValidate).then((res) => {
        this.infoList = res.data;
        this.tabList = res.data.list;
        this.loading = false;
      });
    },
    getType() {
      this.loading2 = true;
      getType(this.formValidate).then((res) => {
        this.infoList2 = res.data;
        this.tabList2 = res.data.list;
        this.loading2 = false;
      });
    },
    selectChange(e) {},
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.formValidate.time = this.timeVal ? this.timeVal.join('-') : '';
      this.name = this.formValidate.time;
      this.getBasic();
      this.getTrend();
      this.getChannel();
      this.getType();
    },
    // 统计图
    getTrend() {
      this.spinShow = true;
      getTrend(this.formValidate)
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
          this.optionData = {
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
          this.spinShow = false;
        })
        .catch((res) => {
          this.$message.error(res.msg);
          this.spinShow = false;
        });
    },
  },
};
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
  width: calc(50% - 8px);
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
