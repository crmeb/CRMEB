<template>
  <!-- 商品分类 -->
  <view>
    <!-- #ifdef MP || APP-PLUS -->
    <view
      v-if="isFirst"
      :style="{ height: statusBarHeight + 'px', background: '#ffffff' }"
    ></view>
    <!-- #endif -->
    <view
      :class="{
        tabNav1: dataConfig.styleConfig.tabVal == 0,
        tabNav2: dataConfig.styleConfig.tabVal == 1,
        tabNav3: dataConfig.styleConfig.tabVal == 2,
        isFixed: isFixed,
        on: isFixed && special,
      }"
    >
      <common-wrapper :config="configData">
        <view :style="[tabNavBgColor]">
          <scroll-view
            scroll-x="true"
            :scroll-left="tabLeft"
            scroll-with-animation
          >
            <view class="list acea-row row-middle">
              <view
                v-for="(item, index) in tabListConfig"
                :key="index"
                :class="{ on: index == tabClick }"
                :style="[index == tabClick ? textStyle : {}]"
                class="item"
                @click="longClick(item, index)"
              >
                {{ item.text.val }}
                <text
                  v-if="dataConfig.styleConfig.tabVal != 2"
                  class="line"
                  :style="[lineStyle]"
                ></text>
              </view>
            </view>
          </scroll-view>
        </view>
      </common-wrapper>
    </view>
    <view v-if="isFixed" style="height: 80rpx"></view>
  </view>
</template>

<script setup>
import { ref, computed } from "vue";
import commonWrapper from "./commonWrapper.vue";
import { getCategoryList } from "@/api/store.js";
import { getCategoryVersion } from "@/api/public.js";

defineOptions({ name: "tabNav" });

const emit = defineEmits(["bindSortId"]);

const props = defineProps({
  dataConfig: {
    type: Object,
    default: () => ({}),
  },
  isFixed: {
    type: [Boolean, String, Number],
    default: false,
  },
  fromType: {
    type: Number,
    default: 0,
  },
  isFirst: {
    type: Boolean,
    default: false,
  },
  special: {
    type: Number,
    default: 0,
  },
});

const tabTitle = ref([]);
const tabLeft = ref(0);
const isWidth = ref(0); //每个导航栏占位
const tabClick = ref(0); //导航栏被点击
const isLeft = ref(0); //导航栏下划线位置
const fixedTop = ref(0);
const isTop = ref(0);
const navHeight = ref(45);
const tabList = ref(45);
const statusBarHeight = ref(0);

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
const lineStyle = computed(() => {
  let styleObject = {};
  if (props.dataConfig.toneConfig.tabVal) {
    switch (props.dataConfig.styleConfig.tabVal) {
      case 0:
        styleObject[
          "background"
        ] = `linear-gradient(90deg, ${props.dataConfig.decorateColor.color[0].item} 0%, ${props.dataConfig.decorateColor.color[1].item} 100%)`;
        break;
      case 1:
        styleObject["border-bottom-color"] =
          props.dataConfig.decorateColor2.color[0].item;
        break;
    }
  }
  return styleObject;
});
const textStyle = computed(() => {
  let styleObject = {};
  if (props.dataConfig.toneConfig.tabVal) {
    switch (props.dataConfig.styleConfig.tabVal) {
      case 0:
        styleObject["color"] = props.dataConfig.textColor.color[0].item;
        break;
      case 1:
        styleObject["color"] = props.dataConfig.textColor2.color[0].item;
        break;
      case 2:
        styleObject[
          "background"
        ] = `linear-gradient(90deg, ${props.dataConfig.decorateColor.color[0].item} 0%, ${props.dataConfig.decorateColor.color[1].item} 100%)`;
        styleObject["color"] = props.dataConfig.textColor3.color[0].item;
        break;
    }
  }
  return styleObject;
});
const tabNavBgColor = computed(() => {
  let borderRadius = `${props.dataConfig.fillet.val * 2}rpx`;
  if (props.dataConfig.fillet.type) {
    borderRadius = `${props.dataConfig.fillet.valList[0].val * 2}rpx ${
      props.dataConfig.fillet.valList[1].val * 2
    }rpx ${props.dataConfig.fillet.valList[2].val * 2}rpx ${
      props.dataConfig.fillet.valList[3].val * 2
    }rpx`;
  }
  return {
    "border-radius": borderRadius,
    background: `linear-gradient(90deg, ${props.dataConfig.moduleColor.color[0].item} 0%, ${props.dataConfig.moduleColor.color[1].item} 100%)`,
  };
});
// tabNavStyle() {
//   return {
//     padding: `${this.dataConfig.topConfig.val * 2}rpx ${
//       this.dataConfig.prConfig.val * 2
//     }rpx ${this.dataConfig.bottomConfig.val * 2}rpx`,
//     "margin-top": `${this.dataConfig.mbConfig.val * 2}rpx`,
//   };
// },
const tabListConfig = computed(() => {
  let tabList = props.dataConfig.tabListConfig.list;
  tabList.unshift({
    classPage: {
      id: 0,
    },
    dataType: {
      tabVal: 0,
    },
    microPage: {
      id: 0,
    },
    text: {
      val: "首页",
    },
  });
  return tabList;
});

