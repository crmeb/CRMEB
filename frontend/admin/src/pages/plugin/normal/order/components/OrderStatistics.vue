<template>
  <el-row :gutter="20">
    <el-col :span="6">
      <el-card :bordered="false" shadow="never" class="stat-card">
        <div class="stat-item">
          <div class="stat-icon stat-icon-total">
            <i class="el-icon-s-order"></i>
          </div>
          <div class="stat-info">
            <div class="stat-value">{{ (data.all && data.all.count) || 0 }}</div>
            <div class="stat-label">订单总数</div>
          </div>
        </div>
      </el-card>
    </el-col>
    <el-col :span="6">
      <el-card :bordered="false" shadow="never" class="stat-card">
        <div class="stat-item">
          <div class="stat-icon stat-icon-pay">
            <i class="el-icon-yuan"></i>
          </div>
          <div class="stat-info">
            <div class="stat-value">¥{{ (data.all && data.all.amount) || '0.00' }}</div>
            <div class="stat-label">总金额</div>
          </div>
        </div>
      </el-card>
    </el-col>
    <el-col :span="6">
      <el-card :bordered="false" shadow="never" class="stat-card">
        <div class="stat-item">
          <div class="stat-icon stat-icon-pending">
            <i class="el-icon-clock"></i>
          </div>
          <div class="stat-info">
            <div class="stat-value">
              {{ (statusCounts && statusCounts.pending_payment && statusCounts.pending_payment.count) || 0 }}
            </div>
            <div class="stat-label">待支付</div>
          </div>
        </div>
      </el-card>
    </el-col>
    <el-col :span="6">
      <el-card :bordered="false" shadow="never" class="stat-card">
        <div class="stat-item">
          <div class="stat-icon stat-icon-completed">
            <i class="el-icon-check-circle"></i>
          </div>
          <div class="stat-info">
            <div class="stat-value">
              {{ (statusCounts && statusCounts.completed && statusCounts.completed.count) || 0 }}
            </div>
            <div class="stat-label">已完成</div>
          </div>
        </div>
      </el-card>
    </el-col>
  </el-row>
</template>

<script setup>
/**
 * 订单统计卡片组件
 * 展示订单总数、总金额、待支付、已完成等统计数据
 */
import { computed } from 'vue';

defineOptions({ name: 'OrderStatistics' });

const props = defineProps({
  /** 统计数据对象 */
  data: {
    type: Object,
    default: () => ({}),
  },
});

/** 各状态统计 */
const statusCounts = computed(() => props.data.status_counts || {});
</script>

<style lang="scss" scoped>
.stat-card {
  .stat-item {
    display: flex;
    align-items: center;
  }

  .stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-right: 15px;

    &-total {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: #fff;
    }

    &-pay {
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      color: #fff;
    }

    &-pending {
      background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      color: #fff;
    }

    &-completed {
      background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
      color: #fff;
    }
  }

  .stat-info {
    .stat-value {
      font-size: 24px;
      font-weight: bold;
      color: #333;
    }

    .stat-label {
      font-size: 14px;
      color: #999;
      margin-top: 5px;
    }
  }
}
</style>
