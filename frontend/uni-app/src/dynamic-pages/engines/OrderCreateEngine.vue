<!--
  订单确认页引擎（order_create）
  负责数据加载、价格计算、订单提交等状态管理
  UI 区块拆分至 components/order/ 下独立组件
-->
<template>
  <view class="order-page" :style="colorStyle">
    <!-- 加载状态 -->
    <view class="loading-box" v-if="loading">
      <view class="loading-text">{{ $t('加载中...') }}</view>
    </view>

    <!-- 加载失败提示 -->
    <view class="loading-box" v-if="!loading && errorMsg">
      <view class="loading-text error">{{ errorMsg }}</view>
      <view class="retry-btn" @tap="loadConfirmData">{{ $t('重新加载') }}</view>
    </view>

    <template v-if="!loading && !errorMsg">
      <!-- 配送方式 + 收货地址，由 express_shipping / store_pickup / door_service 驱动；送礼订单由领取人填地址 -->
      <DeliverySection
        v-if="!isGift"
        v-model="deliveryType"
        :express-shipping="features.express_shipping"
        :store-pickup="features.store_pickup"
        :door-service="features.door_service"
        :door-postage="features.door_service_postage"
        :address-info="addressInfo"
        :store-info="storeInfo"
        :service-info="serviceInfo"
        @change="onDeliveryTypeChange"
        @address-click="onAddress"
        @pickup-click="onPickupClick"
        @service-change="onServiceChange"
      />

      <!-- 商品信息（购物车模式多商品循环展示，点击跳转商品详情页） -->
      <view
        v-for="(item, idx) in cartInfo"
        :key="idx"
        class="product-card-wrap"
        hover-class="product-card-hover"
        @click="goProductDetail(item)"
      >
        <OrderProductCard
          :image="(item.productInfo || {}).image"
          :name="(item.productInfo || {}).store_name"
          :sku-label="getSkuLabel(item)"
          :price="item.truePrice"
          :integral-price="isIntegral && idx === 0 ? integralUnitPrice : 0"
          :num="item.cart_num"
        />
      </view>

      <!-- display_sections block 选中信息（日期/场次/票种等），由详情页透传 -->
      <OrderBlockInfo :block-data="selectedBlockData" />

      <!-- 优惠信息（优惠券 + 积分） -->
      <OrderDiscountPanel
        :features="features"
        :coupon-title="couponTitle"
        :use-integral="useIntegral"
        :usable-integral-display="usableIntegralDisplay"
        :is-integral="isIntegral"
        @coupon-tap="couponTap"
        @toggle-integral="toggleIntegral"
      />

      <!-- 自定义表单 -->
      <OrderCustomForm
        ref="customFormRef"
        v-model="customFormData"
        :fields="customForm"
        :visible="features.custom_form"
      />

      <!-- 游客信息表单（门票专属，按数量倍增） -->
      <OrderVisitorForm
        ref="visitorFormRef"
        :visible="features.visitor_form"
        :quantity="visitorQuantity"
        :fields="visitorFormConfig"
      />

      <!-- 订单备注 / 送礼留言 -->
      <OrderRemarkInput
        v-if="!isGift"
        v-model="remark"
        :visible="features.remark"
      />
      <view v-if="isGift" class="gift-mark-card">
        <view class="gift-mark-label">{{ $t('礼物留言') }}</view>
        <input
          v-model="giftMark"
          class="gift-mark-input"
          type="text"
          maxlength="40"
          :placeholder="$t('填写好友留言，最多40字')"
        />
      </view>

      <!-- 发票信息（抬头选择模式，编辑/保存走原 user_invoice_form 页面） -->
      <OrderInvoicePanel
        :visible="features.invoice"
        :list="invoiceList"
        :checked="invoiceId"
        :special-invoice="specialInvoice"
        :url-query="invoiceUrlQuery"
        v-model:popup-show="invoicePopupShow"
        @open="onInvoiceOpen"
        @close="onInvoiceClose"
        @change="onInvoiceChange"
        @cancel="onInvoiceCancel"
      />

      <!-- 价格明细 -->
      <OrderPriceDetail
        :total-price="totalPriceText"
        :pay-postage="payPostage"
        :level-price="levelPrice"
        :member-price="memberPrice"
        :postage-discount="postageDiscount"
        :coupon-price="couponPrice"
        :integral-price="integralPrice"
        :integral-total="integralTotal"
        :usable-integral="usableIntegral"
        :gift-price="giftPrice"
        :show-postage="!isGift && features.express_shipping && deliveryType === 'delivery'"
        :door-postage="features.door_service_postage"
        :show-door-postage="!isGift && features.door_service && deliveryType === 'door'"
      />

      <!-- 底部提交栏 -->
      <OrderSubmitBar
        :pay-price="payPriceText"
        :integral-total="integralTotal"
        :submitting="submitting"
        :disabled="submitDisabled"
        @submit="handleConfirm"
      />
    </template>

    <!-- 优惠券弹窗 -->
    <couponListWindow
      :coupon="coupon"
      :openType="1"
      @ChangCouponsClone="ChangCouponsClone"
      @ChangCoupons="ChangCoupons"
      @changeTextareaStatus="changeTextareaStatus"
    />

    <!-- 地址选择弹窗（组件内自带遮罩层，无需额外 mask） -->
    <addressWindow
      ref="addressWindowRef"
      :news="1"
      :address="address"
      :pagesUrl="pagesUrl"
      @OnChangeAddress="OnChangeAddress"
      @changeClose="changeClose"
      @changeTextareaStatus="changeTextareaStatus"
      @onHaveAddressList="onHaveAddressList"
    />
  </view>
