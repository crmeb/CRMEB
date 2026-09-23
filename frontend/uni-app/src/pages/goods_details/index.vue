<template>
  <view class="product-con" :style="colorStyle">
    <view class="product-con">
      <!-- #ifndef APP-PLUS -->
      <view class="navbar" :style="{ height: navH + 'rpx', opacity: opacity }">
        <view class="navbarH" :style="'height:' + navH + 'rpx;'">
          <view
            class="navbarCon acea-row row-center-wrapper"
            :style="{ paddingRight: navbarRight - 20 + 'px' }"
          >
            <view class="header acea-row row-center-wrapper">
              <view class="item on">{{ $t(`商品详情`) }}</view>
            </view>
          </view>
        </view>
      </view>
      <!-- #endif -->
      <!-- <view class='iconfont icon-xiangzuo' :style="{top:navH/2+'rpx',opacity:(1-opacity)}" @tap='returns'></view> -->
      <!-- #ifndef APP-PLUS -->
      <view
        id="home"
        class="home acea-row row-center-wrapper"
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
        id="goods-menu-anchor"
        class="home right acea-row row-center-wrapper"
        :class="[opacity > 0.5 ? 'on' : '']"
        :style="{ top: homeTop + 'rpx' }"
      >
        <!-- #endif -->
        <!-- #ifdef H5 -->
        <view class="iconfont icon-gengduo2" @click="moreNav"> </view>
      </view>
      <!-- #endif -->
      <homeList
        :navH="navH"
        :positionStyle="menuPositionStyle"
        :returnShow="returnShow"
        :currentPage="currentPage"
        :sysHeight="sysHeight"
      ></homeList>

      <view>
        <view id="past0">
          <!-- #ifdef APP-PLUS || MP -->
          <view class="" :style="'width:100%;' + 'height:' + sysHeight"></view>
          <!-- #endif -->
          <PageDesign
            :diyData="diyData"
            :productData="storeInfo"
            :priceData="realPriceData"
            :skuList="skuArr"
            :reply="reply"
            :replyCount="replyCount"
            :replyChance="replyChance"
            :productId="id"
            :couponList="couponList"
            :activity="activity"
            :attr="attr"
            :attrTxt="attrTxt"
            :attrValue="attrValue"
            :isShowPaidVip="isShowPaidVip"
            :displaySections="displaySections"
            :goodList="good_list"
            @bindSortId="bindSortId"
            @changeSpec="onChangeSpecFromPageDesign"
            @showSpecModal="onShowSpecModalFromPageDesign"
            @share="listenerActionSheet"
            @showCoupon="couponTap"
            @openModal="openModal"
            @goActivity="goActivity"
            @blockSelect="onBlockSelect"
          ></PageDesign>
        </view>
        <view class="uni-p-b-98"></view>
      </view>

      <productBottom
        :diyData="diyData"
        :storeInfo="storeInfo"
        :is_gift="is_gift"
        :CartCount="CartCount"
        :noGoods="noGoods"
        :attr="attr"
        :presale_pay_status="presale_pay_status"
        :animated="animated"
        @setCollect="setCollect"
        @goCart="goCart"
        @goGift="goGift"
        @joinCart="joinCart"
        @goBuy="goBuy"
        @share="listenerActionSheet"
      ></productBottom>
      <shareRedPackets
        :sharePacket="sharePacket"
        @listenerActionSheet="listenerActionSheet"
        @closeChange="closeChange"
        :showAnimate="showAnimate"
        @boxStatus="boxStatus"
      ></shareRedPackets>
      <!-- 组件 -->
      <productWindow
        :attr="attr"
        :isShow="1"
        :iSplus="1"
        :iScart="isReadOnlyAttr ? 1 : 0"
        :limitNum="storeInfo.limit_num"
        :minQty="storeInfo.min_qty"
        :unitName="storeInfo.unit_name"
        @myevent="onMyEvent"
        @ChangeAttr="ChangeAttr"
        @ChangeCartNum="ChangeCartNum"
        @attrVal="attrVal"
        @iptCartNum="iptCartNum"
        @goCat="onProductWindowConfirm"
        id="product-window"
        :is_vip="is_vip"
        @getImg="showImg"
        :is_virtual="storeInfo.is_virtual"
        :readOnlyAttr="isReadOnlyAttr"
      ></productWindow>
      <cus-previewImg
        ref="cusPreviewImgRef"
        :list="skuArr"
        @changeSwitch="changeSwitch"
        @shareFriend="listenerActionSheet"
      />
      <swiperPreview
        ref="cusSwiperImg"
        :list="storeInfo.slider_image"
      ></swiperPreview>
      <couponListWindow
        :coupon="coupon"
        v-if="coupon"
        @ChangCouponsClone="ChangCouponsClone"
        @ChangCoupons="ChangCoupons"
        @ChangCouponsUseState="ChangCouponsUseState"
        @tabCouponType="tabCouponType"
      ></couponListWindow>
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
        <!-- #ifdef H5  -->
        <div
          class="item copy-data"
          v-if="storeInfo.command_word != ''"
          :data-clipboard-text="storeInfo.command_word"
        >
          <view class="iconfont icon-fuzhikouling"></view>
          <text>{{ $t(`复制口令`) }}</text>
        </div>
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
        <button class="item" hover-class="none" @click="goPoster">
          <view class="iconfont icon-haibao"></view>
          <view class="">{{ $t(`生成海报`) }}</view>
        </button>
      </view>
      <view class="mask" v-if="posters" @click="listenerActionClose"></view>
      <!-- #ifdef MP -->
      <!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
      <!-- #endif -->
      <!-- 海报展示 -->
      <view class="poster-pop" v-if="posterImageStatus">
        <image
          src="../../static/images/poster-close.png"
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
      <view class="mask" v-if="posterImageStatus"></view>
      <canvas class="canvas" canvas-id="myCanvas" v-if="canvasStatus"></canvas>
      <!-- 发送给朋友图片 -->
      <view class="share-box" v-if="H5ShareBox">
        <image
          :src="imgHost + '/statics/images/share-info.png'"
          @click="H5ShareBox = false"
        ></image>
      </view>
      <kefuIcon
        :ids="parseInt(id)"
        :routineContact="routineContact"
        :storeInfo="storeInfo"
        :goodsCon="1"
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
      <specs
        ref="specsRef"
        :specsInfo="storeInfo.params_list"
      ></specs>
      <!-- 服务抽屉 -->
      <serviceModal
        ref="protection"
        :ensureInfo="storeInfo.protection_list"
      ></serviceModal>
    </view>
  </view>
</template>

<script setup>
import {
  ref,
  reactive,
  computed,
  watch,
  nextTick,
  getCurrentInstance,
} from "vue";
import {
  onLoad,
  onReady,
  onPageScroll,
  onNavigationBarButtonTap,
  onShareAppMessage,
  onShareTimeline,
} from "@dcloudio/uni-app";
import { storeToRefs } from "pinia";
import {
  getProductDetail,
  getProductCode,
  collectAdd,
  collectDel,
  postCartAdd,
  realPrice,
} from "@/api/store.js";
import { getUserInfo as getUserInfoApi, userShare } from "@/api/user.js";
import { getCoupons, getThemeInfo } from "@/api/api.js";
import { getCartCounts } from "@/api/order.js";
import { toLogin } from "@/libs/login.js";

import cusPreviewImg from "@/components/cusPreviewImg/index.vue";
import swiperPreview from "@/components/cusPreviewImg/swiperPreview.vue";
import couponListWindow from "@/components/couponListWindow";
import productWindow from "@/components/productWindow";
import shareRedPackets from "@/components/shareRedPackets";
import kefuIcon from "@/components/kefuIcon";
import menuIcon from "@/components/menuIcon.vue";
import { updateURLParameter } from "@/utils";
import ClipboardJS from "@/plugin/clipboard/clipboard.js";
// #ifdef MP
import authorize from "@/components/Authorize";
// #endif
// #ifdef APP-PLUS
import { TOKENNAME } from "@/config/app.js";
// #endif
import { HTTP_REQUEST_URL } from "@/config/app";
const app = getApp();
import { useColorTheme } from "@/composables/useColorTheme";
import { useSharePoster } from "@/composables/useSharePoster";
import homeList from "@/components/homeList";
import specs from "./components/specs/index.vue";
import serviceModal from "./components/serviceModal/index.vue";
import PageDesign from "@/subpackage/diyComponents/pageDesign.vue";
import productBottom from "@/subpackage/diyComponents/productBottom.vue";
import { useAppStore } from "@/store/modules/app";
import { useIndexDataStore } from "@/store/modules/indexData";
import util from "@/utils/util.js";
import i18n from "@/utils/lang.js";
import ActivePermission from "@/libs/permission.js";
// #ifdef H5
import wechat from "@/libs/wechat.js";
// #endif

