<template>
  <view class="chat-box" :style="colorStyle">
    <!-- #ifdef MP -->
    <!-- 		<view class="head-box">
			<view class="system-head" :style="{ height: sysHead }"></view>
			<view class="title-hd">
				<view class="iconfont icon-fanhui" @click="goBack"></view>
				<view>{{ titleName }}</view>
			</view>
		</view> -->
    <!-- #endif -->
    <view class="chat-scroll-box">
      <scroll-view
        scroll-y="true"
        style="height: 100%"
        :scroll-top="scrollTop"
        @scrolltoupper="scrollToTop"
      >
        <Loading :loaded="status" :loading="loading"></Loading>
        <view id="box" class="chat" ref="chat">
          <view
            v-for="(item, index) in records"
            :key="index"
            :id="`msg-${item.id}`"
          >
            <view class="day-box" v-if="item.show">{{ item._add_time }}</view>
            <view class="chat-item" :class="{ 'right-box': item.uid == myUid }">
              <image class="avatar" :src="item.avatar" mode=""></image>
              <!-- 消息 -->
              <view
                class="msg-box"
                v-if="item.msn_type <= 2"
                v-html="item.msn"
              ></view>
              <!-- 图片 -->
              <view class="img-box" v-if="item.msn_type == 3">
                <image
                  :src="item.msn"
                  mode="widthFix"
                  @tap="previewImage(item.msn)"
                ></image>
              </view>
              <!-- 商品卡片：点击跳转商品详情 -->
              <view class="product-box" v-if="item.msn_type == 5 && item.productInfo" @click="goGoods(item.productInfo.id)">
                <image :src="item.productInfo.image" mode="widthFix"></image>
                <view class="info">
                  <view class="name line1">{{ item.productInfo.store_name }}</view>
                  <view class="price"><text>￥</text>{{ item.productInfo.price }}</view>
                </view>
              </view>
              <!-- 订单卡片：点击跳转订单详情（中台按 order_no） -->
              <view class="order-box" v-if="item.msn_type == 6 && item.orderInfo" @click="goOrder(item.orderInfo.order_no)">
                <view class="title">订单</view>
                <view class="info">
                  <image
                    class="order-img"
                    :src="item.orderInfo.image"
                    mode="aspectFill"
                  ></image>
                  <view class="product-info">
                    <view class="name line1">{{ item.orderInfo.title }}</view>
                    <view class="price">￥{{ item.orderInfo.pay_price }}</view>
                    <view class="status-txt">{{
                      item.orderInfo.pay_status_name }}{{ item.orderInfo.status_name ? ' · ' + item.orderInfo.status_name : ''
                    }}</view>
                  </view>
                </view>
                <view class="order-no">订单号：{{ item.orderInfo.order_no }}</view>
              </view>
            </view>
          </view>
        </view>
      </scroll-view>
    </view>
    <view class="footer-box">
      <view class="words" @click="uploadImg"
        ><text class="iconfont icon-tupian"></text
      ></view>
      <view class="input-box">
        <input
          type="text"
          :placeholder="$t(`请输入内容`)"
          v-model="con"
          confirm-type="send"
          @confirm="sendText"
        />
        <text
          class="iconfont icon-fasong"
          @click="sendText"
          :class="{ isSend: isSend }"
        ></text>
      </view>
      <view class="emoji" @click="isSwiper = !isSwiper"
        ><span class="iconfont icon-biaoqing"></span
      ></view>
    </view>
    <!-- 表情 -->
    <view class="banner slider-banner" v-if="isSwiper">
      <!-- 表情类型 tab：默认/QQ，与管理端客服表情保持同步 -->
      <view class="emoji-tabs">
        <text
          class="tab-item"
          :class="{ on: emojiTab === 'default' }"
          @click="emojiTab = 'default'"
          >{{ $t(`默认表情`) }}</text
        >
        <text
          class="tab-item"
          :class="{ on: emojiTab === 'qq' }"
          @click="emojiTab = 'qq'"
          >{{ $t(`QQ表情`) }}</text
        >
      </view>
      <!-- 默认表情：雪碧图表情，8 列网格 + 上下滚动，与 QQ 表情列表同高同布局 -->
      <scroll-view scroll-y class="default-emoji-list" v-if="emojiTab === 'default'">
        <view class="emoji-grid">
          <view
            class="emoji-cell"
            v-for="emoji in emojiList"
            :key="emoji"
            @click="addEmoji(emoji)"
          >
            <i
              class="em"
              :class="emoji"
              :style="'background-image:url(' + httpUrl + ')'"
            ></i>
          </view>
        </view>
      </scroll-view>
      <!-- QQ 表情：Unicode 原生字符，点击直接追加到输入框，随文本发送 -->
      <scroll-view scroll-y class="qq-emoji-list" v-else>
        <text
          class="qq-item"
          v-for="(item, index) in qqEmojis"
          :key="index"
          @click="addQqEmoji(item)"
          >{{ item.char }}</text
        >
      </scroll-view>
    </view>
    <canvas
      canvas-id="canvas"
      v-if="canvasStatus"
      :style="{
        width: canvasWidth + 'px',
        height: canvasHeight + 'px',
        position: 'absolute',
        left: '-100000px',
        top: '-100000px',
      }"
    ></canvas>
  </view>