</template>

<script setup>
import { ref, computed, onBeforeUnmount } from 'vue';
import {
  getCenterOrderConfirm,
  postCenterOrderComputed,
  submitOrder,
} from '@/api/order-center.js';
import { orderPay } from '@/api/order.js';
import { getAddressDefault, getAddressDetail, invoiceList as invoiceListApi } from '@/api/user.js';
import { storeListApi } from '@/api/store.js';
import routes from '../api/routes.js';
import addressWindow from '@/components/addressWindow/index.vue';
import couponListWindow from '@/components/couponListWindow/index.vue';
import DeliverySection from '../components/order/create/DeliverySection.vue';
import OrderProductCard from '../components/order/common/OrderProductCard.vue';
import OrderBlockInfo from '../components/order/common/OrderBlockInfo.vue';
import OrderDiscountPanel from '../components/order/create/OrderDiscountPanel.vue';
import OrderCustomForm from '../components/order/create/OrderCustomForm.vue';
import OrderVisitorForm from '../components/order/create/OrderVisitorForm.vue';
import OrderRemarkInput from '../components/order/create/OrderRemarkInput.vue';
import OrderInvoicePanel from '../components/order/create/OrderInvoicePanel.vue';
import OrderPriceDetail from '../components/order/common/OrderPriceDetail.vue';
import OrderSubmitBar from '../components/order/create/OrderSubmitBar.vue';
import { useColorTheme } from '@/composables/useColorTheme';
import i18n from '@/utils/lang.js';
import { openPaySubscribe } from '@/utils/subscribeMessage.js';
import {openOrderCreateSubscribe} from "../../utils/subscribeMessage";

defineOptions({ name: 'OrderCreateEngine' });

const { colorStyle } = useColorTheme();

// ===== data =====
// 路由参数
const productId = ref('');
const skuUnique = ref('');
const submitApi = ref('center/order/create');
const productType = ref('normal');
const activityType = ref('');
const activityId = ref(0);
// 活动订单信息（含免费支付方式标识 free_pay_type 与跳转地址 pay_redirect_url）
const activityInfo = ref({});
const num = ref(1);
// 购物车模式参数（携带 cartId 时走多商品合并下单）
const cartId = ref('');
const news = ref(0);
const isCartMode = ref(false);
// 送礼订单：1=购买礼物（不填地址/不计运费），2=领取礼物
const isGift = ref(0);
const giftMark = ref('');
const giftPrice = ref(0);
// 详情页 display_sections block 选中数据（票种/场次/日期），通过 URL block_data 参数透传
const selectedBlockData = ref([]);

// 加载
const loading = ref(true);
const errorMsg = ref('');

// 商品数据
const cartInfo = ref([]);
const orderKey = ref('');
const totalPrice = ref(0);
const payPrice = ref(0);
const payPostage = ref(0);
// 价格明细中的会员优惠，确认接口使用 camelCase，重算接口使用 snake_case。
const levelPrice = ref(0);
const memberPrice = ref(0);
const postageDiscount = ref(0);
const priceComputing = ref(false);
const priceReady = ref(false);
const priceError = ref('');
let priceRequestSerial = 0;
const initialized = ref(false);
const features = ref({
  express_shipping: true,  // 是否支持快递配送
  store_pickup: true,      // 是否支持到店自提
  door_service: false,     // 是否支持上门服务
  remark: true,
  invoice: true,
  special_invoice: true,
  integral: false,
  coupon: false,
  custom_form: false,
  visitor_form: false,     // 游客信息表单（门票专属，按数量倍增）
});

// 积分
const usableIntegral = ref(0);
const surplusIntegral = ref(0);
const integralOpen = ref(false);
const integralRatio = ref(0);
const useIntegral = ref(false);
const integralPrice = ref(0);

// 优惠券
const coupon = ref({
  coupon: false,
  list: [],
  statusTile: i18n.global.t('立即使用'),
});
const couponId = ref(0);
const couponTitle = ref(i18n.global.t('请选择'));
const couponPrice = ref(0);

// 地址
const addressInfo = ref({});
const addressId = ref(0);
const haveAddressList = ref(false);
const address = ref({
  address: false,
  addressId: 0,
});
const pagesUrl = ref('');

// 自定义表单
const customForm = ref([]);
const customFormData = ref({});

// 游客信息表单（门票专属，按数量倍增）
const visitorFormConfig = ref([]);   // 字段配置，由后端 visitor_form_config 下发
const visitorQuantity = ref(1);      // 游客卡片数量，等于购买数量 cart_num

// 配送方式
const deliveryType = ref('delivery');

// 门店信息（自提模式）
const storeInfo = ref({});

// 上门服务预约信息（联系人/电话/地址/预约时间）
const serviceInfo = ref({});

// 订单备注
const remark = ref('');

