<!--
  商品卡片组件（DIY 通用化版）
  展示商品图片、名称、规格、价格、数量，点击跳转商品详情
  v8.1.1 支持多商品订单：遍历 product.items 渲染所有商品
  数据来源：section.props.product + section._ctx.payPrice
-->
<template>
  <view class="product-card">
    <view class="card-title">{{ $t('商品信息') }}</view>
    <view class="product-list">
      <!-- 多商品模式：遍历 items 渲染每个商品 -->
      <view
        v-for="(item, idx) in productList"
        :key="idx"
        class="product-row"
        @click="goDetail(item)"
      >
        <image :src="item.image" mode="aspectFill" class="product-image" />
        <view class="product-info">
          <text class="product-name">{{ item.store_name || '' }}</text>
          <text v-if="getSkuText(item)" class="sku-text">{{ getSkuText(item) }}</text>
          <view class="product-meta">
            <text v-if="!isGiftReceiver" class="product-price">¥{{ getItemPrice(item) }}</text>
            <text class="product-qty">x{{ item.cart_num || 1 }}</text>
          </view>
        </view>
      </view>
    </view>
  </view>
</template>

<script>
/**
 * 商品卡片组件
 * 展示订单中的商品列表，点击单个商品跳转商品详情
 */
export default {
  name: 'ProductCard',
  // DIY 通用化契约：组件唯一标识
  componentKey: 'product_card',
};
</script>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /** section 对象，含 props.product 与 _ctx.payPrice */
  section: { type: Object, required: true },
});

const emit = defineEmits(['click']);

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 引擎注入的全局状态 */
const ctx = computed(() => (props.section && props.section._ctx) || {});
/** 礼品领取人查看：不展示商品价格（与旧版 is_gift=2 一致） */
const isGiftReceiver = computed(() => ctx.value.viewerRole === 'gift_receiver');
/** 商品信息对象 */
const product = computed(() => p.value.product || {});
/**
 * 商品列表：从 product.items 读取，兼容无 items 的降级场景
 * @return {array} 商品数组
 */
const productList = computed(() => {
  var items = product.value.items || [];
  if (items.length > 0) {
    return items;
  }
  // 降级：无 items 时用顶层 title/image 构造单条记录
  return [{
    image: product.value.image || '',
    store_name: product.value.title || '',
    cart_num: product.value.total_num || 0,
    price: product.value.price || ctx.value.payPrice || '0.00',
    product_id: p.value.product_id || 0,
  }];
});

/**
 * 商品金额：展示规格原价（true_price 为优惠后单价，不作为商品金额展示）
 * @param {object} item 商品项
 * @return {string} 金额
 */
function getItemPrice(item) {
  var attr = item.attr_info || {};
  // 订单快照 item.price 可能是商品级价格，规格金额以 attr_info.price 为准
  var val = attr.price || item.price || '0.00';
  var num = parseFloat(val);
  return isNaN(num) ? String(val) : num.toFixed(2);
}

/**
 * 提取规格文本
 * @param {object} item 商品项
 * @return {string} 规格文本
 */
function getSkuText(item) {
  if (item.sku) return item.sku;
  if (item.attr_info && item.attr_info.suk) return item.attr_info.suk;
  return '';
}

/**
 * 点击商品跳转详情
 * @param {object} item 商品项
 */
function goDetail(item) {
  var productId = item.product_id || 0;
  if (productId > 0) {
    emit('click', { product_id: productId });
  }
}
</script>

<style scoped>
.product-card { margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 30rpx; }
.card-title { font-size: 30rpx; font-weight: bold; color: #333; margin-bottom: 20rpx; }
.product-row { display: flex; align-items: center; padding: 20rpx 0; border-bottom: 1rpx solid #f5f5f5; }
.product-row:first-child { padding-top: 0; }
.product-row:last-child { border-bottom: none; padding-bottom: 0; }
.product-image { width: 140rpx; height: 140rpx; border-radius: 12rpx; flex-shrink: 0; }
.product-info { flex: 1; margin-left: 20rpx; display: flex; flex-direction: column; }
.product-name { font-size: 28rpx; color: #333; line-height: 1.4; }
.sku-text { font-size: 24rpx; color: #999; margin-top: 6rpx; }
.product-meta { display: flex; justify-content: space-between; align-items: center; margin-top: 12rpx; }
.product-price { font-size: 30rpx; color: var(--view-theme, #e93323); font-weight: 600; }
.product-qty { font-size: 26rpx; color: #999; }
</style>
