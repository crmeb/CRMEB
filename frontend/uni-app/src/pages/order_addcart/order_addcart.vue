<template>
  <view :style="colorStyle" class="order-addcart">
    <view class="guarantee-bar">
      <view class="guarantee-item"
        ><text class="iconfont icon-xuanzhong"></text
        ><text>{{ $t(`100%正品保证`) }}</text></view
      >
      <view class="guarantee-item"
        ><text class="iconfont icon-xuanzhong"></text
        ><text>{{ $t(`所有商品精挑细选`) }}</text></view
      >
      <view class="guarantee-item"
        ><text class="iconfont icon-xuanzhong"></text
        ><text>{{ $t(`售后无忧`) }}</text></view
      >
    </view>
    <view class="main-content" v-if="canShow">
      <view class="nav-bar">
        <view class="nav-left"
          >{{ $t(`购物数量`) }}<text class="count">{{ cartCount }}</text></view
        >
        <view
          v-if="cartList.valid.length || cartList.invalid.length"
          class="nav-right"
          :class="{ managing: !footerswitch }"
          @click="manage"
          >{{ footerswitch ? $t(`管理`) : $t(`取消`) }}</view
        >
      </view>
      <view
        v-if="cartList.valid.length || cartList.invalid.length"
        class="goods-wrapper"
      >
        <view class="valid-list">
          <checkbox-group @change="checkboxChange">
            <view
              v-for="(item, index) in cartList.valid"
              :key="index"
              class="goods-item"
            >
              <checkbox
                :value="item.id.toString()"
                :checked="item.checked"
                :disabled="!item.attrStatus && footerswitch"
                backgroundColor="#ffffff"
                activeBackgroundColor="var(--view-theme, #E93323)"
                activeBorderColor="var(--view-theme, #E93323)"
                iconColor="#ffffff"
              />
              <navigator :render-link="false"
                :url="'/pages/goods_details/index?id=' + item.product_id"
                hover-class="none"
                class="goods-main"
              >
                <view class="goods-img"
                  ><image
                    v-if="item.productInfo.attrInfo"
                    :src="item.productInfo.attrInfo.image"
                    mode="aspectFill"
                  ></image
                  ><image
                    v-else
                    :src="item.productInfo.image"
                    mode="aspectFill"
                  ></image
                ></view>
                <view class="goods-info">
                  <view
                    class="name line2"
                    :class="{ gray: !item.attrStatus }"
                    >{{ item.productInfo.store_name }}</view
                  >
                  <view class="sku line1" v-if="item.productInfo.attrInfo"
                    >{{ $t(`属性`) }}：{{ item.productInfo.attrInfo.suk }}</view
                  >
                  <view class="price" v-if="item.attrStatus"
                    >{{ $t(`￥`) }}{{ item.truePrice }}</view
                  >
                  <view class="reselect" v-else
                    ><text class="tip">{{ $t(`请重新选择商品规格`) }}</text
                    ><view class="btn" @click.stop="reElection(item)">{{
                      $t(`重选`)
                    }}</view></view
                  >
                </view>
                <view v-if="item.attrStatus" class="qty-ctrl">
                  <view
                    class="qty-btn reduce"
                    :class="{ off: item.numSub }"
                    @click.stop="subCart(index)"
                    >-</view
                  >
                  <view class="qty-num"
                    ><input
                      type="number"
                      v-model="item.cart_num"
                      @click.stop
                      @input="iptCartNum(index)"
                      @blur="blurInput(index)"
                  /></view>
                  <view
                    class="qty-btn plus"
                    :class="{ off: item.numAdd && !disabledChangeNumber }"
                    @click.stop="addCart(index)"
                    >+</view
                  >
                </view>
              </navigator>
            </view>
          </checkbox-group>
        </view>
        <view class="invalid-section" v-if="cartList.invalid.length">
          <view class="invalid-header">
            <view class="header-left" @click="goodsOpen"
              ><text
                class="iconfont"
                :class="goodsHidden ? 'icon-xiangxia' : 'icon-xiangshang'"
              ></text
              ><text>{{ $t(`失效商品`) }}</text></view
            >
            <view class="header-right" @click="unsetCart"
              ><text class="iconfont icon-shanchu1"></text
              ><text>{{ $t(`清空`) }}</text></view
            >
          </view>
          <view class="invalid-list" :class="{ hide: goodsHidden }">
            <view
              v-for="(item, index) in cartList.invalid"
              :key="index"
              class="invalid-item"
            >
              <view class="invalid-tag">{{ $t(`失效`) }}</view>
              <view class="goods-img"
                ><image
                  v-if="item.productInfo.attrInfo"
                  :src="item.productInfo.attrInfo.image"
                  mode="aspectFill"
                ></image
                ><image
                  v-else
                  :src="item.productInfo.image"
                  mode="aspectFill"
                ></image
              ></view>
              <view class="goods-info"
                ><view class="name line1">{{
                  item.productInfo.store_name
                }}</view
                ><view class="sku line1" v-if="item.productInfo.attrInfo"
                  >{{ $t(`属性`) }}：{{ item.productInfo.attrInfo.suk }}</view
                ><view class="tip-text">{{ $t(`该商品已失效`) }}</view></view
              >
            </view>
          </view>
        </view>
        <view class="load-more" v-if="cartList.invalid.length && loadend"
          ><text
            class="loading iconfont icon-jiazai"
            :hidden="!loadingInvalid"
          ></text
          ><text>{{ loadTitleInvalid }}</text></view
        >
      </view>
      <view class="empty-cart" v-else>
        <view class="empty-box"
          ><image
            :src="imgHost + '/statics/images/no-thing.png'"
            mode="aspectFit"
          ></image
          ><view class="tips">{{ $t(`暂无商品`) }}</view></view
        >
        <recommend
          v-if="hostProduct.length"
          :hostProduct="hostProduct"
        ></recommend>
      </view>
      <view class="bottom-space" :style="medHeight"></view>
      <view
        class="footer-bar"
        v-if="cartList.valid.length"
        :class="{ active: is_diy && is_diy_set }"
        :style="componentStyle"
      >
        <view class="bar-left"
          ><checkbox-group @change="checkboxAllChange"
            ><checkbox
              value="all"
              :checked="!!isAllSelect"
              backgroundColor="#ffffff"
              activeBackgroundColor="var(--view-theme, #E93323)"
              activeBorderColor="var(--view-theme, #E93323)"
              iconColor="#ffffff"
            /><text
              >{{ $t(`全选`) }}({{ selectValue.length }})</text
            ></checkbox-group
          ></view
        >
        <view class="bar-right" v-if="footerswitch"
          ><text class="total">{{ $t(`￥`) }}{{ selectCountPrice }}</text>
          <form @submit="subOrder">
            <button class="submit-btn" formType="submit">
              {{ $t(`立即下单`) }}
            </button>
          </form></view
        >
        <view class="bar-actions" v-else
          ><form @submit="subCollect">
            <button class="action-btn" formType="submit">
              {{ $t(`收藏`) }}
            </button>
          </form>
          <form @submit="subDel">
            <button class="action-btn danger" formType="submit">
              {{ $t(`删除`) }}
            </button>
          </form></view
        >
      </view>
    </view>
    <productWindow
      :attr="attr"
      :isShow="1"
      :iSplus="1"
      :iScart="1"
      @myevent="onMyEvent"
      @ChangeAttr="ChangeAttr"
      @ChangeCartNum="ChangeCartNum"
      @attrVal="attrVal"
      @iptCartNum="iptCartNum"
      @goCat="reGoCat"
      id="product-window"
    ></productWindow>
    <view class="footer-hold"></view>
    <pageFooter @newDataStatus="newDataStatus"></pageFooter>
  </view>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import {
  onLoad,
  onShow,
  onReachBottom,
  onPageScroll,
} from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import {
  getCartList as getCartListApi,
  getCartCounts,
  changeCartNum,
  cartDel,
  getResetCart,
} from "@/api/order.js";
import { getProductHot, collectAll, getProductDetail } from "@/api/store.js";
import { toLogin } from "@/libs/login.js";
import recommend from "@/components/recommend";
import productWindow from "@/components/productWindow";
import pageFooter from "@/components/pageFooter/index.vue";
import { HTTP_REQUEST_URL } from "@/config/app";
import { Throttle } from "@/utils/validate.js";
import util from "@/utils/util.js";
import i18n from "@/utils/lang.js";
import { useColorTheme } from "@/composables/useColorTheme";
import { useAppStore } from "@/store/modules/app";
import { useIndexDataStore } from "@/store/modules/indexData";

