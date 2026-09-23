<template>
	    <view class='swiper'>
	        <swiper :autoplay="autoplay" :circular="circular" :interval="interval" :duration="duration" @change="swiperChange">
	            <block v-for="(item,index) in imgUrls" :key="index">
	              <swiper-item>
	                <navigator :render-link="false" :url="item.link" style='width:100%;height:100%;' hover-class='none'><image :src="item.img" class="slide-image"/></navigator>
	              </swiper-item>
	            </block>
	        </swiper>
	        <view class="dots acea-row">
	          <view class="dot" :class="index == currentSwiper ? 'active' : ''" v-for="(item,index) in imgUrls" :key="index"></view>
	        </view>
	    </view>
</template>

	<script setup>
		import { ref } from 'vue';

		defineProps({
			 imgUrls: {
			 	type: Array,
			 	default: function(){
			 		return [];
			 	}
			 }
		});

		const circular = ref(true);
		const autoplay = ref(true);
		const interval = ref(3000);
		const duration = ref(500);
		const currentSwiper = ref(0);

		function swiperChange(e) {
			currentSwiper.value = e.detail.current
		}
	</script>

<style scoped lang="scss">
	.swiper{width:100%;height:282rpx;position:relative;}
	.swiper swiper{width:100%;height:100%;position:relative;}
	.swiper swiper .slide-image{width:100%;height:100%;}
	.swiper .dots{position:absolute;right:40rpx;bottom:20rpx;}
	.swiper .dots .dot{width:12rpx;height:12rpx;border:2rpx solid #fff;border-radius:50%;margin-right:15rpx;}
	.swiper .dots .dot.active{border-color:#e93323;background-color:#e93323;}
</style>
