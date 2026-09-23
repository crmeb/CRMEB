<template>
  <!-- 辅助线 -->
  <view v-show="!isSortType">
    <common-wrapper :config="configData">
      <view class="lines">
        <view class="item" :style="[lineStyle]"></view>
      </view>
    </common-wrapper>
  </view>
</template>

<script setup>
import { ref, computed } from "vue";
import commonWrapper from "./commonWrapper.vue";

defineOptions({ name: "guide" });

const props = defineProps({
  dataConfig: {
    type: Object,
    default: () => ({}),
  },
  isSortType: {
    type: [String, Number],
    default: 0,
  },
});

const heightConfig = ref(0);
const lineColor = ref("");
// lineStyle: 0,
const lrEdge = ref(0);
const mbConfig = ref(0);

const lineStyle = computed(() => {
  return {
    borderBottomWidth: `${props.dataConfig.heightConfig.val * 2}rpx`,
    borderBottomStyle:
      props.dataConfig.lineStyle.tabList[props.dataConfig.lineStyle.tabVal]
        .style,
    borderBottomColor: props.dataConfig.lineColor.color[0].item,
  };
});
const configData = computed(() => {
  return {
    ...props.dataConfig,
  };
});
</script>

<style lang="scss">
.lines {
  .item {
    box-sizing: border-box;
    border-bottom-color: red;
    border-width: 0 0 1rpx;
    border-bottom-style: solid;
  }
}
</style>
