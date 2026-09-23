<template>
  <view v-if="couponList.length || productList.length || newcomer_integral">
    <common-wrapper :config="configData">
      <view
        class="newVip2"
        :style="[newVip2Background, newVipBorderRadius]"
        v-if="dataConfig.styleConfig.tabVal"
      >
        <view class="header acea-row row-between row-middle">
          <view class="title-box acea-row row-middle">
            <image
              :src="`${imgHost}/statics/images/newVip1.png`"
              class="image"
            ></image>
            <view class="info">{{ $t(`超值优惠 限时专享`) }}</view>
          </view>
          <view class="more" @click="goNewList"
            >{{ $t(`去逛逛`) }}<text class="iconfont icon-ic_rightarrow"></text
          ></view>
        </view>
        <view class="wrapper">
          <view
            class="coupon"
            v-if="couponList.length && dataConfig.checkboxInfo.type.includes(1)"
          >
            <view class="title">{{ $t(`新人红包`) }}</view>
            <view class="content">
              <scroll-view scroll-x="true">
                <view class="list acea-row">
                  <view v-for="item in couponList" :key="item.id" class="item">
                    <view class="back" :style="[couponStyle]"></view>
                    <view class="money" :style="[moneyStyle]">
                      <view v-if="item.coupon_type == 1"
                        >{{ $t(`¥`)
                        }}<text class="number">{{
                          item.coupon_price
                        }}</text></view
                      >
                      <view v-else-if="item.coupon_type == 2"
                        ><text class="number">{{
                          parseFloat(item.coupon_price) / 10
                        }}</text
                        >{{ $t(`折`) }}</view
                      >
                    </view>
                    <view
                      v-if="item.use_min_price"
                      class="info"
                      :style="[couponInfoStyle]"
                      >{{ $t(`满`) }}{{ item.use_min_price
                      }}{{ $t(`可用`) }}</view
                    >
                    <view v-else class="info" :style="[couponInfoStyle]">{{
                      $t(`无门槛券`)
                    }}</view>
                  </view>
                </view>
              </scroll-view>
            </view>
          </view>
          <view
            class="product"
            v-if="
              productList.length && dataConfig.checkboxInfo.type.includes(2)
            "
          >
            <view class="title">{{ $t(`新人商品专区`) }}</view>
            <view class="content">
              <scroll-view scroll-x="true">
                <view class="list acea-row">
                  <view
                    class="item"
                    v-for="item in productList"
                    :key="item.id"
                    @click="goDetail(item)"
                  >
                    <easy-loadimage
                      mode="widthFix"
                      :image-src="item.image"
                      width="144rpx"
                      height="144rpx"
                      borderRadius="12rpx"
                    ></easy-loadimage>
                    <view class="money"
                      >{{ $t(`¥`)
                      }}<text class="number">{{ item.price }}</text></view
                    >
                    <view class="name line1">{{ item.store_name }}</view>
                  </view>
                </view>
              </scroll-view>
            </view>
          </view>
        </view>
        <view
          class="bonus acea-row row-middle"
          v-if="dataConfig.checkboxInfo.type.includes(0)"
        >
          <image
            :src="`${imgHost}/statics/images/newVip2.png`"
            class="image"
          ></image>
          <view class="text">
            <view class="info acea-row row-middle">
              {{ $t(`新用户注册领积分`) }}
              <view class="red" :style="[bonusRedStyle]">
                <view class="inner acea-row row-middle">
                  <image
                    :src="`${imgHost}/statics/images/newVip3.png`"
                    class="image"
                  ></image>
                  +{{ newcomer_integral }}
                </view>
              </view>
            </view>
            <view class="">{{ $t(`新用户注册后即可获得积分`) }}</view>
          </view>
          <view class="button" :style="[buttonStyle]" @click="goNewList">{{
            $t(`去看看`)
          }}</view>
        </view>
      </view>
      <view class="newVip" :style="[newVipBorderRadius]" v-else>
        <view class="header acea-row row-between row-middle">
          <view class="title">{{ $t(`新人专享福利`) }}</view>
          <view class="more" @click="goNewList"
            >{{ $t(`更多优惠`)
            }}<text class="iconfont icon-ic_rightarrow"></text
          ></view>
        </view>
        <view
          class="bonus"
          :style="[bonusStyle]"
          v-if="dataConfig.checkboxInfo.type.includes(0)"
        >
          <view class="inner acea-row row-middle">
            <image
              :src="`${imgHost}/statics/images/newVip3.png`"
              class="image"
            ></image>
            {{ $t(`新用户注册即可`) }}
            <text class="red" :style="[bonusRedStyle]">{{
              $t(`赠送积分`)
            }}</text>
          </view>
        </view>
        <view
          class="coupon"
          v-if="dataConfig.checkboxInfo.type.includes(1) && couponList.length"
        >
          <view class="title">{{ $t(`专属优惠券`) }}</view>
          <view class="content acea-row" :style="[couponContentStyle]">
            <scroll-view scroll-x="true">
              <view class="list acea-row">
                <view
                  v-for="item in couponList"
                  :key="item.id"
                  class="item"
                  :style="[couponStyle]"
                >
                  <view class="item-top acea-row row-center row-middle">
                    <view class="money" :style="[moneyStyle]">
                      <view v-if="item.coupon_type == 1"
                        >{{ $t(`¥`)
                        }}<text class="number">{{
                          item.coupon_price
                        }}</text></view
                      >
                      <view v-else-if="item.coupon_type == 2"
                        ><text class="number">{{
                          parseFloat(item.coupon_price) / 10
                        }}</text
                        >{{ $t(`折`) }}</view
                      >
                    </view>
                  </view>
                  <view
                    class="item-bottom acea-row row-column row-center row-middle"
                  >
                    <view class="name" :style="[couponTypeStyle]">
                      <text v-if="item.coupon_type == 1">{{
                        $t(`品类券`)
                      }}</text>
                      <text v-else-if="item.coupon_type == 2">{{
                        $t(`商品券`)
                      }}</text>
                      <text v-else-if="item.coupon_type == 3">{{
                        $t(`品牌券`)
                      }}</text>
                      <text v-else>{{ $t(`通用券`) }}</text>
                    </view>
                    <view v-if="item.use_min_price" class="info"
                      >{{ $t(`满`) }}{{ item.use_min_price
                      }}{{ $t(`可用`) }}</view
                    >
                    <view v-else class="info">{{ $t(`无门槛券`) }}</view>
                  </view>
                </view>
              </view>
            </scroll-view>
            <view class="station" :style="[stationStyle]">
              <view class="money"
                >{{ $t(`¥`) }}<text class="number">{{ totalPrice }}</text></view
              >
              <view class="info">{{ $t(`新人专享优惠券`) }}</view>
              <view class="button" :style="[buttonStyle]" @click="goUser">{{
                $t(`一键领取`)
              }}</view>
            </view>
          </view>
        </view>
        <view
          class="product"
          v-if="productList.length && dataConfig.checkboxInfo.type.includes(2)"
        >
          <view class="title">{{ $t(`新人商品专区`) }}</view>
          <view class="content">
            <scroll-view scroll-x="true">
              <view class="list acea-row">
                <view
                  class="item"
                  v-for="item in productList"
                  :key="item.id"
                  @click="goDetail(item)"
                >
                  <easy-loadimage
                    mode="widthFix"
                    :image-src="item.image"
                    width="158rpx"
                    height="158rpx"
                    borderRadius="12rpx"
                  ></easy-loadimage>
                  <view class="name line1">{{ item.store_name }}</view>
                  <view class="money" :style="[productMoneyStyle]"
                    >{{ $t(`¥`)
                    }}<text class="number">{{ item.price }}</text></view
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
import { ref, computed, watch } from "vue";
import { storeToRefs } from "pinia";
import commonWrapper from "./commonWrapper.vue";
import { useAppStore } from "@/store/modules/app";
import util from "@/utils/util.js";
import { HTTP_REQUEST_URL, LIMIT } from "@/config/app";

