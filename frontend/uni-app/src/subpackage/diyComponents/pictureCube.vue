<template>
  <!-- 图片魔方 -->
  <view v-if="picList.length">
    <common-wrapper :config="configData">
      <view class="pictureCube">
        <view class="advert1" v-if="style == 0">
          <view
            class="item"
            v-for="(item, index) in picList"
            :key="index"
            :style="[imgGap]"
            @click="goDetail(item)"
          >
            <easy-loadimage
              mode="widthFix"
              width="100%"
              :height="imageH + 'px'"
              :image-src="item.image"
              class="image"
              :borderRadius="imgBorderRadius"
            ></easy-loadimage>
          </view>
        </view>
        <view class="advert2" v-else-if="style == 1">
          <view
            class="item"
            v-for="(item, index) in picList"
            :key="index"
            :style="[imgGap]"
            @click="goDetail(item)"
          >
            <easy-loadimage
              mode="aspectFill"
              width="100%"
              :height="imageH + 'rpx'"
              :image-src="item.image"
              class="image"
              :borderRadius="imgBorderRadius"
            ></easy-loadimage>
          </view>
        </view>
        <view class="advert3" v-else-if="style == 2">
          <view
            class="item"
            v-for="(item, index) in picList"
            :key="index"
            :style="[imgGap]"
            @click="goDetail(item)"
          >
            <easy-loadimage
              mode="aspectFill"
              width="100%"
              :height="imageH + 'rpx'"
              :image-src="item.image"
              class="image"
              :borderRadius="imgBorderRadius"
            ></easy-loadimage>
          </view>
        </view>
        <view class="advert4" :style="[autoHeight]" v-else-if="style == 3">
          <view class="item">
            <view class="pic" :style="[imgGap]" @click="goDetail(picList[0])">
              <easy-loadimage
                mode="aspectFill"
                width="100%"
                height="100%"
                :image-src="picList[0].image"
                class="image"
                :borderRadius="imgBorderRadius"
              ></easy-loadimage>
            </view>
            <view class="pic" :style="[imgGap]" @click="goDetail(picList[1])">
              <easy-loadimage
                mode="aspectFill"
                width="100%"
                height="100%"
                :image-src="picList[1].image"
                class="image"
                :borderRadius="imgBorderRadius"
              ></easy-loadimage>
            </view>
          </view>
          <view class="item" :style="[imgGap]" @click="goDetail(picList[2])">
            <easy-loadimage
              mode="aspectFill"
              width="100%"
              height="100%"
              :image-src="picList[2].image"
              class="image"
              :borderRadius="imgBorderRadius"
            ></easy-loadimage>
          </view>
        </view>
        <view class="advert5" :style="[autoHeight]" v-else-if="style == 4">
          <view class="item" :style="[imgGap]" @click="goDetail(picList[0])">
            <easy-loadimage
              mode="aspectFill"
              width="100%"
              height="100%"
              :image-src="picList[0].image"
              class="image"
              :borderRadius="imgBorderRadius"
            ></easy-loadimage>
          </view>
          <view class="item">
            <view class="pic" :style="[imgGap]" @click="goDetail(picList[1])">
              <easy-loadimage
                mode="aspectFill"
                width="100%"
                height="100%"
                :image-src="picList[1].image"
                class="image"
                :borderRadius="imgBorderRadius"
              ></easy-loadimage>
            </view>
            <view class="pic" :style="[imgGap]" @click="goDetail(picList[2])">
              <easy-loadimage
                mode="aspectFill"
                width="100%"
                height="100%"
                :image-src="picList[2].image"
                class="image"
                :borderRadius="imgBorderRadius"
              ></easy-loadimage>
            </view>
          </view>
        </view>
        <view class="advert6" :style="[autoHeight]" v-else-if="style == 5">
          <view class="item" :style="[imgGap]" @click="goDetail(picList[0])">
            <easy-loadimage
              mode="aspectFill"
              width="100%"
              height="100%"
              :image-src="picList[0].image"
              class="image"
              :borderRadius="imgBorderRadius"
            ></easy-loadimage>
          </view>
          <view class="item">
            <view class="pic" :style="[imgGap]" @click="goDetail(picList[1])">
              <easy-loadimage
                mode="aspectFill"
                width="100%"
                height="100%"
                :image-src="picList[1].image"
                class="image"
                :borderRadius="imgBorderRadius"
              ></easy-loadimage>
            </view>
            <view class="pic" :style="[imgGap]" @click="goDetail(picList[2])">
              <easy-loadimage
                mode="aspectFill"
                width="100%"
                height="100%"
                :image-src="picList[2].image"
                class="image"
                :borderRadius="imgBorderRadius"
              ></easy-loadimage>
            </view>
          </view>
        </view>
        <view class="advert7" :style="[autoHeight]" v-else-if="style == 6">
          <view class="item">
            <view class="pic" :style="[imgGap]" @click="goDetail(picList[0])">
              <easy-loadimage
                mode="aspectFill"
                width="100%"
                height="100%"
                :image-src="picList[0].image"
                class="image"
                :borderRadius="imgBorderRadius"
              ></easy-loadimage>
            </view>
            <view class="pic" :style="[imgGap]" @click="goDetail(picList[1])">
              <easy-loadimage
                mode="aspectFill"
                width="100%"
                height="100%"
                :image-src="picList[1].image"
                class="image"
                :borderRadius="imgBorderRadius"
              ></easy-loadimage>
            </view>
          </view>
          <view class="item" :style="[imgGap]" @click="goDetail(picList[2])">
            <easy-loadimage
              mode="aspectFill"
              width="100%"
              height="100%"
              :image-src="picList[2].image"
              class="image"
              :borderRadius="imgBorderRadius"
            ></easy-loadimage>
          </view>
        </view>
        <view class="advert8" :style="[autoHeight]" v-else-if="style == 7">
          <view
            class="item"
            :style="[imgGap]"
            v-for="(item, index) in picList"
            @click="goDetail(item)"
            :key="index"
          >
            <easy-loadimage
              mode="aspectFill"
              width="100%"
              height="100%"
              :image-src="item.image"
              class="image"
              :borderRadius="imgBorderRadius"
            ></easy-loadimage>
          </view>
        </view>
        <view class="advert9" :style="[autoHeight]" v-else-if="style == 8">
          <view class="item">
            <view class="pic" :style="[imgGap]" @click="goDetail(picList[0])">
              <easy-loadimage
                mode="aspectFill"
                width="100%"
                height="100%"
                :image-src="picList[0].image"
                class="image"
                :borderRadius="imgBorderRadius"
              ></easy-loadimage>
            </view>
            <view class="pic" :style="[imgGap]" @click="goDetail(picList[1])">
              <easy-loadimage
                mode="aspectFill"
                width="100%"
                height="100%"
                :image-src="picList[1].image"
                class="image"
                :borderRadius="imgBorderRadius"
              ></easy-loadimage>
            </view>
          </view>
          <view class="item">
            <view class="pic" :style="[imgGap]" @click="goDetail(picList[2])">
              <easy-loadimage
                mode="aspectFill"
                width="100%"
                height="100%"
                :image-src="picList[2].image"
                class="image"
                :borderRadius="imgBorderRadius"
              ></easy-loadimage>
            </view>
            <view class="pic" :style="[imgGap]" @click="goDetail(picList[3])">
              <easy-loadimage
                mode="aspectFill"
                width="100%"
                height="100%"
                :image-src="picList[3].image"
                class="image"
                :borderRadius="imgBorderRadius"
              ></easy-loadimage>
            </view>
            <view class="pic" :style="[imgGap]" @click="goDetail(picList[4])">
              <easy-loadimage
                mode="aspectFill"
                width="100%"
                height="100%"
                :image-src="picList[4].image"
                class="image"
                :borderRadius="imgBorderRadius"
              ></easy-loadimage>
            </view>
          </view>
        </view>
        <view class="advert10" :style="[autoHeight]" v-else-if="style == 9">
          <view class="item" :style="[imgGap]" @click="goDetail(picList[0])">
            <easy-loadimage
              mode="aspectFill"
              width="100%"
              height="100%"
              :image-src="picList[0].image"
              class="image"
              :borderRadius="imgBorderRadius"
            ></easy-loadimage>
          </view>
          <view class="item">
            <view class="pic-wrap">
              <view class="pic" :style="[imgGap]" @click="goDetail(picList[1])">
                <easy-loadimage
                  mode="aspectFill"
                  width="100%"
                  height="100%"
                  :image-src="picList[1].image"
                  class="image"
                  :borderRadius="imgBorderRadius"
                ></easy-loadimage>
              </view>
            </view>
            <view class="pic-wrap">
              <view class="pic" :style="[imgGap]" @click="goDetail(picList[2])">
                <easy-loadimage
                  mode="aspectFill"
                  width="100%"
                  height="100%"
                  :image-src="picList[2].image"
                  class="image"
                  :borderRadius="imgBorderRadius"
                ></easy-loadimage>
              </view>
              <view class="pic" :style="[imgGap]" @click="goDetail(picList[3])">
                <easy-loadimage
                  mode="aspectFill"
                  width="100%"
                  height="100%"
                  :image-src="picList[3].image"
                  class="image"
                  :borderRadius="imgBorderRadius"
                ></easy-loadimage>
              </view>
            </view>
          </view>
        </view>
        <view class="advert11" v-else-if="style == 10">
          <view class="pic" :style="[imgGap]" @click="goDetail(picList[0])">
            <easy-loadimage
              mode="scaleToFill"
              width="100%"
              :height="imageH + 'px'"
              :image-src="picList[0].image"
              class="image"
              :borderRadius="imgBorderRadius"
            ></easy-loadimage>
          </view>
        </view>
        <view class="advert12" :style="[autoHeight]" v-else-if="style == 11">
          <template v-if="dataConfig.picStyle.docPicList.length">
            <view
              v-for="(item, index) in dataConfig.picStyle.docPicList"
              :key="index"
              :style="{
                top: `${(item.doc.startY / 375) * 100}%`,
                left: `${(item.doc.startX / 375) * 100}%`,
                width: `${(item.doc.w / 375) * 100}%`,
                height: `${(item.doc.h / 375) * 100}%`,
                borderWidth: `${dataConfig.imgConfig.val * 2}rpx`,
              }"
              class="item"
              @click="goDetail(item)"
            >
              <easy-loadimage
                mode="aspectFill"
                width="100%"
                height="100%"
                :imageSrc="item.img"
                class="image"
                :borderRadius="imgBorderRadius"
              ></easy-loadimage>
            </view>
          </template>
        </view>
      </view>
    </common-wrapper>
  </view>
