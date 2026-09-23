<!--
  游客信息回显组件（门票专属，DIY 通用化契约版）
  展示用户下单时填写的游客实名信息，身份证号由后端脱敏后下发
  已出票订单会下发每位游客的独立核销二维码（一人一码）
  数据来源：section.props.items = [{name, id_card, qr_code}, ...]
-->
<template>
  <view class="info-card visitor-info-card" v-if="items && items.length > 0">
    <view class="card-title">{{ $t('游客信息') }}</view>
    <view class="visitor-row" v-for="(item, idx) in items" :key="idx">
      <view class="visitor-idx">{{ $t('游客') }}{{ idx + 1 }}</view>
      <view class="visitor-fields">
        <view class="info-row acea-row row-between">
          <text class="info-label">{{ $t('姓名') }}</text>
          <text class="info-value">{{ item.name }}</text>
        </view>
        <view class="info-row acea-row row-between">
          <text class="info-label">{{ $t('身份证号') }}</text>
          <text class="info-value">{{ item.id_card }}</text>
        </view>
      </view>
      <!-- 核销二维码：一人一码，仅已出票（item.qr_code 非空）时展示 -->
      <view class="visitor-qrcode" v-if="item.qr_code">
        <view class="qrcode-title">{{ $t('核销二维码') }}</view>
        <view class="qrcode-wrap">
          <zb-code
            :cid="'visitor-qr-' + idx"
            :val="item.qr_code"
            :size="200"
            :unit="'upx'"
            :show="true"
            :loadMake="true"
            :onval="true"
          />
        </view>
        <view class="qrcode-tip">{{ $t('请凭此码入场核销') }}</view>
        <view class="qrcode-code">{{ item.qr_code }}</view>
      </view>
    </view>
  </view>
</template>

<script>
/**
 * 游客信息回显组件
 * 展示门票订单的游客实名信息，身份证号已脱敏（保留前6后4）
 * 已出票订单展示每位游客的独立核销二维码
 * 通过 componentKey='visitors' 匹配 section.component
 */
export default {
  name: 'VisitorInfoCard',
  // DIY 引擎 componentKey 标识
  componentKey: 'visitors',
};
</script>

<script setup>
import { computed } from 'vue';
import zbCode from '@/components/zb-code/zb-code.vue'

const props = defineProps({
  /** section 对象，含 props.items 和 _ctx 全局状态 */
  section: { type: Object, required: true },
});

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 引擎注入的全局状态（预留，当前 VisitorInfoCard 不依赖全局状态） */
const ctx = computed(() => (props.section && props.section._ctx) || {});
/** 游客信息数组（后端下发在 section.props.items），每项含 {name, id_card, qr_code}，qr_code 为空表示未出票 */
const items = computed(() => p.value.items || []);
</script>

<style scoped>
.visitor-info-card {
  margin: 20rpx;
  background: #fff;
  border-radius: 16rpx;
  padding: 30rpx;
}
.card-title {
  font-size: 30rpx;
  font-weight: bold;
  color: #333;
  margin-bottom: 20rpx;
}
.visitor-row {
  padding: 16rpx 0;
  border-top: 1px solid #f5f5f5;
}
.visitor-row:first-of-type {
  border-top: none;
}
.visitor-idx {
  font-size: 28rpx;
  font-weight: 600;
  color: var(--view-theme, #e93323);
  margin-bottom: 12rpx;
}
.visitor-fields {
  padding-left: 16rpx;
}
.info-row {
  padding: 8rpx 0;
  font-size: 26rpx;
}
.info-label {
  color: #999;
  flex-shrink: 0;
}
.info-value {
  color: #333;
  text-align: right;
}
.visitor-qrcode {
  margin-top: 20rpx;
  padding: 24rpx;
  background: #fafafa;
  border-radius: 12rpx;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.qrcode-title {
  font-size: 26rpx;
  font-weight: 600;
  color: #333;
  margin-bottom: 16rpx;
}
.qrcode-wrap {
  padding: 16rpx;
  background: #fff;
  border-radius: 8rpx;
  display: flex;
  align-items: center;
  justify-content: center;
}
.qrcode-tip {
  margin-top: 12rpx;
  font-size: 22rpx;
  color: #999;
}
.qrcode-code {
  margin-top: 8rpx;
  font-size: 22rpx;
  color: #bbb;
  word-break: break-all;
}
</style>
