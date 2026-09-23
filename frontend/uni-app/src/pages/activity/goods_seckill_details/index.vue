<template>
  <view :style="colorStyle">
    <!-- 头部 -->

    <!-- #ifndef APP-PLUS -->
    <view class="navbar" :style="{ height: navH + 'rpx', opacity: opacity }">
      <view class="navbarH" :style="'height:' + navH + 'rpx;'">
        <view class="navbarCon acea-row row-center-wrapper">
          <view class="header acea-row row-center-wrapper">
            <view
              class="item"
              :class="navActive === index ? 'on' : ''"
              v-for="(item, index) in navList"
              :key="index"
              @tap="tap(item, index)"
            >
              {{ item }}
            </view>
          </view>
        </view>
      </view>
    </view>
    <view
      id="home"
      class="home-nav acea-row row-center-wrapper"
      :class="[opacity > 0.5 ? 'on' : '']"
      :style="{ top: homeTop + 'rpx' }"
    >
      <view class="iconfont icon-fanhui2" @tap="returns"></view>
      <!-- #ifdef MP -->
      <view class="line"></view>
      <view class="iconfont icon-gengduo5" @click="moreNav"></view>
      <!-- #endif -->
    </view>
    <!-- #endif -->

    <!-- #ifdef H5 -->
    <view
      id="home"
      class="home-nav right acea-row row-center-wrapper"
      :class="[opacity > 0.5 ? 'on' : '']"
      :style="{ top: homeTop + 'rpx' }"
    >
      <!-- #ifdef  H5 -->
      <view class="iconfont icon-gengduo2" @click="moreNav"></view>
    </view>
    <!-- #endif -->
    <!-- #endif -->
    <homeList
      :navH="navH"
      :returnShow="returnShow"
      :currentPage="currentPage"
      :sysHeight="sysHeight"
    ></homeList>
    <view class="product-con">
      <scroll-view
        :scroll-top="scrollTop"
        scroll-y="true"
        scroll-with-animation="true"
        :style="'height:' + height + 'px;'"
        @scroll="scroll"
      >
        <view id="past0">
          <!-- #ifdef APP-PLUS || MP -->
          <view class="" :style="'width:100%;' + 'height:' + sysHeight"></view>
          <!-- #endif -->
          <productConSwiper
            :imgUrls="imgUrls"
            @showSwiperImg="showSwiperImg"
          ></productConSwiper>
          <view class="bg-color">
            <view class="nav acea-row row-between-wrapper">
              <view class="money">
                {{ $t(`￥`) }}
                <text class="num">{{ storeInfo.price || "" }}</text>
                <text
                  v-if="
                    attribute.productAttr.length &&
                    (attribute.productAttr.length
                      ? attribute.productAttr[0].attr_values.length
                      : 0) > 1
                  "
                  >{{ $t(`起`) }}</text
                >
                <text class="y-money"
                  >{{ $t(`￥`) }}{{ storeInfo.product_price || "" }}</text
                >
              </view>
              <view class="acea-row row-middle">
                <view class="timeItem" v-if="status == 1">
                  <view>{{ $t(`距秒杀结束仅剩`) }}</view>
                  <countDown
                    :is-day="false"
                    :tip-text="' '"
                    :day-text="' '"
                    :hour-text="' : '"
                    :minute-text="' : '"
                    :second-text="' '"
                    :datatime="datatime"
                    style="margin-top: 4rpx"
                  ></countDown>
                </view>
                <!-- <view class="timeState" wx:if="{{status == 0}}">已结束</view>
						  <view class="timeState" wx:if="{{status == 2}}">即将开始</view> -->
                <!-- <view class='iconfont icon-jiantou'></view> -->
              </view>
            </view>
          </view>
          <view class="wrapper">
            <view class="introduce acea-row row-between">
              <view class="infor">{{ storeInfo.title || "" }}</view>
              <!-- <button class='iconfont icon-fenxiang' open-type='share'></button> -->
              <view
                class="iconfont icon-fenxiang"
                @click="listenerActionSheet"
              ></view>
            </view>
            <view class="label acea-row row-middle">
              <!-- <view class='stock'>库存：{{storeInfo.stock}}{{storeInfo.unit_name}}</view> -->
              <view class="stock"
                >{{ $t(`累计销售`) }}：{{ storeInfo.total ? storeInfo.total : 0
                }}{{ $t(storeInfo.unit_name) || "" }}</view
              >
              <view
                >{{ $t(`限量剩余`) }}: {{ storeInfo.quota ? storeInfo.quota : 0
                }}{{ $t(storeInfo.unit_name) || "" }}</view
              >
            </view>
          </view>
          <view
            class="attribute acea-row row-between-wrapper"
            @tap="selecAttr"
            v-if="attribute.productAttr.length"
          >
            <!-- 	<view>{{attr}}：<text class='atterTxt'>{{attrValue}}</text></view>
						<view class='iconfont icon-jiantou'></view> -->
            <view class="flex">
              <view style="display: flex; align-items: center; width: 90%">
                <view class="attr-txt">{{ attr }}：</view>
                <view class="atterTxt line1" style="width: 82%">{{
                  attrValue
                }}</view>
              </view>
              <view class="iconfont icon-jiantou"></view>
            </view>
            <view
              class="acea-row row-between-wrapper"
              style="margin-top: 7px; padding-left: 70px"
              v-if="skuArr.length > 1"
            >
              <view class="flexs">
                <image
                  :src="item.image"
                  v-for="(item, index) in skuArr.slice(0, 4)"
                  :key="index"
                  class="attrImg"
                ></image>
              </view>
              <view class="switchTxt"
                >{{ $t(`共`) }}{{ skuArr.length }}{{ $t(`种规格可选`) }}</view
              >
            </view>
          </view>
        </view>
        <view class="userEvaluation" id="past1" v-if="replyCount">
          <view class="title acea-row row-between-wrapper">
            <view>{{ $t(`用户评价`) }}({{ replyCount }})</view>
            <navigator :render-link="false"
              class="praise"
              hover-class="none"
              :url="
                '/pages/goods/goods_comment_list/index?product_id=' +
                storeInfo.product_id
              "
            >
              <text class="font-color">{{ replyChance }}%</text>
              {{ $t(`好评率`) }}
              <text class="iconfont icon-jiantou"></text>
            </navigator>
          </view>
          <userEvaluation :reply="reply"></userEvaluation>
        </view>
        <view class="product-intro" id="past2">
          <view class="title">{{ $t(`产品介绍`) }}</view>
          <view class="conter">
            <!-- <view class="" v-html="storeInfo.description">
						</view> -->

            <!-- #ifndef APP-PLUS -->
            <mp-html :content="storeInfo.description" :tag-style="tagStyle" />
            <!-- #endif -->
            <!-- #ifdef APP-PLUS -->
            <view class="description" v-html="storeInfo.description"></view>
            <!-- #endif -->
          </view>
        </view>
        <view class="uni-p-b-98"></view>
      </scroll-view>
      <view
        class="footer acea-row row-between-wrapper"
        :class="{ eject: storeInfo.id }"
      >
        <navigator :render-link="false"
          hover-class="none"
          open-type="switchTab"
          class="item"
          url="/pages/index/index"
        >
          <view class="iconfont icon-shouye6"></view>
          <view class="p_center">{{ $t(`首页`) }}</view>
        </navigator>
        <view @tap="setCollect" class="item">
          <view
            class="iconfont icon-shoucang1"
            v-if="storeInfo.userCollect"
          ></view>
          <view class="iconfont icon-shoucang" v-else></view>
          <view class="p_center">{{ $t(`收藏`) }}</view>
        </view>
        <view
          class="bnt acea-row"
          v-if="
            status == 1 &&
            attribute.productSelect.quota > 0 &&
            attribute.productSelect.product_stock > 0
          "
        >
          <view
            class="joinCart bnts"
            @tap="openAlone"
            v-if="storeInfo.product_is_show"
            >{{ $t(`单独购买`) }}</view
          >
          <view
            class="buy bnts"
            :class="!storeInfo.product_is_show ? 'long-btn' : ''"
            @tap="goCat"
            >{{ $t(`立即购买`) }}</view
          >
        </view>
        <view
          class="bnt acea-row"
          v-if="
            (status == 1 && attribute.productSelect.quota <= 0) ||
            (status == 3 && attribute.productSelect.quota <= 0) ||
            (status == 1 && attribute.productSelect.product_stock <= 0) ||
            (status == 3 && attribute.productSelect.product_stock <= 0)
          "
        >
          <view
            class="joinCart bnts"
            @tap="openAlone"
            v-if="storeInfo.product_is_show"
            >{{ $t(`单独购买`) }}</view
          >
          <view
            class="buy bnts bg-color-hui"
            :class="!storeInfo.product_is_show ? 'long-btn' : ''"
            >{{ $t(`已售罄`) }}</view
          >
        </view>
        <view class="bnt acea-row" v-if="!dataShow && status == 1">
          <view
            class="joinCart bnts"
            @tap="openAlone"
            v-if="storeInfo.product_is_show"
            >{{ $t(`单独购买`) }}</view
          >
          <view
            class="buy bnts bg-color-hui"
            :class="!storeInfo.product_is_show ? 'long-btn' : ''"
            >{{ $t(`立即购买`) }}</view
          >
        </view>
        <view class="bnt acea-row" v-if="status == 2">
          <view
            class="joinCart bnts"
            @tap="openAlone"
            v-if="storeInfo.product_is_show"
            >{{ $t(`单独购买`) }}</view
          >
          <view
            class="buy bnts bg-color-hui"
            :class="!storeInfo.product_is_show ? 'long-btn' : ''"
            >{{ $t(`未开始`) }}</view
          >
        </view>
        <view class="bnt acea-row" v-if="status == 0">
          <view
            class="joinCart bnts"
            @tap="openAlone"
            v-if="storeInfo.product_is_show"
            >{{ $t(`单独购买`) }}</view
          >
          <view
            class="buy bnts bg-color-hui"
            :class="!storeInfo.product_is_show ? 'long-btn' : ''"
            >{{ $t(`已结束`) }}</view
          >
        </view>
      </view>
    </view>
    <cus-previewImg
      ref="cusPreviewRef"
      :list="skuArr"
      @changeSwitch="changeSwitch"
      @shareFriend="listenerActionSheet"
    />
    <product-window
      :attr="attribute"
      :limitNum="1"
      @myevent="onMyEvent"
      @ChangeAttr="ChangeAttr"
      :type="'seckill'"
      @ChangeCartNum="ChangeCartNum"
      @attrVal="attrVal"
      @iptCartNum="iptCartNum"
      @getImg="showImg"
    ></product-window>

    <!-- #ifdef MP -->
    <!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth"></authorize> -->
    <!-- #endif -->
    <!-- 分享按钮 -->
    <view
      class="generate-posters acea-row row-middle"
      :class="posters ? 'on' : ''"
    >
      <!-- #ifndef MP -->
      <button
        class="item"
        hover-class="none"
        v-if="weixinStatus === true"
        @click="H5ShareBox = true"
      >
        <!-- <button class="item" hover-class='none' v-if="weixinStatus === true" @click="setShareInfoStatus"> -->
        <view class="iconfont icon-weixin3"></view>
        <view class="">{{ $t(`发送给朋友`) }}</view>
      </button>
      <!-- #endif -->
      <!-- #ifdef MP -->
      <button
        class="item"
        open-type="share"
        hover-class="none"
        @click="goFriend"
      >
        <view class="iconfont icon-weixin3"></view>
        <view class="">{{ $t(`发送给朋友`) }}</view>
      </button>
      <!-- #endif -->
      <!-- #ifdef APP-PLUS -->
      <view class="item" @click="appShare('WXSceneSession')">
        <view class="iconfont icon-weixin3"></view>
        <view class="">{{ $t(`微信好友`) }}</view>
      </view>
      <view class="item" @click="appShare('WXSenceTimeline')">
        <view class="iconfont icon-pengyouquan"></view>
        <view class="">{{ $t(`微信朋友圈`) }}</view>
      </view>
      <!-- #endif -->
      <button class="item" hover-class="none" @tap="goPoster('seckill')">
        <view class="iconfont icon-haibao"></view>
        <view class="">{{ $t(`生成海报`) }}</view>
      </button>
    </view>
    <view class="mask" v-if="posters" @click="listenerActionClose"></view>

    <!-- 海报展示 -->
    <view class="poster-pop" v-if="posterImageStatus">
      <image
        src="/static/images/poster-close.png"
        class="close"
        @click="posterImageClose"
      ></image>
      <image class="poster-img" :src="posterImage"></image>
      <!-- #ifndef H5  -->
      <view class="save-poster" @click="savePosterPath">{{
        $t(`保存到手机`)
      }}</view>
      <!-- #endif -->
      <!-- #ifdef H5 -->
      <view class="keep">{{ $t(`长按图片可以保存到手机`) }}</view>
      <!-- #endif -->
    </view>
    <view class="mask1" v-if="posterImageStatus"></view>
    <canvas class="canvas" canvas-id="myCanvas" v-if="canvasStatus"></canvas>
    <kefuIcon
      :ids="storeInfo.product_id"
      :routineContact="routineContact"
    ></kefuIcon>
    <!-- 发送给朋友图片 -->
    <view class="share-box" v-if="H5ShareBox">
      <image
        :src="imgHost + '/statics/images/share-info.png'"
        @click="H5ShareBox = false"
      ></image>
    </view>
    <swiperPreview ref="cusSwiperImg" :list="storeInfo.images"></swiperPreview>
    <!-- #ifdef H5 || APP-PLUS -->
    <zb-code
      ref="qrcode"
      :show="codeShow"
      :cid="cid"
      :val="codeVal"
      :size="size"
      :unit="unit"
      :background="background"
      :foreground="foreground"
      :pdground="pdground"
      :icon="codeIcon"
      :iconSize="iconsize"
      :onval="onval"
      :loadMake="loadMake"
      @result="qrR"
    />
    <!-- #endif -->
  </view>