// 发票（抬头选择模式：编辑/保存走原 user_invoice_form 页面）
const invoiceList = ref([]); // 已保存的抬头列表
const invoiceId = ref(''); // 选中的抬头 ID（字符串）
const invoicePopupShow = ref(false); // 抬头选择弹窗显隐
const specialInvoice = ref(true); // 是否启用专用发票（由 features.special_invoice 控制）

// 提交
const submitting = ref(false);
// 积分兑换总额
const integralTotal = ref(0);

// ===== 模板 ref（子组件实例，供校验/取数调用） =====
const customFormRef = ref(null);
const visitorFormRef = ref(null);
// 模板 ref 名 addressWindowRef：避免与同名组件导入 addressWindow 冲突
const addressWindowRef = ref(null);

// ===== computed =====
/** 是否为积分兑换模式 */
const isIntegral = computed(() => activityType.value === 'integral' && integralTotal.value > 0);
/** 购物车首项，作为积分/单品下单商品信息来源 */
const firstCart = computed(() => (cartInfo.value && cartInfo.value[0]) || {});
/** 积分商品单价 */
const integralUnitPrice = computed(() => {
  if (!isIntegral.value) return 0;
  var num = firstCart.value.cart_num || 1;
  return num > 0 ? Math.floor(integralTotal.value / num) : integralTotal.value;
});
/** 积分展示：使用积分时展示剩余积分，否则展示可用积分 */
const usableIntegralDisplay = computed(() => {
  if (useIntegral.value && surplusIntegral.value) {
    return surplusIntegral.value;
  }
  return usableIntegral.value;
});
const integralCheckDisabled = computed(() => usableIntegral.value <= 0 && !useIntegral.value);
const totalPriceText = computed(() => totalPrice.value || '0.00');
const payPriceText = computed(() => payPrice.value ?? '0.00');
/** 提交禁用：快递配送模式下未选地址，自提模式下未选门店，上门模式下未填完整预约信息；送礼订单不校验收货信息 */
const submitDisabled = computed(() => {
  if (!priceReady.value || priceComputing.value) {
    return true;
  }
  if (isGift.value) {
    return false;
  }
  if (features.value.express_shipping && deliveryType.value === 'delivery') {
    return !addressInfo.value.real_name;
  }
  if (features.value.store_pickup && deliveryType.value === 'pickup') {
    return !storeInfo.value.id;
  }
  if (features.value.door_service && deliveryType.value === 'door') {
    return !serviceInfo.value.contact_name || !serviceInfo.value.contact_phone || !serviceInfo.value.appointment_time;
  }
  return false;
});
/** 跳转 user_invoice_form 时携带的回跳参数（不含 ?），购物车模式携带 cartId 保证回跳后仍为购物车结算 */
const invoiceUrlQuery = computed(() => {
  var query;
  if (isCartMode.value) {
    query =
      'cartId=' + encodeURIComponent(cartId.value) +
      '&new=' + news.value +
      '&address_id=' + addressId.value +
      '&submit_api=' + encodeURIComponent(submitApi.value) +
      '&specialInvoice=' + (specialInvoice.value ? 'true' : 'false');
  } else {
    query =
      'product_id=' + encodeURIComponent(productId.value) +
      '&sku_unique=' + encodeURIComponent(skuUnique.value) +
      '&product_type=' + encodeURIComponent(productType.value) +
      '&activity_type=' + encodeURIComponent(activityType.value) +
      '&activity_id=' + activityId.value +
      '&num=' + num.value +
      '&submit_api=' + encodeURIComponent(submitApi.value) +
      '&specialInvoice=' + (specialInvoice.value ? 'true' : 'false');
  }
  return query;
});

// ===== methods =====
/**
 * 获取单个购物车商品的规格文案：优先 attrInfo.suk，其次 attrInfo.value，最后兜底 productInfo.attrInfo.suk
 * @param {Object} item 购物车商品项
 * @returns {string} 规格文案
 */
function getSkuLabel(item) {
  if (!item) return '';
  var attrInfo = item.attrInfo || {};
  var productInfo = item.productInfo || {};
  if (attrInfo.suk) return attrInfo.suk;
  if (attrInfo.value) return attrInfo.value;
  if (productInfo.attrInfo && productInfo.attrInfo.suk) return productInfo.attrInfo.suk;
  return '';
}
/**
 * 点击商品卡片跳转对应商品详情页
 * @param {Object} item 购物车商品项
 */
function goProductDetail(item) {
  var productInfo = (item && item.productInfo) || {};
  var pid = productInfo.id || productInfo.product_id || (item.product_id) || 0;
  if (!pid) {
    uni.showToast({ title: i18n.global.t('商品信息异常'), icon: 'none' });
    return;
  }
  uni.navigateTo({ url: routes.goodsDetail + '?id=' + pid });
}
/**
 * 加载订单确认数据
 */
