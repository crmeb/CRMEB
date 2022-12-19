<template>
  <Card :bordered="false" dis-hover class="ivu-mt">
    <div class="acea-row row-between-wrapper">
      <div class="header-title mb20">
        用户概况
        <Poptip word-wrap width="500" trigger="hover" placement="right-start">
          <Icon type="ios-information-circle-outline" />
          <div slot="content">
            <div>访客数</div>
            <div>在选定条件下，访问商城页面的去重人数</div>
            <br />
            <div>浏览量</div>
            <div>在选定条件下，用户浏览商城页面的次数。每打开一个页面或每刷新一次页面都记录1次</div>
            <br />
            <div>新增用户数</div>
            <div>在选定条件下，新注册的用户</div>
            <br />
            <div>成交用户数</div>
            <div>在选定条件下，下单并支付成功的用户</div>
            <br />
            <div>访客-支付转化率</div>
            <div>在选定条件下，全部成交客户数 / 商城访客数</div>
            <br />
            <div>激活付费会员数</div>
            <div>在选定条件下，通过各种方式成为付费会员的用户数</div>
            <br />
            <div>充值用户数</div>
            <div>在选定条件下，成功充值的用户</div>
            <br />
            <div>客单价</div>
            <div>在选定条件下，用户支付的总金额 / 支付人数</div>
            <br />
            <div>累积用户数</div>
            <div>商城的总用户</div>
            <br />
            <div>累积付费会员数</div>
            <div>筛选时间截止时，具有商城付费会员身份的用户数</div>
            <br />
            <div>累积充值用户数</div>
            <div>筛选时间截止时，商城成功充值过的用户</div>
            <br />
            <div>累积成交用户数</div>
            <div>筛选时间截止时，下单并支付成功的用户</div>
          </div>
        </Poptip>
      </div>
    </div>
    <div class="mb20" style="padding-left: 25px">
      <Row>
        <Col v-bind="grid" v-for="(item, index) in list" :key="index">
          <div class="acea-row mb30 fwn">
            <div class="iconCrl mr15" :class="item.colors">
              <i class="iconfont" :class="item.icon"></i>
            </div>
            <div class="info">
              <span class="sp1" v-text="item.name"></span>
              <span class="sp2" v-if="index === list.length - 1" v-text="item.list.num"></span>
              <span class="sp2" v-else v-text="item.list.num"></span>
              <span class="content-time spBlock"
                >环比增长：<i class="content-is" :class="Number(item.list.percent) >= 0 ? 'up' : 'down'"
                  >{{ item.list.percent }}%</i
                ><Icon
                  :color="Number(item.list.percent) >= 0 ? '#F5222D' : '#39C15B'"
                  :type="Number(item.list.percent) >= 0 ? 'md-arrow-dropup' : 'md-arrow-dropdown'"
              /></span>
            </div>
          </div>
        </Col>
      </Row>
    </div>
    <echarts-new :option-data="optionData" :styles="style" height="100%" width="100%" v-if="optionData"></echarts-new>
    <Spin size="large" fix v-if="spinShow"></Spin>
  </Card>
</template>

<script>
import { statisticUserBasicApi, statisticUserTrendApi } from '@/api/statistic';
import echartsNew from '@/components/echartsNew/index';
export default {
  name: 'userInfo',
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
      spinShow: false,
      grid: {
        xl: 4,
        lg: 8,
        md: 12,
        sm: 24,
        xs: 24,
      },
      name: '近30天',
      timeVal: [],
      dataTime: '',
      list: [],
      optionData: {},
      style: { height: '400px' },
    };
  },
  mounted() {
    this.getStatistics();
    this.getTrend();
  },
  methods: {
    onSeach() {
      this.getStatistics();
      this.getTrend();
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.dataTime = this.timeVal.join('-');
      this.name = this.dataTime;
    },
    // 统计
    getStatistics() {
      statisticUserBasicApi(this.formInline)
        .then(async (res) => {
          const cardLists = res.data;
          this.list = [
            {
              name: '访客数',
              icon: 'iconfangkeshu',
              list: cardLists.people,
              colors: 'one',
            },
            {
              name: '浏览量',
              icon: 'iconshangpinliulanliang',
              list: cardLists.browse,
              colors: 'two',
            },
            {
              name: '新增用户数',
              icon: 'iconxinzengyonghushu',
              list: cardLists.newUser,
              colors: 'three',
            },
            {
              name: '成交用户数',
              icon: 'iconchengjiaoyonghushu',
              list: cardLists.payPeople,
              colors: 'four',
            },
            {
              name: '访客-支付转化率',
              icon: 'iconfangke-zhifuzhuanhuashuai',
              list: cardLists.payPercent,
              colors: 'three',
            },
            {
              name: '付费会员数',
              icon: 'iconfufeihuiyuanshu',
              list: cardLists.payUser,
              colors: 'four',
            },
            {
              name: '充值用户数',
              icon: 'iconchongzhiyonghushu',
              list: cardLists.rechargePeople,
              colors: 'two',
            },
            {
              name: '客单价',
              icon: 'iconkedanjia',
              list: cardLists.payPrice,
              colors: 'one',
            },
            {
              name: '累计用户',
              icon: 'iconleijiyonghu',
              list: cardLists.cumulativeUser,
              colors: 'four',
            },
            {
              name: '累计付费会员数',
              icon: 'iconfufeihuiyuanshu',
              list: cardLists.cumulativePayUser,
              colors: 'one',
            },
            {
              name: '累计充值用户数',
              icon: 'iconchongzhiyonghushu',
              list: cardLists.cumulativeRechargePeople,
              colors: 'four',
            },
            {
              name: '累计成交用户数',
              icon: 'iconchengjiaoyonghushu',
              list: cardLists.cumulativePayPeople,
              colors: 'three',
            },
          ];
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
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
              smooth: true,
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

<style scoped lang="less">
.one {
  background: #1890ff;
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
.iconfont {
  font-size: 16px;
  color: #fff;
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
    color: rgba(0, 0, 0, 0.85);
  }
  &-time {
    font-size: 12px;
    color: #000000;
    opacity: 0.45;
  }
}
.iconCrl {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  text-align: center;
  line-height: 32px;
  opacity: 0.7;
  /*margin-left: 74px;*/
}

.lan {
  background: #1890ff;
}

.iconshangpinliulanliang {
  color: #fff;
}

.infoBox {
  width: 20%;
}

.info {
  .sp1 {
    color: #666;
    font-size: 14px;
    display: block;
  }
  .sp2 {
    font-weight: 400;
    font-size: 30px;
    color: rgba(0, 0, 0, 0.85);
    display: block;
  }
  .sp3 {
    font-size: 12px;
    font-weight: 400;
    color: rgba(0, 0, 0, 0.45);
    display: block;
  }
}
.fwn {
  flex-wrap: nowrap;
}
</style>
