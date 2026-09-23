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
        <!-- #ifdef APP-PLUS || H5 -->
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
          <productConSwiper :imgUrls="imgUrls"></productConSwiper>
          <view class="nav acea-row row-between-wrapper">
            <view class="share acea-row row-between row-bottom">
              <view class="money font-color">
                <image src="./static/my-point.png" mode=""></image>
                <text class="num" v-text="storeInfo.price || 0"></text
                >{{ $t(`积分`) }}
              </view>
              <view></view>
            </view>
          </view>
          <view class="wrapper">
            <view class="introduce acea-row row-between">
              <view class="infor"> {{ storeInfo.title }}</view>
            </view>
            <view class="limit_good" v-if="storeInfo.num > 0">
              {{ $t(`最多可兑换`) }}: {{ storeInfo.num
              }}{{ $t(storeInfo.unit_name) }}
            </view>
            <view class="label acea-row row-middle">
              <view class="stock delete-line">{{
                storeInfo.product_price
              }}</view>
              <view class="stock"
                >{{ $t(`限量`) }}:
                {{ storeInfo.quota_show }}
              </view>
              <view class="stock"
                >{{ $t(`已兑换`) }}：{{ storeInfo.sales }}
              </view>
            </view>
          </view>
          <view
            class="attribute acea-row row-between-wrapper"
            @tap="selecAttr"
            v-if="attribute.productAttr.length"
          >
            <!-- <view class="df"><text class='atterTxt line1'>{{attr}}：{{attrValue}}</text></view>
						<view class='iconfont icon-jiantou'></view> -->
            <view class="flex">
              <view style="display: flex; align-items: center; width: 90%">
                <view class="attr-txt"> {{ attr }}： </view>
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
                >
                </image>
              </view>
              <view class="switchTxt"
                >{{ $t(`共`) }}{{ skuArr.length }}{{ $t(`种规格可选`) }}</view
              >
            </view>
          </view>
        </view>
        <view class="product-intro" id="past2">
          <view class="title">{{ $t(`产品介绍`) }}</view>
          <view class="conter">
            <mp-html :content="storeInfo.description" :tag-style="tagStyle" />
          </view>
        </view>
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
            attribute.productSelect.product_stock > 0
          "
        >
          <view class="buy bnts" @tap="goCat">{{ $t(`立即兑换`) }}</view>
        </view>
        <view class="bnt acea-row" v-else>
          <view class="bnts no-goods">{{ $t(`无法兑换`) }}</view>
        </view>
      </view>
    </view>
    <product-window
      :attr="attribute"
      :limitNum="1"
      @myevent="onMyEvent"
      @ChangeAttr="ChangeAttr"
      @ChangeCartNum="ChangeCartNum"
      @attrVal="attrVal"
      @iptCartNum="iptCartNum"
      @getImg="showImg"
    >
    </product-window>
    <cus-previewImg
      ref="cusPreviewImgRef"
      :list="skuArr"
      @changeSwitch="changeSwitch"
      @shareFriend="listenerActionSheet"
    />
    <!-- 分享按钮 -->
    <kefuIcon
      :ids="storeInfo.product_id"
      :routineContact="routineContact"
    ></kefuIcon>
    <!-- 发送给朋友图片 -->
  </view>
</template>

<script setup>
import { ref, computed, watch, nextTick, getCurrentInstance } from 'vue';
import { onLoad, onNavigationBarButtonTap } from '@dcloudio/uni-app';
const app = getApp();
import { useAppStore } from '@/store/modules/app';
import { storeToRefs } from 'pinia';
import { getIntegralProductDetail as getIntegralProductDetailApi } from '@/api/activity.js';
import { getCenterOrderConfirm } from '@/api/order-center.js';
import productConSwiper from '@/components/productConSwiper/index.vue';
import productWindow from './component/productWindow.vue';
import userEvaluation from '@/components/userEvaluation/index.vue';
import kefuIcon from '@/components/kefuIcon';
// #ifdef MP
import authorize from '@/components/Authorize';
// #endif
import countDown from '@/components/countDown';
import { imageBase64 } from '@/api/public';
import { toLogin } from '@/libs/login.js';
import { getUserInfo } from '@/api/user.js';
// #ifdef APP-PLUS
import { TOKENNAME, HTTP_REQUEST_URL } from '@/config/app.js';
// #endif
import { useColorTheme } from '@/composables/useColorTheme';
import cusPreviewImg from '@/components/cusPreviewImg/index.vue';
import homeList from '@/components/homeList';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';
// #ifdef H5
import Auth from '@/libs/wechat';
// #endif

