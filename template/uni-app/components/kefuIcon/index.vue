<template>
	<!-- #ifdef APP-PLUS || H5 -->
	<text class="acea-row row-center-wrapper cartf iconfont icon-kefu3" :style="{ top: top + 'px'}" @click="goCustomer"
		@touchmove.stop.prevent="setTouchMove"></text>
	<!-- #endif -->
	<!-- #ifdef MP -->
	<view v-if="routineContact == 0" class="acea-row row-center-wrapper cartf iconfont icon-kefu3"
		:style="{ top: top + 'px'}" @touchmove.stop.prevent="setTouchMove" @click="goCustomer"></view>
	<button class="acea-row row-center-wrapper cartf iconfont icon-kefu3" open-type='contact'
		:style="{ top: top + 'px'}" @touchmove.stop.prevent="setTouchMove"
		v-else-if="routineContact==1 && !goodsCon"></button>
	<button class="acea-row row-center-wrapper cartf iconfont icon-kefu3" open-type='contact'
		:send-message-title="storeInfo.store_name" :send-message-img="storeInfo.image"
		:send-message-path="`/pages/goods_details/index?id=${storeInfo.id}`" show-message-card
		:style="{ top: top + 'px'}" @touchmove.stop.prevent="setTouchMove"
		v-else-if="routineContact==1 && goodsCon"></button>
	<!-- #endif -->
</template>

<script>
	import {
		getCustomer
	} from '@/utils/index.js'
	let app = getApp();
	export default {
		name: "kefuIcon",
		props: {
			ids: {
				type: Number,
				default: 0
			},
			routineContact: {
				type: Number,
				default: 0
			},
			storeInfo: {
				type: Object,
				default: () => {}
			},
			goodsCon: {
				type: Number,
				default: 0
			}
		},
		data: function() {
			return {
				top: "480"
			};
		},
		mounted() {
			// #ifdef H5
			this.top = parseFloat(window.innerHeight) - 200
			// #endif
		},
		methods: {
			goCustomer() {
				getCustomer(`/pages/extension/customer_list/chat?productId=${this.ids}`)
			},
			setTouchMove(e) {
				let that = this;
				if (e.touches[0].clientY < 480 && e.touches[0].clientY > 66) {
					that.top = e.touches[0].clientY
				}
			}
		},
		created() {}
	};
</script>

<style lang="scss">
	.cartf {
		width: 96rpx;
		height: 96rpx;
		background: rgba(255, 255, 255, 0.8);
		box-shadow: 0 3rpx 16rpx rgba(0, 0, 0, 0.08);
		backdrop-filter: blur(10px);
		border-radius: 50%;
		font-size: 47rpx;
		color: #666;
		position: fixed;
		right: 15rpx;
		z-index: 9;
	}
</style>
