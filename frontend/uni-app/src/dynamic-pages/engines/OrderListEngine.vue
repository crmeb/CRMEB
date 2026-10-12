<template>
  <scroll-view scroll-y class="engine-scroll">
    <view :style="colorStyle">
      <view class="my-order">
        <view class="header bg-color">
          <view class="picTxt acea-row row-between-wrapper">
            <view class="text">
              <view class="name">{{ $t(`订单信息`) }}</view>
              <view>
                <text class="mr-40">{{ $t(`消费订单`) }}：{{ orderData.all && orderData.all.count || 0 }} 单 </text>
                {{ $t(`总消费`) }}：{{ $t(`￥`) }}{{ orderData.all && orderData.all.amount || 0 }}
              </view>
            </view>
            <view class="pictrue">
              <image src="@/pages/goods/static/orderTime.png"></image>
            </view>
          </view>
        </view>
        <view class="nav acea-row row-around">
          <view class="item" :class="orderStatus == 'all' ? 'on' : ''" @click="statusClick('all')">
            <view>{{ $t(`全部`) }}</view>
            <view class="num">{{ orderData.all && orderData.all.count || 0 }}</view>
          </view>
          <view class="item" :class="orderStatus == 'pending_payment' ? 'on' : ''" @click="statusClick('pending_payment')">
            <view>{{ $t(`待支付`) }}</view>
            <view class="num">{{ orderData.grouped && orderData.grouped.pending_payment && orderData.grouped.pending_payment.count || 0 }}</view>
          </view>
          <view class="item" :class="orderStatus == 'pending_delivery' ? 'on' : ''" @click="statusClick('pending_delivery')">
            <view>{{ $t(`待收货`) }}</view>
            <view class="num">{{ orderData.grouped && orderData.grouped.pending_delivery && orderData.grouped.pending_delivery.count || 0 }}</view>
          </view>
          <view class="item" :class="orderStatus == 'pending_use' ? 'on' : ''" @click="statusClick('pending_use')">
            <view>{{ $t(`待使用`) }}</view>
            <view class="num">{{ orderData.grouped && orderData.grouped.pending_use && orderData.grouped.pending_use.count || 0 }}</view>
          </view>
          <view class="item" :class="orderStatus == 'pending_comment' ? 'on' : ''" @click="statusClick('pending_comment')">
            <view>{{ $t(`待评价`) }}</view>
            <view class="num">{{ orderData.grouped && orderData.grouped.pending_comment && orderData.grouped.pending_comment.count || 0 }}</view>
          </view>
        </view>
        <view class="list">
          <view v-for="(order, index) in orders" :key="order.order_no" class="item">
            <view @click="goDetail(order)">
              <view class="title acea-row row-between-wrapper">
                <view class="acea-row row-middle">
                  <text v-if="order.type_label" class="type-tag">{{ order.type_label }}</text>
                  <view>{{ formatTime(order.add_time) }}</view>
                </view>
                <StatusBadge :status-code="order.status_code" :status-text="order.status_text" />
              </view>
              <view class="item-info acea-row row-between row-top">
                <!-- v8.1.1 多商品：遍历 extra_info.items 展示所有商品 -->
                <template v-if="getOrderItems(order).length > 1">
                  <view class="multi-goods">
                    <view v-for="(item, idx) in getOrderItems(order)" :key="idx" class="goods-row">
                      <image :src="item.image" mode="aspectFill" class="goods-img"></image>
                      <view class="goods-text">
                        <text class="goods-name line2">{{ item.store_name || '' }}</text>
                        <text v-if="getItemSkuText(item)" class="goods-sku line1">{{ getItemSkuText(item) }}</text>
                        <view class="goods-meta">
                          <text class="goods-price">¥{{ item.true_price || item.price || '0.00' }}</text>
                          <text class="goods-num">x{{ item.cart_num || 1 }}</text>
                        </view>
                      </view>
                    </view>
                  </view>
                </template>
                <!-- 单商品或无 items 降级：保持原有展示 -->
                <template v-else>
                  <view class="pictrue">
                    <image :src="order.image" mode="aspectFill"></image>
                  </view>
                  <view class="text">
                    <view class="title-row">
                      <text class="name line2">{{ order.title || '' }}</text>
                      <view class="money">
                        <view>{{ $t(`￥`) }}{{ order.pay_price || '0.00' }}</view>
                        <view>x{{ order.total_num || 0 }}</view>
                      </view>
                    </view>
                    <view v-if="getSkuText(order)" class="sku-text line1">
                      {{ getSkuText(order) }}
                    </view>
                  </view>
                </template>
              </view>
              <view v-if="getOrderExtendText(order)" class="extend-text line1">
                {{ getOrderExtendText(order) }}
              </view>
            </view>
            <view class="bottom acea-row row-right row-middle">
              <button
                v-for="btn in (order.order_buttons || [])"
                :key="btn.action"
                class="bnt"
                :class="getBtnClass(btn)"
                @click.stop="handleAction(order, btn)"
              >{{ btn.text }}</button>
            </view>
          </view>
        </view>
        <view class="loadingicon acea-row row-center-wrapper" v-if="orders.length > 0">
          <text class="loading iconfont icon-jiazai" :hidden="loading == false"></text>
          {{ loadTitle }}
        </view>
        <view v-if="orders.length == 0">
          <emptyPage v-if="!loading" :title="$t(`暂无订单`)"></emptyPage>
          <view class="loadingicon acea-row row-center-wrapper">
            <text class="loading iconfont icon-jiazai" :hidden="loading == false"></text>
          </view>
        </view>
      </view>
      <!-- #ifndef MP -->
      <home></home>
      <!-- #endif -->
    </view>
  </scroll-view>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import StatusBadge from '../components/order/common/StatusBadge.vue';
