<template>
  <view :style="colorStyle">
    <view class="product-con">
      <!-- 头部 -->
      <!-- #ifndef APP-PLUS -->
      <view class="navbar" :style="{ height: navH + 'rpx', opacity: opacity }">
        <view class="navbarH" :style="'height:' + navH + 'rpx;'">
          <view class="navbarCon acea-row row-center-wrapper">
            <view class="header acea-row row-center-wrapper">
              <view class="item line1">
                {{ storeInfo.title }}
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
        <!-- #ifdef H5 -->
        <view class="iconfont icon-gengduo2" @click="moreNav"></view>
      </view>
      <!-- #endif -->
      <!-- #endif -->
      <homeList
        :navH="navH"
        :returnShow="returnShow"
        :currentPage="currentPage"
        :sysHeight="sysHeight"
      >
      </homeList>
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
          <!-- 拼团特有商品信息模块（PageDesign 中 productInfo 已隐藏，由拼团页面自己渲染） -->
          <productConSwiper :imgUrls="imgUrls"></productConSwiper>
          <view class="nav acea-row row-between-wrapper">
            <view class="share acea-row row-between row-bottom">
              <view class="money font-color">
                {{ $t(`￥`) }}<text class="num">{{ storeInfo.price || 0 }}</text>
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
                  >{{ $t(`￥`) }}{{ storeInfo.product_price || storeInfo.ot_price || 0 }}</text
                >
              </view>
              <view
                class="iconfont icon-fenxiang"
                @click="listenerActionSheet"
              ></view>
            </view>
          </view>
          <view class="wrapper">
            <view class="introduce acea-row row-between">
              <view class="infor"> {{ storeInfo.title }}</view>
            </view>
            <view class="label acea-row row-between-wrapper">
              <view class="stock"
                >{{ $t(`类型`) }}：{{ storeInfo.people || 0
                }}{{ $t(`人团`) }}</view
              >
              <view
                >{{ $t(`限量剩余`) }}:
                {{ storeInfo.quota ? storeInfo.quota : 0 }}
                {{ $t(storeInfo.unit_name) || "" }}
              </view>
              <view
                >{{ $t(`累计销量`) }}：{{
                  storeInfo.sales ? storeInfo.sales : 0
                }}
                {{ $t(storeInfo.unit_name) || "" }}
              </view>
            </view>
          </view>
          <view
            class="attribute acea-row row-between-wrapper"
            @tap="selecAttr"
            v-if="attribute.productAttr.length"
          >
            <view class="flex">
              <view style="display: flex; align-items: center; width: 90%">
                <view class="attr-txt"> {{ attr }}： </view>
                <view class="atterTxt line1" style="width: 82%">{{ attrValue }}</view>
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
          <!-- 已拼统计 -->
          <view class="bg-color" v-if="pink_ok_sum > 0">
            <view class="notice acea-row row-middle">
              <view class="num font-num">
                <text class="iconfont icon-laba"></text>
                {{ $t(`已拼`) }}{{ pink_ok_sum }}{{ $t(`件`) }}<text class="line">|</text>
              </view>
            </view>
          </view>
          <!-- 进行中的开团列表（来自 extensions.pink） -->
          <view class="assemble" v-if="pink.length">
            <view
              class="item acea-row row-between-wrapper"
              v-for="(item, index) in pink"
              :key="index"
              v-if="index < AllIndex"
            >
              <view class="pictxt acea-row row-between-wrapper">
                <view class="pictrue">
                  <image :src="item.avatar"></image>
                </view>
                <view class="text line1">{{ item.nickname }}</view>
              </view>
              <view class="right acea-row row-middle">
                <view>
                  <view class="lack"
                    >{{ $t(`还差`)
                    }}<text class="font-num">{{ item.count }}</text
                    >{{ $t(`人成团`) }}</view
                  >
                  <view class="time">
                    <count-down
                      :is-day="false"
                      :tip-text="' '"
                      :day-text="' '"
                      :hour-text="':'"
                      :minute-text="':'"
                      :second-text="' '"
                      :datatime="item.stop_time"
                    >
                    </count-down>
                  </view>
                </view>
                <navigator :render-link="false"
                  hover-class="none"
                  :url="
                    '/pages/activity/goods_combination_status/index?id=' +
                    item.id
                  "
                  class="spellBnt"
                >
                  {{ $t(`去拼单`) }}
                </navigator>
              </view>
            </view>
            <template v-if="pink.length">
              <view class="more" @tap="showAll" v-if="pink.length > AllIndex"
                >{{ $t(`查看更多`) }}<text class="iconfont icon-xiangxia"></text
              ></view>
              <view
                class="more"
                @tap="hideAll"
                v-else-if="
                  pink.length === AllIndex && pink.length !== AllIndexDefault
                "
              >
                {{ $t(`收起`) }}<text class="iconfont icon-xiangshang"></text>
              </view>
            </template>
          </view>
          <!-- 拼团玩法 -->
          <view class="playWay">
            <view class="title acea-row row-between-wrapper">
              <view>{{ $t(`拼团玩法`) }}</view>
            </view>
            <view class="way acea-row row-middle">
              <view class="item">
                <text class="num">①</text>
                <text>{{ $t(`开团/参团`) }}</text>
              </view>
              <view class="iconfont icon-arrow"></view>
              <view class="item">
                <text class="num">②</text>
                <text>{{ $t(`邀请好友`) }}</text>
              </view>
              <view class="iconfont icon-arrow"></view>
              <view class="item">
                <text class="num">③</text>
                <text>{{ $t(`满员发货`) }}</text>
              </view>
            </view>
          </view>
          <!-- PageDesign 渲染其他 DIY 模块（产品介绍、评论、display_sections 等） -->
          <PageDesign
            :diyData="diyData"
            :productData="storeInfo"
            :priceData="priceData"
            :skuList="skuArr"
            :reply="reply"
            :replyCount="replyCount"
            :replyChance="replyChance"
            :productId="id"
            :couponList="couponList"
            :activity="activity"
            :attr="attribute"
            :attrTxt="attrTxt"
            :attrValue="attrValue"
            :displaySections="displaySections"
            :goodList="good_list"
            @changeSpec="onChangeSpecFromPageDesign"
            @showSpecModal="onShowSpecModalFromPageDesign"
            @blockSelect="onBlockSelect"
          ></PageDesign>
        </view>
        <view class="uni-p-b-98"></view>
      </scroll-view>
      <view class="footer acea-row row-between-wrapper">
        <navigator :render-link="false"
          hover-class="none"
          open-type="switchTab"
          class="item"
          url="/pages/index/index"
        >
          <view class="iconfont icon-shouye6"></view>
          <view class="p_center">{{ $t(`首页`) }}</view>
        </navigator>
        <view
          class="bnt acea-row"
          v-if="
            attribute.productSelect.quota > 0 &&
            attribute.productSelect.stock > 0
          "
        >
          <view class="buy bnts" @tap="goCat">{{ $t(`发起拼团`) }}</view>
        </view>
        <view class="bnt acea-row" v-else>
          <view class="bnts no-goods">{{ $t(`已售罄`) }}</view>
        </view>
      </view>
    </view>

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
      <button class="item" hover-class="none" @tap="goPoster('scombination')">
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
    <view class="share-box" v-if="H5ShareBox">
      <image
        :src="imgHost + '/statics/images/share-info.png'"
        @click="H5ShareBox = false"
      ></image>
    </view>

    <product-window
      :attr="attribute"
      :limitNum="1"
      @myevent="onMyEvent"
      @ChangeAttr="ChangeAttr"
      @ChangeCartNum="ChangeCartNum"
      @iptCartNum="iptCartNum"
      @attrVal="attrVal"
      @getImg="showImg"
    >
    </product-window>
    <cus-previewImg
      ref="cusPreviewRef"
      :list="skuArr"
      @changeSwitch="changeSwitch"
      @shareFriend="listenerActionSheet"
    />
    <!-- 分享按钮 -->
    <kefuIcon
      :ids="storeInfo.product_id"
      :routineContact="routineContact"
    ></kefuIcon>
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
import { getProductDetail } from "@/api/store.js";
import { getCenterOrderConfirm } from "@/api/order-center.js";
import { getCombinationDetail, scombinationCode } from "@/api/activity.js";
import { getThemeInfo } from "@/api/api.js";
import productConSwiper from "@/components/productConSwiper/index.vue";
import productWindow from "./component/productWindow.vue";
import PageDesign from "@/subpackage/diyComponents/pageDesign.vue";
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
import cusPreviewImg from "@/components/cusPreviewImg/index.vue";
import homeList from "@/components/homeList";
import { HTTP_REQUEST_URL } from "@/config/app";
import util from "@/utils/util.js";
import Cache from "@/utils/cache.js";
import i18n from "@/utils/lang.js";
import { ref, computed, watch, nextTick, getCurrentInstance } from "vue";
import { storeToRefs } from "pinia";
import { onLoad, onNavigationBarButtonTap, onShareAppMessage } from "@dcloudio/uni-app";

