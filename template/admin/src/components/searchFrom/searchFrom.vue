<template>
  <div class="table_box">
    <Form
      ref="DataList"
      :model="DataList"
      :rules="rules"
      :label-width="80"
      :label-position="labelPosition"
      class="tabform"
    >
      <Row :gutter="24" type="flex" justify="end">
        <Col span="24" class="ivu-text-left">
          <FormItem label="订单状态：">
            <RadioGroup v-model="DataList.status" type="button" @on-change="selectChange(DataList.status)">
              <Radio :label="item.label" v-for="(item, i) in typeName" :key="i">{{
                item.name + '(' + item.num + ')'
              }}</Radio>
            </RadioGroup>
          </FormItem>
        </Col>
        <Col span="24" class="ivu-text-left">
          <Col v-bind="grid">
            <FormItem label="创建时间：">
              <RadioGroup v-model="DataList.data" type="button" @on-change="timeChange(DataList.data)">
                <Radio label="today">今天</Radio>
                <Radio label="yesterday">昨天</Radio>
                <Radio label="lately7">最近7天</Radio>
                <Radio label="lately30">最近30天</Radio>
              </RadioGroup>
            </FormItem>
          </Col>
          <Col v-bind="grid">
            <FormItem class="tab_data">
              <DatePicker
                :editable="false"
                :value="value2"
                format="yyyy/MM/dd"
                type="daterange"
                placement="bottom-end"
                placeholder="Select date"
                style="width: 200px"
              ></DatePicker>
            </FormItem>
          </Col>
        </Col>
        <Col span="24" class="ivu-text-left" v-if="$route.path === '/admin/echarts/trade/order'">
          <FormItem label="订单类型：">
            <RadioGroup v-model="currentTab" type="button" @on-change="onClickTab(currentTab)">
              <Radio label="">全部</Radio>
              <Radio label="1">普通</Radio>
              <Radio label="2">拼团</Radio>
              <Radio label="3">砍价</Radio>
              <Radio label="4">秒杀</Radio>
            </RadioGroup>
          </FormItem>
        </Col>
      </Row>
    </Form>
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

<style scoped lang="stylus">
.tab_data >>> .ivu-form-item-content
    margin-left 0 !important
.table_box >>> .ivu-divider-horizontal
    margin-top 0px !important
.table_box >>> .ivu-form-item
    margin-bottom: 15px !important;
.tabform
    margin-bottom 10px
.Refresh
    font-size 12px
    color #1890FF
    cursor pointer
</style>
