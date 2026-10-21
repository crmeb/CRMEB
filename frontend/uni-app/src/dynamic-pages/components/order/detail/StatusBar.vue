<!--
  状态头部组件（DIY 通用化版）
  样式对齐旧版订单详情 header：主题色通栏 + 状态图标 + 粗体状态文案 + 副文案/下单时间
  数据来源：section.props（text/icon/subtitle/add_time） + section._ctx（isRefund）
-->
<template>
  <view class="header bg-color" :class="isRefund ? 'on' : ''">
    <view v-if="icon" class="pictrue" :class="isRefund ? 'on' : ''">
      <text class="iconfont" :class="icon"></text>
    </view>
    <view class="data">
      <view class="state">{{ text || '' }}</view>
      <view v-if="subText" class="sub">{{ subText }}</view>
    </view>
  </view>
</template>

<script>
/**
 * 状态头部组件
 * 展示订单当前状态图标、状态文案与副文案（业务状态/下单时间）
 * 退款/售后类状态使用深灰背景样式（对齐旧版 header.on）
 */
export default {
  name: 'StatusBar',
  // DIY 通用化契约：组件唯一标识
  componentKey: 'status_bar',
};
</script>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /** section 对象，含 props.text/icon/subtitle/add_time 与 _ctx.isRefund */
  section: { type: Object, required: true },
});

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 引擎注入的全局状态（含 isRefund 退款状态标识） */
const ctx = computed(() => (props.section && props.section._ctx) || {});
/** 状态文案，如"进行中"、"待支付" */
const text = computed(() => p.value.text || '');
/** 状态图标 class（iconfont），如 icon-daifukuan */
const icon = computed(() => p.value.icon || '');
/** 业务状态副文案，如"商家已发货" */
const subtitle = computed(() => p.value.subtitle || '');
/** 下单时间戳（后端字段为 add_time，兼容旧驼峰 addTime） */
const addTime = computed(() => p.value.add_time || p.value.addTime || 0);
/** 是否退款类状态（影响样式，来自全局 ctx） */
const isRefund = computed(() => !!ctx.value.isRefund);

/** 状态下方副行：业务状态优先，其次显示下单时间 */
const subText = computed(() => {
  if (subtitle.value) return subtitle.value;
  var t = addTime.value;
  if (!t) return '';
  return formatTime(typeof t === 'number' ? t : parseInt(t));
});

/**
 * 格式化时间戳为可读字符串
 * @param {number} ts 时间戳（秒）
 * @return {string} 格式化后的时间
 */
function formatTime(ts) {
  if (!ts) return '';
  var d = new Date(ts * 1000);
  var pad = function (n) { return n < 10 ? '0' + n : '' + n; };
  return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
}
</script>

<style scoped>
/* 对齐旧版 .order-details .header：通栏主题色、150rpx 高、内容垂直居中 */
.header {
  display: flex;
  align-items: center;
  flex-wrap: nowrap;
  height: 150rpx;
  padding: 0 30rpx;
}
/* 退款/售后态：深灰背景（旧版 header.on） */
.header.on { background: #666 !important; }

/* 状态图标：旧版此处为状态图，现由后端下发 iconfont 图标，置于半透明白色圆底上 */
.pictrue {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 88rpx;
  height: 88rpx;
  margin-right: 24rpx;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.18);
}
.pictrue .iconfont { font-size: 48rpx; color: #fff; }
.pictrue.on { background: rgba(255, 255, 255, 0.12); }

.data { color: #fff; min-width: 0; }
.state {
  font-size: 34rpx;
  font-weight: bold;
  color: #fff;
  margin-bottom: 8rpx;
}
.sub { font-size: 24rpx; color: rgba(255, 255, 255, 0.8); }
.bg-color { background: var(--view-theme, #e93323); }
</style>
