<template>
  <view
    class="new-users copy-data"
    :class="
      bgTabVal == 2
        ? 'fullsize noRepeat'
        : bgTabVal == 1
        ? 'repeat ysize'
        : 'noRepeat ysize'
    "
    :style="{
      height: pageHeight,
      backgroundColor: bgColor,
      backgroundImage: bgPic ? `url(${bgPic})` : '',
    }"
  >
    <view class="top" :style="colorStyle">
      <!-- #ifdef MP || APP-PLUS -->
      <view class="sys-head">
        <view class="sys-bar" :style="{ height: sysHeight }"></view>
        <!-- #ifdef MP -->
        <view
          class="sys-title"
          :style="member_style == 3 ? 'color:#333' : ''"
          >{{ $t("个人中心") }}</view
        >
        <!-- #endif -->
        <view
          class="bg"
          :style="member_style == 3 ? 'background:#f5f5f5' : ''"
        ></view>
      </view>
      <!-- #endif -->
    </view>

    <PageDesign
      :style="colorStyle"
      :diyData="currentDiyData"
      :isHome="false"
      :isPageVisible="isPageVisible"
      :isScrolled="isScrolled"
      :isFixed="isFixed"
      :belongIndex="belongIndex"
      @bindSortId="bindSortId"
      @bindHeight="bindHeighta"
      @storeTap="storeTap"
      @changeLogin="changeLogin"
      @changeBarg="changeBarg"
      @goDetail="goDetail"
    ></PageDesign>
    <image :src="copyRightPic" alt="" class="support"></image>
    <editUserModal
      :isShow="editModal"
      @closeEdit="closeEdit"
      @editSuccess="editSuccess"
    ></editUserModal>
    <pageFooter :style="colorStyle"></pageFooter>
  </view>
</template>
<script setup>
import { ref, computed } from 'vue';
import { onLoad, onReady, onShow, onHide, onUnload, onPullDownRefresh, onPageScroll } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import {
  getMenuList,
  getUserInfo as getUserInfoApi,
  setVisit as setVisitApi,
  mpBindingPhone,
} from "@/api/user.js";
import { getThemeInfo } from "@/api/api.js";
import { wechatAuthV2, silenceAuth } from "@/api/public.js";
import { toLogin } from "@/libs/login.js";
// #ifdef H5
import Auth from "@/libs/wechat";
// #endif
const app = getApp();
import dayjs from "@/plugin/dayjs/dayjs.min.js";
import Routine from "@/libs/routine";
import pageFooter from "@/components/pageFooter/index.vue";
import { getCustomer } from "@/utils/index.js";
import editUserModal from "@/components/editUserModal/index.vue";
import couponWindow from "@/components/couponWindow/index";
import waterfallsFlow from "@/components/WaterfallsFlow/WaterfallsFlow.vue";
import emptyPage from "@/components/emptyPage.vue";
import Loading from "@/components/Loading/index.vue";
import { getCrmebCopyRight } from "@/api/api.js";
import { goShopDetail } from "@/libs/order.js";
import PageDesign from "@/subpackage/diyComponents/pageDesign.vue";
import supportPic from "@/static/images/support.png";
import Cache from "@/utils/cache";
import util from "@/utils/util.js";
import i18n from "@/utils/lang.js";
import { useColorTheme } from "@/composables/useColorTheme";
import { useAppStore } from "@/store/modules/app";
import { useIndexDataStore } from "@/store/modules/indexData";

const { colorStyle } = useColorTheme();
const appStore = useAppStore();
const indexDataStore = useIndexDataStore();
const { isLogin } = storeToRefs(appStore);
const { cartNum } = storeToRefs(indexDataStore);

// ---------- computed ----------
const pdHeights = computed(() => {
  return { height: "100rpx" };
});