defineOptions({ name: "OrderAddCart" });

const { colorStyle } = useColorTheme();
const appStore = useAppStore();
const indexDataStore = useIndexDataStore();
const { isLogin } = storeToRefs(appStore);

// ---------- data ----------
const imgHost = HTTP_REQUEST_URL;
const is_diy = ref(uni.getStorageSync("is_diy"));
const canShow = ref(false);
const cartCount = ref(0);
const goodsHidden = ref(true);
const footerswitch = ref(true);
const hostProduct = ref([]);
const cartList = reactive({ valid: [], invalid: [] });
const isAllSelect = ref(false);
const selectValue = ref([]);
const selectCountPrice = ref(0.0);
const isAuto = ref(false);
const isShowAuth = ref(false);
const hotScroll = ref(false);
const hotPage = ref(1);
const hotLimit = ref(10);
const loading = ref(false);
const loadend = ref(false);
const loadTitle = ref(i18n.t(`我也是有底线的`));
const page = ref(1);
const limit = ref(20);
const loadingInvalid = ref(false);
const loadendInvalid = ref(false);
const loadTitleInvalid = ref(i18n.t(`加载更多`));
const pageInvalid = ref(1);
const limitInvalid = ref(20);
const attr = reactive({ cartAttr: false, productAttr: [], productSelect: {} });
const productValue = ref([]);
const storeInfo = ref({});
const attrValue = ref("");
const attrTxt = ref(i18n.t(`请选择`));
const cartId = ref(0);
const product_id = ref(0);
const sysHeight = ref(0);
const newData = ref({});
const activeRouter = ref("");
const is_diy_set = ref(false);
const adding = ref(false);
const disabledChangeNumber = ref(false);
const isFooter = ref(false);
const btmNum = ref(0);
const navConfig = ref(0);
const pdHeight = ref(0);

