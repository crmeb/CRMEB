<template>
  <view class="home-paid-vip" v-if="isShowPaidVip">
    <commonWrapper :config="dataConfig">
      <view class="vip-container" :style="[containerStyle]">
        <view class="left-content">
          <image
            :src="imgUrl"
            class="vip-icon"
            v-if="imgUrl"
            mode="heightFix"
          ></image>
          <view class="text-content" :style="{ color: tipsTextColor }">
            {{ $t(`开通SVIP会员预计省`) }}
            <text :style="{ color: moneyTextColor }">{{ diff }}</text>
            {{ $t(`元`) }}
          </view>
        </view>
        <view class="right-content">
          <navigator :render-link="false"
            url="/pages/annex/vip_paid/index"
            hover-class="none"
            class="open-btn"
            :style="[btnStyle]"
          >
            {{ rightBntText }}
            <text class="iconfont icon-jiantou"></text>
          </navigator>
        </view>
      </view>
    </commonWrapper>
  </view>
</template>

<script setup>
import { computed } from "vue";
import { storeToRefs } from "pinia";
import commonWrapper from "./commonWrapper.vue";
import { useAppStore } from "@/store/modules/app";
import i18n from "@/utils/lang.js";

defineOptions({ name: "homePaidVip" });

const props = defineProps({
  dataConfig: {
    type: Object,
    default: () => ({}),
  },
  productData: {
    type: Object,
    default: () => ({}),
  },
  priceData: {
    type: Object,
    default: () => ({}),
  },
  isShowPaidVip: {
    type: Boolean,
    default: false,
  },
});

const appStore = useAppStore();
const { isLogin, userInfo } = storeToRefs(appStore);

const diff = computed(() => {
  let price = Number(props.priceData.price) || 0;
  let memberPrice = Number(props.priceData.member_price) || 0;
  let val = (price - memberPrice).toFixed(2);
  return val > 0 ? val : "0.00";
});
const containerStyle = computed(() => {
  return {};
});
const imgUrl = computed(() => {
  return props.dataConfig.imgConfig ? props.dataConfig.imgConfig.url : "";
});
const rightBntText = computed(() => {
  return props.dataConfig.rightBntConfig
    ? props.dataConfig.rightBntConfig.value
    : i18n.t("立即开通");
});
const isCustomTone = computed(() => {
  return (
    props.dataConfig.toneConfig && props.dataConfig.toneConfig.tabVal === 1
  );
});
const tipsTextColor = computed(() => {
  return isCustomTone.value && props.dataConfig.tipsColor
    ? props.dataConfig.tipsColor.color[0].item
    : "#333333";
});
const moneyTextColor = computed(() => {
  return isCustomTone.value && props.dataConfig.moneyColor
    ? props.dataConfig.moneyColor.color[0].item
    : "#F62C2C";
});
const btnStyle = computed(() => {
  return {
    color:
      isCustomTone.value && props.dataConfig.btnColor
        ? props.dataConfig.btnColor.color[0].item
        : "#FF6B00",
    fontSize:
      (props.dataConfig.btnConfig ? props.dataConfig.btnConfig.val : 13) * 2 +
      "rpx",
  };
});
</script>

<style lang="scss" scoped>
.home-paid-vip {
  .vip-container {
    display: flex;
    justify-content: space-between;
    align-items: center;

    .left-content {
      display: flex;
      align-items: center;

      .vip-icon {
        width: 36rpx; // Default size from admin component info
        height: 36rpx;
        margin-right: 16rpx;
      }

      .text-content {
        font-size: 26rpx;
        font-weight: 400;
      }
    }

    .right-content {
      .open-btn {
        display: flex;
        align-items: center;
        font-weight: 500;

        .iconfont {
          font-size: 24rpx;
          margin-left: 4rpx;
        }
      }
    }
  }
}
</style>
