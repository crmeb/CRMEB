<template>
  <view class="product-info-diy" v-if="productData">
    <common-wrapper :config="configData">
      <view class="product-info-box" :class="'style-' + specStyle">
        <!-- 图片区域 -->
        <view class="image-wrap">
          <swiper
            class="swiper"
            :circular="true"
            :autoplay="!videoPlaying"
            :interval="3000"
            :duration="500"
            @change="swiperChange"
          >
            <swiper-item v-for="(item, index) in sliderImage" :key="index">
              <!-- 视频项 -->
              <view v-if="item.isVideo" class="video-item">
                <view v-show="!videoControls" class="video-wrap">
                  <video
                    id="productVideo"
                    :src="item.src"
                    object-fit="contain"
                    controls
                    style="width: 100%; height: 100%"
                    show-center-play-btn
                    show-mute-btn="true"
                    auto-pause-if-navigate
                    :custom-cache="false"
                    :enable-progress-gesture="false"
                    :poster="item.poster"
                    @play="onVideoPlay"
                    @pause="onVideoPause"
                  ></video>
                </view>
                <view class="poster" v-show="videoControls">
                  <image
                    class="image"
                    :src="item.poster"
                    mode="aspectFill"
                  ></image>
                </view>
                <view
                  class="play-btn"
                  v-show="videoControls"
                  @click.stop="playVideo"
                >
                  <image
                    class="icon"
                    src="@/static/images/stop.png"
                    mode="aspectFit"
                  ></image>
                </view>
              </view>
              <!-- 图片项 -->
              <image
                v-else
                :src="item"
                mode="aspectFill"
                class="slide-image"
              ></image>
            </swiper-item>
          </swiper>
          <!-- 镜像效果 -->
          <view class="mirror-wrap">
            <image
              :src="currentSlideImage"
              mode="aspectFill"
              class="mirror-image"
            ></image>
          </view>

          <!-- 指示器 -->
          <view
            class="indicators"
            :class="'pos-' + indicatorPosition"
            v-if="indicatorConfig.tabVal !== undefined"
          >
            <!-- 线段样式 -->
            <view v-if="indicatorConfig.tabVal === 0" class="indicator-line">
              <view
                class="line-item"
                v-for="(item, index) in sliderImage"
                :key="index"
                :class="{ active: currentSwiper === index }"
                :style="{
                  backgroundColor:
                    currentSwiper === index ? selectColor : defaultColor,
                }"
              ></view>
            </view>
            <!-- 点线样式 -->
            <view v-if="indicatorConfig.tabVal === 1" class="indicator-dot">
              <view
                class="dot-item"
                v-for="(item, index) in sliderImage"
                :key="index"
                :class="{ active: currentSwiper === index }"
                :style="{
                  backgroundColor:
                    currentSwiper === index ? selectColor : defaultColor,
                }"
              ></view>
            </view>
            <!-- 数字样式 -->
            <view v-if="indicatorConfig.tabVal === 2" class="indicator-number">
              <view class="num-box">
                <text class="current">{{ currentSwiper + 1 }}</text>
                <text>/</text>
                <text class="total">{{ sliderImage.length }}</text>
              </view>
            </view>
          </view>

          <!-- 规格样式 4: 图片内部底部 -->
          <view
            v-if="specStyle === 3 && skuList.length > 0"
            class="spec-style-4"
          >
            <scroll-view scroll-x="true" class="spec-list">
              <view
                class="spec-item"
                v-for="(item, index) in skuList"
                :key="index"
                :style="{
                  borderColor:
                    index === selectedIndex
                      ? specSelectedBorderColor
                      : 'transparent',
                  background:
                    index === selectedIndex ? specSelectedBgColor : '#777777',
                }"
                @click="changeSpec(item, index)"
              >
                <image :src="item.image" mode="aspectFill"></image>
                <view class="spec-info">
                  <view
                    class="name line2"
                    :style="{
                      color:
                        index === selectedIndex
                          ? specSelectedTextColor
                          : specUnselectedTextColor,
                    }"
                  >
                    {{ item.suk || $t(`规格`) }}
                  </view>
                </view>
              </view>
            </scroll-view>
            <view
              class="total-count"
              :style="{ color: specTextColor }"
              @click="showSpecModal"
            >
              <view>
                <view>{{ skuList.length }}{{ $t(`款`) }}</view>
                <view>{{ $t(`可选`) }}</view>
              </view>
              <text class="iconfont icon-jiantou"></text>
            </view>
          </view>
        </view>

        <!-- 信息区域 -->
        <view class="info-box" :style="{ background: infoBoxBg }">
          <!-- 规格样式 0 & 1: 顶部 -->
          <view
            v-if="(specStyle === 0 || specStyle === 1) && skuList.length > 0"
            class="spec-top-section"
            :class="'style-' + specStyle"
          >
            <!-- 样式 0: 小图列表 -->
            <scroll-view
              scroll-x="true"
              class="spec-list"
              v-if="specStyle === 0"
            >
              <view
                class="spec-item"
                v-for="(item, index) in skuList"
                :key="index"
                @click="changeSpec(item, index)"
              >
                <image
                  :src="item.image"
                  mode="aspectFill"
                  :style="{
                    borderColor:
                      index === selectedIndex ? specSelectedColor : '#eee',
                  }"
                ></image>
              </view>
            </scroll-view>
            <view
              v-if="specStyle === 0"
              class="total-count"
              :style="{ color: specTextColor }"
              @click="showSpecModal"
            >
              <view>
                <view>{{ skuList.length }}{{ $t(`款`) }}</view>
                <view>{{ $t(`可选`) }}</view>
              </view>
              <text class="iconfont icon-jiantou"></text>
            </view>
            <!-- 样式 1: 图文列表 -->
            <view class="spec-list-text-wrapper" v-if="specStyle === 1">
              <scroll-view scroll-x="true" class="spec-list-text">
                <view
                  class="spec-item"
                  v-for="(item, index) in skuList"
                  :key="index"
                  :class="{ active: index === selectedIndex }"
                  :style="{
                    background:
                      index === selectedIndex ? specSelectedBgColor : '#f5f5f5',
                    borderColor:
                      index === selectedIndex
                        ? specSelectedColor
                        : 'transparent',
                  }"
                  @click="changeSpec(item, index)"
                >
                  <image :src="item.image" mode="aspectFill"></image>
                  <text
                    class="name line1"
                    :style="{
                      color:
                        index === selectedIndex
                          ? specSelectedTextColor
                          : specUnselectedTextColor,
                    }"
                    >{{ item.suk }}</text
                  >
                </view>
                <view class="spacer"></view>
              </scroll-view>
              <view
                class="total-count"
                :style="{ color: specTextColor }"
                @click="showSpecModal"
              >
                <text>{{ $t(`共`) }}{{ skuList.length }}{{ $t(`款`) }}</text>
                <text class="iconfont icon-jiantou"></text>
              </view>
            </view>
          </view>

          <!-- 信息排序列表 -->
          <view
            class="info-item"
            v-for="(item, index) in sortList"
            :key="index"
          >
            <!-- 价格区域 -->
            <view
              v-if="item.name === 'price' && item.show"
              class="price-section"
            >
              <view class="price-row">
                <view
                  v-if="item.checkList.includes(0)"
                  class="main-price-wrap"
                  :style="{ color: finalPriceColor }"
                >
                  <view class="label">{{ $t(`到手价`) }}</view>
                  <view class="symbol">¥</view>
                  <view
                    class="price"
                    :style="{
                      fontSize: priceFontSize + 'px',
                      lineHeight: priceFontSize + 'px',
                    }"
                    >{{ displayInfo.real_price }}</view
                  >
                </view>
                <view
                  v-if="item.checkList.includes(1)"
                  class="ot-price-wrap"
                  :style="{ color: sellingPriceColor }"
                >
                  <text class="label">{{ $t(`售价`) }}</text>
                  <text class="price">¥{{ displayInfo.price }}</text>
                </view>
                <view
                  v-if="item.checkList.includes(2) && displayInfo.vip_price"
                  class="vip-price-wrap"
                >
                  <text class="badge">SVIP</text>
                  <text class="price">¥{{ displayInfo.vip_price }}</text>
                </view>
              </view>
            </view>

            <!-- 名称区域 -->
            <view v-if="item.name === 'name' && item.show" class="name-section">
              <view
                class="title"
                :style="{
                  color: titleColor,
                  fontSize: titleFontSize + 'px',
                  fontWeight: 'bold',
                }"
                >{{ productData.store_name }}</view
              >
              <!-- <view class="share" @click="emitShare">
                <text class="iconfont icon-fenxiang"></text>
              </view> -->
            </view>

            <!-- 数据区域 -->
            <view v-if="item.name === 'data' && item.show" class="data-section">
              <text
                v-if="item.checkList.includes(0)"
                :style="{ color: originalPriceColor, textDecoration: 'line-through' }"
                >划线价: ¥{{ displayInfo.ot_price }}</text
              >
              <text
                v-if="item.checkList.includes(1)"
                :style="{ color: stockColor }"
                >库存: {{ displayInfo.stock }}</text
              >
              <text
                v-if="item.checkList.includes(2)"
                :style="{ color: salesColor }"
                >销量: {{ displayInfo.fsales }}{{ displayInfo.unit_name }}</text
              >
            </view>

            <!-- 标签区域 -->
            <view
              v-if="
                item.name === 'tags' &&
                item.show &&
                productData.label_list &&
                productData.label_list.length
              "
              class="tags-section"
            >
              <BaseTag
                :text="tag.name"
                :color="tag.font_color"
                :background="tag.bg_color"
                :borderColor="tag.border_color"
                :circle="tag.border_color ? true : false"
                :imgSrc="tag.image"
                size="middle"
                v-for="(tag, tIndex) in productData.label_list"
                :key="tIndex"
              ></BaseTag>
            </view>
          </view>

          <!-- 规格样式 2: 底部 -->
          <view
            v-if="specStyle === 2 && skuList.length > 0"
            class="spec-bottom-section"
          >
            <view class="spec-list-wrapper">
              <scroll-view scroll-x="true" class="spec-list">
                <view
                  class="spec-item"
                  v-for="(item, index) in skuList"
                  :key="index"
                  @click="changeSpec(item, index)"
                  :style="{
                    borderColor:
                      index === selectedIndex
                        ? specSelectedBorderColor
                        : 'transparent',
                  }"
                >
                  <image :src="item.image" mode="aspectFill"></image>
                  <view
                    class="name line1"
                    :style="{
                      color:
                        index === selectedIndex
                          ? specSelectedTextColor
                          : specUnselectedTextColor,
                      background:
                        index === selectedIndex
                          ? specSelectedBgColor
                          : '#f5f5f5',
                    }"
                  >
                    {{ item.suk }}
                  </view>
                </view>
                <view class="spacer"></view>
              </scroll-view>
              <view class="total-count" @click="showSpecModal">
                <view>
                  <view>{{ skuList.length }}{{ $t(`款`) }}</view>
                  <view>{{ $t(`可选`) }}</view>
                </view>
                <text class="iconfont icon-jiantou"></text>
              </view>
            </view>
          </view>
          <view class="presell_count" v-if="productData.presale">
            <view>
              <view>{{ $t(`预售活动时间`) }}：</view>
              <view
                v-if="
                  productData.presale_start_time && productData.presale_end_time
                "
                class="presell_time"
              >
                <view class="iconfont icon-shijian1"></view>
                {{ productData.presale_start_time }}
                <span class="area_line">~</span>
                {{ productData.presale_end_time }}
              </view>
            </view>
            <view
              >{{ $t(`预售结束后`) }} {{ productData.presale_day }}
              {{ $t(`天内发货`) }}</view
            >
          </view>
        </view>
      </view>
    </common-wrapper>
  </view>
