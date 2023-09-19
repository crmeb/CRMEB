<template>
  <div v-loading="spinShow">
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-form ref="formValidate" :model="formValidate" class="tabform" @submit.native.prevent>
        <el-row :gutter="24">
          <el-col :span="24">
            <el-form-item label="订单时间：">
              <el-radio-group
                v-model="formValidate.data"
                type="button"
                @change="selectChange(formValidate.data)"
                class="mr"
              >
                <el-radio-button :label="item.val" v-for="(item, i) in fromList.fromTxt" :key="i">{{
                  item.text
                }}</el-radio-button>
              </el-radio-group>
              <el-date-picker
                :editable="false"
                clearable
                @change="onchangeTime"
                v-model="timeVal"
                format="yyyy/MM/dd"
                type="daterange"
                value-format="yyyy/MM/dd"
                range-separator="-"
                start-placeholder="开始日期"
                end-placeholder="结束日期"
              ></el-date-picker>
            </el-form-item> </el-col
        ></el-row>
      </el-form>
    </el-card>
    <cards-data :cardLists="cardLists" v-if="cardLists.length >= 0"></cards-data>
    <echarts-new :option-data="optionData" :styles="style" height="100%" width="100%" v-if="optionData"></echarts-new>
    <div class="code-row-bg">
      <el-card :bordered="false" shadow="never" class="ivu-mt">
        <div class="acea-row row-between-wrapper">
          <div class="statics-header-title">积分来源</div>
          <div>切换样式</div>
        </div>
        <echarts-new
          :option-data="optionData"
          :styles="style"
          height="100%"
          width="100%"
          v-if="optionData"
        ></echarts-new>
      </el-card>
      <el-card :bordered="false" shadow="never" class="ivu-mt">
        <div class="acea-row row-between-wrapper">
          <div class="statics-header-title">积分消耗</div>
          <div>切换样式</div>
        </div>
        <echarts-new
          :option-data="optionData"
          :styles="style"
          height="100%"
          width="100%"
          v-if="optionData"
        ></echarts-new>
      </el-card>
    </div>
  </div>
</template>

<script>
import cardsData from '@/components/cards/cards';
import echartsNew from '@/components/echartsNew/index';

export default {
  name: 'index',
  components: { cardsData, echartsNew },
  data() {
    return {
      timeVal: [],
      style: { height: '400px' },

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
        status: '',
        date: '',
      },
      cardLists: [
        {
          col: 6,
          count: 0,
          name: '参与人数(人)',
          className: 'ios-speedometer-outline',
        },
        {
          col: 6,
          count: 0,
          name: '成团数量(个)',
          className: 'md-rose',
        },
        {
          col: 6,
          count: 0,
          name: '参与人数(人)',
          className: 'ios-speedometer-outline',
        },
        {
          col: 6,
          count: 0,
          name: '成团数量(个)',
          className: 'md-rose',
        },
        {
          col: 6,
          count: 0,
          name: '参与人数(人)',
          className: 'ios-speedometer-outline',
        },
        {
          col: 6,
          count: 0,
          name: '成团数量(个)',
          className: 'md-rose',
        },
      ],
      optionData: {},
      spinShow: false,
    };
  },
  created() {
    // this.getTrend();
  },
  methods: {
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.dataTime = this.timeVal ? this.timeVal.join('-') : '';
      this.name = this.dataTime;
    },
    // 统计图
    getTrend() {
      this.spinShow = true;
      statisticUserTrendApi(this.formInline)
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
              data: item.value,
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
}
.code-row-bg .ivu-mt {
  width: 100%;
  margin: 0 5px;
}
</style>
