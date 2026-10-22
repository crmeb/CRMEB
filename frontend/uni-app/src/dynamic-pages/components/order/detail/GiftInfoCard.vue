<!--
  礼物信息卡片（送礼订单专用）
  送礼人：展示领取人（未领取时展示等待提示）
  领取人：展示赠送人
  数据来源：section.props.gift + section.props.role / display
  展示对象由后端 applyGiftViewerPolicy 按查看者身份下发，前端不判断用户关系
-->
<template>
  <view v-if="gift" class="info-card gift-card">
    <view class="card-header acea-row row-between-wrapper">
      <view class="card-title">{{ $t('礼物信息') }}</view>
      <view v-if="role === 'sender' && hasDisplay && gift.gift_time" class="gift-time">
        {{ $t('领取时间') }}：{{ formatTime(gift.gift_time) }}
      </view>
    </view>
    <view class="gift-user acea-row row-middle">
      <image v-if="display.avatar" class="avatar" :src="display.avatar" mode="aspectFill" />
      <view v-else class="avatar avatar-empty">
        <text class="iconfont icon-ic_gift1"></text>
      </view>
      <view class="user-info">
        <view v-if="role === 'receiver'" class="gift-text">
          <text class="nickname">{{ displayName }}</text>
          <text class="gift-suffix">{{ $t('赠您一份礼物，请查收！') }}</text>
        </view>
        <view v-else-if="hasDisplay" class="gift-text">
          <text class="nickname">{{ displayName }}</text>
          <text class="gift-suffix">{{ $t('已领取您的礼物') }}</text>
        </view>
        <view v-else class="gift-text">
          <text class="gift-suffix">{{ $t('等待好友领取') }}</text>
        </view>
      </view>
    </view>
    <view v-if="gift.gift_mark" class="gift-mark">
      <text class="gift-mark-label">{{ $t('礼物留言') }}：</text>
      <text class="gift-mark-text">{{ gift.gift_mark }}</text>
    </view>
  </view>
</template>

<script>
/**
 * 礼物信息卡片
 * 送礼订单详情中展示赠送人 / 领取人信息与礼物留言
 */
export default {
  name: 'GiftInfoCard',
  // DIY 通用化契约：组件唯一标识
  componentKey: 'gift_info',
};
</script>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /** section 对象，含 props.gift / props.role / props.display */
  section: { type: Object, required: true },
});

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 礼物数据 */
const gift = computed(() => p.value.gift || null);
/** 查看者角色：sender=送礼人，receiver=领取人 */
const role = computed(() => p.value.role || 'sender');
/** 需要展示的用户（送礼人看领取人，领取人看赠送人） */
const display = computed(() => p.value.display || {});
/** 是否存在可展示对象：送礼人未领取时为 false */
const hasDisplay = computed(() => !!p.value.display);
/** 展示用户名，缺失时兜底为“好友” */
const displayName = computed(() => display.value.nickname || '好友');

/**
 * 时间戳格式化为 YYYY-MM-DD HH:mm
 * @param {number} ts 秒级时间戳
 * @return {string} 格式化后的时间
 */
function formatTime(ts) {
  if (!ts) return '';
  const date = new Date(ts * 1000);
  const pad = function (n) {
    return n < 10 ? '0' + n : '' + n;
  };
  return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate())
    + ' ' + pad(date.getHours()) + ':' + pad(date.getMinutes());
}
</script>

<style scoped>
.info-card { margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 30rpx; }
.card-header { margin-bottom: 20rpx; }
.card-title { font-size: 30rpx; font-weight: bold; color: #333; }
.gift-user { padding: 6rpx 0; }
.avatar { width: 72rpx; height: 72rpx; border-radius: 50%; background: #f5f5f5; flex-shrink: 0; }
.avatar-empty { display: flex; align-items: center; justify-content: center; }
.avatar-empty .iconfont { font-size: 36rpx; color: #e93323; }
.user-info { flex: 1; margin-left: 20rpx; min-width: 0; }
.gift-text { font-size: 28rpx; color: #333; display: flex; align-items: center; flex-wrap: wrap; }
.nickname { font-weight: 600; color: #333; margin-right: 8rpx; }
.gift-suffix { color: #666; }
.gift-mark {
  padding: 18rpx 20rpx;
  border-radius: 12rpx;
  background: #faf7f5;
  font-size: 26rpx;
  line-height: 1.6;
}
.gift-mark-label { color: #999; }
.gift-mark-text { color: #666; }
.gift-time { flex-shrink: 0; margin-left: 20rpx; font-size: 24rpx; color: #999; }
</style>
