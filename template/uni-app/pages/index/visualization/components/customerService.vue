<template>
	<view style="touch-action: none;">
		<!-- #ifdef H5 || APP-PLUS -->
		<view class="customerService" :style="'top:'+topConfig" @touchmove.stop.prevent="setTouchMove" v-if="isShow && logoConfig && !isIframe">
			<navigator class="pictrue" url="/pages/extension/customer_list/chat" hover-class="none">
				<image :src="logoConfig"></image>
			</navigator>
		</view>
		<view class="customerService borderService" :style="'top:'+topConfig" v-if="logoConfig && isIframe">
			<view class="pictrue">
				<image :src="logoConfig"></image>
			</view>
		</view>
		<view class="customerService borderService" :style="'top:'+topConfig" v-if="!logoConfig && isIframe">
			<view class="pictrue">{{$t(`客服`)}}</view>
		</view>
		<!-- #endif -->
		<!-- #ifdef MP -->
		<view class="customerService" :style="'top:'+topConfig" @touchmove.stop.prevent="setTouchMove" v-if="routineContact === '0' && logoConfig">
			<navigator class="pictrue" url="/pages/extension/customer_list/chat" hover-class="none">
				<image :src="logoConfig"></image>
			</navigator>
		</view>
		<button class="customerService-sty" :style="'top:'+topConfig" @touchmove.stop.prevent="setTouchMove" open-type='contact' v-if="routineContact === '1' && logoConfig">
			<image class="pictrue" :src="logoConfig"></image>
		</button>
		<!-- #endif -->
	</view>
</template>

<script>
	let app = getApp();
	export default {
		name: 'customerService',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			},
		},
		watch: {
			dataConfig: {
				immediate: true,
				handler(nVal, oVal) {
					if(nVal){
						this.logoConfig = nVal.imgUrl.url;
						this.isShow = nVal.isShow.val;
						this.routineContact = nVal.routine_contact_type;
					}
				}
			}
		},
		data() {
			return {
				logoConfig: '',
				topConfig: '200px',
				name: this.$options.name,
				isIframe: false,
				isShow: true,
				routineContact:'0'
				}
		},
		created() {
			this.isIframe = app.globalData.isIframe;
		},
		methods: {
			setTouchMove(e) {
				var that = this;
				if (e.touches[0].clientY < 545 && e.touches[0].clientY > 66) {
					that.topConfig = e.touches[0].clientY+'px'
				}
			}
		}
	}
</script>

<style lang="scss">
	.borderShow{
		position: fixed;
	}
	.borderShow .borderService::after{
		content: ' ';
		position: absolute;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		border:1px dashed #007AFF;
		box-sizing: border-box;
	}
	.customerService,.customerService-sty {
		position: fixed !important;
		right: 20rpx;
		z-index: 40;
		.pictrue {
			width: 86rpx;
			height: 86rpx;
			text-align: center;
			line-height: 86rpx;
			color: #fff;
			border-radius: 50%;
			background-color: #ccc;

			image {
				width: 100%;
				height: 100%;
				border-radius: 50%;
			}
		}
	}
	.customerService-sty{
		background-color: rgba(0,0,0,0) !important;
	}
</style>