async function loadConfirmData() {
  loading.value = true;
  errorMsg.value = '';
  priceRequestSerial += 1;
  priceComputing.value = false;
  priceReady.value = false;
  priceError.value = '';
  payPrice.value = null;
  payPostage.value = null;
  try {
    var params;
    if (isCartMode.value) {
      // 购物车模式：多商品合并下单，后端校验产品类型一致并汇总价格
      params = {
        cartId: cartId.value,
        new: news.value,
        address_id: addressId.value || 0,
        shipping_type: 1,
      };
    } else {
      // 单品直购模式
      params = {
        product_id: productId.value,
        sku_unique: skuUnique.value,
        product_type: productType.value,
        num: num.value,
      };
      if (activityType.value) params.activity_type = activityType.value;
      if (activityId.value) params.activity_id = activityId.value;
    }
    if (isGift.value) params.is_gift = isGift.value;
    var res = await getCenterOrderConfirm(params);

    var data = res.data || {};
    cartInfo.value = data.cartInfo || [];
    orderKey.value = data.order_key || '';
    totalPrice.value = data.total_price || 0;
    payPrice.value = data.pay_price || 0;
    payPostage.value = data.pay_postage || 0;
    // 确认接口返回 priceGroup，不能只使用顶层金额字段，否则等级/会员折扣会丢失。
    var confirmPriceGroup = data.priceGroup || {};
    levelPrice.value = confirmPriceGroup.levelPrice ?? data.level_price ?? 0;
    memberPrice.value = confirmPriceGroup.memberPrice ?? data.member_price ?? 0;
    postageDiscount.value = confirmPriceGroup.postageDiscount ?? data.postage_discount ?? 0;
    giftPrice.value = confirmPriceGroup.giftPrice ?? data.gift_price ?? 0;
    features.value = { ...features.value, ...(data.features || {}) };
    // 送礼订单：后端 features 仍按商品配送能力下发，前端按 is_gift 隐藏地址与运费
    if (isGift.value) {
      features.value = {
        ...features.value,
        express_shipping: false,
        store_pickup: false,
        door_service: false,
      };
    }
    // 保存活动订单信息（含免费支付方式标识与跳转地址，由交易模式 Handler 下发）
    activityInfo.value = data.activity_info || {};
    // 同步发票相关配置：后端未返回时默认开启专用发票
    specialInvoice.value = features.value.special_invoice !== false;
    usableIntegral.value = parseInt(data.usable_integral || 0);
    integralOpen.value = !!data.integral_open;
    integralRatio.value = data.integral_ratio || 0;

    // 设置优惠券列表
    if (data.coupon_list && data.coupon_list.length) {
      coupon.value.list = data.coupon_list;
    }

    // 设置自定义表单
    customForm.value = data.custom_form || [];
    customFormData.value = {};
    if (customForm.value.length) {
      customForm.value.forEach(item => {
        customFormData.value[item.field] = '';
      });
    }

    // 设置游客信息表单（门票专属）
    visitorFormConfig.value = data.visitor_form_config || [];
    // 游客卡片数量 = 购买总数量：
    // 购物车模式累加所有商品 cart_num（每张票对应一位游客），
    // 单品模式取 num 参数
    var visitorQty = isCartMode.value
      ? cartInfo.value.reduce(function (sum, item) {
          return sum + (parseInt(item.cart_num, 10) || 0);
        }, 0)
      : num.value;
    visitorQuantity.value = Math.max(1, parseInt(visitorQty, 10) || 1);

    // 积分兑换订单：从 cartInfo 读取积分总额
    if (activityType.value === 'integral' && data.cartInfo && data.cartInfo[0]) {
      integralTotal.value = data.cartInfo[0].activity_integral || 0;
    }

    // 加载地址：有回跳 addressId 时加载地址详情，否则加载默认地址
    // 购物车模式从地址列表选择后回跳会携带 address_id；送礼订单由领取人填地址，跳过
    if (!isGift.value && (features.value.express_shipping || features.value.store_pickup || features.value.door_service)) {
      if (addressId.value) {
        await refreshAddressInfo();
      } else {
        await loadDefaultAddress();
      }
    }

    // 根据后端返回的功能开关，自动选择默认配送方式
    // 仅支持到店自提时，默认切换为 pickup 并加载门店列表
    if (features.value.store_pickup && !features.value.express_shipping) {
      deliveryType.value = 'pickup';
      await loadDefaultStore();
    } else if (features.value.door_service && !features.value.express_shipping && !features.value.store_pickup) {
      // 仅支持上门服务时，默认切换为 door
      deliveryType.value = 'door';
    } else {
      // 默认快递配送（包括两者都支持或仅支持快递的情况）
      deliveryType.value = 'delivery';
    }

    // 重新计算价格（含运费等，此时默认地址已加载）
    computedPrice();
  } catch (e) {
    errorMsg.value = e.msg || e.message || i18n.global.t('加载失败');
  } finally {
    loading.value = false;
  }
}

// ===== 优惠券方法 =====
/**
 * 打开优惠券选择弹窗
 */
function couponTap() {
  var list = coupon.value.list;
  for (var i = 0; i < list.length; i++) {
    if (list[i].id == couponId.value) {
      list[i].is_use = 1;
    } else {
      list[i].is_use = 0;
    }
  }
  coupon.value['list'] = list;
  coupon.value.coupon = true;
}

/**
 * 关闭优惠券弹窗
 */
function ChangCouponsClone() {
  coupon.value.coupon = false;
}

/**
 * 选中优惠券
 */