// ---------- data ----------
const currentDiyData = ref({});
const isPageVisible = ref(false);
const editModal = ref(false); // 编辑头像信息
const storeMenu = ref([]); // 商家管理
const orderMenu = ref([
  {
    img: "icon-daifukuan",
    title: "待付款",
    url: "/pages/goods/order_list/index?status=0",
  },
  {
    img: "icon-daifahuo",
    title: "待发货",
    url: "/pages/goods/order_list/index?status=1",
  },
  {
    img: "icon-daishouhuo",
    title: "待收货",
    url: "/pages/goods/order_list/index?status=2",
  },
  {
    img: "icon-daipingjia",
    title: "待评价",
    url: "/pages/goods/order_list/index?status=3",
  },
  {
    img: "icon-a-shouhoutuikuan",
    title: "售后/退款",
    url: "/pages/users/user_return_list/index",
  },
]);
const imgUrls = ref([]);
const autoplay = ref(true);
const circular = ref(true);
const interval = ref(3000);
const duration = ref(500);
const isAuto = ref(false); //没有授权的不会自动授权
const isShowAuth = ref(false); //是否隐藏授权
const orderStatusNum = ref({});
const userInfo = ref({});
const MyMenus = ref([]);
const sysHeight = ref(uni.getWindowInfo().statusBarHeight + "px");
const mpHeight = ref(0);
const showStatus = ref(1);
const activeRouter = ref("");
// #ifdef H5 || MP
const routineContact = ref(0);
// #endif
// 原 data 中 pageHeight 双声明（H5||MP 为 '100%'，APP-PLUS 取 globalData）
// #ifdef H5 || MP
const pageHeight = ref("100%");
// #endif
// #ifdef APP-PLUS
const pageHeight = ref(app.globalData.windowHeight);
// #endif
// #ifdef H5
const isWeixin = ref(Auth.isWeixin());
//#endif
// #ifndef H5
const isWeixin = ref(false);
//#endif
const footerSee = ref(false);
const my_menus_status = ref(0);
const business_status = ref(0);
const member_style = ref(0);
const my_banner_status = ref(0);
const is_diy = ref(uni.getStorageSync("is_diy"));
const copyRightPic = ref(supportPic); //版权图片
const belongIndex = ref(0);
const isScrolled = ref(false);
const isFixed = ref(false);
const product_video_status = ref(false);
const positionTop = ref(0);
const sortMpTop = ref(0);
const sortList = ref([]);
const curSort = ref(0);
const goodList = ref([]);
const loaded = ref(false);
const loading = ref(false);
const isCouponShow = ref(false);
const couponObj = ref({});
const site_config = ref("");
const configData = ref({});
const isFooter = ref(false);
const entryData = ref({ select_store_id: "", store_id: "" });
const sid = ref(0);
const goodPage = ref(1);
const bgColor = ref("");
const bgPic = ref("");
const bgTabVal = ref("");

// ---------- 生命周期 ----------
onLoad((option) => {
  uni.hideTabBar();
  // #ifdef MP
  // 小程序静默授权
  if (!appStore.isLogin) {
    // Routine.getCode()
    // 	.then(code => {
    // 		Routine.silenceAuth(code).then(res => {
    // 			this.onLoadFun();
    // 		})
    // 	})
    // 	.catch(res => {
    // 		uni.hideLoading();
    // 	});
  }
  // #endif

  // #ifdef H5 || APP-PLUS
  // if (isLogin.value == false) {
  // 	toLogin();
  // }
  //获取用户信息回来后授权
  let cacheCode = Cache.get("snsapi_userinfo_code");
  let res1 = cacheCode ? option.code != cacheCode : true;
  if (
    isWeixin.value &&
    option.code &&
    res1 &&
    option.scope === "snsapi_userinfo"
  ) {
    Cache.set("snsapi_userinfo_code", option.code);
    Auth.auth(option.code)
      .then((res) => {
        getUserInfo();
      })
      .catch((err) => {});
  }
  // #endif
  // #ifdef APP-PLUS
  pageHeight.value = app.globalData.windowHeight;
  // #endif

  let routes = getCurrentPages(); // 获取当前打开过的页面路由数组
  let curRoute = routes[routes.length - 1].route; //获取当前页面路由
  activeRouter.value = "/" + curRoute;
  getCopyRight();
});
onReady(() => {
  // #ifdef MP
  let info = uni.createSelectorQuery().select(".sys-head");
  info
    .boundingClientRect(function (data) {
      //data - 各种参数
      mpHeight.value = data.height;
    })
    .exec();
  // #endif
});
onShow(function () {
  isPageVisible.value = true;
  // #ifdef APP-PLUS
  uni.getSystemInfo({
    success: function (res) {
      pageHeight.value = res.windowHeight + "px";
    },
  });
  // #endif
  if (isLogin.value) {
    getUserInfo();
    setVisit();
  }
  getMyMenus();
  getDiyData();
  getCopyRight();
});
onHide(() => {
  isPageVisible.value = false;
});
onUnload(() => {
  isPageVisible.value = false;
});
onPullDownRefresh(() => {
  onLoadFun();
});
onPageScroll(() => {
  uni.$emit("scroll");
});

