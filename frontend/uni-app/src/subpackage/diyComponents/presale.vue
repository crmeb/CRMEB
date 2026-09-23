<template>
  <view v-if="productList.length">
    <common-wrapper :config="configData">
      <view>
        <!-- 预售头部 -->
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
            <text class="fs-28 text--w111-ccc px-16" :style="[dividerColor]"
              >|</text
            >
            <text class="fs-26 text--w111-999 lh-36rpx" :style="[tipsColor]">{{
              tipTxt
            }}</text>
          </view>
          <view
            class="flex-y-center fs-24 text--w111-999"
            :style="[headerBntColor]"
            @tap="goPage('/pages/activity/presell/index')"
          >
            <text>{{ rightBntTxt }}</text>
            <text
              class="iconfont icon-ic_rightarrow fs-24"
              :style="[headerBntColor]"
            ></text>
          </view>
        </view>
        <!-- 预售列表 -->
        <!-- 单列 -->
        <view
          class="pt-32 pr-20 pb-32 pl-20 bg--w111-fff"
          :style="[boxContentStyle]"
          v-if="goodStyleConfig == 0"
        >
          <view
            class="w-full flex justify-between item"
            v-for="(item, index) in productList"
            :key="index"
            @tap="goDetails(item)"
          >
            <easy-loadimage
              :image-src="item.image"
              width="240rpx"
              height="240rpx"
              :borderRadius="imgStyle"
            ></easy-loadimage>
            <view class="flex-1 flex-col justify-between pl-20 h-240">
              <view
                class="w-full fs-28 h-80 lh-40rpx line2"
                :style="[productStyle]"
                v-if="checkboxInfo.includes(0)"
                >{{ item.store_name }}</view
              >
              <view
                class="flex w-full h-68 rd-8rpx relative"
                :style="{ background: dataConfig.goodsBntColor.color[0].item }"
                v-if="!showBtn"
              >
                <view
                  class="flex-y-center pl-20 fs-22 btn-left"
                  :style="{ color: dataConfig.goodsBntColor.color[0].item }"
                >
                  <!--  v-if="checkboxInfo.includes(2)" -->
                  <baseMoney
                    :money="item.price"
                    symbolSize="26"
                    integerSize="40"
                    decimalSize="26"
                    weight
                    preFix="预售:"
                    preFixSize="22"
                    :textColor="priceColor"
                    :color="priceColor"
                  ></baseMoney>
                </view>
                <view
                  class="flex-center fs-26 fw-bold text--w111-fff btn-right"
                  :style="[btnBgColor]"
                  >{{ filterType(item.presale_pay_status) }}</view
                >
                <image
                  class="shandian"
                  src="@/static/images/presale.png"
                ></image>
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
            v-for="(item, index) in productList"
            :key="index"
            @tap="goDetails(item)"
          >
            <easy-loadimage
              :image-src="item.image"
              width="100%"
              height="324rpx"
              :borderRadius="imgStyle"
            ></easy-loadimage>
            <view
              class="w-full line2 mt-16 fs-28 lh-40rpx"
              :style="[productStyle]"
              v-if="checkboxInfo.includes(0)"
              >{{ item.store_name }}</view
            >
            <view class="flex justify-between items-end mt-10">
              <view class="flex-col">
                <baseMoney
                  :money="item.price"
                  symbolSize="24"
                  integerSize="36"
                  decimalSize="36"
                  weight
                  :color="priceColor"
                  v-if="checkboxInfo.includes(2)"
                ></baseMoney>
                <text
                  class="text-line fs-26 text--w111-999 pt-14 Regular"
                  :style="[otPriceColor]"
                  >{{ $t(`¥`) }}{{ item.ot_price }}</text
                >
              </view>
              <view
                class="w-144 h-56 rd-30rpx flex-center fs-24 text--w111-fff bg--w111-E93323"
                v-if="!showBtn"
                :style="[btnBgColor]"
                >{{ $t(`去预定`) }}</view
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
            v-for="(item, index) in productList"
            :key="index"
            @tap="goDetails(item)"
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
              >{{ item.store_name }}</view
            >
            <view class="flex items-baseline mt-12">
              <baseMoney
                :money="item.price"
                symbolSize="24"
                integerSize="36"
                decimalSize="36"
                weight
                :color="priceColor"
                preFix="预售价"
                preFixSize="22"
                :textColor="priceColor"
                v-if="checkboxInfo.includes(2)"
              ></baseMoney>
            </view>
            <view
              class="text-line fs-24 text--w111-999 Regular lh-32rpx"
              :style="[otPriceColor]"
              v-if="checkboxInfo.includes(3)"
              >{{ $t(`¥`) }}{{ item.ot_price }}</view
            >
          </view>
        </view>
        <!-- 滑动 -->
        <scroll-view
          scroll-x="true"
          show-scrollbar="false"
          class="white-nowrap vertical-middle w-full pt-32 pb-32"
          :style="[boxContentStyle]"
          v-if="goodStyleConfig == 3"
        >
          <view
            class="inline-block ml-20"
            v-for="(item, index) in productList"
            :key="index"
            @tap="goDetails(item)"
          >
            <easy-loadimage
              :image-src="item.image"
              width="224rpx"
              height="224rpx"
              :borderRadius="imgStyle"
            ></easy-loadimage>
            <view
              class="w-222 line1 mt-16 fs-26"
              :style="[productStyle]"
              v-if="checkboxInfo.includes(0)"
              >{{ item.store_name }}</view
            >
            <view class="flex items-baseline mt-12">
              <baseMoney
                :money="item.price"
                symbolSize="24"
                integerSize="36"
                decimalSize="36"
                weight
                :color="priceColor"
                preFix="预售价"
                preFixSize="22"
                :textColor="priceColor"
                v-if="checkboxInfo.includes(2)"
              ></baseMoney>
            </view>
            <view
              class="text-line fs-24 text--w111-999 Regular lh-32rpx"
              :style="[otPriceColor]"
              v-if="checkboxInfo.includes(3)"
              >{{ $t(`¥`) }}{{ item.ot_price }}</view
            >
          </view>
        </scroll-view>
      </view>
    </common-wrapper>
  </view>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import commonWrapper from "./commonWrapper.vue";