defineOptions({ name: "newVip" });

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

const imgHost = ref(HTTP_REQUEST_URL);
const couponList = ref([]);
const productList = ref([]);
const newcomer_integral = ref("");

// created 逻辑
getList();

// 注：原文件 computed 键重复定义（mapGetters 在前被后者覆盖，isLogin 实际不存在、watch 从未触发），
// 此处按原始意图恢复对登录态的监听
watch(
  isLogin,
  function (newV, oldV) {
    if (newV) {
      getList();
    }
  },
  { deep: true }
);

const totalPrice = computed(() => {
  return couponList.value.reduce((total, item) => {
    return util.$h.Add(total, item.coupon_price);
  }, 0);
});
const bonusStyle = computed(() => {
  let styleObject = {};
  if (props.dataConfig.toneConfig.tabVal) {
    styleObject["border-color"] =
      props.dataConfig.integralBgColor.color[0].item;
    styleObject["background"] =
      props.dataConfig.integralBgColor.color[0].item;
  }
  return styleObject;
});
const bonusRedStyle = computed(() => {
  let styleObject = {};
  if (props.dataConfig.toneConfig.tabVal) {
    if (props.dataConfig.styleConfig.tabVal) {
      styleObject["background"] =
        props.dataConfig.integralTxtColor.color[0].item;
      styleObject["color"] = props.dataConfig.integralTxtColor.color[0].item;
    } else {
      styleObject["color"] = props.dataConfig.tipsColor.color[0].item;
    }
  }
  return styleObject;
});
const moneyStyle = computed(() => {
  let styleObject = {};
  if (props.dataConfig.toneCouponConfig.tabVal) {
    styleObject["color"] = props.dataConfig.couponMoneyColor.color[0].item;
  }
  return styleObject;
});
const buttonStyle = computed(() => {
  let styleObject = {};
  if (props.dataConfig.toneConfig.tabVal) {
    if (props.dataConfig.styleConfig.tabVal) {
      styleObject[
        "background"
      ] = `linear-gradient(90deg, ${props.dataConfig.bntColor.color[0].item} 0%, ${props.dataConfig.bntColor.color[1].item} 100%)`;
    }
  }
  if (props.dataConfig.toneCouponConfig.tabVal) {
    if (!props.dataConfig.styleConfig.tabVal) {
      styleObject["color"] = props.dataConfig.bntTxtColor.color[0].item;
    }
  }
  return styleObject;
});
const couponTypeStyle = computed(() => {
  let styleObject = {};
  if (props.dataConfig.toneCouponConfig.tabVal) {
    styleObject["color"] = props.dataConfig.couponTypeColor.color[0].item;
  }
  return styleObject;
});
const couponStyle = computed(() => {
  let styleObject = {};
  if (props.dataConfig.toneCouponConfig.tabVal) {
    if (props.dataConfig.styleConfig.tabVal) {
      styleObject["margin-right"] = `${
        props.dataConfig.spacingConfig2.val * 2
      }rpx`;
      styleObject["background"] =
        props.dataConfig.couponBgColor2.color[0].item;
    } else {
      styleObject["margin-right"] = `${
        props.dataConfig.spacingConfig.val * 2
      }rpx`;
    }
  }
  return styleObject;
});
const couponContentStyle = computed(() => {
  let styleObject = {};
  if (props.dataConfig.toneCouponConfig.tabVal) {
    styleObject[
      "background"
    ] = `linear-gradient(90deg, ${props.dataConfig.couponBgColor.color[0].item} 0%, ${props.dataConfig.couponBgColor.color[1].item} 100%)`;
  }
  return styleObject;
});
const stationStyle = computed(() => {
  let styleObject = {};
  if (props.dataConfig.toneCouponConfig.tabVal) {
    styleObject[
      "background"
    ] = `linear-gradient(90deg, ${props.dataConfig.vipBgColor.color[0].item} 0%, ${props.dataConfig.vipBgColor.color[1].item} 100%)`;
  }
  return styleObject;
});
const productMoneyStyle = computed(() => {
  let styleObject = {};
  if (props.dataConfig.toneGoodsConfig.tabVal) {
    styleObject["color"] = props.dataConfig.priceColor.color[0].item;
  }
  return styleObject;
});
const couponInfoStyle = computed(() => {
  let styleObject = {};
  if (props.dataConfig.toneCouponConfig.tabVal) {
    styleObject[
      "background"
    ] = `linear-gradient(90deg, ${props.dataConfig.bntBgColor.color[1].item} 0%, ${props.dataConfig.bntBgColor.color[0].item} 100%)`;
  }
  return styleObject;
});
// 组件背景
const newVip2Background = computed(() => {
  return {
    background: `linear-gradient(90deg, ${props.dataConfig.moduleColor.color[0].item} 0%, ${props.dataConfig.moduleColor.color[1].item} 99%)`,
  };
});
// 背景圆角
const newVipBorderRadius = computed(() => {
  let borderRadius = `${props.dataConfig.fillet.val * 2}rpx`;
  if (props.dataConfig.fillet.type) {
    borderRadius = `${props.dataConfig.fillet.valList[0].val * 2}rpx ${
      props.dataConfig.fillet.valList[1].val * 2
    }rpx ${props.dataConfig.fillet.valList[3].val * 2}rpx ${
      props.dataConfig.fillet.valList[2].val * 2
    }rpx`;
  }
  return {
    "border-radius": borderRadius,
  };
});
// 底部背景
const newVipWrapStyle = computed(() => {
  return {
    "margin-top": `${props.dataConfig.mbConfig.val * 2}rpx`,
    background: props.dataConfig.bottomBgColor.color[0].item,
  };
});