const instance = getCurrentInstance();
const { colorStyle } = useColorTheme();

const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

// ---------- data ----------
const dataShow = ref(0);
const id = ref(0);
const time = ref(0);
const countDownHour = ref('00');
const countDownMinute = ref('00');
const countDownSecond = ref('00');
const storeInfo = ref([]);
const imgUrls = ref([]);
const parameter = ref({
  navbar: '1',
  return: '1',
  title: i18n.t(`抢购详情页`),
  color: false,
});
const attribute = ref({
  cartAttr: false,
  productAttr: [],
  productSelect: {},
});
const productValue = ref([]);
const isOpen = ref(false);
const attr = ref(i18n.t(`请选择`));
const attrValue = ref('');
// 原 this.attrTxt（未在 data 声明的实例属性），保留为 ref
const attrTxt = ref('');
const status = ref(1);
const isAuto = ref(false);
const isShowAuth = ref(false);
const iShidden = ref(false);
const limitNum = ref(1); //限制本属性产品的个数；
const iSplus = ref(false);
const replyCount = ref(0); //总评论数量
const reply = ref([]); //评论列表
const replyChance = ref(0);
const navH = ref('');
const navList = ref([i18n.t(`商品`), i18n.t(`详情`)]);
const opacity = ref(0);
const scrollY = ref(0);
const topArr = ref([]);
const toView = ref('');
const height = ref(0);
const heightArr = ref([]);
const lock = ref(false);
const scrollTop = ref(0);
const tagStyle = ref({
  img: 'width:100%;display:block;',
  table: 'width:100%',
  video: 'width:100%',
});
const datatime = ref('');
const navActive = ref(0);
const meunHeight = ref(0);
const backH = ref('');
const posters = ref(false);
const weixinStatus = ref(false);
const posterImageStatus = ref(false);
const storeImage = ref(''); //海报产品图
const PromotionCode = ref(''); //二维码图片
const posterImage = ref(''); //海报路径
const actionSheetHidden = ref(false);
const cart_num = ref(1);
const homeTop = ref(20);
const returnShow = ref(true);
const H5ShareBox = ref(false); //公众号分享图片
const routineContact = ref(0);
const skuArr = ref([]);
const selectSku = ref({});
const currentPage = ref(false);
const sysHeight = ref(uni.getWindowInfo().statusBarHeight + 'px');
// 模板 ref：sku 轮播预览组件
const cusPreviewImgRef = ref(null);

// ---------- watch ----------
watch(isLogin, (newV, oldV) => {
  if (newV) {
    getIntegralProductDetail();
  }
}, { deep: true });