</template>

<script setup>
import { ref, computed, watch, nextTick, getCurrentInstance } from "vue";
import commonWrapper from "./commonWrapper.vue";
import BaseTag from "@/components/BaseTag.vue";

defineOptions({ name: "productInfo" });

const emit = defineEmits(["changeSpec", "showSpecModal", "share"]);

const props = defineProps({
  dataConfig: {
    type: Object,
    default: () => ({}),
  },
  productData: {
    type: Object,
    default: () => ({}),
  },
  priceData: {
    type: Object,
    default: () => ({}),
  },
  skuList: {
    type: Array,
    default: () => [],
  },
  attrValue: {
    type: String,
    default: "",
  },
  colorStyle: {
    type: Object,
    default: () => ({ theme: "#E93323" }),
  },
});

const currentSwiper = ref(0);
const selectedIndex = ref(0);
const hasUserSelected = ref(false);
const videoControls = ref(true);
const videoPlaying = ref(false);

const displayInfo = computed(() => {
  let price = props.priceData.price || "0.00";
  let real_price = props.priceData.real_price || "0.00";
  let ot_price = props.priceData.ot_price || "0.00";
  let vip_price = props.priceData.vip_price;
  let stock = props.productData.stock || 0;
  let fsales = props.productData.fsales || 0;
  let unit_name = props.productData.unit_name || "";

  if (props.skuList.length > 0 && selectedIndex.value < props.skuList.length) {
    let sku = props.skuList[selectedIndex.value];
    if (sku) {
      if (sku.price) price = sku.price;
      if (sku.real_price) price = sku.real_price;
      if (sku.ot_price) ot_price = sku.ot_price;
      if (sku.vip_price) vip_price = sku.vip_price;
      if (sku.stock || sku.stock === 0) stock = sku.stock;
    }
  }
  return {
    price,
    ot_price,
    vip_price,
    stock,
    fsales,
    unit_name,
    real_price,
  };
});
const sliderImage = computed(() => {
  const images = props.productData.slider_image || [];
  const videoLink = props.productData.video_link;
  if (videoLink) {
    // 有视频时，第一项为视频对象，后面是图片（跳过第一张图片作为封面）
    return [
      {
        isVideo: true,
        src: videoLink,
        poster: images[0] || "",
      },
      ...images.slice(1),
    ];
  }
  return images;
});
const currentSlideImage = computed(() => {
  const currentItem = sliderImage.value[currentSwiper.value];
  if (currentItem) {
    // 如果是视频，返回 poster；否则返回图片
    return currentItem.isVideo ? currentItem.poster : currentItem;
  }
  return "";
});
const configData = computed(() => {
  return {
    ...props.dataConfig,
    paddingConfig: props.dataConfig.paddingConfig || {
      isAll: false,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    },
    marginConfig: props.dataConfig.marginConfig || {
      isAll: false,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    },
  };
});
const infoBoxBg = computed(() => {
  const componentBgConfig = props.dataConfig.componentBgConfig;
  const colorConfig = componentBgConfig && componentBgConfig.colorConfig;
  const colors = colorConfig && colorConfig.color ? colorConfig.color.map((item) => item.item || "#ffffff") : [];
  if (!colors.length) return "linear-gradient(180deg, #ffffff 0%, #ffffff 100%)";
  if (colors.length === 1) return colors[0];
  const directionMap = ["90deg", "180deg", "135deg", "200deg"];
  const direction =
    componentBgConfig && componentBgConfig.colorDirection ? componentBgConfig.colorDirection.tabVal : 1;
  return `linear-gradient(${directionMap[direction] || "180deg"}, ${colors[0]} 0%, ${colors[1]} 100%)`;
});
const specStyle = computed(() => {
  return props.dataConfig.specStyle ? props.dataConfig.specStyle.tabVal : 0;
});
const specSettings = computed(() => {
  return props.dataConfig.specSettings || {};
});
const isCustomSpecTone = computed(() => {
  return (
    specSettings.value.colorTone && specSettings.value.colorTone.tabVal === 1
  );
});
const specSelectedColor = computed(() => {
  if (isCustomSpecTone.value) {
    return specSettings.value.selectedBorderColor &&
      specSettings.value.selectedBorderColor.color[0].item
      ? specSettings.value.selectedBorderColor.color[0].item
      : "#E93323";
  }
  return "var(--view-theme)";
});
const specTextColor = computed(() => {
  if (isCustomSpecTone.value) {
    return specSettings.value.textColor &&
      specSettings.value.textColor.color[0].item
      ? specSettings.value.textColor.color[0].item
      : "#666";
  }
  return "var(--view-theme)";
});
const specSelectedBorderColor = computed(() => {
  if (isCustomSpecTone.value) {
    return specSettings.value.selectedBorderColor &&
      specSettings.value.selectedBorderColor.color[0].item
      ? specSettings.value.selectedBorderColor.color[0].item
      : specSelectedColor.value;
  }
  return specSelectedColor.value;
});
const specSelectedTextColor = computed(() => {
  if (isCustomSpecTone.value) {
    return specSettings.value.selectedTextColor &&
      specSettings.value.selectedTextColor.color[0].item
      ? specSettings.value.selectedTextColor.color[0].item
      : specTextColor.value;
  }
  return specTextColor.value;
});
const specSelectedBgColor = computed(() => {
  if (isCustomSpecTone.value) {
    return specSettings.value.selectedBgColor &&
      specSettings.value.selectedBgColor.color[0].item
      ? specSettings.value.selectedBgColor.color[0].item
      : hexToRgba(props.colorStyle.theme || "#E93323", 0.1);
  }
  return specStyle.value === 3
    ? "#777777"
    : hexToRgba(props.colorStyle.theme || "#E93323", 0.1);
});
const specUnselectedTextColor = computed(() => {
  if (isCustomSpecTone.value) {
    return specSettings.value.unselectedTextColor &&
      specSettings.value.unselectedTextColor.color[0].item
      ? specSettings.value.unselectedTextColor.color[0].item
      : specStyle.value === 3
      ? "#ffffff"
      : "#333333";
  }
  return specStyle.value === 3 ? "#ffffff" : "#333333";
});
const titleColor = computed(() => {
  const config = props.dataConfig.titleConfig;
  if (!config) return "#333333";
  if (config.tabVal === 0) {
    return "var(--view-theme)";
  }
  return config.color && config.color.color[0].item
    ? config.color.color[0].item
    : "#333333";
});
const titleFontSize = computed(() => {
  return props.dataConfig.titleConfig && props.dataConfig.titleConfig.fontSize
    ? props.dataConfig.titleConfig.fontSize.val
    : 16;
});
const sortList = computed(() => {
  return props.dataConfig.sortList
    ? props.dataConfig.sortList.list
    : [
        { name: "price", show: true, checkList: [0, 1, 2] },
        { name: "name", show: true },
        { name: "data", show: true, checkList: [0, 1, 2] },
        { name: "tags", show: true },
      ];
});
const indicatorConfig = computed(() => {
  return props.dataConfig.indicatorConfig || {};
});
const selectColor = computed(() => {
  return indicatorConfig.value.selectColor
    ? indicatorConfig.value.selectColor.color[0].item
    : "#E93323";
});
const defaultColor = computed(() => {
  return indicatorConfig.value.defaultColor
    ? indicatorConfig.value.defaultColor.color[0].item
    : "#CCCCCC";
});
const indicatorPosition = computed(() => {
  if (indicatorConfig.value.tabVal === 0) return "center";
  const pos = indicatorConfig.value.positionVal;
  return pos === 0 ? "left" : pos === 2 ? "right" : "center";
});
const priceSettings = computed(() => {
  return props.dataConfig.priceSettings || {};
});
const dataSettings = computed(() => {
  return props.dataConfig.dataSettings || {};
});
const isCustomPriceTone = computed(() => {
  return (
    priceSettings.value.colorTone &&
    priceSettings.value.colorTone.tabVal === 1
  );
});
const finalPriceColor = computed(() => {
  if (isCustomPriceTone.value) {
    return priceSettings.value.finalPriceColor &&
      priceSettings.value.finalPriceColor.color[0].item
      ? priceSettings.value.finalPriceColor.color[0].item
      : "#E93323";
  }
  return "var(--view-theme)" || "#E93323";
});
const sellingPriceColor = computed(() => {
  if (isCustomPriceTone.value) {
    return priceSettings.value.sellingPriceColor &&
      priceSettings.value.sellingPriceColor.color[0].item
      ? priceSettings.value.sellingPriceColor.color[0].item
      : "#333333";
  }
  return "#333333";
});
const priceFontSize = computed(() => {
  return priceSettings.value.priceFontSize
    ? priceSettings.value.priceFontSize.val
    : 24;
});
const originalPriceColor = computed(() => {
  return dataSettings.value.originalPriceColor &&
    dataSettings.value.originalPriceColor.color[0].item
    ? dataSettings.value.originalPriceColor.color[0].item
    : "#999999";
});
const stockColor = computed(() => {
  return dataSettings.value.stockColor &&
    dataSettings.value.stockColor.color[0].item
    ? dataSettings.value.stockColor.color[0].item
    : "#999999";
});
const salesColor = computed(() => {
  return dataSettings.value.salesColor &&
    dataSettings.value.salesColor.color[0].item
    ? dataSettings.value.salesColor.color[0].item
    : "#999999";
});

