<template>
  <view :style="colorStyle" class="wrapper">
    <!-- <view class="bag">
      <img :src="`../static/login-bg_1.jpg`" alt="" srcset="" />
    </view> -->
    <view class="system-height" :style="{ height: statusBarHeight }"></view>
    <!-- #ifdef MP -->
    <view class="title-bar" style="height: 43px">
      <view class="icon" @click="back" v-if="!isHome">
        <image src="../static/left.png"></image>
      </view>
      <view class="icon" @click="home" v-else>
        <image src="../static/home.png"></image>
      </view>
      {{ $t(`商城登录`) }}
    </view>
    <!-- #endif -->
    <view class="merchant-msg">
      <image class="logo" :src="configData.wap_login_logo" mode="aspectFit" />
      <view class="name">
        {{ configData.site_name }}
      </view>
    </view>
    <view class="wechat_login">
      <view class="btn-wrapper">
        <!-- #ifdef H5 -->
        <button hover-class="none" @click="wechatLogin" class="bg-theme btn1">
          {{ $t(`微信登录`) }}
        </button>
        <!-- #endif -->
        <!-- #ifdef MP -->
        <template v-if="configData.wechat_auth_switch">
          <button
            class="bg-theme btn1"
            v-if="bindPhone"
            open-type="getPhoneNumber"
            @getphonenumber="getphonenumber"
          >
            {{ $t(`授权登录`) }}
          </button>
          <button
            class="bg-theme btn1"
            v-else-if="!bindPhone"
            @click="getAuthLogin"
          >
            {{ $t(`授权登录`) }}
          </button>
        </template>
        <button
          v-if="configData.phone_auth_switch"
          hover-class="none"
          @click="phoneLogin"
          class="btn2"
        >
          {{ $t(`手机号登录`) }}
        </button>
        <view class="cancel-login" @click="onReject">{{ $t(`取消登录`) }}</view>
        <!-- #endif -->
      </view>
    </view>
    <view class="protocol" v-if="!canGetPrivacySetting">
      <!-- 用 @change 同步勾选，避免微信 H5 下 @click 连点/冒泡导致 protocol 被来回拨回 -->
      <checkbox-group @change="ChangeIsDefault">
        <checkbox
          value="1"
          :class="inAnimation ? 'trembling' : ''"
          @animationend="inAnimation = false"
          :checked="protocol"
          backgroundColor="#ffffff"
          activeBackgroundColor="var(--view-theme, #E93323)"
          activeBorderColor="var(--view-theme, #E93323)"
          iconColor="#ffffff"
        />
        <text @click.stop="toggleProtocol">{{ $t(`已阅读并同意`) }}</text>
        <text class="main-color" @click.stop="privacy(4)">{{
          $t(`《用户协议》`)
        }}</text>
        {{ $t(`与`)
        }}<text class="main-color" @click.stop="privacy(3)">{{
          $t(`《隐私协议》`)
        }}</text>
      </checkbox-group>
    </view>
    <block v-if="isUp">
      <mobileLogin
        :isUp="isUp"
        :canClose="canClose"
        @close="maskClose"
        :authKey="authKey"
        @wechatPhone="wechatPhone"
      ></mobileLogin>
    </block>
    <block v-if="isPhoneBox">
      <routinePhone
        :logoUrl="logoUrl"
        :isPhoneBox="isPhoneBox"
        @loginSuccess="bindPhoneClose"
        :authKey="authKey"
      >
      </routinePhone>
    </block>
    <block>
      <editUserModal
        :isShow="isShow"
        @closeEdit="closeEdit"
        @editSuccess="editSuccess"
      >
      </editUserModal>
    </block>
    <!-- #ifdef MP -->
    <privacyAgreementPopup
      v-if="canGetPrivacySetting"
      @onReject="onReject"
      @onAgree="onAgree"
    >
    </privacyAgreementPopup>
    <!-- #endif -->
  </view>
