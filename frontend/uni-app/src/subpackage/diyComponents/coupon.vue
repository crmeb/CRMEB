<template>
  <!-- 优惠券 -->
  <view v-show="!isSortType">
    <common-wrapper :config="configData">
      <view :style="[couponWrapStyle]">
        <view class="coupon-wrap" v-if="couponList.length">
          <view v-if="dataConfig.styleConfig.tabVal == 0" class="coupon1">
            <scroll-view scroll-x="true">
              <view class="list acea-row">
                <view
                  v-for="item in couponList"
                  :key="item.id"
                  class="item"
                  :style="[couponItemFirstStyle]"
                >
                  <view class="text">
                    <view class="money" :style="[couponMoneyColor]">
                      <template>
                        {{ $t(`¥`)
                        }}<text class="number">{{ item.coupon_price }}</text>
                      </template>
                    </view>
                    <view class="info">
                      <text v-if="item.use_min_price"
                        >{{ $t(`满`) }}{{ item.use_min_price
                        }}{{ $t(`可用`) }}</text
                      >
                      <text v-else>{{ $t(`无门槛券`) }}</text>
                    </view>
                  </view>
                  <view
                    v-if="item.is_use >= item.receive_limit"
                    class="button"
                    :style="[bntBgColor]"
                    >{{ $t(`已领取`) }}</view
                  >
                  <view
                    v-else
                    class="button"
                    :style="[bntBgColor]"
                    @click="receiveCoupon(item)"
                    >{{ $t(`去领取`) }}</view
                  >
                </view>
              </view>
            </scroll-view>
          </view>
          <view
            v-else-if="dataConfig.styleConfig.tabVal == 1"
            class="coupon2 p-24"
          >
            <scroll-view scroll-x="true">
              <view class="list acea-row">
                <view v-for="item in couponList" :key="item.id" class="item">
                  <view class="name" :style="[typeTxtColor]">{{
                    typeFilter(item.type)
                  }}</view>
                  <view class="text">
                    <view class="money" :style="[couponMoneyColor]">
                      <template>
                        {{ $t(`¥`)
                        }}<text class="number">{{ item.coupon_price }}</text>
                      </template>
                    </view>
                    <view class="info" :style="[reduceTxtColor]">
                      <text v-if="item.use_min_price"
                        >{{ $t(`满`) }}{{ item.use_min_price
                        }}{{ $t(`可用`) }}</text
                      >
                      <text v-else>{{ $t(`无门槛券`) }}</text>
                    </view>
                  </view>
                  <view
                    v-if="item.is_use >= item.receive_limit"
                    class="button"
                    :style="[bntBgColor, bntTxtColor]"
                    >{{ $t(`已领取`) }}</view
                  >
                  <view
                    v-else
                    class="button"
                    :style="[bntBgColor, bntTxtColor]"
                    @click="receiveCoupon(item)"
                    >{{ $t(`去领取`) }}</view
                  >
                </view>
              </view>
            </scroll-view>
          </view>
          <view
            v-else-if="dataConfig.styleConfig.tabVal == 2"
            class="coupon3 p-24 rd-16rpx"
            :style="[coupon3Color]"
          >
            <scroll-view scroll-x="true">
              <view class="list acea-row">
                <view v-for="item in couponList" :key="item.id" class="item">
                  <view class="inner acea-row">
                    <view
                      class="text acea-row row-column row-middle row-center"
                    >
                      <view class="money">
                        <template>
                          {{ $t(`¥`)
                          }}<text class="number">{{ item.coupon_price }}</text>
                        </template>
                      </view>
                      <view class="info" :style="[reduceTxtColor]">
                        <text v-if="item.use_min_price"
                          >{{ $t(`满`) }}{{ item.use_min_price
                          }}{{ $t(`可用`) }}</text
                        >
                        <text v-else>{{ $t(`无门槛券`) }}</text>
                      </view>
                    </view>
                    <view
                      v-if="item.is_use >= item.receive_limit"
                      class="button acea-row row-middle"
                      :style="[bntBgColor, bntTxtColor]"
                      >{{ $t(`已领取`) }}</view
                    >
                    <view
                      v-else
                      class="button acea-row row-middle"
                      :style="[bntBgColor, bntTxtColor]"
                      @click="receiveCoupon(item)"
                      >{{ $t(`立即领取`) }}</view
                    >
                  </view>
                </view>
              </view>
            </scroll-view>
          </view>
          <view
            v-else-if="dataConfig.styleConfig.tabVal == 3"
            class="coupon4"
            :style="[coupon4Color]"
          >
            <view class="content">
              <scroll-view scroll-x="true">
                <view class="list acea-row">
                  <view
                    v-for="item in couponList"
                    :key="item.id"
                    :style="[couponItemStyle]"
                    class="item"
                  >
                    <view class="name" :style="typeStyle">
                      <view class="inner">{{ typeFilter(item.type) }}</view>
                    </view>
                    <view class="text">
                      <view class="money" :style="[couponMoneyColor]">
                        <template>
                          {{ $t(`¥`)
                          }}<text class="number">{{ item.coupon_price }}</text>
                        </template>
                      </view>
                      <view class="info" :style="[reduceTxtColor]">
                        <text v-if="item.use_min_price"
                          >{{ $t(`满`) }}{{ item.use_min_price
                          }}{{ $t(`可用`) }}</text
                        >
                        <text v-else>{{ $t(`无门槛券`) }}</text>
                      </view>
                    </view>
                  </view>
                </view>
              </scroll-view>
              <view
                class="station acea-row row-column row-middle row-center"
                :style="[bntBgColor]"
              >
                <view class="station-name">{{ $t(`先领券 再购物`) }}</view>
                <view class="station-info">{{
                  $t(`领券下单·享购物优惠`)
                }}</view>
                <view
                  class="button"
                  :style="[bntBgColor, bntTxtColor]"
                  @click="goCoupon"
                  >{{ $t(`立即领取`) }}</view
                >
              </view>
            </view>
          </view>
          <view
            v-else-if="dataConfig.styleConfig.tabVal == 4"
            class="coupon5 pt-24 pb-24 pr-20 pl-20 rd-16rpx"
          >
            <scroll-view scroll-x="true">
              <view class="list acea-row">
                <view
                  v-for="item in couponList"
                  :key="item.id"
                  class="item acea-row"
                  :style="[bntBgColor]"
                >
                  <view class="text acea-row row-column row-middle row-center">
                    <view class="money" :style="[couponMoneyColor]">
                      <template>
                        {{ $t(`¥`)
                        }}<text class="number">{{ item.coupon_price }}</text>
                      </template>
                    </view>
                    <view class="info">
                      <text v-if="item.use_min_price"
                        >{{ $t(`满`) }}{{ item.use_min_price
                        }}{{ $t(`可用`) }}</text
                      >
                      <text v-else>{{ $t(`无门槛券`) }}</text>
                    </view>
                  </view>
                  <view
                    v-if="item.is_use >= item.receive_limit"
                    class="button acea-row row-middle"
                    >{{ $t(`已领取`) }}</view
                  >
                  <view
                    v-else
                    class="button acea-row row-middle"
                    @click="receiveCoupon(item)"
                    >{{ $t(`领取`) }}</view
                  >
                </view>
              </view>
            </scroll-view>
          </view>
        </view>
      </view>
    </common-wrapper>
  </view>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { storeToRefs } from "pinia";
