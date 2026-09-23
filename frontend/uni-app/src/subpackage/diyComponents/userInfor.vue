<template>
  <common-wrapper :config="configData">
    <view class="userInfor" :style="[userInfoStyle]" @click="goLogin">
      <view class="left acea-row row-middle">
        <view class="pictrue acea-row row-center-wrapper relative">
          <image :src="diyInfo.avatar" v-if="diyInfo.avatar && isLogin"></image>
          <image
            src="@/static/images/king.png"
            class="king"
            v-if="diyInfo.is_money_level"
          ></image>
          <image
            v-if="!diyInfo.avatar && isLogin"
            :src="dataConfig.logoConfig.url || '@/static/images/f.png'"
          ></image>
          <image
            v-if="!isLogin"
            :src="dataConfig.logoConfig.url || '@/static/images/f.png'"
          ></image>
        </view>
        <view class="text">
          <view v-if="!isLogin" class="name">{{ $t(`请点击登录`) }}</view>
          <view v-else class="name acea-row row-middle">
            <view class="nameCon line1">{{ diyInfo.nickname }}</view>
            <view
              class="lable acea-row row-middle"
              v-if="diyInfo.level > 0 && (diyInfo.vip_icon || diyInfo.vip_name)"
              :style="[lableStyle]"
            >
              <img
                class="icon"
                v-if="diyInfo.vip_icon"
                :src="diyInfo.vip_icon"
                alt=""
              />
              {{ diyInfo.vip_name }}
            </view>
          </view>
          <view
            class="acea-row row-middle"
            v-if="isLogin && diyInfo.vip && diyInfo.level > 0"
          >
            <view class="progress" :style="[progressStyle]">
              <view class="bg-reds" :style="[bgRedsStyle]"> </view>
            </view>
            <view class="percent"
              >{{ diyInfo.exp ? diyInfo.exp.split(".")[0] : 0 }}
              {{ diyInfo.next_exp ? `/ ${diyInfo.next_exp}` : "" }}</view
            >
          </view>
          <view
            class="phone acea-row row-middle"
            v-if="isLogin && diyInfo.level <= 0 && diyInfo.phone"
          >
            <text>{{ diyInfo.phone }}</text>
          </view>
        </view>
        <view v-if="isLogin" class="right acea-row row-bottom">
          <template v-if="dataConfig.styleConfig.tabVal == 1">
            <view
              class="item"
              v-if="checkType.indexOf(1) > -1"
              @click.stop="handleMenu(1)"
            >
              <view class="num">{{ diyInfo.integral || 0 }}</view>
              <view>{{ $t(`积分`) }}</view>
            </view>
            <view
              class="item"
              v-if="checkType.indexOf(2) > -1"
              @click.stop="handleMenu(2)"
            >
              <view class="num">{{ diyInfo.now_money || 0 }}</view>
              <view>{{ $t(`余额`) }}</view>
            </view>
            <view
              class="item"
              v-if="checkType.indexOf(0) > -1"
              @click.stop="handleMenu(0)"
            >
              <view class="num">{{ diyInfo.couponCount || 0 }}</view>
              <view>{{ $t(`优惠券`) }}</view>
            </view>
            <view
              class="item"
              v-if="checkType.indexOf(4) > -1"
              @click.stop="handleMenu(4)"
            >
              <view class="num">{{ diyInfo.collectCount || 0 }}</view>
              <view>{{ $t(`收藏`) }}</view>
            </view>
            <view
              class="item"
              v-if="checkType.indexOf(5) > -1"
              @click.stop="handleMenu(5)"
            >
              <view class="num">{{ diyInfo.visit_num || 0 }}</view>
              <view>{{ $t(`浏览`) }}</view>
            </view>
          </template>
        </view>
      </view>
      <view
        v-if="dataConfig.styleConfig.tabVal == 0 && isLogin"
        class="bottom acea-row row-middle"
      >
        <view
          v-if="checkType.indexOf(1) != -1"
          class="item"
          @click.stop="handleMenu(1)"
        >
          <view class="num">{{ diyInfo.integral || 0 }}</view>
          <view>{{ $t(`积分`) }}</view>
        </view>
        <view
          v-if="checkType.indexOf(2) != -1"
          class="item"
          @click.stop="handleMenu(2)"
        >
          <view class="num">{{ diyInfo.now_money || 0 }}</view>
          <view>{{ $t(`余额`) }}</view>
        </view>
        <view
          v-if="checkType.indexOf(0) != -1"
          class="item"
          @click.stop="handleMenu(0)"
        >
          <view class="num">{{ diyInfo.coupon_num || 0 }}</view>
          <view>{{ $t(`优惠券`) }}</view>
        </view>
        <view
          v-if="checkType.indexOf(4) != -1"
          class="item"
          @click.stop="handleMenu(4)"
        >
          <view class="num">{{ diyInfo.collectCount || 0 }}</view>
          <view>{{ $t(`收藏`) }}</view>
        </view>
        <view
          v-if="checkType.indexOf(5) != -1"
          class="item"
          @click.stop="handleMenu(5)"
        >
          <view class="num">{{ diyInfo.visit_num || 0 }}</view>
          <view>{{ $t(`浏览`) }}</view>
        </view>
      </view>
      <!-- <view class="codePopup" :style="colorStyle" v-show="isCode">
			<view class="header acea-row row-between-wrapper">
				<view class="title" :class="{'on': codeIndex == index,'onLeft':codeIndex == 1}" v-for="(item, index) in codeList" :key="index" @click="tapCode(index)">{{item.name}}</view>
			</view>
			<view>
				<view class="acea-row row-center-wrapper">
					<w-barcode :options="config.bar"></w-barcode>
				</view>
				<view class="acea-row row-center-wrapper" style="margin-top: 35rpx;">
					<w-qrcode :options="config.qrc" @generate="hello"></w-qrcode>
				</view>
				<view class="codeNum">{{config.bar.code}}</view>
				<view class="tip">{{ $t(`如遇到扫码失败请将屏幕调至最亮重新扫码`) }}</view>
			</view>
			<view class="iconfont icon-guanbi2" @click="closeCode"></view>
		</view> -->
      <!-- <view class="mark" v-if="isCode"></view> -->
    </view>
  </common-wrapper>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { storeToRefs } from "pinia";