</template>

<script setup>
const app = typeof getApp === 'function' ? getApp() : null;
let statusBarHeight = uni.getWindowInfo().statusBarHeight + "px";
import { ref } from "vue";
import { onLoad } from "@dcloudio/uni-app";
import mobileLogin from "../components/login_mobile/index.vue";
import routinePhone from "../components/login_mobile/routine_phone.vue";
import editUserModal from "@/components/editUserModal/index.vue";
import privacyAgreementPopup from "@/components/privacyAgreementPopup/index.vue";
import {
  getLogo,
  silenceAuth,
  routineBindingPhone,
  wechatAuthV2,
  authType,
  authLogin,
  wechatAuthLogin as wechatAuthLoginApi,
} from "@/api/public";
import { LOGO_URL, EXPIRES_TIME, USER_INFO, STATE_R_KEY } from "@/config/cache";
import { getUserInfo as getUserInfoApi } from "@/api/user.js";
import Routine from "@/libs/routine";
import wechat from "@/libs/wechat";
import { useColorTheme } from "@/composables/useColorTheme";
import Auth from "@/libs/wechat.js";
import { HTTP_REQUEST_URL } from "@/config/app";
import { isWeixin } from "@/utils";
import Cache from "@/utils/cache";
import { useAppStore } from "@/store/modules/app";
import util from "@/utils/util.js";
import i18n from "@/utils/lang.js";

const { colorStyle } = useColorTheme();
const appStore = useAppStore();
const globalData = app?.globalData || {};

// ---------- data ----------
const imgHost = HTTP_REQUEST_URL;
const isUp = ref(false);
const canClose = ref(true);
const phone = ref("");
const statusBarHeightVal = statusBarHeight;
const isHome = ref(false);
const isPhoneBox = ref(false);
const protocol = ref(false);
const isShow = ref(false);
const isLogin = ref(false);
const logoUrl = ref("");
const code = ref("");
const authKey = ref("");
const options = ref("");
const userInfo = ref({});
const codeNum = ref(0);
const canUseGetUserProfile = ref(false);
const canGetPrivacySetting = ref(false);
const inAnimation = ref(false);
const colorStatus = uni.getStorageSync("color_status");
const mp_is_new = Boolean(Cache.get("MP_VERSION_ISNEW"));
const configData = Cache.get("BASIC_CONFIG") || {
  wap_login_logo: '',
  site_name: '',
  wechat_auth_switch: false,
  phone_auth_switch: false,
};
const bindPhone = ref(false);

// ---------- 生命周期 ----------
onLoad((options) => {
  if (uni.getUserProfile) {
    canUseGetUserProfile.value = true;
  }
  // #ifdef MP
  if (wx.getPrivacySetting) {
    canGetPrivacySetting.value = true;
  }
  // #endif
  // #ifdef MP
  userLogin();
  // #endif
  // #ifdef H5
  const { code, state } = options;
  if (code) {
    let spread = Cache.get("spread") || "";
    let agent_id = Cache.get("agent_id") || "";
    let backUrl = state ? decodeURIComponent(state) : "";
    wechatAuthLogin(
      {
        code,
        spread,
        agent_id,
      },
      backUrl
    );
  }
  // #endif
  let pages = getCurrentPages();
  let prePage = pages[pages.length - 2];
  if (prePage && prePage.route == "pages/order_addcart/order_addcart") {
    isHome.value = true;
  } else {
    isHome.value = false;
  }
});

