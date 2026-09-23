<template>
  <common-wrapper :config="configData" v-if="bargList.length">
    <view class="box-content" :style="[boxContentStyle]">
      <!-- 砍价头部 -->
      <view
        class="w-full h-96 px-24 flex-between-center bg-cover"
        :style="[headerStyle]"
      >
        <view class="flex-y-center">
          <text
            class="fs-32 lh-44rpx fw-500"
            :style="[titleStyle]"
            v-if="titleConfig"
            >{{ titleTxtConfig }}</text
          >
          <image :src="titleImg" class="w-140 h-32" v-else></image>
          <text class="fs-28 text--w111-ccc px-20" :style="[dividerColor]"
            >|</text
          >
          <text class="fs-26 text--w111-999 lh-36rpx" :style="[tipsColor]">{{
            tipTxt
          }}</text>
        </view>
        <view
          class="flex-y-center fs-24 text--w111-999"
          :style="[headerBntColor]"
          @tap="goPage('/pages/activity/goods_bargain/index')"
        >
          <text>{{ rightBntTxt }}</text>
          <text
            class="iconfont icon-ic_rightarrow"
            :style="[headerBntColor]"
          ></text>
        </view>
      </view>
      <!-- 砍价列表 -->
      <!-- 单列 -->
      <view
        class="pt-32 pr-20 pb-32 pl-20"
        :style="[boxContentStyle]"
        v-if="goodStyleConfig == 0"
      >
        <view
          class="w-full flex justify-between item"
          v-for="(item, index) in bargList"
          :key="index"
          @tap="bargDetail(item)"
        >
          <easy-loadimage
            :image-src="item.image"
            width="280rpx"
            height="280rpx"
            :borderRadius="imgStyle"
          ></easy-loadimage>
          <view class="flex-1 flex-col pl-20 h-280 flex-column justify-between">
            <view class="w-full">
              <view
                class="w-full fs-28 h-80 lh-40rpx line2"
                :style="[productStyle]"
                v-if="checkboxInfo.includes(0)"
                >{{ item.title }}</view
              >
              <view
                class="flex-y-center mt-26"
                :style="[joinColor]"
                v-if="checkboxInfo.includes(1)"
              >
                <text class="iconfont icon-ic_fire fs-20"></text>
                <text class="fs-24 fw-500 pl-4" :style="[braginSuccess]"
                  >{{ item.people }}{{ $t(`人正在参与`) }}</text
                >
              </view>
            </view>
            <view class="flex justify-between items-end">
              <view class="flex-col">
                <baseMoney
                  :money="item.min_price"
                  symbolSize="24"
                  integerSize="32"
                  decimalSize="32"
                  weight
                  :color="priceColor"
                  v-if="checkboxInfo.includes(2)"
                ></baseMoney>
                <text
                  class="text-line fs-28 text--w111-999 pt-10 Regular line-price"
                  v-if="checkboxInfo.includes(3)"
                  :style="[otPriceColor]"
                  >¥{{ item.product_price }}</text
                >
              </view>
              <view
                class="w-144 h-56 rd-30rpx flex-center fs-24 text--w111-fff"
                v-if="!showBtn"
                :style="[btnBgColor]"
                >{{ $t(`参与砍价`) }}</view
              >
            </view>
          </view>
        </view>
      </view>
      <!-- 两列 -->
      <view
        class="grid-column-2 grid-gap-22rpx pt-32 pr-20 pb-32 pl-20"
        :style="[boxContentStyle]"
        v-if="goodStyleConfig == 1"
      >
        <view
          v-for="(item, index) in bargList"
          :key="index"
          @tap="bargDetail(item)"
        >
          <easy-loadimage
            :image-src="item.image"
            width="100%"
            height="324rpx"
            :borderRadius="imgStyle"
          ></easy-loadimage>
          <view
            class="w-full line1 mt-16 fs-28 lh-40rpx"
            :style="[productStyle]"
            v-if="checkboxInfo.includes(0)"
            >{{ item.title }}</view
          >
          <view class="flex justify-between items-end mt-10">
            <view class="flex-col">
              <baseMoney
                :money="item.min_price"
                symbolSize="24"
                integerSize="36"
                decimalSize="36"
                weight
                :color="priceColor"
                v-if="checkboxInfo.includes(2)"
              ></baseMoney>
              <text
                class="text-line fs-28 text--w111-999 pt-14 Regular line-price"
                v-if="checkboxInfo.includes(3)"
                :style="[otPriceColor]"
                >{{ $t(`¥`) }}{{ item.product_price }}</text
              >
            </view>
            <view
              class="w-144 h-56 rd-30rpx flex-center fs-24 bg--w111-E93323"
              v-if="!showBtn"
              :style="[btnBgColor]"
              >{{ $t(`参与砍价`) }}</view
            >
          </view>
        </view>
      </view>
      <!-- 三列 -->
      <view
        class="grid-column-3 grid-gap-18rpx pt-32 pr-20 pb-32 pl-20"
        :style="[boxContentStyle]"
        v-if="goodStyleConfig == 2"
      >
        <view
          v-for="(item, index) in bargList"
          :key="index"
          @tap="bargDetail(item)"
        >
          <easy-loadimage
            :image-src="item.image"
            width="100%"
            height="212rpx"
            :borderRadius="imgStyle"
          ></easy-loadimage>
          <view
            class="w-full line1 mt-16 fs-26"
            :style="[productStyle]"
            v-if="checkboxInfo.includes(0)"
            >{{ item.title }}</view
          >
          <view class="flex items-baseline mt-12">
            <baseMoney
              :money="item.min_price"
              symbolSize="26"
              integerSize="36"
              decimalSize="36"
              weight
              :color="priceColor"
              preFix="低至"
              preFixSize="22"
              :textColor="priceColor"
              v-if="checkboxInfo.includes(2)"
            ></baseMoney>
          </view>
          <view
            class="text-line fs-24 text--w111-999 Regular lh-32rpx"
            :style="[otPriceColor]"
            v-if="checkboxInfo.includes(3)"
            >{{ $t(`¥`) }}{{ item.product_price }}</view
          >
        </view>
      </view>
      <!-- 滑动 -->
      <scroll-view
        scroll-x="true"
        show-scrollbar="false"
        class="bargain-scroll w-full p-32"
        :style="[boxContentStyle]"
        v-if="goodStyleConfig == 3"
      >
        <view
          class="bargain-scroll-item"
          :class="{ 'ml-20': index }"
          v-for="(item, index) in bargList"
          :key="index"
          @tap="bargDetail(item)"
        >
          <easy-loadimage
            :image-src="item.image"
            width="224rpx"
            height="224rpx"
            :borderRadius="imgStyle"
          ></easy-loadimage>
          <view
            class="w-222 line1 mt-16 fs-26 mb-10"
            :style="[productStyle]"
            v-if="checkboxInfo.includes(0)"
            >{{ item.title }}</view
          >
          <text
            class="badge fs-20 mb-14"
            v-if="checkboxInfo.includes(1)"
            :style="[braginSuccess]"
            >{{ item.people }}{{ $t(`人正在参与`) }}</text
          >
          <view>
            <baseMoney
              :money="item.min_price"
              symbolSize="24"
              integerSize="36"
              decimalSize="36"
              weight
              :color="priceColor"
              v-if="checkboxInfo.includes(2)"
            ></baseMoney>
          </view>
          <view
            class="text-line fs-24 text--w111-999 Regular lh-32rpx"
            :style="[otPriceColor]"
            v-if="checkboxInfo.includes(3)"
            >{{ $t(`¥`) }}{{ item.product_price }}</view
          >
        </view>
      </scroll-view>
    </view>
  </common-wrapper>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import commonWrapper from "./commonWrapper.vue";
