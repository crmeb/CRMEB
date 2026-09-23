<template>
  <!--
    BlockTimeSlots 时段列表
    渲染：场次/时段卡片列表（名称 + 时段 + 库存状态）
    适用：门票场次、预约时段、课程课时
    交互：点击选中高亮 + 提示"请点击底部立即购买"，详情页只读预览不采集下单数据
  -->
  <view class="block-time-slots" v-if="items.length">
    <view
      class="block-time-slots__item"
      :class="{
        'block-time-slots__item--sold-out': isSoldOut(item),
        'block-time-slots__item--active': isSelected(item),
      }"
      v-for="(item, idx) in items"
      :key="idx"
      @click="onSelect(item)"
    >
      <view class="block-time-slots__main">
        <view class="block-time-slots__name">{{ item.name }}</view>
        <view class="block-time-slots__time">{{ item.time }}</view>
      </view>
      <view class="block-time-slots__stock" v-if="item.stock_status">
        <text :class="stockClass(item.stock_status)">{{ stockText(item.stock_status, item.stock_count) }}</text>
      </view>
    </view>
  </view>
</template>

<script setup>
/**
 * 时段列表子渲染器
 * block.items 结构：[{name, time, stock_status, stock_count}]
 * stock_status 枚举：sufficient 充足 / tight 紧张 / sold_out 售罄
 *
 * 交互设计（符合设计文档 11.5 节职责边界）：
 * - 详情页只读预览，不采集下单数据
 * - 点击可购项 → 高亮选中 + 提示"请点击底部立即购买"
 * - 点击售罄项 → 提示"已售罄"
 * - 选中状态仅为视觉反馈，不参与下单采集
 */
import { ref, computed } from "vue";
import i18n from "@/utils/lang.js";

defineOptions({ name: "BlockTimeSlots" });

const props = defineProps({
  block: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(["blockSelect"]);

// 当前选中项的 name+time（唯一标识）
const selectedKey = ref("");

const items = computed(() => {
  return (props.block && props.block.items) || [];
});

function isSoldOut(item) {
  return item.stock_status === "sold_out";
}
/**
 * 生成 item 的唯一 key
 * @param {Object} item
 * @return {string}
 */
function itemKey(item) {
  return (item.name || "") + "|" + (item.time || "");
}
/**
 * 判断是否选中
 * @param {Object} item
 * @return {boolean}
 */
function isSelected(item) {
  return selectedKey.value && selectedKey.value === itemKey(item);
}
/**
 * 点击场次/时段
 * 售罄拦截，可购项高亮选中 + emit 选中数据给父组件
 * 父组件（goods_details）会在跳转下单页时携带选中数据
 * @param {Object} item 场次/时段数据
 */
function onSelect(item) {
  if (isSoldOut(item)) {
    return uni.showToast({ title: i18n.t("已售罄"), icon: "none" });
  }
  selectedKey.value = itemKey(item);
  // 通知父组件当前选中项，跳转下单页时携带
  emit("blockSelect", { type: "time_slots", item });
}
function stockText(status, count) {
  if (status === "sold_out") return "已售罄";
  if (status === "tight") {
    return count !== undefined && count !== null ? `仅剩 ${count}` : "紧张";
  }
  if (status === "sufficient") return "充足";
  return "";
}
function stockClass(status) {
  const map = {
    sufficient: "block-time-slots__stock--sufficient",
    tight: "block-time-slots__stock--tight",
    sold_out: "block-time-slots__stock--sold-out",
  };
  return map[status] || "";
}
</script>

<style lang="scss" scoped>
.block-time-slots {
  display: flex;
  flex-wrap: wrap;

  &__item {
    position: relative;
    width: calc(50% - 10rpx);
    margin-right: 20rpx;
    margin-bottom: 20rpx;
    padding: 20rpx;
    background: #f7f8fa;
    border-radius: 12rpx;
    border: 1rpx solid transparent;
    transition: all 0.2s;

    &:nth-child(2n) {
      margin-right: 0;
    }

    &--sold-out {
      opacity: 0.5;
    }

    // 选中高亮样式
    &--active {
      background: var(--view-op-point-four);
      border-color: var(--view-theme, #e93323);
    }
  }

  &__main {
    margin-bottom: 8rpx;
  }

  &__name {
    font-size: 26rpx;
    color: #333;
    margin-bottom: 4rpx;

    // 选中时名称高亮
    .block-time-slots__item--active & {
      color: var(--view-theme, #e93323);
      font-weight: 600;
    }
  }

  &__time {
    font-size: 24rpx;
    color: #666;
  }

  &__stock {
    font-size: 22rpx;

    &--sufficient {
      color: #52c41a;
    }

    &--tight {
      color: #ff7a00;
    }

    &--sold-out {
      color: #999;
    }
  }
}
</style>
