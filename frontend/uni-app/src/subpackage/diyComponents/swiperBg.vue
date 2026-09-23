<template>
  <!-- 带背景轮播图 -->
  <common-wrapper :config="configData">
    <view class="swiperBg">
      <template v-if="dataConfig.swiperConfig.list.length">
        <view class="swiper">
          <swiper
            :style="'height:' + imageH + 'rpx;'"
            :autoplay="true"
            :previous-margin="swiperMargin"
            :next-margin="swiperMargin"
            :circular="circular"
            :interval="interval"
            :duration="duration"
            @change="bannerfun"
            v-if="imageH"
          >
            <swiper-item
              v-for="(item, index) in dataConfig.swiperConfig.list"
              :key="index"
            >
              <view
                @click="goDetail(item)"
                class="swiper-item"
                :style="[itemStyle, active == index ? activeStyle : '']"
              >
                <image
                  :src="item.img"
                  mode="aspectFill"
                  class="image"
                  :style="[imageStyle]"
                ></image>
              </view>
            </swiper-item>
          </swiper>
          <view class="noPic" v-else>{{ $t(`图片加载中`) }}...</view>
          <view class="dot acea-row" :style="[dotStyle]">
            <view
              class="progress"
              v-if="dataConfig.docConfig.tabVal == 2"
              :style="[progressWidth, dotItemStyle]"
            >
              <view
                class="inner"
                :style="[progressValue, dotItemActiveStyle]"
              ></view>
            </view>
            <view
              class="acea-row"
              :class="{
                small: dataConfig.docConfig.tabVal == 1,
                line: dataConfig.docConfig.tabVal == 3,
              }"
              v-else
            >
              <view
                class="dot-item"
                v-for="(item, index) in dataConfig.swiperConfig.list"
                :key="index"
                :class="{ active: active == index }"
                :style="[active == index ? dotItemActiveStyle : dotItemStyle]"
              ></view>
            </view>
          </view>
        </view>
      </template>
    </view>
  </common-wrapper>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from "vue";
import commonWrapper from "./commonWrapper.vue";
import util from "@/utils/util.js";

defineOptions({ name: "swiperBg" });

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

const circular = ref(true);
const autoplay = ref(true);
const interval = ref(3000);
const duration = ref(500);
const imgUrls = ref([]); //图片轮播数据
const bgColor = ref(""); //轮播背景颜色
const marginTop = ref(0); //组件上边距
const paddinglr = ref(0); //轮播左右边距
const docConfig = ref(0); //指示点样式
const imgConfig = ref(0); //是否为圆角
const imageH = ref(0);
const isColor = ref(0);
const txtStyle = ref(0);
const dotColor = ref("");
const current = ref(1); //数字指示器当前
const active = ref(0); //一般指示器当前
const swiperMargin = ref("");

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
const itemStyle = computed(() => {
  let val = props.dataConfig.imgConfig.val;
  let num = 1;
  if (props.dataConfig.styleConfig.tabVal == 1) {
    num = !val ? 1 : 0.9;
  }
  return {
    transform: `scale(${num})`,
  };
});
const activeStyle = computed(() => {
  let val = props.dataConfig.imgConfig.val;
  let num = 1;
  if (props.dataConfig.styleConfig.tabVal == 1) {
    num = !val ? 1 : 1 - val / 400;
  }
  return {
    transform: `scale(${num})`,
  };
});
// colorBgStyle() {
//   let styleObject = {
//     background: this.dataConfig.bgColor.color[0].item,
//   };
//   if (this.dataConfig.styleConfig.tabVal == 1) {
//     styleObject["height"] = "50%";
//   }
//   return styleObject;
// },
const imageStyle = computed(() => {
  let borderRadius = `${props.dataConfig.filletImg.val * 2}rpx`;
  if (props.dataConfig.filletImg.type) {
    borderRadius = `${props.dataConfig.filletImg.valList[0].val * 2}rpx ${
      props.dataConfig.filletImg.valList[1].val * 2
    }rpx ${props.dataConfig.filletImg.valList[3].val * 2}rpx ${
      props.dataConfig.filletImg.valList[2].val * 2
    }rpx`;
  }
  return {
    borderRadius: borderRadius,
  };
});
const dotStyle = computed(() => {
  let styleObject = {};
  if (props.dataConfig.docPosition.tabVal) {
    styleObject["justify-content"] =
      props.dataConfig.docPosition.tabVal == 1 ? "center" : "flex-end";
  }
  if (props.dataConfig.styleConfig.tabVal == 1) {
    styleObject["padding"] = "0 100rpx";
    styleObject["bottom"] = "32rpx";
  }
  return styleObject;
});
const dotItemStyle = computed(() => {
  let styleObject = {};
  if (props.dataConfig.toneConfig.tabVal) {
    styleObject["background"] = props.dataConfig.dotBgColor.color[0].item;
  }
  return styleObject;
});
const dotItemActiveStyle = computed(() => {
  let styleObject = {};
  if (props.dataConfig.toneConfig.tabVal) {
    styleObject["background"] = props.dataConfig.dotColor.color[0].item;
  }
  return styleObject;
});
const progressWidth = computed(() => {
  return {
    width: `${props.dataConfig.swiperConfig.list.length * 20}rpx`,
  };
});
const progressValue = computed(() => {
  return {
    width: `${
      (current.value / props.dataConfig.swiperConfig.list.length) * 100
    }%`,
  };
});

