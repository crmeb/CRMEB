<!--
  配送方式+收货地址合并组件
  由 express_shipping / store_pickup / door_service 功能开关驱动：
  - 都关闭：不渲染任何内容（虚拟商品等场景）
  - 任一开启：显示配送方式 tab 栏（按可用项渲染）
  - tab 栏下方显示对应内容（快递地址/门店信息/上门服务预约表单）
-->
<template>
  <view v-if="expressShipping || storePickup || doorService">
    <!-- 配送方式切换 -->
    <view class="delivery-section">
      <view
        class="delivery-tab"
        :class="{ active: modelValue === 'delivery' }"
        v-if="expressShipping"
        @click="onTabChange('delivery')"
      >{{ $t('快递配送') }}</view>
      <view
        class="delivery-tab"
        :class="{ active: modelValue === 'pickup' }"
        v-if="storePickup"
        @click="onTabChange('pickup')"
      >{{ $t('到店自提') }}</view>
      <view
        class="delivery-tab"
        :class="{ active: modelValue === 'door' }"
        v-if="doorService"
        @click="onTabChange('door')"
      >{{ $t('上门服务') }}</view>
    </view>

    <!-- 快递配送 - 地址卡片 -->
    <view
      class="address-section"
      v-if="modelValue === 'delivery'"
      @click="$emit('address-click')"
    >
      <view class="address-info" v-if="addressInfo.real_name">
        <view class="name">
          {{ addressInfo.real_name }}
          <text class="phone">{{ addressInfo.phone }}</text>
        </view>
        <view class="detail line1">
          <text class="default-tag" v-if="addressInfo.is_default">[{{ $t('默认') }}]</text>
          {{ addressInfo.province }}{{ addressInfo.city }}{{ addressInfo.district }}{{ addressInfo.detail }}
        </view>
      </view>
      <view class="address-info" v-else>
        <view class="name">{{ $t('设置收货地址') }}</view>
        <view class="detail">{{ $t('请填写收货地址信息') }}</view>
      </view>
      <text class="arrow">›</text>
    </view>

    <!-- 到店自提 - 门店卡片 -->
    <view
      class="address-section pickup-section"
      v-if="modelValue === 'pickup'"
      @click="onPickupClick"
    >
      <view class="address-info" v-if="storeInfo.name">
        <view class="name">
          {{ storeInfo.name }}
          <text class="phone">{{ storeInfo.phone }}</text>
        </view>
        <view class="detail line1">
          {{ storeInfo.address }}{{ storeInfo.detailed_address ? ', ' + storeInfo.detailed_address : '' }}
        </view>
      </view>
      <view class="address-info" v-else>
        <view class="name">{{ $t('暂无门店信息') }}</view>
        <view class="detail">{{ $t('请选择门店') }}</view>
      </view>
      <view class="pickup-icons" v-if="storeInfo.name">
        <view class="iconfont icon-dianhua" @click.stop="call(storeInfo.phone)"></view>
        <view class="iconfont icon-dingwei2" @click.stop="openLocation(storeInfo)"></view>
      </view>
      <text class="arrow" v-if="storeInfo.name">›</text>
    </view>

    <!-- 上门服务 - 预约信息表单 -->
    <view class="address-section door-section" v-if="modelValue === 'door'">
      <view class="door-form">
        <view class="form-row">
          <text class="form-label">{{ $t('联系人') }}</text>
          <input class="form-input" v-model="localService.contact_name" :placeholder="$t('请输入联系人姓名')" @input="emitServiceChange" />
        </view>
        <view class="form-row">
          <text class="form-label">{{ $t('联系电话') }}</text>
          <input class="form-input" v-model="localService.contact_phone" type="number" maxlength="11" :placeholder="$t('请输入联系电话')" @input="emitServiceChange" />
        </view>
        <view class="form-row">
          <text class="form-label">{{ $t('服务地址') }}</text>
          <input class="form-input" v-model="localService.address" :placeholder="$t('请输入上门服务地址')" @input="emitServiceChange" />
        </view>
        <view class="form-row" @click="openAppointmentPopup">
          <text class="form-label">{{ $t('预约时间') }}</text>
          <view class="picker-value" :class="{ 'picker-placeholder': !appointmentTimeText }">
            {{ appointmentTimeText || $t('请选择预约时间') }}
          </view>
          <text class="arrow">›</text>
        </view>
        <view class="form-row" v-if="doorPostage > 0">
          <text class="form-label">{{ $t('上门服务费') }}</text>
          <view class="picker-value door-postage">￥{{ doorPostage }}</view>
        </view>
      </view>
    </view>

    <!-- 预约时间底部弹窗 -->
    <view class="appointment-mask" v-if="popupVisible" @click="closeAppointmentPopup"></view>
    <view class="appointment-popup" :class="{ 'popup-show': popupVisible }">
      <view class="popup-header">
        <text class="popup-cancel" @click="closeAppointmentPopup">{{ $t('取消') }}</text>
        <text class="popup-title">{{ $t('选择预约时间') }}</text>
        <text class="popup-confirm" @click="confirmAppointment">{{ $t('确定') }}</text>
      </view>
      <view class="popup-body">
        <!-- 日期选择 -->
        <scroll-view class="date-scroll" scroll-x>
          <view class="date-list">
            <view
              v-for="(item, idx) in dateList"
              :key="idx"
              class="date-item"
              :class="{ 'date-selected': tempDate === item.date }"
              @click="selectDate(item)"
            >
              <text class="date-weekday">{{ item.weekday }}</text>
              <text class="date-day">{{ item.day }}</text>
            </view>
          </view>
        </scroll-view>
        <!-- 时间段选择 -->
        <view class="time-section">
          <text class="time-label">{{ $t('选择时间段') }}</text>
          <view class="time-grid">
            <view
              v-for="(slot, idx) in timeSlots"
              :key="idx"
              class="time-slot"
              :class="{ 'slot-selected': tempTime === slot.value }"
              @click="selectTime(slot)"
            >
              <text class="slot-text">{{ slot.label }}</text>
            </view>
          </view>
        </view>
      </view>
    </view>
  </view>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import i18n from '@/utils/lang.js';