</template>

<script setup>
const app = getApp();
import { ref, computed, nextTick, onMounted, onBeforeUnmount, getCurrentInstance } from "vue";
import { onLoad } from "@dcloudio/uni-app";
import { getChatRecord } from "@/api/user";
let statusBarHeight = uni.getWindowInfo().statusBarHeight + "px";
import { socketInstance } from "@/libs/new_chat";
import emojiList, { qqEmojis } from "@/utils/emoji";
import Loading from "@/components/Loading";
import { useColorTheme } from "@/composables/useColorTheme";
import { HTTP_REQUEST_URL } from "@/config/app.js";
import { useAppStore } from "@/store/modules/app";
import { toLogin } from "@/libs/login";
import util from "@/utils/util.js";
import i18n from "@/utils/lang.js";
// #ifdef H5
import wechat from "@/libs/wechat";
// #endif

defineOptions({ name: "adminChat_index" });

const { colorStyle } = useColorTheme();
const appStore = useAppStore();
const instance = getCurrentInstance();

// ---------- data ----------
const status = ref(false);
const loading = ref(false);
const sysHead = statusBarHeight;
const isTool = ref(false);
const isSwiper = ref(false);
const emojiTab = ref("default"); // 表情面板当前 tab：default=默认表情 qq=QQ表情（与管理端同步）
const isWords = ref(false);
const wordsList = ref([]);
const con = ref("");
const toUid = ref(0);
const limit = ref(15);
const upperId = ref(0);
const chatList = ref([]);
const kefuInfo = ref({});
const scrollTop = ref(0);
const active = ref(true);
const isScroll = ref(true);
const oldHeight = ref(0);
const myUid = ref(0);
const page = ref(1);
const uidTo = ref(0);
const titleName = ref("");
const chatStatus = ref(false);
const userType = ref(0);
const canvasWidth = ref("");
const canvasHeight = ref("");
const canvasStatus = ref(false);
const httpUrl = ref("");
// 待自动发送的订单/商品卡片：{ msn: 订单号或商品id, type: 6|5 }，发送一次后置空
const pendingCard = ref(null);

// ---------- computed ----------
const isSend = computed(() => {
  if (con.value.length == 0) {
    return false;
  } else {
    return true;
  }
});
const records = computed(() => {
  return chatList.value.map((item, index) => {
    if (index) {
      if (item.add_time - chatList.value[index - 1].add_time >= 300) {
        item.show = true;
      } else {
        item.show = false;
      }
    } else {
      item.show = true;
    }
    return item;
  });
});

onLoad((options) => {
  uni.showLoading({
    title: i18n.t(`客服连接中`),
  });
  myUid.value = appStore.uid;
  toUid.value = options.to_uid;
  userType.value = options.type;
  // 入口携带的订单/商品上下文（订单详情/商品详情等页「联系客服」跳入）：WS 就绪后自动发送对应卡片，
  // 让客服直接看到用户咨询的订单/商品；发送一次后清空，断线重连不会重发
  if (options.orderId) {
    pendingCard.value = { msn: options.orderId, type: 6 };
  } else if (options.productId) {
    pendingCard.value = { msn: options.productId, type: 5 };
  }
});