const instance = getCurrentInstance();
function swiperChange(e) {
  currentSwiper.value = e.detail.current;
  // 切换到非视频页时暂停视频
  if (currentSwiper.value !== 0 || !props.productData.video_link) {
    videoControls.value = true;
    videoPlaying.value = false;
    // 暂停视频
    const videoContext = uni.createVideoContext("productVideo", instance.proxy);
    videoContext && videoContext.pause();
  }
}
function playVideo() {
  videoControls.value = false;
  videoPlaying.value = true;
  nextTick(() => {
    const videoContext = uni.createVideoContext("productVideo", instance.proxy);
    videoContext.play();
  });
}
function onVideoPlay() {
  videoPlaying.value = true;
}
function onVideoPause() {
  videoPlaying.value = false;
}
function changeSpec(item, index) {
  if (index !== undefined) {
    selectedIndex.value = index;
    hasUserSelected.value = true;
  }
  emit("changeSpec", item);
}
function syncSelectedIndex() {
  if (!props.skuList.length) return;
  const attrIndex = props.skuList.findIndex(
    (item) => item.suk === props.attrValue
  );
  if (attrIndex > -1) {
    selectedIndex.value = attrIndex;
    hasUserSelected.value = false;
    return;
  }
  if (
    hasUserSelected.value &&
    selectedIndex.value > -1 &&
    selectedIndex.value < props.skuList.length
  ) {
    return;
  }
  const stockIndex = props.skuList.findIndex((item) => Number(item.stock) > 0);
  selectedIndex.value = stockIndex > -1 ? stockIndex : 0;
}
function showSpecModal() {
  emit("showSpecModal");
}
function emitShare() {
  emit("share");
}
function hexToRgba(hex, opacity) {
  if (!hex) return "";
  let c;
  if (/^#([A-Fa-f0-9]{3}){1,2}$/.test(hex)) {
    c = hex.substring(1).split("");
    if (c.length == 3) {
      c = [c[0], c[0], c[1], c[1], c[2], c[2]];
    }
    c = "0x" + c.join("");
    return (
      "rgba(" +
      [(c >> 16) & 255, (c >> 8) & 255, c & 255].join(",") +
      "," +
      opacity +
      ")"
    );
  }
  return hex;
}

