<!--
  价格明细组件
  展示商品总价、运费、优惠券抵扣、积分抵扣等金额信息
-->
<template>
  <view class="money-list">
    <!-- 商品总价（非积分兑换模式） -->
    <view class="item acea-row row-between-wrapper" v-if="!integralTotal">
      <view>{{ $t(`商品总价`) }}</view>
      <view class="money">￥{{ totalPrice }}</view>
    </view>
    <!-- 积分兑换模式：可用积分 -->
    <view class="item acea-row row-between-wrapper" v-if="integralTotal">
      <view>{{ $t(`可用积分`) }}</view>
      <view class="money">{{ usableIntegral }}</view>
    </view>
    <!-- 礼品附加费（送礼订单） -->
    <view class="item acea-row row-between-wrapper" v-if="parseFloat(giftPrice) > 0">
      <view>{{ $t(`礼品附加费`) }}</view>
      <view class="money">￥{{ parseFloat(giftPrice).toFixed(2) }}</view>
    </view>
    <!-- 运费（仅支持快递配送时显示） -->
    <view class="item acea-row row-between-wrapper" v-if="showPostage">
      <view>{{ $t(`快递费用`) }}</view>
      <view class="money" v-if="!integralTotal && payPostage > 0">￥{{ payPostage }}</view>
      <view class="money" v-else-if="!integralTotal">{{ $t(`免运费`) }}</view>
      <view class="money" v-else>{{ $t(`免运费`) }}</view>
    </view>
    <!-- 上门服务费（仅上门服务时显示） -->
    <view class="item acea-row row-between-wrapper" v-if="showDoorPostage">
      <view>{{ $t(`上门服务费`) }}</view>
      <view class="money" v-if="!integralTotal && parseFloat(doorPostage) > 0">￥{{ formattedDoorPostage }}</view>
      <view class="money" v-else-if="!integralTotal">{{ $t(`免费上门`) }}</view>
      <view class="money" v-else>{{ $t(`免费上门`) }}</view>
    </view>
    <!-- 会员等级折扣（确认接口 priceGroup.levelPrice / 重算接口 level_price） -->
    <view class="item acea-row row-between-wrapper" v-if="parseFloat(levelPrice) > 0">
      <view>{{ $t(`用户等级优惠`) }}</view>
      <view class="money money-minus">-￥{{ parseFloat(levelPrice).toFixed(2) }}</view>
    </view>
    <!-- 付费会员折扣 -->
    <view class="item acea-row row-between-wrapper" v-if="parseFloat(memberPrice) > 0">
      <view>{{ $t(`付费会员优惠`) }}</view>
      <view class="money money-minus">-￥{{ parseFloat(memberPrice).toFixed(2) }}</view>
    </view>
    <!-- 会员运费折扣 -->
    <view class="item acea-row row-between-wrapper" v-if="parseFloat(postageDiscount) > 0">
      <view>{{ $t(`会员运费优惠`) }}</view>
      <view class="money money-minus">-￥{{ parseFloat(postageDiscount).toFixed(2) }}</view>
    </view>
    <!-- 优惠券抵扣 -->
    <view class="item acea-row row-between-wrapper" v-if="couponPrice > 0">
      <view>{{ $t(`优惠券抵扣`) }}</view>
      <view class="money money-minus">-￥{{ couponPrice }}</view>
    </view>
    <!-- 积分抵扣 -->
    <view class="item acea-row row-between-wrapper" v-if="integralPrice > 0">
      <view>{{ $t(`积分抵扣`) }}</view>
      <view class="money money-minus">-￥{{ parseFloat(integralPrice).toFixed(2) }}</view>
    </view>
  </view>
</template>

<script setup>
import { computed } from 'vue';

defineOptions({ name: 'OrderPriceDetail' });

const props = defineProps({
  /** 商品总价 */
  totalPrice: { type: [Number, String], default: '0.00' },
  /** 运费 */
  payPostage: { type: [Number, String], default: 0 },
  /** 用户等级优惠金额 */
  levelPrice: { type: [Number, String], default: 0 },
  /** 付费会员优惠金额 */
  memberPrice: { type: [Number, String], default: 0 },
  /** 会员运费优惠金额 */
  postageDiscount: { type: [Number, String], default: 0 },
  /** 优惠券抵扣金额 */
  couponPrice: { type: [Number, String], default: 0 },
  /** 积分抵扣金额 */
  integralPrice: { type: [Number, String], default: 0 },
  /** 积分兑换总额（非0时替换商品总价显示） */
  integralTotal: { type: [Number, String], default: 0 },
  /** 礼品附加费（送礼订单按件累计） */
  giftPrice: { type: [Number, String], default: 0 },
  /** 可用积分（积分兑换模式展示） */
  usableIntegral: { type: [Number, String], default: 0 },
  /** 是否显示快递费用行（快递配送关闭时隐藏） */
  showPostage: { type: Boolean, default: true },
  /** 上门服务费 */
  doorPostage: { type: [Number, String], default: 0 },
  /** 是否显示上门服务费行（上门服务时显示） */
  showDoorPostage: { type: Boolean, default: false },
});

/** 上门服务费用格式化：保留两位小数 */
const formattedDoorPostage = computed(() => {
  const v = parseFloat(props.doorPostage || 0);
  return isNaN(v) ? '0.00' : v.toFixed(2);
});
</script>

<style scoped>
/* 价格明细 */
.money-list {
  margin: 20rpx;
  background: #fff;
  border-radius: 12rpx;
  padding: 24rpx 30rpx;
}
.money-list .item {
  font-size: 28rpx;
  color: #282828;
  display: flex;
  justify-content: space-between;
  padding: 12rpx 0;
}
.money-list .item .money {
  color: #868686;
}
.money-list .item .money-minus {
  color: var(--view-theme, #e93323);
}
</style>
