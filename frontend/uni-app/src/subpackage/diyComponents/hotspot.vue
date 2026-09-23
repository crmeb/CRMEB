<template>
  <common-wrapper :config="configData">
    <view class="hotspot">
      <image
        :src="dataConfig.picStyle.url"
        mode="widthFix"
        class="image"
        :style="[imageRadius]"
      ></image>
      <view
        v-for="item in dataConfig.picStyle.list"
        :key="item.number"
        :style="{
          top: `${item.starY}rpx`,
          left: `${item.starX}rpx`,
          width: `${item.areaWidth}rpx`,
          height: `${item.areaHeight}rpx`,
        }"
        class="area"
        @click="goPage(item.link)"
      ></view>
    </view>
  </common-wrapper>
</template>

<script setup>
import { computed } from "vue";
import commonWrapper from "./commonWrapper.vue";
import util from "@/utils/util.js";

defineOptions({ name: "hotspot" });

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

const imageRadius = computed(() => {
  let borderRadius = `${props.dataConfig.fillet.val * 2}rpx`;
  if (props.dataConfig.fillet.type) {
    borderRadius = `${props.dataConfig.fillet.valList[0].val * 2}rpx ${
      props.dataConfig.fillet.valList[1].val * 2
    }rpx ${props.dataConfig.fillet.valList[3].val * 2}rpx ${
      props.dataConfig.fillet.valList[2].val * 2
    }rpx`;
  }
  return {
    "border-radius": borderRadius,
  };
});
const configData = computed(() => {
  return {
    ...props.dataConfig,
  };
});

function goPage(link) {
  util.JumpPath(link);
}
</script>

<style lang="scss" scoped>
.hotspot {
  position: relative;

  .image {
    display: block;
    width: 100%;
  }

  .area {
    position: absolute;
  }
}
</style>