function ChangCoupons(e) {
  var index = e;
  var list = coupon.value.list;
  // 局部变量重命名 nextCouponTitle/nextCouponId：避免与同名 ref（couponTitle/couponId）在函数内遮蔽
  var nextCouponTitle = i18n.global.t('请选择');
  var nextCouponId = 0;

  for (var i = 0; i < list.length; i++) {
    if (i != index) {
      list[i].use_title = '';
      list[i].is_use = 0;
    }
  }
  if (list[index].is_use) {
    // 不使用优惠券
    list[index].use_title = '';
    list[index].is_use = 0;
  } else {
    // 使用优惠券
    list[index].use_title = i18n.global.t('不使用');
    list[index].is_use = 1;
    nextCouponTitle = list[index].coupon_title || list[index].title;
    nextCouponId = list[index].id;
  }
  couponTitle.value = nextCouponTitle;
  couponId.value = nextCouponId;
  coupon.value.coupon = false;
  coupon.value['list'] = list;
  computedPrice();
}

/**
 * 判断优惠券列表是否已包含指定用户券
 * @param {Array} list 优惠券列表
 * @param {Number|String} id 用户券ID
 * @returns {boolean}
 */
function hasCouponRow(list, id) {
  for (var i = 0; i < list.length; i++) {
    if (list[i].id == id) return true;
  }
  return false;
}

/**
 * 计算价格（调用computed接口）
 */
async function computedPrice() {
  var requestSerial = ++priceRequestSerial;
  // 快递运费依赖收货地址，未选择时等待地址回调再试算；送礼订单不计运费，无需地址
  if (!orderKey.value || (!isGift.value && features.value.express_shipping && deliveryType.value === 'delivery' && !addressId.value)) {
    priceComputing.value = false;
    priceReady.value = false;
    priceError.value = '';
    payPrice.value = null;
    payPostage.value = null;
    couponPrice.value = 0;
    integralPrice.value = 0;
    surplusIntegral.value = usableIntegral.value;
    return;
  }
  priceComputing.value = true;
  priceReady.value = false;
  priceError.value = '';
  // 重算完成前清空旧值，避免用户继续看到已经可能失效的金额。
  payPrice.value = null;
  payPostage.value = null;
  try {
    var res = await postCenterOrderComputed({
      key: orderKey.value,
      coupon_id: couponId.value,
      use_integral: useIntegral.value ? 1 : 0,
      address_id: addressId.value,
      delivery_type: deliveryType.value,
      store_id: deliveryType.value === 'pickup' ? (storeInfo.value.id || 0) : 0,
      is_gift: isGift.value || 0,
    });
    if (requestSerial !== priceRequestSerial) return;
    var data = res.data || {};
    payPrice.value = data.pay_price ?? 0;
    payPostage.value = data.pay_postage ?? 0;
    giftPrice.value = data.gift_price ?? giftPrice.value;
    // computed 接口返回 snake_case 价格字段，和确认接口的 priceGroup 保持同一展示口径。
    levelPrice.value = data.level_price ?? data.priceGroup?.levelPrice ?? levelPrice.value;
    memberPrice.value = data.member_price ?? data.priceGroup?.memberPrice ?? memberPrice.value;
    postageDiscount.value = data.storePostageDiscount ?? data.postage_discount ?? data.priceGroup?.postageDiscount ?? postageDiscount.value;
    couponPrice.value = data.coupon_price ?? 0;
    // 后端自动选券回显：未手动选券时展示后端自动领取并使用的优惠券
    if (!couponId.value && data.coupon_id > 0) {
      couponId.value = data.coupon_id;
      couponTitle.value = data.coupon_title || i18n.global.t('请选择');
    }
    // 自动领券发生在确认接口之后，计算接口返回的列表才是最新可用券列表。
    // 同步列表和选中状态，避免入口显示优惠券但弹窗仍显示为空。
    if (Array.isArray(data.coupon_list)) {
      var nextCouponList = data.coupon_list;
      // 兜底：已使用的券不在最新列表中时保留原行（如自动领券与列表查询落在同一秒），
      // 否则入口显示已用券、价格已抵扣，弹窗却渲染成空优惠券。
      if (couponId.value > 0 && !hasCouponRow(nextCouponList, couponId.value)) {
        for (var usedIndex = 0; usedIndex < coupon.value.list.length; usedIndex++) {
          if (coupon.value.list[usedIndex].id == couponId.value) {
            nextCouponList = nextCouponList.concat([coupon.value.list[usedIndex]]);
            break;
          }
        }
      }
      coupon.value.list = nextCouponList;
      features.value.coupon = nextCouponList.length > 0 || couponId.value > 0;
      for (var couponIndex = 0; couponIndex < coupon.value.list.length; couponIndex++) {
        coupon.value.list[couponIndex].is_use = coupon.value.list[couponIndex].id == couponId.value ? 1 : 0;
      }
    }
    integralPrice.value = data.deduction_price ?? 0;
    surplusIntegral.value = data.SurplusIntegral ?? usableIntegral.value;
    priceReady.value = true;
  } catch (e) {
    if (requestSerial !== priceRequestSerial) return;
    priceError.value = (typeof e === 'string' ? e : e?.msg || e?.message) || i18n.global.t('金额计算失败');
    payPrice.value = null;
    payPostage.value = null;
    couponPrice.value = 0;
    integralPrice.value = 0;
    surplusIntegral.value = usableIntegral.value;
    uni.showToast({ title: priceError.value, icon: 'none' });
  } finally {
    if (requestSerial === priceRequestSerial) {
      priceComputing.value = false;
    }
  }
}