// created 逻辑
getAllCategory();
// 获取设备宽度及状态栏高度
uni.getSystemInfo({
  success(e) {
    isWidth.value = e.windowWidth / 5;
    statusBarHeight.value = e.statusBarHeight || 0;
  },
});

// 导航栏点击
function longClick(item, index) {
  if (tabTitle.value.length > 5) {
    tabLeft.value = (index - 2) * isWidth.value; //设置下划线位置
  }
  tabClick.value = index; //设置导航点击了哪一个
  isLeft.value = index * isWidth.value; //设置下划线位置
  let data = {
    type: item.dataType.tabVal, //0 商品分类 1 微页面
    microPage: item.microPage.id,
    classPage: item.classPage.id,
  };
  emit("bindSortId", item, data);
}
function setCategory(data) {
  data.unshift({
    id: -99,
    text:{
      val: "首页"
    }
  });
  tabTitle.value = data;
  // #ifdef MP || APP-PLUS
  isTop.value = uni.getWindowInfo().statusBarHeight + 43 + "px";
  // #endif
  // #ifdef H5
  isTop.value = 0;
  // #endif
}
function getCategory() {
  getCategoryList().then((res) => {
    uni.setStorageSync("category", JSON.stringify(res.data));
    setCategory(res.data);
  });
}
// 获取导航
function getAllCategory() {
  let category = uni.getStorageSync("category");
  getCategory();
}
</script>

<style lang="scss">
.tabNav1 {
  &.isFixed {
    z-index: 45;
    position: fixed;
    left: 0;
    width: 100%;
    /* #ifdef H5 */
    top: 0;
    /* #endif */
  }
  &.on {
    top: 0;
  }

  .list {
    flex-wrap: nowrap;
    height: 80rpx;
    margin-left: 24rpx;
  }

  .item {
    position: relative;
    z-index: 2;
    flex-shrink: 0;
    height: 80rpx;
    margin: 0 55rpx 0 0;
    font-size: 28rpx;
    line-height: 80rpx;
    color: #333333;

    &:last-child {
      margin: 0 24rpx 0 0;
    }
  }

  .on {
    font-weight: bold;
    font-size: 32rpx;

    .line {
      position: absolute;
      bottom: 22rpx;
      left: 50%;
      z-index: -1;
      width: 100%;
      height: 8rpx;
      border-radius: 4rpx;
      background: linear-gradient(
        90deg,
        var(--view-theme) 0%,
        var(--view-gradient) 100%
      );
      transform: translateX(-50%);
    }
  }
}

.tabNav2 {
  .list {
    flex-wrap: nowrap;
    height: 80rpx;
    margin-left: 24rpx;
  }

  .item {
    position: relative;
    z-index: 2;
    flex-shrink: 0;
    height: 80rpx;
    margin: 0 55rpx 0 0;
    font-size: 28rpx;
    line-height: 80rpx;
    color: #333333;

    &:last-child {
      margin: 0 24rpx 0 0;
    }
  }

  .on {
    font-weight: bold;
    font-size: 32rpx;
    color: var(--view-theme);

    .line {
      position: absolute;
      bottom: 8rpx;
      left: 50%;
      z-index: -1;
      width: 64rpx;
      height: 64rpx;
      border: 4rpx solid var(--view-theme);
      border-top-color: transparent;
      border-right-color: transparent;
      border-left-color: transparent;
      border-radius: 50%;
      transform: translateX(-50%);
    }
  }
}

.tabNav3 {
  .list {
    flex-wrap: nowrap;
    height: 80rpx;
    margin-left: 15rpx;
  }

  .item {
    position: relative;
    flex-shrink: 0;
    height: 80rpx;
    padding: 0 20rpx;
    margin: 0 15rpx 0 0;
    font-size: 28rpx;
    line-height: 80rpx;
    color: #333333;
  }

  .on {
    height: 48rpx;
    border-radius: 24rpx;
    background: linear-gradient(
      90deg,
      var(--view-theme) 0%,
      var(--view-gradient) 100%
    );
    text-align: center;
    font-size: 26rpx;
    line-height: 48rpx;
    color: #ffffff;
  }
}
</style>
