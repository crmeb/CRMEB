<template>
  <div @resize="handleResize">
    <el-row :gutter="16">
      <el-col :xl="16" :lg="12" :md="24" :sm="24" :xs="24" class="ivu-mb dashboard-console-visit">
        <el-card :bordered="false" shadow="never">
          <div class="card-title">
            <el-avatar
              icon="el-icon-user-solid"
              style="color: var(--prev-color-primary); background-color: #e6f7ff"
            ></el-avatar>
            <h4 class="ivu-pl-8">用户</h4>
          </div>
          <echarts-from
            ref="userChart"
            :echartsTitle="line"
            :infoList="infoList"
            :series="series"
            v-if="infoList && series.length !== 0"
          ></echarts-from>
        </el-card>
      </el-col>
      <el-col :xl="8" :lg="12" :md="24" :sm="24" :xs="24">
        <el-card :bordered="false" shadow="never" class="dashboard-console-visit">
          <div class="card-title">
            <el-avatar
              icon="el-icon-s-marketing"
              style="color: var(--prev-color-primary); background-color: #e6f7ff"
            ></el-avatar>
            <h4 class="ivu-pl-8">购买用户统计</h4>
          </div>
          <echarts-from ref="visitChart" :infoList="infoList" :echartsTitle="circle"></echarts-from>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { ElMessage } from 'element-plus';
import { userApi, rankApi } from '@/api/index';
import echartsFrom from '@/components/echarts/index';

defineOptions({ name: 'user-chart' });

const userChart = ref(null);
const visitChart = ref(null);

const line = 'line';
const circle = 'circle';
const infoList = ref({});
const series = ref([]);
const xData = ref([]);
const y1Data = ref([]);
const y2Data = ref([]);
const lists = ref([]);
const bing_data = ref([]);
const bing_xdata = ref([]);

// 统计
function getStatistics() {
  userApi()
    .then(async (res) => {
      infoList.value = res.data;
      series.value = [
        {
          data: res.data.series,
          name: '人数（人）',
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
      ];
      bing_data.value = res.bing_data;
      bing_xdata.value = res.bing_xdata;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function getRank() {
  rankApi()
    .then(async (res) => {
      let data = res.data;
      lists.value = data.list;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 监听页面宽度变化，刷新表格
function handleResize() {
  if (infoList.value && series.value.length !== 0) userChart.value.handleResize();
  if (infoList.value) visitChart.value.handleResize();
}

onMounted(() => {
  getStatistics();
  getRank();
});
</script>

<style scoped lang="scss">
.dashboard-console-visit {
  ul {
    li {
      list-style-type: none;
      margin-top: 12px;
    }
  }
}
.trees-coadd {
  width: 100%;
  height: 100%;
  .scollhide {
    width: 100%;
    height: 100%;
    overflow-x: hidden;
    overflow-y: scroll;
  }
}
.scollhide::-webkit-scrollbar {
  display: none;
}
.names {
  display: inline-block;
  text-overflow: ellipsis;
  overflow: hidden;
  white-space: nowrap;
  width: 84%;
  margin-bottom: -7px;
}
.card-title {
  display: flex;
  align-items: center;
  :deep(.el-avatar--small) {
    background-color: var(--prev-color-primary-light-9) !important;
  }
}
</style>
