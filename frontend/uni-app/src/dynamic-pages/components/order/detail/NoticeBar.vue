<!--
  提示条组件（DIY 通用化版）
  展示运营提示信息（如发货提醒、签收提醒等），支持多条提示
  数据来源：section.props.notices
-->
<template>
  <view class="notice-bar" v-if="notices && notices.length > 0">
    <view
      v-for="(notice, idx) in notices"
      :key="idx"
      :class="['notice-item', 'notice-' + (notice.type || 'info')]"
    >
      <text v-if="notice.type === 'warning'" class="iconfont icon-zhuyi-copy"></text>
      <text>{{ notice.text }}</text>
    </view>
  </view>
</template>

<script>
/**
 * 提示条组件
 * 展示运营提示信息，支持 info/warning/success 三种类型
 */
export default {
  name: 'NoticeBar',
  // DIY 通用化契约：组件唯一标识
  componentKey: 'notice_bar',
};
</script>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /** section 对象，含 props.notices */
  section: { type: Object, required: true },
});

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 引擎注入的全局状态（预留，当前组件不依赖） */
const ctx = computed(() => (props.section && props.section._ctx) || {});
/** 提示数组，每项含 {type: 'info'|'warning'|'success', text} */
const notices = computed(() => p.value.notices || []);
</script>

<style scoped>
.notice-bar { margin: 20rpx; }
.notice-item { display: flex; align-items: center; padding: 16rpx 24rpx; border-radius: 12rpx; margin-bottom: 10rpx; font-size: 26rpx; }
.notice-item:last-child { margin-bottom: 0; }
.notice-info { background: #e8f3ff; color: #1989fa; }
.notice-warning { background: #fff4e5; color: #ff976a; }
.notice-success { background: #e8f8ee; color: #07c160; }
.iconfont { margin-right: 8rpx; }
</style>
