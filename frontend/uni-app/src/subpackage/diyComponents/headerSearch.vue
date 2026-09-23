<template>
  <common-wrapper :config="configData">
    <view>
      <!-- 搜索框 -->
      <!-- #ifdef H5  -->
      <view class="header">
        <view
          class="serch-wrapper acea-row row-middle"
          :style="[serchWrapperStyle, txtPosition]"
        >
          <view
            class="logo skeleton-rect"
            v-if="styleConfig == 0 && styleTypeConfig == 1 && logoConfig"
          >
            <image :src="logoConfig" mode="heightFix"></image>
          </view>
          <view
            class="title"
            :style="[txtStyle]"
            v-if="
              titleConfig &&
              (styleConfig == 1 || (styleConfig == 0 && styleTypeConfig == 0))
            "
            @click="goLink"
            >{{ titleConfig }}</view
          >
          <navigator :render-link="false"
            v-if="styleConfig === 0"
            url="/pages/goods/goods_search/index"
            class="input acea-row row-middle skeleton-rect"
            hover-class="none"
          >
            <view class="search acea-row row-middle" :style="[searchStyle]">
              <text class="iconfont icon-ic_search"></text>
              <swiper
                v-if="hotWords.length"
                :autoplay="true"
                :interval="3000"
                :duration="1000"
                :vertical="true"
                :circular="true"
                class="swiper"
                :style="{ color: dataConfig.hotWordsColor.color[0].item }"
              >
                <swiper-item v-for="(item, index) in hotWords" :key="index">
                  {{ item.val }}
                </swiper-item>
              </swiper>
              <text v-else>{{ dataConfig.tipConfig.value }}</text>
            </view>
          </navigator>
        </view>
      </view>
      <!-- #endif -->
      <!-- #ifdef MP || APP-PLUS -->
      <view>
        <view class="mp-header" :class="special ? 'on' : ''">
          <view
            class="sys-head"
            :style="[statusBarStyle]"
            v-if="!special"
          ></view>
          <view
            class="serch-box"
            :style="[
              serchWrapperStyle,
              { height: serchHeight + 'px' },
              { paddingRight: (!special ? serchRight : 0) + 'px' },
            ]"
          >
            <view
              class="serch-wrapper acea-row row-middle"
              :style="[txtPosition]"
            >
              <view
                class="logo skeleton-rect"
                v-if="styleConfig == 0 && styleTypeConfig == 1 && logoConfig"
              >
                <image :src="logoConfig" mode="heightFix"></image>
              </view>
              <view
                class="title"
                :style="[txtStyle]"
                v-if="
                  titleConfig &&
                  (styleConfig == 1 ||
                    (styleConfig == 0 && styleTypeConfig == 0))
                "
                @click="goLink"
                >{{ titleConfig }}</view
              >
              <navigator :render-link="false"
                v-if="styleConfig === 0"
                url="/pages/goods/goods_search/index"
                class="input acea-row row-middle skeleton-rect"
                hover-class="none"
              >
                <view
                  class="search acea-row row-middle line1"
                  :style="[searchStyle]"
                >
                  <text class="iconfont icon-ic_search"></text>
                  <swiper
                    v-if="hotWords.length"
                    :autoplay="true"
                    :interval="3000"
                    :duration="1000"
                    :vertical="true"
                    :circular="true"
                    class="swiper"
                    :style="{ color: dataConfig.hotWordsColor.color[0].item }"
                  >
                    <swiper-item v-for="(item, index) in hotWords" :key="index">
                      {{ item.val }}
                    </swiper-item>
                  </swiper>
                  <text class="line1 flex-1" v-else>{{
                    dataConfig.tipConfig.value
                  }}</text>
                </view>
              </navigator>
            </view>
          </view>
        </view>
        <view
          :style="'height:' + (statusBarHeight + serchHeight) + 'px;'"
          v-if="!special"
        ></view>
      </view>
      <!-- #endif -->
    </view>
  </common-wrapper>
</template>

<script setup>
import { ref, computed, getCurrentInstance, onMounted } from "vue";
import commonWrapper from "./commonWrapper.vue";
import util from "@/utils/util.js";
import { useHotWordsStore } from "@/store/modules/hotWords";
let statusBarHeight = uni.getWindowInfo().statusBarHeight;

defineOptions({ name: "headerSerch" });

