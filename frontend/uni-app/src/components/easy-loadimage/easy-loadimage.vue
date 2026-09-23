<template>
  <view class="easy-loadimage" :style="[boxStyle]" :id="uid">
    <image
      class="origin-img"
      :src="imageSrc"
      mode="aspectFill"
      v-if="loadImg && !isLoadError"
      v-show="showImg"
      :style="[imgStyle]"
      :class="{
        'no-transition': !openTransition,
        'show-transition': showTransition && openTransition,
      }"
      @load="handleImgLoad"
      @error="handleImgError"
    >
    </image>
    <image
      class="border-img"
      :src="borderSrc"
      mode="aspectFill"
      v-if="loadImg && !isLoadError && borderSrc"
      v-show="showImg"
      :style="[imgStyle]"
      :class="{
        'no-transition': !openTransition,
        'show-transition': showTransition && openTransition,
      }"
    >
    </image>
    <view class="loadfail-img" v-else-if="isLoadError"></view>
    <view
      :class="['loading-img', 'spin-circle', loadingMode]"
      v-show="!showImg && !isLoadError"
    ></view>
  </view>
</template>
<script setup>
import { ref, computed, onMounted, onBeforeUnmount, getCurrentInstance, nextTick } from "vue";
import { Throttle } from "@/utils/validate.js";

// 生成全局唯一id
function generateUUID() {
  return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, function (c) {
    let r = (Math.random() * 16) | 0,
      v = c == "x" ? r : (r & 0x3) | 0x8;
    return v.toString(16);
  });
}

const props = defineProps({
  imageSrc: {
    type: String,
    default: "",
  },
  borderSrc: {
    type: String,
    default: "",
  },
  mode: {
    type: String,
    default: "",
  },
  loadingMode: {
    type: String,
    default: "looming-gray",
  },
  openTransition: {
    type: Boolean,
    default: true,
  },
  viewHeight: {
    type: Number,
    default() {
      return uni.getWindowInfo().windowHeight;
    },
  },
  width: {
    type: String,
    default: "",
  },
  height: {
    type: String,
    default: "",
  },
  borderRadius: {
    type: String,
    default: "",
  },
});

const instance = getCurrentInstance();

// ---------- data ----------
// uid:'',
const uid = ref("uid-" + generateUUID());
const loadImg = ref(false);
const showImg = ref(false);
const isLoadError = ref(false);
const borderLoaded = ref(0);
const showTransition = ref(false);
// 原滚动懒加载监听逻辑原样保留（未重构为 IntersectionObserver）；
// 同一函数引用，保证 uni.$on / uni.$off 配对解绑
// 小程序端 Vue3 的 onMounted 早于渲染层首帧，此时 createSelectorQuery
// 查不到节点（data 为 null）；有限次延时重试直至节点就绪，
// 就绪后停止重试，后续仍由页面 scroll 事件驱动
let mountRetries = 0;
const MAX_MOUNT_RETRIES = 6;

const scrollFn = Throttle(function () {
  // 加载img时才执行滚动监听判断是否可加载
  if (loadImg.value || isLoadError.value) return;
  const id = uid.value;
  const query = uni.createSelectorQuery().in(instance?.proxy);
  query
    .select("#" + id)
    .boundingClientRect((data) => {
      if (!data) {
        if (mountRetries < MAX_MOUNT_RETRIES) {
          mountRetries++;
          setTimeout(onScroll, 150);
        }
        return;
      }
      mountRetries = MAX_MOUNT_RETRIES;
      if (data.top - props.viewHeight < 0) {
        loadImg.value = !!props.imageSrc;
        isLoadError.value = !loadImg.value;
      }
    })
    .exec();
}, 200);

// ---------- computed ----------
const boxStyle = computed(() => {
  return {
    width: props.width,
    height: props.height,
    borderRadius: props.borderRadius,
  };
});
const imgStyle = computed(() => {
  return {
    borderRadius: props.borderRadius,
  };
});

// ---------- methods ----------
function init() {
  nextTick(onScroll);
}
function handleBorderLoad() {
  borderLoaded.value = 1;
}
function handleBorderError() {
  borderLoaded.value = 2;
}
function handleImgLoad(e) {
  showImg.value = true;
  // nextTick(function(){
  //     showTransition.value = true
  // })
  setTimeout(() => {
    showTransition.value = true;
  }, 50);
}
function handleImgError(e) {
  isLoadError.value = true;
}
function onScroll() {
  scrollFn();
}

// ---------- mounted ----------
onMounted(() => {
  init();
  uni.$on("scroll", scrollFn);
  onScroll();
});
onBeforeUnmount(() => {
  uni.$off("scroll", scrollFn);
});
</script>

<style scoped>
.easy-loadimage {
  width: 100%;
  height: 100%;
  position: relative;
  overflow: hidden;
}

.border-img {
  position: absolute;
  width: 100%;
  height: 100%;
  /* max-height: 360rpx; */
  top: 0;
  left: 0;
}

/* 官方优化图片tips */
image {
  will-change: transform;
  overflow: hidden;
}

/* 渐变过渡效果处理 */
image.origin-img {
  width: 100%;
  height: 100%;
  opacity: 0.3;
  /* max-height: 360rpx; */
}

image.origin-img.show-transition {
  transition: opacity 0.5s;
  opacity: 1;
}

image.origin-img.no-transition {
  opacity: 1;
}

/* 渐变过渡效果处理 */
image.border-img {
  width: 100%;
  height: 100%;
  opacity: 0.3;
  /* max-height: 360rpx; */
}

image.border-img.show-transition {
  transition: opacity 0.5s;
  opacity: 1;
}

image.border-img.no-transition {
  opacity: 1;
}

/* 加载失败、加载中的占位图样式控制 */
.loadfail-img {
  height: 100%;
  background: url("@/static/easy-loadimage/loadfail.png") no-repeat center;
  background-size: 50%;
}

.loading-img {
  height: 100%;
}

/* 转圈 */
.spin-circle {
  background: url("@/static/easy-loadimage/loading.png") no-repeat center;
  background-size: 60%;
}

/* 动态灰色若隐若现 */
.looming-gray {
  animation: looming-gray 1s infinite linear;
  background-color: #e3e3e3;
  border-radius: 12rpx;
}

@keyframes looming-gray {
  0% {
    background-color: #e3e3e3aa;
  }

  50% {
    background-color: #e3e3e3;
  }

  100% {
    background-color: #e3e3e3aa;
  }
}

/* 骨架屏1 */
.skeleton-1 {
  background-color: #e3e3e3;
  background-image: linear-gradient(
    100deg,
    rgba(255, 255, 255, 0),
    rgba(255, 255, 255, 0.2) 50%,
    rgba(255, 255, 255, 0) 80%
  );
  background-size: 100rpx 100%;
  background-repeat: repeat-y;
  background-position: 0 0;
  animation: skeleton-1 0.6s infinite;
}

@keyframes skeleton-1 {
  to {
    background-position: 200% 0;
  }
}

/* 骨架屏2 */
.skeleton-2 {
  background-image: linear-gradient(
    -90deg,
    #fefefe 0%,
    #e6e6e6 50%,
    #fefefe 100%
  );
  background-size: 400% 400%;
  background-position: 0 0;
  animation: skeleton-2 1.2s ease-in-out infinite;
}

@keyframes skeleton-2 {
  to {
    background-position: -135% 0;
  }
}
</style>