</template>

<script setup>
const app = getApp();
import { useAppStore } from "@/store/modules/app";
import { getSeckillDetail as getSeckillDetailApi, seckillCode } from "@/api/activity.js";
import { postCartAdd, collectAdd, collectDel } from "@/api/store.js";
import productConSwiper from "@/components/productConSwiper/index.vue";
import swiperPreview from "@/components/cusPreviewImg/swiperPreview.vue";
import productWindow from "@/components/productWindow/index.vue";
import userEvaluation from "@/components/userEvaluation/index.vue";
import kefuIcon from "@/components/kefuIcon";
// #ifdef MP
import authorize from "@/components/Authorize";
// #endif
import countDown from "@/components/countDown";
import { imageBase64 } from "@/api/public";
import { toLogin } from "@/libs/login.js";
import { getUserInfo } from "@/api/user.js";
// #ifdef APP-PLUS
import { TOKENNAME } from "@/config/app.js";
// #endif
import { useColorTheme } from "@/composables/useColorTheme";
import { useSharePoster } from "@/composables/useSharePoster";
// #ifdef H5
import wechat from "@/libs/wechat.js";
// #endif
import menuIcon from "@/components/menuIcon.vue";
import cusPreviewImg from "@/components/cusPreviewImg/index.vue";
import { HTTP_REQUEST_URL } from "@/config/app";
import homeList from "@/components/homeList";
import util from "@/utils/util.js";
import i18n from "@/utils/lang.js";
import { ref, watch, nextTick, getCurrentInstance } from "vue";
import { storeToRefs } from "pinia";
import { onLoad, onNavigationBarButtonTap, onShareAppMessage } from "@dcloudio/uni-app";

