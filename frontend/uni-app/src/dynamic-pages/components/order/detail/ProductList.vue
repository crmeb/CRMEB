<!--
  商品列表组件（赠品/推荐商品，DIY 通用化版）
  展示多个商品的图片、名称、价格、数量
  数据来源：section.props.items + section.props.title
-->
<template>
  <view class="info-card product-list-card" v-if="items && items.length > 0">
    <view class="card-title">{{ title || $t('商品列表') }}</view>
    <view
      v-for="(item, idx) in items"
      :key="idx"
      class="product-list-item acea-row row-middle"
    >
      <image :src="item.image" mode="aspectFill" class="list-item-image" />
      <view class="list-item-info">
        <text class="list-item-name">{{ item.name || item.store_name || '' }}</text>
        <text v-if="item.price" class="list-item-price">¥{{ item.price }}</text>
      </view>
      <text class="list-item-qty">x{{ item.num || item.cart_num || 1 }}</text>
    </view>
  </view>
</template>

<script>
/**
 * 商品列表组件
 * 用于赠品列表、推荐商品列表等多商品场景
 */
export default {
  name: 'ProductList',
  // DIY 通用化契约：组件唯一标识
  componentKey: 'product_list',
};
</script>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /** section 对象，含 props.items/title */
  section: { type: Object, required: true },
});

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 引擎注入的全局状态（预留，当前组件不依赖） */
const ctx = computed(() => (props.section && props.section._ctx) || {});
/** 商品数组，每项含 {image, name/store_name, price, num/cart_num} */
const items = computed(() => p.value.items || []);
/** 列表标题（如"赠品"、"推荐商品"） */
const title = computed(() => p.value.title || '');
</script>

<style scoped>
.product-list-card { margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 30rpx; }
.card-title { font-size: 30rpx; font-weight: bold; color: #333; margin-bottom: 20rpx; }
.product-list-item { display: flex; align-items: center; padding: 16rpx 0; border-bottom: 1rpx solid #f5f5f5; }
.product-list-item:last-child { border-bottom: none; }
.list-item-image { width: 100rpx; height: 100rpx; border-radius: 8rpx; flex-shrink: 0; }
.list-item-info { flex: 1; margin-left: 16rpx; display: flex; flex-direction: column; }
.list-item-name { font-size: 28rpx; color: #333; }
.list-item-price { font-size: 26rpx; color: var(--view-theme, #e93323); margin-top: 4rpx; }
.list-item-qty { font-size: 26rpx; color: #999; flex-shrink: 0; }
</style>
