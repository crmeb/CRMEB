<!--
  订单确认页商品信息卡片
  展示商品图、名称、规格、单价、数量
-->
<template>
  <view class="product-section" v-if="visible">
    <view class="product-card">
      <image class="product-image" :src="image" mode="aspectFill"></image>
      <view class="product-detail">
        <view class="product-name line2">{{ name }}</view>
        <view class="product-sku" v-if="skuLabel">{{ $t(`规格：`) }}{{ skuLabel }}</view>
        <view class="product-price-row">
          <text class="product-price" v-if="!integralPrice">￥{{ price }}</text>
          <text class="product-price integral" v-else>{{ integralPrice }}{{ $t(`积分`) }}</text>
          <text class="product-num">x{{ num }}</text>
        </view>
      </view>
    </view>
  </view>
</template>

<script setup>
defineOptions({ name: 'OrderProductCard' });

defineProps({
  /** 商品主图 */
  image: { type: String, default: '' },
  /** 商品名称 */
  name: { type: String, default: '' },
  /** 规格文案 */
  skuLabel: { type: String, default: '' },
  /** 单价 */
  price: { type: [Number, String], default: 0 },
  /** 购买数量 */
  num: { type: [Number, String], default: 1 },
  /** 积分价（非0时替换金额显示） */
  integralPrice: { type: [Number, String], default: 0 },
  /** 是否显示 */
  visible: { type: Boolean, default: true },
});
</script>

<style scoped>
/* 商品区域 */
.product-section {
  margin: 20rpx;
}
.product-card {
  display: flex;
  background: #fff;
  border-radius: 12rpx;
  padding: 24rpx;
}
.product-image {
  width: 180rpx;
  height: 180rpx;
  border-radius: 8rpx;
  background: #f0f0f0;
  flex-shrink: 0;
}
.product-detail {
  flex: 1;
  margin-left: 20rpx;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
.product-name {
  font-size: 28rpx;
  font-weight: bold;
  color: #282828;
  line-height: 1.4;
}
.product-sku {
  font-size: 24rpx;
  color: #999;
  margin-top: 8rpx;
}
.product-price-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 12rpx;
}
.product-price {
  font-size: 32rpx;
  font-weight: bold;
  color: var(--view-theme, #e93323);
}
.product-price.integral {
  font-size: 30rpx;
}
.product-num {
  font-size: 26rpx;
  color: #999;
}
.line2 {
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}
</style>