// ---------- methods ----------
function wechatAuthLogin(d, back_url) {
  uni.showLoading({
    title: i18n.t(`正在登录中`),
  });
  wechatAuthLoginApi(d)
    .then((res) => {
      uni.hideLoading();
      if (res.data.bindPhone) {
        authKey.value = res.data.key;
        uni.navigateTo({
          url: `/pages/users/binding_phone/index?authKey=${authKey.value}&backUrl=${back_url}`,
        });
      } else {
        let time = res.data.expires_time - Cache.time();
        appStore.LOGIN({
          token: res.data.token,
          time: time,
        });
        getUserInfo(0, back_url);
      }
    })
    .catch((err) => {
      uni.hideLoading();
      uni.showToast({
        title: err,
        icon: "none",
        duration: 2000,
      });
    });
}
function onAgree() {
  protocol.value = true;
}
// 小程序 22.11.8日删除getUserProfile 接口获取用户昵称头像
function userLogin() {
  // if (!protocol.value) {
  // 	uni.showToast({
  // 		title: i18n.t('请先阅读并同意协议'),
  // 		icon: 'none',
  // 		duration: 2000
  // 	});
  // 	return
  // }
  Routine.getCode()
    .then((code) => {
      // uni.showLoading({
      // 	title: i18n.t(`正在登录中`)
      // });
      authType({
        code,
        spread_spid: globalData.spid || 0,
        spread_code: globalData.code || 0,
      })
        .then((res) => {
          uni.hideLoading();
          authKey.value = res.data.key;
          bindPhone.value = res.data.bindPhone;
          // uni.navigateTo({
          // 	url: `/pages/users/binding_phone/index?authKey=${res.data.key}`
          // })
        })
        .catch((err) => {
          uni.hideLoading();
          uni.showToast({
            title: err,
            icon: "none",
            duration: 2000,
          });
        });
    })
    .catch((err) => {
      console.log(err);
    });
}
function getAuthLogin() {
  if (!authKey.value) return;
  if (!protocol.value) {
    uni.showToast({
      title: i18n.t("请先阅读并同意协议"),
      icon: "none",
      duration: 2000,
    });
    return;
  }
  uni.showLoading({
    title: i18n.t(`正在登录中`),
  });
  authLogin({
    key: authKey.value,
  })
    .then((res) => {
      let time = res.data.expires_time - Cache.time();
      appStore.LOGIN({
        token: res.data.token,
        time: time,
      });
      getUserInfo(res.data.bindName);
    })
    .catch((err) => {
      uni.hideLoading();
      uni.showToast({
        title: err,
        icon: "none",
        duration: 2000,
      });
    });
}
function ChangeIsDefault(e) {
  const values = (e && e.detail && e.detail.value) || [];
  protocol.value = values.length > 0;
}
function toggleProtocol() {
  protocol.value = !protocol.value;
}
function editSuccess() {
  isShow.value = false;
}
function phoneLogin() {
  uni.navigateTo({
    url: `/pages/users/binding_phone/index?authKey=${authKey.value}&pageType=0`,
  });
}

