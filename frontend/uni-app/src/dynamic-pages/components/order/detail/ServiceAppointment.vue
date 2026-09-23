<!--
  上门服务预约信息组件
  展示服务地址、联系人、预约时间、服务人员
  数据来源：section.props.service（含 address/contact_name/contact_phone/appointment_time/service_staff）
-->
<template>
  <view class="info-card service-card" v-if="service">
    <view class="card-title">{{ $t('上门服务') }}</view>
    <view class="info-row acea-row row-between">
      <text class="info-label">{{ $t('服务地址') }}</text>
      <text class="info-value">{{ service.address || '-' }}</text>
    </view>
    <view class="info-row acea-row row-between">
      <text class="info-label">{{ $t('联系人') }}</text>
      <text class="info-value">{{ contactText }}</text>
    </view>
    <view v-if="service.appointment_time" class="info-row acea-row row-between">
      <text class="info-label">{{ $t('预约时间') }}</text>
      <text class="info-value">{{ service.appointment_time }}</text>
    </view>
    <view v-if="service.service_staff" class="info-row acea-row row-between">
      <text class="info-label">{{ $t('服务人员') }}</text>
      <text class="info-value">{{ staffText }}</text>
    </view>
  </view>
</template>

<script>
/**
 * 上门服务预约信息组件
 * 展示上门服务订单的预约信息和服务人员
 * 通过 componentKey='service_appointment' 匹配 section.component
 */
export default {
  name: 'ServiceAppointment',
  // 匹配 section.component='service_appointment'
  componentKey: 'service_appointment',
};
</script>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /** section 对象，含 props.service 和 _ctx 全局状态 */
  section: { type: Object, required: true },
});

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 引擎注入的全局状态（预留） */
const ctx = computed(() => (props.section && props.section._ctx) || {});
/** 服务信息对象 */
const service = computed(() => p.value.service || null);
/** 联系人文本：姓名 + 电话 */
const contactText = computed(() => {
  if (!service.value) return '';
  return [service.value.contact_name, service.value.contact_phone].filter(function (v) { return v; }).join(' ');
});
/** 服务人员文本：姓名 + 电话 */
const staffText = computed(() => {
  var staff = service.value && service.value.service_staff;
  if (!staff) return '';
  return [staff.name, staff.phone].filter(function (v) { return v; }).join(' ');
});
</script>

<style scoped>
/* 统一卡片样式（与引擎其他组件一致） */
.info-card { margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 30rpx; }
.card-title { font-size: 30rpx; font-weight: bold; color: #333; margin-bottom: 20rpx; }
.info-row { display: flex; justify-content: space-between; align-items: center; padding: 12rpx 0; font-size: 26rpx; }
.info-label { color: #999; flex-shrink: 0; }
.info-value { color: #333; text-align: right; }

/* 上门服务专属样式 */
.service-card { margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 30rpx; }
</style>