// ---------- uni.$on 具名回调（onMounted 注册 / onBeforeUnmount 成对解绑） ----------
// socketOpen：连接建立（含断线重连成功）后触发
function onSocketOpen() {
  // 登录
  socketInstance.send({
    data: appStore.token,
    //#ifdef MP || APP-PLUS
    form_type: 2,
    //#endif
    //#ifdef H5
    form_type: wechat.isWeixin() ? 1 : 3,
    //#endif
    type: "login",
  });
  // 重连场景下整体刷新消息，避免 getChatList 前置拼接导致历史消息重复；同时重置分页游标
  chatList.value = [];
  uidTo.value = 0;
  nextTick((e) => {
    getChatList();
  });
}
// 监听客服转接
function onToTransfer(data) {
  toUid.value = data.toUid;
  socketInstance.send({
    data: {
      id: toUid.value,
    },
    type: "to_chat",
  });
  chatList.value.forEach((el) => {
    if (el.uid != myUid.value) {
      el.avatar = data.avatar;
    }
  });
}
// 超时了（服务端下发 timeout，如 token 校验超时）：重建连接，消息由 onSocketOpen 统一刷新
function onTimeout() {
  uni.showLoading({
    title: i18n.t(`重连中`),
    mask: true,
  });
  initSocket();
}
// 断线重连达到上限仍失败：弹窗引导用户手动重试
function onReconnectFailed() {
  uni.hideLoading();
  uni.showModal({
    title: i18n.t(`提示`),
    content: i18n.t(`连接已断开，是否重新连接？`),
    success: (res) => {
      if (res.confirm) {
        uni.showLoading({
          title: i18n.t(`客服连接中`),
          mask: true,
        });
        initSocket();
      }
    },
  });
}
// 服务端鉴权失败（token 失效）：自动重连已终止，登出并引导重新登录
function onAuthExpired(e) {
  uni.hideLoading();
  uni.showModal({
    title: i18n.t(`提示`),
    content: i18n.t((e && e.msg) || `登录已失效，请重新登录`),
    showCancel: false,
    success: () => {
      toLogin();
    },
  });
}
// 链接成功
function onSuccess() {
  socketInstance.init();
}
// 消息接收
function onReplyChat(data) {
  if (data.msn_type == 1) {
    data.msn = replace_em(data.msn);
  }
  data._add_time = data._add_time.substring(0, data._add_time.length - 3);
  chatList.value.push(data);
  nextTick(() => {
    height();
  });
}
function onSocketError() {
  util.Tips({
    title: i18n.t(`连接失败`),
  });
}
function onErrTip(e) {
  util.Tips({
    title: e.msg,
  });
}
function onOnline(data) {
  if (data.online == 0) {
    uni.showModal({
      title: i18n.t(`提示`),
      content: i18n.t(`客服已下线，是否需要反馈？`),
      success: function (res) {
        if (res.confirm) {
          uni.redirectTo({
            url: "/pages/columnGoods/HotNewGoods/feedback",
          });
        } else if (res.cancel) {
        }
      },
    });
  }
}

function initSocket() {
  let form_type;
  //#ifdef MP || APP-PLUS
  form_type = 2;
  //#endif
  //#ifdef H5
  form_type = wechat.isWeixin() ? 1 : 3;
  //#endif
  socketInstance.onStart(appStore.token, form_type);
}

