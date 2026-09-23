<template>
  <!--
    BlockChips 属性标签
    渲染：横排标签芯片 {label: value}
    适用：卡密面值、会员卡面值、当前选中规格、有效期等
  -->
  <view class="block-chips" v-if="items.length">
    <view class="block-chips__item" v-for="(item, idx) in items" :key="idx">
      <text class="block-chips__label">{{ item.label }}</text>
      <text class="block-chips__value">{{ item.value }}</text>
    </view>
  </view>
</template>

<script setup>
/**
 * 属性标签子渲染器
 * block.items 结构：[{label, value}]
 */
import { computed } from "vue";

defineOptions({ name: "BlockChips" });

const props = defineProps({
  // block 对象（含 type/title/items）
  block: {
    type: Object,
    default: () => ({}),
  },
});

const items = computed(() => {
  return (props.block && props.block.items) || [];
});
</script>

<style lang="scss" scoped>
.block-chips {
  display: flex;
  flex-wrap: wrap;

  &__item {
    display: inline-flex;
    align-items: center;
    padding: 8rpx 16rpx;
    margin: 0 16rpx 16rpx 0;
    background: #f7f8fa;
    border-radius: 8rpx;
    font-size: 24rpx;
  }

  &__label {
    color: #999;

    &::after {
      content: "：";
    }
  }

  &__value {
    color: #333;
    font-weight: 500;
  }
}
</style>
