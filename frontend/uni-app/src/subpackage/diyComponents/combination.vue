<template>
  <!-- 拼团活动 -->
  <view v-show="!isSortType">
    <common-wrapper v-if="combinationList.length" :config="configData">
      <view class="combination" v-if="combinationList.length">
        <view>
          <!-- 拼团头部 -->
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
              <view
                class="avatar-group flex-y-center mr-20"
                v-if="pinkInfo.avatars && pinkInfo.avatars.length"
              >
                <image
                  v-for="(item, index) in pinkInfo.avatars"
                  :key="index"
                  :src="item"
                  mode=""
                  class="w-36 h-36 rd-50-p111-"
                ></image>
              </view>
              <text class="fs-26 text--w111-999 lh-36rpx" :style="[tipsColor]"
                >{{ pinkInfo.pink_count }}{{ $t(`人参与拼团`) }}</text
              >
            </view>
            <view
              class="flex-y-center fs-24 text--w111-999"
              :style="[headerBntColor]"
              @tap="goPage('/pages/activity/goods_combination/index')"
            >
              <text>{{ rightBntTxt }}</text>
              <text
                class="iconfont icon-ic_rightarrow fs-24"
                :style="[headerBntColor]"
              ></text>
            </view>
          </view>
          <!-- 拼团列表 -->
          <!-- 单列 -->
          <view
            class="pt-32 pr-20 pb-32 pl-20"
            :style="[boxContentStyle]"
            v-if="goodStyleConfig == 0"
          >
            <view
              class="w-full flex justify-between item"
              v-for="(item, index) in combinationList"
              :key="index"
              @tap="goDetail(item)"
            >
              <easy-loadimage
                :image-src="item.image"
                width="240rpx"
                height="240rpx"
                :borderRadius="imgStyle"
              ></easy-loadimage>
              <view class="flex-1 flex-col justify-between pl-20 h-240">
                <view class="w-full">
                  <view
                    class="w-full fs-28 h-80 lh-40rpx line2"
                    :style="[productStyle]"
                    v-if="checkboxInfo.includes(0)"
                    >{{ item.title }}</view
                  >
                  <view class="flex mt-14" v-if="checkboxInfo.includes(1)">
                    <view class="flex fs-20 rd-8rpx" :style="[labelBg]">
                      <text
                        class="tuan-num text--w111-fff flex-center"
                        v-if="checkboxInfo.includes(1)"
                        >{{ item.people }}{{ $t(`人团`) }}</text
                      >
                      <text class="complete flex-center" :style="[pinkNumStyle]"
                        >{{ $t(`已拼`) }}{{ item.pink_count || 0 }}{{ $t(`份`) }}</text
                      >
                    </view>
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
                      preFix="拼团价"
                      preFixSize="24"
                      :textColor="priceColor"
                      v-if="checkboxInfo.includes(2)"
                    ></baseMoney>
                    <text
                      class="text-line fs-28 text--w111-999 pt-14 Regular"
                      v-if="checkboxInfo.includes(3)"
                      :style="[otPriceColor]"
                      >{{ $t(`¥`) }}{{ item.product_price }}</text
                    >
                  </view>
                  <view
                    class="w-144 h-56 rd-30rpx flex-center fs-24 text--w111-fff"
                    v-if="!showBtn"
                    :style="[btnBgColor]"
                    >{{ $t(`去拼团`) }}</view
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
              v-for="(item, index) in combinationList"
              :key="index"
              @tap="goDetail(item)"
            >
              <easy-loadimage
                :image-src="item.image"
                width="100%"
                height="324rpx"
                :borderRadius="imgStyle"
              ></easy-loadimage>
              <view
                class="w-full mt-16 line1"
                :style="[productStyle]"
                v-if="checkboxInfo.includes(1)"
              >
                <view
                  class="inline fs-20 rd-4rpx mr-10 rd-4rpx"
                  :style="[labelBg]"
                >
                  <text
                    class="complete flex-center rd-4rpx"
                    :style="[pinkNumStyle]"
                    >{{ item.people }}{{ $t(`人团`) }}</text
                  >
                </view>
                <view
                  class="inline fs-28 lh-40rpx"
                  v-if="checkboxInfo.includes(0)"
                  >{{ item.title }}</view
                >
              </view>
              <view class="flex justify-between items-end mt-12">
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
                    class="text-line fs-28 text--w111-999 Regular"
                    v-if="checkboxInfo.includes(3)"
                    :style="[otPriceColor]"
                    >{{ $t(`¥`) }}{{ item.product_price }}</text
                  >
                </view>
                <view
                  class="w-144 h-56 rd-30rpx flex-center fs-24 text--w111-fff bg--w111-E93323"
                  v-if="!showBtn"
                  :style="[btnBgColor]"
                  >{{ $t(`去拼团`) }}</view
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
              class="relative"
              v-for="(item, index) in combinationList"
              :key="index"
              @tap="goDetail(item)"
            >
              <view
                class="abs-tag z-20"
                :style="[labelBg]"
                v-if="checkboxInfo.includes(1)"
              >
                <text
                  class="circle-tag flex-center fs-22"
                  :style="[pinkNumStyle]"
                  >{{ item.people }}{{ $t(`人团`) }}</text
                >
              </view>
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
              <view class="flex items-baseline">
                <baseMoney
                  :money="item.price"
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
          </view>
          <!-- 滑动 -->
          <scroll-view
            scroll-x="true"
            show-scrollbar="false"
            :style="[boxContentStyle]"
            class="white-nowrap vertical-middle w-full p-32"
            v-if="goodStyleConfig == 3"
          >
            <view
              class="inline-block relative"
              :class="{ 'ml-20': index }"
              v-for="(item, index) in combinationList"
              :key="index"
              @tap="goDetail(item)"
            >
              <view
                class="abs-tag z-20"
                :style="[labelBg]"
                v-if="checkboxInfo.includes(1)"
              >
                <text
                  class="circle-tag flex-center fs-22"
                  :style="[pinkNumStyle]"
                  >{{ item.people }}{{ $t(`人团`) }}</text
                >
              </view>
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
                >{{ item.title }}</view
              >
              <baseMoney
                class="mt-12"
                :money="item.price"
                symbolSize="24"
                integerSize="36"
                decimalSize="36"
                weight
                :color="priceColor"
                v-if="checkboxInfo.includes(2)"
              ></baseMoney>
              <view
                class="text-line fs-24 Regular lh-32rpx"
                :style="[otPriceColor]"
                v-if="checkboxInfo.includes(3)"
                >{{ $t(`¥`) }}{{ item.product_price }}</view
              >
            </view>
          </scroll-view>
        </view>
      </view>
    </common-wrapper>
  </view>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import commonWrapper from "./commonWrapper.vue";