onLoad((options) => {
  let statusBarHeight = '';
  var pages = getCurrentPages();
  returnShow.value = pages.length === 1 ? false : true;
  //设置商品列表高度
  uni.getSystemInfo({
    success: function (res) {
      height.value = res.windowHeight;
      statusBarHeight = res.statusBarHeight;
      //res.windowHeight:获取整个窗口高度为px，*2为rpx；98为头部占据的高度；
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
    if (value.time) datatime.value = value.time;
  }
  // #endif

  if (options.id) {
    id.value = options.id;
    datatime.value = Number(options.time);
    status.value = options.status;
  }
  if (isLogin.value) {
    getIntegralProductDetail();
  } else {
    toLogin();
  }
  nextTick(() => {
    // #ifdef MP
    const menuButton = uni.getMenuButtonBoundingClientRect();
    const query = uni.createSelectorQuery().in(instance.proxy);
    query
      .select('#home')
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
    provider: 'weixin',
    scene: scene,
    type: 0,
    href: `${HTTP_REQUEST_URL}${curRoute}`,
    title: storeInfo.value.title,
    summary: storeInfo.value.info,
    imageUrl: storeInfo.value.small_image,
    success: function (res) {
      uni.showToast({
        title: title.$t(`分享成功`),
        icon: 'success',
      });
      posters.value = false;
    },
    fail: function (err) {
      uni.showToast({
        title: title.$t(`分享失败`),
        icon: 'none',
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
  attribute.value.productSelect['cart_num'] = e;
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
    getIntegralProductDetail();
  }
}
function getIntegralProductDetail() {
  getIntegralProductDetailApi(id.value)
    .then((res) => {
      dataShow.value = 1;
      let title = res.data.storeInfo.title;
      storeInfo.value = res.data.storeInfo;
      imgUrls.value = res.data.storeInfo.images;
      storeInfo.value.description = storeInfo.value.description.replace(
        /<img/gi,
        '<img style="max-width:100%;height:auto;float:left;display:block" '
      );
      attribute.value.productAttr = res.data.productAttr;
      productValue.value = res.data.productValue;
      attribute.value.productSelect.num = res.data.storeInfo.num;
      replyCount.value = res.data.replyCount;
      reply.value = res.data.reply ? [res.data.reply] : [];
      replyChance.value = res.data.replyChance;
      routineContact.value = Number(res.data.routine_contact_type);
      for (let key in res.data.productValue) {
        let obj = res.data.productValue[key];
        skuArr.value.push(obj);
      }
      selectSku.value = skuArr.value[0];
      uni.setNavigationBarTitle({
        title: title.substring(0, 7) + '...',
      });
      // #ifdef H5
      PromotionCode.value = res.data.storeInfo.code_base;
      storeImage.value = storeInfo.value.image;
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
function setShare() {
  Auth &&
    Auth.isWeixin() &&
    Auth.wechatEvevt(
      [
        'updateAppMessageShareData',
        'updateTimelineShareData',
        'onMenuShareAppMessage',
        'onMenuShareTimeline',
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
      value = attribute.value.productAttr.length ? key.split(',') : [];
      break;
    }
  }
  for (let i = 0; i < productAttr.length; i++) {
    productAttr[i]['index'] = value[i];
  }
  //sort();排序函数:数字-英文-汉字；
  let productSelect = productValue.value[value.join(',')];
  if (productSelect && productAttr.length) {
    attribute.value.productSelect['store_name'] = storeInfo.value.title;
    attribute.value.productSelect['image'] = productSelect.image;
    attribute.value.productSelect['price'] = productSelect.price;
    attribute.value.productSelect['stock'] = productSelect.stock;
    attribute.value.productSelect['unique'] = productSelect.unique;
    attribute.value.productSelect['quota'] = productSelect.quota;
    attribute.value.productSelect['quota_show'] = productSelect.quota_show;
    attribute.value.productSelect['product_stock'] = productSelect.product_stock;
    attribute.value.productSelect['cart_num'] = 1;
    attrValue.value = value.join(',');
  } else if (!productSelect && productAttr.length) {
    attribute.value.productSelect['store_name'] = storeInfo.value.title;
    attribute.value.productSelect['image'] = storeInfo.value.image;
    attribute.value.productSelect['price'] = storeInfo.value.price;
    attribute.value.productSelect['quota'] = 0;
    attribute.value.productSelect['quota_show'] = 0;
    attribute.value.productSelect['product_stock'] = 0;
    attribute.value.productSelect['stock'] = 0;
    attribute.value.productSelect['unique'] = '';
    attribute.value.productSelect['cart_num'] = 0;
    attrValue.value = '';
    attrTxt.value = i18n.t(`请选择`);
  } else if (!productSelect && !productAttr.length) {
    attribute.value.productSelect['store_name'] = storeInfo.value.title;
    attribute.value.productSelect['image'] = storeInfo.value.image;
    attribute.value.productSelect['price'] = storeInfo.value.price;
    attribute.value.productSelect['stock'] = storeInfo.value.stock;
    attribute.value.productSelect['quota'] = storeInfo.value.quota;
    attribute.value.productSelect['product_stock'] = storeInfo.value.product_stock;
    attribute.value.productSelect['unique'] = storeInfo.value.unique || '';
    attribute.value.productSelect['cart_num'] = 1;
    attribute.value.productSelect['quota'] = productSelect.quota;
    attribute.value.productSelect['product_stock'] = productSelect.product_stock;
    attrValue.value = '';
    attrTxt.value = i18n.t(`请选择`);
  }
}
function selecAttr() {
  attribute.value.cartAttr = true;
}
function onMyEvent() {
  attribute.value['cartAttr'] = false;
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
    if (num.cart_num < attribute.value.productSelect.quota) {
      num.cart_num++;
      attribute.value.productSelect['cart_num'] = num.cart_num;
      cart_num.value = num.cart_num;
      attribute.value.productSelect['cart_num'] = num.cart_num;
    }
  } else {
    if (num.cart_num == 1) return;
    num.cart_num--;
    cart_num.value = num.cart_num;
    attribute.value.productSelect['cart_num'] = num.cart_num;
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
    attribute.value.productSelect['image'] = productSelect.image;
    attribute.value.productSelect['price'] = productSelect.price;
    attribute.value.productSelect['stock'] = productSelect.stock;
    attribute.value.productSelect['unique'] = productSelect.unique;
    attribute.value.productSelect['cart_num'] = 1;
    attribute.value.productSelect['quota'] = productSelect.quota;
    attribute.value.productSelect['quota_show'] = productSelect.quota_show;
    attrValue.value = res;

    attrTxt.value = i18n.t(`已选择`);
  } else {
    attribute.value.productSelect['image'] = storeInfo.value.image;
    attribute.value.productSelect['price'] = storeInfo.value.price;
    attribute.value.productSelect['stock'] = 0;
    attribute.value.productSelect['unique'] = '';
    attribute.value.productSelect['cart_num'] = 0;
    attribute.value.productSelect['quota'] = 0;
    attribute.value.productSelect['quota_show'] = 0;
    attrValue.value = '';
    attrTxt.value = i18n.t(`已选择`);
  }
}
function scroll(e) {
  let scrollYVal = e.detail.scrollTop;
  var opacityVal = scrollYVal / 200;
  opacityVal = opacityVal > 1 ? 1 : opacityVal;
  opacity.value = opacityVal;
  currentPage.value = false;
  scrollY.value = scrollYVal;
  if (lock.value) {
    lock.value = false;
    return;
  }
  for (var i = 0; i < topArr.value.length; i++) {
    if (
      scrollYVal <
      topArr.value[i] - app.globalData.navHeight / 2 + heightArr.value[i]
    ) {
      navActive.value = i;
      break;
    }
  }
}
function tap(item, index) {
  var id = item.id;
  var index = index;
  toView.value = id;
  navActive.value = index;
  lock.value = true;
  scrollTop.value =
    index > 0
      ? topArr.value[index] - app.globalData.navHeight / 2
      : topArr.value[index];
}
//点击sku图片打开轮播图
function showImg(index) {
  cusPreviewImgRef.value.open(selectSku.value.suk);
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
    if (Auth.isWeixin() === true) {
      weixinStatus.value = true;
    }
    // #endif
    // #ifndef APP-PLUS
    downloadFilePromotionCode();
    // #endif
    // #ifdef APP-PLUS
    if (PromotionCode.value.indexOf('http') == 0) {
      // downloadFilePromotionCode();
    }
    // #endif

    posters.value = true;
  }
}
//滑动轮播图选择商品
function changeSwitch(e) {
  let productSelect = skuArr.value[e];
  selectSku.value = productSelect;
  var skuList = productSelect.suk.split(',');
  attribute.value.productAttr[0]['index'] = skuList[0];
  if (skuList.length == 2) {
    attribute.value.productAttr[0]['index'] = skuList[0];
    attribute.value.productAttr[1]['index'] = skuList[1];
  } else if (skuList.length == 3) {
    attribute.value.productAttr[0]['index'] = skuList[0];
    attribute.value.productAttr[1]['index'] = skuList[1];
    attribute.value.productAttr[2]['index'] = skuList[2];
  } else if (skuList.length == 4) {
    attribute.value.productAttr[0]['index'] = skuList[0];
    attribute.value.productAttr[1]['index'] = skuList[1];
    attribute.value.productAttr[2]['index'] = skuList[2];
    attribute.value.productAttr[3]['index'] = skuList[3];
  }
  if (productSelect) {
    attribute.value.productSelect['image'] = productSelect.image;
    attribute.value.productSelect['price'] = productSelect.price;
    attribute.value.productSelect['stock'] = productSelect.stock;
    attribute.value.productSelect['unique'] = productSelect.unique;
    attribute.value.productSelect['vipPrice'] = productSelect.vipPrice;
    attrTxt.value = i18n.t(`已选择`);
    attrValue.value = productSelect.suk;
  }
}
/*
 *  下订单
 */
async function goCat() {
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
    return app.$util.Tips({
      title: i18n.t(`请选择属性`),
    });
  if (cart_num.value <= 0) {
    return app.$util.Tips({
      title: i18n.t(`请选择数量`),
    });
  }
  isOpen.value = false;
  // 走通用下单流程，跳转中台订单确认页
  let productId = storeInfo.value.product_id || 0;
  let activityId = storeInfo.value.id || 0;
  let productType = storeInfo.value.product_type || storeInfo.value.target_product_type || 'normal';
  let skuUnique = productSelect.unique || storeInfo.value.unique || '';
  if (!productId || !activityId) {
    return app.$util.Tips({
      title: i18n.t(`商品信息不完整`)
    });
  }

  // 预校验：调用确认接口检查库存、限购、积分余额
  try {
    uni.showLoading({ title: i18n.t(`校验中...`), mask: true });
    await getCenterOrderConfirm({
      product_id: productId,
      sku_unique: skuUnique,
      product_type: productType,
      activity_type: 'integral',
      activity_id: activityId,
      num: cart_num.value || 1,
    });
    uni.hideLoading();
  } catch (e) {
    uni.hideLoading();
    // 弹窗提示实际错误信息，不跳转订单确认页
    var errMsg = typeof e === 'string' ? e : (e.msg || e.message || i18n.t(`校验失败`));
    uni.showModal({
      title: i18n.t(`提示`),
      content: errMsg,
      showCancel: false
    });
    return;
  }

  uni.navigateTo({
    url: `/pages/center/order/create/index?product_id=${productId}&sku_unique=${skuUnique}&product_type=${productType}&activity_type=integral&activity_id=${activityId}&num=${cart_num.value || 1}`,
  });
}
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

.icon-xiangzuo {
  /* color: #000;
		position: fixed;
		font-size: 40rpx;
		width: 100rpx;
		height: 56rpx;
		line-height: 54rpx;
		z-index: 1000;
		left: 33rpx; */
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
  font-size: 28rpx;
  color: #fff;
}

.product-con .nav .iconfont {
  color: #fff;
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

.limit_good {
  font-size: 16rpx;
  margin: 10rpx 0rpx;
  color: red;
}

.product-con .wrapper .introduce .infor {
  // width: 570rpx;
}

.product-con .wrapper .introduce .iconfont {
  font-size: 37rpx;
  color: #515151;
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

.product-con .footer .bnt .joinCart {
  border-radius: 50rpx 0 0 50rpx;
  background-image: linear-gradient(to right, #fea10f 0%, #fa8013 100%);
}

.product-con .footer .bnt .buy {
  border-radius: 50rpx;
  // background-image: linear-gradient(to right, #fa6514 0%, #e93323 100%);
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

.delete-line {
  text-decoration: line-through;
}
</style>