function closeEdit() {
  isShow.value = false;
  util.Tips(
    {
      title: i18n.t(`登录成功`),
      icon: "success",
    },
    {
      tab: 3,
    }
  );
}
function onReject() {
  uni.navigateBack();
}
// #ifdef MP
function back() {
  if (isLogin.value) {
    appStore.LOGIN({
      token: "",
      time: 0,
    });
  }
  uni.navigateBack();
}
// #endif
// #ifndef MP
function back() {
  uni.navigateBack({
    delta: 1,
  });
}
// #endif
function home() {
  uni.switchTab({
    url: "/pages/index/index",
  });
}
// 弹窗关闭
function maskClose(new_user) {
  isUp.value = false;
  // #ifdef MP
  if (new_user) {
    isShow.value = true;
  }
  // #endif
}
function bindPhoneClose(data) {
  isPhoneBox.value = false;
  if (data.isStatus) {
    // #ifdef MP
    getUserInfo(data.new_user);
    // #endif
    // #ifndef MP
    util.Tips(
      {
        title: i18n.t(`登录成功`),
        icon: "success",
      },
      {
        tab: 3,
      }
    );
    // #endif
  }
}
// #ifdef MP
// 小程序获取手机号码
function getphonenumber(e) {
  if (!protocol.value) {
    uni.showToast({
      title: i18n.t("请先阅读并同意协议"),
      icon: "none",
      duration: 2000,
    });
    return;
  }
  uni.showLoading({
    title: i18n.t(`正在登录中`),
  });
  Routine.getCode()
    .then((code) => {
      getUserPhoneNumber(e.detail.encryptedData, e.detail.iv, code);
    })
    .catch((error) => {
      uni.$emit("closePage", false);
      uni.hideLoading();
    });
}
// 小程序获取手机号码回调
function getUserPhoneNumber(encryptedData, iv, code) {
  routineBindingPhone({
    encryptedData: encryptedData,
    iv: iv,
    code: code,
    spread_spid: globalData.spid || 0,
    spread_code: globalData.code || 0,
    agent_id: globalData.agent_id || 0,
    key: authKey.value,
  })
    .then((res) => {
      let time = res.data.expires_time - Cache.time();
      appStore.LOGIN({
        token: res.data.token,
        time: time,
      });
      // userInfo.value = res.data.userInfo;
      // appStore.SETUID(res.data.userInfo.uid);
      // appStore.UPDATE_USERINFO(res.data.userInfo);
      Cache.clear("snsapiKey");
      getUserInfo(res.data.bindName);
      // util.Tips({
      // 	title: i18n.t(`登录成功`),
      // 	icon: 'success'
      // }, {
      // 	tab: 3
      // });
    })
    .catch((res) => {
      uni.hideLoading();
    });
}
// #endif
/**
 * 获取个人用户信息
 */
function getUserInfo(new_user, back_url) {
  getUserInfoApi()
    .then((res) => {
      uni.hideLoading();
      userInfo.value = res.data;
      appStore.SETUID(res.data.uid);
      appStore.UPDATE_USERINFO(res.data);
      if (new_user) {
        isShow.value = true;
      } else {
        // #ifdef MP
        util.Tips(
          {
            title: i18n.t(`登录成功`),
            icon: "success",
          },
          {
            tab: 3,
          }
        );
        // #endif
        // #ifndef MP
        util.Tips(
          {
            title: i18n.t(`登录成功`),
            icon: "success",
          },
          {
            tab: 4,
            url: back_url || "/pages/user/index",
          }
        );
        // #endif
      }
    })
    .catch((err) => {
      uni.hideLoading();
      uni.showToast({
        title: err.msg,
        icon: "none",
        duration: 2000,
      });
    });
}
function privacy(type) {
  uni.navigateTo({
    url: "/pages/users/privacy/index?type=" + type,
  });
}
// #ifdef H5
// 获取url后面的参数
function getQueryString(name) {
  var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
  var reg_rewrite = new RegExp("(^|/)" + name + "/([^/]*)(/|$)", "i");
  var r = window.location.search.substr(1).match(reg);
  var q = window.location.pathname.substr(1).match(reg_rewrite);
  if (r != null) {
    return unescape(r[2]);
  } else if (q != null) {
    return unescape(q[2]);
  } else {
    return null;
  }
}
// 公众号登录
function wechatLogin() {
  if (!protocol.value) {
    uni.showToast({
      title: i18n.t("请先阅读并同意协议"),
      icon: "none",
      duration: 2000,
    });
    return;
  }
  if (!code.value || options.value.scope !== "snsapi_base") {
    Auth.oAuth("snsapi_userinfo", location.href);
  } else {
    if (authKey.value) {
      // isUp.value = true;
      uni.navigateTo({
        url: `/pages/users/binding_phone/index?authKey=${authKey.value}`,
      });
    }
  }
}