import { openPinkSubscribe } from "@/utils/subscribeMessage.js";
import { pink as pinkApi } from "@/api/api.js";
import { getCombinationList as getCombinationListApi } from "@/api/activity.js";
import { LIMIT } from "@/config/app";

defineOptions({ name: "combination" });

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

const pinkInfo = ref("");
const combinationList = ref([]);

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
const pinkNumStyle = computed(() => {
  return {
    color: props.dataConfig.toneConfig.tabVal
      ? props.dataConfig.labelColor.color[0].item
      : "var(--view-theme)",
    // background: this.dataConfig.toneConfig.tabVal ? this.dataConfig.labelColor.color[0].item : 'var(--view-theme)'
  };
});
const labelBg = computed(() => {
  return {
    background: props.dataConfig.toneConfig.tabVal
      ? props.dataConfig.labelColor.color[0].item
      : "var(--view-theme)",
    height: "32rpx",
  };
});
/* 价格颜色 */
const priceColor = computed(() => {
  return props.dataConfig.toneConfig.tabVal
    ? props.dataConfig.pinkPriceColor.color[0].item
    : "var(--view-theme)";
});
/* 划线价颜色 */
const otPriceColor = computed(() => {
  return {
    color: props.dataConfig.goodsPriceColor.color[0].item,
  };
});
const showBtn = computed(() => {
  return props.dataConfig.pinkConfig.tabVal;
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
/*商品数量*/
const numberConfig = computed(() => {
  return props.dataConfig.numberConfig.val;
});

onMounted(() => {
  getPinkInfo();
  getCombinationList();
});

function goPage(url) {
  uni.navigateTo({
    url,
  });
}
function goDetail(item) {
  let productId = item.product_id || item.id;
  let activityId = item.product_id ? item.id : 0;
  let url = `/pages/activity/goods_combination_details/index?id=${productId}`;
  if (activityId) url += `&activity_id=${activityId}`;
  // #ifndef MP
  uni.navigateTo({ url });
  // #endif
  // #ifdef MP
  openPinkSubscribe().then((res) => {
    uni.navigateTo({ url });
  });
  // #endif
}
// 拼团列表
function getCombinationList() {
  let limit = LIMIT;
  let data = {
    page: 1,
    limit: numberConfig.value >= limit ? limit : numberConfig.value,
  };
  // 拼团插件未安装/未启用或网络异常时静默隐藏区块，不弹错误提示
  getCombinationListApi(data)
    .then((res) => {
      combinationList.value = res.data;
    })
    .catch(() => {});
}
// 拼团数据（拼团人数头部图片），原方法名 pink 与 api 导入冲突，改名 getPinkInfo
function getPinkInfo() {
  pinkApi().then((res) => {
    pinkInfo.value = res.data;
  }).catch(() => {});
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
.inline-flex {
  display: inline-flex;
}
.tuan-num {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  padding: 0 8rpx;
  height: 32rpx;
  border-radius: 8rpx 0 0 8rpx;
}
.complete {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  padding: 0 8rpx;
  height: 32rpx;
  line-height: 32rpx;
  border-radius: 0 8rpx 8rpx 0;
  background: rgba(255, 255, 255, 0.9);
}
.rd-4rpx {
  border-radius: 4rpx !important;
}
.avatar-group image {
  margin-right: -10rpx;
}
.abs-tag {
  position: absolute;
  left: 10rpx;
  top: 10rpx;
  width: 84rpx;
  height: 30rpx;
  border-radius: 20rpx;
}
.circle-tag {
  width: 84rpx;
  height: 30rpx;
  border-radius: 20rpx;
  background: rgba(255, 255, 255, 0.9);
}
scroll-view {
  box-sizing: border-box;
}
</style>
