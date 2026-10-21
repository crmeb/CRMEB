<!--
  发票信息卡片组件（DIY 通用化契约版）
  展示发票抬头、类型、税号、邮箱、开票状态、发票号码、开票时间
  已开票时额外展示发票号码和开票时间
  数据来源：section.props.invoice（发票信息对象）
  注意：复合 componentKey='info_list:invoice'
-->
<template>
  <view class="info-card invoice-card">
    <view class="card-title">{{ $t('发票信息') }}</view>
    <!-- 发票抬头 + 企业/个人标签 -->
    <view class="info-row acea-row row-between">
      <text class="info-label">{{ $t('发票抬头') }}</text>
      <view class="info-value acea-row row-middle row-right">
        <text>{{ invoice.title || '-' }}</text>
        <text class="invoice-tag">{{ invoice.header_type === 2 ? $t('企业') : $t('个人') }}</text>
      </view>
    </view>
    <!-- 发票类型：电子/纸质 -->
    <view class="info-row acea-row row-between">
      <text class="info-label">{{ $t('发票类型') }}</text>
      <text class="info-value">{{ invoice.type === 2 ? $t('纸质发票') : $t('电子发票') }}</text>
    </view>
    <!-- 税号（企业抬头才有） -->
    <view v-if="invoice.tax_number" class="info-row acea-row row-between">
      <text class="info-label">{{ $t('税号') }}</text>
      <text class="info-value">{{ invoice.tax_number }}</text>
    </view>
    <!-- 邮箱 -->
    <view v-if="invoice.email" class="info-row acea-row row-between">
      <text class="info-label">{{ $t('邮箱') }}</text>
      <text class="info-value">{{ invoice.email }}</text>
    </view>
    <!-- 开票状态：已开票(绿)/未开票(橙) -->
    <view class="info-row acea-row row-between">
      <text class="info-label">{{ $t('开票状态') }}</text>
      <text class="info-value" :class="invoice.is_invoice === 1 ? 'invoice-done' : 'invoice-pending'">
        {{ invoice.is_invoice === 1 ? $t('已开票') : $t('未开票') }}
      </text>
    </view>
    <!-- 发票号码（已开票才有） -->
    <view v-if="invoice.is_invoice === 1 && invoice.invoice_number" class="info-row acea-row row-between">
      <text class="info-label">{{ $t('发票号码') }}</text>
      <text class="info-value" selectable>{{ invoice.invoice_number }}</text>
    </view>
    <!-- 开票时间（已开票才有） -->
    <view v-if="invoice.is_invoice === 1 && invoice.invoice_time" class="info-row acea-row row-between">
      <text class="info-label">{{ $t('开票时间') }}</text>
      <text class="info-value">{{ formatTime(invoice.invoice_time) }}</text>
    </view>
  </view>
</template>

<script>
/**
 * 发票信息卡片组件
 * 展示完整发票信息，含开票状态
 * 通过复合 componentKey='info_list:invoice' 匹配 section.component
 */
export default {
  name: 'InvoiceCard',
  // DIY 引擎 componentKey 标识（复合 key：组件类型:业务子类型）
  componentKey: 'info_list:invoice',
};
</script>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /** section 对象，含 props.invoice 和 _ctx 全局状态 */
  section: { type: Object, required: true },
});

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 引擎注入的全局状态（预留，当前 InvoiceCard 不依赖全局状态） */
const ctx = computed(() => (props.section && props.section._ctx) || {});
/**
 * 发票信息对象（后端下发在 section.props.fields）。
 * 兼容历史订单仍返回的 name/duty_number 字段，确保抬头和税号始终可见。
 */
const invoice = computed(() => {
  const fields = p.value.fields || {};
  return {
    ...fields,
    title: fields.title || fields.name || '',
    tax_number: fields.tax_number || fields.duty_number || '',
    header_type: Number(fields.header_type || 1),
    type: Number(fields.type || 1),
    is_invoice: Number(fields.is_invoice || 0),
  };
});

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
.invoice-card { margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 30rpx; }
.card-title { font-size: 30rpx; font-weight: bold; color: #333; margin-bottom: 20rpx; }
.info-row { display: flex; justify-content: space-between; align-items: center; padding: 12rpx 0; font-size: 26rpx; }
.info-label { color: #999; flex-shrink: 0; }
.info-value { color: #333; text-align: right; }
.invoice-tag { margin-left: 12rpx; padding: 2rpx 12rpx; font-size: 22rpx; color: #fff; background: var(--view-theme, #e93323); border-radius: 6rpx; }
.invoice-done { color: #07c160; font-weight: 500; }
.invoice-pending { color: #ff976a; }
</style>
