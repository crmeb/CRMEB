<template>
  <!-- 秒杀列表 -->
  <common-wrapper v-show="!isSortType" :config="configData">
    <view class="seckill" v-if="spikeList.length > 0">
      <view>
        <!-- 秒杀头部 -->
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
            <text
              class="fs-26 text--w111-999 lh-36rpx pl-20"
              :style="[tipsColor]"
              >{{ $t(`距离结束`) }}</text
            >
            <countDown
              :is-day="false"
              tip-text=" "
              day-text=" "
              hour-text=":"
              minute-text=":"
              second-text=" "
              :datatime="datatime"
              :bgColor="numberBgColor"
              :colors="numberColor"
              :dotColor="dotColor"
            ></countDown>
          </view>
          <view
            class="flex-y-center fs-24 text--w111-999"
            :style="[headerBntColor]"
            @tap="goPage('/pages/activity/goods_seckill/index')"
          >
            <text>{{ rightBntTxt }}</text>
            <text
              class="iconfont icon-ic_rightarrow fs-24"
              :style="[headerBntColor]"
            ></text>
          </view>
        </view>
        <!-- 单列 -->
        <view
          class="pt-32 pr-20 pb-32 pl-20 bg--w111-fff"
          :style="[boxContentStyle]"
          v-if="goodStyleConfig == 0"
        >
          <view
            class="w-full flex justify-between item"
            v-for="(item, index) in spikeList"
            :key="index"
            @tap="goDetails(item)"
          >
            <easy-loadimage
              :image-src="item.image"
              :border-src="item.activity_image"
              width="240rpx"
              height="240rpx"
              :borderRadius="imgStyle"
            ></easy-loadimage>
            <view class="flex-1 flex-col justify-between pl-20 h-240">
              <view>
                <view
                  class="w-full fs-28 h-80 lh-40rpx line2"
                  :style="[productStyle]"
                  v-if="checkboxInfo.includes(0)"
                  >{{ item.title }}</view
                >
                <view
                  class="w-full flex-y-center mt-20"
                  v-if="checkboxInfo.includes(1)"
                >
                  <view class="progress relative">
                    <view
                      class="active"
                      :style="{
                        width: item.percent + '%',
                        background: progressBgColor,
                      }"
                    >
                      <!-- <image src="@/static/images/lightning.png" class="lightning"></image> -->
                    </view>
                    <image
                      src="@/static/images/lightning.png"
                      class="lightning"
                      :style="{
                        left: item.percent + '%',
                        transform:
                          item.percent == 100
                            ? 'translateX(-100%)'
                            : 'translateX(0)',
                      }"
                    ></image>
                  </view>
                  <text
                    class="fs-22 lh-30rpx pl-12"
                    :style="{ color: progressTextColor }"
                    >已抢{{ item.percent + "%" }}</text
                  >
                </view>
              </view>
              <view class="flex justify-between items-end">
                <view class="flex-col">
                  <baseMoney
                    :money="item.price"
                    symbolSize="24"
                    integerSize="36"
                    decimalSize="36"
                    weight
                    :color="priceColor"
                    preFix="秒杀价"
                    preFixSize="24"
                    :textColor="priceColor"
                    v-if="checkboxInfo.includes(2)"
                  ></baseMoney>
                  <text
                    class="text-line fs-28 text--w111-999 pt-10 mb-10 Regular"
                    v-if="checkboxInfo.includes(3)"
                    :style="[otPriceColor]"
                    >{{ $t(`¥`)
                    }}{{
                      item.product_price.toString().split(".")[1]
                        ? item.product_price
                        : item.product_price + ".00"
                    }}</text
                  >
                </view>
                <view
                  class="w-144 h-56 rd-30rpx flex-center fs-24 text--w111-fff"
                  v-if="!showBtn"
                  :style="[btnBgColor]"
                  >{{ $t(`去抢购`) }}</view
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
            v-for="(item, index) in spikeList"
            :key="index"
            @tap="goDetails(item)"
          >
            <easy-loadimage
              :image-src="item.image"
              :border-src="item.activity_image"
              width="100%"
              height="324rpx"
              :borderRadius="imgStyle"
            ></easy-loadimage>
            <view
              class="w-full line2 mt-16 fs-28 lh-40rpx"
              :style="[productStyle]"
              v-if="checkboxInfo.includes(0)"
              >{{ item.title }}</view
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
                  class="text-line fs-28 text--w111-999 pt-14 Regular"
                  v-if="checkboxInfo.includes(3)"
                  :style="[otPriceColor]"
                  >{{ $t(`¥`)
                  }}{{
                    item.product_price.toString().split(".")[1]
                      ? item.product_price
                      : item.product_price + ".00"
                  }}</text
                >
              </view>
              <view
                class="w-144 h-56 rd-30rpx flex-center fs-24 text--w111-fff bg--w111-E93323"
                v-if="!showBtn"
                :style="[btnBgColor]"
                >{{ $t(`去抢购`) }}</view
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
            v-for="(item, index) in spikeList"
            :key="index"
            @tap="goDetails(item)"
          >
            <easy-loadimage
              :image-src="item.image"
              :border-src="item.activity_image"
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
            <view
              class="price-box flex-center relative"
              :style="[tagBag]"
              v-if="checkboxInfo.includes(2)"
            >
              <baseMoney
                :money="item.price"
                symbolSize="24"
                integerSize="30"
                decimalSize="30"
                weight
                :color="bargainPriceColor"
              ></baseMoney>
              <image
                src="@/static/images/shandian.png"
                class="shandian"
              ></image>
            </view>
            <view
              class="text-line fs-24 text--w111-999 Regular lh-32rpx mt-10"
              :style="[otPriceColor]"
              v-if="checkboxInfo.includes(3)"
              >{{ $t(`¥`)
              }}{{
                item.product_price.toString().split(".")[1]
                  ? item.product_price
                  : item.product_price + ".00"
              }}</view
            >
          </view>
        </view>
        <!-- 滑动 -->
        <scroll-view
          scroll-x="true"
          show-scrollbar="false"
          class="seckill-scroll w-full p-32"
          :style="[boxContentStyle]"
          v-if="goodStyleConfig == 3"
        >
          <view
            class="seckill-scroll-item"
            :class="{ 'ml-20': index }"
            v-for="(item, index) in spikeList"
            :key="index"
            @tap="goDetails(item)"
          >
            <easy-loadimage
              :image-src="item.image"
              :border-src="item.activity_image"
              width="224rpx"
              height="224rpx"
              :borderRadius="imgStyle"
            ></easy-loadimage>
            <view
              class="w-222 line1 mt-16 fs-26 lh-26rpx"
              :style="[productStyle]"
              v-if="checkboxInfo.includes(0)"
              >{{ item.title }}</view
            >
            <view class="flex justify-between items-end mt-16">
              <view>
                <baseMoney
                  :money="item.price"
                  symbolSize="24"
                  integerSize="36"
                  decimalSize="36"
                  weight
                  :color="priceColor"
                  v-if="checkboxInfo.includes(2)"
                ></baseMoney>
                <view
                  class="text-line fs-24 text--w111-999 Regular lh-32rpx"
                  :style="[otPriceColor]"
                  v-if="checkboxInfo.includes(3)"
                  >{{ $t(`¥`)
                  }}{{
                    item.product_price.toString().split(".")[1]
                      ? item.product_price
                      : item.product_price + ".00"
                  }}</view
                >
              </view>
              <view
                class="qiang flex-center fs-24 text--w111-fff"
                v-if="!showBtn"
                :style="[btnBgColor]"
                >{{ $t(`抢`) }}</view
              >
            </view>
          </view>
        </scroll-view>
      </view>
    </view>
  </common-wrapper>