/**
 * 切换积分抵扣
 */
function toggleIntegral() {
  useIntegral.value = !useIntegral.value;
  computedPrice();
}

/**
 * 提交订单
 */
async function handleConfirm() {
  if (submitting.value) return;
  if (!priceReady.value || priceComputing.value) {
    uni.showToast({ title: i18n.global.t('金额正在计算，请稍后再试'), icon: 'none' });
    return;
  }
  // 送礼订单由领取人填收货信息，不校验地址/门店/预约
  if (!isGift.value) {
    if (features.value.express_shipping && deliveryType.value === 'delivery' && !addressId.value) {
      uni.showToast({ title: i18n.global.t('请选择收货地址'), icon: 'none' });
      return;
    }
    if (features.value.store_pickup && deliveryType.value === 'pickup' && !storeInfo.value.id) {
      uni.showToast({ title: i18n.global.t('请选择自提门店'), icon: 'none' });
      return;
    }
    if (features.value.door_service && deliveryType.value === 'door') {
      if (!serviceInfo.value.contact_name) {
        uni.showToast({ title: i18n.global.t('请填写联系人姓名'), icon: 'none' });
        return;
      }
      if (!serviceInfo.value.contact_phone) {
        uni.showToast({ title: i18n.global.t('请填写联系电话'), icon: 'none' });
        return;
      }
      if (!serviceInfo.value.appointment_time) {
        uni.showToast({ title: i18n.global.t('请选择预约时间'), icon: 'none' });
        return;
      }
    }
  }
  if (features.value.custom_form && customForm.value.length > 0) {
    if (customFormRef.value && !customFormRef.value.validate()) {
      return;
    }
  }
  // 游客信息校验（门票专属，按数量倍增）
  if (features.value.visitor_form) {
    if (visitorFormRef.value && !visitorFormRef.value.validate()) {
      return;
    }
  }
  submitting.value = true;
  try {
    var params;
    if (isCartMode.value) {
      // 购物车模式：携带 confirm 阶段返回的 order_key，后端从缓存读取多商品数据
      params = {
        key: orderKey.value,
        coupon_id: couponId.value,
        use_integral: useIntegral.value ? 1 : 0,
        order_data: {
          address_id: isGift.value ? 0 : (!features.value.express_shipping || deliveryType.value === 'pickup' || deliveryType.value === 'door' ? 0 : addressId.value),
          delivery_type: isGift.value ? 'delivery' : deliveryType.value,
          store_id: deliveryType.value === 'pickup' ? (storeInfo.value.id || 0) : 0,
          service: deliveryType.value === 'door' ? serviceInfo.value : {},
          mark: remark.value,
          custom_form: customFormData.value,
          visitors: (visitorFormRef.value && features.value.visitor_form) ? visitorFormRef.value.getData() : [],
          block_data: selectedBlockData.value,
          invoice_id: features.value.invoice ? (invoiceId.value || 0) : 0,
          is_gift: isGift.value || 0,
          gift_mark: isGift.value ? giftMark.value : '',
        },
      };
    } else {
      // 单品直购模式
      params = {
        // 确认阶段生成的一次性订单凭证，创建成功后由后端消费
        key: orderKey.value,
        product_id: productId.value,
        sku_unique: skuUnique.value,
        product_type: productType.value,
        num: num.value,
        coupon_id: couponId.value,
        use_integral: useIntegral.value ? 1 : 0,
        order_data: {
          address_id: isGift.value ? 0 : (!features.value.express_shipping || deliveryType.value === 'pickup' || deliveryType.value === 'door' ? 0 : addressId.value),
          delivery_type: isGift.value ? 'delivery' : deliveryType.value,
          store_id: deliveryType.value === 'pickup' ? (storeInfo.value.id || 0) : 0,
          service: deliveryType.value === 'door' ? serviceInfo.value : {},
          mark: remark.value,
          custom_form: customFormData.value,
          visitors: (visitorFormRef.value && features.value.visitor_form) ? visitorFormRef.value.getData() : [],
          block_data: selectedBlockData.value,
          invoice_id: features.value.invoice ? (invoiceId.value || 0) : 0,
          is_gift: isGift.value || 0,
          gift_mark: isGift.value ? giftMark.value : '',
        },
      };
      if (activityType.value) params.activity_type = activityType.value;
      if (activityId.value) params.activity_id = activityId.value;
    }
    await openOrderCreateSubscribe().then(res=>{
      console.log(res)
    }).catch(() => {});
    var res = await submitOrder(params, submitApi.value);
    var orderNo = res.data?.order_no || '';
    var payPrice = res.data?.pay_price ?? '0.00';
    if (!orderNo) {
      uni.showToast({ title: i18n.global.t('下单失败：响应数据异常'), icon: 'none' });
      return;
    }
    // 免费支付场景（如积分兑换）：由后端 activity_info 下发支付方式标识与跳转地址
    // 前端不再硬编码 activityType 判断、paytype:'free' 与跳转路径
    var freePayType = activityInfo.value.free_pay_type || '';
    if (freePayType) {
      await orderPay({ uni: orderNo, paytype: freePayType, type: 0 });
      var redirectUrl = activityInfo.value.pay_redirect_url || '';
      if (redirectUrl) {
        uni.redirectTo({ url: redirectUrl.replace('{order_no}', orderNo) });
      }
      return;
    }
    uni.redirectTo({
      url: routes.cashier + '?order_no=' + orderNo + '&from_type=order&price=' + payPrice + (isGift.value ? '&is_gift=1' : ''),
    });
  } catch (e) {
    uni.showToast({ title: e.msg || e.message || i18n.global.t('下单失败'), icon: 'none' });
  } finally {
    submitting.value = false;
  }
}

