<template>
	<view>
		<view class='coupon-window' :class='window==true?"on":""' :style="'background-image: url('+couponImage+')'">
			<view class='couponWinList'>
				<view class='item acea-row row-between-wrapper' v-for="(item,index) in couponList" :key="index">
					<view class='money font-color'>￥<text class='num'>{{item.coupon_price}}</text></view>
					<view class='text'>
						<view class='name'>购物满{{item.use_min_price}}减{{item.coupon_price}}</view>
						<view>{{item.start_time ? item.start_time+'-' : ''}}{{item.end_time === 0 ? '不限时': item.end_time}}</view>
					</view>
				</view>
			</view>
			<view class='lid'>
				<navigator hover-class='none' url='/pages/users/user_get_coupon/index' class='bnt font-color'>立即领取</navigator>
				<view class='iconfont icon-guanbi3' @click="close"></view>
			</view>
		</view>
		<view class='mask' catchtouchmove="true" :hidden="window==false"></view>
	</view>
</template>

<script>
	export default {

		props: {
			window: {
				type: Boolean,
				default: false,
			},
			couponList: {
				type: Array,
				default: function() {
					return []
				},
			},
			couponImage: {
				type: String,
				default: '',
			},
		},
		data() {
			return {

			};
		},
		methods: {
			close:function(){
			      this.$emit('onColse');
			    }
		}
	}
</script>

<style scoped lang="scss">
	.coupon-window {
		background-repeat: no-repeat;
		background-size: 100% 100%;
		width: 630rpx;
		height: 649rpx;
		position: fixed;
		top: 20%;
		z-index: 99;
		left: 50%;
		margin-left: -305rpx;
		transform: translate3d(0, -200%, 0);
		transition: all .3s cubic-bezier(.25, .5, .5, .9);
	}

	.coupon-window.on {
		transform: translate3d(0, 0, 0);
	}

	.coupon-window .couponWinList {
		width: 480rpx;
		margin: 157rpx 0 0 60rpx;
		height: 415rpx;
		overflow: auto;
	}

	.coupon-window .couponWinList .item {
		width: 100%;
		height: 120rpx;
		background-color: #fff;
		position: relative;
		margin-bottom: 17rpx;
	}

	.coupon-window .couponWinList .item::after {
		content: '';
		position: absolute;
		width: 18rpx;
		height: 18rpx;
		border-radius: 50%;
		background-color: #f2443a;
		left: 25.5%;
		bottom: 0;
		margin-bottom: -9rpx;
	}

	.coupon-window .couponWinList .item::before {
		content: '';
		position: absolute;
		width: 18rpx;
		height: 18rpx;
		border-radius: 50%;
		background-color: #f2443a;
		left: 25.5%;
		top: 0;
		margin-top: -9rpx;
	}

	.coupon-window .couponWinList .item .money {
		width: 130rpx;
		border-right: 1px dashed #ddd;
		height: 100%;
		text-align: center;
		line-height: 120rpx;
		font-size: 26rpx;
		font-weight: bold;
	}

	.coupon-window .couponWinList .item .money .num {
		font-size: 40rpx;
	}

	.coupon-window .couponWinList .item .text {
		width: 349rpx;
		font-size: 22rpx;
		color: #999;
		padding: 0 29rpx;
		box-sizing: border-box;
	}

	.coupon-window .couponWinList .item .text .name {
		font-size: 26rpx;
		color: #282828;
		font-weight: bold;
		margin-bottom: 9rpx;
	}

	.coupon-window .lid {
		background-image: url('data:image/png;base64,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');
		background-repeat: no-repeat;
		background-size: 100% 100%;
		width: 573rpx;
		height: 224rpx;
		position: fixed;
		left: 50%;
		top: 20%;
		margin: 424rpx 0 0 -296rpx;
	}

	.coupon-window .lid .bnt {
		font-size: 29rpx;
		width: 440rpx;
		height: 80rpx;
		border-radius: 40rpx;
		background-color: #f9f1d3;
		text-align: center;
		line-height: 80rpx;
		font-weight: bold;
		margin: 98rpx auto 0 auto;
	}

	.coupon-window .lid .iconfont {
		color: #fff;
		font-size: 60rpx;
		text-align: center;
		margin-top: 87rpx;
	}
</style>
