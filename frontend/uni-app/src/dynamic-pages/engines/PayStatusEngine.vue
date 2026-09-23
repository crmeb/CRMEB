<template>
  <view :style="colorStyle">
    <view class="payment-status" v-if="loading">
      <!-- 状态图标：成功/线下支付用对勾，失败用关闭 -->
      <view class="iconfont icons" :class="statusIcon"></view>
      <!-- 状态文案 -->
      <view class="status">{{ statusText }}</view>
      <!-- 订单信息 -->
      <view class="wrapper">
        <view class="item acea-row row-between-wrapper">
          <view>{{ $t(`订单号`) }}</view>
          <view class="itemCom">{{ orderId }}</view>
        </view>
        <view class="item acea-row row-between-wrapper">
          <view>{{ $t(`下单时间`) }}</view>
          <view class="itemCom">{{ formatTime(order_pay_info.add_time) }}</view>
        </view>
        <view class="item acea-row row-between-wrapper">
          <view>{{ $t(`支付方式`) }}</view>
          <view class="itemCom">{{ order_pay_info.pay_type_name || $t(`暂未支付`) }}</view>
        </view>
        <view class="item acea-row row-between-wrapper">
          <view>{{ $t(`支付金额`) }}</view>
          <view class="itemCom">{{ order_pay_info.pay_price }}</view>
        </view>
        <!-- 失败时展示失败原因 -->
        <view class="item acea-row row-between-wrapper" v-if="order_pay_info.pay_status !== 1 && order_pay_info.pay_type != 'offline'">
          <view>{{ $t(`失败原因`) }}</view>
          <view class="itemCom">{{ $t(`未支付`) }}</view>
        </view>
      </view>
      <!-- 操作按钮（数据驱动，遍历 currentActions） -->
      <view class="action-bar">
        <button
          v-for="action in currentActions"
          :key="action.key"
          :class="['returnBnt', action.type === 'primary' ? 'bg-color' : 'cart-color']"
          hover-class="none"
          @click="onAction(action)"
        >{{ $t(action.label) }}</button>
      </view>
      <!-- 赠送优惠券 -->
      <view class="coupons" v-if="couponList.length">
        <view class="title acea-row row-center-wrapper">
          <view class="line"></view>
          <view class="name">{{ $t(`赠送优惠券`) }}</view>
          <view class="line"></view>
        </view>
        <view class="list">
          <template v-for="(item, index) in couponList" :key="index">
            <view class="item acea-row row-between-wrapper" v-if="index < 2 || !couponsHidden">
            <view class="moneyCon acea-row row-between-wrapper">
              <view class="price acea-row row-center-wrapper">
                <view>
                  {{ $t(`￥`) }}
                  <text>{{ item.coupon_price }}</text>
                </view>
              </view>
            </view>
            <view class="text">
              <view class="name line1">{{ item.coupon_title }}</view>
              <view class="priceMin">{{ $t(`满`) }}{{ item.use_min_price }}{{ $t(`元可用`) }}</view>
              <view class="time">{{ $t(`有效期`) }}:{{ item.add_time ? item.add_time + '-' : '' }}{{ item.end_time }}</view>
            </view>
          </view>
          </template>
          <view class="open acea-row row-center-wrapper" @click="openTap" v-if="couponList.length > 2">
            {{ couponsHidden ? $t(`更多`) : $t(`关闭`) }}
            <text class="iconfont" :class="couponsHidden ? 'icon-xiangxia' : 'icon-xiangshang'"></text>
          </view>
        </view>
      </view>
    </view>
  </view>
</template>

<script setup>
/**
 * 支付结果页引擎
 * 职责：
 * 1. 查询并展示支付结果（成功/失败/创建成功）与订单信息
 * 2. 通过 actions 数据驱动渲染操作按钮（查看订单/返回首页/重新支付/重新购买/刷新）
 * 3. 展示赠送优惠券列表
 * 页面参数 order_no/status/msg/payType/is_gift 由壳子 onLoad 暂存，
 * 于引擎挂载后经 initFromOptions 注入（小程序端引擎创建早于页面 onLoad，props 传参不可靠）
 */
