<template>
  <common-wrapper v-if="signEnabled" :config="configData">
    <view class="signIn" :style="[signStyle]">
      <view
        v-if="dataConfig.styleConfig.tabVal"
        class="text-wrap acea-row row-middle"
      >
        <image
          src="@/static/images/sign-icon-04.png"
          mode="widthFix"
          class="image"
        ></image>
        <view class="text">
          <view class="name-wrap acea-row row-middle">
            <view class="name">{{ $t(`签到立即获取`) }}</view>
            <view class="num acea-row row-middle" :style="[numStyle]">
              <view
                class="inner acea-row row-middle"
                :class="{ opacity: !dataConfig.toneConfig.tabVal }"
              >
                <image
                  src="@/static/images/sign-icon-01.png"
                  mode="widthFix"
                  class="icon"
                ></image>
                +{{ sign_give_point }}
              </view>
            </view>
          </view>
          <view
            >{{ $t(`连续签到`) }} {{ continuousSignDays || 0
            }}{{ $t(`天`) }}</view
          >
        </view>
        <view class="button" :style="[buttonStyle]" @click="goUserSgin">{{
          checkSign ? $t(`已签到`) : $t(`立即签到`)
        }}</view>
      </view>
      <view v-else class="week-wrap acea-row row-middle">
        <view class="week acea-row">
          <view
            v-for="(item, index) in signList"
            :key="index"
            class="item acea-row row-column row-center-wrapper"
          >
            <image
              v-if="item.is_sign"
              src="@/static/images/sign-icon-03.png"
              mode="widthFix"
              class="image"
            ></image>
            <image
              v-else-if="item.type == 1"
              src="@/static/images/sign-icon-01.png"
              mode="widthFix"
              class="image"
            ></image>
            <image
              v-else-if="item.type == 3 || item.type == 4"
              src="@/static/images/sign-icon-04.png"
              mode="widthFix"
              class="image"
            ></image>
            <view>{{ $t(`周`) }}{{ weekFormat(index) }}</view>
          </view>
        </view>
        <view class="button" :style="[buttonStyle]" @click="goUserSgin">{{
          checkSign ? $t(`已签到`) : $t(`签到`)
        }}</view>
      </view>
    </view>
  </common-wrapper>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from "vue";
import { storeToRefs } from "pinia";
import commonWrapper from "./commonWrapper.vue";
import { getSign } from "@/api/api.js";
import { useAppStore } from "@/store/modules/app";

defineOptions({ name: "signIn" });

const props = defineProps({
  dataConfig: {
    type: Object,
    default: () => ({}),
  },
  isSortType: {
    type: [String, Number],
    default: 0,
  },
  isPageVisible: {
    type: Boolean,
    default: true,
  },
});