import wechat from '@/libs/wechat';

defineOptions({ name: 'DeliverySection' });

const props = defineProps({
  /** 当前配送方式：delivery / pickup / door */
  modelValue: { type: String, default: 'delivery' },
  /** 是否支持快递配送 */
  expressShipping: { type: Boolean, default: true },
  /** 是否支持到店自提 */
  storePickup: { type: Boolean, default: true },
  /** 是否支持上门服务 */
  doorService: { type: Boolean, default: false },
  /** 收货地址信息 */
  addressInfo: { type: Object, default: () => ({}) },
  /** 门店信息 */
  storeInfo: { type: Object, default: () => ({}) },
  /** 上门服务预约信息初始值 */
  serviceInfo: { type: Object, default: () => ({}) },
  /** 上门服务费用（元） */
  doorPostage: { type: [Number, String], default: 0 },
});

const emit = defineEmits(['update:modelValue', 'change', 'address-click', 'pickup-click', 'service-change']);

// 上门服务表单本地数据（提交时通过 service-change 事件上报）
const localService = reactive({
  contact_name: '',
  contact_phone: '',
  address: '',
});
const serviceDate = ref('');
const serviceTime = ref('');
// 弹窗相关
const popupVisible = ref(false);
const tempDate = ref('');
const tempTime = ref('');

/** 预约时间展示文案 */
const appointmentTimeText = computed(() => {
  if (serviceDate.value && serviceTime.value) {
    return serviceDate.value + ' ' + serviceTime.value;
  }
  if (serviceDate.value) {
    return serviceDate.value;
  }
  return '';
});

/** 生成未来30天日期列表 */
const dateList = computed(() => {
  var list = [];
  var days = [i18n.t('日'), i18n.t('一'), i18n.t('二'), i18n.t('三'), i18n.t('四'), i18n.t('五'), i18n.t('六')];
  var today = new Date();
  for (var i = 0; i < 30; i++) {
    var d = new Date(today);
    d.setDate(today.getDate() + i);
    var year = d.getFullYear();
    var month = String(d.getMonth() + 1).padStart(2, '0');
    var day = String(d.getDate()).padStart(2, '0');
    var dateStr = year + '-' + month + '-' + day;
    list.push({
      date: dateStr,
      weekday: i18n.t('周') + days[d.getDay()],
      day: month + '/' + day,
    });
  }
  return list;
});

/** 生成时间段列表 08:00-20:00，每小时一段 */
const timeSlots = computed(() => {
  var list = [];
  for (var h = 8; h < 20; h++) {
    var start = String(h).padStart(2, '0') + ':00';
    var end = String(h + 1).padStart(2, '0') + ':00';
    list.push({ label: start + '-' + end, value: start });
  }
  return list;
});