// ---------- 原 onReady 逻辑（监听注册） ----------
onMounted(() => {
  httpUrl.value = `${HTTP_REQUEST_URL}/statics/images/look.png`;
  // #ifdef H5
  let dom = document.querySelector(".chat-box");
  dom.style.height = window.innerHeight + "px";
  // #endif
  initSocket();
  // 初始化

  // 连接建立（含断线重连成功）：发送登录并刷新消息
  uni.$on("socketOpen", onSocketOpen);
  // 监听客服转接
  uni.$on("to_transfer", onToTransfer);
  // 超时了
  uni.$on("timeout", onTimeout);
  // 链接成功
  uni.$on("success", onSuccess);
  // 消息接收
  uni.$on(["reply", "chat"], onReplyChat);
  uni.$on("socket_error", onSocketError);
  // 断线重连达上限仍失败
  uni.$on("reconnect_failed", onReconnectFailed);
  // 服务端鉴权失败（token 失效）
  uni.$on("ws_auth_expired", onAuthExpired);
  uni.$on("err_tip", onErrTip);
  uni.$on("online", onOnline);
  nextTick(() => {
    height();
  });
});

// ---------- 原 onUnload 逻辑（具名解绑，替代原 uni.$off() 全量解绑） ----------
onBeforeUnmount(() => {
  socketInstance.onClose();
  uni.$off("socketOpen", onSocketOpen);
  uni.$off("to_transfer", onToTransfer);
  uni.$off("timeout", onTimeout);
  uni.$off("success", onSuccess);
  uni.$off(["reply", "chat"], onReplyChat);
  uni.$off("socket_error", onSocketError);
  uni.$off("reconnect_failed", onReconnectFailed);
  uni.$off("ws_auth_expired", onAuthExpired);
  uni.$off("err_tip", onErrTip);
  uni.$off("online", onOnline);
});

// ---------- methods ----------
function previewImage(n) {
  uni.previewImage({
    urls: [n],
  });
}
// 返回
function goBack() {
  uni.navigateBack();
}
// 表情点击
function addEmoji(item) {
  let val = `[${item}]`;
  con.value += val;
}
// QQ 表情点击（Unicode 原生字符直接追加，随文本发送，全端原生渲染）
function addQqEmoji(item) {
  con.value += item.char;
}
// 聊天表情转换
function replace_em(str) {
  str = str.replace(
    /\[([^\[\]]+)\]/g,
    "<span class='em $1' style='background-image:url(" +
      httpUrl.value +
      ")'></span>"
  );
  return str;
}
// 获取聊天列表
function getChatList() {
  getChatRecord({
    limit: limit.value,
    uidTo: uidTo.value,
    toUid: toUid.value,
  })
    .then((res) => {
      let selector = "";
      if (res.data.serviceList.length) {
        if (uidTo.value == 0) {
          selector = `#msg-${
            res.data.serviceList[res.data.serviceList.length - 1].id
          }`;
        } else {
          selector = `#msg-${
            chatList.value.length ? chatList.value[0].id : 0
          }`;
        }
      }
      let arr = [];
      var sH = 0;
      uni.hideLoading();
      uni.setNavigationBarTitle({
        title: res.data.nickname,
      });
      titleName.value = res.data.nickname;
      toUid.value = res.data.uid;
      res.data.serviceList.forEach((el) => {
        el._add_time = el._add_time.substring(0, el._add_time.length - 3);
        if (el.msn_type == 1 || el.msn_type == 2) {
          el.msn = replace_em(el.msn);
        }
      });
      loading.value = false;
      chatList.value = [...res.data.serviceList, ...chatList.value];

      nextTick(() => {
        if (chatList.value.length) {
          setPageScrollTo(selector);
          isScroll.value = res.data.serviceList.length >= limit.value;
        }
      });
      socketInstance.send({
        data: {
          id: toUid.value,
        },
        type: "to_chat",
      });
      // 入口携带的订单/商品上下文：会话就绪后自动发送对应卡片（仅首次，发送后清空）
      if (pendingCard.value) {
        sendMsg(pendingCard.value.msn, pendingCard.value.type);
        pendingCard.value = null;
      }
    })
    .catch((error) => {
      uni.hideLoading();
      util.Tips({
        title: error,
      });
      loading.value = false;
      isScroll.value = false;
      uni.redirectTo({
        url: "/pages/columnGoods/HotNewGoods/feedback",
      });
    });
}

// 设置页面滚动位置
function setPageScrollTo(selector) {
  let view = uni.createSelectorQuery().in(instance?.proxy).select(selector);
  view
    .boundingClientRect((res) => {
      scrollTop.value = res ? parseFloat(res.top) - 60 : 0;
    })
    .exec();
}

