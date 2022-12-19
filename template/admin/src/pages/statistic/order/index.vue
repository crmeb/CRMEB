<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <dateRadio @selectDate="onSelectDate"></dateRadio>
      <DatePicker
        :editable="false"
        :clearable="false"
        @on-change="onchangeTime"
        :value="timeVal"
        format="yyyy/MM/dd"
        type="daterange"
        placement="bottom-start"
        placeholder="请选择时间"
        style="width: 200px"
        :options="options"
        class="mr20"
      ></DatePicker>
    </Card>
    <cards-data :cardLists="cardLists" v-if="cardLists.length >= 0"></cards-data>
    <Card :bordered="false" dis-hover>
      <h3>营业趋势</h3>
      <echarts-new :option-data="optionData" :styles="style" height="100%" width="100%" v-if="optionData"></echarts-new>
    </Card>
    <Spin size="large" fix v-if="spinShow"></Spin>
    <div class="code-row-bg">
      <Card :bordered="false" dis-hover class="ivu-mt">
        <div class="acea-row row-between-wrapper">
          <h3 class="header-title">订单来源分析</h3>
          <div class="change-style" @click="echartLeft = !echartLeft">切换样式</div>
        </div>
        <div class="ech-box">
          <echarts-from v-if="echartLeft" ref="visitChart" :infoList="infoList" echartsTitle="circle"></echarts-from>
          <Table
            v-show="!echartLeft"
            ref="selection"
            :columns="columns"
            :data="tabList"
            :loading="loading"
            no-data-text="暂无数据"
            highlight-row
            no-filtered-data-text="暂无筛选结果"
          >
            <template slot-scope="{ row }" slot="percent">
              <div class="percent-box">
                <div class="line">
                  <div class="bg"></div>
                  <div class="percent" :style="'width:' + row.percent + '%;'"></div>
                </div>
                <div class="num">{{ row.percent }}%</div>
              </div>
            </template>
          </Table>
        </div>
      </Card>
      <Card :bordered="false" dis-hover class="ivu-mt">
        <div class="acea-row row-between-wrapper">
          <h3 class="header-title">订单类型分析</h3>
          <div class="change-style" @click="echartRight = !echartRight">切换样式</div>
        </div>
        <div class="ech-box">
          <echarts-from v-if="echartRight" ref="visitChart" :infoList="infoList2" echartsTitle="circle"></echarts-from>
          <Table
            v-show="!echartRight"
            ref="selection"
            :columns="columns"
            :data="tabList2"
            :loading="loading2"
            no-data-text="暂无数据"
            highlight-row
            no-filtered-data-text="暂无筛选结果"
          >
            <template slot-scope="{ row }" slot="percent">
              <div class="percent-box">
                <div class="line">
                  <div class="bg"></div>
                  <div class="percent" :style="'width:' + row.percent + '%;'"></div>
                </div>
                <div class="num">{{ row.percent }}%</div>
              </div>
            </template>
          </Table>
        </div>
      </Card>
    </div>
  </div>
</template>

<script>
import cardsData from '@/components/cards/cards';
import echartsNew from '@/components/echartsNew/index';
import { getBasic, getTrend, getChannel, getType } from '@/api/statistic';
import { formatDate } from '@/utils/validate';
import echartsFrom from '@/components/echarts/index';
import dateRadio from '@/components/dateRadio';

export default {
  name: 'index',
  components: { cardsData, echartsNew, echartsFrom, dateRadio },
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
      fromList: {
        title: '选择时间',
        custom: true,
        fromTxt: [
          { text: '全部', val: '' },
          { text: '今天', val: 'today' },
          { text: '本周', val: 'week' },
          { text: '本月', val: 'month' },
          { text: '本季度', val: 'quarter' },
          { text: '本年', val: 'year' },
        ],
      },
      formValidate: {
        time: '',
      },
      cardLists: [
        {
          col: 6,
          count: 0,
          name: '订单量',
          className: 'md-rose',
        },
        {
          col: 6,
          count: 0,
          name: '订单销售额',
          className: 'ios-speedometer-outline',
        },
        {
          col: 6,
          count: 0,
          name: '退款订单数',
          className: 'ios-speedometer-outline',
        },
        {
          col: 6,
          count: 0,
          name: '退款金额',
          className: 'md-rose',
        },
      ],
      optionData: {},
      spinShow: false,
      options: this.$timeOptions,
      columns: [
        {
          title: '序号',
          type: 'index',
          width: 60,
          align: 'center',
        },
        {
          title: '来源',
          key: 'name',
          minWidth: 80,
          align: 'center',
        },
        {
          title: '金额',
          width: 180,
          key: 'value',
          align: 'center',
        },
        {
          title: '占比率',
          slot: 'percent',
          minWidth: 100,
          align: 'center',
        },
      ],
      tabList: [],
      tabList2: [],
    };
  },
  created() {
    // this.getTrend();
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
    selectChange(e) {
      console.log(this.timeG(e.split(',')[0]), this.timeG(e.split(',')[1]));
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.formValidate.time = this.timeVal.join('-');
      this.name = this.formValidate.time;
      this.getBasic();
      this.getTrend();
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
          this.$Message.error(res.msg);
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
}
.code-row-bg .ivu-mt {
  width: 100%;
  margin: 0 5px;
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
  background-color: cornflowerblue;
  z-index: 9999;
}
.num {
  white-space: nowrap;
  margin: 0 10px;
  width: 15px;
}
</style>