function goDetail(item) {
  uni.navigateTo({
    url: `/pages/activity/goods_details/index?id=${item.id}&type=7`,
  });
}
function goNewList() {
  uni.navigateTo({
    url: `/pages/activity/new_customer/index`,
  });
}
function goUser() {
  uni.switchTab({
    url: `/pages/users/user_coupon/index`,
  });
}
// 历史遗留：newcomerList 接口在 api/api.js 中从未存在（Vue2 下加载新人专享数据即报错），
// Vite 下缺失具名导入会阻断模块加载，此处降级为本地 stub
const newcomerList = (params) => Promise.reject(new Error('newcomerList api not defined'));

function getList() {
  let limit = LIMIT;
  newcomerList({
    page: 1,
    limit: limit,
  })
    .then((res) => {
      let integral = res.data.newcomer_integral;
      couponList.value = res.data.newcomer_coupon;
      productList.value = res.data.newcomer_products;
      if (Array.isArray(integral)) {
        newcomer_integral.value = 0;
      } else {
        newcomer_integral.value = integral;
      }
    })
    .catch((err) => {
      return util.Tips({
        title: err.msg,
      });
    });
}
</script>

<style lang="scss">
.newVip {
  padding: 0 20rpx 40rpx;
  border-radius: 16rpx;
  background: #ffffff;

  .header {
    height: 96rpx;

    .title {
      font-weight: bold;
      font-size: 32rpx;
      color: #333333;
    }

    .more {
      font-size: 24rpx;
      color: #999999;
    }

    .iconfont {
      font-size: 24rpx;
    }
  }

  .bonus {
    border: 1rpx solid var(--view-theme);
    border-radius: 17rpx;
    background: var(--view-theme);

    .inner {
      height: 80rpx;
      padding: 0 32rpx;
      border-radius: 16rpx;
      background: rgba(255, 255, 255, 0.8);
      font-size: 26rpx;
      color: #333333;
    }

    .image {
      width: 48rpx;
      height: 48rpx;
      margin-right: 16rpx;
    }

    .red {
      color: var(--view-theme);
    }
  }

  .coupon {
    .title {
      padding: 28rpx 0 20rpx;
      font-weight: bold;
      font-size: 28rpx;
      line-height: 40rpx;
      color: #333333;
    }

    .content {
      position: relative;
      padding: 12rpx 190rpx 12rpx 12rpx;
      border-radius: 24rpx;
      background: linear-gradient(
        270deg,
        var(--view-theme) 0%,
        var(--view-gradient) 100%
      );
    }

    .list {
      flex-wrap: nowrap;
    }

    .item {
      flex-shrink: 0;
      position: relative;
      width: 144rpx;
      height: 188rpx;
      border-radius: 12rpx;
      margin-top: 0;
      margin-right: 8rpx;
      background: radial-gradient(
            circle at left center,
            transparent 6rpx,
            #ffffff 6rpx
          )
          top left,
        radial-gradient(circle at right center, transparent 6rpx, #ffffff 6rpx)
          top right;
      background-repeat: no-repeat;
      background-size: 78rpx 188rpx;

      &::before {
        content: "";
        position: absolute;
        top: 94rpx;
        right: 10rpx;
        left: 10rpx;
        border-top: 1rpx dashed #d8d8d8;
      }

      .item-top,
      .item-bottom {
        height: 94rpx;
      }

      .money {
        font-weight: 500;
        font-size: 28rpx;
        color: var(--view-theme);
      }

      .number {
        font-family: SemiBold;
        font-size: 40rpx;
      }

      .name {
        font-weight: 500;
        font-size: 22rpx;
        line-height: 30rpx;
        color: var(--view-theme);
      }

      .info {
        margin-top: 4rpx;
        font-size: 18rpx;
        line-height: 26rpx;
        color: #666666;
      }
    }

    .station {
      position: absolute;
      top: 0;
      right: 0;
      width: 190rpx;
      height: 100%;
      padding-top: 40rpx;
      border-radius: 0 24rpx 24rpx 0;
      background: linear-gradient(
        270deg,
        var(--view-theme) 0%,
        var(--view-gradient) 100%
      );

      &::before {
        content: "";
        position: absolute;
        top: 0;
        left: -20rpx;
        width: 20rpx;
        height: 210rpx;
        background: linear-gradient(
          270deg,
          rgba(0, 0, 0, 0.2) 0%,
          rgba(255, 255, 255, 0) 100%
        );
      }

      .money {
        text-align: center;
        font-weight: 500;
        font-size: 28rpx;
        color: #ffffff;
      }

      .number {
        font-family: SemiBold;
        font-size: 40rpx;
      }

      .info {
        margin-top: 18rpx;
        text-align: center;
        font-size: 20rpx;
        line-height: 28rpx;
        color: #ffffff;
      }

      .button {
        width: 136rpx;
        height: 52rpx;
        border-radius: 26rpx;
        margin: 12rpx auto 0;
        background: #ffffff;
        text-align: center;
        font-weight: 500;
        font-size: 22rpx;
        line-height: 52rpx;
        color: var(--view-theme);
      }
    }
  }

  .product {
    .title {
      padding: 28rpx 0 20rpx;
      font-weight: bold;
      font-size: 28rpx;
      line-height: 40rpx;
      color: #333333;
    }

    .list {
      flex-wrap: nowrap;
    }

    .item {
      width: 158rpx;
      margin-top: 0;
      margin-right: 12rpx;
    }

    .name {
      margin-top: 12rpx;
      text-align: center;
      font-size: 24rpx;
      line-height: 34rpx;
      color: #333333;
    }

    .money {
      margin-top: 8rpx;
      text-align: center;
      font-weight: 600;
      font-size: 22rpx;
      color: var(--view-theme);
    }

    .number {
      font-family: SemiBold;
      font-size: 32rpx;
    }
  }
}

.newVip2 {
  padding: 0 24rpx 32rpx;
  border-radius: 24rpx;
  background: linear-gradient(270deg, #ff7931 0%, #e93323 100%);

  .header {
    height: 92rpx;

    .info {
      position: relative;
      padding-left: 12rpx;
      margin-left: 16rpx;
      font-size: 24rpx;
      color: #ffffff;

      &::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 0;
        width: 1rpx;
        height: 20rpx;
        background-color: #ffffff;
        transform: translateY(-50%);
      }
    }

    .more {
      font-size: 24rpx;
      color: #ffffff;
    }

    .iconfont {
      font-size: 24rpx;
    }

    .image {
      width: 156rpx;
      height: 28rpx;
    }
  }

  .wrapper {
    border-radius: 16rpx;
    background: #ffffff;
  }

  .coupon {
    padding-bottom: 20rpx;
    .title {
      padding: 24rpx 20rpx 20rpx;
      font-weight: bold;
      font-size: 28rpx;
      line-height: 40rpx;
      color: #333333;
    }

    .list {
      flex-wrap: nowrap;
      padding-left: 20rpx;
    }

    .item {
      flex-shrink: 0;
      position: relative;
      width: 152rpx;
      height: 168rpx;
      margin-top: 0;
      margin-right: 24rpx;
    }

    .back {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 152rpx;
      height: 148rpx;
      border-radius: 20rpx;
      background: var(--view-theme);
    }

    .money {
      position: absolute;
      bottom: 12rpx;
      left: 6rpx;
      width: 140rpx;
      height: 156rpx;
      padding-top: 36rpx;
      border: 2rpx solid #fceae9;
      border-radius: 20rpx;
      background: #ffffff;
      text-align: center;
      font-weight: 500;
      font-size: 28rpx;
      color: var(--view-theme);

      .number {
        font-family: SemiBold;
        font-size: 40rpx;
      }
    }

    .info {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 152rpx;
      height: 78rpx;
      padding-top: 34rpx;
      border-radius: 0 0 20rpx 20rpx;
      background: linear-gradient(
        90deg,
        var(--view-theme) 0%,
        var(--view-gradient) 100%
      );
      text-align: center;
      font-size: 20rpx;
      line-height: 28rpx;
      color: #ffffff;

      &::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 152rpx;
        height: 20rpx;
        border: 8rpx solid #fceae9;
        border-bottom-right-radius: 76rpx 20rpx;
        border-bottom-left-radius: 76rpx 20rpx;
        border-top-color: transparent;
        box-sizing: border-box;
        background: #ffffff;
      }
    }
  }

  .product {
    padding-bottom: 20rpx;
    .title {
      padding: 20rpx 20rpx 20rpx;
      font-weight: bold;
      font-size: 28rpx;
      line-height: 40rpx;
      color: #333333;
    }

    .list {
      padding: 0 20rpx;
    }

    .item {
      width: 144rpx;
      margin-top: 0;
      margin-right: 15rpx;
    }

    .money {
      margin-top: 18rpx;
      text-align: center;
      font-weight: 500;
      font-size: 32rpx;
      color: #333333;
    }

    .number {
      font-family: SemiBold;
    }

    .name {
      margin-top: 6rpx;
      text-align: center;
      font-size: 24rpx;
      line-height: 34rpx;
      color: #333333;
    }
  }

  .bonus {
    padding: 26rpx 26rpx 26rpx 20rpx;
    border-radius: 16rpx;
    margin-top: 20rpx;
    background: #ffffff;

    .image {
      width: 88rpx;
      height: 88rpx;
    }

    .text {
      flex: 1;
      padding-left: 20rpx;
      font-size: 24rpx;
      color: #999999;
    }

    .info {
      margin-bottom: 10rpx;
      font-weight: bold;
      font-size: 30rpx;
      line-height: 42rpx;
      color: #333333;
    }

    .red {
      border-radius: 16rpx;
      margin-left: 8rpx;
      background: var(--view-theme);
      font-weight: 400;
      font-size: 20rpx;
      line-height: 32rpx;
      color: var(--view-theme);

      .image {
        width: 24rpx;
        height: 24rpx;
      }

      .inner {
        height: 32rpx;
        padding: 0 7rpx;
        border-radius: 16rpx;
        background: rgba(255, 255, 255, 0.9);
      }
    }

    .button {
      width: 114rpx;
      height: 52rpx;
      border-radius: 26rpx;
      background: linear-gradient(
        90deg,
        var(--view-theme) 0%,
        var(--view-gradient) 100%
      );
      text-align: center;
      font-weight: 500;
      font-size: 22rpx;
      line-height: 52rpx;
      color: #ffffff;
    }
  }
}
</style>
