<template>
  <div>
    <div :id="echarts" style="height: 300px"></div>
  </div>
</template>

<script>
import echarts from 'echarts';
export default {
  name: 'index',
  props: {
    infoList: {
      type: Object,
      default: null,
    },
    series: Array,
    echartsTitle: {
      type: String,
      default: '',
    },
    yAxisData: {
      type: Array,
      default: () => [],
    },
    bingXdata: Array,
  },
  data() {
    return {
      infoLists: this.infoList,
      seriesArray: this.series,
    };
  },
  watch: {
    infoList: {
      handler(newVal, oldVal) {
        this.infoLists = newVal;
        this.handleSetVisitChart();
      },
      deep: true, // 对象内部属性的监听，关键。
    },
    series: {
      handler(newVal, oldVal) {
        this.seriesArray = newVal;
        this.handleSetVisitChart();
      },
      deep: true, // 对象内部属性的监听，关键。
    },
  },
  computed: {
    echarts() {
      return 'echarts' + Math.ceil(Math.random() * 100);
    },
  },
  mounted: function () {
    const vm = this;
    vm.$nextTick(() => {
      vm.handleSetVisitChart();
      window.addEventListener('resize', this.wsFunc);
    });
  },
  methods: {
    wsFunc() {
      this.myChart.resize();
    },
    handleSetVisitChart() {
      this.myChart = echarts.init(document.getElementById(this.echarts));
      let option = null;
      if (this.echartsTitle === 'circle') {
        option = {
          tooltip: {
            trigger: 'item',
            formatter: '{a} <br/>{b} : {c} ({d}%)',
          },
          legend: {
            orient: 'vertical',
            left: 'right',
            data: this.infoLists.bing_xdata || [],
          },
          series: [
            {
              name: '访问来源',
              type: 'pie',
              radius: '70%',
              center: ['50%', '60%'],
              data: this.infoLists.bing_data || [],
              emphasis: {
                itemStyle: {
                  shadowBlur: 10,
                  shadowOffsetX: 0,
                  shadowColor: 'rgba(0, 0, 0, 0.5)',
                },
              },
            },
          ],
        };
      } else {
        option = {
          tooltip: {
            trigger: 'axis',
          },
          toolbox: {},
          legend: {
            data: this.infoLists.legend || [],
          },
          color: ['#1495EB', '#00CC66', '#F9D249', '#ff9900', '#9860DF'],
          grid: {
            left: 16,
            right: 25,
            bottom: 10,
            top: 40,
            containLabel: true,
          },
          xAxis: [
            {
              type: 'category',
              axisLine: {
                lineStyle: {
                  color: '#D7DDE4',
                },
              },
              axisTick: {
                alignWithLabel: true,
                lineStyle: {
                  color: '#D7DDE4',
                },
              },
              splitLine: {
                show: false,
                lineStyle: {
                  color: '#F5F7F9',
                },
              },
              // axisPointer: {
              //     type: 'shadow'
              // },
              axisLabel: {
                interval: 0,
                rotate: 40,
                textStyle: {
                  color: '#7F8B9C',
                },
              },
              data: this.infoLists.xAxis,
            },
          ],
          yAxis: this.yAxisData.length
            ? this.yAxisData
            : {
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
                type: 'value',
              },
          series: this.seriesArray,
        };
      }
      // 基于准备好的dom，初始化echarts实例
      this.myChart.setOption(option, true);
    },
    handleResize() {
      this.myChart.resize();
    },
  },
  beforeDestroy() {
    window.removeEventListener('resize', this.wsFunc);
    if (!this.myChart) {
      return;
    }
    this.myChart.dispose();
    this.myChart = null;
  },
};
</script>

<style scoped></style>
