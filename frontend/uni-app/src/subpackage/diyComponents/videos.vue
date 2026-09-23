<template>
  <view v-show="!isSortType">
    <common-wrapper :config="configData">
      <view class="video-box" id="myVideo" :style="[videoStyle]">
        <video
          class="video"
          object-fit="cover"
          :src="dataConfig.videoConfig.url"
          :poster="dataConfig.imgConfig.url"
          controls
        ></video>
      </view>
    </common-wrapper>
  </view>
</template>

<script setup>
import { ref, computed, getCurrentInstance, onMounted } from "vue";
import commonWrapper from "./commonWrapper.vue";

defineOptions({ name: "videos" });

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

const height = ref(0);

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
// 视频高度
const videoStyle = computed(() => {
  let borderRadius = `${props.dataConfig.fillet.val * 2}rpx`;
  if (props.dataConfig.fillet.type) {
    borderRadius = `${props.dataConfig.fillet.valList[0].val * 2}rpx ${
      props.dataConfig.fillet.valList[1].val * 2
    }rpx ${props.dataConfig.fillet.valList[3].val * 2}rpx ${
      props.dataConfig.fillet.valList[2].val * 2
    }rpx`;
  }
  return {
    height: height.value + "px",
    "border-radius": borderRadius,
  };
});

const instance = getCurrentInstance();
onMounted(() => {
  const query = uni.createSelectorQuery().in(instance.proxy);
  query
    .select("#myVideo")
    .boundingClientRect((data) => {
      switch (props.dataConfig.scaleConfig.tabVal) {
        case 0:
          height.value = (data.width * 9) / 16;
          break;
        case 1:
          height.value = (data.width * 3) / 4;
          break;
        default:
          height.value = data.width;
          break;
      }
    })
    .exec();
});
</script>

<style lang="scss" scoped>
.video-box {
  overflow: hidden;
}

.video {
  width: 100%;
  height: 100%;
}
</style>