import { getPresellList } from "@/api/activity.js"; // 原 getAdvancellList 为拼写笔误（advance/list 预售列表）
import { LIMIT } from "@/config/app";

defineOptions({ name: "presale" });

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

const productList = ref([]);

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
// boxStyle(){
// 	return {
// 		padding: `${this.dataConfig.topConfig.val * 2}rpx ${this.dataConfig.prConfig.val * 2}rpx ${this.dataConfig.bottomConfig.val * 2}rpx`,
// 		marginTop: `${this.dataConfig.mbConfig.val * 2}rpx`,
// 		background: this.dataConfig.bottomBgColor.color[0].item,
// 	}
// },
const boxContentStyle = computed(() => {
  let br = `${props.dataConfig.fillet.val * 2}rpx`;
  let borderRadius = `0 0 ${br} ${br}`;
  if (props.dataConfig.fillet.type) {
    borderRadius = `0 0 ${props.dataConfig.fillet.valList[3].val * 2}rpx ${
      props.dataConfig.fillet.valList[2].val * 2
    }rpx`;
  }
  return {
    borderRadius,
    background: `linear-gradient(90deg, ${props.dataConfig.moduleColor.color[0].item} 0%, ${props.dataConfig.moduleColor.color[1].item} 100%)`,
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
  return {
    backgroundImage: styleConfig.value
      ? "url(" + imgBgUrl + ")"
      : `linear-gradient(90deg,${props.dataConfig.headerBgColor.color[0].item} 0%,${props.dataConfig.headerBgColor.color[1].item} 100%)`,
    borderRadius,
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
/* 价格颜色 */
const priceColor = computed(() => {
  return props.dataConfig.toneConfig.tabVal
    ? props.dataConfig.presalePriceColor.color[0].item
    : "var(--view-theme)";
});
/* 划线价颜色 */
const otPriceColor = computed(() => {
  return props.dataConfig.goodsPriceColor.color[0].item;
});
const showBtn = computed(() => {
  return props.dataConfig.presaleConfig.tabVal;
});
/* 按钮颜色 */
const btnBgColor = computed(() => {
  return {
    background: props.dataConfig.toneConfig.tabVal
      ? `linear-gradient(90deg,${props.dataConfig.goodsBntColor.color[0].item} 0%,${props.dataConfig.goodsBntColor.color[1].item} 100%)`
      : "linear-gradient(90deg, var(--view-theme) 0%, var(--view-gradient) 100%)",
  };
});
/*商品数量*/
const numberConfig = computed(() => {
  return props.dataConfig.numberConfig.val;
});

onMounted(() => {
  getList();
});

function filterType(val) {
  let obj = {
    1: "未开始",
    2: "进行中",
    3: "已结束",
  };
  return obj[val];
}
function goPage(url) {
  uni.navigateTo({
    url,
  });
}
function getList() {
  let limit = LIMIT;
  getPresellList({
    page: 1,
    limit: numberConfig.value >= limit ? limit : numberConfig.value,
    time_type: 0,
  }).then((res) => {
    productList.value = res.data.list;
  });
}
function goDetails(item) {
  uni.navigateTo({
    url: `/pages/activity/goods_details/index?id=${item.id}&type=6`,
  });
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
  width: 152rpx;
  height: 26rpx;
  background: linear-gradient(90deg, #ff7931 0%, #e93323 100%);
  border-radius: 20rpx;
  display: inline-flex;
  justify-content: center;
  align-items: center;
  margin: 16rpx 0;
}
.btn-left {
  width: 60%;
  border-radius: 8rpx 0 0 8rpx;
  background: rgba(255, 255, 255, 0.9);
}
.btn-right {
  width: 40%;
  border-radius: 0 8rpx 8rpx 0;
}
.shandian {
  width: 48rpx;
  height: 74rpx;
  position: absolute;
  // transform: scale(1.1);
  left: 60%;
  top: 0;
  margin-top: -2rpx;
  margin-left: -22rpx;
}
</style>