import actionMap from '../api/action-map.js';
import routes from '../api/routes.js';
import { getCenterOrderList, getCenterOrderStat, cancelOrder, takeOrder, delOrder } from '@/api/order-center.js';
import emptyPage from '@/components/emptyPage.vue';
import home from '@/components/home';
import { useColorTheme } from '@/composables/useColorTheme';
import i18n from '@/utils/lang.js';

defineOptions({ name: 'OrderListEngine' });

const props = defineProps({
  statusFilter: { type: String, default: '' },
});

const { colorStyle } = useColorTheme();

// ---------- data ----------
const orders = ref([]);
const orderData = ref({});
const page = ref(1);
const limit = ref(20);
const loading = ref(false);
const finished = ref(false);
const orderStatus = ref('all');
const loadTitle = ref('');

// ---------- watch ----------
watch(() => props.statusFilter, (val) => {
  if (val) {
    statusClick(val);
  }
});

// ---------- created（顶层直写） ----------
loadTitle.value = i18n.t('加载更多');
orderStatus.value = props.statusFilter || 'all';
getOrderData();
loadOrders();

// ---------- methods ----------
function formatTime(timestamp) {
  if (!timestamp) return '';
  const date = new Date(timestamp * 1000);
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  const hour = String(date.getHours()).padStart(2, '0');
  const minute = String(date.getMinutes()).padStart(2, '0');
  return `${year}-${month}-${day} ${hour}:${minute}`;
}
function getBtnClass(btn) {
  // 按钮样式以后端下发的 primary 字段为准，主操作高亮，次操作描边
  return btn && btn.primary ? 'bg-color' : 'cancelBnt';
}
/**
 * 获取商品规格文本
 * 优先使用后端 extra_info.sku_info.suk，兼容旧字段 attrInfo/suk
 */
function getSkuText(order) {
  if (!order) return '';
  const extra = order.extra_info || {};
  const skuInfo = extra.sku_info || {};
  if (skuInfo.suk) {
    return skuInfo.suk;
  }
  return '';
}
/**
 * 获取订单商品列表
 * v8.1.1 从 extra_info.items 读取多商品数据，无则返回空数组（走单商品降级）
 * @param {object} order 订单对象
 * @return {array} 商品数组
 */
function getOrderItems(order) {
  if (!order) return [];
  const extra = order.extra_info || {};
  return extra.items || [];
}
/**
 * 获取单个商品的规格文本
 * @param {object} item 商品项
 * @return {string} 规格文本
 */
function getItemSkuText(item) {
  if (!item) return '';
  if (item.sku) return item.sku;
  if (item.attr_info && item.attr_info.suk) return item.attr_info.suk;
  return '';
}
/**
 * 获取订单扩展信息文案
 * 直接使用后端下发的 extend_text（后端已通过 TradeModeRegistry/OrderTypeRegistry 动态生成活动/分类文案）
 */