// 原 directives: { trigger: { inserted } }；v-trigger 未在模板使用，
// Vue3 指令钩子 inserted → mounted，此处按 Vue3 命名保留
const vTrigger = {
  mounted(el, binging) {
    el.click();
  },
};

const { colorStyle } = useColorTheme();

const appStore = useAppStore();
const indexDataStore = useIndexDataStore();
const { isLogin } = storeToRefs(appStore);
const { cartNum } = storeToRefs(indexDataStore);

// 页面实例（onReady 中 createSelectorQuery().in() 使用）
const instance = getCurrentInstance();

/**
 * 生成 mock 日历日期数据（当前月份每天一条）
 * 用于本地调试 BlockCalendar 渲染效果
 * @return {Array} [{date, status, min_price, stock_level}]
 */
function buildMockCalendarDates() {
  const now = new Date();
  const year = now.getFullYear();
  const month = now.getMonth() + 1;
  const daysInMonth = new Date(year, month, 0).getDate();
  const dates = [];
  for (let d = 1; d <= daysInMonth; d++) {
    const dateStr = `${year}-${String(month).padStart(2, "0")}-${String(d).padStart(2, "0")}`;
    // 模拟库存状态：前 10 天充足，中间 10 天紧张，最后几天售罄
    let status, minPrice;
    if (d <= 10) {
      status = "sufficient";
      minPrice = 99;
    } else if (d <= 20) {
      status = "tight";
      minPrice = 129;
    } else {
      status = "sold_out";
      minPrice = null;
    }
    dates.push({
      date: dateStr,
      status,
      min_price: minPrice,
      stock_level: status === "sufficient" ? "high" : status === "tight" ? "low" : "none",
    });
  }
  return dates;
}

// ---------- data ----------
// ===== 本地调试开关：是否强制使用 mock display_sections 数据 =====
// true：忽略接口返回，使用下面 mockDisplaySections 渲染所有 4 种 component + 6 种 block
// false：使用接口返回的 res.data.display_sections（生产环境必须为 false）
const useMockDisplaySections = ref(false);
// mock 数据：覆盖 spec_preview(6 blocks) + info_card + store_card + time_card
const mockDisplaySections = ref([        // 1. 售卖形态展示位：含 6 种 block 全集，验证 SpecPreview 容器分发
        {
          key: "spec",
          component: "spec_preview",
          visible: true,
          order: 10,
          data: {
            blocks: [
              // block 1: chips 属性标签（卡密面值示例）
              {
                type: "chips",
                title: "商品属性",
                items: [
                  { label: "面值", value: "¥100" },
                  { label: "有效期", value: "30天" },
                  { label: "发放方式", value: "自动发放" },
                ],
              },
              // block 2: matrix 规格矩阵（实物多规格示例）
              {
                type: "matrix",
                title: "规格预览",
                attrs: [
                  { name: "颜色", values: ["黑色", "白色"] },
                  { name: "尺寸", values: ["S", "M", "L"] },
                ],
                skus: [
                  { suk: "黑色,S", price: 99, stock: 10, image: "" },
                  { suk: "黑色,M", price: 99, stock: 5, image: "" },
                  { suk: "黑色,L", price: 109, stock: 0, image: "" },
                  { suk: "白色,S", price: 99, stock: 8, image: "" },
                  { suk: "白色,M", price: 99, stock: 3, image: "" },
                  { suk: "白色,L", price: 109, stock: 6, image: "" },
                ],
              },
              // block 3: price_list 起价列表（门票票种示例）
              {
                type: "price_list",
                title: "票种",
                items: [
                  { name: "成人票", price_from: 99, tags: ["可退改"], stock_status: "sufficient", stock_count: 50 },
                  { name: "儿童票", price_from: 59, tags: ["不可退"], stock_status: "tight", stock_count: 8 },
                  { name: "老人票", price_from: 49, tags: ["凭身份证"], stock_status: "sold_out", stock_count: 0 },
                ],
              },
              // block 4: time_slots 时段列表（门票场次示例）
              {
                type: "time_slots",
                title: "场次",
                items: [
                  { name: "上午场", time: "09:00-12:00", stock_status: "sufficient", stock_count: 50 },
                  { name: "下午场", time: "14:00-17:00", stock_status: "tight", stock_count: 8 },
                  { name: "夜场", time: "19:00-22:00", stock_status: "sold_out", stock_count: 0 },
                ],
              },
              // block 5: calendar 日历概览（门票日历示例，含本月若干日期）
              {
                type: "calendar",
                title: "选择日期",
                config: { mode: "single", show_stock: true },
                dates: buildMockCalendarDates(),
              },
              // block 6: policy 政策键值对（酒店入住政策示例）
              {
                type: "policy",
                title: "购买须知",
                items: [
                  { label: "入住", value: "14:00后" },
                  { label: "退房", value: "12:00前" },
                  { label: "最短连住", value: "1晚" },
                  { label: "限购", value: "每账号每日限购5张" },
                  { label: "库存说明", value: "库存实时变化，以下单为准" },
                ],
              },
            ],
          },
        },
        // 2. info_card 万能信息卡（配送信息示例）
        {
          key: "fulfillment",
          component: "info_card",
          title: "配送",
          visible: true,
          order: 20,
          data: {
            items: [
              { label: "配送方式", value: "快递配送 / 到店自提", icon: "express" },
              { label: "发货时效", value: "48小时内", icon: "clock" },
              { label: "运费", value: "包邮", icon: "truck" },
              {
                label: "自提点",
                value: "中关村旗舰店",
                icon: "location",
                action: { type: "map", lat: 39.98, lng: 116.31, name: "中关村旗舰店" },
              },
            ],
          },
        },
        // 3. store_card 门店/场馆卡（自提门店示例）
        {
          key: "store",
          component: "store_card",
          title: "自提门店",
          visible: true,
          order: 30,
          data: {
            name: "中关村旗舰店",
            address: "北京市海淀区中关村大街1号海龙大厦1层",
            distance: "1.2km",
            lat: 39.9847,
            lng: 116.3074,
            store_type: "pickup",
            store_type_label: "自提店",
            phone: "010-12345678",
          },
        },
        // 4. time_card 时间线（预售期/使用期示例）
        {
          key: "validity",
          component: "time_card",
          title: "有效期",
          visible: true,
          order: 40,
          data: {
            periods: [
              { label: "售卖期", time: "2026-07-01 ~ 2026-08-14", status: "active", statusText: "进行中" },
              { label: "使用期", time: "2026-08-15 当天", status: "pending", statusText: "未开始" },
              { label: "退改截止", time: "2026-08-14 23:59", status: "pending" },
            ],
          },
        },
]);
// ===== mock 数据结束 =====
const diyData = ref({});
const imgHost = HTTP_REQUEST_URL;
const noGoods = ref(false);
const showSkeleton = ref(true); //骨架屏显示隐藏
const isNodes = ref(0); //控制什么时候开始抓取元素节点,只要数值改变就重新抓取
const Active = ref(false);
const presale_pay_status = ref(1);
//属性是否打开
const coupon = reactive({
  coupon: false,
  type: -1,
  list: [],
  count: [],
});
const showAnimate = ref(false);
const showMenuIcon = ref(false);
const attrTxt = ref(i18n.t(`请选择`)); //属性页面提示
const attrValue = ref(""); //已选属性
const animated = ref(false); //购物车动画
const id = ref(0); //商品id
const replyCount = ref(0); //总评论数量
const reply = ref([]); //评论列表
const storeInfo = ref({}); //商品详情
const productValue = ref([]); //系统属性
const couponList = ref([]); //优惠券
const cart_num = ref(1); //购买数量
const isAuto = ref(false); //没有授权的不会自动授权
const isShowAuth = ref(false); //是否隐藏授权
const isOpen = ref(false); //是否打开属性组件
const posterbackgd = ref("/static/images/posterbackgd.png");
const sharePacket = reactive({
  isState: true, //默认不显示
}); //分销商详细
const uid = ref(0); //用户uid
const good_list = ref([]);
const replyChance = ref(0);
const CartCount = ref(0);
const ot_price = ref(0);
const storeSelfMention = ref(true);
const weixinStatus = ref(false);
const attr = reactive({
  cartAttr: false,
  productAttr: [],
  productSelect: {},
});
const description = ref("");
const H5ShareBox = ref(false); //公众号分享图片
const activity = ref([]);
const navH = ref("");
const opacity = ref(0);
const scrollY = ref(0);
const returnShow = ref(true); //判断顶部返回是否出现
const diff = ref("");
const is_money_level = ref(1);
const is_vip = ref(0); //是否是会员
const navbarRight = ref(0);
const homeTop = ref(20);
const menuPositionStyle = ref({});
const routineContact = ref(0);
const skuArr = ref([]);
const svip_price_open = ref(1);
const is_gift = ref(0); // 是否支持送礼
const isGiftOrder = ref(0);
const realPriceData = ref({
  is_vip: 0,
  price: 0,
  real_price: 0,
});
// 后端 display_sections 数据（产品类型/营销监听器注入的展示块）
// 透传给 PageDesign，驱动 infoCard/storeCard/timeCard/specPreview 4 个 DIY 组件渲染
const displaySections = ref([]);
// display_sections block 选中数据（票种/场次/日期），跳转下单页时携带
// 结构：[{type: 'price_list'|'time_slots'|'calendar', item: {...}}]
const selectedBlockData = ref([]);
// readOnly 弹窗模式下存储第一次进入 goCat 时的 news 参数，供"确定"按钮复用
// 加入购物车=undefined，立即购买=true
const pendingNews = ref(undefined);

