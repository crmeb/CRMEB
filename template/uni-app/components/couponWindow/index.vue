<template>
	<view :style="colorStyle">
		<view class='coupon-window' :class='window==true?"on":""'>
			<image class="co-bag" :src="imgHost + '/statics/images/co-bag.png'" mode=""></image>
			<view class='couponWinList'>
				<view class='item acea-row row-between-wrapper' v-for="(item,index) in couponList" :key="index">
					<view class='money font-color'>{{$t(`￥`)}}<text class='num'>{{item.coupon_price}}</text></view>
					<view class='text'>
						<view class='name'>{{$t(`购物满`)}}{{item.use_min_price}}{{$t(`减`)}}{{item.coupon_price}}</view>
						<view v-if="item.coupon_time">{{$t(`领取后`)}}{{item.coupon_time}}{{$t(`天内可用`)}}</view>
						<view v-else>
							{{item.start_time ? item.start_time+'-' : ''}}{{item.end_time === 0 ? $t(`不限时`) : item.end_time}}
						</view>
					</view>
				</view>
			</view>
			<view class='lid'>
				<navigator v-if="window" hover-class='none' url='/pages/users/user_get_coupon/index' class='bnt'>{{$t(`立即领取`)}}</navigator>
				<view class='iconfont icon-guanbi3' @click="close"></view>
			</view>
		</view>
		<view class='mask' catchtouchmove="true" :hidden="window==false"></view>
	</view>
</template>

<script>
	import colors from "@/mixins/color";
	import {HTTP_REQUEST_URL} from '@/config/app';
	export default {
		props: {
			window: {
				type: Boolean | String | Number,
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
		mixins: [colors],
		data() {
			return {
				imgHost:HTTP_REQUEST_URL
			};
		},
		methods: {
			close: function() {
				this.$emit('onColse');
			}
		}
	}
</script>


<style scoped lang="scss">
	.mask {
		z-index: 9999;
	}

	.coupon-window {
		width: 572rpx;
		height: 760rpx;
		position: fixed;
		top: 20%;
		z-index: 10000;
		left: 50%;
		margin-left: -286rpx;
		transform: translate3d(0, -200%, 0);
		transition: all .3s cubic-bezier(.25, .5, .5, .9);
		border-radius: 30rpx 30rpx 0 0;
		overflow-x: hidden;
	}

	.co-bag {
		width: 100%;
		height: 250rpx;
		z-index: 33333;
		top: -40rpx;
		position: absolute;
	}

	.coupon-window:after {
		width: 900rpx;
		height: 650rpx;
		position: absolute;
		top: 0%;
		left: 50%;
		z-index: 11111;
		margin-left: -450rpx;
		content: '';
		border-radius: 50% 50% 0 0;
		background: var(--view-theme);
	}

	.coupon-window.on {
		transform: translate3d(0, 0, 0);
	}

	.coupon-window .couponWinList {
		width: 480rpx;
		margin: 157rpx 0 0 50rpx;
		height: 340rpx;
		overflow-y: scroll;
	}

	.coupon-window .couponWinList .item {
		width: 100%;
		height: 120rpx;
		background-color: #fff;
		position: relative;
		margin-bottom: 17rpx;
		position: relative;
		z-index: 99999;
	}

	.coupon-window .couponWinList .item .left {
		border-right: 1px dashed #ccc;
	}

	.coupon-window .couponWinList .label {
		width: 28rpx;
		height: 64rpx;
		display: block;
		position: absolute;
		top: 0;
		right: 12rpx;
	}

	.coupon-window .couponWinList .item::after {
		content: '';
		position: absolute;
		width: 18rpx;
		height: 18rpx;
		border-radius: 50%;
		background-color: var(--view-theme);
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
		background-color: var(--view-theme);
		left: 25.5%;
		top: 0;
		margin-top: -9rpx;
	}

	.coupon-window .couponWinList .item .money {
		width: 130rpx;
		text-align: center;
		font-size: 26rpx;
		font-weight: bold;
	}

	.coupon-window .couponWinList .item .min_money {
		color: #ccc;
		font-size: 18rpx;
		text-align: center;
	}

	.coupon-window .couponWinList .item .money .num {
		font-size: 40rpx;
	}

	.coupon-window .couponWinList .item .text {
		width: 349rpx;
		font-size: 22rpx;
		color: #ccc;
		padding: 0 29rpx;
		box-sizing: border-box;
	}

	.coupon-window .couponWinList .item .text .image {
		width: 32rpx;
		height: 32rpx;
		display: inline-block;
		vertical-align: bottom;
		margin-right: 10rpx;
	}

	.coupon-window .couponWinList .item .text .name {
		font-size: 26rpx;
		color: var(--view-priceColor);
		font-weight: bold;
		margin-bottom: 9rpx;
		width: 250rpx;
	}

	.coupon-window .lid {
		background: rgba(255, 255, 255, 0.2);
		width: 582rpx;
		height: 224rpx;
		position: fixed;
		z-index: 22222;
		left: 50%;
		top: 0%;
		margin: 424rpx 0 0 -296rpx;
	}

	.coupon-window .lid:after {
		width: 920rpx;
		height: 280rpx;
		position: absolute;
		top: -100%;
		left: 50%;
		z-index: 22222;
		margin-left: -460rpx;
		content: '';
		border-radius: 0 0 50% 50%;
		background: var(--view-theme);
	}

	.coupon-window .lid .bnt {
		font-size: 29rpx;
		width: 440rpx;
		height: 80rpx;
		border-radius: 40rpx;
		background: linear-gradient(90deg, #FFCA52 0%, #FE960F 100%);
		text-align: center;
		line-height: 80rpx;
		font-weight: bold;
		margin: 98rpx auto 0 auto;
		color: #fff;
	}

	.coupon-window .lid .iconfont {
		color: #fff;
		font-size: 60rpx;
		text-align: center;
		margin-top: 87rpx;
	}
</style>
