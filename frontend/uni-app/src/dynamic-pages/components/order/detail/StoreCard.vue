<!--
  门店信息卡片组件（到店自提，DIY 通用化版）
  展示门店名称、电话、地址，支持点击拨打电话
  数据来源：section.props.store
-->
<template>
  <view class="info-card store-card mt-12">
    <view class="card-title">{{ $t('店铺信息') }}</view>
    <view class="address acea-row row-between-wrapper">
      <view class="address-box">
        <view class="name">
          {{ store.name || '' }}
          <text class="phone" @tap="onCall">{{ store.phone || '' }}</text>
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
 * 门店信息卡片组件
 * 展示自提门店信息和地址，支持拨打电话
 */
export default {
  name: 'StoreCard',
  // DIY 通用化契约：组件唯一标识
  componentKey: 'store_card',
};
</script>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /** section 对象，含 props.store */
  section: { type: Object, required: true },
});

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 引擎注入的全局状态（预留，当前组件不依赖） */
const ctx = computed(() => (props.section && props.section._ctx) || {});
/** 门店信息对象，含 name/phone/address/detailed_address */
const store = computed(() => p.value.store || {});
/**
 * 格式化门店地址文本
 * @return {string} 门店完整地址
 */
const addressText = computed(() => {
  if (!store.value) return '';
  // detailed_address 优先，其次拼接 address
  if (store.value.detailed_address) return store.value.detailed_address;
  return store.value.address || '';
});

/** 拨打门店电话 */
function onCall() {
  var phone = store.value.phone;
  if (!phone) return;
  uni.makePhoneCall({ phoneNumber: String(phone) });
}
</script>

<style scoped>
.store-card { margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 30rpx; }
.card-title { font-size: 30rpx; font-weight: bold; color: #333; margin-bottom: 20rpx; }
.address { display: flex; justify-content: space-between; align-items: center; }
.address-box { flex: 1; }
.name { font-size: 28rpx; color: #333; font-weight: 500; margin-bottom: 8rpx; }
.phone { margin-left: 20rpx; font-size: 26rpx; color: #666; }
.address-box > view:last-child { font-size: 26rpx; color: #666; line-height: 1.5; }
.icon { margin-left: 20rpx; flex-shrink: 0; }
.iconfont { font-size: 36rpx; color: var(--view-theme, #e93323); }
</style>
