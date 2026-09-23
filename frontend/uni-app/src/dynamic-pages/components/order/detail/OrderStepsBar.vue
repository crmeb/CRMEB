<!--
  订单状态流程步骤条基元
  根据订单状态码和时间字段，展示订单从下单到完成的进度
-->
<template>
  <view class="steps-bar">
    <!-- 正常流程 -->
    <template v-if="steps.length > 0">
      <view
        v-for="(step, idx) in steps"
        :key="idx"
        class="step-item"
        :class="{ done: idx < currentIndex, active: idx === currentIndex }"
      >
        <view class="step-circle">
          <text v-if="idx < currentIndex" class="step-check">✓</text>
          <text v-else class="step-num">{{ idx + 1 }}</text>
        </view>
        <text class="step-label">{{ step.label }}</text>
        <text v-if="step.time" class="step-time">{{ step.time }}</text>
        <!-- 连接线 -->
        <view v-if="idx < steps.length - 1" class="step-line" :class="{ filled: idx < currentIndex }" />
      </view>
    </template>
    <!-- 异常状态（取消/退款） -->
    <view v-else class="abnormal-status">
      <view class="step-circle active">
        <text class="step-num">!</text>
      </view>
      <text class="step-label">{{ abnormalText }}</text>
    </view>
  </view>
</template>

<script setup>
import { computed } from 'vue';
import i18n from '@/utils/lang.js';

defineOptions({ name: 'PrimitiveOrderStepsBar' });

const props = defineProps({
  /** 订单状态码 */
  statusCode: { type: String, default: '' },
  /** 订单详情数据（含各阶段时间戳） */
  detail: { type: Object, default: () => ({}) },
});

// 步骤列表
const steps = computed(() => {
  if (isAbnormal.value) return [];

  const d = props.detail || {};
  const fmt = (ts) => {
    if (!ts) return '';
    const t = typeof ts === 'number' ? ts : parseInt(ts);
    if (!t) return '';
    const date = new Date(t * 1000);
    const pad = (n) => (n < 10 ? '0' + n : n);
    return pad(date.getMonth() + 1) + '-' + pad(date.getDate()) + ' ' + pad(date.getHours()) + ':' + pad(date.getMinutes());
  };

  // 定义正常流程节点，通过时间戳判断是否完成
  const nodes = [
    { key: 'add_time',      label: i18n.t('提交订单') },
    { key: 'pay_time',      label: i18n.t('支付成功') },
    { key: 'delivery_time', label: i18n.t('商品发货') },
    { key: 'take_time',     label: i18n.t('确认收货') },
  ];

  return nodes.map((node) => {
    const ts = d[node.key] ? (typeof d[node.key] === 'number' ? d[node.key] : parseInt(d[node.key])) : 0;
    return {
      label: node.label,
      time: ts > 0 ? fmt(ts) : '',
      done: ts > 0,
    };
  });
});
// 当前步骤索引
const currentIndex = computed(() => steps.value.findIndex((s, i) => !s.done));
// 是否为异常状态
const isAbnormal = computed(() => ['canceled', 'refunding', 'refunded'].includes(props.statusCode));
// 异常状态文案
const abnormalText = computed(() => {
  const map = {
    canceled: i18n.t('订单已取消'),
    refunding: i18n.t('退款中'),
    refunded: i18n.t('已退款'),
  };
  return map[props.statusCode] || i18n.t('异常状态');
});
</script>

<style scoped>
.steps-bar { display: flex; align-items: flex-start; padding: 30rpx 20rpx; }
.step-item { display: flex; flex-direction: column; align-items: center; flex: 1; position: relative; }
.step-circle { width: 48rpx; height: 48rpx; border-radius: 50%; background: #e0e0e0; display: flex; align-items: center; justify-content: center; z-index: 2; transition: all 0.3s; }
.step-item.done .step-circle { background: #07c160; }
.step-item.active .step-circle { background: var(--view-theme, #e93323); box-shadow: 0 0 0 8rpx var(--view-op-ten); }
.step-check { font-size: 28rpx; color: #fff; }
.step-num { font-size: 24rpx; color: #fff; }
.step-label { font-size: 22rpx; color: #666; margin-top: 12rpx; text-align: center; }
.step-item.active .step-label { color: var(--view-theme, #e93323); font-weight: bold; }
.step-item.done .step-label { color: #07c160; }
.step-time { font-size: 20rpx; color: #999; margin-top: 4rpx; }
.step-line { position: absolute; top: 24rpx; left: 50%; width: 100%; height: 4rpx; background: #e0e0e0; z-index: 1; }
.step-line.filled { background: #07c160; }
.abnormal-status { display: flex; flex-direction: column; align-items: center; padding: 20rpx 0; width: 100%; }
.abnormal-status .step-circle { width: 48rpx; height: 48rpx; border-radius: 50%; background: #ff9900; display: flex; align-items: center; justify-content: center; }
.abnormal-status .step-label { font-size: 28rpx; color: #ff9800; margin-top: 12rpx; font-weight: bold; }
</style>