const { colorStyle } = useColorTheme();

const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

const instance = getCurrentInstance();
// 模板 ref（与组件 import 同名冲突，改名 cusPreviewRef / cusSwiperRef）
const cusPreviewRef = ref(null);
const cusSwiperImg = ref(null);

// ---------- data ----------
const imgHost = HTTP_REQUEST_URL;
const showMenuIcon = ref(false);
const dataShow = ref(0);
const id = ref(0);
const time = ref(0);
const countDownHour = ref("00");
const countDownMinute = ref("00");
const countDownSecond = ref("00");
const storeInfo = ref([]);
const imgUrls = ref([]);
const parameter = {
  navbar: "1",
  return: "1",
  title: i18n.t(`抢购详情页`),
  color: false,
};
const attribute = ref({
  cartAttr: false,
  productAttr: [],
  productSelect: {},
});
const productValue = ref([]);
const isOpen = ref(false);
const attr = ref(i18n.t(`请选择`));
const attrValue = ref("");
const attrTxt = ref(""); // 原 Vue2 data 未声明、直接挂 this 的字段
const status = ref(1);
const isAuto = ref(false);
const isShowAuth = ref(false);
const iShidden = ref(false);
const limitNum = ref(1); //限制本属性产品的个数；
const iSplus = ref(false);
const replyCount = ref(0); //总评论数量
const reply = ref([]); //评论列表
const replyChance = ref(0);
const navH = ref("");
const navList = ref([i18n.t(`商品`), i18n.t(`评价`), i18n.t(`详情`)]);
const opacity = ref(0);
const scrollY = ref(0);
const topArr = ref([]);
const toView = ref("");
const height = ref(0);
const heightArr = ref([]);
const lock = ref(false);
const scrollTop = ref(0);
const tagStyle = {
  img: "width:100%;display:block;",
  table: "width:100%",
  video: "width:100%",
};
const datatime = ref(0);
const navActive = ref(0);
const meunHeight = ref(0);
const backH = ref("");
const weixinStatus = ref(false);
const posterbackgd = ref("/static/images/posterbackgd.png");
const cart_num = ref("");
const homeTop = ref(20);
const returnShow = ref(true);
const H5ShareBox = ref(false); //公众号分享图片
const routineContact = ref(0);
const skuArr = ref([]);
const selectSku = ref({});
const currentPage = ref(false);
const time_id = ref(0);

// ---------- 海报/分销二维码（替代 mixins/sharePoster） ----------
const poster = useSharePoster(() => ({
  id: id.value, //秒杀产品 id
  timeId: time_id.value, //秒杀场次
  storeInfo: storeInfo.value,
  posterbackgd: posterbackgd.value,
}));
const {
  posters, // 分享弹窗开关（原 mixin 与页面 data 合并的同名字段）
  canvasStatus, //海报绘图标签
  PromotionCode, //二维码图片
  posterImage, //海报路径
  posterImageStatus,
  actionSheetHidden,
  storeImage, //海报产品图
  storeImageBase64,
  isDown,
  // 二维码组件参数（zb-code）
  codeShow,
  cid,
  codeVal,
  size,
  unit,
  background,
  foreground,
  pdground,
  codeIcon,
  iconsize,
  lv,
  onval,
  loadMake,
  sysHeight,
  qrR,
  getImageBase64,
  goPoster,
  downloadFilestoreImage,
} = poster;

// ---------- watch ----------
watch(isLogin, {
  handler: function (newV, oldV) {
    if (newV) {
      getSeckillDetail();
    }
  },
  deep: true,
});

// ---------- methods ----------
function moreNav() {
  currentPage.value = !currentPage.value;
}
// app分享
// #ifdef APP-PLUS
function appShare(scene) {
  let routes = getCurrentPages(); // 获取当前打开过的页面路由数组
  let curRoute = routes[routes.length - 1].$page.fullPath; // 获取当前页面路由，也就是最后一个打开的页面路由
  uni.share({
    provider: "weixin",
    scene: scene,
    type: 0,
    href: `${HTTP_REQUEST_URL}${curRoute}`,
    title: storeInfo.value.title,
    summary: storeInfo.value.info,
    imageUrl: storeInfo.value.small_image,
    success: function (res) {
      uni.showToast({
        title: i18n.t(`分享成功`),
        icon: "success",
      });
      posters.value = false;
    },
    fail: function (err) {
      uni.showToast({
        title: i18n.t(`分享失败`),
        icon: "none",
        duration: 2000,
      });
      posters.value = false;
    },
  });
}
// #endif
/**
 * 购物车手动填写
 *
 */
