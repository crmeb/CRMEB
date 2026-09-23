<template>
  <!-- 轮播图 -->
  <view v-show="!isSortType">
    <common-wrapper :config="configData">
      <view
        class="swipers"
        :class="[docConfig ? 'square' : 'circular', imgConfig ? '' : 'fillet']"
        v-if="imgUrls.length && tabConfig"
      >
        <swiper
          :style="'height:' + (imageH + 10) + 'rpx;'"
          indicator-dots="true"
          :autoplay="true"
          :circular="circular"
          :interval="interval"
          :duration="duration"
          indicator-color="#E4E4E4"
          indicator-active-color="#E93323"
          :previous-margin="itemEdge + 'rpx'"
          :next-margin="itemEdge + 'rpx'"
          :current="swiperCur"
          @change="swiperChange"
        >
          <block v-for="(item, index) in imgUrls" :key="index">
            <swiper-item :class="{ active: index == swiperCur }">
              <navigator :render-link="false"
                :url="item.info[1].title"
                class="slide-navigator acea-row row-between-wrapper"
                hover-class="none"
              >
                <image
                  :src="item.img"
                  class="slide-image"
                  mode="widthFix"
                ></image>
              </navigator>
            </swiper-item>
          </block>
        </swiper>
      </view>
      <view v-if="!tabConfig">
        <navigator :render-link="false"
          :url="item.info[1].title"
          hover-class="none"
          class="advert"
          :class="imgConfig ? '' : 'fillet'"
          v-for="(item, index) in imgUrls"
          :key="index"
        >
          <image :src="item.img"></image>
        </navigator>
      </view>
    </common-wrapper>
  </view>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import commonWrapper from "./commonWrapper.vue";

defineOptions({ name: "swiper" });

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

const indicatorDots = ref(false);
const circular = ref(true);
const autoplay = ref(true);
const interval = ref(2500);
const duration = ref(500);
const swiperCur = ref(0);
const imgUrls = ref([]);
const docConfig = ref(props.dataConfig.docConfig.type);
const imgConfig = ref(props.dataConfig.imgConfig.type);
const itemEdge = ref(props.dataConfig.itemEdge.val);
const lrConfig = ref(props.dataConfig.lrConfig.val);
const mbConfig = ref(props.dataConfig.mbConfig.val);
const tabConfig = ref(props.dataConfig.tabConfig.tabVal);
const imageH = ref(0);

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

watch(imageH, (nVal, oVal) => {
  imageH.value = nVal;
});

imgUrls.value = props.dataConfig.swiperConfig.list;

onMounted(() => {
  uni.getImageInfo({
    src: setDomain(imgUrls.value[0].img),
    success: function (res) {
      imageH.value = res.height;
    },
  });
});

// swiper
function swiperChange(e) {
  swiperCur.value = e.detail.current;
}
//替换安全域名
function setDomain(url) {
  url = url ? url.toString() : "";
  //本地调试打开,生产请注销
  if (url.indexOf("https://") > -1) return url;
  else return url.replace("http://", "https://");
}
</script>

<style lang="scss">
.advert {
  box-sizing: border-box;
  image {
    width: 100%;
    box-sizing: border-box;
    display: block;
  }
}
.swipers {
  position: relative;
  width: 100%;
  margin: 0 auto;
  border-radius: 10rpx;
  /* 设置圆角 */
  &.fillet {
    border-radius: 10rpx;

    image {
      border-radius: 10rpx;
    }
  }

  swiper,
  .swiper-item,
  image {
    width: 100%;
  }
  image {
    transform: scale(0.93);
    transition: all 0.6s ease;
  }
  swiper-item.active {
    image {
      transform: scale(1);
    }
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
</style>