// 父组件更新初始值（如地址选择回填）时同步到本地
watch(
  () => props.serviceInfo,
  (val) => {
    if (!val) return;
    var data = val || {};
    // 用 undefined 判断而非 ||，避免用户清空输入后旧值回写覆盖
    if (data.contact_name !== undefined) localService.contact_name = data.contact_name;
    if (data.contact_phone !== undefined) localService.contact_phone = data.contact_phone;
    if (data.address !== undefined) localService.address = data.address;
    // 已组合好的完整预约时间直接拆分回填
    if (data.appointment_time !== undefined && data.appointment_time) {
      var parts = String(data.appointment_time).split(' ');
      serviceDate.value = parts[0] || '';
      serviceTime.value = parts[1] || '';
    }
  },
  { immediate: true }
);

/** 切换配送方式 */
function onTabChange(type) {
  if (type === props.modelValue) return;
  emit('update:modelValue', type);
  emit('change', type);
}
/** 点击门店卡片 */
function onPickupClick() {
  if (props.storeInfo.name) {
    emit('pickup-click');
  }
}
/** 拨打电话 */
function call(phone) {
  if (!phone) return;
  uni.makePhoneCall({ phoneNumber: phone });
}
/** 打开地图导航 */
function openLocation(store) {
  if (!store.latitude || !store.longitude) return;
  // #ifdef H5
  if (wechat && wechat.isWeixin()) {
    wechat.seeLocation({
      latitude: Number(store.latitude),
      longitude: Number(store.longitude),
      name: store.name,
      scale: 13,
      address: store.address + (store.detailed_address ? '-' + store.detailed_address : ''),
    });
    return;
  }
  // #endif
  uni.openLocation({
    latitude: Number(store.latitude),
    longitude: Number(store.longitude),
    name: store.name,
    address: store.address + (store.detailed_address ? '-' + store.detailed_address : ''),
  });
}
/** 打开预约时间底部弹窗 */
function openAppointmentPopup() {
  tempDate.value = serviceDate.value || (dateList.value[0] && dateList.value[0].date) || '';
  tempTime.value = serviceTime.value || '';
  popupVisible.value = true;
}
/** 关闭预约时间弹窗 */
function closeAppointmentPopup() {
  popupVisible.value = false;
}
/** 弹窗内选择日期 */
function selectDate(item) {
  tempDate.value = item.date;
}
/** 弹窗内选择时间 */
function selectTime(slot) {
  tempTime.value = slot.value;
}
/** 确定预约时间 */
function confirmAppointment() {
  if (!tempDate.value) {
    uni.showToast({ title: i18n.t('请选择预约日期'), icon: 'none' });
    return;
  }
  if (!tempTime.value) {
    uni.showToast({ title: i18n.t('请选择预约时间段'), icon: 'none' });
    return;
  }
  serviceDate.value = tempDate.value;
  serviceTime.value = tempTime.value;
  popupVisible.value = false;
  emitServiceChange();
}
/** 表单内容变化时上报预约信息 */
function emitServiceChange() {
  emit('service-change', {
    contact_name: localService.contact_name,
    contact_phone: localService.contact_phone,
    address: localService.address,
    appointment_time: appointmentTime(),
  });
}
/**
 * 组合预约时间
 * @returns {string} 完整预约时间（如 "2026-07-31 14:00"），未选齐时返回空
 */
function appointmentTime() {
  if (serviceDate.value && serviceTime.value) {
    return serviceDate.value + ' ' + serviceTime.value;
  }
  return '';
}
</script>

