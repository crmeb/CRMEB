<template>
  <!-- 标题 -->
  <common-wrapper :config="configData" v-show="!isSortType">
    <view :style="[titleWrapStyle]">
      <view
        @click="goLink"
        class="title acea-row row-middle row-between"
        :style="[titleLocation]"
      >
        <view :style="[titleStyle]">{{ dataConfig.titleConfig.value }}</view>
        <view
          class="more"
          v-if="!dataConfig.buttonConfig.tabVal"
          :style="[moreStyle]"
        >
          {{ dataConfig.titleConfigRight.value }}
          <text class="iconfont icon-ic_rightarrow"></text>
        </view>
      </view>
    </view>
  </common-wrapper>
</template>

<script setup>
import { computed } from "vue";
import commonWrapper from "./commonWrapper.vue";
import util from "@/utils/util.js";

defineOptions({ name: "titles" });

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
          val: props.dataConfig.prConfig ? props.dataConfig.prConfig.val : 0,
        },
        {
          val: props.dataConfig.bottomConfig
            ? props.dataConfig.bottomConfig.val
            : 0,
        },
        {
          val: props.dataConfig.prConfig ? props.dataConfig.prConfig.val : 0,
        },
      ],
    },
    marginConfig: props.dataConfig.marginConfig || {
      isAll: false,
      valList: [
        {
          val: props.dataConfig.mbConfig ? props.dataConfig.mbConfig.val : 0,
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
  };
});
const titleWrapStyle = computed(() => {
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
    // 后端部分主题（如个人中心 titles 项）不下发 moduleColor，缺失时不输出渐变背景
    background: (() => {
      const mc = props.dataConfig.moduleColor;
      if (!mc || !mc.color || !mc.color.length) return "";
      const colors = mc.color.map((c) => c.item).filter((c) => c);
      return colors.length >= 2
        ? `linear-gradient(90deg, ${colors[0]} 0%, ${colors[colors.length - 1]} 100%)`
        : colors.length === 1 ? colors[0] : "";
    })(),
  };
});
const titleStyle = computed(() => {
  let style = {
    "font-size": `${props.dataConfig.fontSize.val * 2}rpx`,
    color: props.dataConfig.themeColor.color[0].item,
  };
  switch (props.dataConfig.textStyle.tabVal) {
    case 1:
      style["font-style"] = "italic";
      break;
    case 2:
      style["font-weight"] = "bold";
      break;
  }
  return style;
});
const titleLocation = computed(() => {
  if (props.dataConfig.buttonConfig.tabVal) {
    let style = {};
    switch (props.dataConfig.textPosition.tabVal) {
      case 1:
        style["justify-content"] = "center";
        break;
      case 2:
        style["justify-content"] = "flex-end";
        break;
    }
    return style;
  }
});
const moreStyle = computed(() => {
  return {
    "font-size": `${props.dataConfig.buttonText.val * 2}rpx`,
    color: props.dataConfig.buttonColor.color[0].item,
  };
});

function goLink() {
  util.JumpPath(props.dataConfig.linkConfig.value);
}
</script>

<style lang="scss">
.title {
  justify-content: space-between;
  padding: 26rpx 24rpx;
  border-radius: 16rpx 16rpx 0rpx 0rpx;
  font-weight: 500;
  font-size: 32rpx;
  line-height: 44rpx;
  color: #333333;

  .more {
    font-weight: 400;
    font-size: 24rpx;
    line-height: 34rpx;
    color: #999999;
  }

  .iconfont {
    font-size: 24rpx;
  }
}
</style>