function iptCartNum(e) {
  attribute.value.productSelect["cart_num"] = e;
  cart_num.value = e;
}
// 后退
function returns() {
  // #ifdef H5
  return history.back();
  // #endif
  // #ifndef H5
  return uni.navigateBack({
    delta: 1,
  });
  // #endif
}
function onLoadFun(data) {
  if (isAuto.value) {
    isAuto.value = false;
    isShowAuth.value = false;
    getSeckillDetail();
  }
}
function getSeckillDetail() {
  getSeckillDetailApi(id.value, { time_id: time_id.value })
    .then((res) => {
      dataShow.value = 1;
      status.value = res.data.storeInfo.status;
      let title = res.data.storeInfo.title;
      storeInfo.value = res.data.storeInfo;
      datatime.value = Number(res.data.storeInfo.last_time);
      imgUrls.value = res.data.storeInfo.images;
      storeInfo.value.description = storeInfo.value.description.replace(
        /<img/gi,
        '<img style="max-width:100%;height:auto;float:left;display:block" '
      );
      storeInfo.value.description = storeInfo.value.description.replace(
        /<video/gi,
        '<video style="width:100%;height:300px;display:block" '
      );
      // this.attribute.productAttr = res.data.productAttr;
      attribute.value["productAttr"] = res.data.productAttr;
      productValue.value = res.data.productValue;
      attribute.value.productSelect.num = res.data.storeInfo.num;
      attribute.value.productSelect.once_num = res.data.storeInfo.once_num;
      replyCount.value = res.data.replyCount;
      reply.value = res.data.reply ? [res.data.reply] : [];
      replyChance.value = res.data.replyChance;
      routineContact.value = Number(res.data.routine_contact_type);
      uni.setNavigationBarTitle({
        title: title.substring(0, 7) + "...",
      });
      for (let key in res.data.productValue) {
        let obj = res.data.productValue[key];
        skuArr.value.push(obj);
      }
      selectSku.value = skuArr.value[0];
      var newNavList = [i18n.t(`商品`), i18n.t(`详情`)];
      if (res.data.replyCount) {
        newNavList.splice(1, 0, i18n.t(`评价`));
      }
      navList.value = newNavList;
      // #ifdef H5 || APP-PLUS
      // this.PromotionCode = res.data.storeInfo.code_base
      storeImage.value = storeInfo.value.image;
      getImageBase64();
      // #endif
      if (!storeInfo.value.wechat_code) {
        // #ifdef H5
        codeVal.value =
          window.location.origin +
          "/pages/activity/goods_seckill_details/index?id=" +
          id.value +
          "&spid=" +
          storeInfo.value.uid +
          "&time_id=" +
          time_id.value;
        // #endif
        // #ifdef APP-PLUS
        codeVal.value =
          HTTP_REQUEST_URL +
          "/pages/activity/goods_seckill_details/index?id=" +
          id.value +
          "&spid=" +
          storeInfo.value.uid +
          "&time_id=" +
          time_id.value;
        // #endif
      } else {
        PromotionCode.value = storeInfo.value.wechat_code;
      }
      // #ifdef APP-PLUS
      uni.downloadFile({
        url: setDomain(res.data.storeInfo.wechat_code),
        success: function (res) {
          PromotionCode.value = res.tempFilePath;
        },
        fail: function () {
          return util.Tips({
            title: i18n.t(`二维码获取失败`),
          });
        },
      });

      downloadFilestoreImage();
      // #endif
      // #ifdef H5
      setShare();
      // #endif
      // #ifndef H5 || APP-PLUS
      downloadFilestoreImage();
      // that.downloadFilePromotionCode();
      // #endif
      DefaultSelect();
      setTimeout(() => {
        infoScroll();
      }, 500);
    })
    .catch((err) => {
      util.Tips({
        title: err,
      });
    });
}
/**
 * 获取产品分销二维码（页面自身实现，覆盖 composable 同名方法：秒杀需带 time_id）
 * @param function successFn 下载完成回调
 *
 */
function downloadFilePromotionCode(successFn) {
  // #ifdef MP
  seckillCode(id.value, { time_id: time_id.value })
    .then((res) => {
      uni.downloadFile({
        url: setDomain(res.data.code),
        success: function (res) {
          isDown.value = false;
          PromotionCode.value = res.tempFilePath;
          if (typeof successFn == "function")
            successFn && successFn(res.tempFilePath);
        },
        fail: function () {
          isDown.value = false;
          PromotionCode.value = "";
        },
      });
    })
    .catch((err) => {
      isDown.value = false;
      PromotionCode.value = "";
    });
  // #endif
  // #ifdef APP-PLUS
  uni.downloadFile({
    url: setDomain(PromotionCode.value),
    success: function (res) {
      isDown.value = false;
      if (typeof successFn == "function")
        successFn && successFn(res.tempFilePath);
      else PromotionCode.value = res.tempFilePath;
    },
    fail: function () {
      isDown.value = false;
      PromotionCode.value = "";
    },
  });
  // #endif
}
function setShare() {
  wechat.isWeixin() &&
    wechat
      .wechatEvevt(
        [
          "updateAppMessageShareData",
          "updateTimelineShareData",
          "onMenuShareAppMessage",
          "onMenuShareTimeline",
        ],
        {
          desc: storeInfo.value.info,
          title: storeInfo.value.title,
          link: location.href,
          imgUrl: storeInfo.value.image,
        }
      )
      .then((res) => {})
      .catch((err) => {});
}
/**
 * 默认选中属性
 *
 */
function DefaultSelect() {
  let productAttr = attribute.value.productAttr;
  let value = [];
  for (var key in productValue.value) {
    if (productValue.value[key].quota > 0) {
      value = attribute.value.productAttr.length ? key.split(",") : [];
      break;
    }
  }
  for (let i = 0; i < productAttr.length; i++) {
    productAttr[i]["index"] = value[i];
  }
  //sort();排序函数:数字-英文-汉字；
  let productSelect = productValue.value[value.join(",")];
  if (productSelect && productAttr.length) {
    attribute.value.productSelect["store_name"] = storeInfo.value.title;
    attribute.value.productSelect["image"] = productSelect.image;
    attribute.value.productSelect["price"] = productSelect.price;
    attribute.value.productSelect["stock"] = productSelect.stock;
    attribute.value.productSelect["unique"] = productSelect.unique;
    attribute.value.productSelect["quota"] = productSelect.quota;
    attribute.value.productSelect["quota_show"] = productSelect.quota_show;
    attribute.value.productSelect["product_stock"] = productSelect.product_stock;
    attribute.value.productSelect["cart_num"] = 1;
    attrValue.value = value.join(",");
  } else if (!productSelect && productAttr.length) {
    attribute.value.productSelect["store_name"] = storeInfo.value.title;
    attribute.value.productSelect["image"] = storeInfo.value.image;
    attribute.value.productSelect["price"] = storeInfo.value.price;
    attribute.value.productSelect["quota"] = 0;
    attribute.value.productSelect["quota_show"] = 0;
    attribute.value.productSelect["product_stock"] = 0;
    attribute.value.productSelect["stock"] = 0;
    attribute.value.productSelect["unique"] = "";
    attribute.value.productSelect["cart_num"] = 0;
    attrValue.value = "";
    attrTxt.value = i18n.t(`请选择`);
  } else if (!productSelect && !productAttr.length) {
    attribute.value.productSelect["store_name"] = storeInfo.value.title;
    attribute.value.productSelect["image"] = storeInfo.value.image;
    attribute.value.productSelect["price"] = storeInfo.value.price;
    attribute.value.productSelect["stock"] = storeInfo.value.stock;
    attribute.value.productSelect["quota"] = storeInfo.value.quota;
    attribute.value.productSelect["product_stock"] = storeInfo.value.product_stock;
    attribute.value.productSelect["unique"] = storeInfo.value.unique || "";
    attribute.value.productSelect["cart_num"] = 1;
    attribute.value.productSelect["quota"] = productSelect.quota;
    attribute.value.productSelect["product_stock"] = productSelect.product_stock;
    attrValue.value = "";
    attrTxt.value = i18n.t(`请选择`);
  }
}
function selecAttr() {
  attribute.value.cartAttr = true;
}
function onMyEvent() {
  attribute.value["cartAttr"] = false;
  isOpen.value = false;
}
/**
 * 购物车数量加和数量减
 *
 */
