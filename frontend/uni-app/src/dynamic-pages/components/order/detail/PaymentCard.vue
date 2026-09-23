<!--
  支付详情卡片组件（DIY 通用化契约版）
  展示交易单号、支付时间、支付方式、支付金额
  数据来源：section.props.payment（支付详情对象）
  注意：复合 componentKey='info_list:payment'
-->
<template>
  <view class="info-card payment-card">
    <view class="card-title">{{ $t('支付详情') }}</view>
    <!-- 交易单号（第三方支付流水号） -->
    <view v-if="payment.trade_no" class="info-row acea-row row-between">
      <text class="info-label">{{ $t('交易单号') }}</text>
      <text class="info-value" selectable>{{ payment.trade_no }}</text>
    </view>
    <!-- 支付时间 -->
    <view v-if="payment.pay_time" class="info-row acea-row row-between">
      <text class="info-label">{{ $t('支付时间') }}</text>
      <text class="info-value">{{ formatTime(payment.pay_time) }}</text>
    </view>
    <!-- 支付方式（JSAPI/NATIVE/APP 等） -->
    <view v-if="payment.pay_method" class="info-row acea-row row-between">
      <text class="info-label">{{ $t('支付方式') }}</text>
      <text class="info-value">{{ payment.pay_method }}</text>
    </view>
    <!-- 支付金额 -->
    <view v-if="payment.pay_price" class="info-row acea-row row-between">
      <text class="info-label">{{ $t('支付金额') }}</text>
      <text class="info-value pay-amount">¥{{ payment.pay_price }}</text>
    </view>
  </view>
</template>

<script>
/**
 * 支付详情卡片组件
 * 展示第三方交易号、支付时间、支付方式、支付金额
 * 通过复合 componentKey='info_list:payment' 匹配 section.component
 */
export default {
  name: 'PaymentCard',
  // DIY 引擎 componentKey 标识（复合 key：组件类型:业务子类型）
  componentKey: 'info_list:payment',
};
</script>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /** section 对象，含 props.payment 和 _ctx 全局状态 */
  section: { type: Object, required: true },
});

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 引擎注入的全局状态（预留，当前 PaymentCard 不依赖全局状态） */
const ctx = computed(() => (props.section && props.section._ctx) || {});
/** 支付详情对象（后端下发在 section.props.fields），含 trade_no/pay_time/pay_method/pay_price */
const payment = computed(() => p.value.fields || {});

/**
 * 格式化时间戳为可读字符串
 * @param {number} ts 时间戳
 * @return {string} 格式化后的时间
 */
function formatTime(ts) {
  if (!ts) return '';
  var t = typeof ts === 'number' ? ts : parseInt(ts);
  if (!t) return '';
  var d = new Date(t * 1000);
  var pad = function (n) { return n < 10 ? '0' + n : '' + n; };
  return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
}
</script>

<style scoped>
.payment-card { margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 30rpx; }
.card-title { font-size: 30rpx; font-weight: bold; color: #333; margin-bottom: 20rpx; }
.info-row { display: flex; justify-content: space-between; align-items: center; padding: 12rpx 0; font-size: 26rpx; }
.info-label { color: #999; flex-shrink: 0; }
.info-value { color: #333; text-align: right; word-break: break-all; }
.pay-amount { color: var(--view-theme, #e93323); font-weight: 600; font-size: 28rpx; }
</style>