import commonWrapper from "./commonWrapper.vue";
import { useColorTheme } from "@/composables/useColorTheme";
import { getlevelInfo as getlevelInfoApi, getUserInfo } from "@/api/user.js";
import { useAppStore } from "@/store/modules/app";
import util from "@/utils/util.js";

defineOptions({ name: "userInfor" });

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

const { colorStyle } = useColorTheme();

const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

// 遗留判断：原 closeCode 中 this.isextension 从未在 data 声明（等效 no-op），以同名 undefined 变量保持同等语义
var isextension;

const config = ref({
  bar: {
    code: "",
    color: ["#000"],
    bgColor: "#FFFFFF", // 背景色
    width: 480, // 宽度
    height: 110, // 高度
  },
  qrc: {
    code: "",
    size: 380, // 二维码大小
    level: 3, //等级 0～4
    bgColor: "#FFFFFF", //二维码背景色 默认白色
    border: {
      color: ["#eee", "#eee"], //边框颜色支持渐变色
      lineWidth: 3, //边框宽度
    },
    // img: '/static/logo.png', //图片
    // iconSize: 40, //二维码图标的大小
    color: ["#333", "#333"], //边框颜色支持渐变色
  },
});
const codeList = ref([
  {
    name: "会员码",
  },
  {
    name: "付款码",
  },
]);
const codeIndex = ref(0);
const isCode = ref(false);
const bgColor = ref("");
const textColor = ref("");
const progressColor = ref(props.dataConfig.progressColor.color);
const mbCongfig = ref(0);
const prConfig = ref(0); //背景边距
const itemStyle = ref(0);
const checkType = ref(props.dataConfig.checkboxInfo.type);
const diyInfo = ref({});
const currentLevelColor = ref("");

// created 逻辑
if (isLogin.value) {
  getDiyUserInfo();
  getLevelInfo();
}