function ChangeCartNum(changeValue) {
  //changeValue:是否 加|减
  //获取当前变动属性
  let productSelect = productValue.value[attrValue.value];
  if (cart_num.value) {
    productSelect.cart_num = cart_num.value;
    attribute.value.productSelect.cart_num = cart_num.value;
  }
  //如果没有属性,赋值给商品默认库存
  if (productSelect === undefined && !attribute.value.productAttr.length)
    productSelect = attribute.value.productSelect;
  //无属性值即库存为0；不存在加减；
  if (productSelect === undefined) return;
  let stock = productSelect.stock || 0;
  let quotaShow = productSelect.quota_show || 0;
  let quota = productSelect.quota || 0;
  let productStock = productSelect.product_stock || 0;
  let num = attribute.value.productSelect;
  let nums = storeInfo.value.num || 0;
  let onceNum = storeInfo.value.once_num || 0;
  //设置默认数据
  if (productSelect.cart_num == undefined) productSelect.cart_num = 1;
  if (changeValue) {
    num.cart_num++;
    let arrMin = [];
    arrMin.push(nums);
    arrMin.push(onceNum);
    arrMin.push(quota);
    arrMin.push(productStock);
    let minN = Math.min.apply(null, arrMin);
    if (num.cart_num >= minN) {
      attribute.value.productSelect["cart_num"] = minN ? minN : 1;
      cart_num.value = minN ? minN : 1;
    }
    // if(quotaShow >= productStock){
    // 	 if (num.cart_num > productStock) {
    // 	 	this.attribute.productSelect["cart_num"] = productStock;
    // 	 	this["cart_num"] = productStock;
    // 	 }
    // }else{
    // 	if (num.cart_num > quotaShow) {
    // 		this.attribute.productSelect["cart_num"] = quotaShow;
    // 		this["cart_num"] = quotaShow;
    // 	}
    // }
    cart_num.value = num.cart_num;
    attribute.value.productSelect["cart_num"] = num.cart_num;
  } else {
    num.cart_num--;
    if (num.cart_num < 1) {
      attribute.value.productSelect["cart_num"] = 1;
      cart_num.value = 1;
    }
    cart_num.value = num.cart_num;
    attribute.value.productSelect["cart_num"] = num.cart_num;
  }
}
function attrVal(val) {
  attribute.value.productAttr[val.indexw].index =
    attribute.value.productAttr[val.indexw].attr_values[val.indexn];
}
/**
 * 属性变动赋值
 *
 */
function ChangeAttr(res) {
  cart_num.value = 1;
  let productSelect = productValue.value[res];
  selectSku.value = productSelect;
  if (productSelect) {
    attribute.value.productSelect["image"] = productSelect.image;
    attribute.value.productSelect["price"] = productSelect.price;
    attribute.value.productSelect["stock"] = productSelect.stock;
    attribute.value.productSelect["unique"] = productSelect.unique;
    attribute.value.productSelect["cart_num"] = 1;
    attribute.value.productSelect["quota"] = productSelect.quota;
    attribute.value.productSelect["quota_show"] = productSelect.quota_show;
    attrValue.value = res;

    attrTxt.value = i18n.t(`已选择`);
  } else {
    attribute.value.productSelect["image"] = storeInfo.value.image;
    attribute.value.productSelect["price"] = storeInfo.value.price;
    attribute.value.productSelect["stock"] = 0;
    attribute.value.productSelect["unique"] = "";
    attribute.value.productSelect["cart_num"] = 0;
    attribute.value.productSelect["quota"] = 0;
    attribute.value.productSelect["quota_show"] = 0;
    attrValue.value = "";
    attrTxt.value = i18n.t(`已选择`);
  }
}
function scroll(e) {
  var scrollYTmp = e.detail.scrollTop;
  var opacityTmp = scrollYTmp / 200;
  opacityTmp = opacityTmp > 1 ? 1 : opacityTmp;
  opacity.value = opacityTmp;
  scrollY.value = scrollYTmp;
  currentPage.value = false;
  showMenuIcon.value = false;
  if (lock.value) {
    lock.value = false;
    return;
  }
  for (var i = 0; i < topArr.value.length; i++) {
    if (
      scrollYTmp <
      topArr.value[i] - app.globalData.navHeight / 2 + heightArr.value[i]
    ) {
      navActive.value = i;
      break;
    }
  }
}
function open(data) {
  showMenuIcon.value = data;
}
function tap(item, index) {
  // 局部变量与 id ref 同名遮蔽，改名 viewId
  var viewId = item.id;
  if (!replyCount.value && viewId == "past1") {
    viewId = "past2";
  }
  toView.value = viewId;
  navActive.value = index;
  lock.value = true;
  scrollTop.value =
    index > 0
      ? topArr.value[index] - app.globalData.navHeight / 2
      : topArr.value[index];
}
function infoScroll() {
  var newTopArr = [],
    newHeightArr = [];
  for (var i = 0; i < navList.value.length; i++) {
    //productList
    //获取元素所在位置
    var query = wx.createSelectorQuery().in(instance.proxy);
    var idView = "#past" + i;
    if (!replyCount.value && i == 1) {
      idView = "#past" + 2;
    }
    query.select(idView).boundingClientRect();
    query.exec(function (res) {
      var top = res[0].top;
      var height = res[0].height;
      newTopArr.push(top);
      newHeightArr.push(height);
      topArr.value = newTopArr;
      heightArr.value = newHeightArr;
    });
  }
}
/**
 * 收藏商品
 */
function setCollect() {
  if (storeInfo.value.userCollect) {
    collectDel([storeInfo.value.product_id]).then((res) => {
      storeInfo.value.userCollect = !storeInfo.value.userCollect;
    });
  } else {
    collectAdd(storeInfo.value.product_id).then((res) => {
      storeInfo.value.userCollect = !storeInfo.value.userCollect;
    });
  }
}
/*
 *  单独购买
 */
function openAlone() {
  uni.navigateTo({
    url: `/pages/goods_details/index?id=${storeInfo.value.product_id}`,
  });
}
/*
 *  下订单
 */
