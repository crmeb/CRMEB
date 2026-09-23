<template>
  <!--
    BlockPolicy 政策键值对
    渲染：购买须知/退改政策/入住政策等键值对列表
    适用：酒店入住政策、门票退改、租赁押金、限购规则、库存说明
  -->
  <view class="block-policy" v-if="items.length">
    <view
      class="block-policy__item"
      v-for="(item, idx) in items"
      :key="idx"
    >
      <view class="block-policy__label">{{ item.label }}</view>
      <view class="block-policy__value">{{ item.value }}</view>
    </view>
  </view>
</template>

<script setup>
/**
 * 政策键值对子渲染器
 * block.items 结构：[{label, value}]
 * 视觉与 InfoCard 类似，但语义独立（强调"政策/规则"含义）
 */
import { computed } from "vue";

defineOptions({ name: "BlockPolicy" });

const props = defineProps({
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
.block-policy {
  &__item {
    display: flex;
    align-items: flex-start;
    padding: 14rpx 0;
    font-size: 26rpx;

    & + & {
      border-top: 1rpx dashed #f0f0f0;
    }
  }

  &__label {
    width: 160rpx;
    flex-shrink: 0;
    color: #999;
  }

  &__value {
    flex: 1;
    color: #333;
    line-height: 1.6;
  }
}
</style>
