<!--
  评价卡片组件（DIY 通用化契约版）
  展示商品评价（星级+文字+图片+商家回复），图片可预览
  数据来源：section.props.items（评价数组）
-->
<template>
  <view class="info-card review-card" v-if="items && items.length > 0">
    <view class="card-title">{{ $t('商品评价') }}</view>
    <view
      v-for="(item, idx) in items"
      :key="idx"
      class="review-item"
    >
      <!-- 商品名 + 星级 -->
      <view class="review-header acea-row row-between">
        <text class="review-product">{{ item.product_name || '' }}</text>
        <view class="review-rating">
          <text
            v-for="n in 5"
            :key="n"
            class="iconfont"
            :class="n <= (item.rating || 0) ? 'icon-xingxing' : 'icon-xingxing1'"
          ></text>
        </view>
      </view>
      <!-- 评价文字 -->
      <view v-if="item.comment" class="review-comment">{{ item.comment }}</view>
      <!-- 评价图片 -->
      <view v-if="item.images && item.images.length" class="review-images acea-row">
        <image
          v-for="(img, imgIdx) in item.images"
          :key="imgIdx"
          :src="img"
          mode="aspectFill"
          class="review-image"
          @tap="$emit('preview', { images: item.images, index: imgIdx })"
        />
      </view>
      <!-- 商家回复 -->
      <view v-if="item.merchant_reply" class="review-reply">
        <text class="reply-label">{{ $t('商家回复：') }}</text>
        <text class="reply-text">{{ item.merchant_reply }}</text>
      </view>
    </view>
  </view>
</template>

<script>
/**
 * 评价卡片组件
 * 展示用户对商品的评价，含星级、文字、图片、商家回复
 * 图片点击 emit('preview', {images, index}) 由引擎调起预览
 * 通过 componentKey='review_card' 匹配 section.component
 */
export default {
  name: 'ReviewCard',
  // DIY 引擎 componentKey 标识
  componentKey: 'review_card',
};
</script>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /** section 对象，含 props.items 和 _ctx 全局状态 */
  section: { type: Object, required: true },
});

defineEmits(['preview']);

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 引擎注入的全局状态（预留，当前 ReviewCard 不依赖全局状态） */
const ctx = computed(() => (props.section && props.section._ctx) || {});
/** 评价数组（后端下发在 section.props.items），每项含 {product_name, rating, comment, images[], merchant_reply} */
const items = computed(() => p.value.items || []);
</script>

<style scoped>
.review-card { margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 30rpx; }
.card-title { font-size: 30rpx; font-weight: bold; color: #333; margin-bottom: 20rpx; }
.review-item { padding-bottom: 24rpx; border-bottom: 1rpx solid #f5f5f5; margin-bottom: 24rpx; }
.review-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
.review-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12rpx; }
.review-product { font-size: 28rpx; color: #333; font-weight: 500; }
.review-rating .iconfont { font-size: 24rpx; color: #ffcc00; margin-left: 4rpx; }
.review-rating .icon-xingxing1 { color: #ddd; }
.review-comment { font-size: 26rpx; color: #666; line-height: 1.6; margin-bottom: 12rpx; }
.review-images { gap: 12rpx; margin-bottom: 12rpx; }
.review-image { width: 160rpx; height: 160rpx; border-radius: 8rpx; margin-right: 12rpx; }
.review-reply { background: #f9f9fb; border-radius: 8rpx; padding: 16rpx; }
.reply-label { font-size: 24rpx; color: #999; }
.reply-text { font-size: 24rpx; color: #666; }
</style>
