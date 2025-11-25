<template>
  <div class="table_box">
    <el-form
      ref="DataList"
      :model="DataList"
      :rules="rules"
      label-width="85px"
      :label-position="labelPosition"
      class="tabform"
    >
      <el-row :gutter="24" justify="end">
        <el-col :span="24" class="ivu-text-left">
          <el-form-item label="订单状态：">
            <el-radio-group v-model="DataList.status" type="button" @input="selectChange(DataList.status)">
              <el-radio-button :label="item.label" v-for="(item, i) in typeName" :key="i">{{
                item.name + '(' + item.num + ')'
              }}</el-radio-button>
            </el-radio-group>
          </el-form-item>
        </el-col>
        <el-col :span="24" class="ivu-text-left">
          <el-col v-bind="grid">
            <el-form-item label="创建时间：">
              <el-radio-group v-model="DataList.data" type="button" @input="timeChange(DataList.data)">
                <el-radio-button label="today">今天</el-radio-button>
                <el-radio-button label="yesterday">昨天</el-radio-button>
                <el-radio-button label="lately7">最近7天</el-radio-button>
                <el-radio-button label="lately30">最近30天</el-radio-button>
              </el-radio-group>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid">
            <el-form-item class="tab_data">
              <el-date-picker
                :editable="false"
                v-model="value2"
                value-format="yyyy/MM/dd"
                type="daterange"
                range-separator="-"
                start-placeholder="开始日期"
                end-placeholder="结束日期"
                style="width: 200px"
              ></el-date-picker>
            </el-form-item>
          </el-col>
        </el-col>
        <el-col :span="24" class="ivu-text-left" v-if="$route.path === routePro + '/echarts/trade/order'">
          <el-form-item label="订单类型：">
            <el-radio-group v-model="currentTab" type="button" @input="onClickTab(currentTab)">
              <el-radio-button label="">全部</el-radio-button>
              <el-radio-button label="1">普通</el-radio-button>
              <el-radio-button v-permission="'combination'" label="2">拼团</el-radio-button>
              <el-radio-button v-permission="'bargain'" label="3">砍价</el-radio-button>
              <el-radio-button v-permission="'seckill'" label="4">秒杀</el-radio-button>
            </el-radio-group>
          </el-form-item>
        </el-col>
      </el-row>
    </el-form>
  </div>
</template>

<script>
import { mapState } from 'vuex';
export default {
  name: 'searchFrom',
  props: {
    typeName: Array,
  },
  data() {
    return {
      routePro: this.$routeProStr,
      currentTab: '',
      grid: {
        xl: 8,
        lg: 8,
        md: 8,
        sm: 24,
        xs: 24,
      },
      // collapse: false,
      // 搜索条件
      DataList: {
        status: '',
        data: '',
        real_name: '',
      },
      rules: {},
      statusType: '',
      time: '',
      value2: [],
    };
  },
  computed: {
    ...mapState('order', ['orderChartType']),
  },
  methods: {
    // 订单选择状态
    selectChange(status) {
      this.$emit('getTypeNum', status);
    },
    // 时间状态
    timeChange(time) {
      this.$emit('getSeachTime', time);
    },
    // 订单号搜索
    orderSearch(num) {
      this.getOrderNum(num);
      this.$emit('getList');
    },
    // 点击订单类型
    onClickTab() {
      this.$emit('onChangeType', this.currentTab);
    },
    handleSubmit() {
      this.$emit('on-submit', this.data);
    },
    // 刷新
    Refresh() {
      this.$emit('getList');
    },
    handleReset() {
      this.$refs.form.resetFields();
      this.$emit('on-reset');
    },
  },
};
</script>

<style lang="scss" scoped>
.tab_data ::v-deep .ivu-form-item-content {
  margin-left: 0 !important;
}
.table_box ::v-deep .ivu-divider-horizontal {
  margin-top: 0px !important;
}
.table_box ::v-deep .ivu-form-item {
  margin-bottom: 15px !important;
}
.tabform {
  margin-bottom: 10px;
}
.Refresh {
  font-size: 12px;
  color: var(--prev-color-primary);
  cursor: pointer;
}
</style>