// 商品卡片跳转商品详情
function goGoods(id) {
	if (!id) return;
	uni.navigateTo({ url: `/pages/goods_details/index?id=${id}` });
}
// 订单卡片跳转订单详情（订单中台按 order_no 查询）
function goOrder(orderNo) {
	if (!orderNo) return;
	uni.navigateTo({ url: `/pages/center/order/detail/index?order_no=${orderNo}` });
}
// 发送消息
function sendText() {
  if (!isSend.value) {
    return util.Tips({
      title: i18n.t(`请输入内容`),
    });
  }
  sendMsg(con.value, 1);
  con.value = "";
}
// ws发送
function sendMsg(msn, type) {
  socketInstance.send({
    data: {
      msn,
      type,
      to_uid: toUid.value,
    },
    //#ifdef MP || APP-PLUS
    form_type: 2,
    //#endif
    //#ifdef H5
    form_type: wechat.isWeixin() ? 1 : 3,
    //#endif
    type: "chat",
  });
}
function uploadImg() {
  canvasStatus.value = true;
  util.uploadImageChange(
    "upload/image",
    (res) => {
      if (res.status == 200) {
        sendMsg(res.data.url, 3);
      }
    },
    (res) => {
      canvasStatus.value = false;
    },
    (res) => {
      canvasWidth.value = res.w;
      canvasHeight.value = res.h;
    }
  );
}
// 滚动到底部（原函数内局部变量 scrollTop 与 ref 同名，改名 scrollTopVal 避免遮蔽）
function height() {
  var scrollTopVal = 0;
  let info = uni.createSelectorQuery().select(".chat");
  setTimeout((res) => {
    info
      .boundingClientRect(function (data) {
        //data - 各种参数
        scrollTopVal = data.height;
        if (active.value) {
          scrollTop.value = parseInt(scrollTopVal) + 500;
        } else {
          scrollTop.value = parseInt(scrollTopVal) + 100;
        }
      })
      .exec();
  }, 200);
}
// 滚动到顶部
function scrollToTop() {
  if (isScroll.value) {
    loading.value = true;
    uidTo.value = chatList.value.length ? chatList.value[0].id : 0;
    isScroll.value = false;
    setTimeout((res) => {
      getChatList();
    }, 800);
  }
}
</script>
<style>
/* #ifdef MP || APP-PLUS || H5 */
page,
uni-page-body,
html,
body {
  height: 100%;
}