<style scoped>
/* 配送方式切换 */
.delivery-section {
  display: flex;
  justify-content: flex-start;
  background: #fff;
  margin: 20rpx 20rpx 0;
  border-radius: 12rpx;
  overflow: hidden;
}
.delivery-tab {
  flex: 1;
  text-align: center;
  padding: 28rpx 0;
  font-size: 30rpx;
  color: #666;
  position: relative;
}
.delivery-tab:only-child {
  flex: none;
  text-align: left;
  padding-left: 30rpx;
}
.delivery-tab.active {
  color: var(--view-theme, #e93323);
  font-weight: bold;
}
.delivery-tab.active::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 25%;
  right: 25%;
  height: 6rpx;
  background: var(--view-theme, #e93323);
  border-radius: 3rpx;
}
.delivery-tab:only-child.active::after {
  left: 30rpx;
  width: 100rpx;
  right: auto;
}

/* 地址区域 */
.address-section {
  display: flex;
  align-items: center;
  background: #fff;
  margin: 0 20rpx 20rpx;
  padding: 28rpx 30rpx;
  border-radius: 0 0 12rpx 12rpx;
}
.address-info {
  flex: 1;
}
.address-info .name {
  font-size: 30rpx;
  font-weight: bold;
  color: #282828;
  margin-bottom: 10rpx;
}
.address-info .name .phone {
  margin-left: 50rpx;
  font-weight: normal;
}
.address-info .detail {
  font-size: 26rpx;
  color: #666;
}
.address-info .default-tag {
  margin-right: 12rpx;
}
.arrow {
  font-size: 40rpx;
  color: #707070;
  margin-left: 20rpx;
}
.line1 {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* 自提模式 */
.pickup-section .address-info {
  max-width: 510rpx;
}
.pickup-icons {
  display: flex;
  align-items: center;
  margin-right: 10rpx;
}
.pickup-icons .iconfont {
  width: 44rpx;
  height: 44rpx;
  background: #f5f5f5;
  font-size: 20rpx;
  border-radius: 50%;
  text-align: center;
  line-height: 44rpx;
  color: var(--view-theme, #e93323);
  margin-left: 26rpx;
}

/* 上门服务表单 */
.door-section {
  display: block;
}
.door-form {
  width: 100%;
}
.form-row {
  display: flex;
  align-items: center;
  padding: 16rpx 0;
  font-size: 28rpx;
  border-bottom: 2rpx solid #f5f5f5;
}
.form-row:last-child {
  border-bottom: none;
}
.form-label {
  width: 150rpx;
  color: #282828;
  font-weight: bold;
  flex-shrink: 0;
}
.form-input {
  flex: 1;
  text-align: right;
  font-size: 28rpx;
  color: #333;
}
.form-picker {
  flex: 1;
}
.picker-value {
  flex: 1;
  text-align: right;
  font-size: 28rpx;
  color: #333;
}
.picker-placeholder {
  color: #999;
}
.door-postage {
  color: var(--view-theme, #e93323);
  font-weight: bold;
}

/* 预约时间底部弹窗 */
.appointment-mask {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 998;
}
.appointment-popup {
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;
  background: #fff;
  border-radius: 24rpx 24rpx 0 0;
  z-index: 999;
  transform: translateY(100%);
  transition: transform 0.3s ease;
  max-height: 70vh;
  display: flex;
  flex-direction: column;
}
.appointment-popup.popup-show {
  transform: translateY(0);
}
.popup-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 28rpx 30rpx;
  border-bottom: 2rpx solid #f5f5f5;
}
.popup-cancel {
  font-size: 28rpx;
  color: #999;
}
.popup-title {
  font-size: 32rpx;
  font-weight: bold;
  color: #333;
}
.popup-confirm {
  font-size: 28rpx;
  color: var(--view-theme, #e93323);
  font-weight: bold;
}
.popup-body {
  flex: 1;
  overflow-y: auto;
  padding: 20rpx 0 40rpx;
}
.appointment-popup .date-scroll {
  white-space: nowrap;
  margin-bottom: 30rpx;
}
.appointment-popup .date-list {
  display: inline-flex;
  padding: 0 20rpx;
}
.appointment-popup .date-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 120rpx;
  padding: 16rpx 0;
  margin-right: 16rpx;
  border-radius: 12rpx;
  border: 2rpx solid #eee;
}
.appointment-popup .date-weekday {
  font-size: 22rpx;
  color: #999;
}
.appointment-popup .date-day {
  font-size: 30rpx;
  color: #333;
  font-weight: bold;
  margin-top: 4rpx;
}
.appointment-popup .date-selected {
  border-color: var(--view-theme, #e93323);
  background: var(--view-op-point-four);
}
.appointment-popup .date-selected .date-day {
  color: var(--view-theme, #e93323);
}
.time-section {
  padding: 0 30rpx;
}
.time-label {
  display: block;
  font-size: 28rpx;
  font-weight: bold;
  color: #333;
  margin-bottom: 20rpx;
}
.time-grid {
  display: flex;
  flex-wrap: wrap;
}
.time-slot {
  width: 30%;
  margin-right: 5%;
  margin-bottom: 20rpx;
  padding: 18rpx 0;
  text-align: center;
  border-radius: 12rpx;
  border: 2rpx solid #eee;
  background: #fff;
}
.time-slot:nth-child(3n) {
  margin-right: 0;
}
.slot-text {
  font-size: 26rpx;
  color: #333;
}
.slot-selected {
  border-color: var(--view-theme, #e93323);
  background: var(--view-op-point-four);
}
.slot-selected .slot-text {
  color: var(--view-theme, #e93323);
}
</style>