// ---------- 海报/分销二维码（替代原 mixins/sharePoster.js）----------
// 原 mixin 写到页面 this 上的字段（posters/PromotionCode/posterImage/...）全部解构自 poster；
// 商品详情无秒杀场次，timeId 保持空
const poster = useSharePoster(() => ({
  id: id.value, //商品id
  timeId: "",
  storeInfo: storeInfo.value,
  posterbackgd: posterbackgd.value,
}));
const {
  posters,
  canvasStatus,
  PromotionCode,
  posterImage,
  posterImageStatus,
  actionSheetHidden,
  storeImage,
  storeImageBase64,
  // 二维码组件参数
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
  onval,
  loadMake,
  shareQrcode,
  followCode,
  selectSku,
  currentPage,
  sysHeight,
  // 方法
  qrR,
  getImageBase64,
  goPoster,
  downloadFilestoreImage,
  downloadFilePromotionCode,
} = poster;

// ---------- 模板 ref（与 import 组件同名冲突的已改名：cusPreviewImgRef/specsRef）----------
const cusPreviewImgRef = ref(null);
const cusSwiperImg = ref(null);
const specsRef = ref(null);
const protection = ref(null);
// PageDesign/homeProductService emit openModal 的 type：'specs' | 'protection'
const modalRefs = {
  specs: specsRef,
  protection: protection,
};

// ---------- computed ----------
const isShowPaidVip = computed(() => {
  let s =
    !is_money_level.value &&
    storeInfo.value.vip_price &&
    storeInfo.value.is_vip;
  return !!s;
});
/**
 * 加购模式：cart（双按钮）| direct（单按钮直接下单）
 * 缺失时 fallback 到 cart，保证 normal 商品现状不变
 * @return {string}
 */
const cartMode = computed(() => {
  return storeInfo.value.cart_strategy?.cart_mode || "cart";
});
/**
 * 确认弹窗模式：editable（可编辑）| readonly（只读回显）| none（无弹窗）
 * 缺失时 fallback 到 editable，保证 normal 商品现状不变
 * @return {string}
 */
const confirmWindow = computed(() => {
  return storeInfo.value.selection_spec?.confirm_window || "editable";
});
/**
 * 是否只读规格弹窗（门票/卡密/核销券等 confirm_window=readonly 类型）
 * @return {boolean}
 */
const isReadOnlyAttr = computed(() => {
  return confirmWindow.value === "readonly";
});
/**
 * 是否直接下单模式（预售/租赁/服务等 cart_mode=direct 类型）
 * @return {boolean}
 */
const isDirectMode = computed(() => {
  return cartMode.value === "direct";
});

// ---------- watch ----------
watch(
  isLogin,
  function (newV, oldV) {
    if (newV == true) {
      getCouponList();
      getCartCount();
      downloadFilePromotionCode();
      // this.ShareInfo();
    }
  },
  {
    deep: true,
  }
);
watch(
  storeInfo,
  function () {
    nextTick(() => {});
  },
  { immediate: true }
);

// ---------- 生命周期 ----------
onLoad((options) => {
  var pages = getCurrentPages();
  returnShow.value = pages.length === 1 ? false : true;
  // #ifdef MP
  navH.value = app.globalData.navHeight;
  // #endif
  // #ifdef H5
  navH.value = 76;
  // #endif
  // #ifdef APP-PLUS
  navH.value = 30;
  // #endif
  id.value = options.id;
  uni.getSystemInfo({
    success: function (res) {
      //res.windowHeight:获取整个窗口高度为px，*2为rpx；98为头部占据的高度；
      // #ifndef APP-PLUS || H5 || MP-ALIPAY
      navbarRight.value =
        res.windowWidth - uni.getMenuButtonBoundingClientRect().left;
      // #endif
    },
  });
  //扫码携带参数处理
  // #ifdef MP
  if (options.scene) {
    let value = util.getUrlParams(decodeURIComponent(options.scene));
    if (value.id) options.id = value.id;
    //记录推广人uid
    if (value.pid) app.globalData.spid = value.pid;
  }
  if (!options.id) {
    showSkeleton.value = false;
    return util.Tips(
      {
        title: i18n.t(`缺少参数无法查看商品`),
      },
      {
        tab: 3,
        url: 1,
      }
    );
  } else {
    id.value = options.id;
  }
  // #endif
  getGoodsDetails();
  getDiyData();
});
onReady(() => {
  isNodes.value++;
  nextTick(function () {
    // #ifdef MP
    const menuButton = uni.getMenuButtonBoundingClientRect();
    const query = uni.createSelectorQuery().in(instance && instance.proxy);
    query
      .select("#home")
      .boundingClientRect((data) => {
        if (!data) return;
        const windowWidth = uni.getSystemInfoSync().windowWidth;
        homeTop.value = (menuButton.top + (menuButton.height - data.height) / 2) * 750 / windowWidth;
      })
      .exec();
    // #endif
    // #ifdef H5
    const clipboard = new ClipboardJS(".copy-data");
    clipboard.on("success", () => {
      util.Tips({
        title: i18n.t(`复制成功`),
      });
    });
    // #endif
  });
});
/**
 * 用户点击右上角分享
 */
// #ifdef MP
onShareAppMessage(function () {
  actionSheetHidden.value = !actionSheetHidden.value;
  userShare();
  return {
    title: storeInfo.value.store_name || "",
    imageUrl: storeInfo.value.image || "",
    path: "/pages/goods_details/index?id=" + id.value + "&spid=" + uid.value,
  };
});
onShareTimeline(() => {
  userShare();
  return {
    title: storeInfo.value.store_name,
    query: {
      id: id.value,
      spid: uid.value || 0,
    },
    imageUrl: storeInfo.value.image,
  };
});
// #endif
onNavigationBarButtonTap((e) => {
  currentPage.value = !currentPage.value;
});
onPageScroll((e) => {
  let scrollYPos = e.scrollTop;
  let opacityVal = scrollYPos / 200;
  opacityVal = opacityVal > 1 ? 1 : opacityVal;
  opacity.value = opacityVal;
  scrollY.value = scrollYPos;
  showAnimate.value = false;
  showMenuIcon.value = false;
  currentPage.value = false;
  uni.$emit("scroll");
});

