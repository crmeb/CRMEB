<!--
  合同/协议入口组件
  展示协议标题，点击查看协议内容
  数据来源：section.props.title / section.props.url
-->
<template>
  <view class="action-card contract-entry" @click="$emit('click', url)">
    <text class="iconfont icon-xieyi"></text>
    <text class="action-title">{{ title || $t('查看协议') }}</text>
    <text class="iconfont icon-xiangyou action-arrow"></text>
  </view>
</template>

<script>
/**
 * 合同/协议入口组件
 * 点击 emit('click', url) 由引擎打开协议页面
 * 通过 componentKey='contract_entry' 匹配 section.component
 */
export default {
  name: 'ContractEntry',
  // 匹配 section.component='contract_entry'
  componentKey: 'contract_entry',
};
</script>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /** section 对象，含 props.title / props.url 和 _ctx 全局状态 */
  section: { type: Object, required: true },
});

defineEmits(['click']);

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 引擎注入的全局状态（预留） */
const ctx = computed(() => (props.section && props.section._ctx) || {});
/** 协议标题 */
const title = computed(() => p.value.title || '');
/** 协议链接 */
const url = computed(() => p.value.url || '');
</script>

<style scoped>
/* 合同入口专属样式 */
.contract-entry { display: flex; align-items: center; margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 24rpx; }
.iconfont { font-size: 36rpx; color: var(--view-theme, #e93323); margin-right: 16rpx; }
.action-title { flex: 1; font-size: 28rpx; color: #333; }
.action-arrow { font-size: 24rpx; color: #ccc; margin-right: 0; }
</style>
