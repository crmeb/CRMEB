<template>
  <div class="i-layout-page-header header-title">
    <div class="header-main">
      <div v-if="backUrl" class="font-sm after-line" v-db-click @click="goBack">
        <span class="el-icon-arrow-left"></span>
        <span class="pl10">返回</span>
      </div>
      <span class="ivu-page-header-title" :class="{ ml10: backUrl }">{{ title }}</span>
      <!-- 描述与标题同一行、基线对齐，紧跟标题之后；描述前置主色 info 图标 -->
      <span v-if="description" class="header-description">
        <el-icon class="header-description-icon"><InfoFilled /></el-icon>
        {{ description }}
      </span>
    </div>
    <slot></slot>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';

const props = defineProps({
  title: {
    type: String,
    default: '',
  },
  backUrl: {
    type: String,
    default: '',
  },
  description: {
    type: String,
    default: '',
  },
});

const router = useRouter();

function goBack() {
  if (props.backUrl) {
    router.push({
      path: props.backUrl,
    });
  } else {
    router.back();
  }
}
</script>

<style lang="scss" scoped>
.header-title {
  justify-content: space-between;
}
/* 标题行：返回钮 + 标题 + 描述，基线对齐（描述字号小于标题，用 baseline 不用 flex-end） */
.header-main {
  display: flex;
  align-items: baseline;
  min-width: 0;
  gap: 16px;
}
.header-description {
  color: var(--prev-color-text-secondary);
  font-size: 12px;
  line-height: 18px;
}
.header-description-icon {
  font-size: 14px;
  color: var(--prev-color-primary);
  vertical-align: -2px;
  margin-right: 2px;
}
.after-line {
  display: inline-block;
  position: relative;
  margin-right: 16px;
  color: rgba(0, 0, 0, 0.85);
  cursor: pointer;
  align-self: center;
}
.after-line:after {
  content: '';
  position: absolute;
  top: 0;
  right: -16px;
  width: 1px;
  height: 16px;
  background: #eee;
}
.font-sm {
  font-size: 14px;
}
</style>
