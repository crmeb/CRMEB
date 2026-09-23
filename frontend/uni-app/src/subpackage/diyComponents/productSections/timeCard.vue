<template>
  <!--
    时间线
    数据来源：后端 display_sections 中 component=time_card 的所有 section
    渲染：时间段列表（label + time + 状态），按 status 高亮
    适用：预售期/使用期、有效期、演出场次时间、活动倒计时
  -->
  <view class="time-card" v-if="sections.length">
    <common-wrapper :config="dataConfig">
      <view
        class="time-card__section"
        v-for="(section, sIdx) in sections"
        :key="sIdx"
      >
        <view class="time-card__title" v-if="section.title">
          <text class="time-card__title-text">{{ section.title }}</text>
        </view>
        <view class="time-card__periods">
          <view
            class="time-card__period"
            :class="['time-card__period--' + (period.status || 'pending')]"
            v-for="(period, idx) in getPeriods(section)"
            :key="idx"
          >
            <view class="time-card__period-label">
              <view class="time-card__dot"></view>
              <text>{{ period.label }}</text>
            </view>
            <view class="time-card__period-time">
              <text>{{ period.time }}</text>
            </view>
            <view class="time-card__period-status" v-if="period.statusText">
              <text>{{ period.statusText }}</text>
            </view>
          </view>
        </view>
      </view>
    </common-wrapper>
  </view>
</template>

<script setup>
/**
 * 时间线组件
 * 接收 PageDesign 透传的同类型 sections 数组，渲染时间段列表
 * status 枚举：active 进行中 / pending 未开始 / expired 已结束
 */
import commonWrapper from "./commonWrapper.vue";

defineOptions({ name: "timeCard" });

defineProps({
  // DIY 配置项
  dataConfig: {
    type: Object,
    default: () => ({}),
  },
  // 后端 display_sections 中 component=time_card 的所有项
  sections: {
    type: Array,
    default: () => [],
  },
});

/**
 * 提取 section 的 periods
 * @param {Object} section
 * @return {Array}
 */
function getPeriods(section) {
  if (!section || !section.data) return [];
  return section.data.periods || [];
}
</script>

<style lang="scss" scoped>
.time-card {
  /* margin/background/border-radius 由 commonWrapper 控制 */

  &__section {
    padding: 24rpx;
  }

  &__title {
    padding-bottom: 16rpx;

    &-text {
      font-size: 28rpx;
      font-weight: 600;
      color: #333;
    }
  }

  &__periods {
    /* 时间线容器 */
  }

  &__period {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16rpx 0;
    position: relative;

    & + & {
      border-top: 1rpx solid #f5f5f5;
    }

    &-label {
      display: flex;
      align-items: center;
      font-size: 26rpx;
      color: #666;
    }

    &-time {
      font-size: 26rpx;
      color: #333;
      flex: 1;
      text-align: right;
      padding: 0 16rpx;
    }

    &-status {
      font-size: 22rpx;
      padding: 4rpx 12rpx;
      border-radius: 20rpx;
      background: #f5f5f5;
      color: #999;
    }

    /* 进行中：绿色 */
    &--active {
      .time-card__dot {
        background: #52c41a;
      }
      .time-card__period-status {
        background: rgba(82, 196, 26, 0.1);
        color: #52c41a;
      }
    }

    /* 未开始：灰色 */
    &--pending {
      .time-card__dot {
        background: #d9d9d9;
      }
    }

    /* 已结束：红色淡化 */
    &--expired {
      .time-card__dot {
        background: #ff4d4f;
      }
      .time-card__period-label,
      .time-card__period-time {
        color: #999;
      }
      .time-card__period-status {
        background: rgba(255, 77, 79, 0.1);
        color: #ff4d4f;
      }
    }
  }

  &__dot {
    width: 14rpx;
    height: 14rpx;
    border-radius: 50%;
    margin-right: 12rpx;
    background: #d9d9d9;
    flex-shrink: 0;
  }
}
</style>