function getOrderExtendText(order) {
  if (!order) return '';
  // 直接信任后端 extend_text（后端已通过 TradeModeRegistry/OrderTypeRegistry 动态生成）
  return (order.extend_text && typeof order.extend_text === 'string') ? order.extend_text : '';
}
async function getOrderData() {
  try {
    const res = await getCenterOrderStat();
    orderData.value = res.data || {};
  } catch (e) {
    // ignore
  }
}
async function loadOrders() {
  if (loading.value || finished.value) return;
  loading.value = true;
  loadTitle.value = i18n.t('加载更多');
  try {
    const res = await getCenterOrderList({
      page: page.value,
      limit: limit.value,
      // 直接传语义化状态，后端 mapSemanticStatus 映射到 status_code
      status: orderStatus.value === 'all' ? '' : orderStatus.value,
    });
    const list = res.data && res.data.list || [];
    if (list.length < limit.value) finished.value = true;
    orders.value = page.value === 1 ? list : [...orders.value, ...list];
    page.value++;
    loadTitle.value = finished.value ? i18n.t('没有更多内容啦~') : i18n.t('加载更多');
  } catch (e) {
    // ignore
  } finally {
    loading.value = false;
  }
}
function statusClick(status) {
  if (status === orderStatus.value) return;
  orderStatus.value = status;
  finished.value = false;
  page.value = 1;
  orders.value = [];
  loadOrders();
}
function handleAction(order, btn) {
  const handler = actionMap[btn.action];
  if (handler) {
    handler(order, btn.params || {});
  }
}
/**
 * 取消订单
 */
async function onCancelOrder({ order_no }) {
  try {
    await cancelOrder(order_no);
    uni.showToast({ title: i18n.t('取消成功'), icon: 'success' });
    refreshList();
  } catch (e) {
    uni.showToast({ title: e?.msg || i18n.t('取消失败'), icon: 'none' });
  }
}
/**
 * 确认收货
 */
async function onConfirmOrder({ order_no }) {
  try {
    await takeOrder(order_no);
    uni.showToast({ title: i18n.t('收货成功'), icon: 'success' });
    refreshList();
  } catch (e) {
    uni.showToast({ title: e?.msg || i18n.t('收货失败'), icon: 'none' });
  }
}
/**
 * 删除订单
 */
async function onDeleteOrder({ order_no }) {
  try {
    await delOrder(order_no);
    uni.showToast({ title: i18n.t('删除成功'), icon: 'success' });
    refreshList();
  } catch (e) {
    uni.showToast({ title: e?.msg || i18n.t('删除失败'), icon: 'none' });
  }
}
/**
 * 刷新列表
 */
function refreshList() {
  page.value = 1;
  finished.value = false;
  orders.value = [];
  getOrderData();
  loadOrders();
}
function goDetail(order) {
  if (!order || !order.order_no) return;
  const url = routes.orderDetail + '?order_no=' + order.order_no;
  uni.navigateTo({ url });
}

// ---------- 事件总线（$on/$off 传同一具名函数引用） ----------
// 监听订单操作事件（列表页按钮触发后由本页执行实际 API）
onMounted(() => {
  uni.$on('order:cancel', onCancelOrder);
  uni.$on('order:confirm', onConfirmOrder);
  uni.$on('order:delete', onDeleteOrder);
});

onBeforeUnmount(() => {
  uni.$off('order:cancel', onCancelOrder);
  uni.$off('order:confirm', onConfirmOrder);
  uni.$off('order:delete', onDeleteOrder);
});

// 包装页 onShow 返回时调用（详情页评价/收货等操作后返回列表需看到最新状态）
defineExpose({ refresh: refreshList });
</script>

<style scoped>
.engine-scroll { height: 100%; }

.my-order .header {
  height: 260rpx;
  padding: 0 30rpx;
}

.my-order .header .picTxt {
  height: 190rpx;
}

.my-order .header .picTxt .text {
  color: rgba(255, 255, 255, 0.8);
  font-size: 26rpx;
}

.my-order .header .picTxt .text .name {
  font-size: 34rpx;
  font-weight: bold;
  color: #fff;
  margin-bottom: 20rpx;
}

.my-order .header .picTxt .pictrue {
  width: 122rpx;
  height: 109rpx;
}

.my-order .header .picTxt .pictrue image {
  width: 100%;
  height: 100%;
}

.my-order .nav {
  background-color: #fff;
  width: 690rpx;
  height: 140rpx;
  border-radius: 6rpx;
  margin: -73rpx auto 0 auto;
  display: flex;
  justify-content: space-around;
  align-items: center;
}

.my-order .nav .item {
  text-align: center;
  font-size: 26rpx;
  color: #282828;
  flex: 1;
  padding: 24rpx 0;
  border-bottom: 5rpx solid transparent;
  white-space: nowrap;
}

.my-order .nav .item.on {
  font-weight: bold;
  border-color: var(--view-theme);
}

.my-order .nav .item .num {
  margin-top: 18rpx;
}

