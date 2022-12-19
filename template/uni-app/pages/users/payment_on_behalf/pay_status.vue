<template>
	<view :style="colorStyle" class="main">
		<view class='payment-status'>
			<view class='iconfont icons icon-duihao2 bg-color'></view>
			<view class='status'>{{$t(`代付成功`)}}</view>
			<view class='wrapper'>
				<view class='itemCom'> <text class="rmb">{{$t(`￥`)}}</text> {{resData.pay_price || 0.00}}</view>
			</view>
			<view class="head-other">
				<view class="user-img">
					<image class="" :src="resData.avatar" mode=""></image>
				</view>
				<view class="order-status">
					{{$t(`谢谢你为我付款，还可以再去看看其他商品哟~`)}}
				</view>
			</view>
		</view>
		<button @click="goIndex" class='returnBnt' formType="submit" hover-class='none'>{{$t(`返回首页`)}}</button>
	</view>
</template>

<script>
	import {
		getOrderDetail
	} from '@/api/order.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		mapGetters
	} from "vuex";
	import colors from "@/mixins/color";
	import {
		friendDetail
	} from '@/api/user.js'
	export default {
		mixins: [colors],
		data() {
			return {
				loading: false,
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false, //是否隐藏授权
				resData: {}
			};
		},
		computed: mapGetters(['isLogin']),
		watch: {
			isLogin: {
				handler: (newV, oldV) => {
					if (newV) {
						this.getDetail();
					}
				},
				deep: true
			}
		},
		onLoad(options) {
			this.options = options
			if (!options.order_id) return this.$util.Tips({
				title: this.$t(`缺少参数无法查看订单支付状态`)
			}, {
				tab: 3,
				url: 1
			});
			this.orderId = options.order_id;
		},
		onShow() {
			if (this.isLogin) {
				this.getDetail();
			} else {
				toLogin();
			}
		},
		methods: {
			getDetail() {
				friendDetail(this.orderId).then(res => {
					if (this.resData.paid == 0) {
						return this.$util.Tips({
							title: this.$t(`该订单暂未支付`)
						}, {
							tab: 3,
							url: 1
						});
					}
					this.resData = res.data.info

				})
			},
			openTap() {
				this.$set(this, 'couponsHidden', !this.couponsHidden);
			},
			onLoadFun() {
				this.getDetail();
			},
			/**
			 * 支付完成查询支付状态
			 */
			getOrderPayInfo() {
				let that = this;
				uni.showLoading({
					title: that.$t(`正在加载中`)
				});
				getOrderDetail(that.orderId).then(res => {
					uni.hideLoading();
					that.$set(that, 'order_pay_info', res.data);
					uni.setNavigationBarTitle({
						title: res.data.paid ? that.$t(`支付成功`) : that.$t(`未支付`)
					});
					this.loading = true
				}).catch(err => {
					this.loading = true
					uni.hideLoading();
				});
			},
			/**
			 * 去首页关闭当前所有页面
			 */
			goIndex(e) {
				uni.switchTab({
					url: '/pages/index/index'
				});
			},

		}
	}
</script>

<style lang="scss" scoped>
	.main {
		width: 100%;
		height: 100vh;
		background-color: #fff;

		.payment-status {
			background-color: #fff;
			margin: 0rpx 30rpx 0 30rpx;
			border-radius: 10rpx;
			padding: 94rpx 0 60rpx 0;
			color: #333;

			.icons {
				font-size: 70rpx;
				width: 140rpx;
				height: 140rpx;
				border-radius: 50%;
				color: #fff;
				text-align: center;
				line-height: 140rpx;
				border: 6rpx solid #f5f5f5;
				margin: 0rpx auto 0 auto;
				background-color: #999;
			}

			.rmb {
				font-size: 33rpx;
			}

			.status {
				font-size: 32rpx;
				font-weight: bold;
				text-align: center;
				margin: 25rpx 0 7rpx 0;
			}

			.wrapper {
				text-align: center;
				color: #333;
				font-size: 66rpx;
				font-weight: bold;
			}

			.head-other {
				display: flex;
				align-items: center;
				justify-content: center;
				padding-top: 20rpx;

				.user-img {
					display: flex;
					align-items: center;

					image {
						width: 50rpx;
						height: 50rpx;
						border-radius: 50%;
					}
				}

				.order-status {
					margin-left: 20rpx;
					color: #666666;
					padding: 6rpx 13rpx;
					font-size: 24rpx;
				}
			}

		}

		.returnBnt {
			width: 80%;
			height: 86rpx;
			border-radius: 50rpx;
			color: #fff;
			font-size: 30rpx;
			text-align: center;
			line-height: 86rpx;
			margin: 0 auto;
			background-color: var(--view-theme);
		}
	}
</style>