watch(imageH, (nVal, oVal) => {
  imageH.value = nVal;
});

// created 逻辑
imgUrls.value = props.dataConfig.swiperConfig.list;
if (props.dataConfig.styleConfig.tabVal == 1) {
  swiperMargin.value = "55rpx";
}

onMounted(() => {
  if (imgUrls.value.length) {
    nextTick((e) => {
      uni.getImageInfo({
        src: setDomain(imgUrls.value[0].img),
        success: (res) => {
          if (res && res.height > 0) {
            let p = props.dataConfig.paddingConfig.isAll
              ? props.dataConfig.paddingConfig.val
              : props.dataConfig.paddingConfig.valList[1].val;
            let height = res.height * ((750 - p * 4) / res.width);
            imageH.value = height;
          } else {
            imageH.value = 375;
          }
        },
        fail: function (error) {
          imageH.value = 375;
        },
      });
    });
  }
});

function bannerfun(e) {
  active.value = e.detail.current;
  current.value = e.detail.current + 1;
}
//替换安全域名
function setDomain(url) {
  url = url ? url.toString() : "";
  //本地调试打开,生产请注销
  if (url.indexOf("https://") > -1) return url;
  else return url.replace("http://", "https://");
}
function goDetail(url) {
  let urls = url.info[0].value;
  util.JumpPath(urls);
}
</script>

<style lang="scss">
.noPic {
  border-radius: 10rpx;
  width: 100%;
  height: 300rpx;
  background-color: #f0f0f0;
  color: #ccc;
  text-align: center;
  line-height: 300rpx;
  font-size: 30rpx;
}

.swiperBg {
  position: relative;

  .colorBg {
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 100%;
  }

  .swiper {
    z-index: 20;
    position: relative;
    overflow: hidden;

    .dot {
      position: absolute;
      bottom: 20rpx;
      left: 0;
      width: 100%;
      padding: 0 20rpx;

      .dot-item {
        width: 12rpx;
        height: 12rpx;
        border-radius: 6rpx;
        margin-right: 16rpx;
        background: #dddddd;

        &:last-child {
          margin-right: 0;
        }

        &.active {
          background: var(--view-theme);
        }
      }

      .small {
        .dot-item {
          width: 10rpx;
          height: 10rpx;
          border-radius: 5rpx;
          margin-right: 8rpx;

          &.active {
            width: 18rpx;
          }
        }
      }

      .line {
        .dot-item {
          width: 20rpx;
          height: 6rpx;
          border-radius: 3rpx;
          margin-right: 10rpx;
        }
      }

      .progress {
        width: 60rpx;
        height: 6rpx;
        border-radius: 3rpx;
        background: #dddddd;

        .inner {
          width: 33%;
          height: 6rpx;
          border-radius: 3rpx;
          background: var(--view-theme);
          transition: 0.3s;
        }
      }
    }

    .swiper-item {
      width: 100%;
      height: 100%;
      transform: scale(0.9);
      transition: 0.3s;

      // &.active {
      // 	transform: scale(1);
      // }
    }

    .image {
      width: 100%;
      height: 100%;
    }

    // 圆形指示点
    &.circular {
:deep(.uni-swiper-dot){
        width: 10rpx !important;
        height: 10rpx !important;
        background: rgba(0, 0, 0, 0.4) !important;
      }

:deep(.uni-swiper-dot-active){
        background: #fff !important;
      }
    }

    // 方形指示点
    &.square {
:deep(.uni-swiper-dot){
        width: 20rpx !important;
        height: 5rpx !important;
        border-radius: 3rpx;
        background: rgba(0, 0, 0, 0.4) !important;
      }

:deep(.uni-swiper-dot-active){
        background: #fff !important;
      }
    }
  }
}
</style>
