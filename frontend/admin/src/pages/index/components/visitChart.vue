<template>
  <div @resize="handleResize">
    <el-row :gutter="24">
      <el-col san="24" class="ivu-mb">
        <el-card :bordered="false" shadow="never" class="dashboard-console-visit">
          <div>
            <el-row justify="center" align="middle">
              <el-col :span="8" class="card-title">
                <el-avatar
                  icon="el-icon-s-marketing"
                  style="color: var(--prev-color-primary); background-color: #e6f7ff"
                ></el-avatar>
                <h4 class="ivu-pl-8">订单</h4>
              </el-col>
              <el-col :span="16" class="ivu-text-right">
                <el-radio-group v-model="visitDate" type="button" class="ivu-mr-8" @change="handleChangeVisitType">
                  <el-radio-button label="thirtyday" value="thirtyday">30天</el-radio-button>
                  <el-radio-button label="week" value="week">周</el-radio-button>
                  <el-radio-button label="month" value="month">月</el-radio-button>
                  <el-radio-button label="year" value="year">年</el-radio-button>
                </el-radio-group>
              </el-col>
            </el-row>
          </div>
          <echarts-from
            ref="visitChart"
            :series="series"
            :infoList="infoList"
            v-if="infoList"
            :yAxisData="yAxisData"
          ></echarts-from>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>
<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import { orderApi } from '@/api/index';
import echartsFrom from '@/components/echarts/index';

const visitChart = ref(null);

const infoList = ref(null);
const visitDate = ref('thirtyday');
const series = ref([]);
const yAxisData = ref([]);

// 统计
function getStatistics() {
  let data = {
    cycle: visitDate.value,
  };
  orderApi(data)
    .then(async (res) => {
      infoList.value = res.data || {};
      series.value = infoList.value.series || [];
      yAxisData.value = [
        {
          type: 'value',
          name: '金额',
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
        {
          type: 'value',
          name: '数量',
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
          // axisLabel: {
          //     formatter: '{value} °C'
          // }
        },
      ];
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 时间改变
function handleChangeVisitType() {
  getStatistics();
}
// 监听页面宽度变化，刷新表格
function handleResize() {
  if (infoList.value) visitChart.value.handleResize();
}

// created
getStatistics();
</script>
<style lang="scss" scoped>
.dashboard-console-visit {
  ul {
    li {
      list-style-type: none;
      margin-top: 12px;
    }
  }
}
.ivu-text-right {
  text-align: right;
}
.ivu-pl-8 {
  padding-left: 8px !important;
}
.card-title {
  display: flex;
  align-items: center;
  :deep(.el-avatar--small) {
    background-color: var(--prev-color-primary-light-9) !important;
  }
}
</style>
