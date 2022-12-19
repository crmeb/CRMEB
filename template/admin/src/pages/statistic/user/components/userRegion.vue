<template>
  <Row :gutter="16">
    <Col :xs="24" :sm="24" :md="24" :lg="18">
      <Card :bordered="false" dis-hover class="ivu-mt">
        <div class="acea-row row-between-wrapper">
          <div class="header-title mb20">用户地域分布</div>
        </div>
        <Row>
          <Col :xs="24" :sm="24" :md="24" :lg="10">
            <div class="echarts">
              <div :style="{ height: '400px', width: '100%' }" ref="myEchart"></div>
            </div>
          </Col>
          <Col :xs="24" :sm="24" :md="24" :lg="14">
            <div class="tables">
              <Table height="400" :columns="columns1" :data="resdataList"></Table>
            </div>
          </Col>
        </Row>
      </Card>
    </Col>
    <Col :xs="24" :sm="24" :md="24" :lg="6">
      <Card :bordered="false" dis-hover class="ivu-mt">
        <div class="acea-row row-between-wrapper">
          <div class="header-title mb20">用户性别比例</div>
        </div>
        <echarts-new
          :option-data="optionData"
          :styles="style"
          height="100%"
          width="100%"
          v-if="optionData"
        ></echarts-new>
      </Card>
    </Col>
  </Row>
</template>

<script>
import echarts from 'echarts';
import '../../../../../node_modules/echarts/map/js/china.js'; // 引入中国地图数据
import { statisticWechatRegionApi, statisticWechatSexApi } from '@/api/statistic';
import echartsNew from '@/components/echartsNew/index';
export default {
  name: 'userRegion',
  components: {
    echartsNew,
  },
  props: {
    formInline: {
      type: Object,
      default: function () {
        return {
          channel_type: '',
          data: '',
        };
      },
    },
  },
  data() {
    return {
      chart: null,
      resdata: [],
      resdataList: [],
      columns1: [
        {
          title: 'TOP省份',
          key: 'province',
        },
        {
          title: '累积用户数',
          key: 'allNum',
          sortable: true,
        },
        {
          title: '新增用户数',
          key: 'newNum',
          sortable: true,
        },
        {
          title: '访客数',
          key: 'visitNum',
          sortable: true,
        },
        {
          title: '支付金额',
          key: 'payPrice',
          sortable: true,
        },
      ],
      style: { height: '400px' },
      optionData: {},
    };
  },
  mounted() {
    this.getTrend();
    this.getSex();
  },
  beforeDestroy() {
    if (!this.chart) {
      return;
    }
    this.chart.dispose();
    this.chart = null;
  },
  methods: {
    chinaConfigure() {
      let myChart = echarts.init(this.$refs.myEchart); //这里是为了获得容器所在位置
      window.onresize = myChart.resize;
      myChart.setOption({
        // 进行相关配置
        backgroundColor: '#fff',
        tooltip: {
          trigger: 'item',
          formatter: function (params) {
            return params.data
              ? `地区:${params.name}</br>累计用户: ${params.data.value}</br>新增用户: ${params.data.newNum}</br>访客数: ${params.data.visitNum}</br>支付金额: ${params.data.payPrice}`
              : `地区:${params.name}</br>累计用户: 0</br>新增用户: 0</br>访客数: 0</br>支付金额: 0`;
          },
        }, // 鼠标移到图里面的浮动提示框
        dataRange: {
          show: false,
          min: 0,
          max: 1000,
          text: ['High', 'Low'],
          realtime: true,
          calculable: true,
          color: ['orangered', 'yellow', 'lightskyblue'],
        },
        geo: {
          // 这个是重点配置区
          map: 'china', // 表示中国地图
          roam: false,
          label: {
            normal: {
              show: false, // 是否显示对应地名
              textStyle: {
                color: 'rgba(0,0,0,0.4)',
              },
            },
          },
          itemStyle: {
            normal: {
              borderColor: 'rgba(0, 0, 0, 0.2)',
            },
            emphasis: {
              areaColor: null,
              shadowOffsetX: 0,
              shadowOffsetY: 0,
              shadowBlur: 20,
              borderWidth: 0,
              shadowColor: 'rgba(0, 0, 0, 0.5)',
            },
          },
        },
        series: [
          {
            type: 'scatter',
            zoom: 1.2,
            aspectScale: 1.75, //长宽比
            coordinateSystem: 'geo', // 对应上方配置
          },
          {
            type: 'map',
            geoIndex: 0,
            data: this.resdata,
          },
        ],
      });
    },
    // 统计图
    getTrend() {
      statisticWechatRegionApi(this.formInline)
        .then(async (res) => {
          this.resdataList = res.data;
          this.resdata = res.data.map((item) => {
            let jsonData = {};
            jsonData.name = item.province;
            jsonData.value = item.allNum;
            jsonData.newNum = item.newNum;
            jsonData.payPrice = item.payPrice;
            jsonData.visitNum = item.visitNum;
            return jsonData;
          });
          this.chinaConfigure();
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    //性别
    getSex() {
      statisticWechatSexApi(this.formInline)
        .then(async (res) => {
          let totalSumAll = 0;
          res.data.forEach((item) => {
            totalSumAll += item.value;
          });
          this.optionData = {
            title: {
              show: true,
              text: '总用户数', // 当前写死
              subtext: totalSumAll, // 当前写死
              x: 'center',
              y: 'center',
              textStyle: {
                fontSize: '14',
                color: '#666666',
              },
              subtextStyle: {
                fontSize: '30',
                fontWeight: 'bold',
                color: '#333333',
              },
            },
            tooltip: {
              trigger: 'item',
              formatter: '{a} <br/>{b}: {c} ({d}%)',
            },
            legend: {
              orient: 'vertical',
              left: 10,
              data: ['未知', '男', '女'],
            },
            series: [
              {
                name: '访问来源',
                type: 'pie',
                radius: ['50%', '70%'],
                avoidLabelOverlap: false,
                label: {
                  show: false,
                  position: 'center',
                },
                labelLine: {
                  show: false,
                },
                data: res.data,
                itemStyle: {
                  emphasis: {
                    shadowBlur: 10,
                    shadowOffsetX: 0,
                    shadowColor: 'rgba(0, 0, 0, 0.5)',
                  },
                  normal: {
                    color: function (params) {
                      //自定义颜色
                      var colorList = ['#999999', '#1890FF', '#FFAB2B'];
                      return colorList[params.dataIndex];
                    },
                  },
                },
              },
            ],
          };
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
  },
};
</script>

<style scoped lang="less">
.echarts {
  width: 100%;
}
.tables {
  width: 100%;
  /deep/.ivu-table-overflowY {
    &::-webkit-scrollbar {
      width: 0;
    }
    &::-webkit-scrollbar-track {
      background-color: transparent;
    }
    &::-webkit-scrollbar-thumb {
      background: #e8eaec;
    }
  }
}
</style>