// ===== 地址相关方法 =====
async function loadDefaultAddress() {
  try {
    var res = await getAddressDefault();
    if (res.data && res.data.id) {
      res.data.is_default = parseInt(res.data.is_default);
      addressInfo.value = res.data;
      addressId.value = res.data.id;
      address.value.addressId = res.data.id;
    }
  } catch (e) {
    // no default address
  }
}

function onAddress() {
  // 无需地址场景不响应点击（express_shipping/store_pickup 均 false 时不会渲染地址区域，此处为防御性判断）
  if (!features.value.express_shipping && !features.value.store_pickup) return;
  // 始终先打开弹窗，让用户看到"选择其它地址"按钮；无地址时弹窗展示空状态图片+按钮
  // from=order_create：地址列表/地址表单据此走 uni.$emit('orderCreateAddressSelect') 回跳，而非旧版 cartId 重定向
  pagesUrl.value = routes.addressList + '?news=1&from=order_create';
  addressWindowRef.value.getAddressList();
  address.value.address = true;
}

async function OnChangeAddress(e) {
  addressId.value = e;
  address.value.address = false;
  await refreshAddressInfo();
  await computedPrice();
}

async function refreshAddressInfo() {
  if (addressId.value) {
    try {
      var res = await getAddressDetail(addressId.value);
      if (res.data && !Array.isArray(res.data)) {
        res.data.is_default = parseInt(res.data.is_default);
        addressInfo.value = res.data;
        address.value.addressId = res.data.id;
      }
    } catch (e) {
      // ignore
    }
  } else {
    await loadDefaultAddress();
  }
}

function changeClose() {
  address.value.address = false;
}

function changeTextareaStatus() {
  // noop
}

function onHaveAddressList(val) {
  haveAddressList.value = val;
}

// ===== 配送方式 =====
/**
 * 配送方式变更回调
 * 自提/上门模式下运费置 0，并重新计算价格
 */
async function onDeliveryTypeChange(type) {
  if (type === 'pickup') {
    payPostage.value = 0;
    if (!storeInfo.value.name) {
      await loadDefaultStore();
    }
  }
  if (type === 'door') {
    payPostage.value = 0;
    // 上门服务表单初始值：从地址信息带出（联系人/电话/地址）
    if (!serviceInfo.value.contact_name && addressInfo.value.real_name) {
      serviceInfo.value = {
        contact_name: addressInfo.value.real_name,
        contact_phone: addressInfo.value.phone,
        address: [
          addressInfo.value.province,
          addressInfo.value.city,
          addressInfo.value.district,
          addressInfo.value.detail,
        ].filter(Boolean).join(''),
        appointment_time: '',
      };
    }
  }
  await computedPrice();
}

/**
 * 上门服务预约信息变更回调（由 DeliverySection 表单输入触发）
 */
function onServiceChange(service) {
  serviceInfo.value = service || {};
}

// ===== 门店相关（自提模式） =====
/**
 * 加载默认门店（优先从本地存储读取定位，否则请求列表第一条）
 */
async function loadDefaultStore() {
  try {
    var latitude = uni.getStorageSync('user_latitude') || '';
    var longitude = uni.getStorageSync('user_longitude') || '';
    var res = await storeListApi({
      latitude: latitude,
      longitude: longitude,
      page: 1,
      limit: 10,
    });
    var list = res.data?.list?.list || [];
    if (list.length > 0) {
      storeInfo.value = list[0];
    }
  } catch (e) {
    // ignore
  }
}

/**
 * 点击自提区域，跳转门店列表页
 */
function onPickupClick() {
  uni.navigateTo({
    url: routes.storeList,
  });
}

/**
 * 监听门店选择事件（由 goods_details_store/index.vue 通过 uni.$emit 触发）
 */
function onStoreSelect(e) {
  if (e && e.address) {
    storeInfo.value = e.address;
    computedPrice();
  }
}

/**
 * 监听地址选择事件（由 user_address_list/user_address 在 from=order_create
 * 标记下通过 uni.$emit 触发，选中后 navigateBack 返回本页，保留引擎内已有状态）
 */
function onAddressSelect(e) {
  if (e && e.addressId) {
    OnChangeAddress(e.addressId);
  }
}