// ---------- computed ----------
const componentStyle = computed(() => {
  if (btmNum.value && navConfig.value)
    return { bottom: `${btmNum.value * 2 + 104}rpx` };
  return {};
});
const medHeight = computed(() => {
  const s = { backgroundColor: "#f5f5f5", height: "120rpx" };
  if (btmNum.value) s.height = `${btmNum.value * 2 + 120}rpx`;
  return s;
});

// ---------- 生命周期 ----------
onLoad(() => {
  uni.hideTabBar();
  const routes = getCurrentPages();
  activeRouter.value = "/" + routes[routes.length - 1].route;
});
onShow(() => {
  if (!isLogin.value) toLogin();
  canShow.value = false;
  if (isLogin.value) {
    resetData();
    getHostProduct();
    loadend.value = false;
    page.value = 1;
    cartList.valid = [];
    getCartList(1);
    loadendInvalid.value = false;
    pageInvalid.value = 1;
    cartList.invalid = [];
    getInvalidList();
    goodsHidden.value = true;
    footerswitch.value = true;
    cartCount.value = 0;
    isShowAuth.value = false;
  } else {
    hotPage.value = 1;
    hostProduct.value = [];
    hotScroll.value = false;
    getHostProduct();
    canShow.value = true;
  }
});
onReachBottom(() => {
  if (loadend.value) getInvalidList();
  if (cartList.valid.length === 0 && cartList.invalid.length === 0)
    getHostProduct();
});
onPageScroll(() => {
  uni.$emit("scroll");
});