</template>

<script>
// 小程序组件样式隔离选项：deep 重写子组件样式需 shared（script setup 无法声明，故并列普通 script 块）
export default {
  name: "seckill",
  options: {
    styleIsolation: "shared",
  },
};
</script>

<script setup>
import { ref, computed, onMounted } from "vue";
import commonWrapper from "./commonWrapper.vue";
import countDown from "@/components/countDown";
import { getSeckillIndexTime as getSeckillIndexTimeApi, getSeckillList } from "@/api/activity.js";
import { LIMIT } from "@/config/app";

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

const datatime = ref("");
const spikeList = ref([]);
const seckillTimeIndex = ref(0);
const seckillTime = ref([]);

// 遗留判断：timeList/active 原代码从未在 data 声明（直接挂 this，非响应式），以普通变量保持同等语义
var timeList, active;

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
// boxStyle() {
//   return {
//     padding: `${this.dataConfig.topConfig.val * 2}rpx ${
//       this.dataConfig.prConfig.val * 2
//     }rpx ${this.dataConfig.bottomConfig.val * 2}rpx`,
//     marginTop: `${this.dataConfig.mbConfig.val * 2}rpx`,
//     background: this.dataConfig.bottomBgColor.color[0].item,
//   };
// },
const tagBag = computed(() => {
  return {
    color: props.dataConfig.toneConfig.tabVal
      ? props.dataConfig.goodsBntTxtColor.color[0].item
      : "#fff",
    background: props.dataConfig.toneConfig.tabVal
      ? `linear-gradient(270deg,${props.dataConfig.goodsBntColor.color[1].item} 0%,${props.dataConfig.goodsBntColor.color[0].item} 100%)`
      : "var(--view-theme)",
  };
});
const bargainPriceColor = computed(() => {
  return props.dataConfig.toneConfig.tabVal
    ? props.dataConfig.seckillPriceColor2.color[0].item
    : "#fff";
});
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
    ? props.dataConfig.seckillPriceColor.color[0].item
    : "var(--view-theme)";
});
/* 进度文字颜色 */
const progressTextColor = computed(() => {
  return props.dataConfig.toneConfig.tabVal
    ? props.dataConfig.progressTxtColor.color[0].item
    : "var(--view-theme)";
});
/* 划线价颜色 */
const otPriceColor = computed(() => {
  return {
    color: props.dataConfig.goodsPriceColor.color[0].item,
  };
});
const showBtn = computed(() => {
  return props.dataConfig.seckillConfig.tabVal;
});
/* 按钮颜色 */
const btnBgColor = computed(() => {
  return {
    background: props.dataConfig.toneConfig.tabVal
      ? `linear-gradient(90deg,${props.dataConfig.goodsBntColor.color[1].item} 0%,${props.dataConfig.goodsBntColor.color[0].item} 100%)`
      : "linear-gradient(90deg, var(--view-gradient) 0%, var(--view-theme) 100%)",
    color: props.dataConfig.goodsBntTxtColor.color[0].item,
  };
});
const progressBgColor = computed(() => {
  return props.dataConfig.toneConfig.tabVal
    ? `linear-gradient(90deg,${props.dataConfig.progressColor.color[0].item} 0%,${props.dataConfig.progressColor.color[1].item} 100%)`
    : "linear-gradient(45deg, var(--view-gradient) 0%, var(--view-theme) 100%)";
});
/*倒计时背景色*/
const numberBgColor = computed(() => {
  return styleConfig.value
    ? `linear-gradient(90deg, ${props.dataConfig.numberBgColor.color[0].item} 0%, ${props.dataConfig.numberBgColor.color[1].item} 100%)`
    : `linear-gradient(90deg, ${props.dataConfig.numberBgColor2.color[0].item} 0%, ${props.dataConfig.numberBgColor2.color[1].item} 100%)`;
});
const numberColor = computed(() => {
  return styleConfig.value
    ? props.dataConfig.numberColor.color[0].item
    : props.dataConfig.numberColor2.color[0].item;
});
const dotColor = computed(() => {
  return styleConfig.value
    ? props.dataConfig.numberBgColor.color[0].item
    : props.dataConfig.numberBgColor2.color[0].item;
});
/*商品数量*/
const numberConfig = computed(() => {
  return props.dataConfig.numberConfig.val;
});

