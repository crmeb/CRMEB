<template>
  <div class="order-list-wrap">
    <!-- 订单时间线 -->
    <div class="timeline-section" v-if="timeline && timeline.length">
      <el-timeline>
        <el-timeline-item
          v-for="(item, index) in timeline"
          :key="index"
          :timestamp="item.time"
          :color="index === 0 ? '#409eff' : '#e4e7ed'"
        >
          <span class="tl-title">{{ item.title }}</span>
          <span class="tl-desc">{{ item.desc }}</span>
        </el-timeline-item>
      </el-timeline>
    </div>
  </div>
</template>

<script setup>
defineOptions({ name: 'DetailOrderList' });

defineProps({
  timeline: {
    type: Array,
    default: () => [],
  },
});
</script>

<style lang="scss" scoped>
.order-list-wrap {
  .timeline-section {
    padding: 16px 0;

    :deep(.el-timeline-item__content) {
      .tl-title {
        font-size: 13px;
        font-weight: 500;
        color: #303133;
      }
      .tl-desc {
        margin-left: 8px;
        font-size: 12px;
        color: #909399;
      }
    }
  }
}
</style>