// ---------- methods ----------
function resetData() {
  hostProduct.value = [];
  hotScroll.value = false;
  hotPage.value = 1;
  hotLimit.value = 10;
  cartList.valid = [];
  cartList.invalid = [];
  isAllSelect.value = false;
  selectValue.value = [];
  selectCountPrice.value = 0.0;
}
function authColse(e) {
  isShowAuth.value = e;
}
function newDataStatus(val, num, btmNumParam, navConfigParam) {
  isFooter.value = !!val;
  pdHeight.value = num;
  btmNum.value = btmNumParam;
  navConfig.value = navConfigParam;
}
function reGoCat() {
  const productSelect = productValue.value[attrValue.value];
  if (attr.productAttr.length && productSelect === undefined)
    return util.Tips({ title: i18n.t(`产品库存不足，请选择其它`) });
  const q = {
    id: cartId.value,
    product_id: product_id.value,
    num: attr.productSelect.cart_num,
    unique: attr.productSelect?.unique || "",
  };
  getResetCart(q)
    .then(() => {
      attr.cartAttr = false;
      util.Tips({
        title: i18n.t(`添加购物车成功`),
        success: () => {
          loadend.value = false;
          page.value = 1;
          cartList.valid = [];
          getCartList();
          getCartNum();
        },
      });
    })
    .catch((res) => {
      util.Tips({ title: res.msg });
    });
}
function onMyEvent() {
  attr["cartAttr"] = false;
}
function reElection(item) {
  getGoodsDetails(item);
}
function getGoodsDetails(item) {
  uni.showLoading({ title: i18n.t(`加载中`), mask: true });
  cartId.value = item.id;
  product_id.value = item.product_id;
  getProductDetail(item.product_id)
    .then((res) => {
      uni.hideLoading();
      attr.cartAttr = true;
      storeInfo.value = res.data.storeInfo;
      attr["productAttr"] = res.data.productAttr;
      productValue.value = res.data.productValue;
      DefaultSelect();
    })
    .catch(() => {
      uni.hideLoading();
    });
}
function ChangeAttr(res) {
  const productSelect = productValue.value[res];
  if (productSelect && productSelect.stock > 0) {
    attr.productSelect["image"] = productSelect.image;
    attr.productSelect["price"] = productSelect.price;
    attr.productSelect["stock"] = productSelect.stock;
    attr.productSelect["unique"] = productSelect.unique;
    attr.productSelect["cart_num"] = 1;
    attrValue.value = res;
    attrTxt.value = i18n.t(`已选择`);
  } else {
    attr.productSelect["image"] = storeInfo.value.image;
    attr.productSelect["price"] = storeInfo.value.price;
    attr.productSelect["stock"] = 0;
    attr.productSelect["unique"] = "";
    attr.productSelect["cart_num"] = 0;
    attrValue.value = "";
    attrTxt.value = i18n.t(`请选择`);
  }
}
function DefaultSelect() {
  const productAttr = attr.productAttr;
  let value = [];
  for (const key in productValue.value) {
    if (productValue.value[key].stock > 0) {
      value = productAttr.length ? key.split(",") : [];
      break;
    }
  }
  for (let i = 0; i < productAttr.length; i++)
    productAttr[i]["index"] = value[i];
  const productSelect = productValue.value[value.sort().join(",")];
  if (productSelect && productAttr.length)
    initSelect(
      storeInfo.value.store_name,
      productSelect.image,
      productSelect.price,
      productSelect.stock,
      productSelect.unique,
      1,
      value.sort().join(",")
    );
  else if (!productSelect && productAttr.length)
    initSelect(
      storeInfo.value.store_name,
      storeInfo.value.image,
      storeInfo.value.price,
      0,
      "",
      0,
      ""
    );
  else if (!productSelect && !productAttr.length)
    initSelect(
      storeInfo.value.store_name,
      storeInfo.value.image,
      storeInfo.value.price,
      storeInfo.value.stock,
      storeInfo.value.unique || "",
      1,
      ""
    );
}
function initSelect(name, image, price, stock, unique, num, attrValueParam) {
  attr.productSelect["store_name"] = name;
  attr.productSelect["image"] = image;
  attr.productSelect["price"] = price;
  attr.productSelect["stock"] = stock;
  attr.productSelect["unique"] = unique;
  attr.productSelect["cart_num"] = num;
  attrValue.value = attrValueParam;
  attrTxt.value = attrValueParam ? i18n.t(`已选择`) : i18n.t(`请选择`);
}
function attrVal(val) {
  attr.productAttr[val.indexw]["index"] =
    attr.productAttr[val.indexw].attr_values[val.indexn];
}
function ChangeCartNum(changeValue) {
  let productSelect = productValue.value[attrValue.value];
  if (productSelect === undefined && !attr.productAttr.length)
    productSelect = attr.productSelect;
  if (productSelect === undefined) return;
  const stock = productSelect.stock || 0;
  const num = attr.productSelect;
  if (changeValue) {
    num.cart_num++;
    if (num.cart_num > stock)
      attr.productSelect["cart_num"] = stock || 1;
  } else {
    num.cart_num--;
    if (num.cart_num < 1) attr.productSelect["cart_num"] = 1;
  }
}
function subDel() {
  if (selectValue.value.length > 0)
    cartDel(selectValue.value).then(() => {
      loadend.value = false;
      page.value = 1;
      cartList.valid = [];
      getCartList();
      getCartNum();
    });
  else util.Tips({ title: i18n.t(`请选择产品`) });
}
function subCollect() {
  if (selectValue.value.length > 0) {
    const ids = cartList.valid
      .filter((i) => selectValue.value.includes(i.id.toString()))
      .map((i) => i.product_id)
      .join(",");
    collectAll(ids)
      .then((res) => {
        util.Tips({ title: res.msg, icon: "success" });
      })
      .catch((err) => {
        util.Tips({ title: err });
      });
  } else util.Tips({ title: i18n.t(`请选择产品`) });
}
function subOrder() {
  if (selectValue.value.length > 0) {
    // 结算前校验：选中商品的产品类型必须一致，不同产品类型需分开下单
    // 避免跳转到确认页才被后端拦截，导致页面报错
    const valid = cartList.valid;
    const types = [];
    for (const item of valid) {
      if (selectValue.value.includes(item.id.toString())) {
        const t =
          (item.productInfo && item.productInfo.product_type) || "normal";
        if (!types.includes(t)) types.push(t);
      }
    }
    if (types.length > 1) {
      util.Tips({
        title: i18n.t(`不同产品类型的商品请分开下单`),
      });
      return;
    }
    uni.navigateTo({
      url:
        "/pages/center/order/create/index?cartId=" +
        selectValue.value.join(","),
    });
  } else util.Tips({ title: i18n.t(`请选择产品`) });
}
function checkboxAllChange(e) {
  setAllSelectValue(e.detail.value.length > 0 ? 1 : 0);
}
function setAllSelectValue(status) {
  const selectValueLocal = [];
  const valid = cartList.valid;
  if (valid.length > 0) {
    const newValid = valid.map((item) => {
      if (status) {
        if (footerswitch.value) {
          if (item.attrStatus) {
            item.checked = true;
            selectValueLocal.push(item.id.toString());
          } else item.checked = false;
        } else {
          item.checked = true;
          selectValueLocal.push(item.id.toString());
        }
        isAllSelect.value = true;
      } else {
        item.checked = false;
        isAllSelect.value = false;
      }
      return item;
    });
    cartList["valid"] = newValid;
    selectValue.value = selectValueLocal;
    switchSelect();
  }
}
function checkboxChange(e) {
  const value = e.detail.value;
  const valid = cartList.valid;
  // 勾选时校验：选中的商品产品类型必须一致，不同产品类型需分开下单
  const selectedItems = valid.filter((item) =>
    value.includes(item.id.toString())
  );
  const types = [];
  selectedItems.forEach((item) => {
    const t =
      (item.productInfo && item.productInfo.product_type) || "normal";
    if (!types.includes(t)) types.push(t);
  });
  if (types.length > 1) {
    util.Tips({
      title: i18n.t(`不同产品类型的商品请分开下单`),
    });
    // 取消本次新勾选的项，恢复到原选中状态，避免选中不一致类型
    const newValid = valid.map((item) => {
      if (
        value.includes(item.id.toString()) &&
        !selectValue.value.includes(item.id.toString())
      ) {
        item.checked = false;
      }
      return item;
    });
    cartList["valid"] = newValid;
    return;
  }
  const arr1 = [],
    arr2 = [];
  let arr3 = [];
  const newValid = valid.map((item) => {
    if (value.includes(item.id.toString())) {
      if (footerswitch.value) {
        if (item.attrStatus) {
          item.checked = true;
          arr1.push(item);
        } else item.checked = false;
      } else {
        item.checked = true;
        arr1.push(item);
      }
    } else {
      item.checked = false;
      arr2.push(item);
    }
    return item;
  });
  if (footerswitch.value) arr3 = arr2.filter((item) => !item.attrStatus);
  cartList["valid"] = newValid;
  isAllSelect.value = newValid.length === arr1.length + arr3.length;
  selectValue.value = value;
  switchSelect();
}
function switchSelect() {
  let selectCountPriceLocal = 0.0;
  if (selectValue.value.length > 0) {
    for (const item of cartList.valid) {
      if (selectValue.value.includes(item.id.toString()))
        selectCountPriceLocal = util.$h.Add(
          selectCountPriceLocal,
          util.$h.Mul(item.cart_num, item.truePrice)
        );
    }
  }
  selectCountPrice.value = selectCountPriceLocal;
}
function iptCartNum(index) {
  const item = cartList.valid[index];
  if (item.cart_num) setCartNum(item.id, item.cart_num);
  if (!item.cart_num) item.cart_num = 1;
  switchSelect();
}
function blurInput(index) {
  const item = cartList.valid[index];
  if (!item.cart_num) {
    item.cart_num = 1;
    cartList["valid"] = cartList.valid;
  }
}
function subCart(index) {
  if (disabledChangeNumber.value) return;
  const item = cartList.valid[index];
  let cart_num = Number(item.cart_num) - 1;
  if (cart_num < 1 || cart_num < item.min_qty) {
    uni.showModal({
      title: i18n.t(`提示`),
      content: i18n.t(`确定删除吗？`),
      success: (res) => {
        if (res.confirm) {
          cartCount.value -= item.cart_num;
          cartDel([item.id]).then(() => {
            cartList.valid.splice(index, 1);
            indexDataStore.setCartNum(cartCount.value);
          });
          selectValue.value = selectValue.value.filter(
            (i) => i !== item.id.toString()
          );
          switchSelect();
        }
      },
    });
  } else {
    item.cart_num = cart_num <= 1 ? 1 : cart_num;
    item.numSub = cart_num <= 1;
    item.numAdd = false;
    setCartNum(
      item.id,
      item.cart_num,
      () => {
        cartList.valid[index] = item;
        getCartNum();
        switchSelect();
      },
      () => {
        item.cart_num = Number(item.cart_num) + 1;
      }
    );
  }
}
function addCart(index) {
  if (adding.value || disabledChangeNumber.value) return;
  const item = cartList.valid[index];
  item.cart_num = Number(item.cart_num) + 1;
  const productInfo = item.productInfo;
  if (
    productInfo.hasOwnProperty("attrInfo") &&
    item.cart_num >= productInfo.attrInfo.stock
  ) {
    item.cart_num = productInfo.attrInfo.stock;
    item.numAdd = true;
    item.numSub = false;
  } else {
    item.numAdd = false;
    item.numSub = false;
  }
  Throttle(
    setCartNum(
      item.id,
      item.cart_num,
      () => {
        cartList.valid[index] = item;
        getCartNum();
        switchSelect();
      },
      () => {
        item.cart_num = Number(item.cart_num) - 1;
      }
    ),
    3000
  );
}
function setCartNum(cartId, cartNum, successCallback, errorCallback) {
  if (disabledChangeNumber.value) return;
  disabledChangeNumber.value = true;
  changeCartNum(cartId, cartNum)
    .then((res) => {
      successCallback?.(res.data);
    })
    .catch(() => {
      errorCallback?.();
    })
    .finally(() => {
      setTimeout(() => {
        disabledChangeNumber.value = false;
      }, 0); // 原 DEBOUNCETIME 从未在 config/app.js 定义（undefined 等效 0ms）
    });
}
function getCartNum() {
  getCartCounts().then((res) => {
    cartCount.value = res.data.count;
    adding.value = false;
    indexDataStore.setCartNum(
      res.data.count > 99 ? ".." : res.data.count
    );
    if (res.data.count > 0)
      uni.setTabBarBadge({ index: 2, text: res.data.count + "" }); // wx.* 为小程序专属，H5 下抛错（JSSDK 无此 API），改 uni.* 跨端安全
    else uni.hideTabBarRedDot({ index: 2 });
  });
}
function getCartData(data) {
  return new Promise((resolve, reject) => {
    getCartListApi(data)
      .then((res) => resolve(res.data))
      .catch((err) => {
        loading.value = false;
        canShow.value = true;
        util.Tips({ title: err });
      });
  });
}
async function getCartList(init) {
  uni.showLoading({ title: i18n.t(`加载中`), mask: true });
  const data = { page: page.value, limit: limit.value, status: 1 };
  getCartCounts().then(async (c) => {
    cartCount.value = c.data.count;
    if (init) {
      adding.value = false;
      indexDataStore.setCartNum(
        c.data.count > 99 ? ".." : c.data.count
      );
      if (c.data.count > 0)
        uni.setTabBarBadge({ index: 2, text: c.data.count + "" });
      else uni.hideTabBarRedDot({ index: 2 });
    }
    for (let i = 0; i < Math.ceil(c.data.ids.length / limit.value); i++) {
      const cartListData = await getCartData(data);
      data.page++;
      let validList = util.SplitArray(
        cartListData.valid,
        cartList.valid
      );
      const selectValueLocal = [];
      if (validList.length > 0) {
        for (const item of validList) {
          item.numSub = item.cart_num == 1;
          const productInfo = item.productInfo;
          if (
            productInfo.hasOwnProperty("attrInfo") &&
            item.cart_num == productInfo.attrInfo.stock
          )
            item.numAdd = true;
          else if (item.cart_num == productInfo.stock) item.numAdd = true;
          else item.numAdd = false;
          if (item.attrStatus) {
            item.checked = true;
            selectValueLocal.push(item.id.toString());
          } else item.checked = false;
        }
      }
      cartList["valid"] = validList;
      selectValue.value = selectValueLocal;
      const newArr = validList.filter((item) => item.attrStatus);
      isAllSelect.value =
        newArr.length === selectValueLocal.length && newArr.length;
      switchSelect();
    }
    loading.value = false;
    canShow.value = true;
    uni.hideLoading();
  });
}
function getInvalidList() {
  if (loadendInvalid.value || loadingInvalid.value) return;
  const data = {
    page: pageInvalid.value,
    limit: limitInvalid.value,
    status: 0,
  };
  getCartListApi(data)
    .then((res) => {
      const { invalid } = res.data;
      const loadendInvalidLocal = invalid.length < limitInvalid.value;
      const invalidList = util.SplitArray(
        invalid,
        cartList.invalid
      );
      cartList["invalid"] = invalidList;
      loadendInvalid.value = loadendInvalidLocal;
      loadTitleInvalid.value = loadendInvalidLocal
        ? i18n.t(`我也是有底线的`)
        : i18n.t(`加载更多`);
      pageInvalid.value++;
      loadingInvalid.value = false;
    })
    .catch(() => {
      loadingInvalid.value = false;
      loadTitleInvalid.value = i18n.t(`加载更多`);
    });
}
function getHostProduct() {
  if (hotScroll.value) return;
  getProductHot(hotPage.value, hotLimit.value).then((res) => {
    hotPage.value++;
    hotScroll.value = res.data.length < hotLimit.value;
    hostProduct.value = hostProduct.value.concat(res.data);
  });
}
function goodsOpen() {
  goodsHidden.value = !goodsHidden.value;
}
function manage() {
  footerswitch.value = !footerswitch.value;
  const arr1 = [],
    arr2 = [];
  const newValid = cartList.valid.map((item) => {
    if (footerswitch.value) {
      if (item.attrStatus) {
        if (item.checked) arr1.push(item.id.toString());
      } else {
        item.checked = false;
        arr2.push(item);
      }
    } else {
      if (item.checked) arr1.push(item.id.toString());
    }
    return item;
  });
  cartList.valid = newValid;
  isAllSelect.value = footerswitch.value
    ? newValid.length === arr1.length + arr2.length
    : newValid.length === arr1.length;
  selectValue.value = arr1;
  switchSelect();
}
function unsetCart() {
  const ids = cartList.invalid.map((item) => item.id);
  cartDel(ids)
    .then(() => {
      util.Tips({ title: i18n.t(`清除成功`) });
      cartList["invalid"] = [];
      getCartNum();
    })
    .catch(() => {});
}
</script>