/* #endif */
</style>
<style lang="scss">
.chat-box {
  display: flex;
  flex-direction: column;
  height: 100%;
  background: #f0f1f2;
  /* #ifdef H5 */
  height: 100vh;

  /* #endif */
  .head-box {
    /* #ifdef H5 */
    height: 86rpx;
    /* #endif */
    background: linear-gradient(85deg, $kf-star 0%, $kf-end 100%);

    .title-hd {
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      height: 43px;
      padding: 0 30rpx;
      color: #fff;

      .icon-fanhui {
        position: absolute;
        left: 30rpx;
        top: 50%;
        transform: translateY(-50%);
      }

      .icon-gengduo2 {
        /* #ifdef MP */
        position: absolute;
        right: 210rpx;
        top: 50%;
        transform: translateY(-50%);
        /* #endif */
      }
    }
  }

  .scroll-box {
    flex: 1;
  }

  .footer-box {
    display: flex;
    align-items: center;
    color: rgba(0, 0, 0, 0.8);
    background: #f7f7f7;
    /* #ifdef APP-PLUS */
    padding: 0 30rpx;
    height: 70rpx;
    height: (70rpx + constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
    height: calc(70rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
    /* #endif */

    /* #ifndef APP-PLUS */
    padding: 0 30rpx 15rpx 30rpx;
    height: 115rpx;

    /* #endif */
    .words .icon-tupian {
      font-size: 50rpx;
    }

    .input-box {
      display: flex;
      align-items: center;
      flex: 1;
      height: 64rpx;
      padding-right: 5rpx;
      margin-left: 18rpx;
      background-color: #fff;
      border-radius: 32rpx;

      input {
        flex: 1;
        padding-left: 20rpx;
        height: 100%;
        font-size: 28rpx;
        font-weight: normal;
      }

      .icon-fasong {
        font-size: 50rpx;
        color: #ccc;
        font-weight: normal;
      }

      .isSend {
        color: $kf-theme;
      }
    }

    .emoji .icon-biaoqing {
      margin-left: 18rpx;
      font-size: 50rpx;
    }

    .more .icon-gengduozhankai {
      margin-left: 18rpx;
      font-size: 50rpx;
    }
  }
}

.tool-wrapper {
  display: flex;
  justify-content: space-between;
  padding: 45rpx 60rpx;
  background: #fff;
  font-size: 24rpx;

  .tool-item {
    text-align: center;

    image {
      width: 104rpx;
      height: 104rpx;
    }
  }
}

.slider-banner {
  background: #fff;
  padding-bottom: 0rpx;
  padding-bottom: calc(constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
  padding-bottom: calc(env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
}

/* 表情类型 tab：默认/QQ（与管理端客服表情同步） */
.emoji-tabs {
  display: flex;
  align-items: center;
  padding: 16rpx 30rpx 0;

  .tab-item {
    padding: 6rpx 24rpx;
    font-size: 24rpx;
    color: #666;
    background: #f5f6f7;
    border-radius: 8rpx;

    &.on {
      color: #fff;
      background: $kf-theme;
    }
  }

  .tab-item + .tab-item {
    margin-left: 16rpx;
  }
}

/* 表情面板两个列表统一高度（300rpx），均支持上下滚动查看全部 */
/* 默认表情：8 列网格，单元格内居中一个正方形 .em（雪碧图 background-size:4100% 要求等宽等高，否则错位） */
.default-emoji-list {
  height: 300rpx;

  .emoji-grid {
    display: flex;
    flex-wrap: wrap;
  }

  .emoji-cell {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 12.5%;
    height: 75rpx;
  }

  /* 覆盖全局 .em 的 50rpx 尺寸与外边距，保证网格对齐 */
  .em {
    width: 56rpx;
    height: 56rpx;
    margin: 0;
  }
}

/* QQ 表情列表：一行 8 个 */
.qq-emoji-list {
  height: 300rpx;

  .qq-item {
    display: inline-block;
    width: 12.5%;
    height: 75rpx;
    font-size: 40rpx;
    line-height: 75rpx;
    text-align: center;
  }
}

.words-mask {
  z-index: 50;
  position: fixed;
  left: 0;
  top: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);

  .content {
    position: absolute;
    left: 0;
    right: 0;
    top: 114rpx;
    bottom: 0;
    display: flex;
    flex-direction: column;
    padding: 0 30rpx;
    background: #fff;
    border-radius: 6rpx 6rpx 0px 0px;

    .title-box {
      position: relative;
      height: 125rpx;
      line-height: 125rpx;
      text-align: center;
      font-size: 32rpx;

      .icon-cha1 {
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
      }
    }

    .scroll-box {
      flex: 1;
      overflow: hidden;

      .msg-item {
        padding: 25rpx 0;
        border-bottom: 1px solid #eceff8;
      }
    }
  }
}

.chat-scroll-box {
  flex: 1;
  padding: 30rpx 30rpx 0;
  overflow: hidden;

  .chat-item {
    display: flex;
    margin-bottom: 36rpx;
    -webkit-user-select: auto;

    .avatar {
      width: 80rpx;
      height: 80rpx;
      border-radius: 50%;
    }

    .msg-box {
      display: flex;
      align-items: center;
      max-width: 452rpx;
      margin-left: 22rpx;
      padding: 10rpx 24rpx;
      background: #fff;
      border-radius: 14rpx;
      word-break: break-all;
      -webkit-user-select: auto;
    }

    .img-box {
      width: 270rpx;
      margin-left: 22rpx;

      image {
        width: 270rpx;
      }
    }

    .product-box {
      width: 452rpx;
      margin-left: 22rpx;
      background-color: #fff;
      border-radius: 14rpx;
      overflow: hidden;

      image {
        width: 452rpx;
      }
      .info {
        padding: 16rpx 26rpx;

        .price {
          font-size: 36rpx;
          color: var(--view-priceColor);

          text {
            font-size: 28rpx;
          }
        }
      }
    }

    .order-box {
      width: 452rpx;
      margin-left: 22rpx;
      background-color: #fff;
      border-radius: 14rpx;
      overflow: hidden;

      .title {
        padding: 15rpx 20rpx;
        font-size: 26rpx;
        color: #282828;
        border-bottom: 1px solid #eceff8;
      }

      .info {
        display: flex;
        padding: 20rpx;

        /* 商品图：始终渲染保证布局稳定，无图/加载失败时由底色占位 */
        .order-img {
          flex: none;
          width: 124rpx;
          height: 124rpx;
          background: #f5f6f7;
          border-radius: 6rpx;
        }

        .product-info {
          flex: 1;
          /* 允许 flex 收缩：否则 min-width:auto 使长商品名撑破卡片，line1 省略号失效 */
          min-width: 0;
          display: flex;
          flex-direction: column;
          justify-content: space-between;
          margin-left: 16rpx;

          .name {
            font-size: 26rpx;
          }

          .price {
            font-size: 30rpx;
            color: var(--view-priceColor);
          }

          .status-txt {
            font-size: 24rpx;
            color: #999;
          }
        }
      }

      .order-no {
        padding: 0 20rpx 15rpx;
        font-size: 22rpx;
        color: #999;
        word-break: break-all;
      }
    }

    .line1 {
      overflow: hidden;
      white-space: nowrap;
      text-overflow: ellipsis;
    }

    &.right-box {
      flex-direction: row-reverse;

      .msg-box {
        margin-left: 0;
        margin-right: 22rpx;
        background-color: #9cec60;
      }

      .img-box {
        margin-left: 0;
        margin-right: 22rpx;
      }

      .product-box {
        margin-left: 0;
        margin-right: 22rpx;
      }

      .order-box {
        margin-left: 0;
        margin-right: 22rpx;
      }
    }

    .em {
      margin: 0;
    }
  }
}

.broadcast-details_box {
  display: flex;
  background: #fff;
  border-radius: 6px;
  padding: 24rpx;
}

.broadcast_details_img {
  width: 140rpx;
  height: 140rpx;
  border-radius: 8px;
  overflow: hidden;
  position: relative;
}

.broadcast_details_img .goods-img {
  width: 100%;
  height: 100%;
}

.broadcast_details_picBox {
  width: 75%;
  margin-left: 24rpx;
}

.broadcast_details_tit {
  font-size: 28rpx;
  color: #333333;
  height: 85rpx;
  font-weight: 800;
  line-height: 40rpx;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  text-align: left !important;
}

.broadcast_details_pic {
  font-size: 36rpx;
  color: var(--view-priceColor);
  text-align: left;
}

.broadcast_details_pic_num {
  text-decoration: line-through;
  font-size: 28rpx;
  color: rgba(0, 0, 0, 0.5);
  margin-left: 0.1rem;
}

.broadcast_details_btn {
  width: 130rpx;
  height: 50rpx;
  background: var(--view-theme);
  opacity: 1;
  border-radius: 125rpx;
  color: #fff;
  font-size: 24rpx;
  text-align: center;
  line-height: 50rpx;
}

.broadcast-details_num {
  width: 100%;
  height: 80rpx;
  line-height: 80rpx;
  color: #000000;
  font-size: 26rpx;
  display: flex;
  justify-content: space-between;
  background: #fff;
  border-bottom: 1px dashed rgba(0, 0, 0, 0.2);
  padding: 0 24rpx;
}

.day-box {
  font-size: 24rpx;
  color: #999;
  text-align: center;
  margin-bottom: 36rpx;
}
</style>
<style>
.em {
  display: inline-block;
  width: 50rpx;
  height: 50rpx;
  margin: 40rpx 0 0 50rpx;
}

.emoji-outer {
  position: absolute;
  right: 50rpx;
  bottom: 30rpx;
  width: 50rpx;
  height: 50rpx;
}
</style>