import commonWrapper from "./commonWrapper.vue";
import { getCoupons, setCouponReceive } from "@/api/api.js";
import { useAppStore } from "@/store/modules/app";
import util from "@/utils/util.js";
import { LIMIT } from "@/config/app";

defineOptions({ name: "coupon" });

const emit = defineEmits(["changeLogin"]);

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

const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

watch(
  isLogin,
  function (newV, oldV) {
    if (newV) {
      getCoupon();
    }
  },
  { deep: true }
);

const isAuto = ref(false); //没有授权的不会自动授权
const isShowAuth = ref(false); //是否隐藏授权
const couponList = ref([]);

const couponMoneyColor = computed(() => {
  let styleObject = {};
  if (props.dataConfig.toneConfig.tabVal) {
    styleObject["color"] = props.dataConfig.couponMoneyColor.color[0].item;
  }
  return styleObject;
});
const reduceTxtColor = computed(() => {
  let styleObject = {};
  if (props.dataConfig.toneConfig.tabVal && props.dataConfig.couponReduceColor) {
    styleObject["color"] = props.dataConfig.couponReduceColor.color[0].item;
  }
  return styleObject;
});
const typeTxtColor = computed(() => {
  let styleObject = {};
  if (props.dataConfig.toneConfig.tabVal && props.dataConfig.couponTypeColor) {
    styleObject["color"] = props.dataConfig.couponTypeColor.color[0].item;
  }
  return styleObject;
});
const bntTxtColor = computed(() => {
  let styleObject = {};
  if (props.dataConfig.toneConfig.tabVal && props.dataConfig.bntTxtColor) {
    styleObject["color"] = props.dataConfig.bntTxtColor.color[0].item;
  }
  return styleObject;
});
const couponItemFirstStyle = computed(() => {
  if (props.dataConfig.toneConfig.tabVal) {
    return {
      background: props.dataConfig.couponBgColor.color[0].item,
    };
  } else {
    return {
      background: "var(--view-theme)",
    };
  }
});
const couponItemStyle = computed(() => {
  return {
    "margin-right": `${props.dataConfig.spacingConfig.val * 2}rpx`,
  };
});
const typeStyle = computed(() => {
  if (!props.dataConfig.toneConfig.tabVal) {
    return {
      background: "var(--view-theme)",
      color: "var(--view-theme)",
    };
  } else if (props.dataConfig.couponTypeColor) {
    return {
      background: props.dataConfig.couponTypeColor.color[0].item,
      color: props.dataConfig.couponTypeColor.color[0].item,
    };
  } else {
    return {
      background: props.dataConfig.couponMoneyColor.color[0].item,
      color: props.dataConfig.couponMoneyColor.color[0].item,
    };
  }
});
const bntBgColor = computed(() => {
  let styleObject = {};
  if (props.dataConfig.toneConfig.tabVal) {
    styleObject["background"] =
      `linear-gradient(180deg, ${props.dataConfig.bntBgColor.color[0].item} 0%, ${props.dataConfig.bntBgColor.color[1].item} 100%)`;
  }
  return styleObject;
});
const coupon3Color = computed(() => {
  let styleObject = {
    "--coupon3-color": "var(--view-theme)",
  };
  if (props.dataConfig.toneConfig.tabVal) {
    styleObject["--coupon3-color"] =
      props.dataConfig.couponMoneyColor.color[0].item;
  }
  return styleObject;
});
const coupon4Color = computed(() => {
  let styleObject = {
    "--coupon4-color": "var(--view-theme)",
  };
  if (props.dataConfig.toneConfig.tabVal) {
    styleObject["--coupon4-color"] =
      props.dataConfig.couponBgColor.color[0].item;
  }
  return styleObject;
});
const couponWrapBgColor = computed(() => {
  let styleObject = {
    "--module-color":
      (props.dataConfig.moduleColor && props.dataConfig.moduleColor.color && props.dataConfig.moduleColor.color[0] && props.dataConfig.moduleColor.color[0].item) || "",
  };
  if (props.dataConfig.styleConfig.tabVal == 1) {
    styleObject["--module-color"] =
      props.dataConfig.moduleColor && props.dataConfig.moduleColor.color && props.dataConfig.moduleColor.color.length >= 2
        ? `linear-gradient(90deg,${props.dataConfig.moduleColor.color[0].item} 0%,${props.dataConfig.moduleColor.color[1].item} 100%)`
        : "";
  }
  // if (this.dataConfig.styleConfig.tabVal == 3) {
  let borderRadius = `${props.dataConfig.fillet.val * 2}rpx`;
  if (props.dataConfig.fillet.type) {
    borderRadius = `${props.dataConfig.fillet.valList[0].val * 2}rpx ${
      props.dataConfig.fillet.valList[1].val * 2
    }rpx ${props.dataConfig.fillet.valList[3].val * 2}rpx ${
      props.dataConfig.fillet.valList[2].val * 2
    }rpx`;
  }
  styleObject["border-radius"] = borderRadius;
  // }
  return styleObject;
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
const couponWrapStyle = computed(() => {
  let style = {},
    borderRadius = `${props.dataConfig.fillet.val * 2}rpx`;
  if (props.dataConfig.moduleColor) {
    const colors = props.dataConfig.moduleColor.color.map((c) => c.item);
    style["background"] =
      style.background = `linear-gradient(90deg, ${colors[0]} 0%, ${colors[1]} 100%)`;
  }
  if (props.dataConfig.fillet.type) {
    borderRadius = `${props.dataConfig.fillet.valList[0].val * 2}rpx ${
      props.dataConfig.fillet.valList[1].val * 2
    }rpx ${props.dataConfig.fillet.valList[3].val * 2}rpx ${
      props.dataConfig.fillet.valList[2].val * 2
    }rpx`;
  }
  style["border-radius"] = borderRadius;
  return style;
});

onMounted(() => {
  getCoupon();
});

function typeFilter(val) {
  let obj = {
    0: "通用券",
    1: "品类券",
    2: "商品券",
    3: "品牌券",
  };
  return obj[val];
}
function getCoupon() {
  let limit = LIMIT;
  getCoupons({
    page: 1,
    limit:
      props.dataConfig.numberConfig.val >= limit
        ? limit
        : props.dataConfig.numberConfig.val,
    type: -1,
  })
    .then((res) => {
      couponList.value = res.data.list;
    })
    .catch((err) => {
      return util.Tips({
        title: err,
      });
    });
}
function receiveCoupon(item) {
  if (!isLogin.value) {
    emit("changeLogin");
  } else {
    setCouponReceive(item.id)
      .then(function () {
        item.is_use = true;
        couponList.value = couponList.value;
        util.Tips({
          title: "领取成功",
        });
      })
      .catch(function (err) {
        util.Tips({
          title: err,
        });
      });
  }
}
function goCoupon() {
  uni.navigateTo({
    url: "/pages/users/user_get_coupon/index",
  });
}
</script>

<style lang="scss">
.coupon-wrap {
  // background: var(--module-color);
}

.coupon1 {
  padding: 12rpx;
  .list {
    flex-wrap: nowrap;
  }

  .item {
    flex-shrink: 0;
    position: relative;
    width: 156rpx;
    height: 156rpx;
    border-radius: 32rpx;
    margin-top: 20rpx;
    margin-right: 12rpx;
    background: var(--view-theme);

    .text {
      width: 144rpx;
      height: 156rpx;
      padding-top: 22rpx;
      border: 2rpx solid #fceae9;
      border-radius: 16rpx;
      margin: -20rpx auto 0;
      background: #ffffff;
    }

    .money {
      height: 40rpx;
      text-align: center;
      font-weight: 500;
      font-size: 28rpx;
      color: var(--view-theme);
    }

    .number {
      font-family: SemiBold;
      font-size: 40rpx;
      line-height: 40rpx;
    }

    .info {
      margin-top: 10rpx;
      text-align: center;
      font-size: 18rpx;
      line-height: 26rpx;
      color: #999999;
    }

    .button {
      position: absolute;
      right: 0;
      bottom: 0;
      left: 0;
      height: 76rpx;
      padding-top: 28rpx;
      border-radius: 0 0 32rpx 32rpx;
      background: linear-gradient(
        90deg,
        var(--view-theme) 0%,
        var(--view-gradient) 100%
      );
      text-align: center;
      font-weight: 500;
      font-size: 24rpx;
      color: #ffffff;

      &::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 156rpx;
        height: 20rpx;
        border: 8rpx solid #fceae9;
        border-top-color: #ffffff;
        border-bottom-right-radius: 78rpx 20rpx;
        border-bottom-left-radius: 78rpx 20rpx;
        box-sizing: border-box;
        background: #ffffff;
      }
    }
  }
}

.coupon2 {
  .list {
    flex-wrap: nowrap;
  }

  .item {
    flex-shrink: 0;
    width: 204rpx;
    height: 232rpx;
    padding-top: 28rpx;
    border-radius: 16rpx;
    margin-top: 0;
    margin-right: 12rpx;
    background: #ffffff;
  }

  .name {
    text-align: center;
    font-weight: 500;
    font-size: 24rpx;
    line-height: 34rpx;
    color: #333333;
  }

  .text {
    margin-top: 8rpx;
    text-align: center;
    color: var(--view-theme);
  }

  .money {
    height: 48rpx;
    font-weight: 500;
    font-size: 28rpx;
  }

  .number {
    font-family: SemiBold;
    font-size: 44rpx;
  }

  .info {
    font-size: 18rpx;
    line-height: 26rpx;
  }

  .button {
    width: 136rpx;
    height: 48rpx;
    border-radius: 24rpx;
    margin: 12rpx auto 0;
    background: linear-gradient(
      90deg,
      var(--view-theme) 0%,
      var(--view-gradient) 100%
    );
    text-align: center;
    font-weight: 500;
    font-size: 24rpx;
    line-height: 48rpx;
    color: #ffffff;
  }
}

.coupon3 {
  .list {
    flex-wrap: nowrap;
  }

  .item {
    flex-shrink: 0;
    width: 274rpx;
    height: 150rpx;
    border: 1rpx solid var(--coupon3-color);
    border-radius: 16rpx;
    margin-top: 0;
    margin-right: 12rpx;
    background: var(--coupon3-color);
  }

  .inner {
    height: 100%;
    border-radius: 15rpx;
    background: rgba(255, 255, 255, 0.9);
  }

  .text {
    flex: 1;
    position: relative;

    &::before {
      content: "";
      position: absolute;
      top: 0;
      right: 0;
      z-index: 2;
      width: 16rpx;
      height: 16rpx;
      border: 1rpx solid var(--coupon3-color);
      border-radius: 50%;
      background: var(--module-color);
      transform: translate(50%, -50%);
    }

    &::after {
      content: "";
      position: absolute;
      bottom: 0;
      right: 0;
      z-index: 2;
      width: 16rpx;
      height: 16rpx;
      border: 1rpx solid var(--coupon3-color);
      border-radius: 50%;
      background: var(--module-color);
      transform: translate(50%, 50%);
    }
  }

  .money {
    height: 48rpx;
    text-align: center;
    font-weight: 500;
    font-size: 28rpx;
    color: var(--coupon3-color);
  }

  .number {
    font-family: SemiBold;
    font-size: 44rpx;
  }

  .info {
    margin-top: 8rpx;
    text-align: center;
    font-size: 24rpx;
    line-height: 34rpx;
    color: var(--coupon3-color);
  }

  .button {
    position: relative;
    width: 74rpx;
    padding: 0 24rpx;
    font-size: 26rpx;
    line-height: 28rpx;
    color: var(--coupon3-color);

    &::before {
      content: "";
      position: absolute;
      top: 12rpx;
      bottom: 12rpx;
      left: 0;
      border-left: 1rpx dashed var(--coupon3-color);
    }
  }
}

.coupon5 {
  .list {
    flex-wrap: nowrap;
  }

  .item {
    flex-shrink: 0;
    width: 228rpx;
    height: 108rpx;
    border-radius: 12rpx;
    margin-right: 12rpx;
    background: linear-gradient(
      0deg,
      var(--view-theme) 0%,
      var(--view-gradient) 100%
    );
  }

  .text {
    flex: 1;
    border-radius: 12rpx;
    background: radial-gradient(
        circle at left 54rpx,
        var(--module-color) 11rpx,
        rgba(255, 255, 255, 0.9) 0%,
        rgba(255, 255, 255, 0.8) 100%
      )
      top;
  }

  .money {
    height: 48rpx;
    font-weight: 500;
    font-size: 28rpx;
    color: var(--view-theme);
  }

  .number {
    font-family: SemiBold;
    font-size: 44rpx;
  }

  .info {
    font-size: 22rpx;
    line-height: 30rpx;
    color: var(--view-theme);
  }

  .button {
    width: 56rpx;
    padding: 0 16rpx;
    font-size: 24rpx;
    line-height: 28rpx;
    color: #ffffff;
  }
}

.coupon4 {
  border-radius: 16rpx;

  .content {
    position: relative;
    padding: 20rpx 296rpx 20rpx 20rpx;
    border-radius: 24rpx;
    background: var(--coupon4-color);
  }

  .list {
    flex-wrap: nowrap;
  }

  .item {
    flex-shrink: 0;
    width: 140rpx;
    height: 132rpx;
    border-radius: 12rpx;
    margin-right: 12rpx;
    background: #ffffff;
  }

  .name {
    width: 108rpx;
    height: 38rpx;
    line-height: 38rpx;
    border-radius: 0 0 19rpx 19rpx;
    margin: 0 auto;
    background: var(--coupon4-color);
    text-align: center;
    font-size: 22rpx;
    color: var(--coupon4-color);
  }

  .inner {
    width: 100%;
    height: 100%;
    border-radius: 0 0 19rpx 19rpx;
    background: rgba(255, 255, 255, 0.9);
  }

  .money {
    height: 46rpx;
    margin-top: 10rpx;
    text-align: center;
    font-weight: 500;
    font-size: 28rpx;
    color: var(--coupon4-color);
  }

  .number {
    font-family: SemiBold;
    font-size: 40rpx;
  }

  .info {
    margin-top: 4rpx;
    text-align: center;
    font-size: 18rpx;
    line-height: 26rpx;
    color: #333333;
  }

  .station {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    width: 296rpx;
    // padding-left: 12rpx;
    border-radius: 0 24rpx 24rpx 0;
    // background: radial-gradient(circle at -180rpx 86rpx, transparent 200rpx, var(--coupon4-color) 0%, var(--coupon4-color) 100%);
    background: linear-gradient(
      0deg,
      var(--view-theme) 0%,
      var(--view-gradient) 100%
    );
    // filter: drop-shadow(-16rpx 0rpx 10rpx rgba(0, 0, 0, 0.10));
  }

  .station-name {
    font-weight: 500;
    font-size: 32rpx;
    line-height: 44rpx;
    color: #ffffff;
  }

  .station-info {
    margin-top: 4rpx;
    font-size: 20rpx;
    line-height: 28rpx;
    color: #f5f5f5;
  }

  .button {
    width: 160rpx;
    height: 48rpx;
    border-radius: 24rpx;
    margin-top: 8rpx;
    background: rgba(255, 255, 255, 0.9);
    text-align: center;
    font-weight: 500;
    font-size: 24rpx;
    line-height: 48rpx;
    color: var(--coupon4-color);
  }
}
</style>