watch(
  isLogin,
  function (newV, oldV) {
    if (newV) {
      getDiyUserInfo();
    }
  },
  { deep: true }
);

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
const lableStyle = computed(() => {
  let styleObject = {
    background: currentLevelColor.value,
    color: currentLevelColor.value,
  };
  return styleObject;
});
// componentStyle() {
// 	return {
// 		'padding': `${this.dataConfig.topConfig.val * 2}rpx ${this.dataConfig.prConfig.val * 2}rpx ${this.dataConfig.bottomConfig.val * 2}rpx`,
// 		'margin-top': `${this.dataConfig.mbConfig.val * 2}rpx`,
// 		'background': this.dataConfig.bottomBgColor.color[0].item,
// 	};
// },
const progressStyle = computed(() => {
  let styleObject = {};
  if (props.dataConfig.toneConfig.tabVal) {
    styleObject["background"] =
      props.dataConfig.progressBgColor.color[0].item;
  }
  return styleObject;
});
const bgRedsStyle = computed(() => {
  let info = diyInfo.value;
  let styleObject = {
    width: `${
      info.exp > info.next_exp
        ? 100
        : util.$h.Div(parseInt(info.exp), info.next_exp) *
              100 >=
            5
          ? util.$h.Div(parseInt(info.exp), info.next_exp) * 100
          : 5
    }%`,
  };
  if (props.dataConfig.toneConfig.tabVal) {
    styleObject["background"] =
      `linear-gradient(90deg, ${props.dataConfig.progressColor.color[0].item} 0%, ${props.dataConfig.progressColor.color[1].item} 100%)`;
  }
  return styleObject;
});
const userInfoStyle = computed(() => {
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
    background: `linear-gradient(90deg, ${props.dataConfig.moduleColor.color[0].item} 0%, ${props.dataConfig.moduleColor.color[1].item} 100%)`,
  };
});

// 历史遗留：原代码导入的 getRandCode 接口在 api/user.js 中从未存在（Vue2 下点按即 TypeError
// 的坏功能，webpack 仅告警不阻断；Vite 下缺失具名导入会阻断模块加载），此处移除导入并降级为提示
function getRandCode() {
  return Promise.reject(new Error('getRandCode api not defined'));
}

function getCode() {
  getRandCode()
    .then((res) => {
      let code = res.data.code;
      config.value.bar.code = code;
      config.value.qrc.code = code;
    })
    .catch((err) => {
      return util.Tips(err);
    });
}
function tapQrCode() {
  // this.isCode = true;
  // this.codeIndex = 0;
  // this.$nextTick(function() {
  // 	let code = this.diyInfo.bar_code;
  // 	this.config.bar.code = code;
  // 	this.config.qrc.code = code;
  // })
  uni.navigateTo({
    url: "/pages/users/user_member_code/index",
  });
}
function closeCode() {
  isCode.value = false;
  isextension = false;
}
function tapCode(index) {
  codeIndex.value = index;
  if (index == 1) {
    getCode();
  } else {
    let code = diyInfo.value.bar_code;
    config.value.bar.code = code;
    config.value.qrc.code = code;
  }
}
function handleMenu(type) {
  let url = "";
  switch (type) {
    case 0:
      url = "/pages/users/user_coupon/index";
      break;
    case 1:
      url = "/pages/users/user_integral/index";
      break;
    case 2:
      url = "/pages/users/user_money/index";
      break;
    case 4:
      url = "/pages/users/user_goods_collection/index";
      break;
    case 5:
      url = "/pages/users/visit_list/index";
      break;
  }
  if (url) {
    uni.navigateTo({
      url: url,
    });
  }
}
function goLogin() {
  if (!isLogin.value) {
    emit("changeLogin");
  }
}
function getDiyUserInfo() {
  getUserInfo()
    .then((res) => {
      diyInfo.value = res.data;
    })
    .catch((err) => {
      util.Tips({
        title: err,
      });
    });
}
function getLevelInfo() {
  getlevelInfoApi().then((res) => {
    const { level_info, level_list } = res.data;
    const currentLevel = level_list.find(
      (item) => item.grade == level_info.grade,
    );
    if (currentLevel) {
      currentLevelColor.value = currentLevel.color;
    }
  });
}
function colorToRgba(str, n) {
  // 十六进制颜色值的正则表达式
  const reg = /^#([0-9a-fA-f]{3}|[0-9a-fA-f]{6})$/;
  let sColor = str.toLowerCase();
  // 十六进制颜色转换为RGB格式
  if (sColor && reg.test(sColor)) {
    if (sColor.length === 4) {
      let sColorNew = "#";
      for (let i = 1; i < 4; i += 1) {
        sColorNew += sColor.slice(i, i + 1).concat(sColor.slice(i, i + 1));
      }
      sColor = sColorNew;
    }
    // 处理六位颜色值
    const sColorChange = [];
    for (let k = 1; k < 7; k += 2) {
      sColorChange.push(parseInt(`0x${sColor.slice(k, k + 2)}`, 16));
    }
    return `rgba(${sColorChange.join(",")}, ${n})`;
  }
  return sColor;
}
</script>

