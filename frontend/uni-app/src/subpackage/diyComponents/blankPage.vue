<template>
  <!-- 辅助空白 -->
  <common-wrapper v-show="!isSortType" :config="configData">
    <view class="blankPage">
      <view class="bankCon" :style="[blankStyle]"></view>
    </view>
  </common-wrapper>
</template>

<script setup>
import { ref, computed } from "vue";
import commonWrapper from "./commonWrapper.vue";

defineOptions({ name: "blankPage" });

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

const bgColor = ref("");
const heightConfig = ref(0);

const configData = computed(() => {
  return {
    ...props.dataConfig,
    paddingConfig: props.dataConfig.paddingConfig || {
      isAll: false,
      valList: [
        {
          val: props.dataConfig.topConfig
            ? props.dataConfig.topConfig.val
            : 0,
        },
        {
          val: props.dataConfig.lrEdge ? props.dataConfig.lrEdge.val : 0,
        },
        {
          val: props.dataConfig.bottomConfig
            ? props.dataConfig.bottomConfig.val
            : 0,
        },
        {
          val: props.dataConfig.lrEdge ? props.dataConfig.lrEdge.val : 0,
        },
      ],
    },
    marginConfig: props.dataConfig.marginConfig || {
      isAll: false,
      valList: [
        {
          val: 0,
        },
        {
          val: 0,
        },
        {
          val: 0,
        },
        {
          val: 0,
        },
      ],
    },
    componentBgConfig:
      props.dataConfig.componentBgConfig || props.dataConfig.bottomBgColor,
  };
});
const blankStyle = computed(() => {
  let borderRadius = `${props.dataConfig.fillet.val * 2}rpx`;
  if (props.dataConfig.fillet.type) {
    borderRadius = `${props.dataConfig.fillet.valList[0].val * 2}rpx ${
      props.dataConfig.fillet.valList[1].val * 2
    }rpx ${props.dataConfig.fillet.valList[3].val * 2}rpx ${
      props.dataConfig.fillet.valList[2].val * 2
    }rpx`;
  }
  return {
    height: `${props.dataConfig.heightConfig.val * 2}rpx`,
    "border-radius": borderRadius,
    background: props.dataConfig.bgColor.color[0].item,
  };
});
</script>

<style lang="scss">
.blankPage {
  .bankCon {
    width: 100%;
    height: 100%;
    background-color: blue;
  }
}
</style>