const { colorStyle } = useColorTheme();

const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

const instance = getCurrentInstance();
// 模板 ref（与组件 import 同名冲突，改名 cusPreviewRef）
const cusPreviewRef = ref(null);

// ---------- data ----------
const imgHost = HTTP_REQUEST_URL;
const dataShow = ref(0);
const id = ref(0);
const activityId = ref(0);
const time = ref(0);
const countDownHour = ref("00");
const countDownMinute = ref("00");
const countDownSecond = ref("00");
const storeInfo = ref({});
const imgUrls = ref([]);
// DIY 装修配置数据（通过 getThemeInfo("detail") 获取）
const diyData = ref({});
// 价格数据（传给 PageDesign 的 productInfo 组件）
const priceData = ref({ is_vip: 0, price: 0, real_price: 0, ot_price: 0 });
// 优惠券列表（拼团商品通常无优惠券，传空数组）
const couponList = ref([]);
// 活动信息（传给 PageDesign）
const activity = ref([]);
const parameter = {
  navbar: "1",
  return: "1",
  title: i18n.t(`拼团详情页`),
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
const attrTxt = ref("");
const attrValue = ref("");
const status = ref(1);
const isAuto = ref(false);
const isShowAuth = ref(false);
const iShidden = ref(false);
const limitNum = ref(1); //限制本属性产品的个数；
const iSplus = ref(false);
const replyCount = ref(0); //总评论数量
const reply = ref([]); //评论列表
// display_sections 数据（产品类型/营销监听器注入的展示块）
const displaySections = ref([]);
// 优品推荐列表
const good_list = ref([]);
// display_sections block 选中数据（日期等）
const selectedBlockData = ref([]);
const replyChance = ref(0);
const navH = ref("");
const navList = ref([i18n.t(`商品`), i18n.t(`详情`)]);
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
const datatime = ref("");
const navActive = ref(0);
const meunHeight = ref(0);
const backH = ref("");
const weixinStatus = ref(false);
const posterbackgd = ref("/static/images/posterbackgd.png");
const cart_num = ref(1);
const homeTop = ref(20);
const returnShow = ref(true);
const H5ShareBox = ref(false); //公众号分享图片
const routineContact = ref(0);
const skuArr = ref([]);
const selectSku = ref({});
const currentPage = ref(false);
// 拼团特有：进行中的开团列表
const pink = ref([]);
const pink_ok_sum = ref(0);
const AllIndex = ref(2);
const AllIndexDefault = ref(0);

// ---------- 海报/分销二维码（替代 mixins/sharePoster） ----------
const poster = useSharePoster(() => ({
  id: id.value, //商品 id
  timeId: "", //拼团无秒杀场次
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
  shareQrcode,
  followCode,
  // 二维码组件参数（zb-code，原页面 data 与 mixin 重复声明的同名同值字段）
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
  initPoster,
  downloadFilestoreImage,
} = poster;

// ---------- watch ----------
watch(isLogin, {
  handler: function (newV, oldV) {
    if (newV) {
      getCombinationProductDetail();
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
  let routes = getCurrentPages();
  let curRoute = routes[routes.length - 1].$page.fullPath;
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
    getCombinationProductDetail();
  }
}
// 授权关闭
function authColse(e) {
  isShowAuth.value = e;
}
/**
 * 获取拼团商品详情
 * 调用通用详情接口，后端拼团监听器注入拼团价/限量/开团列表
 * 兼容旧链接格式：?id=combination_activity_id（无 activity_id）
 */
function getCombinationProductDetail() {
  // 旧格式兼容：如果没有 activity_id，id 是 combination 活动 ID
  // 需要先调旧的 combination detail 接口获取 product_id
  if (!activityId.value && id.value) {
    getCombinationDetail(id.value)
      .then((res) => {
        let productId = res.data.storeInfo.product_id;
        // 旧格式下 id 就是 activity_id
        activityId.value = id.value;
        id.value = productId;
        loadProductDetail();
      })
      .catch((err) => {
        util.Tips(
          {
            title: err,
          },
          {
            tab: 3,
          }
        );
      });
    return;
  }
  loadProductDetail();
}
/**
 * 加载商品详情（通用接口）
 */
function loadProductDetail() {
  // 调用通用详情接口，后端拼团监听器注入拼团价/限购/开团列表
  getProductDetail(id.value, "combination", activityId.value)
    .then((res) => {
      dataShow.value = 1;
      let title = res.data.storeInfo.store_name || res.data.storeInfo.title || "";
      storeInfo.value = res.data.storeInfo;
      // 通用详情接口返回 slider_image，兼容旧字段 images
      imgUrls.value = res.data.storeInfo.slider_image || res.data.storeInfo.images || [];
      storeInfo.value.description = (storeInfo.value.description || "").replace(
        /<img/gi,
        '<img style="max-width:100%;height:auto;float:left;display:block" '
      );
      // 构造 priceData 传给 PageDesign（其他 DIY 模块可能需要）
      priceData.value = {
        is_vip: 0,
        price: storeInfo.value.price || 0,
        real_price: storeInfo.value.price || 0,
        ot_price: storeInfo.value.ot_price || 0,
      };
      attribute.value.productAttr = res.data.productAttr;
      productValue.value = res.data.productValue;
      // 拼团监听器注入 num（每人限购数量）
      attribute.value.productSelect.num = res.data.storeInfo.num || 0;
      replyCount.value = res.data.replyCount;
      reply.value = res.data.reply ? [res.data.reply] : [];
      replyChance.value = res.data.replyChance;
      // 透传后端 display_sections（产品类型/拼团监听器注入的展示块）
      displaySections.value = res.data.display_sections || [];
      good_list.value = res.data.good_list || [];
      // 拼团特有：从 extensions 读取开团列表
      let extensions = res.data.extensions || {};
      pink.value = extensions.pink || [];
      pink_ok_sum.value = extensions.pink_ok_sum || 0;
      routineContact.value = Number(res.data.routine_contact_type);
      for (let key in res.data.productValue) {
        let obj = res.data.productValue[key];
        skuArr.value.push(obj);
      }
      selectSku.value = skuArr.value[0];
      uni.setNavigationBarTitle({
        title: title.substring(0, 7) + "...",
      });
      // #ifdef H5
      PromotionCode.value = res.data.storeInfo.code_base;
      storeImage.value = storeInfo.value.image;
      setShare();
      // #endif
      // 设置二维码内容（新格式 URL，分享链接回传 product_id + activity_id）
      let shareActivityId = storeInfo.value.activity_id || activityId.value;
      // #ifdef H5
      codeVal.value =
        window.location.origin +
        "/pages/activity/goods_combination_details/index?id=" +
        storeInfo.value.product_id +
        "&activity_id=" +
        shareActivityId +
        "&spid=" +
        appStore.uid;
      // #endif
      // #ifdef APP-PLUS
      codeVal.value =
        HTTP_REQUEST_URL +
        "/pages/activity/goods_combination_details/index?id=" +
        storeInfo.value.product_id +
        "&activity_id=" +
        shareActivityId +
        "&spid=" +
        appStore.uid;
      // #endif
      // #ifdef APP-PLUS
      downloadFilestoreImage();
      // #endif
      // #ifdef MP
      downloadFilestoreImage();
      // #endif
      DefaultSelect();
    })
    .catch((err) => {
      util.Tips(
        {
          title: err,
        },
        {
          tab: 3,
        }
      );
    });
}
/**
 * 获取 DIY 装修配置
 * 与通用详情页一致，通过 getThemeInfo("detail") 获取后台装修的商品详情页配置
 * 拼团页面通过 isHide 隐藏需要重写的 DIY 模块，由页面自己渲染拼团特有内容
 */
function getDiyData() {
  let previewThemeId = uni.getStorageSync("previewThemeId");
  let data = {};
  if (previewThemeId) data.theme_id = previewThemeId;
  // 需要隐藏的 DIY 模块名称列表（由拼团页面自己重写）
  let hiddenModules = ["productInfo"];
  getThemeInfo("detail", data).then((res) => {
    // 局部变量与 diyData ref 同名遮蔽，改名 diyDataTmp
    let diyDataTmp = res.data || {};
    if (diyDataTmp.value) {
      Object.keys(diyDataTmp.value).forEach(function (key) {
        if (hiddenModules.indexOf(diyDataTmp.value[key].name) !== -1) {
          diyDataTmp.value[key].isHide = true;
        }
      });
    }
    diyData.value = diyDataTmp;
  });
}
// #ifdef H5
function setShare() {
  let shareActivityId = storeInfo.value.activity_id || activityId.value;
  let href =
    window.location.origin +
    "/pages/activity/goods_combination_details/index?id=" +
    storeInfo.value.product_id +
    "&activity_id=" +
    shareActivityId +
    "&spid=" +
    appStore.uid;
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
          link: href,
          imgUrl: storeInfo.value.image,
        }
      )
      .then((res) => {})
      .catch((err) => {});
}
// #endif
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
    attribute.value.productSelect["product_stock"] = 0;
    attribute.value.productSelect["unique"] = storeInfo.value.unique || "";
    attribute.value.productSelect["cart_num"] = 1;
    attrValue.value = "";
    attrTxt.value = i18n.t(`请选择`);
  }
}
function selecAttr() {
  attribute.value.cartAttr = true;
}
/**
 * PageDesign 规格变化回调
 */
function onChangeSpecFromPageDesign(item) {
  if (item && item.suk) {
    let values = item.suk.split(",");
    if (
      attribute.value.productAttr &&
      attribute.value.productAttr.length === values.length
    ) {
      // 参数名与 attr ref 同名遮蔽，改名 attrItem
      attribute.value.productAttr.forEach((attrItem, i) => {
        attrItem["index"] = values[i];
      });
      DefaultSelect();
    }
  }
}
/**
 * PageDesign 显示规格弹窗回调
 */
function onShowSpecModalFromPageDesign() {
  attribute.value["cartAttr"] = true;
}
/**
 * PageDesign block 选中回调（日期/票种等）
 * @param {Object} block 选中的 block 数据
 */
function onBlockSelect(block) {
  if (!block) return;
  // 记录选中的 block 数据（日期等），下单时携带
  let blockData = {
    type: block.type || "",
    item: block.item || block,
  };
  // 更新已选 block 列表（同类型替换）
  let idx = selectedBlockData.value.findIndex(
    (b) => b.type === blockData.type
  );
  if (idx >= 0) {
    selectedBlockData.value[idx] = blockData;
  } else {
    selectedBlockData.value.push(blockData);
  }
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
  //设置默认数据
  if (productSelect.cart_num == undefined) productSelect.cart_num = 1;
  if (changeValue) {
    num.cart_num++;
    let arrMin = [];
    arrMin.push(nums);
    arrMin.push(quota);
    arrMin.push(productStock);
    let minN = Math.min.apply(null, arrMin);
    if (num.cart_num >= minN) {
      attribute.value.productSelect["cart_num"] = minN ? minN : 1;
      cart_num.value = minN ? minN : 1;
    }
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
  currentPage.value = false;
  scrollY.value = scrollYTmp;
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
function tap(item, index) {
  // 局部变量与 id ref 同名遮蔽，改名 viewId
  var viewId = item.id;
  toView.value = viewId;
  navActive.value = index;
  lock.value = true;
  scrollTop.value =
    index > 0
      ? topArr.value[index] - app.globalData.navHeight / 2
      : topArr.value[index];
}
//点击sku图片打开轮播图
function showImg(index) {
  cusPreviewRef.value.open(selectSku.value.suk);
}
/**
 * 分享打开
 *
 */
function listenerActionSheet() {
  currentPage.value = false;
  if (isLogin.value === false) {
    toLogin();
  } else {
    if (posterImage.value) {
      posters.value = true;
      return;
    }
    // #ifdef H5
    if (wechat.isWeixin() === true) {
      weixinStatus.value = true;
    }
    // #endif
    // #ifndef APP-PLUS
    downloadFilePromotionCode();
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
  if (url.indexOf("https://") > -1) return url;
  else return url.replace("http://", "https://");
}
/**
 * 获取拼团分销二维码（页面自身实现，覆盖 composable 同名方法：拼团需用活动 ID）
 * 使用拼团活动 ID 调用 scombinationCode
 * @param function successFn 下载完成回调
 */
function downloadFilePromotionCode(successFn) {
  let combinationId = storeInfo.value.activity_id || activityId.value;
  scombinationCode(combinationId)
    .then((res) => {
      uni.downloadFile({
        url: setDomain(res.data.code),
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
    })
    .catch((err) => {
      isDown.value = false;
      PromotionCode.value = "";
    });
}
// 小程序关闭分享弹窗；
function goFriend() {
  posters.value = false;
}
// zb-code 二维码生成回调（页面自身实现，覆盖 composable 同名方法）
function qrR(res) {
  // #ifdef H5
  if (!wechat.isWeixin() || shareQrcode.value != "1") {
    PromotionCode.value = res;
    followCode.value = "";
  }
  // #endif
  // #ifdef APP-PLUS
  PromotionCode.value = res;
  // #endif
}
/**
 * 生成海报（页面自身实现，覆盖 composable 的 goPoster，使用拼团活动 ID 调用 scombinationCode）
 */
async function goPoster(type) {
  posters.value = false;
  canvasStatus.value = true;
  let combinationId = storeInfo.value.activity_id || activityId.value;
  let arr2;
  // #ifdef MP
  scombinationCode(combinationId)
    .then((res) => {
      uni.downloadFile({
        url: setDomain(res.data.code),
        success: function (res) {
          isDown.value = false;
          PromotionCode.value = res.tempFilePath;
          arr2 = [posterbackgd.value, storeImage.value, PromotionCode.value];
          initPoster(arr2);
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
      return util.Tips({
        title: err,
      });
    });
  // #endif
  // #ifdef H5 || APP-PLUS
  arr2 = [posterbackgd.value, storeImageBase64.value, PromotionCode.value];
  if (!storeImageBase64.value)
    return util.Tips({
      title: i18n.t(`正在下载海报,请稍后再试`),
    });
  initPoster(arr2);
  // #endif
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
    attribute.value.productSelect["unique"] = productSelect.unique;
    attribute.value.productSelect["vipPrice"] = productSelect.vipPrice;
    attrTxt.value = i18n.t(`已选择`);
    attrValue.value = productSelect.suk;
  }
}
function showAll() {
  AllIndexDefault.value = AllIndex.value;
  AllIndex.value = pink.value.length;
}
function hideAll() {
  AllIndex.value = AllIndexDefault.value;
}
/*
 *  下订单（发起拼团）
 *  与积分页面一致：先 getCenterOrderConfirm 预校验，再跳转中台订单确认页
 */
const goCat = async function () {
  currentPage.value = false;
  var productSelect = productValue.value[attrValue.value];
  // 单规格（无属性）商品回退到默认选中规格，避免 productSelect 为 undefined
  if (productSelect === undefined && !attribute.value.productAttr.length) {
    productSelect = attribute.value.productSelect;
  }
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
  if (cart_num.value <= 0) {
    return util.Tips({
      title: i18n.t(`请选择数量`),
    });
  }
  isOpen.value = false;
  // 走通用下单流程，跳转中台订单确认页
  let productId = storeInfo.value.product_id || id.value || 0;
  // 局部变量与 activityId ref 同名遮蔽，改名 orderActivityId
  let orderActivityId = storeInfo.value.activity_id || activityId.value || 0;
  let productType = storeInfo.value.product_type || storeInfo.value.target_product_type || "normal";
  let skuUnique = (productSelect && productSelect.unique) || storeInfo.value.unique || "";

  if (!productId || !orderActivityId) {
    return util.Tips({
      title: i18n.t(`商品信息不完整`),
    });
  }

  // 预校验：调用确认接口检查库存、限购
  try {
    uni.showLoading({ title: i18n.t(`校验中...`), mask: true });
    await getCenterOrderConfirm({
      product_id: productId,
      sku_unique: skuUnique,
      product_type: productType,
      activity_type: "combination",
      activity_id: orderActivityId,
      num: cart_num.value || 1,
    });
    uni.hideLoading();
  } catch (e) {
    uni.hideLoading();
    // 弹窗提示实际错误信息，不跳转订单确认页
    var errMsg = typeof e === "string" ? e : (e.msg || e.message || i18n.t(`校验失败`));
    uni.showModal({
      title: i18n.t(`提示`),
      content: errMsg,
      showCancel: false,
    });
    return;
  }

  uni.navigateTo({
    url: `/pages/center/order/create/index?product_id=${productId}&sku_unique=${skuUnique}&product_type=${productType}&activity_type=combination&activity_id=${orderActivityId}&num=${cart_num.value || 1}`,
  });
};

// ---------- 生命周期 ----------
onLoad((options) => {
  let statusBarHeight = "";
  var pageStack = getCurrentPages();
  returnShow.value = pageStack.length === 1 ? false : true;
  //设置商品列表高度
  uni.getSystemInfo({
    success: function (res) {
      height.value = res.windowHeight;
      statusBarHeight = res.statusBarHeight;
    },
  });
  // #ifdef H5
  navH.value = 96;
  // #endif
  // #ifdef APP-PLUS
  navH.value = 30;
  // #endif
  // #ifdef MP
  navH.value = app.globalData.navHeight;
  let menuButtonInfo = uni.getMenuButtonBoundingClientRect();
  meunHeight.value = menuButtonInfo.height;
  backH.value = navH.value / 2 + meunHeight.value / 2;

  //扫码携带参数处理
  if (options.scene) {
    let value = util.getUrlParams(decodeURIComponent(options.scene));
    if (value.id) {
      id.value = value.id;
      activityId.value = value.activity_id || 0;
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
  }
  // #endif

  if (options.id) {
    id.value = options.id;
    activityId.value = options.activity_id || 0;
  }
  // 获取 DIY 装修配置（与通用详情页一致）
  getDiyData();
  if (isLogin.value) {
    getCombinationProductDetail();
  } else {
    // 记录登录回跳 URL（兼容新旧格式）
    Cache.set(
      "login_back_url",
      `/pages/activity/goods_combination_details/index?id=${id.value}${
        activityId.value ? "&activity_id=" + activityId.value : ""
      }`
    );
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

// #ifdef MP
onShareAppMessage(() => {
  let shareActivityId = storeInfo.value.activity_id || activityId.value;
  return {
    title: storeInfo.value.title,
    path:
      "/pages/activity/goods_combination_details/index?id=" +
      storeInfo.value.product_id +
      "&activity_id=" +
      shareActivityId +
      "&spid=" +
      appStore.uid,
    imageUrl: storeInfo.value.image,
  };
});
// #endif
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
  width: 400rpx;
  height: 96rpx;
  font-size: 30rpx;
  color: #050505;
  background-color: #fff;
  /* #ifdef MP */
  padding-right: 95rpx;
  /* #endif */
}

.icon-xiangzuo {
  /* #ifdef H5 */
  top: 30rpx !important;
  /* #endif */
}

.navbar .header .item {
  position: relative;
  margin: 0 25rpx;
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
}

.product-con .nav {
  width: 100%;
  height: 100rpx;
  padding: 0 30rpx;
  box-sizing: border-box;
  background-color: #fff;
}

.product-con .nav .money {
  font-size: 28rpx;
  color: #e93323;
  font-weight: bold;

  image {
    width: 34rpx;
    height: 34rpx;
  }
}

.product-con .nav .money .num {
  font-size: 48rpx;
  padding-left: 16rpx;
}

.product-con .nav .money .y-money {
  font-size: 26rpx;
  margin-left: 10rpx;
  text-decoration: line-through;
  color: #82848f;
  font-weight: normal;
}

.product-con .nav .iconfont {
  color: #333;
  font-size: 30rpx;
  margin-left: 20rpx;
}

.product-con .wrapper {
  padding: 0 32rpx 32rpx 32rpx;
  width: 100%;
  box-sizing: border-box;
}

.product-con .wrapper .introduce {
  margin: 0;
}

.product-con .wrapper .introduce .infor {
  font-size: 32rpx;
  color: #282828;
}

.product-con .wrapper .label {
  display: flex;
  justify-content: space-between;
  margin: 18rpx 0 0 0;
  font-size: 24rpx;
  color: #82848f;
}

.product-con .wrapper .label .stock {
}

.product-con .notice {
  width: 100%;
  height: 62rpx;
  margin-top: 20rpx;
  padding: 0 30rpx;
  box-sizing: border-box;
  background-color: rgba(255, 255, 255, 0.88);
}

.product-con .notice .num {
  font-size: 24rpx;
}

.product-con .notice .num .iconfont {
  font-size: 30rpx;
  vertical-align: -3rpx;
  margin-right: 20rpx;
}

.product-con .notice .num .line {
  color: #282828;
  margin-left: 15rpx;
}

.product-con .assemble {
  background-color: #fff;
}

.product-con .assemble .item {
  padding-right: 30rpx;
  margin-left: 30rpx;
  border-bottom: 1rpx solid #f0f0f0;
  height: 132rpx;
}

.product-con .assemble .item .pictxt {
  width: 295rpx;
}

.product-con .assemble .item .pictxt .text {
  width: 194rpx;
}

.product-con .assemble .item .pictxt .pictrue {
  width: 80rpx;
  height: 80rpx;
}

.product-con .assemble .item .pictxt .pictrue image {
  width: 100%;
  height: 100%;
  border-radius: 50%;
}

.product-con .assemble .item .right .lack {
  font-size: 24rpx;
  color: #333333;
}

.product-con .assemble .item .right .time {
  position: relative;
  left: -10rpx;
  font-size: 22rpx;
  color: #82848f;
  margin-top: 5rpx;
}

.product-con .assemble .item .right .spellBnt {
  font-size: 24rpx;
  color: #fff;
  width: 140rpx;
  height: 50rpx;
  border-radius: 50rpx;
  text-align: center;
  line-height: 50rpx;
  background-color: var(--view-theme);
  margin-left: 30rpx;
}

.product-con .assemble .item .right .spellBnt .iconfont {
  font-size: 25rpx;
  margin-left: 5rpx;
}

.product-con .assemble .more {
  font-size: 24rpx;
  color: #282828;
  text-align: center;
  height: 90rpx;
  line-height: 90rpx;
}

.product-con .assemble .more .iconfont {
  margin-left: 13rpx;
  font-size: 25rpx;
}

.product-con .playWay {
  background-color: #fff;
  padding: 0 30rpx;
  margin-top: 20rpx;
  font-size: 28rpx;
  color: #282828;
}

.product-con .playWay .title {
  height: 86rpx;
  border-bottom: 1rpx solid #eee;
}

.product-con .playWay .way {
  min-height: 110rpx;
  font-size: 26rpx;
  color: #282828;
}

.product-con .playWay .way .iconfont {
  color: #cdcdcd;
  font-size: 40rpx;
  margin: 0 35rpx;
}

.product-con .playWay .way .item .num {
  font-size: 34rpx;
  margin-right: 6rpx;
  width: 17px;
  height: 28px;
  display: inline-block;
  vertical-align: middle;
}

.product-con .footer {
  padding: 0 20rpx 0 30rpx;
  position: fixed;
  bottom: 0;
  width: 100%;
  box-sizing: border-box;
  background-color: #fff;
  z-index: 277;
  border-top: 1rpx solid #f0f0f0;
  height: 100rpx;
  display: flex;
  align-items: center;
  flex-wrap: nowrap;
  height: calc(100rpx + constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
  height: calc(100rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
}

.product-con .footer .item {
  width: 100rpx;
  font-size: 18rpx;
  color: #666;
}

.product-con .footer .item .iconfont {
  text-align: center;
  font-size: 40rpx;
}

.product-con .footer .bnt {
  width: 100%;
  height: 76rpx;
}

.product-con .footer .bnt .bnts {
  width: 100%;
  text-align: center;
  line-height: 76rpx;
  color: #fff;
  font-size: 28rpx;
}

.product-con .footer .bnt .buy {
  border-radius: 50rpx;
  background-color: var(--view-theme);
}

.product-con .footer .bnt .no-goods {
  border-radius: 50rpx;
  background-color: #cccccc;
}

.product-con .conter {
  display: block;
}

.product-con .conter img {
  display: block;
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
  font-size: 22rpx;
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

.df {
  display: flex;
  align-items: center;
  flex-wrap: nowrap;
  width: 100%;
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
