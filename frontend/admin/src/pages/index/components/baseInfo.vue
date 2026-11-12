<template>
  <el-row :gutter="16" class="base-info">
    <el-col v-bind="grid" class="base-info__col" v-for="(item, index) in infoList" :key="index">
      <el-card shadow="never" class="stat-card">
        <div class="stat-head acea-row row-between-wrapper">
          <span class="stat-title">{{ item.title }}</span>
          <span class="stat-date">{{ item.date }}</span>
        </div>
        <div class="stat-value">{{ item.today }}</div>
        <div class="stat-meta acea-row row-middle">
          <span>昨日 {{ item.yesterday }}</span>
          <span class="stat-ratio" :class="Number(item.today_ratio) >= 0 ? 'is-up' : 'is-down'">
            日环比 {{ Number(item.today_ratio) }}%
            <el-icon>
              <CaretTop v-if="Number(item.today_ratio) >= 0" />
              <CaretBottom v-else />
            </el-icon>
          </span>
        </div>
        <div class="stat-foot acea-row row-between-wrapper">
          <span>{{ item.total_name }}</span>
          <span class="stat-total">{{ item.total }}</span>
        </div>
      </el-card>
    </el-col>
  </el-row>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { ElMessage } from 'element-plus';
import { headerApi } from '@/api/index';

defineOptions({ name: 'baseInfo' });

const infoList = ref([]);
const grid = {
  xl: 6,
  lg: 6,
  md: 12,
  sm: 12,
  xs: 12,
};

function getStatistics() {
  headerApi()
    .then((res) => {
      infoList.value = res.data?.info || [];
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

onMounted(() => {
  getStatistics();
});
</script>

<style lang="scss" scoped>
.base-info__col {
  margin-bottom: 16px;
}

.stat-card {
  height: 100%;

  :deep(.el-card__body) {
    padding: 16px 20px;
  }
}

.stat-head {
  margin-bottom: 8px;
}

.stat-title {
  font-size: 14px;
  font-weight: 500;
  color: var(--prev-color-text-regular);
}

.stat-date {
  font-size: 12px;
  color: var(--prev-color-text-secondary);
}

.stat-value {
  font-size: 28px;
  font-weight: 500;
  line-height: 36px;
  color: var(--prev-color-text-regular);
}

.stat-meta {
  margin-top: 8px;
  font-size: 12px;
  color: var(--prev-color-text-secondary);
  gap: 16px;
}

.stat-ratio {
  display: inline-flex;
  align-items: center;
  gap: 2px;

  &.is-up {
    color: var(--el-color-success);
  }

  &.is-down {
    color: var(--el-color-danger);
  }
}

.stat-foot {
  margin-top: 14px;
  padding-top: 12px;
  border-top: 1px solid var(--prev-border-color-extra-light);
  font-size: 12px;
  color: var(--prev-color-text-secondary);
}

.stat-total {
  font-size: 14px;
  font-weight: 500;
  color: var(--prev-color-text-regular);
}
</style>
