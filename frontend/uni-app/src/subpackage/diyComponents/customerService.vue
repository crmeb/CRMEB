<template>
  <!-- 在线客服 -->
  <view class="custmer" v-show="!isSortType">
    <!-- #ifdef H5 || APP-PLUS -->
    <view
      class="customerService"
      :class="positions ? '' : 'on'"
      :style="'top:' + topConfig"
      @touchmove.stop.prevent="setTouchMove"
      @click="licks"
    >
      <view class="pictrue">
        <image :src="logoConfig"></image>
      </view>
    </view>
    <!-- #endif -->
    <!-- #ifdef MP -->
    <view
      class="customerService"
      :class="positions ? '' : 'on'"
      :style="'top:' + topConfig"
      @touchmove.stop.prevent="setTouchMove"
      v-if="routineContact === 0"
      @click="licks"
    >
      <view class="pictrue">
        <image :src="logoConfig"></image>
      </view>
    </view>
    <button
      class="customerService-sty"
      :class="positions ? '' : 'on'"
      :style="'top:' + topConfig"
      @touchmove.stop.prevent="setTouchMove"
      open-type="contact"
      v-if="routineContact === 1"
    >
      <image class="pictrue" :src="logoConfig"></image>
    </button>
    <!-- #endif -->
  </view>
</template>

<script setup>
import { ref, computed } from "vue";
import { storeToRefs } from "pinia";
import { useAppStore } from "@/store/modules/app";
import { getCustomer } from "@/utils/index.js";
import util from "@/utils/util.js";

defineOptions({ name: "customerService" });

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
const { userInfo } = storeToRefs(appStore);

const routineContact = ref(parseFloat(props.dataConfig.routine_contact_type));
const logoConfig = ref(props.dataConfig.logoConfig.url);
const topConfig = ref(
  props.dataConfig.marginConfig.val
    ? props.dataConfig.marginConfig.val >= 80
      ? 80 + "%"
      : props.dataConfig.marginConfig.val + "%"
    : "30%"
);
const positions = ref(props.dataConfig.locationConfig.tabVal);

function licks() {
  if (props.dataConfig.buttonConfig.tabVal) {
    getCustomer(`/pages/extension/customer_list/chat`);
  } else {
    util.JumpPath(props.dataConfig.logoConfig.link);
  }
}

function setTouchMove(e) {
  if (e.touches[0].clientY < 545 && e.touches[0].clientY > 66) {
    topConfig.value = e.touches[0].clientY + "px";
  }
}
</script>

<style lang="scss">
.custmer {
  touch-action: none;
}
.customerService,
.customerService-sty {
  position: fixed;
  right: 20rpx;
  z-index: 40;
  &.on {
    left: 20rpx;
  }
  .pictrue {
    width: 86rpx;
    height: 86rpx;
    border-radius: 50%;

    image {
      width: 100%;
      height: 100%;
      border-radius: 50%;
    }
  }
}
.customerService-sty {
  background-color: rgba(0, 0, 0, 0) !important;
}
</style>
