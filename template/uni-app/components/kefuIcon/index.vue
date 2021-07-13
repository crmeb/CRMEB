<template>
	<navigator :url="'/pages/customer_list/chat?productId='+ids" hover-class="none" class="acea-row row-center-wrapper cartf iconfont icon-kefu3" :style="{ top: top + 'px'}" @touchmove.stop.prevent="setTouchMove"></navigator>
</template>

<script>
	let app = getApp();
	export default {
		name: "kefuIcon",
		props: {
			ids: {
				type: Number,
				default: 0,
			}
		},
		data: function() {
			return {
				top: "480"
			};
		},
		mounted() {
			// #ifdef H5
				this.top =  parseFloat(window.innerHeight) -200
			// #endif
		},
		methods: {
			setTouchMove(e) {
				let that = this;
				if (e.touches[0].clientY < 480 && e.touches[0].clientY > 66) {
					that.top = e.touches[0].clientY
				}
			}
		},
		created() {
		}
	};
</script>

<style lang="scss">
	.cartf{
		width: 96rpx;
		height: 96rpx;
		background: #FFFFFF;
		box-shadow: 0 3rpx 16rpx rgba(0, 0, 0, 0.08);
		border-radius: 50%;
		font-size: 47rpx;
		color: #666;
		position: fixed;
		right: 15rpx;
		z-index: 9;
	}
</style>
