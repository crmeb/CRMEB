<!--
  收货地址卡片组件（DIY 通用化版）
  展示收货人姓名、电话、详细地址，支持点击拨打电话
  数据来源：section.props.contact
-->
<template>
  <view class="info-card address-card mt-12">
    <view class="card-title">{{ $t('收货信息') }}</view>
    <view class="address acea-row row-between-wrapper">
      <view class="address-box">
        <view class="name">
          {{ contact.name || '' }}
          <text class="phone" @tap="onCall">{{ contact.phone || '' }}</text>
        </view>
        <view>{{ addressText }}</view>
      </view>
      <view class="icon acea-row row-middle">
        <view class="iconfont icon-dianhua" @click.stop="onCall"></view>
      </view>
    </view>
  </view>
</template>

<script>
/**
 * 收货地址卡片组件
 * 展示收货人信息和地址，支持拨打电话
 */
export default {
  name: 'AddressCard',
  // DIY 通用化契约：组件唯一标识
  componentKey: 'address_card',
};
</script>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /** section 对象，含 props.contact */
  section: { type: Object, required: true },
});

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 引擎注入的全局状态（预留，当前组件不依赖） */
const ctx = computed(() => (props.section && props.section._ctx) || {});
/** 收货信息对象，含 name/phone/address(对象或字符串) */
const contact = computed(() => p.value.contact || {});
/**
 * 格式化地址文本
 * address 可能是对象（含 province/city/district/detail）或字符串
 * @return {string} 格式化后的地址
 */
const addressText = computed(() => {
  var addr = contact.value.address;
  if (!addr) return '';
  if (typeof addr === 'string') return addr;
  // 对象格式：拼接省市区+详细地址
  var parts = [addr.province, addr.city, addr.district, addr.detail].filter(function (p) { return p; });
  return parts.join('');
});

/** 拨打电话 */
function onCall() {
  var phone = contact.value.phone;
  if (!phone) return;
  uni.makePhoneCall({ phoneNumber: String(phone) });
}
</script>

<style scoped>
.address-card { margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 30rpx; }
.card-title { font-size: 30rpx; font-weight: bold; color: #333; margin-bottom: 20rpx; }
.address { display: flex; justify-content: space-between; align-items: center; }
.address-box { flex: 1; }
.name { font-size: 28rpx; color: #333; font-weight: 500; margin-bottom: 8rpx; }
.phone { margin-left: 20rpx; font-size: 26rpx; color: #666; }
.address-box > view:last-child { font-size: 26rpx; color: #666; line-height: 1.5; }
.icon { margin-left: 20rpx; flex-shrink: 0; }
.iconfont { font-size: 36rpx; color: var(--view-theme, #e93323); }
</style>