// ---------- methods ----------
function bindSortId(item, data) {
  if (item.dataType.tabVal == 1) {
    uni.navigateTo({
      url: `/pages/goods/goods_list/index?cid=${item.classPage.id}&title=${item.classPage.name}`,
    });
    // 与后端 DIY 配置下发的菜单文本做匹配（非展示文案），保持中文原文比较，不可包 $t
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
function storeTap(id) {
  entryData.value.select_store_id = id;
  entryData.value.store_id = "";
  uni.removeStorageSync("rulesStoreId");
}
function bindHeighta(data) {
  // #ifdef APP-PLUS
  sortMpTop.value = data.top + data.height;
  // #endif
}
function changeSort(item, index) {
  if (curSort.value == index) return;
  curSort.value = index;
  sid.value = item.id;
  goodList.value = [];
  goodPage.value = 1;
  loaded.value = false;
  // 原代码 this.getGoodsList 在本组件不存在（恒为假），typeof 保持同等语义
  if (typeof getGoodsList === "function") getGoodsList();
}
function goDetail(item) {
  goShopDetail(item, appStore.uid).then((res) => {
    uni.navigateTo({
      url: `/pages/goods_details/index?id=${item.id}`,
    });
  });
}
function couponClose() {
  isCouponShow.value = false;
  // 原代码 this.getNewCoupon 在本组件不存在（恒为假），typeof 保持同等语义
  if (!uni.getStorageSync("oldUser") && typeof getNewCoupon === "function") {
    getNewCoupon();
  }
}
function goICP(url) {
  // #ifdef H5
  window.open(url);
  // #endif
  // #ifdef MP
  uni.navigateTo({
    url: `/pages/annex/web_view/index?url=${url}`,
  });
  // #endif
}
function changeBarg(item) {
  if (!isLogin.value) {
    toLogin();
  } else {
    uni.navigateTo({
      url: `/pages/activity/goods_bargain_details/index?id=${item.id}&spid=${appStore.uid}`,
    });
  }
}
function changeLogin() {
  if (!isLogin.value) {
    toLogin();
  }
}
function objToArr(data) {
  if (!data || typeof data !== "object") return [];
  let obj = Object.keys(data).sort();
  let m = obj.map((key) => data[key]);
  return m;
}
function getDiyData() {
  let previewThemeId = uni.getStorageSync("previewThemeId");
  let data = {};
  if (previewThemeId) data.theme_id = previewThemeId;
  getThemeInfo("user", data).then((res) => {
    currentDiyData.value = res.data;
    if (currentDiyData.value.is_bg_color) {
      bgColor.value = currentDiyData.value.color_picker || "";
    }
    if (currentDiyData.value.is_bg_pic) {
      bgPic.value = currentDiyData.value.bg_pic || "";
      bgTabVal.value = currentDiyData.value.bg_tab_val || "";
    }
  });
}
function getWechatuserinfo() {
  //#ifdef H5
  Auth.isWeixin() && Auth.toAuth("snsapi_userinfo", "/pages/user/index");
  //#endif
}
function editSuccess() {
  editModal.value = false;
  getUserInfo();
}
function closeEdit() {
  editModal.value = false;
}
// 记录会员访问
function setVisit() {
  setVisitApi({
    url: "/pages/user/index",
  }).then((res) => {});
}
// 打开授权
function openAuto() {
  toLogin();
}
// 授权回调
function onLoadFun() {
  getUserInfo();
  getMyMenus();
  getDiyData();
  setVisit();
}
function Setting() {
  uni.openSetting({
    success: function (res) {},
  });
}
// 授权关闭
function authColse(e) {
  isShowAuth.value = e;
}
// 绑定手机
function bindPhone() {
  uni.navigateTo({
    url: "/pages/users/user_phone/index",
  });
}
function getphonenumber(e) {
  if (e.detail.errMsg == "getPhoneNumber:ok") {
    Routine.getCode()
      .then((code) => {
        let data = {
          code,
          iv: e.detail.iv,
          encryptedData: e.detail.encryptedData,
        };
        mpBindingPhone(data)
          .then((res) => {
            getUserInfo();
            util.Tips({
              title: res.msg,
              icon: "success",
            });
          })
          .catch((err) => {
            return util.Tips({
              title: err,
            });
          });
      })
      .catch((error) => {
        uni.hideLoading();
      });
  }
}
/**
 * 获取个人用户信息
 */
function getUserInfo() {
  getUserInfoApi().then((res) => {
    userInfo.value = res.data;
    appStore.UPDATE_USERINFO(res.data);
    appStore.SETUID(res.data.uid);
    orderMenu.value.forEach((item, index) => {
      switch (item.title) {
        case "待付款":
          item.num = res.data.orderStatusNum.unpaid_count;
          break;
        case "待发货":
          item.num = res.data.orderStatusNum.unshipped_count;
          break;
        case "待收货":
          item.num = res.data.orderStatusNum.received_count;
          break;
        case "待评价":
          item.num = res.data.orderStatusNum.evaluated_count;
          break;
        case "售后/退款":
          item.num = res.data.orderStatusNum.refunding_count;
          break;
      }
    });
    uni.stopPullDownRefresh();
  });
}
//小程序授权api替换 getUserInfo
function getUserProfile() {
  toLogin();
}
/**
 *
 * 获取个人中心图标
 */
function switchTabIcon(order) {
  orderMenu.value.forEach((item, index) => {
    switch (item.title) {
      case "待付款":
        item.img = order.dfk;
        break;
      case "待发货":
        item.img = order.dfh;
        break;
      case "待收货":
        item.img = order.dsh;
        break;
      case "待评价":
        item.img = order.dpj;
        break;
      case "售后/退款":
        item.img = order.sh;
        break;
    }
  });
}
function getMyMenus() {
  // if (MyMenus.value.length) return;
  getMenuList().then((res) => {
    member_style.value = Number(res.data.diy_data.value);
    my_banner_status.value = res.data.diy_data.my_banner_status;
    my_menus_status.value = res.data.diy_data.my_menus_status;
    business_status.value = res.data.diy_data.business_status;
    let storeMenuLocal = [];
    let myMenu = [];
    res.data.routine_my_menus.forEach((el, index, arr) => {
      // el.name 为后端下发的菜单名，保持中文原文比较，不可包 $t
      if (el.name == "客服接待") {
        storeMenuLocal.push(el);
      } else {
        myMenu.push(el);
      }
    });

    let order01 = {
      dfk: "icon-daifukuan",
      dfh: "icon-daifahuo",
      dsh: "icon-daishouhuo",
      dpj: "icon-daipingjia",
      sh: "icon-a-shouhoutuikuan",
    };
    let order02 = {
      dfk: "icon-daifukuan-lan",
      dfh: "icon-daifahuo-lan",
      dsh: "icon-daishouhuo-lan",
      dpj: "icon-daipingjia-lan",
      sh: "icon-shouhoutuikuan-lan",
    };
    let order03 = {
      dfk: "icon-daifukuan-ju",
      dfh: "icon-daifahuo-ju",
      dsh: "icon-daishouhuo-ju",
      dpj: "icon-daipingjia-ju",
      sh: "icon-shouhoutuikuan-ju",
    };
    let order04 = {
      dfk: "icon-daifukuan-fen",
      dfh: "icon-daifahuo-fen",
      dsh: "icon-daishouhuo-fen",
      dpj: "icon-daipingjia-fen",
      sh: "icon-shouhoutuikuan-fen",
    };
    let order05 = {
      dfk: "icon-daifukuan-lv",
      dfh: "icon-daifahuo-lv",
      dsh: "icon-daishouhuo-lv",
      dpj: "icon-daipingjia-lv",
      sh: "icon-shouhoutuikuan-lv",
    };
    if (member_style.value == 1) {
      switchTabIcon(order01);
    } else if (member_style.value == 2) {
      switchTabIcon(order02);
    } else if (member_style.value == 3) {
      switchTabIcon(order03);
    } else if (member_style.value == 4) {
      switchTabIcon(order04);
    } else if (member_style.value == 5) {
      switchTabIcon(order05);
    }
    storeMenu.value = storeMenuLocal;
    MyMenus.value = myMenu;
  });
}
function getCopyRight() {
  getCrmebCopyRight().then((res) => {
    if (res.data.copyrightImage)
      copyRightPic.value = res.data.copyrightImage;
  });
}
</script>

<style lang="scss">
.new-users {
  padding-bottom: 100rpx;
  .sys-head {
    position: relative;
    width: 100%;
    background: #fff;
    z-index: 50;

    .sys-bar {
      width: 100%;
    }

    .sys-title {
      width: 100%;
      height: 43px;
      line-height: 43px;
      text-align: center;
      font-size: 32rpx;
      color: #000;
      font-weight: 500;
      position: relative;
      z-index: 10;
    }

    .bg {
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      z-index: 5;
    }
  }
  .support {
    width: 219rpx;
    height: 74rpx;
    margin: 54rpx auto;
    display: block;
  }
}
.ysize {
  background-size: 100%;
}

.fullsize {
  background-size: 100% 100%;
}

.repeat {
  background-repeat: repeat;
}

.noRepeat {
  background-repeat: no-repeat;
}
</style>
