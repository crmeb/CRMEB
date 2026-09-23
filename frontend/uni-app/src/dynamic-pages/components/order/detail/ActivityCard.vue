<!--
  活动进度卡片组件（DIY 通用化版，自包含标题）
  支持拼团(combination)、砍价(bargain)、秒杀(seckill)、积分兑换(integral)四种活动类型
  数据来源：section.props.activity
  标题"活动信息"由组件内部渲染（原引擎外层标题已收归组件）
-->
<template>
  <view class="info-card activity-card" v-if="activity && activity.type">
    <view class="card-title">{{ $t('活动信息') }}</view>
    <view class="activity-card-content">
      <!-- 拼团进度 -->
      <template v-if="activity.type === 'combination'">
        <view class="activity-status">
          <text class="status-label">{{ $t('拼团状态：') }}</text>
          <text class="status-value" :class="activity.status === 'success' ? 'success' : 'warning'">
            {{ combinationStatusText }}
          </text>
        </view>
        <view class="activity-progress">
          <text class="progress-text">{{ activity.current_num || 0 }}/{{ activity.need_num || 0 }} {{ $t('人') }}</text>
          <view class="progress-bar">
            <view
              class="progress-fill"
              :style="{ width: combinationPercent + '%' }"
            ></view>
          </view>
        </view>
        <view v-if="activity.members && activity.members.length" class="activity-members">
          <view
            v-for="(member, idx) in activity.members"
            :key="idx"
            class="member-item"
          >
            <image :src="member.avatar || '/static/images/f.png'" mode="aspectFill" class="member-avatar" />
            <text class="member-name">{{ member.nickname || $t('用户') }}</text>
            <text v-if="member.is_head" class="member-tag">{{ $t('团长') }}</text>
          </view>
        </view>
        <view v-if="activity.expire_time" class="activity-expire">
          <text class="iconfont icon-shijian"></text>
          <text>{{ $t('剩余') }} {{ formatRemaining(activity.expire_time) }} {{ $t('结束') }}</text>
        </view>
      </template>

      <!-- 砍价进度 -->
      <template v-else-if="activity.type === 'bargain'">
        <view class="activity-status">
          <text class="status-label">{{ $t('当前价格：') }}</text>
          <text class="status-value price">¥{{ activity.current_price || '0.00' }}</text>
        </view>
        <view class="activity-progress">
          <text class="progress-label">{{ $t('最低可砍至') }} ¥{{ activity.min_price || '0.00' }}</text>
          <view class="progress-bar">
            <view
              class="progress-fill"
              :style="{ width: bargainPercent + '%' }"
            ></view>
          </view>
        </view>
        <view v-if="activity.helpers && activity.helpers.length" class="activity-helpers">
          <text class="helpers-title">{{ $t('帮砍好友') }}（{{ activity.helpers.length }}{{ $t('人') }}）</text>
          <view class="helpers-list">
            <view
              v-for="(helper, idx) in activity.helpers"
              :key="idx"
              class="helper-item"
            >
              <image :src="helper.avatar || '/static/images/f.png'" mode="aspectFill" class="helper-avatar" />
              <text class="helper-name">{{ helper.nickname || $t('好友') }}</text>
              <text class="helper-cut">-¥{{ helper.cut_price || '0.00' }}</text>
            </view>
          </view>
        </view>
      </template>

      <!-- 秒杀活动 -->
      <template v-else-if="activity.type === 'seckill'">
        <view class="activity-status">
          <text class="status-label">{{ $t('秒杀价：') }}</text>
          <text class="status-value price">¥{{ activity.seckill_price || '0.00' }}</text>
        </view>
        <view v-if="activity.original_price" class="activity-original">
          <text class="original-label">{{ $t('原价：') }}</text>
          <text class="original-price">¥{{ activity.original_price }}</text>
        </view>
      </template>

      <!-- 积分兑换 -->
      <template v-else-if="activity.type === 'integral'">
        <view class="activity-status">
          <text class="status-label">{{ $t('消耗积分：') }}</text>
          <text class="status-value">{{ activity.use_integral || 0 }} {{ $t('积分') }}</text>
        </view>
        <view v-if="activity.integral_price" class="activity-original">
          <text class="original-label">{{ $t('抵扣金额：') }}</text>
          <text class="original-price">¥{{ activity.integral_price }}</text>
        </view>
      </template>

      <!-- 未知活动类型降级 -->
      <template v-else>
        <view class="activity-unknown">
          <text>{{ $t('活动类型：') }}{{ activity.type }}</text>
        </view>
      </template>
    </view>
  </view>
</template>

<script>
/**
 * 活动进度卡片组件
 * 支持拼团(combination)、砍价(bargain)、秒杀(seckill)、积分兑换(integral)四种活动类型
 * 由 OrderDetailEngine 按 page_config 的 activity_card 组件声明渲染
 */