.my-order .list {
  width: 690rpx;
  margin: 14rpx auto 0 auto;
}

.my-order .list .item {
  background-color: #fff;
  border-radius: 6rpx;
  margin-bottom: 14rpx;
}

.my-order .list .item .title {
  height: 84rpx;
  padding: 0 30rpx;
  border-bottom: 1rpx solid #eee;
  font-size: 28rpx;
  color: #282828;
}

.my-order .list .item .title .type-tag {
  flex-shrink: 0;
  font-size: 20rpx;
  line-height: 1.2;
  padding: 4rpx 12rpx;
  margin-right: 12rpx;
  color: var(--view-theme, #e93323);
  background: var(--view-op-ten, rgba(233, 51, 35, 0.1));
  border-radius: 6rpx;
}

.my-order .list .item .item-info {
  padding: 0 30rpx;
  margin-top: 22rpx;
}

/* v8.1.1 多商品列表样式 */
.my-order .list .item .item-info .multi-goods {
  width: 100%;
}
.my-order .list .item .item-info .multi-goods .goods-row {
  display: flex;
  align-items: flex-start;
  padding: 12rpx 0;
  border-bottom: 1rpx solid #f5f5f5;
}
.my-order .list .item .item-info .multi-goods .goods-row:last-child {
  border-bottom: none;
}
.my-order .list .item .item-info .multi-goods .goods-img {
  width: 120rpx;
  height: 120rpx;
  border-radius: 6rpx;
  flex-shrink: 0;
}
.my-order .list .item .item-info .multi-goods .goods-text {
  flex: 1;
  margin-left: 20rpx;
  display: flex;
  flex-direction: column;
}
.my-order .list .item .item-info .multi-goods .goods-name {
  font-size: 28rpx;
  color: #333;
  line-height: 1.4;
}
.my-order .list .item .item-info .multi-goods .goods-sku {
  font-size: 24rpx;
  color: #999;
  margin-top: 4rpx;
}
.my-order .list .item .item-info .multi-goods .goods-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 8rpx;
}
.my-order .list .item .item-info .multi-goods .goods-price {
  font-size: 28rpx;
  color: var(--view-theme, #e93323);
  font-weight: 600;
}
.my-order .list .item .item-info .multi-goods .goods-num {
  font-size: 26rpx;
  color: #999;
}

.my-order .list .item .item-info .pictrue {
  width: 120rpx;
  height: 120rpx;
}

.my-order .list .item .item-info .pictrue image {
  width: 100%;
  height: 100%;
  border-radius: 6rpx;
}

.my-order .list .item .item-info .text {
  width: 486rpx;
  font-size: 28rpx;
  color: #999;
  margin-top: 6rpx;
  display: flex;
  flex-direction: column;
  line-height: 39rpx;
}

.my-order .list .item .item-info .text .title-row {
  display: flex;
  justify-content: space-between;
}

.my-order .list .item .item-info .text .name {
  flex: 1;
  color: #282828;
  height: 78rpx;
  margin-right: 20rpx;
}

.my-order .list .item .item-info .text .money {
  text-align: right;
  flex-shrink: 0;
}

.my-order .list .item .item-info .text .sku-text {
  font-size: 24rpx;
  color: #999;
  margin-top: 8rpx;
}

.my-order .list .item .extend-text {
  font-size: 24rpx;
  color: #666;
  padding: 0 30rpx;
  margin-top: 16rpx;
}

.my-order .list .item .totalPrice {
  font-size: 26rpx;
  color: #282828;
  text-align: right;
  margin: 27rpx 0 0 30rpx;
  padding: 0 30rpx 30rpx 0;
  border-bottom: 1rpx solid #eee;
}

.my-order .list .item .totalPrice .money {
  font-size: 28rpx;
  font-weight: bold;
  color: var(--view-priceColor);
}

.my-order .list .item .bottom {
  height: 107rpx;
  padding: 0 30rpx;
}

.my-order .list .item .bottom .bnt {
  width: 176rpx;
  height: 60rpx;
  text-align: center;
  line-height: 60rpx;
  color: #fff;
  border-radius: 50rpx;
  font-size: 27rpx;
  padding: 0;
  margin: 0;
  border: none;
}

.my-order .list .item .bottom .bnt.cancelBnt {
  border: 1rpx solid #ddd;
  color: #aaa;
  background: #fff;
}

.my-order .list .item .bottom .bnt ~ .bnt {
  margin-left: 17rpx;
}

.loadingicon {
  padding: 30rpx 0;
  font-size: 26rpx;
  color: #999;
}

.loading {
  margin-right: 10rpx;
}
</style>