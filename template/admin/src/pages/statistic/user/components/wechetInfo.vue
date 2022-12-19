<template>
  <Card :bordered="false" dis-hover class="ivu-mt">
    <div class="acea-row row-between-wrapper">
      <div class="header-title mb20">
        公众号用户概括
        <Poptip word-wrap width="500" trigger="hover" placement="right-start">
          <Icon type="ios-information-circle-outline" />
          <div slot="content">
            <div>新增关注用户数</div>
            <div>在选定条件下，关注公众号的用户数量，包括首次关注和再次关注的用户</div>
            <br />
            <div>新增取关用户数</div>
            <div>在选定条件下，取消关注公众号的用户数量</div>
            <br />
            <div>净增用户数</div>
            <div>在选定条件下，新增关注用户数 - 新增取关用户数</div>
            <br />
            <div>累积关注用户数</div>
            <div>筛选时间截止时，关注公众号的用户数量</div>
            <br />
            <div>累积取关用户数</div>
            <div>筛选时间截止时，取消关注公众号的用户数量</div>
          </div>
        </Poptip>
      </div>
    </div>
    <div class="acea-row mb20">
      <div class="infoBox acea-row mb30" v-for="(item, index) in list" :key="index">
        <div
          class="iconCrl mr15"
          :class="{ one: index % 4 == 0, two: index % 4 == 1, three: index % 4 == 2, four: index % 4 == 3 }"
        >
          <i class="iconfont" :class="item.icon"></i>
        </div>
        <div class="info">
          <span class="sp1" v-text="item.name"></span>
          <span class="sp2" v-if="index === list.length - 1" v-text="item.list.num"></span>
          <span class="sp2" v-else v-text="item.list.num"></span>
          <span class="content-time spBlock"
            >环比增长：<i class="content-is" :class="Number(item.list.percent) >= 0 ? 'up' : 'down'"
              >{{ Number(item.list.percent).toFixed(2) }}%</i
            ><Icon
              :color="Number(item.list.percent) >= 0 ? '#F5222D' : '#39C15B'"
              :type="Number(item.list.percent) >= 0 ? 'md-arrow-dropup' : 'md-arrow-dropdown'"
          /></span>
        </div>
      </div>
    </div>
    <echarts-new :option-data="optionData" :styles="style" height="100%" width="100%" v-if="optionData"></echarts-new>
    <Spin size="large" fix v-if="spinShow"></Spin>
  </Card>
</template>

<script>
import { statisticWechatApi, statisticWechatTrendApi } from '@/api/statistic';
import echartsNew from '@/components/echartsNew/index';
export default {
  name: 'wechetInfo',
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
        xl: 8,
        lg: 8,
        md: 8,
        sm: 24,
        xs: 24,
      },
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
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.dataTime = this.timeVal.join('-');
      this.name = this.dataTime;
      this.getStatistics();
      this.getTrend();
      // this.userFrom.user_time = this.timeVal.join('-')
    },
    // 统计
    getStatistics() {
      statisticWechatApi(this.formInline)
        .then(async (res) => {
          const cardLists = res.data;
          this.list = [
            {
              name: '新增关注用户数',
              icon: 'iconxinzengguanzhuyonghu',
              list: cardLists.subscribe,
            },
            {
              name: '新增取关用户数',
              icon: 'iconxinzengquguanyonghu',
              list: cardLists.unSubscribe,
            },
            {
              name: '净增用户数',
              icon: 'iconjingzengyonghu',
              list: cardLists.increaseSubscribe,
            },
            {
              name: '累积关注用户数',
              icon: 'iconleijiguanzhuyonghu',
              list: cardLists.cumulativeSubscribe,
            },
            {
              name: '累积取关用户数',
              icon: 'iconleijiquguanyonghu',
              list: cardLists.cumulativeUnSubscribe,
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
      statisticWechatTrendApi(this.formInline)
        .then(async (res) => {
          let legend = res.data.series.map((item) => {
            return item.name;
          });
          let xAxis = res.data.xAxis;
          let col = ['#5B8FF9', '#5AD8A6', '#5D7092', '#5D7092'];
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

.iconfont {
  font-size: 16px;
  color: #fff;
}

.iconCrl {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  text-align: center;
  line-height: 32px;
  opacity: 0.7;
}

.lan {
  background: #1890ff;
}

.iconshangpinliulanliang {
  color: #fff;
}

.infoBox {
  width: 20%;
  @media screen and (max-width: 1200px) {
    width: 33%;
  }
  @media screen and (max-width: 900px) {
    width: 50%;
  }
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
</style>