export default {
  name: 'ActivityCard',
  // DIY 通用化契约：组件唯一标识
  componentKey: 'activity_card',
};
</script>

<script setup>
import { computed } from 'vue';
import i18n from '@/utils/lang.js';

const props = defineProps({
  /** section 对象，含 props.activity */
  section: { type: Object, required: true },
});

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 引擎注入的全局状态（预留，当前组件不依赖） */
const ctx = computed(() => (props.section && props.section._ctx) || {});
/** 活动数据对象，结构因 type 而异 */
const activity = computed(() => p.value.activity || {});
/**
 * 拼团进度百分比
 */
const combinationPercent = computed(() => {
  var need = activity.value.need_num || 1;
  var current = activity.value.current_num || 0;
  return Math.min(100, Math.round((current / need) * 100));
});
/**
 * 拼团状态文案
 */
const combinationStatusText = computed(() => {
  var map = {
    grouping: i18n.t('拼团中'),
    success: i18n.t('拼团成功'),
    fail: i18n.t('拼团失败'),
    expired: i18n.t('已过期'),
  };
  return map[activity.value.status] || i18n.t('拼团中');
});
/**
 * 砍价进度百分比（已砍比例）
 */
const bargainPercent = computed(() => {
  var current = parseFloat(activity.value.current_price) || 0;
  var min = parseFloat(activity.value.min_price) || 0;
  var max = parseFloat(activity.value.original_price) || current;
  if (max <= min) return 100;
  return Math.min(100, Math.round(((max - current) / (max - min)) * 100));
});

/**
 * 格式化剩余时间
 * @param {number} expireTime 过期时间戳
 * @return {string} 返回如 "2天3小时" 或 "15分钟" 的文案
 */
function formatRemaining(expireTime) {
  var remaining = expireTime - Math.floor(Date.now() / 1000);
  if (remaining <= 0) return i18n.t('已结束');
  var days = Math.floor(remaining / 86400);
  var hours = Math.floor((remaining % 86400) / 3600);
  var minutes = Math.floor((remaining % 3600) / 60);
  if (days > 0) return days + i18n.t('天') + hours + i18n.t('小时');
  if (hours > 0) return hours + i18n.t('小时') + minutes + i18n.t('分钟');
  return minutes + i18n.t('分钟');
}
</script>

<style scoped>
/* 卡片容器（自包含，与其他 section 组件一致） */
.info-card { margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 30rpx; }
.card-title { font-size: 30rpx; font-weight: bold; color: #333; margin-bottom: 20rpx; }

.activity-card-content { padding: 10rpx 0; }

.activity-status { display: flex; align-items: center; margin-bottom: 16rpx; }
.status-label { font-size: 26rpx; color: #999; }
.status-value { font-size: 28rpx; font-weight: bold; color: #333; }
.status-value.price { color: var(--view-theme, #e93323); }
.status-value.success { color: #07c160; }
.status-value.warning { color: #ff976a; }

.activity-progress { margin-bottom: 16rpx; }
.progress-text { font-size: 26rpx; color: #666; margin-bottom: 10rpx; display: block; }
.progress-label { font-size: 24rpx; color: #999; margin-bottom: 10rpx; display: block; }
.progress-bar { height: 12rpx; background: #f5f5f5; border-radius: 6rpx; overflow: hidden; }
.progress-fill { height: 100%; background: var(--view-theme, #e93323); border-radius: 6rpx; transition: width 0.3s ease; }

.activity-members { margin-top: 16rpx; }
.member-item { display: flex; align-items: center; padding: 10rpx 0; }
.member-avatar { width: 56rpx; height: 56rpx; border-radius: 50%; margin-right: 16rpx; }
.member-name { font-size: 26rpx; color: #333; flex: 1; }
.member-tag { font-size: 20rpx; color: #fff; background: var(--view-theme, #e93323); padding: 2rpx 12rpx; border-radius: 20rpx; }

.activity-expire { display: flex; align-items: center; margin-top: 16rpx; font-size: 24rpx; color: #ff976a; }
.activity-expire .iconfont { font-size: 24rpx; margin-right: 6rpx; }

.activity-helpers { margin-top: 16rpx; }
.helpers-title { font-size: 26rpx; color: #666; margin-bottom: 12rpx; display: block; }
.helpers-list { display: flex; flex-wrap: wrap; }
.helper-item { display: flex; align-items: center; width: 50%; padding: 8rpx 0; }
.helper-avatar { width: 48rpx; height: 48rpx; border-radius: 50%; margin-right: 12rpx; }
.helper-name { font-size: 24rpx; color: #666; flex: 1; }
.helper-cut { font-size: 24rpx; color: var(--view-theme, #e93323); }

.activity-original { margin-top: 8rpx; }
.original-label { font-size: 24rpx; color: #999; }
.original-price { font-size: 24rpx; color: #999; text-decoration: line-through; }

.activity-unknown { padding: 20rpx 0; font-size: 26rpx; color: #999; }
</style>
