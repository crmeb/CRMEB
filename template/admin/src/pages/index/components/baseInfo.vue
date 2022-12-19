<template>
  <Row :gutter="24">
    <Col v-bind="grid" class="ivu-mb" v-for="(item, index) in infoList" :key="index">
      <Card :bordered="false" dis-hover :padding="12">
        <p slot="title">
          <span v-text="item.title"></span>
        </p>
        <Tag slot="extra" color="green">{{ item.date }}</Tag>
        <div>
          <!--<Numeral :value="item.yesterday" style=""/>-->
          <div class="number">{{ item.yesterday }}</div>
          <div class="ivu-pt-8" style="height: 42px">
            <span style="display: inline-block" class="ivu-mr">
              <!--日同比 <Trend :flag="Number(item.today_ratio)>=0?'up':'down'">{{Number(item.today_ratio)}}%</Trend>-->
              日环比 {{ Number(item.today_ratio) }}%
              <Icon
                :type="Number(item.today_ratio) >= 0 ? 'md-arrow-dropup' : 'md-arrow-dropdown'"
                class="iconColor"
                :class="Number(item.today_ratio) >= 0 ? ' ' : 'on'"
              />
            </span>
            <span style="display: inline-block">
              <!--周同比 <Trend :flag="Number(item.week_ratio)>=0?'up':'down'">{{Number(item.week_ratio)}}%</Trend>-->
              周环比 {{ Number(item.week_ratio) }}%
              <Icon
                :type="Number(item.week_ratio) >= 0 ? 'md-arrow-dropup' : 'md-arrow-dropdown'"
                class="iconColor"
                :class="Number(item.week_ratio) >= 0 ? ' ' : 'on'"
              />
            </span>
          </div>
          <Divider style="margin: 8px 0" />
          <div>
            <Row>
              <Col span="12" v-text="item.total_name"></Col>
              <Col span="12" class="ivu-text-right">{{ item.total }}</Col>
            </Row>
          </div>
        </div>
      </Card>
    </Col>
  </Row>
</template>
<script>
import echarts from 'echarts';
import { headerApi } from '@/api/index';
export default {
  data() {
    return {
      infoList: [],
      grid: {
        xl: 6,
        lg: 12,
        md: 12,
        sm: 12,
        xs: 24,
      },
      excessStyle: {
        color: '#f56a00',
        backgroundColor: '#fde3cf',
      },
      avatarList: [],
    };
  },
  methods: {
    // 统计
    getStatistics() {
      headerApi()
        .then(async (res) => {
          let data = res.data;
          this.infoList = data.info;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
  },
  mounted() {
    this.getStatistics();
  },
};
</script>
<style lang="stylus">
.number {
  font-size: 30px;
  margin-bottom: 10px;
}

.iconColor {
  color: #ed4014;
}

.iconColor.on {
  color: #19be6b;
}

.ivu-mr {
  margin-right: 16px !important;
}

.ivu-text-right {
  text-align: right;
}
</style>
