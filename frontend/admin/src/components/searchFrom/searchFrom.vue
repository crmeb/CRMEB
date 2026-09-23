<template>
  <div class="table_box">
    <el-form
      ref="DataListRef"
      :model="DataList"
      :rules="rules"
      label-width="85px"
      :label-position="labelPosition"
      class="tabform"
    >
      <el-row :gutter="24" justify="end">
        <el-col :span="24" class="ivu-text-left">
          <el-form-item label="订单状态：">
            <el-radio-group v-model="DataList.status" type="button" @change="selectChange(DataList.status)">
              <el-radio-button :label="item.label" :value="item.label" v-for="(item, i) in typeName" :key="i">{{
                item.name + '(' + item.num + ')'
              }}</el-radio-button>
            </el-radio-group>
          </el-form-item>
        </el-col>
        <el-col :span="24" class="ivu-text-left">
          <el-col v-bind="grid">
            <el-form-item label="创建时间：">
              <el-radio-group v-model="DataList.data" type="button" @change="timeChange(DataList.data)">
                <el-radio-button label="today" value="today">今天</el-radio-button>
                <el-radio-button label="yesterday" value="yesterday">昨天</el-radio-button>
                <el-radio-button label="lately7" value="lately7">最近7天</el-radio-button>
                <el-radio-button label="lately30" value="lately30">最近30天</el-radio-button>
              </el-radio-group>
            </el-form-item>
          </el-col>
          <el-col v-bind="grid">
            <el-form-item class="tab_data">
              <date-range-picker
                :editable="false"
                v-model="value2"
                value-format="YYYY/MM/DD"
                range-separator="-"
              ></date-range-picker>
            </el-form-item>
          </el-col>
        </el-col>
        <el-col :span="24" class="ivu-text-left" v-if="$route.path === routePro + '/echarts/trade/order'">
          <el-form-item label="订单类型：">
            <el-radio-group v-model="currentTab" type="button" @change="onClickTab(currentTab)">
              <el-radio-button label="" value="">全部</el-radio-button>
              <el-radio-button label="1" value="1">普通</el-radio-button>
              <el-radio-button v-permission="'combination'" label="2" value="2">拼团</el-radio-button>
              <el-radio-button v-permission="'bargain'" label="3" value="3">砍价</el-radio-button>
              <el-radio-button v-permission="'seckill'" label="4" value="4">秒杀</el-radio-button>
            </el-radio-group>
          </el-form-item>
        </el-col>
      </el-row>
    </el-form>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useOrderStore } from '@/store/module/order';
import Setting from '@/setting';

defineOptions({ name: 'searchFrom' });

const props = defineProps({
  typeName: Array,
});

const emit = defineEmits(['getTypeNum', 'getSeachTime', 'getList', 'onChangeType', 'on-submit', 'on-reset']);

const orderStore = useOrderStore();

const DataListRef = ref(null);
const routePro = Setting.routePre;
const currentTab = ref('');
const grid = {
  xl: 8,
  lg: 8,
  md: 8,
  sm: 24,
  xs: 24,
};
// collapse: false,
// 搜索条件
const DataList = ref({
  status: '',
  data: '',
  real_name: '',
});
const rules = {};
const statusType = ref('');
const time = ref('');
const value2 = ref([]);

const orderChartType = computed(() => orderStore.orderChartType);

// 订单选择状态
function selectChange(status) {
  emit('getTypeNum', status);
}
// 时间状态
function timeChange(time) {
  emit('getSeachTime', time);
}
// 订单号搜索
function orderSearch(num) {
  getOrderNum(num);
  emit('getList');
}
// 点击订单类型
function onClickTab() {
  emit('onChangeType', currentTab.value);
}
function handleSubmit() {
  emit('on-submit', data);
}
// 刷新
function Refresh() {
  emit('getList');
}
function handleReset() {
  DataListRef.value.resetFields();
  emit('on-reset');
}
</script>

<style lang="scss" scoped>
.tab_data :deep(.ivu-form-item-content) {
  margin-left: 0 !important;
}
.table_box :deep(.ivu-divider-horizontal) {
  margin-top: 0px !important;
}
.table_box :deep(.ivu-form-item) {
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
