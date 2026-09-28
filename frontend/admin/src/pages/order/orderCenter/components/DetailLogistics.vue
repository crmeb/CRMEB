<template>
  <el-drawer
    :model-value="visible"
    @update:model-value="(val) => $emit('update:visible', val)"
    @close="$emit('update:visible', false)"
    title="物流查询"
    size="420px"
    :wrapper-closable="true"
    class="order_box2"
  >
    <!-- 快递单号信息 -->
    <div class="express-header" v-if="data">
      <img class="express-icon" src="../../../../assets/images/expressi.jpg" />
      <div class="express-info">
        <div class="express-company">{{ data.delivery_name }}</div>
        <div class="express-num">
          <span class="label">运单号</span>
          <span class="value">{{ data.delivery_id }}</span>
          <i class="el-icon-document-copy copy-btn" title="复制" @click="copyNum"></i>
        </div>
      </div>
    </div>

    <!-- 物流轨迹 -->
    <div class="express-timeline">
      <el-timeline v-if="logisticsList.length">
        <el-timeline-item v-for="(item, i) in logisticsList" :key="i" :color="i === 0 ? '#2d8cf0' : '#c0c4cc'">
          <div class="track-item">
            <div class="track-status" :class="{ latest: i === 0 }">{{ item.status }}</div>
            <div class="track-time">{{ item.time }}</div>
          </div>
        </el-timeline-item>
      </el-timeline>
      <div v-else class="empty-logistics">暂无物流信息</div>
    </div>
  </el-drawer>
</template>

<script setup>
import { ElMessage } from 'element-plus';

defineOptions({ name: 'DetailLogistics' });

const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  data: {
    type: Object,
    default: () => ({}),
  },
  logisticsList: {
    type: Array,
    default: () => [],
  },
});

defineEmits(['update:visible']);

/**
 * 复制运单号
 */
function copyNum() {
  const trackingNum = (props.data && props.data.delivery_id) || '';
  if (!trackingNum) return;
  const input = document.createElement('input');
  input.value = trackingNum;
  document.body.appendChild(input);
  input.select();
  document.execCommand('copy');
  document.body.removeChild(input);
  ElMessage.success('已复制');
}
</script>

<style lang="scss" scoped>
/* 快递单号信息区域 */
.express-header {
  display: flex;
  align-items: center;
  padding: 20px 24px;
  background: var(--prev-bg-color);
  border-bottom: 1px solid var(--prev-border-color-lighter);

  .express-icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    margin-right: 16px;
    flex-shrink: 0;
  }

  .express-info {
    flex: 1;
    min-width: 0;
  }

  .express-company {
    font-size: 15px;
    font-weight: 600;
    color: var(--prev-color-text-regular);
    margin-bottom: 6px;
  }

  .express-num {
    display: flex;
    align-items: center;
    font-size: 13px;

    .label {
      color: var(--prev-color-text-secondary);
      margin-right: 8px;
      flex-shrink: 0;
    }

    .value {
      color: var(--prev-color-text-primary);
      word-break: break-all;
    }

    .copy-btn {
      color: var(--prev-color-text-secondary);
      cursor: pointer;
      margin-left: 8px;
      font-size: 14px;
      flex-shrink: 0;

      &:hover {
        color: #2d8cf0;
      }
    }
  }
}

/* 物流轨迹区域 */
.express-timeline {
  flex: 1;
  overflow-y: auto;
  padding: 20px 24px 20px 12px;

  :deep(.el-timeline-item) {
    padding-bottom: 24px;

    &:last-child {
      padding-bottom: 0;
    }
  }

  :deep(.el-timeline-item__node) {
    width: 14px;
    height: 14px;
  }

  :deep(.el-timeline-item__tail) {
    left: 6px;
  }

  :deep(.el-timeline-item__wrapper) {
    padding-left: 28px;
  }
}

.track-item {
  .track-status {
    font-size: 14px;
    color: var(--prev-color-text-primary);
    line-height: 1.6;
    margin-bottom: 4px;

    &.latest {
      color: #2d8cf0;
      font-weight: 500;
    }
  }

  .track-time {
    font-size: 12px;
    color: #c0c4cc;
  }
}

.empty-logistics {
  text-align: center;
  color: var(--prev-color-text-secondary);
  padding: 60px 0;
  font-size: 14px;
}
</style>

<style lang="scss">
/* 抽屉壳层覆盖（非 scoped）：抽屉根类自锚的 scoped :deep 在组件作用域外无法命中；
   body 满高 flex 布局供底部滚动区使用。原 .el-drawer__wrapper z-index 覆盖为
   iView 遗留死规则（选择器结构写反且 EP 层级由弹层管理），不再保留 */
.order_box2 .el-drawer__body {
  padding: 0;
  display: flex;
  flex-direction: column;
  height: 100%;
}
</style>