import { ref, computed, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { getCenterOrderDetail } from '@/api/order-center.js';
import { orderCoupon } from '@/api/order.js';
import { useAppStore } from '@/store/modules/app';
import { useColorTheme } from '@/composables/useColorTheme';
import routes from '../api/routes.js';
import i18n from '@/utils/lang.js';

defineOptions({ name: 'PayStatusEngine' });

const { colorStyle } = useColorTheme();

const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

// ---------- data ----------
/** 订单号（与页面 order_no 参数一致，CRMEB 中即订单编号字符串） */
const orderId = ref('');
/** 支付结果状态：0 默认/成功，1 重新购买，2 重新支付 */
const status = ref(0);
/** 提示消息（页面 msg 参数） */
const msg = ref('');
/** 支付方式标识（用于判断“订单支付中”文案） */
const payType = ref('');
/** 是否礼品订单（0 否） */
const isGift = ref(0);
// 是否已完成首次查询
const loading = ref(false);
// 订单支付信息
const order_pay_info = ref({
  pay_status: 0,
});
// 优惠券列表是否收起
const couponsHidden = ref(true);
// 赠送优惠券列表
const couponList = ref([]);
// 默认操作按钮配置（数据驱动，可由后端或产品类型覆盖）
const actions = ref([
  { key: 'viewOrder', label: '查看订单', type: 'primary', action: 'viewOrder' },
  { key: 'goIndex', label: '返回首页', type: 'default', action: 'goIndex' },
  { key: 'repay', label: '重新支付', type: 'primary', action: 'repay' },
  { key: 'rebuy', label: '重新购买', type: 'primary', action: 'rebuy' },
  { key: 'refresh', label: '刷新支付状态', type: 'default', action: 'refresh' },
]);

// ---------- computed ----------
/**
 * 按钮 key 索引，便于 currentActions 按 key 取用
 * @returns {Object} 以 action.key 为键的按钮对象映射
 */
const actionMap = computed(() => {
  const map = {};
  actions.value.forEach((a) => {
    map[a.key] = a;
  });
  return map;
});
/**
 * 当前应展示的按钮组合
 * 规则：
 * - 线下支付（pay_type=offline）：查看订单 + 返回首页（订单创建成功）
 * - 支付成功（pay_status=1）：查看订单 + 返回首页
 * - 支付失败/取消：status=2 重新支付 / status=1 重新购买 / 其余查看订单，H5 额外提供刷新，最后返回首页
 * @returns {Array} 按钮对象数组
 */
const currentActions = computed(() => {
  const info = order_pay_info.value || {};
  const isOffline = info.pay_type === 'offline';
  const list = [];
  // 线下支付：订单创建成功
  if (isOffline) {
    list.push(actionMap.value.viewOrder);
    list.push(actionMap.value.goIndex);
    return list;
  }
  // 支付成功（pay_status: 1=已支付）
  if (info.pay_status === 1) {
    list.push(actionMap.value.viewOrder);
    list.push(actionMap.value.goIndex);
    return list;
  }
  // 支付失败/取消：根据 status 决定主操作按钮
  if (status.value == 2) {
    list.push(actionMap.value.repay);
  } else if (status.value == 1) {
    list.push(actionMap.value.rebuy);
  } else {
    list.push(actionMap.value.viewOrder);
  }
  // #ifdef H5
  // H5 端未支付时提供刷新支付状态入口
  list.push(actionMap.value.refresh);
  // #endif
  list.push(actionMap.value.goIndex);
  return list;
});
/**
 * 状态图标 class
 * @returns {String} 图标 class 字符串
 */
const statusIcon = computed(() => {
  const info = order_pay_info.value || {};
  if (info.pay_status === 1 || info.pay_type === 'offline') {
    return 'icon-duihao2 bg-color';
  }
  return 'icon-iconfontguanbi';
});
/**
 * 状态文案
 * @returns {String} 已国际化的状态文案
 */
const statusText = computed(() => {
  const info = order_pay_info.value || {};
  if (info.pay_type === 'offline') return i18n.t('订单创建成功');
  if (info.pay_status === 1) return i18n.t('订单支付成功');
  return i18n.t(payType.value ? '订单支付中' : '订单支付失败');
});

// ---------- watch ----------
// 登录状态变化时重新查询（覆盖自动登录后回填数据的场景）
watch(isLogin, (newV) => {
  if (newV && orderId.value) {
    getOrderPayInfo();
  }
}, { deep: true });

// ---------- methods ----------
/**
 * 格式化时间戳
 * @param {number|string} time 10位时间戳
 * @param {string} fmt 格式模板（默认 YYYY-MM-DD HH:mm）
 */
function formatTime(time, fmt) {
  if (!time) return '';
  const date = new Date(parseInt(time) * 1000);
  if (!fmt) fmt = 'YYYY-MM-DD HH:mm';
  const o = {
    'Y+': date.getFullYear().toString(),
    'M+': (date.getMonth() + 1).toString().padStart(2, '0'),
    'D+': date.getDate().toString().padStart(2, '0'),
    'H+': date.getHours().toString().padStart(2, '0'),
    'm+': date.getMinutes().toString().padStart(2, '0'),
    's+': date.getSeconds().toString().padStart(2, '0'),
  };
  let result = fmt;
  for (const [k, v] of Object.entries(o)) {
    result = result.replace(new RegExp(k, 'g'), v);
  }
  return result;
}
/**
 * 从页面 onLoad 参数初始化引擎状态并触发首查（由壳子 onMounted 后调用）
 * 未登录时跳过首查，由 isLogin watch 在登录完成后触发
 * @param {Object} options 页面 onLoad 参数（order_no/status/msg/payType/is_gift）
 */
function initFromOptions(options) {
  orderId.value = options.order_no || '';
  status.value = options.status || 0;
  msg.value = options.msg || '';
  payType.value = options.payType || '';
  isGift.value = options.is_gift || 0;
  if (orderId.value && isLogin.value) {
    getOrderPayInfo();
  }
}
/**
 * 刷新支付状态（供壳子 onShow 通过 ref 调用）
 */
function refresh() {
  if (orderId.value) {
    getOrderPayInfo();
  }
}
/**
 * 查询支付结果并更新订单信息
 * v8.1 单层结构：接口只返回 {page_config: {sections, extensions}}，渲染数据直填在 sections.props
 */
function getOrderPayInfo() {
  uni.showLoading({
    title: i18n.t('正在加载中'),
  });
  getCenterOrderDetail(orderId.value)
    .then((res) => {
      uni.hideLoading();
      // 后端 redirect 指令：扩展模块可控制页面跳转（如拼团支付成功跳转拼团进度页）
      if (res.data && res.data.redirect) {
        var r = res.data.redirect;
        uni[r.mode || 'redirectTo']({ url: r.url });
        return;
      }
      // 单层结构：按 section key 建立 props 映射，读取渲染值
      const pageConfig = (res.data && res.data.page_config) || { sections: [], extensions: {} };
      const propsMap = {};
      (pageConfig.sections || []).forEach((s) => {
        propsMap[s.key] = s.props || {};
      });
      const orderInfoFields = (propsMap['order_info'] && propsMap['order_info'].fields) || {};
      const statusProps = propsMap['status'] || {};
      const pricingProps = propsMap['pricing'] || {};
      order_pay_info.value = {
        add_time: statusProps.add_time || 0,
        pay_type: orderInfoFields.pay_type || '',
        pay_type_name: orderInfoFields.pay_type_name || '',
        pay_price: pricingProps.pay_price || '0.00',
        pay_status: statusProps.pay_status || 0,
      };
      // 根据支付状态设置导航栏标题
      // pay_status: 0未支付 1已支付 2部分退款 3全额退款
      const isPaid = order_pay_info.value.pay_status === 1;
      uni.setNavigationBarTitle({
        title: isPaid ? i18n.t('支付成功') : i18n.t('未支付'),
      });
      loading.value = true;
      // 非礼品订单禁用分享（本引擎不支持礼品分享）
      uni.hideShareMenu();
      getOrderCoupon();
    })
    .catch(() => {
      loading.value = true;
      uni.hideLoading();
    });
}
/**
 * 获取赠送优惠券列表
 */
function getOrderCoupon() {
  orderCoupon(orderId.value)
    .then((res) => {
      couponList.value = res.data || [];
    });
}
/**
 * 展开/收起优惠券列表
 */
function openTap() {
  couponsHidden.value = !couponsHidden.value;
}
/**
 * 按钮点击统一分发
 * @param {Object} action 按钮配置对象
 */
function onAction(action) {
  switch (action.action) {
    case 'viewOrder':
    case 'repay':
    case 'rebuy':
      // 重新支付/重新购买沿用旧页面行为：跳转订单详情（详情页内置付款入口）
      viewOrder();
      break;
    case 'goIndex':
      goIndex();
      break;
    case 'refresh':
      refresh();
      break;
    default:
      break;
  }
}
/**
 * 跳转订单详情（统一使用 routes.orderDetail + order_no）
 */
function viewOrder() {
  uni.redirectTo({
    url: routes.orderDetail + '?order_no=' + orderId.value,
  });
}
/**
 * 返回首页（switchTab 关闭当前页面栈）
 */
function goIndex() {
  uni.switchTab({
    url: '/pages/index/index',
  });
}

// 供壳子（pages/center/pay/status）onMounted 初始化、onShow 刷新，通过 engineRef 调用
defineExpose({
  initFromOptions,
  refresh,
});
</script>

<style lang="scss">
.payment-status {
  background-color: #fff;
  margin: 195rpx 30rpx 0 30rpx;
  border-radius: 10rpx;
  padding: 1rpx 0 28rpx 0;
}

.payment-status .icons {
  font-size: 70rpx;
  width: 140rpx;
  height: 140rpx;
  border-radius: 50%;
  color: #fff;
  text-align: center;
  line-height: 140rpx;
  text-shadow: 0px 4px 0px rgba(255, 255, 255, 0.5);
  border: 6rpx solid #f5f5f5;
  margin: -76rpx auto 0 auto;
  background-color: #999;
}

.payment-status .icons.icon-iconfontguanbi {
  text-shadow: 0px 4px 0px #6c6d6d;
}

.payment-status .iconfont.fail {
  text-shadow: 0px 4px 0px #7a7a7a;
}

.payment-status .status {
  font-size: 32rpx;
  font-weight: bold;
  text-align: center;
  margin: 25rpx 0 37rpx 0;
}

.payment-status .wrapper {
  border: 1rpx solid #eee;
  margin: 0 30rpx 47rpx 30rpx;
  padding: 35rpx 0;
  border-left: 0;
  border-right: 0;
}

.payment-status .wrapper .item {
  font-size: 28rpx;
  color: #282828;
}

.payment-status .wrapper .item ~ .item {
  margin-top: 20rpx;
}

.payment-status .wrapper .item .itemCom {
  color: #666;
}

.payment-status .returnBnt {
  width: 630rpx;
  height: 86rpx;
  border-radius: 50rpx;
  color: #fff;
  font-size: 30rpx;
  text-align: center;
  line-height: 86rpx;
  margin: 0 auto 20rpx auto;
}

.coupons {
  .title {
    margin: 30rpx 0 25rpx 0;
    font-size: 28rpx;
    color: #333333;
  }

  .list {
    padding: 0 20rpx;

    .item {
      margin-bottom: 20rpx;
      box-shadow: 0px 2px 10px 0px rgba(0, 0, 0, 0.06);

      .price {
        width: 236rpx;
        height: 160rpx;
        font-size: 26rpx;
        color: #fff;
        font-weight: 800;

        text {
          font-size: 54rpx;
        }
      }

      .text {
        width: 385rpx;

        .name {
          font-size: #282828;
          font-size: 30rpx;
        }

        .priceMin {
          font-size: 24rpx;
          color: #999;
          margin-top: 10rpx;
        }

        .time {
          font-size: 24rpx;
          color: #999;
          margin-top: 15rpx;
        }
      }
    }

    .open {
      font-size: 24rpx;
      color: #999;
      margin-top: 30rpx;

      .iconfont {
        font-size: 25rpx;
        margin: 5rpx 0 0 10rpx;
      }
    }
  }
}
</style>
