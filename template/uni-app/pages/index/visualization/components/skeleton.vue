<template>
  <view
    v-if="show"
    :style="{
      width: '100vw',
      height: '100vh',
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
      :class="[loading == 'chiaroscuro' ? 'chiaroscuro' : '']"
      :style="{
        width: item.width * 2 + 'rpx',
        height: item.height * 2 + 'rpx',
        backgroundColor: 'rgb(194, 207, 214,.3)',
        position: 'absolute',
        left: item.left * 2 + 'rpx',
        top: item.top * 2 + 'rpx',
      }"
    >
    </view>
    <view
      v-for="(item, circle_idx) in skeletonCircleLists"
      :key="circle_idx + 'circle'"
      :class="loading == 'chiaroscuro' ? 'chiaroscuro' : ''"
      :style="{
        width: item.width * 2 + 'rpx',
        height: item.height * 2 + 'rpx',
        backgroundColor: 'rgb(194, 207, 214,.3)',
        borderRadius: item.width * 2 + 'rpx',
        position: 'absolute',
        left: item.left * 2 + 'rpx',
        top: item.top * 2 + 'rpx',
        zIndex: 9998,
      }"
    >
    </view>
    <view class="spinbox" v-if="loading == 'spin'">
      <view class="spin"></view>
    </view>
  </view>
</template>

<script>
export default {
  name: "skeleton",
  props: {
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
      value: "chiaroscuro",
    },
    show: {
      type: Boolean,
      value: false,
    },
    isNodes: {
      type: Number,
      value: false,
    }, //控制什么时候开始抓取元素节点,只要数值改变就重新抓取
  },
  data() {
    return {
      loadingAni: ["spin", "chiaroscuro"],
      systemInfo: {},
      skeletonRectLists: [
        {
          bottom: 35,
          dataset: {},
          height: 25,
          id: "",
          left: 15,
          right: 65,
          top: 10,
          width: 50,
        },
        {
          bottom: 72,
          dataset: {},
          height: 30,
          id: "",
          left: 15,
          right: 360,
          top: 42,
          width: 345,
        },
        {
          bottom: 232,
          dataset: {},
          height: 145,
          id: "",
          left: 15,
          right: 360,
          top: 87,
          width: 345,
        },
        {
          bottom: 436,
          dataset: {},
          height: 30,
          id: "",
          left: 15,
          right: 360,
          top: 406,
          width: 345,
        },
        {
          bottom: 596,
          dataset: {},
          height: 150,
          id: "",
          left: 15,
          right: 183,
          top: 446,
          width: 168,
        },
        {
          bottom: 519,
          dataset: {},
          height: 73,
          id: "",
          left: 188,
          right: 360,
          top: 446,
          width: 172,
        },
        {
          bottom: 596,
          dataset: {},
          height: 73,
          id: "",
          left: 188,
          right: 360,
          top: 523,
          width: 172,
        },
        {
          bottom: 793,
          dataset: {},
          height: 177,
          id: "",
          left: 15,
          right: 360,
          top: 616,
          width: 345,
        },
        {
          bottom: 1680,
          dataset: {},
          height: 206,
          id: "",
          left: 15,
          right: 360,
          top: 1474,
          width: 345,
        },
      ],
      skeletonCircleLists: [
        {
          id: "",
          dataset: {},
          left: 27,
          right: 72,
          top: 245,
          bottom: 270,
          width: 45,
          height: 45,
        },
        {
          id: "",
          dataset: {},
          left: 96,
          right: 141,
          top: 245,
          bottom: 270,
          width: 45,
          height: 45,
        },
        {
          id: "",
          dataset: {},
          left: 165,
          right: 210,
          top: 245,
          bottom: 270,
          width: 45,
          height: 45,
        },
        {
          id: "",
          dataset: {},
          left: 234,
          right: 279,
          top: 245,
          bottom: 270,
          width: 45,
          height: 45,
        },
        {
          id: "",
          dataset: {},
          left: 303,
          right: 348,
          top: 245,
          bottom: 270,
          width: 45,
          height: 45,
        },
        {
          id: "",
          dataset: {},
          left: 27,
          right: 72,
          top: 327,
          bottom: 352,
          width: 45,
          height: 45,
        },
        {
          id: "",
          dataset: {},
          left: 96,
          right: 141,
          top: 327,
          bottom: 352,
          width: 45,
          height: 45,
        },
        {
          id: "",
          dataset: {},
          left: 165,
          right: 210,
          top: 327,
          bottom: 352,
          width: 45,
          height: 45,
        },
        {
          id: "",
          dataset: {},
          left: 234,
          right: 279,
          top: 327,
          bottom: 352,
          width: 45,
          height: 45,
        },
        {
          id: "",
          dataset: {},
          left: 303,
          right: 348,
          top: 327,
          bottom: 352,
          width: 45,
          height: 45,
        },
      ],
    };
  },
  watch: {
    isNodes(val) {
      // this.readyAction();
    },
  },
  mounted() {
    this.attachedAction();
  },
  methods: {
    attachedAction: function () {
      //默认的首屏宽高，防止内容闪现
      const systemInfo = uni.getSystemInfoSync();
      this.systemInfo = {
        width: systemInfo.windowWidth,
        height: systemInfo.windowHeight,
      };
      this.loading = this.loadingAni.includes(this.loading)
        ? this.loading
        : "spin";
    },
  },
};
</script>

<style>
.box {
  z-index: 1000;
}
.spinbox {
  position: fixed;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
  width: 100%;
  z-index: 10000;
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
