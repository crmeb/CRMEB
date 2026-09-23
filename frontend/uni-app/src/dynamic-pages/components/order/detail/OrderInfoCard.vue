<!--
  订单信息卡片组件（DIY 通用化契约版）
  展示订单编号（可复制）、下单时间、支付时间、支付状态、支付方式、买家备注、商家备注
  数据来源：section.props.fields（订单字段对象），section._ctx.payStatus（支付状态）
  注意：复合 componentKey='info_list:order_info'
-->
<template>
  <view class="info-card order-info-card">
    <view class="card-title">{{ $t('订单信息') }}</view>
    <!-- 订单编号 + 复制按钮 -->
    <view class="info-row acea-row row-between">
      <text class="info-label">{{ $t('订单编号') }}</text>
      <view class="info-value acea-row row-middle row-right">
        <text>{{ fields.order_no || '' }}</text>
        <text class="copy" @tap="onCopy">{{ $t('复制') }}</text>
      </view>
    </view>
    <!-- 下单时间 -->
    <view class="info-row acea-row row-between">
      <text class="info-label">{{ $t('下单时间') }}</text>
      <text class="info-value">{{ formatTime(fields.add_time) }}</text>
    </view>
    <!-- 支付时间（已支付才显示） -->
    <view v-if="fields.pay_time" class="info-row acea-row row-between">
      <text class="info-label">{{ $t('支付时间') }}</text>
      <text class="info-value">{{ formatTime(fields.pay_time) }}</text>
    </view>
    <!-- 支付状态 -->
    <view class="info-row acea-row row-between">
      <text class="info-label">{{ $t('支付状态') }}</text>
      <text class="info-value">{{ payStatus === 1 ? $t('已支付') : $t('未支付') }}</text>
    </view>
    <!-- 支付方式 -->
    <view v-if="fields.pay_type_name" class="info-row acea-row row-between">
      <text class="info-label">{{ $t('支付方式') }}</text>
      <text class="info-value">{{ fields.pay_type_name }}</text>
    </view>
    <!-- 买家备注 -->
    <view v-if="fields.mark" class="info-row acea-row row-between">
      <text class="info-label">{{ $t('买家备注') }}</text>
      <text class="info-value">{{ fields.mark }}</text>
    </view>
    <!-- 商家备注 -->
    <view v-if="fields.remark" class="info-row acea-row row-between">
      <text class="info-label">{{ $t('商家备注') }}</text>
      <text class="info-value">{{ fields.remark }}</text>
    </view>
  </view>
</template>

<script>
/**
 * 订单信息卡片组件
 * 展示订单编号、时间、支付状态、备注等基本信息
 * 通过复合 componentKey='info_list:order_info' 匹配 section.component
 */
export default {
  name: 'OrderInfoCard',
  // DIY 引擎 componentKey 标识（复合 key：组件类型:业务子类型）
  componentKey: 'info_list:order_info',
};
</script>

<script setup>
import { computed } from 'vue';
import i18n from '@/utils/lang.js';

const props = defineProps({
  /** section 对象，含 props.fields 和 _ctx.payStatus 全局状态 */
  section: { type: Object, required: true },
});

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 引擎注入的全局状态（含 payStatus 等订单级共享字段） */
const ctx = computed(() => (props.section && props.section._ctx) || {});
/** 订单信息对象（后端下发在 section.props.fields），含 order_no/add_time/pay_time/pay_type_name/mark/remark */
const fields = computed(() => p.value.fields || {});
/** 支付状态（来自引擎全局状态 _ctx.payStatus）：0未支付 1已支付 */
const payStatus = computed(() => ctx.value.payStatus || 0);

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

/** 复制订单号到剪贴板 */
function onCopy() {
  var orderNo = fields.value.order_no || '';
  if (!orderNo) return;
  // #ifdef H5
  if (typeof navigator !== 'undefined' && navigator.clipboard) {
    navigator.clipboard.writeText(orderNo);
  }
  // #endif
  // #ifndef H5
  uni.setClipboardData({
    data: orderNo,
    success: () => { uni.showToast({ title: i18n.t('已复制'), icon: 'none' }); },
  });
  // #endif
}
</script>

<style scoped>
.order-info-card { margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 30rpx; }
.card-title { font-size: 30rpx; font-weight: bold; color: #333; margin-bottom: 20rpx; }
.info-row { display: flex; justify-content: space-between; align-items: center; padding: 12rpx 0; font-size: 26rpx; }
.info-label { color: #999; flex-shrink: 0; }
.info-value { color: #333; text-align: right; }
.copy { margin-left: 12rpx; padding: 2rpx 16rpx; font-size: 22rpx; color: var(--view-theme, #e93323); border: 1rpx solid var(--view-theme, #e93323); border-radius: 6rpx; }
</style>
