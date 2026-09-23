<template>
  <view class="product-service" v-if="!isHide">
    <commonWrapper :config="dataConfig">
      <view class="service-list">
        <!-- Activity -->
        <view
          class="item"
          v-if="checkList.includes(0) && (couponList.length || activity.length)"
        >
          <view class="label" :style="{ color: titleColor }">{{
            $t("活动")
          }}</view>
          <view class="content" @click="activityTap">
            <view class="tags-wrapper">
              <!-- Coupon -->
              <view
                class="tag-item"
                v-if="couponList.length"
                :style="tagStyle"
                @click.stop="activityTap"
              >
                <text class="iconfont icon-ic_sale"></text>
                {{ $t("优惠券")
                }}<text
                  class="iconfont icon-you2"
                  :style="{ color: activityColor }"
                ></text>
              </view>
              <!-- Activity -->
              <block v-for="(item, index) in activity" :key="index">
                <view
                  class="tag-item"
                  v-if="item.type === '1'"
                  :style="tagStyle"
                  @click.stop="goActivity(item)"
                >
                  <text class="iconfont icon-miaosha1"></text>
                  {{ $t("秒杀")
                  }}<text
                    class="iconfont icon-you2"
                    :style="{ color: activityColor }"
                  ></text>
                </view>
                <view
                  class="tag-item"
                  v-if="item.type === '2'"
                  :style="tagStyle"
                  @click.stop="goActivity(item)"
                >
                  <text class="iconfont icon-yaoqinghaoyou1"></text>
                  {{ $t("砍价")
                  }}<text
                    class="iconfont icon-you2"
                    :style="{ color: activityColor }"
                  ></text>
                </view>
                <view
                  class="tag-item"
                  v-if="item.type === '3'"
                  :style="tagStyle"
                  @click.stop="goActivity(item)"
                >
                  <text class="iconfont icon-wodetuandui"></text>
                  {{ $t("拼团")
                  }}<text
                    class="iconfont icon-you2"
                    :style="{ color: activityColor }"
                  ></text>
                </view>
              </block>
            </view>
            <text
              class="iconfont icon-jiantou"
              :style="{ color: contentColor }"
            ></text>
          </view>
        </view>

        <!-- Selection -->
        <view
          class="item"
          v-if="
            checkList.includes(1) && attr.productAttr && attr.productAttr.length
          "
          @click="showSpecModal"
        >
          <view class="label" :style="{ color: titleColor }">{{
            $t("选择")
          }}</view>
          <view class="content">
            <view class="text line1" :style="{ color: contentColor }">
              {{ attrTxt }}：{{ attrValue }}
            </view>
            <text
              class="iconfont icon-jiantou"
              :style="{ color: contentColor }"
            ></text>
          </view>
        </view>

        <!-- Parameters -->
        <view
          class="item"
          v-if="
            checkList.includes(2) &&
            productData.params_list &&
            productData.params_list.length
          "
          @click="openModal('specs')"
        >
          <view class="label" :style="{ color: titleColor }">{{
            $t("参数")
          }}</view>
          <view class="content">
            <view class="text line1" :style="{ color: contentColor }">
              <text
                v-for="(item, index) in productData.params_list"
                :key="index"
              >
                {{ item.name }} ·
              </text>
            </view>
            <text
              class="iconfont icon-jiantou"
              :style="{ color: contentColor }"
            ></text>
          </view>
        </view>

        <!-- Service -->
        <view
          class="item"
          v-if="
            checkList.includes(3) &&
            productData.protection_list &&
            productData.protection_list.length
          "
          @click="openModal('protection')"
        >
          <view class="label" :style="{ color: titleColor }">{{
            $t("服务")
          }}</view>
          <view class="content">
            <view class="text line1" :style="{ color: contentColor }">
              <text
                v-for="(item, index) in productData.protection_list"
                :key="index"
              >
                {{ item.title }} ·
              </text>
            </view>
            <text
              class="iconfont icon-jiantou"
              :style="{ color: contentColor }"
            ></text>
          </view>
        </view>
      </view>
    </commonWrapper>
  </view>
</template>

<script setup>
import { computed } from "vue";
import commonWrapper from "./commonWrapper.vue";

defineOptions({ name: "homeProductService" });

const emit = defineEmits(["showCoupon", "goActivity", "showSpecModal", "openModal"]);

const props = defineProps({
  dataConfig: {
    type: Object,
    default: () => ({}),
  },
  productData: {
    type: Object,
    default: () => ({}),
  },
  couponList: {
    type: Array,
    default: () => [],
  },
  activity: {
    type: Array,
    default: () => [],
  },
  attr: {
    type: Object,
    default: () => ({}),
  },
  attrTxt: {
    type: String,
    default: "",
  },
  attrValue: {
    type: String,
    default: "",
  },
});

const isHide = computed(() => {
  return props.dataConfig.isHide;
});
const checkList = computed(() => {
  return props.dataConfig.checkBoxConfig
    ? props.dataConfig.checkBoxConfig.type
    : [];
});
const titleColor = computed(() => {
  return props.dataConfig.titleColor
    ? props.dataConfig.titleColor.color[0].item
    : "#999999";
});
const contentColor = computed(() => {
  return props.dataConfig.contentColor
    ? props.dataConfig.contentColor.color[0].item
    : "#333333";
});
const isCustomTone = computed(() => {
  return (
    props.dataConfig.toneConfig && props.dataConfig.toneConfig.tabVal === 1
  );
});
const tagStyle = computed(() => {
  if (isCustomTone.value) {
    const color = props.dataConfig.activityColor
      ? props.dataConfig.activityColor.color[0].item
      : "var(--view-theme)";
    const bg = props.dataConfig.activityBgColor
      ? props.dataConfig.activityBgColor.color[0].item
      : "var(--view-minorColorT)";
    return {
      color: color,
      background: bg,
    };
  }
  return {
    color: "var(--view-theme)",
    background: "var(--view-minorColorT)",
  };
});
const activityColor = computed(() => {
  if (isCustomTone.value) {
    return props.dataConfig.activityColor
      ? props.dataConfig.activityColor.color[0].item
      : "var(--view-theme)";
  }
  return "var(--view-theme)";
});

function activityTap() {
  if (props.couponList.length) {
    emit("showCoupon");
  } else if (props.activity.length) {
  }
}
function goActivity(item) {
  emit("goActivity", item);
}
function showSpecModal() {
  emit("showSpecModal");
}
function openModal(type) {
  emit("openModal", type);
}
</script>

<style lang="scss" scoped>
.product-service {
  .service-list {
    .item {
      display: flex;
      align-items: center;
      padding: 28rpx 0rpx;
      position: relative;

      .label {
        width: 80rpx;
        font-size: 28rpx;
        margin-right: 20rpx;
      }

      .content {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        overflow: hidden;

        .text {
          font-size: 28rpx;
          color: #333;
        }

        .tags-wrapper {
          display: flex;
          flex-wrap: wrap;

          .tag-item {
            font-size: 24rpx;
            padding: 3rpx 10rpx;
            border-radius: 30rpx;
            margin-right: 10rpx;
            display: flex;
            align-items: center;
            .iconfont {
              font-size: 18rpx;
              line-height: 22rpx;
              &:first-child {
                font-size: 24rpx;
                margin-right: 5rpx;
              }
            }
          }
        }

        .iconfont {
          font-size: 24rpx;
        }
      }
    }
  }
}
</style>