// ===== 发票相关（抬头选择模式：编辑/保存走原 user_invoice_form 页面） =====
/** 打开抬头选择弹窗前刷新抬头列表 */
function onInvoiceOpen() {
  loadInvoiceList();
}
/** 关闭弹窗：编辑/新增返回后刷新列表与回显 */
function onInvoiceClose() {
  loadInvoiceList();
}
/** 选中抬头：写入 invoiceId */
function onInvoiceChange(id) {
  invoiceId.value = id;
}
/** 不开发票：清空选中 */
function onInvoiceCancel() {
  invoiceId.value = '';
}
/** 加载已保存的抬头列表 */
function loadInvoiceList() {
  uni.showLoading({ title: i18n.global.t('正在加载中') });
  invoiceListApi()
    .then(res => {
      uni.hideLoading();
      // 统一将 id 转字符串，便于 radio 比对
      invoiceList.value = (res.data || []).map(item => {
        item.id = String(item.id);
        return item;
      });
    })
    .catch(err => {
      uni.hideLoading();
      uni.showToast({ title: err, icon: 'none' });
    });
}

// ===== 页面参数初始化 =====
// 引擎组件不会收到页面 onLoad，需由 pages/center/order/create/index.vue 显式传入。
function initFromOptions(options) {
  if (initialized.value) return;
  initialized.value = true;

  var routeQuery = options || {};
  // 购物车模式：携带 cartId 时多商品合并下单；否则单品直购
  cartId.value = routeQuery.cartId || '';
  news.value = routeQuery.new === '1' || routeQuery.new === 1 ? 1 : 0;
  isCartMode.value = !!cartId.value;
  // 送礼下单：详情页「送礼物」跳转携带 is_gift=1
  isGift.value = Number(routeQuery.is_gift || 0) || 0;
  if (isGift.value === 1) {
    giftMark.value = i18n.global.t('这是送您的一份礼物~');
  }
  productId.value = routeQuery.product_id || '';
  skuUnique.value = routeQuery.sku_unique || '';
  try {
    submitApi.value = decodeURIComponent(routeQuery.submit_api || 'center/order/create');
  } catch (e) {
    submitApi.value = 'center/order/create';
  }
  productType.value = routeQuery.product_type || 'normal';
  activityType.value = routeQuery.activity_type && routeQuery.activity_type !== 'undefined'
    ? routeQuery.activity_type
    : '';
  var parsedActivityId = parseInt(routeQuery.activity_id, 10);
  activityId.value = Number.isFinite(parsedActivityId) && parsedActivityId > 0 ? parsedActivityId : 0;
  var parsedNum = parseInt(routeQuery.num, 10);
  num.value = Number.isFinite(parsedNum) && parsedNum > 0 ? parsedNum : 1;
  // 地址回跳参数（购物车模式从地址列表选择后回跳）
  var parsedAddressId = parseInt(routeQuery.address_id, 10);
  addressId.value = Number.isFinite(parsedAddressId) && parsedAddressId > 0 ? parsedAddressId : 0;
  // 局部变量重命名 invoiceIdParam：避免与同名 ref（invoiceId）遮蔽
  var invoiceIdParam = routeQuery.invoice_id;
  if (invoiceIdParam) invoiceId.value = String(invoiceIdParam);
  // 解析详情页透传的 display_sections block 选中数据（票种/场次/日期）
  var blockDataStr = routeQuery.block_data || '';
  if (blockDataStr) {
    try {
      // Vue Router 可能已解码，小程序通常仍是编码字符串
      selectedBlockData.value = JSON.parse(blockDataStr);
    } catch (e1) {
      try {
        selectedBlockData.value = JSON.parse(decodeURIComponent(blockDataStr));
      } catch (e2) {
        selectedBlockData.value = [];
      }
    }
  }
  loadConfirmData();
}

defineExpose({ initFromOptions });
uni.$on('handClick', onStoreSelect);
uni.$on('orderCreateAddressSelect', onAddressSelect);

// ===== beforeUnmount =====
onBeforeUnmount(() => {
  uni.$off('handClick', onStoreSelect);
  uni.$off('orderCreateAddressSelect', onAddressSelect);
});
</script>

<style scoped>
.order-page {
  min-height: 100vh;
  background: #f5f5f5;
  padding-bottom: 140rpx;
}

/* 加载状态 */
.loading-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
}
.loading-text {
  font-size: 28rpx;
  color: #999;
}
.loading-text.error {
  color: #e93323;
  margin-bottom: 30rpx;
  text-align: center;
  padding: 0 40rpx;
  line-height: 1.6;
}
.retry-btn {
  padding: 16rpx 60rpx;
  background: #e93323;
  color: #fff;
  font-size: 28rpx;
  border-radius: 40rpx;
}

/* 商品卡片点击区域（点击跳转商品详情页） */
.product-card-wrap {
  border-radius: 12rpx;
}
.product-card-hover {
  opacity: 0.7;
}

/* 送礼留言 */
.gift-mark-card {
  margin: 20rpx;
  background: #fff;
  border-radius: 12rpx;
  padding: 24rpx 30rpx;
}
.gift-mark-label {
  font-size: 28rpx;
  color: #282828;
  margin-bottom: 12rpx;
}
.gift-mark-input {
  width: 100%;
  font-size: 28rpx;
  color: #282828;
}
</style>
