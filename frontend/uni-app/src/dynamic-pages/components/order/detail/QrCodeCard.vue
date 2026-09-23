<!--
  核销码卡片组件（DIY 通用化契约版）
  展示核销二维码和核销码文本，用于到店自提核销
  数据来源：section.props.code（核销码字符串）
-->
<template>
  <view class="verify-card mt-12">
    <view class="verify-title">{{ $t('核销信息') }}</view>
    <view class="verify-body">
      <!-- 核销二维码 -->
      <view class="verify-qrcode-box">
        <zb-code
          :val="code || ''"
          :size="340"
          unit="upx"
          :loadMake="true"
          cid="verify-qr-code"
        />
      </view>
      <!-- 核销码文本 -->
      <view class="verify-code-row">
        <text class="verify-code-label">{{ $t('核销码：') }}</text>
        <text class="verify-code-value">{{ code }}</text>
      </view>
      <text class="verify-tip">{{ $t('出示核销码给店员扫码核销') }}</text>
    </view>
  </view>
</template>

<script>
/**
 * 核销码卡片组件
 * 展示核销二维码和核销码，用于到店自提订单
 * 通过 componentKey='qrcode' 匹配 section.component
 */
export default {
  name: 'QrCodeCard',
  // DIY 引擎 componentKey 标识
  componentKey: 'qrcode',
};
</script>

<script setup>
import { computed } from 'vue';
import ZbCode from '@/components/zb-code/zb-code.vue';

const props = defineProps({
  /** section 对象，含 props.code 和 _ctx 全局状态 */
  section: { type: Object, required: true },
});

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 引擎注入的全局状态（预留，当前 QrCodeCard 不依赖全局状态） */
const ctx = computed(() => (props.section && props.section._ctx) || {});
/** 核销码字符串（后端下发在 section.props.code） */
const code = computed(() => p.value.code || '');
</script>

<style scoped>
.verify-card { margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 30rpx; }
.verify-title { font-size: 30rpx; font-weight: bold; color: #333; margin-bottom: 20rpx; }
.verify-body { display: flex; flex-direction: column; align-items: center; }
.verify-qrcode-box { margin-bottom: 20rpx; }
.verify-code-row { display: flex; align-items: center; margin-bottom: 12rpx; }
.verify-code-label { font-size: 26rpx; color: #999; }
.verify-code-value { font-size: 32rpx; color: #333; font-weight: 600; letter-spacing: 2rpx; }
.verify-tip { font-size: 24rpx; color: #999; }
</style>