import { openBargainSubscribe } from "@/utils/subscribeMessage.js";
import { getBargainList as getBargainListApi } from "@/api/activity.js";
import { LIMIT } from "@/config/app";

defineOptions({ name: "bargain" });

const emit = defineEmits(["changeBarg"]);

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

const bargList = ref([]);

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
const boxContentStyle = computed(() => {
  let borderRadius = `0 0 ${props.dataConfig.fillet.val * 2}rpx ${
    props.dataConfig.fillet.val * 2
  }rpx`;
  if (props.dataConfig.fillet.type) {
    borderRadius = `0 0 ${props.dataConfig.fillet.valList[3].val * 2}rpx ${
      props.dataConfig.fillet.valList[2].val * 2
    }rpx`;
  }
  return {
    borderRadius,
    background: (() => {
      const mc = props.dataConfig.moduleColor;
      if (!mc || !mc.color || !mc.color.length) return "";
      const colors = mc.color.map((c) => c.item).filter((c) => c);
      return colors.length >= 2
        ? `linear-gradient(90deg, ${colors[0]} 0%, ${colors[colors.length - 1]} 100%)`
        : colors.length === 1 ? colors[0] : "";
    })(),
  };
});
/*商品模板*/
const goodStyleConfig = computed(() => {
  return props.dataConfig.goodStyleConfig.tabVal;
});
const styleConfig = computed(() => {
  return props.dataConfig.styleConfig.tabVal;
});
const headerStyle = computed(() => {
  let br = `${props.dataConfig.fillet.val * 2}rpx`,
    borderRadius = "",
    imgBgUrl = props.dataConfig.imgBgConfig.url;
  if (props.dataConfig.fillet.type) {
    borderRadius = `${props.dataConfig.fillet.valList[0].val * 2}rpx ${
      props.dataConfig.fillet.valList[1].val * 2
    }rpx 0 0`;
  } else {
    borderRadius = `${br} ${br} 0 0`;
  }
  let headerBgColor =
    props.dataConfig.headerBgColor && props.dataConfig.headerBgColor.color;
  let color1 =
    headerBgColor && headerBgColor[0] ? headerBgColor[0].item : "#F62C2C";
  let color2 =
    headerBgColor && headerBgColor[1] ? headerBgColor[1].item : "#F96E29";
  return {
    backgroundImage: styleConfig.value
      ? "url(" + imgBgUrl + ")"
      : `linear-gradient(90deg,${color1} 0%,${color2} 100%)`,
    borderRadius,
  };
});
const titleStyle = computed(() => {
  let titleText = props.dataConfig.titleText;
  return {
    fontStyle: !titleText.tabVal
      ? "normal"
      : titleText.tabList[titleText.tabVal].style,
    fontWeight: !titleText.tabVal ? "bold" : "normal",
    color: props.dataConfig.titleColor.color[0].item,
    fontSize: props.dataConfig.titleNumber.val * 2 + "rpx",
  };
});
/*标题是文本还是图片*/
const titleConfig = computed(() => {
  return props.dataConfig.titleConfig.tabVal;
});
/*标题文本*/
const titleTxtConfig = computed(() => {
  return props.dataConfig.titleTxtConfig.value;
});
/*标题图片*/
const titleImg = computed(() => {
  return styleConfig.value ? titleUrl.value : titleColorUrl.value;
});
const titleColorUrl = computed(() => {
  return props.dataConfig.imgColorConfig.url;
});
const titleUrl = computed(() => {
  return props.dataConfig.imgConfig.url;
});
/*标题提示文字*/
const tipsColor = computed(() => {
  return {
    color: styleConfig.value
      ? props.dataConfig.tipsColor.color[0].item
      : props.dataConfig.tipsColor2.color[0].item,
  };
});
/*分割线颜色*/
const dividerColor = computed(() => {
  return {
    color: props.dataConfig.dividerColor.color[0].item,
  };
});
/*头部提示语文本*/
const tipTxt = computed(() => {
  return props.dataConfig.tipTxtConfig.value;
});
/*头部按钮文本*/
const rightBntTxt = computed(() => {
  return props.dataConfig.rightBntConfig.value;
});
/*头部按钮样式*/
const headerBntColor = computed(() => {
  return {
    color: styleConfig.value
      ? props.dataConfig.headerBntColor.color[0].item
      : props.dataConfig.headerBntColor2.color[0].item,
    fontSize: `${props.dataConfig.bntNumber.val * 2}rpx`,
  };
});
/*商品图片圆角样式*/
const imgStyle = computed(() => {
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
/*商品名称样式*/
const productStyle = computed(() => {
  return {
    color: props.dataConfig.goodsNameColor.color[0].item,
    fontWeight: props.dataConfig.goodsName.tabVal ? "normal" : "bold",
  };
});
/* 展示信息 */
const checkboxInfo = computed(() => {
  return props.dataConfig.checkboxInfo.type;
});
/* 参与人数颜色 */
const joinColor = computed(() => {
  return {
    color: props.dataConfig.toneConfig.tabVal
      ? props.dataConfig.joinNumColor.color[0].item
      : "var(--view-theme)",
  };
});
/* 价格颜色 */
const priceColor = computed(() => {
  return props.dataConfig.toneConfig.tabVal
    ? props.dataConfig.bargainPriceColor.color[0].item
    : "var(--view-theme)";
});
/* 划线价颜色 */
const otPriceColor = computed(() => {
  return {
    color: props.dataConfig.goodsPriceColor.color[0].item,
  };
});
const showBtn = computed(() => {
  return props.dataConfig.bargainConfig.tabVal;
});
/* 按钮颜色 */
const btnBgColor = computed(() => {
  return {
    background: props.dataConfig.toneConfig.tabVal
      ? `linear-gradient(90deg,${props.dataConfig.goodsBntColor.color[1].item} 0%,${props.dataConfig.goodsBntColor.color[0].item} 100%)`
      : "linear-gradient(90deg, var(--view-theme) 0%, var(--view-gradient) 100%)",
    color: props.dataConfig.goodsBntTxtColor.color[0].item,
  };
});
/*砍价成功*/
const braginSuccess = computed(() => {
  let styleObject = {};
  if (props.dataConfig.toneConfig.tabVal) {
    styleObject["color"] = props.dataConfig.joinNumColor.color[0].item;
    styleObject["background"] = `linear-gradient(90deg,${props.dataConfig.joinBgColor.color[0].item} 0%,${props.dataConfig.joinBgColor.color[1].item} 100%)`;
  } else {
    styleObject["color"] = "var(--view-theme)";
    styleObject["background"] = "linear-gradient(90deg, var(--view-theme) 0%, var(--view-gradient) 100%)";
  }
  return styleObject;
});
/*商品数量*/
const numberConfig = computed(() => {
  return props.dataConfig.numberConfig.val;
});

onMounted(() => {
  getBargainList();
});

function goPage(url) {
  uni.navigateTo({
    url,
  });
}
// 砍价列表
function getBargainList() {
  let limit = LIMIT;
  // 砍价插件未安装/未启用或网络异常时静默隐藏区块，不弹错误提示
  getBargainListApi({
    page: 1,
    limit: numberConfig.value >= limit ? limit : numberConfig.value,
  }).then((res) => {
    bargList.value = res.data;
  }).catch(() => {});
}
function bargDetail(item) {
  // #ifndef MP
  emit("changeBarg", item);
  // #endif
  // #ifdef MP
  openBargainSubscribe().then((res) => {
    emit("changeBarg", item);
  });
  // #endif
}
</script>

<style lang="scss" scoped>
.Regular {
  font-family: "Regular";
}
.bg-cover {
  background-repeat: no-repeat;
  background-size: 100% 100%;
}
.item ~ .item {
  margin-top: 32rpx;
}
.badge {
  padding: 0 10rpx;
  height: 26rpx;
  background: linear-gradient(
    270deg,
    var(--view-theme) 0%,
    var(--view-gradient) 100%
  );
  border-radius: 20rpx;
  display: inline-flex;
  justify-content: center;
  align-items: center;
  vertical-align: middle;
}
.line-price {
  padding-left: 3px;
}
scroll-view {
  box-sizing: border-box;
}
.bargain-scroll {
  white-space: nowrap;

  .bargain-scroll-item {
    display: inline-block;
    width: 224rpx;
    vertical-align: top;
  }
}
</style>