watch(
  () => props.skuList,
  () => {
    syncSelectedIndex();
  },
  { deep: true, immediate: true }
);
watch(() => props.attrValue, () => {
  syncSelectedIndex();
});
</script>

<style scoped lang="scss">
.product-info-box {
  &.style-3 {
    .image-wrap .indicators {
      bottom: 170rpx;
    }
  }

  .image-wrap {
    position: relative;
    width: 100%;
    height: 782rpx;

    .swiper {
      width: 100%;
      height: 750rpx;

      .slide-image {
        width: 100%;
        height: 100%;
      }

      .video-item {
        position: relative;
        width: 100%;
        height: 100%;

        .video-wrap {
          width: 100%;
          height: 100%;
        }

        .poster {
          position: absolute;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          z-index: 9;

          .image {
            width: 100%;
            height: 100%;
          }
        }

        .play-btn {
          position: absolute;
          top: 50%;
          left: 50%;
          width: 136rpx;
          height: 136rpx;
          margin-top: -68rpx;
          margin-left: -68rpx;
          z-index: 10;

          .icon {
            width: 100%;
            height: 100%;
          }
        }
      }
    }
    .mirror-wrap {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 32rpx;
      overflow: hidden;
      z-index: 1;

      .mirror-image {
        width: 100%;
        height: 750rpx;
        transform: scaleY(-1);
        filter: blur(2rpx);
      }
    }

    .indicators {
      position: absolute;
      bottom: 60rpx;
      width: 100%;
      display: flex;
      padding: 0 20rpx;
      box-sizing: border-box;
      z-index: 10;

      &.pos-left {
        justify-content: flex-start;
      }
      &.pos-center {
        justify-content: center;
      }
      &.pos-right {
        justify-content: flex-end;
      }

      .indicator-line {
        display: flex;
        align-items: center;
        width: 100%;
        .line-item {
          width: 20%;
          height: 4rpx;
          margin: 0 6rpx;
          border-radius: 4rpx;
          opacity: 0.5;
          &.active {
            opacity: 1;
          }
        }
      }

      .indicator-dot {
        display: flex;
        align-items: center;
        .dot-item {
          width: 12rpx;
          height: 12rpx;
          border-radius: 50%;
          margin: 0 6rpx;
          transition: all 0.3s;
          opacity: 0.5;
          &.active {
            width: 24rpx;
            border-radius: 6rpx;
            opacity: 1;
          }
        }
      }

      .indicator-number {
        .num-box {
          background: rgba(0, 0, 0, 0.3);
          color: #fff;
          font-size: 24rpx;
          padding: 4rpx 16rpx;
          border-radius: 20rpx;
          .current {
            font-size: 28rpx;
          }
        }
      }
    }

    .spec-style-4 {
      position: absolute;
      bottom: 10rpx;
      width: 100%;
      background: rgba(153, 153, 153, 0.6);
      padding: 20rpx 0 30rpx 20rpx;
      box-sizing: border-box;
      z-index: 10;
      display: flex;
      justify-content: space-between;
      .spec-list {
        flex: 1;
        min-width: 0;
        white-space: nowrap;
        width: 0;
        display: flex;
        align-items: center;
        .spec-item {
          display: inline-flex;
          align-items: center;
          margin-right: 20rpx;
          border: 2rpx solid transparent;
          border-radius: 16rpx;
          padding: 4rpx;
          background: #777777;

          image {
            width: 80rpx;
            height: 80rpx;
            border-radius: 8rpx;
            margin-right: 16rpx;
          }

          .spec-info {
            padding-right: 10rpx;
          }

          .name {
            font-size: 24rpx;
            color: #fff;
            max-width: 120rpx;
          }
        }

      }
      .total-count {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        font-size: 20rpx;
        margin-left: 10rpx;
        vertical-align: top;
        height: 88rpx;
        color: #fff;
        background: #777777;
        position: static;
        flex-shrink: 0;
        z-index: 10;
        padding: 0 20rpx;
        border-radius: 8rpx 0 0 8rpx;

        .iconfont {
          font-size: 20rpx;
        }
      }
    }
  }

  .info-box {
    position: relative;
    padding: 32rpx 32rpx 8rpx 32rpx;
    border-radius: 32rpx 32rpx 0 0;
    background: #fff;
    margin-top: -32rpx;
    z-index: 11;

    .spec-top-section {
      margin-bottom: 30rpx;
      position: relative;
      .spec-list {
        white-space: nowrap;
        width: 100%;

        .spec-item {
          display: inline-block;
          margin-right: 16rpx;

          image {
            width: 80rpx;
            height: 80rpx;
            border-radius: 8rpx;
            border: 2rpx solid #eee;
          }
        }
      }
      .total-count {
        position: absolute;
        right: 0;
        top: 0;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        font-size: 20rpx;
        vertical-align: top;
        height: 80rpx;
        margin-left: 10rpx;
        position: absolute;
        right: 0;
        background-color: #fff;
        z-index: 10;
        padding-left: 10rpx;

        .iconfont {
          font-size: 20rpx;
        }
      }

      .spec-list-text-wrapper {
        position: relative;

        .spec-list-text {
          white-space: nowrap;
          width: 100%;

          .spec-item {
            display: inline-flex;
            align-items: center;
            padding: 8rpx 16rpx;
            border-radius: 12rpx;
            margin-right: 16rpx;
            background: #f5f5f5;
            border: 2rpx solid transparent;
            image {
              width: 40rpx;
              height: 40rpx;
              border-radius: 50%;
              margin-right: 8rpx;
            }

            .name {
              font-size: 24rpx;
              color: #333;
            }
          }

          .spacer {
            display: inline-block;
            width: 100rpx;
            height: 1rpx;
          }
        }

        .total-count {
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 24rpx;
          position: absolute;
          right: 0;
          top: 0;
          bottom: 0;
          min-width: 100rpx;
          height: 60rpx;
          background-color: #fff;
          z-index: 10;
          padding-left: 10rpx;
          box-shadow: -10rpx 0 10rpx -5rpx rgba(0, 0, 0, 0.05);

          .iconfont {
            font-size: 20rpx;
          }
        }
      }
    }

    .info-item {
      margin-bottom: 16rpx;

      .price-section {
        .price-row {
          display: flex;
          align-items: center;
          flex-wrap: wrap;

          .main-price-wrap {
            display: flex;
            align-items: baseline;
            margin-right: 20rpx;
            font-weight: bold;

            .label {
              font-size: 24rpx;
              margin-right: 4rpx;
            }
            .symbol {
              font-size: 28rpx;
              line-height: 28rpx;
            }
          }

          .ot-price-wrap {
            display: flex;
            align-items: center;
            // text-decoration: line-through;
            font-size: 24rpx;
            color: #999;
            margin-right: 20rpx;
          }

          .vip-price-wrap {
            display: flex;
            align-items: center;
            height: 32rpx;

            .badge {
              background: #333;
              color: #f8dcae;
              font-size: 20rpx;
              padding: 0 8rpx;
              border-radius: 18rpx 0 0 18rpx;
              height: 100%;
              display: flex;
              align-items: center;
              font-weight: bold;
            }

            .price {
              background: #fff0d8;
              color: #333;
              font-size: 22rpx;
              padding: 0 10rpx 0 4rpx;
              border-radius: 0 18rpx 18rpx 0;
              height: 100%;
              display: flex;
              align-items: center;
              font-weight: bold;
            }
          }
        }
      }

      .name-section {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-top: 20rpx;

        .title {
          flex: 1;
          font-size: 32rpx;
          font-weight: bold;
          color: #333;
          line-height: 1.4;
        }

        .share {
          display: flex;
          flex-direction: column;
          align-items: center;
          margin-left: 20rpx;
          color: #666;
          font-size: 20rpx;

          .iconfont {
            font-size: 36rpx;
            margin-bottom: 4rpx;
          }
        }
      }

      .data-section {
        display: flex;
        justify-content: space-between;
        font-size: 24rpx;
        color: #999;
        margin-top: 20rpx;
      }

      .tags-section {
        display: flex;
        flex-wrap: wrap;
        margin-top: 20rpx;
      }
    }

    .spec-bottom-section {
      margin-top: 30rpx;

      .spec-list-wrapper {
        position: relative;
      }

      .spec-list {
        white-space: nowrap;
        width: 100%;

        .spec-item {
          display: inline-block;
          margin-right: 16rpx;
          text-align: center;
          border: 2rpx solid transparent;
          border-radius: 8rpx;
          overflow: hidden;
          image {
            width: 116rpx;
            height: 116rpx;
            border-radius: 8rpx;
          }

          .name {
            font-size: 24rpx;
            color: #333;
            background: #f5f5f5;
            padding: 6rpx 14rpx;
            font-size: 22rpx;
            max-width: 116rpx;
          }
        }

        .spacer {
          display: inline-block;
          width: 80rpx;
          height: 1rpx;
        }
      }

      .total-count {
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 20rpx;
        position: absolute;
        right: 0;
        top: 0;
        bottom: 0;
        width: 80rpx;
        background-color: #fff;
        z-index: 10;
        padding-left: 10rpx;

        .iconfont {
          font-size: 20rpx;
        }
      }
    }
  }
}

.line1 {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.presell_count {
  font-size: 24rpx;
  color: #999999;
  padding: 10rpx 0 20rpx 0;

  .presell_time {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    margin: 4rpx 0;

    .area_line {
      display: inline-block;
      margin: 0 6rpx;
    }
  }

  .icon-shijian1 {
    margin-right: 4rpx;
    flex-shrink: 0;
  }
}
</style>