// 输入手机号后的回调
function wechatPhone() {
  Cache.clear("snsapiKey");
  if (options.value.back_url) {
    let url = uni.getStorageSync("snRouter");
    url = url.indexOf("/pages/index/index") != -1 ? "/" : url;
    if (url.indexOf("/pages/users/wechat_login/index") !== -1) {
      url = "/";
    }
    if (!url) {
      url = "/pages/index/index";
    }
    isUp.value = false;
    uni.showToast({
      title: i18n.t(`登录成功`),
      icon: "none",
    });
    setTimeout((res) => {
      location.href = url;
    }, 800);
  } else {
    isUp.value = false;
    uni.showToast({
      title: i18n.t(`登录成功`),
      icon: "none",
    });
    setTimeout((res) => {
      location.href = "/pages/index/index";
    }, 800);
  }
}
// #endif
</script>

<style lang="scss">
page {
  background: #fff;
}

.wrapper {
  position: relative;
  height: 100vh;

  .bag {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    opacity: 0.8;
    z-index: -1;
    /* #ifdef H5 */
    z-index: 0;

    /* #endif */
    img {
      width: 100%;
      height: 838rpx;
    }
  }

  .merchant-msg {
    padding-top: 252rpx;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    z-index: 2;
    /* #ifdef H5 */
    position: relative;

    /* #endif */
    /* 用 image + class，避免小程序把 img 编译成 image 后尺寸样式丢失 */
    .logo {
      width: 152rpx;
      height: 152rpx;
      border-radius: 50%;
    }

    .name {
      font-size: 40rpx;
      font-weight: 500;
      color: #333333;
      line-height: 56rpx;
      margin-top: 32rpx;
    }
  }
}

.wechat_login {
  margin-top: 96rpx;

  .img image {
    width: 100%;
  }

  .btn-wrapper {
    padding: 0 66rpx;

    button {
      width: 100%;
      height: 86rpx;
      line-height: 86rpx;
      margin-bottom: 40rpx;
      border-radius: 120rpx;
      font-size: 30rpx;

      &.btn1 {
        color: #fff;
      }

      &.btn2 {
        color: #666666;
        border: 1px solid #e4e4e4;
        margin-bottom: 30rpx;
      }
    }
    .cancel-login {
      color: #999;
      font-size: 28rpx;
      text-align: center;
    }
  }
}

.title-bar {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 34rpx;
  font-weight: 500;
  color: #333333;
  line-height: 48rpx;
}

.icon {
  position: absolute;
  left: 30rpx;
  top: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 80rpx;
  height: 80rpx;

  image {
    width: 50rpx;
    height: 50rpx;
  }
}

.protocol {
  position: fixed;
  bottom: 52rpx;
  left: 0;
  width: 100%;
  margin: 0 auto;
  color: #999999;
  font-size: 24rpx;
  line-height: 22rpx;
  text-align: center;
  bottom: calc(52rpx + constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
  bottom: calc(52rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/

  .main-color {
    color: var(--view-theme);
  }

  .trembling {
    animation: shake 0.6s;
  }

:deep(uni-checkbox .uni-checkbox-input){
    width: 28rpx;
    height: 28rpx;
  }

:deep(uni-checkbox .uni-checkbox-input.uni-checkbox-input-checked::before){
    font-size: 24rpx;
  }

:deep(uni-checkbox .uni-checkbox-wrapper){
    margin-bottom: 1px;
  }

  /*checkbox 选项框大小  */
:deep(checkbox .wx-checkbox-input){
    width: 28rpx;
    height: 28rpx;
  }

  /*checkbox选中后样式  */
:deep(checkbox .wx-checkbox-input.wx-checkbox-input-checked){
    background: white;
  }

  /*checkbox选中后图标样式  */
:deep(checkbox .wx-checkbox-input.wx-checkbox-input-checked::before){
    width: 28rpx;
    height: 28rpx;
    line-height: 28rpx;
    text-align: center;
    font-size: 22rpx;
    background: transparent;
    transform: translate(-50%, -50%) scale(1);
    -webkit-transform: translate(-50%, -50%) scale(1);
  }
}
</style>