<style lang="scss">
.userInfor {
  // flex: 1;
  // padding: 28rpx 20rpx;
  background: #ffffff;

  .mark {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 50;
  }

  .codePopup .icon-guanbi2 {
    margin-top: 75rpx !important;
  }

  &.pageOn {
    border-radius: 12rpx;
  }

  .right {
    position: relative;

    &::before {
      content: "";
      position: absolute;
      top: 0;
      right: 20rpx;
      left: 20rpx;
      border-top: 1rpx solid #eeeeee;
    }

    .item {
      flex: 1;
      min-width: 0;
      padding: 34rpx 0;
      margin: 0;
      font-size: 26rpx;
      line-height: 36rpx;
      color: #999999;

      .num {
        font-family: SemiBold;
        font-weight: 500;
        font-size: 28rpx;
        margin-left: 8rpx;
        color: #333333;
      }
    }

    .iconfont {
      font-size: 40rpx;
      margin-bottom: 8rpx;
    }
  }

  .left {
    padding: 32rpx 20rpx;

    .pictrue {
      width: 90rpx;
      height: 90rpx;
      border: 1px solid #eeeeee;
      border-radius: 50%;
      margin-right: 20rpx;

      image {
        width: 100%;
        height: 100%;
        border-radius: 50%;
      }

      .king {
        width: 36rpx;
        height: 36rpx;
        position: absolute;
        top: -18rpx;
        right: -8rpx;
      }
    }

    .text {
      flex: 1;
      font-weight: 400;
      //color: #333333;
      font-size: 28rpx;

      .name {
        margin-bottom: 8rpx;

        .nameCon {
          max-width: 190rpx;
          font-weight: bold;
        }

        .lable {
          height: 32rpx;
          padding: 0 8rpx;
          border-radius: 16rpx;
          margin-left: 8rpx;
          font-weight: 500;
          font-size: 20rpx;
          line-height: 32rpx;
          color: #b06a00;
          background-color: #ffe8ca;
          .icon {
            width: 24rpx;
            height: 24rpx;
            margin-right: 4rpx;
          }
        }
      }

      .progress {
        overflow: hidden;
        background-color: #eeeeee;
        width: 120rpx;
        height: 14rpx;
        border-radius: 7rpx;
        position: relative;
        margin-right: 8rpx;

        .bg-reds {
          width: 0;
          height: 14rpx;
          border-radius: 7rpx;
          transition: width 0.6s ease;
          background: linear-gradient(
            90deg,
            var(--view-theme) 0%,
            var(--view-gradient) 100%
          );
        }
      }

      .percent {
        font-size: 20rpx;
        line-height: 20rpx;
        color: #666666;
      }

      .phone {
        font-weight: 400;
        //color: #666666;
        font-size: 20rpx;
        margin-top: 6rpx;

        .icon-shouji2 {
          margin-right: 4rpx;
          font-size: 20rpx;
        }
      }
    }

    .right::before {
      display: none;
    }

    .item {
      width: 108rpx;
      padding: 0;
      overflow: hidden;
      text-align: center;
      font-size: 22rpx;
      color: #999999;

      .num {
        margin: 0 0 12rpx;
        font-size: 32rpx;
      }

      .iconfont {
        font-weight: 500;
        color: #333333;
        font-size: 40rpx;
        margin: 0 0 12rpx;
      }
    }
  }

  .bottom {
    position: relative;

    &::before {
      content: "";
      position: absolute;
      top: 0;
      right: 20rpx;
      left: 20rpx;
      border-top: 1px solid #eeeeee;
    }

    .item {
      flex: 1;
      padding: 42rpx 0 40rpx;
      text-align: center;
      font-weight: 500;
      font-size: 22rpx;
      line-height: 30rpx;
      color: #999999;
    }

    .num {
      margin: 0 0 12rpx;
      font-family: SemiBold;
      font-weight: 500;
      font-size: 32rpx;
      line-height: 32rpx;
      color: #333333;
    }
  }
}
</style>