function goCat() {
  currentPage.value = false;
  var productSelect = productValue.value[attrValue.value];
  //打开属性
  if (isOpen.value) attribute.value.cartAttr = true;
  else attribute.value.cartAttr = !attribute.value.cartAttr;
  //只有关闭属性弹窗时进行加入购物车
  if (attribute.value.cartAttr === true && isOpen.value == false)
    return (isOpen.value = true);
  //如果有属性,没有选择,提示用户选择
  if (
    attribute.value.productAttr.length &&
    productSelect === undefined &&
    isOpen.value == true
  )
    return util.Tips({
      title: i18n.t(`请选择属性`),
    });
  postCartAdd({
    productId: storeInfo.value.product_id,
    secKillId: id.value,
    bargainId: 0,
    combinationId: 0,
    cartNum: cart_num.value,
    uniqueId: productSelect !== undefined ? productSelect.unique : "",
    new: 1,
  })
    .then((res) => {
      isOpen.value = false;
      uni.navigateTo({
        url:
          "/pages/center/order/create/index?new=1&cartId=" +
          res.data.cartId,
      });
    })
    .catch((err) => {
      return util.Tips({
        title: err,
      });
    });
}
/**
 * 分享打开
 *
 */
function listenerActionSheet() {
  if (isLogin.value === false) {
    toLogin();
  } else {
    // #ifdef H5
    if (wechat.isWeixin() === true) {
      weixinStatus.value = true;
    }
    // #endif
    posters.value = true;
  }
}
// 分享关闭
function listenerActionClose() {
  posters.value = false;
}
//隐藏海报
function posterImageClose() {
  posterImageStatus.value = false;
}
//替换安全域名（页面自身实现，覆盖 composable 同名方法）
function setDomain(url) {
  url = url ? url.toString() : "";
  //本地调试打开,生产请注销
  if (url.indexOf("https://") > -1) return url;
  else return url.replace("http://", "https://");
}
// 小程序关闭分享弹窗；
function goFriend() {
  posters.value = false;
}
/*
 *  保存到手机相册
 */
// #ifdef MP
function savePosterPath() {
  uni.getSetting({
    success(res) {
      if (!res.authSetting["scope.writePhotosAlbum"]) {
        uni.authorize({
          scope: "scope.writePhotosAlbum",
          success() {
            uni.saveImageToPhotosAlbum({
              filePath: posterImage.value,
              success: function (res) {
                posterImageClose();
                util.Tips({
                  title: i18n.t(`保存成功`),
                  icon: "success",
                });
              },
              fail: function (res) {
                util.Tips({
                  title: i18n.t(`保存失败`),
                });
              },
            });
          },
        });
      } else {
        uni.saveImageToPhotosAlbum({
          filePath: posterImage.value,
          success: function (res) {
            posterImageClose();
            util.Tips({
              title: i18n.t(`保存成功`),
              icon: "success",
            });
          },
          fail: function (res) {
            util.Tips({
              title: i18n.t(`保存失败`),
            });
          },
        });
      }
    },
  });
}
// #endif
//#ifdef APP-PLUS
function savePosterPath() {
  uni.saveImageToPhotosAlbum({
    filePath: posterImage.value,
    success: function (res) {
      posterImageClose();
      util.Tips({
        title: i18n.t(`保存成功`),
        icon: "success",
      });
    },
    fail: function (res) {
      util.Tips({
        title: i18n.t(`保存失败`),
      });
    },
  });
}
// #endif
function setShareInfoStatus() {
  let data = storeInfo.value;
  let href = location.href;
  if (wechat.isWeixin()) {
    posters.value = true;
    getUserInfo().then((res) => {
      href =
        href.indexOf("?") === -1
          ? href + "?spread=" + res.data.uid
          : href + "&spread=" + res.data.uid;

      let configAppMessage = {
        desc: data.store_info,
        title: data.store_name,
        link: href,
        imgUrl: data.image,
      };
      wechat.wechatEvevt(
        ["updateAppMessageShareData", "updateTimelineShareData"],
        configAppMessage
      );
    });
  }
}
//点击sku图片打开轮播图
function showImg(index) {
  cusPreviewRef.value.open(selectSku.value.suk);
}
//滑动轮播图选择商品
function changeSwitch(e) {
  let productSelect = skuArr.value[e];
  selectSku.value = productSelect;
  var skuList = productSelect.suk.split(",");
  attribute.value.productAttr[0]["index"] = skuList[0];
  if (skuList.length == 2) {
    attribute.value.productAttr[0]["index"] = skuList[0];
    attribute.value.productAttr[1]["index"] = skuList[1];
  } else if (skuList.length == 3) {
    attribute.value.productAttr[0]["index"] = skuList[0];
    attribute.value.productAttr[1]["index"] = skuList[1];
    attribute.value.productAttr[2]["index"] = skuList[2];
  } else if (skuList.length == 4) {
    attribute.value.productAttr[0]["index"] = skuList[0];
    attribute.value.productAttr[1]["index"] = skuList[1];
    attribute.value.productAttr[2]["index"] = skuList[2];
    attribute.value.productAttr[3]["index"] = skuList[3];
  }
  if (productSelect) {
    attribute.value.productSelect["image"] = productSelect.image;
    attribute.value.productSelect["price"] = productSelect.price;
    attribute.value.productSelect["stock"] = productSelect.stock;
    attribute.value.productSelect["unique"] = productSelect.id;
    attribute.value.productSelect["vipPrice"] = productSelect.vipPrice;
    attrTxt.value = i18n.t(`已选择`);
    attrValue.value = productSelect.suk;
  }
}
function showSwiperImg(index) {
  cusSwiperImg.value.open(index);
}

// ---------- 生命周期 ----------
onLoad((options) => {
  let statusBarHeight = "";
  var pageStack = getCurrentPages();
  if (options.id) {
    id.value = options.id;
    //记录推广人uid
    if (options.pid) app.globalData.spid = options.pid;
    if (options.time_id) time_id.value = options.time_id;
    // if (options.time) this.datatime = Number(options.time);
  }
  returnShow.value = pageStack.length === 1 ? false : true;

  //设置商品列表高度
  uni.getSystemInfo({
    success: function (res) {
      height.value = res.windowHeight;
      statusBarHeight = res.statusBarHeight;
      //res.windowHeight:获取整个窗口高度为px，*2为rpx；98为头部占据的高度；
    },
  });
  // #ifdef MP
  navH.value = app.globalData.navHeight;
  // #endif
  // #ifdef H5
  navH.value = 96;
  // #endif
  // #ifdef APP-PLUS
  navH.value = 30;
  // #endif
  // #ifdef MP
  let menuButtonInfo = uni.getMenuButtonBoundingClientRect();
  meunHeight.value = menuButtonInfo.height;
  backH.value = navH.value / 2 + meunHeight.value / 2;

  //扫码携带参数处理
  if (options.scene) {
    let value = util.getUrlParams(decodeURIComponent(options.scene));
    if (value.id) {
      id.value = value.id;
    } else {
      return util.Tips(
        {
          title: i18n.t(`缺少参数无法查看商品`),
        },
        {
          tab: 3,
          url: 1,
        }
      );
    }
    //记录推广人uid
    if (value.pid) app.globalData.spid = value.pid;
    if (value.time_id) time_id.value = value.time_id;
    // if (value.time) this.datatime = value.time
  }
  // #endif
  if (isLogin.value) {
    getSeckillDetail();
  } else {
    toLogin();
  }
  nextTick(() => {
    // #ifdef MP
    const menuButton = uni.getMenuButtonBoundingClientRect();
    const query = uni.createSelectorQuery().in(instance.proxy);
    query
      .select("#home")
      .boundingClientRect((data) => {
        homeTop.value = menuButton.top * 2 + menuButton.height - data.height;
      })
      .exec();
    // #endif
  });
});

