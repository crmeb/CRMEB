<template>
  <div v-loading="spinShow">
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-form ref="formValidate" :model="formValidate" class="tabform" @submit.prevent>
        <el-row :gutter="24">
          <el-col :span="24">
            <el-form-item label="订单时间：">
              <el-radio-group
                v-model="formValidate.data"
                type="button"
                @change="selectChange(formValidate.data)"
                class="mr"
              >
                <el-radio-button :label="item.val" :value="item.val" v-for="(item, i) in fromList.fromTxt" :key="i">{{
                  item.text
                }}</el-radio-button>
              </el-radio-group>
              <date-range-picker
                :editable="false"
                clearable
                @change="onchangeTime"
                v-model="timeVal"
                format="YYYY/MM/DD"
                value-format="YYYY/MM/DD"
                range-separator="-"
              ></date-range-picker>
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

<script setup>
import { ref } from 'vue';
import cardsData from '@/components/cards/cards';
import echartsNew from '@/components/echartsNew/index';

defineOptions({ name: 'index' });

const timeVal = ref([]);
const style = { height: '400px' };

const fromList = {
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
};
const formValidate = ref({
  status: '',
  date: '',
});
const cardLists = [
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
];
const optionData = ref({});
const spinShow = ref(false);

// 具体日期
function onchangeTime(e) {
  timeVal.value = e;
}
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