const props = defineProps({
  dataConfig: {
    type: Object,
    default: () => ({}),
  },
  special: {
    type: Number,
    default: 0,
  },
  belongIndex: {
    type: Number,
    default: 0,
  },
});

const hotWordsStore = useHotWordsStore();

const statusBarHeightRef = ref(statusBarHeight);
const marTop = ref(63);
const styleConfig = ref(props.dataConfig.styleConfig.tabVal);
const styleTypeConfig = ref(props.dataConfig.styleTypeConfig.tabVal);
const bgColor = ref(props.dataConfig.moduleColor.color);
const titleConfig = ref(props.dataConfig.titleConfig.value);
const txtColor = ref(props.dataConfig.txtColor.color[0].item);
const txtStyleConfig = ref(
  props.dataConfig.txtStyleConfig.tabList[
    props.dataConfig.txtStyleConfig.tabVal
  ].style
);
const txtSize = ref(props.dataConfig.txtSize.val);
// fixConfig: this.dataConfig.fixConfig.tabVal,
const logoConfig = ref(props.dataConfig.logoConfig.url);
const txtFixConfig = ref(props.dataConfig.txtFixConfig.tabVal);
const boxStyle = ref("");
const mbConfig = ref("");
const hotWords = ref([]);
const prConfig = ref("");
const tabVal = ref("");
const radioVal = ref("");
const textColor = ref("");
const textStyle = ref("");
const serchHeight = ref(43);
const serchRight = ref("");

const configData = computed(() => {
  return {
    ...props.dataConfig,
    zIndexConfig: 1000,
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
    backgroundConfig: props.dataConfig.bottomBgColor,
  };
});
const txtStyle = computed(() => {
  let num = 0;
  if (styleConfig.value == 0 && styleTypeConfig.value != 2) {
    num = 30;
  }
  return {
    color: `${txtColor.value}`,
    fontStyle: `${
      txtStyleConfig.value != "bold" ? txtStyleConfig.value : ""
    }`,
    fontWeight: `${
      txtStyleConfig.value == "bold" ? txtStyleConfig.value : ""
    }`,
    fontSize: `${txtSize.value * 2}rpx`,
    marginRight: `${num}rpx`,
  };
});
const serchWrapperStyle = computed(() => {
  let borderRadius = `${props.dataConfig.fillet.val * 2}rpx`;
  if (props.dataConfig.fillet.type) {
    borderRadius = `${props.dataConfig.fillet.valList[0].val * 2}rpx ${
      props.dataConfig.fillet.valList[1].val * 2
    }rpx ${props.dataConfig.fillet.valList[3].val * 2}rpx ${
      props.dataConfig.fillet.valList[2].val * 2
    }rpx`;
  }
  return {
    background: `linear-gradient(90deg, ${props.dataConfig.moduleColor.color[0].item} 0%, ${props.dataConfig.moduleColor.color[1].item} 100%)`,
  };
});
// 状态栏背景
const statusBarStyle = computed(() => {
  return {
    height: `${statusBarHeight}px`,
    background: `linear-gradient(90deg, ${props.dataConfig.moduleColor.color[0].item} 0%, ${props.dataConfig.moduleColor.color[1].item} 100%)`,
  };
});
const txtPosition = computed(() => {
  return {
    justifyContent:
      styleConfig.value != 0 && txtFixConfig.value === 1
        ? "center"
        : styleConfig.value != 0 && txtFixConfig.value === 2
        ? "flex-end"
        : "flex-start",
    paddingLeft:
      styleConfig.value != 0 && txtFixConfig.value === 1
        ? serchRight.value + "px !important"
        : 0,
  };
});
const searchStyle = computed(() => {
  return {
    background: props.dataConfig.searchBoxColor.color[0].item,
    color: props.dataConfig.tipColor.color[0].item,
    justifyContent:
      txtFixConfig.value == 0
        ? "flex-start"
        : txtFixConfig.value == 2
        ? "flex-end"
        : "center",
  };
});