// ---------- methods ----------
// 操作菜单
function moreNav() {
  if (currentPage.value) {
    currentPage.value = false;
    return;
  }
  let anchorSelector = '#home';
  // #ifdef H5
  anchorSelector = '#goods-menu-anchor';
  // #endif
  uni.createSelectorQuery().in(instance && instance.proxy)
    .select(anchorSelector)
    .boundingClientRect((rect) => {
      if (!rect) return;
      const position = {
        position: 'fixed',
        top: rect.bottom + uni.upx2px(30) + 'px',
        left: rect.left + 'px',
        right: 'auto',
        marginTop: '0px',
      };
      // #ifdef H5
      position.left = 'auto';
      position.right = uni.getSystemInfoSync().windowWidth - rect.right + 'px';
      // #endif
      menuPositionStyle.value = position;
      currentPage.value = true;
    })
    .exec();
}
// Vue2 时期 methods 中 onChangeSpecFromPageDesign / onShowSpecModalFromPageDesign
// 各定义了两次（对象字面量同名键后者覆盖前者），此处保留运行时实际生效的后一份定义：
// - onChangeSpecFromPageDesign 两份函数体完全一致，合并为一份
// - onShowSpecModalFromPageDesign 后一份委托 selecAttr()，与前一份直接赋值逻辑等价
function onChangeSpecFromPageDesign(item) {
  if (item && item.suk) {
    let values = item.suk.split(",");
    if (
      attr.productAttr &&
      attr.productAttr.length === values.length
    ) {
      for (let i = 0; i < attr.productAttr.length; i++) {
        attr.productAttr[i]["index"] = values[i];
      }
    }
    ChangeAttr(item.suk);
  }
}
function onShowSpecModalFromPageDesign() {
  selecAttr();
}
function getDiyData() {
  let previewThemeId = uni.getStorageSync("previewThemeId");
  let data = {};
  if (previewThemeId) data.theme_id = previewThemeId;
  getThemeInfo("detail", data).then((res) => {
    diyData.value = res.data || {};
  });
}
function jumpUrl(url) {
  uni.switchTab({
    url,
  });
}
function videoPause() {
  // this.$nextTick(() => {
  //   this.infoScroll();
  // });
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
    href: `${HTTP_REQUEST_URL}${curRoute}&spread=${uid.value}`,
    title: storeInfo.value.store_name,
    summary: storeInfo.value.store_info,
    imageUrl: storeInfo.value.small_image,
    success: function (res) {
      // uni.showToast({
      // 	title: that.$t(`分享成功`),
      // 	icon: "success",
      // });
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
function closeChange() {
  sharePacket["isState"] = true;
}
function boxStatus(data) {
  showAnimate.value = data;
}
/**
 * 购物车手动填写
 *
 */
function iptCartNum(e) {
  if (e) {
    let number = storeInfo.value.min_qty;
    if (
      Number.isInteger(parseInt(e)) &&
      parseInt(e) >= storeInfo.value.min_qty
    ) {
      number = parseInt(e);
    }
    nextTick((e) => {
      attr.productSelect["cart_num"] = e < 0 ? storeInfo.value.min_qty : number;
    });
  }
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
/*
 *去商品详情页
 */
function goDetail(item) {
  if (item.activity.length == 0) {
    uni.redirectTo({
      url: "/pages/goods_details/index?id=" + item.id,
    });
    return;
  }
  // 砍价
  if (item.activity && item.activity.type == 2) {
    uni.redirectTo({
      url: `/pages/activity/goods_bargain_details/index?id=${item.activity.id}&bargain=${uid.value}`,
    });
    return;
  }
  // 拼团
  if (item.activity && item.activity.type == 3) {
    uni.redirectTo({
      url: `/pages/activity/goods_combination_details/index?id=${item.activity.product_id || item.activity.id}${item.activity.product_id ? '&activity_id=' + item.activity.id : ''}`,
    });
    return;
  }
  // 秒杀
  if (item.activity && item.activity.type == 1) {
    uni.redirectTo({
      url: `/pages/activity/goods_seckill_details/index?id=${item.activity.id}&time_id=${item.activity.time_id}`,
    });
    return;
  }
}
// 微信登录回调
function onLoadFun(e) {
  // this.getUserInfo();
  // this.get_product_collect();
}
function ChangCouponsClone() {
  coupon["coupon"] = false;
}
/*
 * 获取用户信息
 */
function getUserInfo() {
  getUserInfoApi().then((res) => {
    uid.value = res.data.uid;
    is_money_level.value = res.data.is_money_level;
  });
}
/**
 * 购物车数量加和数量减
 *
 */
function ChangeCartNum(changeValue) {
  //changeValue:是否 加|减
  //获取当前变动属性
  let productSelect = productValue.value[attrValue.value];
  //如果没有属性,赋值给商品默认库存
  // readonly 模式（门票/卡密等 confirm_window=readonly 类型）下 productValue 为空，
  // 但 syncAttrFromBlockData 已构造伪 productAttr 数组（长度非 0），
  // 因此需同时判断 isReadOnlyAttr，否则 productSelect 保持 undefined，
  // 后续 productSelect.stock 访问会抛错中断，导致数量加减按钮失效
  if (
    productSelect === undefined &&
    (!attr.productAttr.length || isReadOnlyAttr.value)
  )
    productSelect = attr.productSelect;
  //无属性值即库存为0；不存在加减；
  let stock = (productSelect && productSelect.stock) || 0;
  let num = attr.productSelect;
  if (
    productSelect === undefined ||
    (storeInfo.value.limit_num &&
      num.cart_num >= storeInfo.value.limit_num &&
      changeValue)
  )
    return;
  if (changeValue) {
    num.cart_num++;
    if (num.cart_num > stock) {
      attr.productSelect["cart_num"] = stock ? stock : storeInfo.value.min_qty;
      cart_num.value = stock ? stock : 1;
    }
  } else {
    num.cart_num--;
    if (num.cart_num < 1) {
      attr.productSelect["cart_num"] = storeInfo.value.min_qty;
      cart_num.value = 1;
    }
  }
}
function attrVal(val) {
  attr.productAttr[val.indexw]["index"] = attr.productAttr[val.indexw].attr_values[val.indexn];
}
/**
 * 属性变动赋值
 *
 */
function ChangeAttr(res) {
  let productSelect = productValue.value[res];
  if (!productSelect) {
    util.Tips({
      title: i18n.t(`重新选择`),
      success: () => {
        noGoods.value = true;
        attr.productSelect.stock = 0;
        attr.productSelect.quota = 0;
        attr.productSelect.cart_num = 0;
      },
    });
  } else {
    noGoods.value = false;
  }
  selectSku.value = productSelect;
  if (productSelect && productSelect.stock > 0) {
    attr.productSelect["image"] = productSelect.image;
    // this.attr.productSelect['price'] = productSelect.price;
    attr.productSelect["stock"] = productSelect.stock;
    attr.productSelect["unique"] = productSelect.unique;
    attr.productSelect["cart_num"] = storeInfo.value.min_qty;
    // this.attr.productSelect['vip_price'] = productSelect.vip_price;
    attrValue.value = res;
    attrTxt.value = i18n.t(`已选择`);
    setRealPrice(storeInfo.value.id, productSelect.unique);
  } else {
    attr.productSelect["image"] = productSelect.image;
    attr.productSelect["price"] = productSelect.price;
    attr.productSelect["stock"] = 0;
    attr.productSelect["unique"] = "";
    attr.productSelect["cart_num"] = 0;
    attr.productSelect["vip_price"] = storeInfo.value.vip_price;
    attrValue.value = "";
    attrTxt.value = i18n.t(`请选择`);
  }
}
function setRealPrice(productId, unique) {
  realPrice(productId, unique).then((res) => {
    realPriceData.value = res.data;
    attr.productSelect["price"] = res.data.real_price;
    attr.productSelect["vip_price"] = res.data.member_price;
    ot_price.value = res.data.ot_price;
  });
}
/**
 * 领取完毕移除当前页面领取过的优惠券展示
 */
function ChangCoupons(e) {
  let couponItem = e;
  let newCouponList = util.ArrayRemove(couponList.value, "id", couponItem.id);
  couponList.value = newCouponList;
  getCouponList();
}

/**
 * 获取产品详情
 *
 */
function getGoodsDetails() {
  uni.showLoading({
    title: i18n.t(`加载中`),
    mask: true,
  });
  getProductDetail(id.value)
    .then((res) => {
      uni.hideLoading();
      let storeInfoRes = res.data.storeInfo;
      let goodList = res.data.good_list || [];
      is_gift.value = res.data.storeInfo.is_gift;
      storeInfo.value = storeInfoRes;
      presale_pay_status.value = res.data.storeInfo.presale_pay_status; // 1未开始; 2进行中; 3已结束
      reply.value = res.data.reply ? [res.data.reply] : [];
      replyCount.value = res.data.replyCount;
      replyChance.value = res.data.replyChance;
      attr["productAttr"] = res.data.productAttr;
      productValue.value = res.data.productValue;
      is_vip.value = res.data.storeInfo.is_vip;
      sharePacket["priceName"] = res.data.priceName;
      sharePacket["isState"] = res.data.priceName != 0 ? true : false;
      storeSelfMention.value = res.data.store_self_mention;
      good_list.value = goodList;
      // 透传后端 display_sections（产品类型/营销监听器输出的展示块）
      // 驱动 PageDesign 中 infoCard/storeCard/timeCard/specPreview 4 个 DIY 组件
      // 本地调试：useMockDisplaySections=true 时强制用 mock 数据，验证渲染效果
      const sectionsData = useMockDisplaySections.value
        ? mockDisplaySections.value
        : res.data.display_sections || [];
      displaySections.value = sectionsData;
      // 调试日志：方便在控制台观察实际生效的 sections 数据
      // #ifdef H5 || APP-PLUS
      console.log(
        "[display_sections]",
        useMockDisplaySections.value ? "[MOCK]" : "[API]",
        sectionsData
      );
      // #endif

      if (!storeInfoRes.wechat_code) {
        // #ifdef H5
        codeVal.value =
          window.location.origin +
          "/pages/goods_details/index?id=" +
          id.value +
          "&spid=" +
          appStore.uid;
        // #endif
        // #ifdef APP-PLUS
        codeVal.value =
          HTTP_REQUEST_URL +
          "/pages/goods_details/index?id=" +
          id.value +
          "&spid=" +
          appStore.uid;
        // #endif
      } else {
        PromotionCode.value = storeInfoRes.wechat_code;
      }
      activity.value = res.data.activity ? res.data.activity : [];
      couponList.value = res.data.coupons;
      routineContact.value = Number(res.data.routine_contact_type);
      uni.setNavigationBarTitle({
        title: storeInfoRes.store_name.substring(0, 7) + "...",
      });
      for (let key in res.data.productValue) {
        let obj = res.data.productValue[key];
        skuArr.value.push(obj);
      }
      selectSku.value = skuArr.value[0];
      diff.value = util.$h.Sub(storeInfoRes.price, storeInfoRes.vip_price);
      storeImage.value = storeInfoRes.image;
      svip_price_open.value = res.data.svip_price_open;
      if (isLogin.value) {
        getUserInfo();
      }
      // #ifdef H5 || APP-PLUS
      getImageBase64();
      // #endif
      // #ifdef H5
      if (isLogin.value) {
        ShareInfo();
      }
      // #endif
      nextTick(() => {
        if (goodList.length) {
          // #ifndef APP-PLUS

          // #endif
          // #ifdef APP-PLUS

          setTimeout(() => {}, 1000);
          // #endif
        }
      });
      // #ifndef H5
      downloadFilestoreImage();
      // #endif
      if (!res.data.productAttr.length) {
        // 单规格
        DefaultSelect();
        setRealPrice(storeInfoRes.id, res.data.spec_unique);
      } else {
        // 多规格
        DefaultSelect();
      }
      getCartCount();
      showAnimate.value = true;
    })
    .catch((err) => {
      uni.hideLoading();
      //状态异常返回上级页面
      return util.Tips(
        {
          title: err.toString(),
        },
        {
          tab: 3,
          url: 1,
        }
      );
    });
}
function infoScroll() {
  return;
}
/**
 * 默认选中属性
 *
 */
function DefaultSelect() {
  let productAttr = attr.productAttr;
  let value = [];
  if (storeInfo.value.default_sku) {
    value = storeInfo.value.default_sku.split(",");
  } else {
    for (var key in productValue.value) {
      if (productValue.value[key].stock > 0) {
        value = attr.productAttr.length ? key.split(",") : [];
        break;
      }
    }
  }
  for (let i = 0; i < productAttr.length; i++) {
    productAttr[i]["index"] = value[i];
  }
  //sort();排序函数:数字-英文-汉字；
  let productSelect = productValue.value[value.join(",")];
  if (productSelect && productAttr.length) {
    attr.productSelect["store_name"] = storeInfo.value.store_name;
    attr.productSelect["image"] = productSelect.image;
    // this.attr.productSelect['price'] = productSelect.price;
    attr.productSelect["stock"] = productSelect.stock;
    attr.productSelect["unique"] = productSelect.unique;
    attr.productSelect["cart_num"] = storeInfo.value.min_qty;
    attrValue.value = value.join(",");
    // this.attr.productSelect['vip_price'] = productSelect.vip_price;
    attrTxt.value = i18n.t(`已选择`);
    setRealPrice(storeInfo.value.id, productSelect.unique);
  } else if (!productSelect && productAttr.length) {
    attr.productSelect["store_name"] = storeInfo.value.store_name;
    attr.productSelect["image"] = storeInfo.value.image;
    attr.productSelect["price"] = storeInfo.value.price;
    attr.productSelect["stock"] = 0;
    attr.productSelect["unique"] = "";
    attr.productSelect["cart_num"] = 0;
    attr.productSelect["vip_price"] = storeInfo.value.vip_price;
    attrValue.value = "";
    attrTxt.value = i18n.t(`请选择`);
  } else if (!productSelect && !productAttr.length) {
    attr.productSelect["store_name"] = storeInfo.value.store_name;
    attr.productSelect["image"] = storeInfo.value.image;
    attr.productSelect["price"] = storeInfo.value.price;
    // readOnly 模式（门票/卡密等 confirm_window=readonly）：
    // 库存按日期×场次×票种三维管理，中台主表 stock 不可靠（常为 0）
    // 给默认非 0 值避免页面底部 productBottom 误判"已售罄"导致用户无法点击进入弹窗
    // 用户选完日期/场次/票种后，syncAttrFromBlockData 会基于 calendar sku_detail 精确更新
    attr.productSelect["stock"] = isReadOnlyAttr.value ? 99 : storeInfo.value.stock;
    attr.productSelect["unique"] = storeInfo.value.unique || "";
    attr.productSelect["cart_num"] = storeInfo.value.min_qty;
    attr.productSelect["vip_price"] = storeInfo.value.vip_price;
    attrValue.value = "";
    attrTxt.value = i18n.t(`请选择`);
  }
}
/**
 * 获取优惠券
 *
 */
function getCouponList(type) {
  let obj = {
    page: 1,
    limit: 20,
    product_id: id.value,
  };
  if (type !== undefined || type !== null) {
    obj.type = type;
  }
  getCoupons(obj).then((res) => {
    coupon["count"] = res.data.count;
    if (type === undefined || type === null) {
      let count = [...coupon.count],
        indexs = "";
      let index = count.findIndex((item) => item);
      let delCount = coupon.count,
        newDelCount = [];
      let countIndex = 0;
      delCount.forEach((item, index) => {
        if (item === 0) {
          countIndex = index;
        } else {
          newDelCount.push(item);
        }
      });
      if (newDelCount.length == 3) {
        indexs = 2;
      } else if (newDelCount.length == 2) {
        if (countIndex === 2) {
          indexs = 1;
        } else {
          indexs = 2;
        }
      } else {
        indexs = delCount.findIndex((item) => item === count[index]);
      }
      coupon["type"] = indexs;
      getCouponList(indexs);
    } else {
      coupon["list"] = res.data.list;
    }
  });
}
function ChangCouponsUseState(index) {
  coupon.list[index].is_use++;
  // coupon["list"] = coupon.list;
  coupon["coupon"] = false;
}
/**
 *
 *
 * 收藏商品
 */
function setCollect() {
  if (isLogin.value === false) {
    toLogin();
  } else {
    if (storeInfo.value.userCollect) {
      collectDel([storeInfo.value.id]).then((res) => {
        storeInfo.value["userCollect"] = !storeInfo.value.userCollect;
        return util.Tips({
          title: res.msg,
        });
      });
    } else {
      collectAdd(storeInfo.value.id).then((res) => {
        storeInfo.value["userCollect"] = !storeInfo.value.userCollect;
        return util.Tips({
          title: res.msg,
        });
      });
    }
  }
}
/**
 * display_sections block 选中回调（票种/场次/日期）
 * 收集选中数据到 selectedBlockData，goCat 跳转下单页时携带
 * 同 type 的选中项会覆盖（如重新选择票种），不同 type 累加（如票种+场次+日期）
 * @param {Object} payload {type: 'price_list'|'time_slots'|'calendar', item: {...}}
 */
function onBlockSelect(payload) {
  if (!payload || !payload.type) return;
  // 同 type 覆盖，不同 type 累加
  const idx = selectedBlockData.value.findIndex(
    (d) => d.type === payload.type
  );
  if (idx >= 0) {
    selectedBlockData.value[idx] = payload;
  } else {
    selectedBlockData.value.push(payload);
  }

  // 调试日志
  // #ifdef H5 || APP-PLUS
  console.log("[blockSelect] 选中数据", selectedBlockData.value);
  // #endif
}
/**
 * 打开属性插件
 */
function selecAttr() {
  // this.$refs.proSwiper.videoIsPause();
  attr["cartAttr"] = true;
  isOpen.value = true;
}
function openModal(refName) {
  modalRefs[refName].value.isShow = true;
}
/**
 * 打开优惠券插件
 */
function couponTap() {
  if (isLogin.value === false) {
    toLogin();
  } else {
    // this.$refs.proSwiper.videoIsPause();
    getCouponList();
    coupon["coupon"] = true;
  }
}
function goActivity(item) {
  if (item.type === "1" && ActivePermission("seckill")) {
    uni.navigateTo({
      url: `/pages/activity/goods_seckill_details/index?id=${item.id}&time_id=${item.time_id}`,
    });
  } else if (item.type === "2" && ActivePermission("bargain")) {
    uni.navigateTo({
      url: `/pages/activity/goods_bargain_details/index?id=${item.id}&bargain=${uid.value}`,
    });
  } else if (item.type === "3" && ActivePermission("combination")) {
    uni.navigateTo({
      url: `/pages/activity/goods_combination_details/index?id=${item.product_id || item.id}${item.product_id ? '&activity_id=' + item.id : ''}`,
    });
  }
}
function onMyEvent() {
  attr["cartAttr"] = false;
  isOpen.value = false;
  isGiftOrder.value = 0;
}
/**
 * 打开属性加入购物车
 *
 */
function joinCart(e) {
  //是否登录
  if (isLogin.value === false) {
    toLogin();
  } else {
    // this.$refs.proSwiper.videoIsPause();
    goCat();
  }
}
function goCart() {
  uni.reLaunch({
    url: "/pages/order_addcart/order_addcart",
  });
}
/*
 * 加入购物车
 */
function goCat(news) {
  // readonly 分支（门票/卡密/核销券等 confirm_window=readonly 类型）
  // 只读规格弹窗：第一次进入校验并打开弹窗，第二次进入（用户点确定）执行下单
  if (isReadOnlyAttr.value) {
    // 第二次进入（用户点确定后 isOpen=true）：执行下单
    if (isOpen.value) {
      isOpen.value = false;
      currentPage.value = false;
      const uniqueId = (attr.productSelect && attr.productSelect.unique) || '';
      const cartNumVal = (attr.productSelect && attr.productSelect.cart_num) || 1;
      const params = buildCartParams(uniqueId, cartNumVal, storeInfo.value.virtual_type);
      params.new = news === undefined ? 0 : 1;
      submitCartAdd(params, news);
      pendingNews.value = undefined;
      return;
    }
    // 第一次进入：校验必选项
    const requiredSelections = storeInfo.value.selection_spec?.required_selections || ['sku'];
    if (!validateSelections(requiredSelections)) return;
    // 存储本次 goCat 的 news 参数，供弹窗"确定"按钮复用
    pendingNews.value = news;
    // 预置 attr 高亮已选值并打开弹窗
    syncAttrFromBlockData();
    attr['cartAttr'] = true;
    isOpen.value = true;
    currentPage.value = true;
    return;
  }

  let productSelect = productValue.value[attrValue.value];
  currentPage.value = false;
  //打开属性
  if (attrValue.value) {
    //默认选中了属性，但是没有打开过属性弹窗还是自动打开让用户查看默认选中的属性
    attr.cartAttr = !isOpen.value ? true : false;
  } else {
    if (isOpen.value) attr.cartAttr = true;
    else attr.cartAttr = !attr.cartAttr;
  }
  //只有关闭属性弹窗时进行加入购物车
  if (attr.cartAttr === true && isOpen.value === false)
    return (isOpen.value = true);
  //如果有属性,没有选择,提示用户选择
  if (
    attr.productAttr.length &&
    productSelect === undefined &&
    isOpen.value === true
  )
    return util.Tips({
      title: i18n.t(`产品库存不足，请选择其它属性`),
    });
  if (attr.productSelect.cart_num <= 0) {
    attr.productSelect.cart_num = 1;
    isOpen.value = false;
    return util.Tips({
      title: i18n.t(`请选择数量`),
    });
  }
  let q = {
    productId: id.value,
    cartNum: attr.productSelect.cart_num,
    new: news === undefined ? 0 : 1,
    uniqueId:
      attr.productSelect !== undefined
        ? attr.productSelect.unique
        : "",
    virtual_type: storeInfo.value.virtual_type,
  };
  postCartAdd(q)
    .then((res) => {
      isOpen.value = false;
      attr.cartAttr = false;
      if (news) {
        let url =
          "/pages/center/order/create/index?new=1&cartId=" +
          res.data.cartId;
        if (isGiftOrder.value) url += "&is_gift=" + isGiftOrder.value;
        // 携带 display_sections block 选中数据（票种/场次/日期）到下单页
        // 下单页通过 onLoad 的 options.block_data 解码 JSON 后使用
        if (selectedBlockData.value && selectedBlockData.value.length) {
          url +=
            "&block_data=" +
            encodeURIComponent(JSON.stringify(selectedBlockData.value));
        }
        uni.navigateTo({
          url,
        });
      } else {
        util.Tips({
          title: i18n.t(`添加成功`),
          success: () => {
            getCartCount(true);
          },
        });
      }
      isGiftOrder.value = 0;
    })
    .catch((err) => {
      isOpen.value = false;
      return util.Tips({
        title: err,
      });
    });
}
/**
 * 构造加购请求参数
 * @param {string} uniqueId SKU 唯一编码
 * @param {number} cartNumArg 加购数量
 * @param {*} virtual_type 虚拟类型
 * @return {Object} postCartAdd 请求参数
 */
function buildCartParams(uniqueId, cartNumArg, virtual_type) {
  return {
    productId: id.value,
    cartNum: cartNumArg,
    new: 0,
    uniqueId: uniqueId,
    virtual_type: virtual_type,
  };
}
/**
 * 跳转下单页，携带 cartId 和 block_data
 * @param {number|string} cartId 购物车ID
 * @param {Array} blockData display_sections 选中数据
 */
function navigateToCreate(cartId, blockData) {
  let url = "/pages/center/order/create/index?new=1&cartId=" + cartId;
  if (isGiftOrder.value) url += "&is_gift=" + isGiftOrder.value;
  // 携带 display_sections block 选中数据（票种/场次/日期等）到下单页
  if (blockData && blockData.length) {
    url += "&block_data=" + encodeURIComponent(JSON.stringify(blockData));
  }
  uni.navigateTo({ url });
}
/**
 * 调用加购接口并处理成功/失败回调
 * @param {Object} params 加购请求参数
 * @param {*} news 是否立即购买（truthy 跳下单页，falsy 提示添加成功）
 */
function submitCartAdd(params, news) {
  postCartAdd(params)
    .then((res) => {
      isOpen.value = false;
      attr.cartAttr = false;
      if (news) {
        navigateToCreate(res.data.cartId, selectedBlockData.value);
      } else {
        util.Tips({
          title: i18n.t(`添加成功`),
          success: () => {
            getCartCount(true);
          },
        });
      }
      isGiftOrder.value = 0;
    })
    .catch((err) => {
      isOpen.value = false;
      return util.Tips({ title: err });
    });
}
/**
 * 校验 selectedBlockData 是否覆盖 selection_spec.required_selections
 * @param {Array} requiredSelections 必选项 key 列表
 * @return {boolean} 校验通过返回 true
 */
function validateSelections(requiredSelections) {
  if (!requiredSelections || !requiredSelections.length) return true;
  if (!selectedBlockData.value || !selectedBlockData.value.length) {
    util.Tips({ title: i18n.t('请先选择') });
    return false;
  }
  const selectedTypes = selectedBlockData.value.map((d) => d.type);
  const tipMap = {
    calendar: '请选择日期',
    time_slots: '请选择场次',
    price_list: '请选择票种',
    staff: '请选择服务人员',
    sku: '请选择规格',
  };
  for (const req of requiredSelections) {
    if (req === 'sku') continue; // SKU 由默认选择或规格弹窗处理
    if (!selectedTypes.includes(req)) {
      util.Tips({ title: i18n.t(tipMap[req] || '请选择') });
      return false;
    }
  }
  return true;
}
/**
 * productWindow"确定"按钮回调（readOnly 模式）
 * 复用 goCat 第二次进入逻辑，传入第一次存储的 pendingNews（加入购物车/立即购买）
 */
function onProductWindowConfirm() {
  goCat(pendingNews.value);
}
/**
 * 从 selectedBlockData 同步更新 attr.productAttr 的 index 高亮和 productSelect
 * 用于 readonly 弹窗打开前预置已选值，保证只读高亮正确
 */
function syncAttrFromBlockData() {
  if (!selectedBlockData.value || !selectedBlockData.value.length) return;

  // 收集 calendar / time_slots / price_list 三类 block 的选中项
  let calendarItem = null;
  let timeSlotsItem = null;
  let priceListItem = null;
  for (const block of selectedBlockData.value) {
    if (!block || !block.item) continue;
    if (block.type === 'calendar') calendarItem = block.item;
    else if (block.type === 'time_slots') timeSlotsItem = block.item;
    else if (block.type === 'price_list') priceListItem = block.item;
  }
  // === 调试日志 ===
  // console.log('[syncAttr DEBUG] selectedBlockData:', JSON.stringify(this.selectedBlockData));
  // console.log('[syncAttr DEBUG] priceListItem:', priceListItem ? JSON.stringify(priceListItem) : 'null');
  // console.log('[syncAttr DEBUG] calendarItem:', calendarItem ? JSON.stringify(calendarItem) : 'null');
  // console.log('[syncAttr DEBUG] isReadOnlyAttr:', isReadOnlyAttr.value);
  // console.log('[syncAttr DEBUG] ps.stock before:', attr.productSelect && attr.productSelect.stock);

  // 选择型业态 unique 拼接：{date}_{sessionId}_{skuId}（如 2026-08-10_4_5）
  // 与后端 Handler::getSkuInfo 解析约定一致，
  // 使购物车列表 handleCartList 能通过 getSkuInfo 还原 suk 文本"日期 场次 票种"
  // 缺失场次时 sessionId 用 0 占位；缺失日期时回退到票种 id 作为 unique
  if (priceListItem) {
    if (!attr.productSelect) attr['productSelect'] = {};
    const skuId = priceListItem.id || '';
    if (calendarItem && calendarItem.date && skuId) {
      const sessionId = (timeSlotsItem && timeSlotsItem.id) ? timeSlotsItem.id : 0;
      const visitDate = calendarItem.date;
      attr.productSelect['unique'] = `${visitDate}_${sessionId}_${skuId}`;
    } else {
      // 仅选票种（无日期/场次）：用票种 id 作为 unique，保留原行为
      const unique = priceListItem.unique || priceListItem.id || '';
      attr.productSelect['unique'] = String(unique);
    }
    if (!attr.productSelect.cart_num) {
      attr.productSelect['cart_num'] = 1;
    }
  }

  // 预置 productAttr 的 index 高亮（若有规格项）
  if (attr.productAttr && attr.productAttr.length) {
    for (let i = 0; i < attr.productAttr.length; i++) {
      const attrItem = attr.productAttr[i];
      // attr_value（单数）为后端标准字段；兼容 attr_values（复数）
      const attrValues = attrItem.attr_value || attrItem.attr_values || [];
      if (!attrItem.index && attrValues.length) {
        // attr_value 元素为对象 {attr, pic}，取 attr 字段；兼容字符串
        const firstVal = typeof attrValues[0] === 'object' ? attrValues[0].attr : attrValues[0];
        attrItem['index'] = firstVal;
      }
    }
  }

  // === readOnly 模式下补全 productSelect 与构造伪 productAttr（已选摘要）===
  // productWindow 直接遍历 attr.productAttr 渲染规格项；卡密等 readonly 类型
  // 后端 getDetailSpecs 返回空数组，导致弹窗规格项空白。
  // 这里从 selectedBlockData + displaySections 的 spec blocks 构造伪 attr 数组，
  // 复用 productWindow 现有 .item/.listn/.itemn.on-readonly 样式渲染已选摘要。
  if (isReadOnlyAttr.value) {
    // 1) 补全 productSelect 顶部展示字段（图片/价格/库存/名称）
    if (priceListItem) {
      const ps = attr.productSelect;
      if (!ps.image) {
        ps['image'] = priceListItem.image || storeInfo.value.image || '';
      }
      if (!ps.store_name) {
        ps['store_name'] = storeInfo.value.store_name || storeInfo.value.name || '';
      }
      if (ps.price === undefined || ps.price === '') {
        // 优先取 price_list 起价
        ps['price'] = priceListItem.price_from ?? priceListItem.total_price ?? storeInfo.value.price ?? 0;
      }
      if (ps.stock === undefined || ps.stock === '' || ps.stock === 0) {
        // 选择型业态库存按日期×场次×票种等维度管理，中台主表 stock 不可靠（常为 0）
        // 根据用户选中的具体日期+票种，从 calendar block 的 sku_detail
        // 查找精确库存状态；找不到数据时给默认非 0 值（宽容处理，让用户能点击
        // 确定，后端下单时再校验真实库存，避免库存未配置时误判"已售罄"）
        let stockValue = priceListItem.stock_count;
        if (stockValue === undefined || stockValue === null) {
          // 从 calendar block 的 dates[].sku_detail[skuId] 查找选中日期+票种的 status
          let preciseStatus = null;
          if (calendarItem && calendarItem.date && priceListItem.id) {
            for (const section of (displaySections.value || [])) {
              if (section.key !== 'spec' || !section.data || !Array.isArray(section.data.blocks)) continue;
              for (const block of section.data.blocks) {
                if (block.type !== 'calendar' || !Array.isArray(block.dates)) continue;
                const dateRow = block.dates.find((d) => d.date === calendarItem.date);
                if (dateRow && dateRow.sku_detail) {
                  const skuDetail = dateRow.sku_detail[priceListItem.id];
                  if (skuDetail && skuDetail.status) {
                    preciseStatus = skuDetail.status;
                  }
                }
              }
            }
          }
          // 库存判断逻辑：
          // - preciseStatus 有值（calendar 的 sku_detail 找到选中日期+票种）：
          //   用精确 status，sold_out 才视为真的售罄
          // - preciseStatus 为 null（daily_stock 未配置或日期不在 calendar 范围）：
          //   宽容处理，给默认非 0 值，避免 calcSkuStockStatus 初始值 sold_out 误判
          //   后端下单时会再次校验真实库存
          let finalStatus;
          if (preciseStatus) {
            finalStatus = preciseStatus;
          } else {
            finalStatus = 'sufficient';
          }
          const stockMap = { sold_out: 0, tight: 5, sufficient: 99 };
          stockValue = stockMap[finalStatus] ?? 99;
        }
        ps['stock'] = stockValue;
      }
      if (!ps.suk) {
        // suk 用于购物车列表展示，readOnly 弹窗暂不显示但保持完整
        ps['suk'] = priceListItem.name || '';
      }
    }

    // 2) 构造伪 productAttr 数组：每个选中 block → 一项规格
    const blockTitleMap = {};
    for (const section of (displaySections.value || [])) {
      if (section.key === 'spec' && section.data && Array.isArray(section.data.blocks)) {
        for (const b of section.data.blocks) {
          if (b.type) blockTitleMap[b.type] = b.title || b.type;
        }
      }
    }
    const fakeAttr = [];
    for (const sel of selectedBlockData.value) {
      if (!sel || !sel.item) continue;
      const value = formatBlockItemLabel(sel.type, sel.item);
      if (!value) continue;
      // productWindow 模板遍历 item.attr_value（单数），每项为对象 {attr, pic?}
      // 显示用 itemn.attr，高亮判断 item.index === itemn.attr
      fakeAttr.push({
        attr_name: blockTitleMap[sel.type] || sel.type,
        attr_value: [{ attr: value }],
        index: value, // 与 itemn.attr 相等 → 触发 on-readonly 高亮
      });
    }
    if (fakeAttr.length) {
      attr['productAttr'] = fakeAttr;
    }
  }
}
/**
 * 格式化 display_sections block 选中项为展示文本
 * 用于 readOnly 弹窗中伪 productAttr 的 attr_values 取值
 * @param {string} type block.type（price_list/calendar/time_slots/staff/sku）
 * @param {Object} item block.item
 * @return {string} 展示文本
 */
function formatBlockItemLabel(type, item) {
  if (!item) return '';
  switch (type) {
    case 'price_list':
      return item.name || '';
    case 'calendar':
      // range 模式：拼入住-离店等日期范围；single 模式：仅日期
      if (item.check_in && item.check_out) {
        return `${item.check_in} 至 ${item.check_out}`;
      }
      return item.date || '';
    case 'time_slots':
      // 拼时段 + 场次名，避免只有时段无名称时信息缺失
      return [item.time, item.name].filter(Boolean).join(' ');
    case 'staff':
      return item.name || '';
    case 'sku':
      return item.suk || item.name || '';
    default:
      return item.name || item.suk || item.date || '';
  }
}
/**
 * 获取购物车数量
 * @param boolean 是否展示购物车动画和重置属性
 */
function getCartCount(isAnima) {
  if (isLogin.value) {
    getCartCounts().then((res) => {
      CartCount.value = res.data.count;
      indexDataStore.setCartNum(
        CartCount.value > 99 ? "..." : CartCount.value + ""
      );
      // uni.setTabBarBadge({
      // 	index: Number(uni.getStorageSync('FOOTER_ADDCART')) || 2,
      // 	text: CartCount.value + ''
      // })
      //加入购物车后重置属性
      if (isAnima) {
        animated.value = true;
        setTimeout(function () {
          animated.value = false;
        }, 500);
      }
    });
  }
}
function goGift() {
  isGiftOrder.value = 1;
  goBuy();
}
/**
 * 立即购买
 */
function goBuy() {
  if (isLogin.value === false) {
    toLogin();
  } else {
    // direct 模式（预售/租赁/服务）：跳过规格弹窗，直接用默认 SKU 下单
    if (isDirectMode.value) {
      const defaultUnique = storeInfo.value.default_sku || storeInfo.value.spec_unique || (attr.productSelect && attr.productSelect.unique) || '';
      const params = buildCartParams(defaultUnique, 1, storeInfo.value.virtual_type);
      params.new = 1;
      submitCartAdd(params, true);
      return;
    }
    // this.$refs.proSwiper.videoIsPause();
    goCat(true);
  }
}
function open(data) {
  showMenuIcon.value = data;
}
// 授权关闭
function authColse(e) {
  isShowAuth.value = e;
}
/**
 * 分享打开
 *
 */
function listenerActionSheet() {
  currentPage.value = false;
  // #ifdef H5
  if (wechat.isWeixin()) {
    weixinStatus.value = true;
  }
  // #endif

  posters.value = true;
}
// 分享关闭
function listenerActionClose() {
  posters.value = false;
  posterImageStatus.value = false;
}
//隐藏海报
function posterImageClose() {
  posterImageStatus.value = false;
}

// 小程序关闭分享弹窗；
function goFriend() {
  posters.value = false;
}
/*
 * 保存到手机相册
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
//#ifdef H5
function ShareInfo() {
  let data = storeInfo.value;
  let href = location.href;
  if (wechat.isWeixin()) {
    getUserInfoApi().then((res) => {
      href =
        href.indexOf("?") === -1
          ? href + "?spread=" + res.data.uid
          : updateURLParameter(href, "spread", res.data.uid);
      let configAppMessage = {
        desc: data.store_info,
        title: data.store_name,
        link: href,
        imgUrl: data.image,
      };
      wechat
        .wechatEvevt(
          [
            "updateAppMessageShareData",
            "updateTimelineShareData",
            "onMenuShareAppMessage",
            "onMenuShareTimeline",
          ],
          configAppMessage
        )
        .then((res) => {})
        .catch((err) => {});
    });
  }
}
//#endif
function tabCouponType(type) {
  coupon["type"] = type;
  getCouponList(type);
}
//点击sku图片打开轮播图
function showImg(index) {
  cusPreviewImgRef.value.open(selectSku.value.suk);
}
function showSwiperImg(index) {
  cusSwiperImg.value.open(index);
}
//滑动轮播图选择商品
function changeSwitch(e) {
  const productSelect = skuArr.value[e];
  if (!productSelect) return;

  selectSku.value = productSelect;

  const skuList = productSelect.suk.split(",");

  // 使用循环设置所有productAttr的index，避免重复代码
  skuList.forEach((sku, index) => {
    if (attr.productAttr[index]) {
      attr.productAttr[index]["index"] = sku;
    }
  });

  // 批量更新productSelect属性
  const selectProps = ["image", "price", "stock", "unique", "vipPrice"];
  selectProps.forEach((prop) => {
    attr.productSelect[prop] = productSelect[prop];
  });

  attrTxt.value = i18n.t(`已选择`);
  attrValue.value = productSelect.suk;
}
function bindSortId(item, data) {
  if (item.dataType.tabVal == 1) {
    uni.navigateTo({
      url: `/pages/goods/goods_list/index?cid=${item.classPage.id}&title=${item.classPage.name}`,
    });
  } else if (item.text.val == "首页") {
    uni.switchTab({
      url: `/pages/index/index`,
    });
  } else {
    uni.navigateTo({
      url: `/pages/annex/special/index?theme_id=${item.microPage.id}`,
    });
  }
}
</script>


<style scoped lang="scss">
.iconfonts {
  color: #fff !important;
  font-size: 28rpx;
}

.mask {
  z-index: 300 !important;
}

.head-bar {
  background: #fff;
}

.generate-posters {
  width: 100%;
  height: 170rpx;
  background-color: #fff;
  position: fixed;
  left: 0;
  bottom: 0;
  z-index: 388;
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

.generate-posters .item .iconfont.icon-haowuquan1 {
  color: #ff954d;
}

// .product-con .footer {
//   padding: 0 20rpx 0 30rpx;
//   position: fixed;
//   bottom: 0;
//   width: 100%;
//   box-sizing: border-box;
//   background-color: rgba(255, 255, 255, 0.85);
//   backdrop-filter: blur(10px);
//   z-index: 277;
//   border-top: 1rpx solid #f0f0f0;
//   height: 100rpx;
//   height: calc(100rpx + constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
//   height: calc(100rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
//   transform: translate3d(0, 100%, 0);
//   transition: all 0.3s cubic-bezier(0.25, 0.5, 0.5, 0.9);
//   .gift-icon {
//     width: 40rpx;
//     height: 40rpx;
//     margin: 5rpx 0 5rpx;
//   }
// }

// .product-con .footer .item {
//   display: flex;
//   flex-direction: column;
//   justify-content: center;
//   align-items: center;
//   font-size: 18rpx;
//   color: #666;
// }

// .product-con .footer .item .iconfont {
//   text-align: center;
//   font-size: 40rpx;
// }

// .product-con .footer .item .iconfont.icon-shoucang1 {
//   color: var(--view-theme);
// }

// .product-con .footer .item .iconfont.icon-gouwuche1 {
//   font-size: 40rpx;
//   position: relative;
// }

// .product-con .footer .item .iconfont.icon-gouwuche1 .num {
//   color: #fff;
//   position: absolute;
//   font-size: 18rpx;
//   padding: 2rpx 10rpx 3rpx;
//   border-radius: 200rpx;
//   top: -10rpx;
//   right: -10rpx;
// }

.virbnt {
  // width: 444rpx !important;
  height: 76rpx !important;
  border-radius: 50rpx !important;
  overflow: hidden;
}

.virbnts {
  width: 444rpx !important;
  text-align: center;
  line-height: 76rpx;
  color: #fff;
  font-size: 28rpx;
  background-color: var(--view-bntColor);
  border-radius: 50rpx !important;
}

.product-con .footer .bnt {
  width: 444rpx;
  height: 76rpx;
}

.product-con .footer .bnt .bnts {
  width: 222rpx;
  text-align: center;
  line-height: 76rpx;
  color: #fff;
  font-size: 28rpx;
}

.product-con .footer .bnt .joinCart {
  border-radius: 50rpx 0 0 50rpx;
  background-color: var(--view-bntColor);
  // background-image: linear-gradient(to right, #fea10f 0%, #fa8013 100%);
}

.product-con .footer .bnt .buy {
  border-radius: 0 50rpx 50rpx 0;
  background-color: var(--view-theme);
  // background-image: linear-gradient(to right, #fa6514 0%, #e93323 100%);
}

button {
  padding: 0;
  margin: 0;
  line-height: normal;
  background-color: #fff;
}

button::after {
  border: 0;
}

action-sheet-item {
  padding: 0;
  height: 240rpx;
  align-items: center;
  display: flex;
}

.contact {
  font-size: 16px;
  width: 50%;
  background-color: #fff;
  padding: 8rpx 0;
  border-radius: 0;
  margin: 0;
  line-height: 2;
}

.contact::after {
  border: none;
}

.action-sheet {
  font-size: 17px;
  line-height: 1.8;
  width: 50%;
  position: absolute;
  top: 0;
  right: 0;
  padding: 25rpx 0;
}

.canvas {
  z-index: 300;
  width: 750px;
  height: 1190px;
}

.poster-pop {
  width: 450rpx;
  height: 714rpx;
  position: fixed;
  left: 50%;
  transform: translateX(-50%);
  z-index: 399;
  top: 50%;
  margin-top: -377rpx;
  .poster-img {
    border-radius: 6px;
  }
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
  border-radius: 40rpx;
  margin-top: 28rpx;
}

.poster-pop .keep {
  color: #fff;
  text-align: center;
  font-size: 25rpx;
  margin-top: 10rpx;
}

.mask {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.6);
  z-index: 9;
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
  padding-left: 48px;
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

.share-box {
  z-index: 1000;
  position: fixed;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;

  image {
    width: 100%;
    height: 100%;
  }
}

.presale .bnts {
  width: 444rpx;
  height: 76rpx;
  border-radius: 50rpx 50rpx;
  background-color: var(--view-theme);
  text-align: center;
  line-height: 76rpx;
  color: #fff;
  font-size: 28rpx;
}

.delete-line {
  text-decoration: line-through;
}
</style>
