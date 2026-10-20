<!--
  价格明细卡片组件（DIY 通用化契约版）
  展示商品总额、运费、优惠券、积分抵扣、会员折扣等多行金额，及实付款
  数据来源：section.props.items（金额明细数组），section._ctx.payPrice（实付金额）
-->
<template>
  <view class="info-card price-detail-card">
    <view class="card-title">{{ $t('价格信息') }}</view>
    <!-- 金额明细列表 -->
    <view
      class="info-row acea-row row-between"
      v-for="(item, idx) in items"
      :key="idx"
    >
      <text class="info-label">{{ item.label }}</text>
      <text
        class="info-value"
        :class="isDiscount(item.value) ? 'discount' : ''"
      >
        {{ formatMoney(item.value) }}
      </text>
    </view>
    <!-- 实付款 -->
    <view class="actualPay acea-row row-right">
      {{ $t('实付款') }}：
      <text class="money font-color">{{ formatMoney(payPrice) }}</text>
    </view>
  </view>
</template>

<script>
/**
 * 价格明细卡片组件
 * 展示多行金额明细和实付款总额
 * 正负号统一显示在货币符号前面，例如 +¥2.70、-¥1.00
 * 通过 componentKey='price_detail' 匹配 section.component
 */
export default {
  name: 'PriceDetail',
  // DIY 引擎 componentKey 标识
  componentKey: 'price_detail',
};
</script>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /** section 对象，含 props.items 和 _ctx.payPrice 全局状态 */
  section: { type: Object, required: true },
});

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 引擎注入的全局状态（含 payPrice 等订单级共享字段） */
const ctx = computed(() => (props.section && props.section._ctx) || {});
/** 金额明细数组（后端下发在 section.props.items），每项含 {label, value, type} */
const items = computed(() => p.value.items || []);
/** 实付金额（来自引擎全局状态 _ctx.payPrice，跨多个 section 共享） */
const payPrice = computed(() => ctx.value.payPrice || '0.00');

/**
 * 判断是否为折扣值（以"-"开头表示抵扣）
 * @param {string} val 金额值
 * @return {boolean} 是否折扣
 */
function isDiscount(val) {
  if (val === null || val === undefined) return false;
  return String(val).indexOf('-') === 0;
}

/**
 * 统一金额显示格式，避免出现 ¥+2.70 这类符号顺序错误
 * @param {string|number} val 金额值
 * @return {string} 带货币符号的金额
 */
function formatMoney(val) {
  const text = String(val === null || val === undefined || val === '' ? '0.00' : val);
  const sign = text[0] === '+' || text[0] === '-' ? text[0] : '';
  const amount = sign ? text.slice(1) : text;
  return `${sign}¥${amount}`;
}
</script>

<style scoped>
.price-detail-card { margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 30rpx; }
.card-title { font-size: 30rpx; font-weight: bold; color: #333; margin-bottom: 20rpx; }
.info-row { display: flex; justify-content: space-between; align-items: center; padding: 10rpx 0; font-size: 26rpx; }
.info-label { color: #999; }
.info-value { color: #333; }
.info-value.discount { color: var(--view-theme, #e93323); }
.actualPay { padding: 16rpx 0 0; font-size: 28rpx; color: #333; }
.actualPay .money { font-size: 32rpx; font-weight: bold; margin-left: 8rpx; }
.font-color { color: var(--view-theme, #e93323); }
</style>
