<!--
  行动入口卡片组件
  抽奖/领券/促销/分享/客服等运营入口，支持图片和图标，点击跳转
  数据来源：section.props.title / image / icon / action / link
-->
<template>
  <view class="action-card" @click="$emit('click', { action: action, link: link })">
    <!-- 图片优先于图标 -->
    <image v-if="image" :src="image" mode="aspectFill" class="action-image" />
    <text v-else-if="icon" :class="['iconfont', icon]"></text>
    <text class="action-title">{{ title }}</text>
    <text class="iconfont icon-xiangyou action-arrow"></text>
  </view>
</template>

<script>
/**
 * 行动入口卡片组件
 * 用于抽奖入口、领券入口、促销引导、分享入口、客服联系等
 * 点击后 emit('click', {action, link}) 由引擎统一跳转
 * 通过 componentKey='action_card' 匹配 section.component
 */
export default {
  name: 'ActionCard',
  // 匹配 section.component='action_card'
  componentKey: 'action_card',
};
</script>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /** section 对象，含 props.title/image/icon/action/link 和 _ctx 全局状态 */
  section: { type: Object, required: true },
});

defineEmits(['click']);

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 引擎注入的全局状态（预留） */
const ctx = computed(() => (props.section && props.section._ctx) || {});
/** 标题文案 */
const title = computed(() => p.value.title || '');
/** 图片地址（优先于 icon） */
const image = computed(() => p.value.image || '');
/** 图标 class（无 image 时使用） */
const icon = computed(() => p.value.icon || '');
/** 行动标识（如 goGoodCall/lottery/coupon 等） */
const action = computed(() => p.value.action || '');
/** 跳转链接 */
const link = computed(() => p.value.link || '');
</script>

<style scoped>
/* 行动入口专属样式 */
.action-card { display: flex; align-items: center; margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 24rpx; }
.action-image { width: 60rpx; height: 60rpx; border-radius: 8rpx; margin-right: 16rpx; flex-shrink: 0; }
.iconfont { font-size: 36rpx; color: var(--view-theme, #e93323); margin-right: 16rpx; }
.action-title { flex: 1; font-size: 28rpx; color: #333; }
.action-arrow { font-size: 24rpx; color: #ccc; margin-right: 0; }
</style>