onNavigationBarButtonTap((e) => {
  currentPage.value = !currentPage.value;
});

//#ifdef MP
onShareAppMessage(() => {
  return {
    title: storeInfo.value.title,
    path:
      "/pages/activity/goods_seckill_details/index?id=" +
      id.value +
      "&spid=" +
      appStore.uid +
      "&time_id=" +
      time_id.value,
    imageUrl: storeInfo.value.image,
  };
});
//#endif
</script>

<style lang="scss">
.generate-posters {
  width: 100%;
  height: 170rpx;
  background-color: #fff;
  position: fixed;
  left: 0;
  bottom: 0;
  z-index: 300;
  transform: translate3d(0, 100%, 0);
  transition: all 0.3s cubic-bezier(0.25, 0.5, 0.5, 0.9);
  border-top: 1rpx solid #eee;
}

.generate-posters.on {
  transform: translate3d(0, 0, 0);
}

.generate-posters .item {
  flex: 1;
  text-align: center;
  font-size: 30rpx;
}

.generate-posters .item .iconfont {
  font-size: 80rpx;
  color: #5eae72;
}

.generate-posters .item .iconfont.icon-haibao {
  color: #5391f1;
}

.navbar .header {
  height: 96rpx;
  font-size: 30rpx;
  color: #050505;
  background-color: #fff;
  /* #ifdef APP-PLUS */
  width: 100%;
  /* #endif */
}

.home {
  /* #ifdef H5 */
  top: 20rpx !important;
  /* #endif */
}

.navbar .header .item {
  position: relative;
  margin: 0 25rpx;
}

.navbar .header .item.on:before {
  position: absolute;
  width: 60rpx;
  height: 5rpx;
  background-repeat: no-repeat;
  content: "";
  // background-image: linear-gradient(to right, #ff3366 0%, #ff6533 100%);
  background-color: var(--view-theme);
  bottom: -10rpx;
  left: 50%;
  margin-left: -28rpx;
}

.navbar {
  position: fixed;
  background-color: #fff;
  top: 0;
  left: 0;
  z-index: 99;
  width: 100%;
}

.navbar .navbarH {
  position: relative;
}

.navbar .navbarH .navbarCon {
  position: absolute;
  bottom: 0;
  height: 100rpx;
  width: 100%;
  /* #ifndef APP-PLUS || H5 || MP-ALIPAY */
  // justify-content: flex-end;
  // padding-left: 48px;
  /* #endif */
}

.home {
  color: #333;
  position: fixed;
  /* #ifdef MP */
  width: 126rpx;
  left: 15rpx;
  /* #endif */
  /* #ifndef MP */
  width: 56rpx;
  left: 33rpx;
  /* #endif */
  height: 56rpx;
  z-index: 99;
  background: rgba(255, 255, 255, 0.3);
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 40rpx;
  font-size: 33rpx;

  &.right {
    right: 33rpx;
    left: unset;
  }

  &.on {
    background: unset;
    color: #333;
  }

  &.homeIndex {
    /* #ifdef MP */
    width: 98rpx;
    /* #endif */
    /* #ifndef MP */
    border-color: rgba(255, 255, 255, 0);
    /* #endif */
  }
}

.home .iconfont {
  width: 58rpx;
  text-align: center;
}

.home .line {
  width: 1rpx;
  height: 34rpx;
  background: #b3b3b3;
}

.home .icon-xiangzuo {
  font-size: 28rpx;
}

.product-con .nav {
  background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAu4AAABkCAMAAADud0VvAAAAeFBMVEUAAAD////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////GqOSsAAAAKHRSTlMAAQIDBAUGBwgJCgsMDQ4PEBESExQVFhcYGRobHB0eHyAhIiMkJSYnEt6j2gAACYZJREFUeNrtnel2qzoMhSUPzOnpef+HvD1NmonB90eShoTRBoMBaXWltV06wNfdLSEAYaMhdjtEAAQAfHtTAAraX04/KVB0BCLCbffeXyofdk/0Xev3AzGxzSPh7yJAUAiqvLfUfUrBDfqGF/XzkxPM3aEUw3YcsXOi51ov1hFgk7hHO6+O8vK+aYY9OxwVodwvCobGMKPhX0Uz67BF3DFJRBPl3fJ+OZyIYg3egY8r4ybkM/z9pK3hzpOYNVLeJe/FaX8lhPUiZ8ySjPeT+BLrm8PdSwJspbxN3vPDniy7gcArPpWMd7C+MdzD2ANspbxF3tPvA1l2s4w1uwv8aDLeU9dZdW4zuGMU8xvYrZTfZ9+JP30fidshAo+WqjPYyDrWqdNGcOdR9Py/1ibv99nHvrq9P3xfiNlhAs/Z2DKObbretLgJ3GXs9zYxv/L+IL74/keWfXjGWiPwdqx7M+vbwN2PRW/KX+UdFaRfe7LsoxiaQjDr1h2Rtf9ZrB13DEOuYWLe5P309UOgjhVZ7Sn88cjHSh1ma7jzMEA9E1OSd7X/jyz7qAKfCrRl3bGam9blqmvGXQYeNvPcIe/511dGhI6csaa85ODHs+7IWE9rv17cvVDcd5KBvF//+ybLbiNjLQQb2bo/WUe1VdzRD9gvv1omBgDg9B9ZdnsCL0a07v11fcW4M99HNDQxCg5fZNmtCrzEcaw7cqbbWbBC3IUvjU0MFP+oym5b4K9CDLfuPVivMTerw136HE1NDKT/qMo+QWS5N6wPHm8pL2ofrHXhjtJnpiYG4UiNMVMJ/EWa98Ejr2v+2h7ueHcxRoV2dfhHl59OF+mbwPclnzHeXLvvVvv14M49jgBmJgby/XdBDE4ZxVkKXeuOnA+8YHUtuAvJQafl8WU2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");
  background-repeat: no-repeat;
  background-size: 100% 100%;
  width: 100%;
  height: 100rpx;
  padding: 0 30rpx;
  box-sizing: border-box;
}