onMounted(() => {
  getSeckillIndexTime();
});

function goPage(url) {
  uni.navigateTo({
    url,
  });
}
function goDetails(item) {
  uni.navigateTo({
    url:
      "/pages/activity/goods_seckill_details/index?id=" +
      item.id +
      "&type=1&time_id=" +
      timeList[active].id,
  });
}
function getSeckillIndexTime() {
  let limit = LIMIT;
  let params = {
    page: 1,
    limit: numberConfig.value >= limit ? limit : numberConfig.value,
    type: "index",
  };
  // 秒杀插件未安装/未启用或网络异常时静默隐藏区块，不弹错误提示
  getSeckillIndexTimeApi().then((res) => {
    timeList = res.data.seckillTime;
    active = res.data.seckillTimeIndex;
    if (res.data.seckillTimeIndex === -1) {
      return;
    }
    datatime.value = res.data.seckillTime[res.data.seckillTimeIndex].stop;
    let id = res.data.seckillTime[res.data.seckillTimeIndex].id;
    getSeckillList(id, params).then(({ data }) => {
      spikeList.value = data;
    }).catch(() => {});
  }).catch(() => {});
}
</script>

<style lang="scss" scoped>
.Regular {
  font-family: "Regular";
}
:deep(.styleAll){
  padding: 0 8rpx;
  border-radius: 4rpx;
  font-size: 24rpx;
  line-height: 40rpx;
}
.bg-cover {
  background-repeat: no-repeat;
  background-size: 100% 100%;
}
.item ~ .item {
  margin-top: 32rpx;
}
.progress {
  width: 240rpx;
  height: 24rpx;
  border-radius: 12rpx;
  background-color: rgba(253, 240, 237, 1);
  overflow: hidden;
  .active {
    height: 24rpx;
    border-radius: 12rpx;
    background: linear-gradient(45deg, #ff7931 0%, #e93323 100%);
  }
  .lightning {
    position: absolute;
    top: -2rpx;
    width: 28rpx;
    height: 28rpx;
  }
}
.price-box {
  width: 150rpx;
  height: 44rpx;
  background: linear-gradient(
    45deg,
    var(--view-gradient) 0%,
    var(--view-theme) 100%
  );
  border-radius: 0px 8rpx 8rpx 0px;
  margin: 20rpx 0 0 10rpx;
  .shandian {
    position: absolute;
    width: 24rpx;
    height: 44rpx;
    top: 0;
    left: -10rpx;
  }
}
.qiang {
  width: 66rpx;
  height: 40rpx;
  background: linear-gradient(45deg, #ff7931 0%, #e93323 100%);
  border-radius: 0px 22rpx 22rpx 0px;
  position: relative;
  &:before {
    content: "";
    position: absolute;
    top: 0;
    left: -10rpx;
    width: 24rpx;
    height: 40rpx;
    background-image: url("@/static/images/shandian.png");
    background-size: cover;
  }
}
scroll-view {
  box-sizing: border-box;
}
.seckill-scroll {
  white-space: nowrap;

  .seckill-scroll-item {
    display: inline-block;
    width: 224rpx;
    vertical-align: top;
  }
}
</style>