const instance = getCurrentInstance();
onMounted(() => {
  hotWords.value = props.dataConfig.hotWords.list.filter((item) => {
    if (item.val) {
      return item;
    }
  });
  uni.setStorageSync("hotList", hotWords.value);
  hotWordsStore.setHotWord(hotWords.value);
  // #ifdef MP || APP-PLUS
  setTimeout(() => {
    // 获取小程序头部高度
    let info = uni.createSelectorQuery().in(instance.proxy).select(".mp-header");
    info
      .boundingClientRect(function (data) {
        marTop.value = data.height;
      })
      .exec();
  }, 100);
  // #endif
  // #ifdef MP
  const { windowWidth, statusBarHeight } = uni.getWindowInfo();
  const { top, left, width, height } = uni.getMenuButtonBoundingClientRect();
  serchHeight.value = (top - statusBarHeight) * 2 + height;
  serchRight.value = windowWidth - left;
  // #endif
});

function goLink() {
  let url = props.dataConfig.linkConfig.value;
  util.JumpPath(url);
}
</script>

<style lang="scss" scoped>
.serch-wrapper {
  &.center {
    justify-content: center;
  }

  &.right {
    justify-content: flex-end;
    /* #ifdef MP */
    padding-right: 185rpx !important;
    /* #endif */
  }
}

.title {
  margin-right: 30rpx;
  font-weight: 400;
  font-size: 30rpx;
  color: #333333;
}

.map {
  color: #fff;
  font-size: 28rpx;
  margin-right: 20rpx;
  max-width: 100%;

  .info {
    &.on {
      max-width: 260rpx;
    }

    &.on1 {
      max-width: 156rpx;
    }
  }

  .iconfont {
    font-size: 28rpx;
  }

  .icon-ic_downarrow {
    opacity: 0.8;
  }

  .icon-ic_location51 {
    margin-right: 6rpx;
  }
}

.header {
  width: 100%;
  // height: 100rpx;
  // background: linear-gradient(90deg, $bg-star 50%, $bg-end 100%);

  .serch-wrapper {
    height: 96rpx;
    padding: 18rpx 30rpx !important;

    .logo {
      height: 60rpx;
      margin-right: 20rpx;

      image {
        width: 100%;
        height: 100%;
      }
    }

    .input {
      position: relative;
      flex: 1;

      .search {
        flex: 1;
        height: 60rpx;
        padding: 0 32rpx;
        border-radius: 30rpx;
        background: #f5f5f5;
        font-size: 28rpx;
        line-height: 32rpx;
      }

      .iconfont {
        margin-right: 16rpx;
        font-size: 32rpx;
      }

      .swiper {
        flex: 1;
        height: 32rpx;
      }
    }
  }
}

/* #ifdef MP || APP-PLUS */
.mp-header {
  z-index: 300;
  position: fixed;
  left: 0;
  top: 0;
  width: 100%;
  &.on {
    position: unset;
  }

  .serch-wrapper {
    height: 100%;
    /* #ifdef MP */
    padding: 0 30rpx !important;
    /* #endif */
    /* #ifdef APP-PLUS */
    padding: 0 30rpx !important;
    /* #endif */
    .logo {
      height: 60rpx;
      margin-right: 20rpx;

      image {
        width: 100%;
        height: 100%;
      }
    }

    .input {
      position: relative;
      flex: 1;

      .search {
        flex: 1;
        height: 60rpx;
        padding: 0 32rpx;
        border-radius: 30rpx;
        background: #f5f5f5;
        font-size: 28rpx;
        line-height: 32rpx;
      }

      .iconfont {
        font-size: 32rpx;
        margin-right: 18rpx;
      }

      .swiper {
        flex: 1;
        height: 32rpx;
      }

      .button {
        position: absolute;
        top: 4rpx;
        right: 4rpx;
        height: 52rpx;
        padding: 0 24rpx;
        border-radius: 26rpx;
        background: var(--view-theme);
        font-weight: 500;
        line-height: 52rpx;
        font-size: 22rpx;
        color: #ffffff;
      }

      .button2 {
        margin-left: 20rpx;
        font-size: 30rpx;
        color: var(--view-theme);
      }

      // 没有logo，直接搜索框
      &.on {
        /* #ifdef MP */
        width: 70%;
        /* #endif */
        /* #ifdef APP-PLUS */
        width: 100%;
        /* #endif */
      }

      // 设置圆角
      &.fillet {
        border-radius: 29rpx;
      }

      // 文本框文字居中
      &.row-center {
        padding: 0;
      }
    }
  }
}

/* #endif */
.row-center uni-swiper-item,
.row-center swiper-item {
  text-align: center;
}
</style>