<style scoped lang="scss">
.order-addcart {
  min-height: 100vh;
  background: #f5f5f5;
}
.guarantee-bar {
  display: flex;
  justify-content: space-around;
  align-items: center;
  height: 76rpx;
  padding: 0 30rpx;
  font-size: 22rpx;
  color: #8c8c8c;
  background: #f5f5f5;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 10;
  box-sizing: border-box;
  .iconfont {
    font-size: 25rpx;
    margin-right: 8rpx;
  }
}
.main-content {
  padding-top: 76rpx;
}
.nav-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 80rpx;
  padding: 0 30rpx;
  font-size: 28rpx;
  color: #282828;
  background: #fff;
  position: fixed;
  top: 76rpx;
  left: 0;
  right: 0;
  z-index: 10;
  box-sizing: border-box;
  .count {
    margin-left: 12rpx;
  }
  .nav-right {
    font-size: 26rpx;
    padding: 0 26rpx;
    height: 52rpx;
    line-height: 52rpx;
    border-radius: 26rpx;
    background: #f5f5f7;
    color: #666;
    &.managing {
      background: var(--view-op-ten, rgba(233, 51, 35, 0.1));
      color: var(--view-theme, #e93323);
    }
  }
}
.goods-wrapper {
  margin-top: 90rpx;
}
.valid-list {
  .goods-item {
    display: flex;
    align-items: center;
    padding: 25rpx 30rpx;
    background: #fff;
    margin-bottom: 15rpx;
    .goods-main {
      flex: 1;
      min-width: 0;
      display: flex;
      align-items: flex-start;
      margin-left: 20rpx;
      position: relative;
    }
    .goods-img {
      width: 160rpx;
      height: 160rpx;
      flex-shrink: 0;
      border-radius: 6rpx;
      overflow: hidden;
      image {
        width: 100%;
        height: 100%;
      }
    }
    .goods-info {
      flex: 1;
      min-width: 0;
      margin-left: 20rpx;
      font-size: 28rpx;
      color: #282828;
      .name {
        line-height: 1.4;
        &.gray {
          color: #999;
        }
      }
      .sku {
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 24rpx;
        color: #868686;
        margin-top: 16rpx;
      }
      .price {
        font-size: 32rpx;
        color: var(--view-theme);
        margin-top: 28rpx;
      }
      .reselect {
        margin-top: 20rpx;
        display: flex;
        align-items: center;
        justify-content: space-between;
        .tip {
          font-size: 24rpx;
          color: #999;
        }
        .btn {
          padding: 0 20rpx;
          height: 46rpx;
          line-height: 46rpx;
          background: var(--view-theme);
          color: #fff;
          border-radius: 23rpx;
          font-size: 26rpx;
        }
      }
    }
    .qty-ctrl {
      position: absolute;
      bottom: 0;
      right: 0;
      display: flex;
      align-items: center;
      background: #f5f5f7;
      border-radius: 24rpx;
      overflow: hidden;

      .qty-btn {
        width: 56rpx;
        height: 48rpx;
        line-height: 48rpx;
        text-align: center;
        font-size: 32rpx;
        color: #282828;
        &.off {
          color: #d0d0d0;
        }
      }

      .qty-num {
        width: 64rpx;
        height: 48rpx;
        line-height: 48rpx;
        text-align: center;
        color: #282828;
        font-size: 28rpx;
        input {
          width: 100%;
          height: 100%;
          text-align: center;
          font-size: 28rpx;
        }
      }
    }
  }
}
.invalid-section {
  background: #fff;
  margin-top: 15rpx;
  .invalid-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 66rpx;
    padding: 0 30rpx;
    font-size: 28rpx;
    color: #282828;
    .header-left .iconfont {
      margin-right: 17rpx;
      font-size: 28rpx;
    }
    .header-right {
      font-size: 26rpx;
      color: #999;
      .iconfont {
        font-size: 33rpx;
        margin-right: 8rpx;
        vertical-align: -2rpx;
      }
    }
  }
  .invalid-list {
    &.hide {
      display: none;
    }
    .invalid-item {
      display: flex;
      align-items: center;
      padding: 20rpx 30rpx;
      border-top: 1rpx solid #f5f5f5;
      .invalid-tag {
        width: 70rpx;
        height: 36rpx;
        line-height: 36rpx;
        text-align: center;
        background: #aaa;
        color: #fff;
        font-size: 22rpx;
        border-radius: 3rpx;
        flex-shrink: 0;
      }
      .goods-img {
        width: 140rpx;
        height: 140rpx;
        margin: 0 20rpx;
        border-radius: 6rpx;
        overflow: hidden;
        image {
          width: 100%;
          height: 100%;
        }
      }
      .goods-info {
        flex: 1;
        min-width: 0;
        font-size: 28rpx;
        color: #999;
        height: 140rpx;
        .name {
          width: 100%;
        }
        .sku {
          width: 100%;
          overflow: hidden;
          text-overflow: ellipsis;
          white-space: nowrap;
          font-size: 24rpx;
        }
        .tip-text {
          font-size: 26rpx;
          color: #bbb;
        }
      }
    }
  }
}
.load-more {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 80rpx;
  font-size: 24rpx;
  color: #999;
  .loading {
    margin-right: 10rpx;
    animation: rotate 1s linear infinite;
  }
}
@keyframes rotate {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
.empty-cart {
  margin-top: 171rpx;
  background: #fff;
  .empty-box {
    text-align: center;
    padding: 80rpx 0;
    .tips {
      color: #aaa;
      font-size: 26rpx;
    }
    image {
      width: 414rpx;
      height: 304rpx;
    }
  }
}
.footer-bar {
  z-index: 999;
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 96rpx;
  padding: 0 30rpx;
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(10px);
  position: fixed;
  left: 0;
  right: 0;
  border-top: 1rpx solid #eee;
  bottom: 98rpx;
  bottom: calc(98rpx + constant(safe-area-inset-bottom));
  bottom: calc(98rpx + env(safe-area-inset-bottom));
  &.active {
    bottom: 0;
  }
  .bar-left {
    font-size: 28rpx;
    color: #282828;
    text {
      margin-left: 16rpx;
    }
  }
  .bar-right {
    display: flex;
    align-items: center;
    .total {
      font-size: 30rpx;
    }
    .submit-btn {
      color: #fff;
      font-size: 30rpx;
      width: 226rpx;
      height: 70rpx;
      border-radius: 35rpx;
      text-align: center;
      line-height: 70rpx;
      margin-left: 22rpx;
      background: var(--view-theme);
      border: none;
    }
  }
  .bar-actions {
    display: flex;
    align-items: center;
    .action-btn {
      font-size: 28rpx;
      color: #999;
      border: 1rpx solid #999;
      border-radius: 35rpx;
      width: 160rpx;
      height: 60rpx;
      text-align: center;
      line-height: 60rpx;
      background: transparent;
      margin-left: 17rpx;
      &.danger {
        border-color: var(--view-theme);
        color: var(--view-theme);
      }
    }
  }
}
.footer-hold {
  height: 100rpx;
  height: calc(100rpx + constant(safe-area-inset-bottom));
  height: calc(100rpx + env(safe-area-inset-bottom));
}
</style>