const { isLogin, uid } = storeToRefs(useAppStore());
const signList = ref([]);
const sign_give_point = ref(0);
const continuousSignDays = ref(0);
const checkSign = ref(false);
const signEnabled = ref(false);
let mounted = false;
let requestVersion = 0;
let refreshPromise = null;
let refreshKey = "";
let dailyTimer;

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
const buttonStyle = computed(() => {
  let styleObject = {};
  if (props.dataConfig.toneConfig.tabVal) {
    styleObject[
      "background"
    ] = `linear-gradient(90deg, ${props.dataConfig.bntBgColor.color[0].item} 0%, ${props.dataConfig.bntBgColor.color[1].item} 100%)`;
    styleObject["color"] = props.dataConfig.bntTxtColor.color[0].item;
  }
  return styleObject;
});
const signStyle = computed(() => {
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
// signWrapStyle() {
// 	return {
// 		padding: `${this.dataConfig.topConfig.val * 2}rpx ${this.dataConfig.prConfig.val * 2}rpx ${this.dataConfig.bottomConfig.val * 2}rpx`,
// 		'margin-top': `${this.dataConfig.mbConfig.val * 2}rpx`,
// 		background: this.dataConfig.bottomBgColor.color[0].item
// 	};
// },
const numStyle = computed(() => {
  let styleObject = {};
  if (props.dataConfig.toneConfig.tabVal) {
    styleObject["background"] = props.dataConfig.labelBgColor.color[0].item;
    styleObject["color"] = props.dataConfig.labelTxtColor.color[0].item;
  }
  return styleObject;
});

onMounted(() => {
  mounted = true;
  uni.$on("signInRefresh", handleSignRefresh);
  getSignData();
  if (props.isPageVisible) scheduleDailyRefresh();
});

onBeforeUnmount(() => {
  mounted = false;
  requestVersion++;
  clearTimeout(dailyTimer);
  uni.$off("signInRefresh", handleSignRefresh);
});

watch([isLogin, uid], () => {
  requestVersion++;
  refreshPromise = null;
  signList.value = [];
  sign_give_point.value = 0;
  continuousSignDays.value = 0;
  checkSign.value = false;
  signEnabled.value = false;
  if (mounted && props.isPageVisible) getSignData();
});

watch(() => props.isPageVisible, (visible) => {
  if (!mounted) return;
  if (visible) {
    handleSignRefresh();
  } else {
    clearTimeout(dailyTimer);
  }
});

function weekFormat(value) {
  return ["一", "二", "三", "四", "五", "六", "日"][value];
}

function goUserSgin() {
  uni.navigateTo({
    url: "/pages/users/user_sgin/index",
  });
}

function getRefreshKey() {
  const now = new Date();
  return `${isLogin.value ? uid.value : 0}:${now.getFullYear()}-${now.getMonth()}-${now.getDate()}`;
}

function scheduleDailyRefresh() {
  clearTimeout(dailyTimer);
  const now = new Date();
  const midnight = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1);
  dailyTimer = setTimeout(() => {
    if (mounted && props.isPageVisible) {
      getSignData();
      scheduleDailyRefresh();
    }
  }, midnight.getTime() - now.getTime() + 100);
}

function handleSignRefresh(event) {
  if (!mounted) return;
  if (props.isPageVisible) scheduleDailyRefresh();
  return getSignData(event?.signed === true);
}

function getSignData(force = false) {
  const key = getRefreshKey();
  if (force !== true && refreshPromise && refreshKey === key) return refreshPromise;
  const version = ++requestVersion;
  refreshKey = key;
  refreshPromise = getSign().then((res) => {
    if (version !== requestVersion || key !== getRefreshKey()) return;
    const data = res.data || {};
    continuousSignDays.value = Number(data.continuousSignDays) || 0;
    sign_give_point.value = Number(data.signGivePoint) || 0;
    signList.value = Array.isArray(data.signList?.[0]) ? data.signList[0] : [];
    checkSign.value = !!Number(data.checkSign);
    signEnabled.value = Number(data.signStatus) === 1;
  }).catch(() => {
    if (version === requestVersion) signEnabled.value = false;
  }).finally(() => {
    if (version === requestVersion) refreshPromise = null;
  });
  return refreshPromise;
}
</script>

<style lang="scss" scoped>
.signIn {
  // padding: 24rpx;
  // border-radius: 0rpx 0rpx 16rpx 16rpx;
  // background: #ffffff;

  .week-wrap {
    height: 156rpx;
    // border-radius: 16rpx;
    // background: linear-gradient(to right, #ffe8f5, #f1fbfd);

    .week {
      flex: 1;
      font-weight: 500;
      font-size: 22rpx;
      line-height: 30rpx;
      color: #999999;

      .item {
        flex: 1;
      }

      .image {
        width: 48rpx;
        margin-bottom: 16rpx;
      }
    }

    .button {
      width: 88rpx;
      height: 48rpx;
      border-radius: 24rpx;
      margin: 0 20rpx;
      background: linear-gradient(
        90deg,
        var(--view-theme) 0%,
        var(--view-gradient) 100%
      );
      text-align: center;
      font-size: 24rpx;
      line-height: 48rpx;
      color: #ffffff;
    }
  }

  .text-wrap {
    height: 140rpx;
    border-radius: 16rpx;
    background: #ffffff;

    .image {
      width: 88rpx;
      height: 88rpx;
      margin-left: 20rpx;
    }

    .text {
      flex: 1;
      padding: 0 20rpx;
      font-size: 24rpx;
      color: #999999;
    }

    .name-wrap {
      margin-bottom: 14rpx;
    }

    .name {
      font-weight: 500;
      font-size: 30rpx;
      color: #282828;
    }

    .num {
      border-radius: 16rpx;
      margin-left: 4rpx;
      background: var(--view-theme);
      font-size: 20rpx;
      line-height: 32rpx;
      color: var(--view-theme);

      .inner {
        height: 32rpx;
        padding: 0 6rpx 0 8rpx;
        border-radius: 16rpx;
      }

      .opacity {
        background: rgba(255, 255, 255, 0.9);
      }
    }

    .icon {
      width: 24rpx;
      height: 24rpx;
    }

    .button {
      width: 140rpx;
      height: 52rpx;
      border-radius: 26rpx;
      margin: 0 20rpx;
      background: linear-gradient(
        90deg,
        var(--view-theme) 0%,
        var(--view-gradient) 100%
      );
      text-align: center;
      font-size: 24rpx;
      line-height: 52rpx;
      color: #ffffff;
    }
  }
}
</style>