.product-con .nav :deep(.time .styleAll){
  padding: 0 6rpx;
  font-size: 22rpx;
  color: var(--view-theme);
  background-color: #fff;
  border-radius: 2rpx;
}

.product-con .nav .money {
  font-size: 28rpx;
  color: #fff;
}

.product-con .nav .money .num {
  font-size: 48rpx;
}

.product-con .nav .money .y-money {
  font-size: 26rpx;
  margin-left: 10rpx;
  text-decoration: line-through;
}

.product-con .nav .timeItem {
  font-size: 20rpx;
  color: #fff;
  text-align: center;
}

.product-con .nav .timeItem .timeCon {
  margin-top: 10rpx;
}

.product-con .nav .timeItem .timeCon .num {
  padding: 0 7rpx;
  font-size: 22rpx;
  color: #ff3d3d;
  background-color: #fff;
  border-radius: 2rpx;
}

.product-con .nav .timeState {
  font-size: 28RPX;
  color: #fff;
}

.product-con .nav .iconfont {
  color: #fff;
  font-size: 30rpx;
  margin-left: 20rpx;
}

.product-con .wrapper {
  padding: 32rpx;
  width: 100%;
  box-sizing: border-box;
}

.product-con .wrapper .introduce {
  margin: 0;
}

.product-con .wrapper .introduce .infor {
  width: 570rpx;
}

.product-con .wrapper .introduce .iconfont {
  font-size: 37rpx;
  color: #515151;
}

.product-con .wrapper .label {
  margin: 18rpx 0 0 0;
  font-size: 24rpx;
  color: #82848f;
  display: flex;
  justify-content: space-between;
}

.product-con .wrapper .label .stock {
  width: 255rpx;
  margin-right: 28rpx;
}

.product-con .footer {
  padding: 0 20rpx 0 30rpx;
  position: fixed;
  bottom: 0;
  width: 100%;
  box-sizing: border-box;
  background-color: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(10px);
  z-index: 277;
  border-top: 1rpx solid #f0f0f0;
  height: 100rpx;
  height: calc(100rpx + constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
  height: calc(100rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
  transform: translate3d(0, 100%, 0);
  transition: all 0.3s cubic-bezier(0.25, 0.5, 0.5, 0.9);
}

.product-con .footer .item {
  font-size: 18rpx;
  color: #666;
}

.product-con .footer .item .iconfont {
  text-align: center;
  font-size: 40rpx;
}

.product-con .footer .item .iconfont.icon-shoucang1 {
  color: var(--view-theme);
}

.product-con .footer .item .iconfont.icon-gouwuche1 {
  font-size: 40rpx;
  position: relative;
}

.product-con .footer .item .iconfont.icon-gouwuche1 .num {
  color: #fff;
  position: absolute;
  font-size: 18rpx;
  padding: 2rpx 8rpx 3rpx;
  border-radius: 200rpx;
  top: -10rpx;
  right: -10rpx;
}

.product-con .footer .bnt {
  width: 540rpx;
  height: 76rpx;
}
.product-con .footer .bnt .bnts.long-btn {
  width: 540rpx;
  border-radius: 50px;
}
.product-con .footer .bnt .bnts {
  width: 270rpx;
  text-align: center;
  line-height: 76rpx;
  color: #fff;
  font-size: 28rpx;
}

.product-con .footer .bnt .joinCart {
  border-radius: 50rpx 0 0 50rpx;
  // background-image: linear-gradient(to right, #fea10f 0%, #fa8013 100%);
  background-color: var(--view-bntColor);
}

.product-con .footer .bnt .buy {
  border-radius: 0 50rpx 50rpx 0;
  background-color: var(--view-theme);
  // background-image: linear-gradient(to right, #fa6514 0%, #e93323 100%);
}

.product-con .conter {
  display: block;
  padding-bottom: 100rpx;
}

.product-con .conter img {
  display: block;
}

.setCollectBox {
  font-size: 18rpx;
  color: #666;
}

.bg-color-hui {
  background: #bbbbbb !important;
}

.canvas {
  width: 750px;
  height: 1190px;
}

.poster-pop {
  width: 450rpx;
  height: 714rpx;
  position: fixed;
  left: 50%;
  transform: translateX(-50%);
  z-index: 300;
  top: 50%;
  margin-top: -377rpx;
}

.poster-pop image {
  width: 100%;
  height: 100%;
  display: block;
}

.poster-pop .close {
  width: 46rpx;
  height: 75rpx;
  position: fixed;
  right: 0;
  top: -73rpx;
  display: block;
}

.poster-pop .save-poster {
  background-color: #df2d0a;
  font-size: ：22rpx;
  color: #fff;
  text-align: center;
  height: 76rpx;
  line-height: 76rpx;
  width: 100%;
}

.poster-pop .keep {
  color: #fff;
  text-align: center;
  font-size: 25rpx;
  margin-top: 10rpx;
}

:deep(.mask){
  z-index: 99 !important;
}

.mask1 {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #000;
  opacity: 0.5;
  z-index: 288;
}

.home-nav {
  /* #ifdef H5 */
  top: 20rpx !important;
  /* #endif */
}

.home-nav {
  color: #333;
  position: fixed;
  /* #ifdef MP */
  width: 126rpx;
  left: 15rpx;
  /* #endif */
  /* #ifndef MP */
  width: 56rpx;
  left: 33rpx;
  /* #endif */
  height: 56rpx;
  font-size: 33rpx;
  z-index: 99;
  background: rgba(255, 255, 255, 0.3);
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 40rpx;

  &.right {
    right: 33rpx;
    left: unset;
  }

  &.on {
    background: unset;
    color: #333;
  }

  &.homeIndex {
    /* #ifdef MP */
    width: 98rpx;
    /* #endif */
    /* #ifndef MP */
    border-color: rgba(255, 255, 255, 0);
    /* #endif */
  }
}

.home-nav .iconfont {
  width: 58rpx;
  text-align: center;
}

.home-nav .line {
  width: 1rpx;
  height: 34rpx;
  background: #b3b3b3;
}

.home-nav .icon-xiangzuo {
  width: auto;
  font-size: 28rpx;
}

.share-box {
  z-index: 1000;
  position: fixed;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
}

.share-box image {
  width: 100%;
  height: 100%;
}

.attrImg {
  width: 66rpx;
  height: 66rpx;
  border-radius: 6rpx;
  display: block;
  margin-right: 14rpx;
}

.switchTxt {
  height: 60rpx;
  flex: 1;
  line-height: 60rpx;
  box-sizing: border-box;
  background: #eeeeee;
  padding: 0 10rpx;
  border-radius: 8rpx;
  text-align: center;
}

.attribute {
  padding: 10rpx 30rpx;

  .line1 {
    width: 600rpx;
  }
}

.flex {
  display: flex;
  justify-content: space-between;
  width: 100%;
}

.flexs {
  display: flex;
}

.attr-txt {
  display: flex;
  flex-wrap: nowrap;
  width: 130rpx;
}
</style>
