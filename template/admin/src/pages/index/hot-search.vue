<template>
  <div>
    <Row :gutter="24">
      <Col span="12">
        <NumberInfo total="23,378" sub-total="16.8" status="up">
          <span slot="subTitle">
            搜索用户数
            <Tooltip content="指标说明" placement="top">
              <Icon type="ios-information-circle-outline" />
            </Tooltip>
          </span>
        </NumberInfo>
        <div style="height: 50px" ref="searchUserChart"></div>
      </Col>
      <Col span="12">
        <NumberInfo total="3.1" sub-total="8.5" status="down">
          <span slot="subTitle">
            人均搜索次数
            <Tooltip content="指标说明" placement="top">
              <Icon type="ios-information-circle-outline" />
            </Tooltip>
          </span>
        </NumberInfo>
        <div style="height: 50px" ref="searchCountChart"></div>
      </Col>
    </Row>
    <div class="ivu-mt">
      <search-table />
    </div>
  </div>
</template>
<script>
import echarts from 'echarts';
import searchTable from './search-table';

export default {
  components: { searchTable },
  data() {
    return {};
  },
  methods: {
    handleSetSearchChart() {
      this.searchUserChart = echarts.init(this.$refs.searchUserChart);
      this.searchCountChart = echarts.init(this.$refs.searchCountChart);
      const option = {
        xAxis: {
          data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
          type: 'category',
          show: false,
        },
        yAxis: {
          show: false,
          type: 'value',
        },
        series: [
          {
            data: [120, 300, 150, 350, 70, 210, 130],
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
        ],
        color: ['#1495EB', '#00CC66', '#F9D249', '#ff9900', '#9860DF'],
        grid: {
          left: -60,
          right: -20,
          bottom: -20,
          top: 0,
          containLabel: true,
        },
        tooltip: {
          trigger: 'axis',
        },
      };
      this.searchUserChart.setOption(option);
      this.searchCountChart.setOption(option);
    },
    handleResize() {
      if (this.searchUserChart) this.searchUserChart.resize();
      if (this.searchCountChart) this.searchCountChart.resize();
    },
  },
  mounted() {
    this.handleSetSearchChart();
  },
  beforeDestroy() {
    if (this.searchUserChart) {
      this.searchUserChart.dispose();
      this.searchUserChart = null;
    }
    if (this.searchCountChart) {
      this.searchCountChart.dispose();
      this.searchCountChart = null;
    }
  },
};
</script>
