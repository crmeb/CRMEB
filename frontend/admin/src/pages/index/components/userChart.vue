<template>
  <div @resize="handleResize">
    <el-row :gutter="16">
      <el-col :xl="16" :lg="12" :md="24" :sm="24" :xs="24" class="ivu-mb dashboard-console-visit">
        <el-card :bordered="false" shadow="never">
          <div class="card-title">
            <el-avatar
              icon="UserFilled"
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
      <el-col :xl="8" :lg="12" :md="24" :sm="24" :xs="24" class="ivu-mb">
        <el-card :bordered="false" shadow="never" class="dashboard-console-visit">
          <div class="card-title">
            <el-avatar
              icon="DataAnalysis"
              style="color: var(--prev-color-primary); background-color: #e6f7ff"
            ></el-avatar>
            <h4 class="ivu-pl-8">购买用户统计</h4>
          </div>
          <echarts-from ref="visitChart" :infoList="infoList" :echartsTitle="circle"></echarts-from>
        </el-card>
      </el-col>
      <el-col :span="24" class="ivu-mb">
        <el-card :bordered="false" shadow="never" class="dashboard-console-visit">
          <div class="card-title">
            <el-avatar
              icon="TrendCharts"
              style="color: var(--prev-color-primary); background-color: #e6f7ff"
            ></el-avatar>
            <h4 class="ivu-pl-8">商品支付排行</h4>
          </div>
          <div class="trees-coadd">
            <div class="scollhide">
              <div v-if="lists.length" class="rank-list">
                <div v-for="(item, index) in lists" :key="item.product_id || index" class="rank-item">
                  <span class="rank-index" :class="{ top: index < 3 }">{{ index + 1 }}</span>
                  <span class="names" :title="item.store_name">{{ item.store_name }}</span>
                  <span class="rank-val">¥{{ formatVal(item.val) }}</span>
                </div>
              </div>
              <el-empty v-else description="暂无排行数据" :image-size="64" />
            </div>
          </div>
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
const lists = ref([]);

function formatVal(val) {
  const num = Number(val || 0);
  return Number.isFinite(num) ? num.toFixed(2) : '0.00';
}

// 统计
function getStatistics() {
  userApi()
    .then(async (res) => {
      infoList.value = res.data || {};
      series.value = [
        {
          data: infoList.value.series || [],
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
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function getRank() {
  rankApi()
    .then(async (res) => {
      lists.value = res.data?.list || [];
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
    max-height: 320px;
    overflow-x: hidden;
    overflow-y: auto;
  }
}
.scollhide::-webkit-scrollbar {
  width: 4px;
}
.rank-list {
  padding: 8px 0 4px;
}
.rank-item {
  display: flex;
  align-items: center;
  padding: 10px 4px;
  border-bottom: 1px solid var(--prev-border-color-lighter, #f0f2f5);
  &:last-child {
    border-bottom: none;
  }
}
.rank-index {
  flex: none;
  width: 22px;
  height: 22px;
  margin-right: 10px;
  border-radius: 50%;
  background: var(--prev-bg-color-page, #f5f7fa);
  color: var(--prev-color-text-secondary, #909399);
  font-size: 12px;
  line-height: 22px;
  text-align: center;
  &.top {
    background: var(--prev-color-primary);
    color: #fff;
  }
}
.names {
  flex: 1;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: var(--prev-color-text-regular);
}
.rank-val {
  flex: none;
  margin-left: 12px;
  color: var(--prev-color-text-primary);
  font-weight: 500;
}
.card-title {
  display: flex;
  align-items: center;
  :deep(.el-avatar--small) {
    background-color: var(--prev-color-primary-light-9) !important;
  }
}
</style>
