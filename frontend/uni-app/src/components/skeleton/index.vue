<template>
  <view
    v-if="show"
    :style="{
      width: systemInfo.width + 'px',
      height: systemInfo.height + 'px',
      backgroundColor: bgcolor,
      position: 'absolute',
      left: 0,
      top: 0,
      zIndex: 9998,
    }"
  >
    <view
      v-for="(item, rect_idx) in skeletonRectLists"
      :key="rect_idx + 'rect'"
      :class="[currentLoading == 'chiaroscuro' ? 'chiaroscuro' : '']"
      :style="{
        width: item.width + 'px',
        height: item.height + 'px',
        backgroundColor: 'rgb(194, 207, 214,.3)',
        position: 'absolute',
        left: item.left + 'px',
        top: item.top + 'px',
      }"
    >
    </view>
    <view
      v-for="(item, circle_idx) in skeletonCircleLists"
      :key="circle_idx + 'circle'"
      :class="currentLoading == 'chiaroscuro' ? 'chiaroscuro' : ''"
      :style="{
        width: item.width + 'px',
        height: item.height + 'px',
        backgroundColor: 'rgb(194, 207, 214,.3)',
        borderRadius: item.width + 'px',
        position: 'absolute',
        left: item.left + 'px',
        top: item.top + 'px',
      }"
    >
    </view>
    <view class="spinbox" v-if="currentLoading == 'spin'">
      <view class="spin"></view>
    </view>
  </view>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";

defineOptions({ name: "skeleton" });

const props = defineProps({
  bgcolor: {
    type: String,
    value: "#FFF",
  },
  selector: {
    type: String,
    value: "skeleton",
  },
  loading: {
    type: String,
    value: "spin",
  },
  show: {
    type: Boolean,
    value: false,
  },
  isNodes: {
    type: Number,
    value: false,
  }, //控制什么时候开始抓取元素节点,只要数值改变就重新抓取
});

// ---------- data ----------
const loadingAni = ["spin", "chiaroscuro"];
const systemInfo = ref({});
const skeletonRectLists = ref([]);
const skeletonCircleLists = ref([]);

// ---------- computed ----------
// 原实现在 attachedAction 中直接给 prop loading 赋值做归一（非法值→'spin'，Vue2 允许改写 prop）。
// Vue3 下 prop 只读，改为 computed 归一，取值行为与原 mounted 后一致
const currentLoading = computed(() =>
  loadingAni.includes(props.loading) ? props.loading : "spin"
);

// ---------- watch ----------
watch(
  () => props.isNodes,
  (val) => {
    readyAction();
  }
);

// ---------- mounted ----------
onMounted(() => {
  attachedAction();
});

// ---------- methods ----------
function attachedAction() {
  //默认的首屏宽高，防止内容闪现
  const windowInfo = uni.getWindowInfo();
  systemInfo.value = {
    width: windowInfo.windowWidth,
    height: windowInfo.windowHeight,
  };
}

function readyAction() {
  //绘制背景
  uni
    .createSelectorQuery()
    .selectAll(`.${props.selector}`)
    .boundingClientRect(function (res) {
      if (res[0] && res[0].length > 0)
        systemInfo.value.height = res[0][0].height + res[0][0].top;
    })
    .exec();

  //绘制矩形
  rectHandle();

  //绘制圆形
  radiusHandle();
}
function rectHandle() {
  //绘制不带样式的节点
  uni
    .createSelectorQuery()
    .selectAll(`.${props.selector}-rect`)
    .boundingClientRect()
    .exec(function (res) {
      skeletonRectLists.value = res[0];
    });
}
function radiusHandle() {
  uni
    .createSelectorQuery()
    .selectAll(`.${props.selector}-radius`)
    .boundingClientRect()
    .exec(function (res) {
      skeletonCircleLists.value = res[0];
    });
}
</script>

<style>
.spinbox {
  position: fixed;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
  width: 100%;
  z-index: 9999;
}

.spin {
  display: inline-block;
  width: 64rpx;
  height: 64rpx;
}

.spin:after {
  content: " ";
  display: block;
  width: 46rpx;
  height: 46rpx;
  margin: 1rpx;
  border-radius: 50%;
  border: 5rpx solid #409eff;
  border-color: #409eff transparent #409eff transparent;
  animation: spin 1.2s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}

.chiaroscuro {
  width: 100%;
  height: 100%;
  background: rgb(194, 207, 214);
  animation-duration: 2s;
  animation-name: blink;
  animation-iteration-count: infinite;
}

@keyframes blink {
  0% {
    opacity: 0.4;
  }

  50% {
    opacity: 1;
  }

  100% {
    opacity: 0.4;
  }
}

@keyframes flush {
  0% {
    left: -100%;
  }

  50% {
    left: 0;
  }

  100% {
    left: 100%;
  }
}

.shine {
  animation: flush 2s linear infinite;
  position: absolute;
  top: 0;
  bottom: 0;
  width: 100%;
  background: linear-gradient(
    to left,
    rgba(255, 255, 255, 0) 0%,
    rgba(255, 255, 255, 0.85) 50%,
    rgba(255, 255, 255, 0) 100%
  );
}
</style>