</template>

<script setup>
import { ref, computed, nextTick, onMounted } from "vue";
import commonWrapper from "./commonWrapper.vue";

defineOptions({ name: "pictureCube" });

const props = defineProps({
  dataConfig: {
    type: Object,
    default: () => ({}),
  },
});

const picList = ref(props.dataConfig.picStyle.picList);
const style = ref(props.dataConfig.styleConfig.tabVal);
const imageH = ref(0);

const configData = computed(() => {
  return {
    ...props.dataConfig,
    paddingConfig: props.dataConfig.paddingConfig || {
      isAll: false,
      val: props.dataConfig.prConfig ? props.dataConfig.prConfig.val : 0,
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
      val: props.dataConfig.mbConfig ? props.dataConfig.mbConfig.val : 0,
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
const imgGap = computed(() => {
  return {
    borderWidth: `${props.dataConfig.imgConfig.val * 2}rpx`,
    borderRadius: imgBorderRadius.value,
    overflow: "hidden",
  };
});
const imgBorderRadius = computed(() => {
  let borderRadius = `${props.dataConfig.filletImg.val * 2}rpx`;
  if (props.dataConfig.filletImg.type) {
    borderRadius = `${props.dataConfig.filletImg.valList[0].val * 2}rpx ${
      props.dataConfig.filletImg.valList[1].val * 2
    }rpx ${props.dataConfig.filletImg.valList[3].val * 2}rpx ${
      props.dataConfig.filletImg.valList[2].val * 2
    }rpx`;
  }
  return borderRadius;
});
const autoHeight = computed(() => {
  let windowWidth = uni.getWindowInfo().windowWidth;
  return {
    height: windowWidth + "px",
  };
});
const horizontalPadding = computed(() => {
  const paddingConfig = configData.value.paddingConfig || {};
  if (!paddingConfig.isAll) {
    return (paddingConfig.val || 0) * 2;
  }
  const right = paddingConfig.valList && paddingConfig.valList[1]
    ? paddingConfig.valList[1].val || 0
    : 0;
  const left = paddingConfig.valList && paddingConfig.valList[3]
    ? paddingConfig.valList[3].val || 0
    : 0;
  return right + left;
});

onMounted(() => {
  computedHeight();
});

//替换安全域名
function setDomain(url) {
  url = url ? url.toString() : "";
  //本地调试打开,生产请注销
  if (url.indexOf("https://") > -1) return url;
  else return url.replace("http://", "https://");
}
function goDetail(url) {
  let urls = url.link;
  uni.navigateTo({
    url: urls,
    fail: (e) => {
      uni.switchTab({ url: urls });
    },
  });
  // this.$util.JumpPath(urls);
}
function computedHeight() {
  if (picList.value.length) {
    let windowWidth = uni.getWindowInfo().windowWidth;
    nextTick((e) => {
      if (style.value == 0) {
        imageH.value = windowWidth / 2;
      } else if ([1, 2].includes(style.value)) {
        uni.getImageInfo({
          src: setDomain(picList.value[0].image),
          success: (res) => {
            if (res && res.height > 0) {
              const imageWidth = style.value == 1
                ? 375
                : 250 - horizontalPadding.value;
              let height =
                res.height *
                (imageWidth / res.width);
              imageH.value = height;
            } else {
              const imageWidth = style.value == 1
                ? 375
                : 250 - horizontalPadding.value;
              imageH.value = imageWidth * 2;
            }
          },
          fail: function (error) {
            const imageWidth = style.value == 1
              ? 375
              : 250 - horizontalPadding.value;
            imageH.value = imageWidth * 2;
          },
        });
      } else if (style.value == 10) {
        uni.getImageInfo({
          src: setDomain(picList.value[0].image),
          success: (image) => {
            imageH.value = (image.height * windowWidth) / image.width;
          },
        });
      }
    });
  }
}
</script>

<style lang="scss">
.pageOn {
  border-radius: 24rpx !important;

  .advertItem01 {
    image {
      border-radius: 20rpx;
    }
  }

  .advertItem02 {
    .item {
      &:nth-child(1) {
        image {
          border-radius: 20rpx 0 0 20rpx;
        }
      }

      &:nth-child(2) {
        image {
          border-radius: 0 20rpx 20rpx 0;
        }
      }
    }
  }

  .advertItem03 {
    .item {
      &:nth-child(1) {
        image {
          border-radius: 20rpx 0 0 20rpx;
        }
      }

      &:nth-child(2) {
        image {
          border-radius: 0;
        }
      }

      &:nth-child(3) {
        image {
          border-radius: 0 20rpx 20rpx 0;
        }
      }
    }
  }

  .advertItem04 {
    .item {
      &:nth-child(1) {
        image {
          border-radius: 20rpx 0 0 20rpx;
        }
      }

      &:nth-child(2) {
        .pic {
          &:nth-child(1) {
            image {
              border-radius: 0 20rpx 0 0;
            }
          }

          &:nth-child(2) {
            image {
              border-radius: 0 0 20rpx 0;
            }
          }
        }
      }
    }
  }

  .advertItem05 {
    .item {
      &:nth-child(1) {
        image {
          border-radius: 20rpx 0 0 20rpx;
        }
      }

      &:nth-child(2) {
        image {
          border-radius: 0;
        }
      }

      &:nth-child(4) {
        image {
          border-radius: 0 20rpx 20rpx 0;
        }
      }
    }
  }

  .advertItem06 {
    .item {
      &:nth-child(1) {
        image {
          border-radius: 20rpx 0 0 0;
        }
      }

      &:nth-child(2) {
        image {
          border-radius: 0 20rpx 0 0;
        }
      }

      &:nth-child(3) {
        image {
          border-radius: 0 0 0 20rpx;
        }
      }

      &:nth-child(4) {
        image {
          border-radius: 0 0 20rpx 0;
        }
      }
    }
  }
}

.pictureCube {
  display: flex;

  .item {
    border-width: 0;
    border-style: solid;
    border-color: transparent;
  }

  .pic {
    border-width: 0;
    border-style: solid;
    border-color: transparent;
  }

  .advert1 {
    flex: 1;
    display: flex;
    flex-direction: column;

    .item {
      flex: 1;
      min-height: 0;
      border-style: solid;
    }

    .image {
      display: block;
      width: 100%;
      height: 100%;
    }
  }

  .advert2 {
    flex: 1;
    display: flex;

    .item {
      flex: 1;
      min-width: 0;
    }

    .image {
      display: block;
      width: 100%;
      height: 100%;
    }
  }

  .advert3 {
    flex: 1;
    display: flex;

    .item {
      flex: 1;
      min-width: 0;
    }

    .image {
      display: block;
      width: 100%;
      height: 100%;
    }
  }

  .advert4 {
    flex: 1;
    display: flex;
    flex-direction: column;

    .item {
      flex: 1;
      min-height: 0;

      &:nth-child(1) {
        display: flex;

        .pic {
          flex: 1;
          min-width: 0;
        }
      }
    }

    .image {
      display: block;
      width: 100%;
      height: 100%;
    }
  }

  .advert5 {
    flex: 1;
    display: flex;
    flex-direction: column;

    .item {
      flex: 1;
      min-height: 0;

      &:nth-child(2) {
        display: flex;

        .pic {
          flex: 1;
        }
      }
    }

    .image {
      display: block;
      width: 100%;
      height: 100%;
    }
  }

  .advert6 {
    flex: 1;
    display: flex;

    .item {
      flex: 1;
      min-width: 0;

      &:nth-child(2) {
        display: flex;
        flex-direction: column;

        .pic {
          flex: 1;
          min-height: 0;
        }
      }
    }

    .image {
      display: block;
      width: 100%;
      height: 100%;
    }
  }

  .advert7 {
    flex: 1;
    display: flex;

    .item {
      flex: 1;
      min-width: 0;

      &:nth-child(1) {
        display: flex;
        flex-direction: column;

        .pic {
          flex: 1;
          min-height: 0;
        }
      }
    }

    .image {
      display: block;
      width: 100%;
      height: 100%;
    }
  }

  .advert8 {
    flex: 1;
    display: flex;
    flex-wrap: wrap;

    .item {
      flex: 0 0 50%;
      min-width: 0;
    }

    .image {
      display: block;
      width: 100%;
      height: 100%;
    }
  }

  .advert9 {
    flex: 1;
    display: flex;
    flex-direction: column;

    .item {
      flex: 1;
      min-height: 0;
      display: flex;
    }

    .pic {
      flex: 1;
      min-width: 0;
    }

    .image {
      display: block;
      width: 100%;
      height: 100%;
    }
  }

  .advert10 {
    flex: 1;
    display: flex;

    .item {
      flex: 1;
      min-width: 0;

      &:nth-child(2) {
        display: flex;
        flex-direction: column;
      }
    }

    .pic-wrap {
      flex: 1;
      min-height: 0;
      display: flex;

      .pic {
        flex: 1;
        min-width: 0;
      }
    }

    .image {
      display: block;
      width: 100%;
      height: 100%;
    }
  }

  .advert11 {
    flex: 1;

    .pic {
      width: 100%;
      height: 100%;
    }

    .image {
      display: block;
      width: 100%;
      height: 100%;
    }
  }

  .advert12 {
    flex: 1;
    position: relative;

    .item {
      position: absolute;
      border-style: solid;
      border-color: transparent;
    }

    .image {
      display: block;
      width: 100%;
      height: 100%;
    }
  }

  .advertItem02 {
    // ::v-deep uni-image>img{
    // 	position: unset;
    // }
    width: 100%;

    .item {
      width: 50%;
      height: auto;

      image {
        width: 100%;
        height: 100%;
        display: block;
      }
    }
  }

  .advertItem03 {
    .item {
      width: 33.3333%;
    }
  }

  .advertItem04 {
    width: 100%;

    .item {
      width: 50%;
      height: 200px;

      .pic {
        width: 100%;
        height: 100px;
      }

      image {
        width: 100%;
        height: 100%;
        display: block;
      }
    }
  }

  .advertItem05 {
    .item {
      width: 25%;
    }
  }

  .advertItem06 {
    .item {
      width: 50%;
      height: 100px;
    }
  }
}
</style>
