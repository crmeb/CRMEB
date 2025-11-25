<template>
	<view :style="colorStyle">
		<view class="mask" @touchmove.prevent :hidden="isShow === false"></view>
		<view class="product-window" :class="{'on':isShow}">
			<!-- 关闭 icon -->
			<!-- <view class="iconfont icon-guanbi" @click="closeAttr"></view> -->
			<view class="mp-data">
				<text class="mp-name">{{mpData.siteName}}{{$t(`服务与隐私协议`)}}</text>
			</view>
			<view class="trip-msg">
				<view class="trip">
					{{$t(`欢迎您使用${mpData.siteName}！请仔细阅读以下内容，并作出适当的选择：`)}}
				</view>
			</view>
			<view class="trip-title">
				{{$t(`隐私政策概要`)}}
			</view>
			<view class="trip-msg">
				<view class="trip">
					{{$t(`当您点击同意并开始时用产品服务时，即表示您已理解并同息该条款内容，该条款将对您产生法律约束力。如您拒绝，将无法继续下一步操作。`)}}
				</view>
			</view>
			<view class="main-color" @click.stop="privacy(3)">{{$t(`点击阅读`)}}{{agreementName}}</view>
			<view class="bottom">
				<button class="save open" type="default" id="agree-btn" open-type="agreePrivacyAuthorization"
					@agreeprivacyauthorization="handleAgree">{{$t(`同意并继续`)}}</button>
				<button class="reject" @click="rejectAgreement">
					{{$t(`取消`)}}
				</button>
			</view>
		</view>
	</view>

</template>

<script>
	import colors from "@/mixins/color";
	import {
		userEdit,
	} from '@/api/user.js';
	export default {
		mixins: [colors],
		data() {
			return {
				isShow: false,
				agreementName: '',
				mpData: uni.getStorageSync('copyRight'),
			};
		},
		mounted() {
			wx.getPrivacySetting({
				success: res => {
					if (res.needAuthorization) {
						// 需要弹出隐私协议
						this.isShow = true
						this.agreementName = res.privacyContractName
					} else {
						this.$emit('onAgree');
						// 用户已经同意过隐私协议，所以不需要再弹出隐私协议，也能调用已声明过的隐私接口
					}
				},
				fail: () => {},
				complete: () => {}
			})
		},
		methods: {
			// 同意
			handleAgree() {
				this.isShow = false
				this.$emit('onAgree');
			},
			// 拒绝
			rejectAgreement() {
				this.isShow = false
				uni.switchTab({
					url: '/pages/index/index'
				})
				this.$emit('onReject');
			},
			closeAttr() {
				this.$emit('onCloseAgePop');
			},
			// 跳转协议
			privacy(type) {
				uni.navigateTo({
					url: "/pages/users/privacy/index?type=" + type
				})
			},
		}
	}
</script>
<style>
	.pl-sty {
		color: #999999;
		font-size: 30rpx;
	}
</style>
<style scoped lang="scss">
	.product-window.on {
		transform: translate3d(0, 0, 0);
	}

	.mask {
		z-index: 99;
	}

	.product-window {
		position: fixed;
		bottom: 0;
		width: 100%;
		left: 0;
		background-color: #fff;
		z-index: 1000;
		border-radius: 40rpx 40rpx 0 0;
		transform: translate3d(0, 100%, 0);
		transition: all .3s cubic-bezier(.25, .5, .5, .9);
		padding: 64rpx 40rpx;
		padding-bottom: 38rpx;
		padding-bottom: calc(38rpx + constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
		padding-bottom: calc(38rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
		box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.06);

		.icon-guanbi {
			position: absolute;
			top: 40rpx;
			right: 40rpx;
			font-size: 24rpx;
			font-weight: bold;
			color: #999;
		}

		.mp-data {
			display: flex;
			align-items: center;
			justify-content: center;
			margin-bottom: 40rpx;

			.mp-name {
				font-size: 34rpx;
				font-weight: 500;
				color: #333333;
				line-height: 48rpx;
			}
		}

		.trip-msg {
			padding-bottom: 32rpx;

			.title {
				font-size: 30rpx;
				font-weight: bold;
				color: #000;
				margin-bottom: 6rpx;
			}

			.trip {
				color: #333333;
				font-size: 28rpx;
				font-family: PingFang SC-Regular, PingFang SC;
				font-weight: 400;
			}
		}

		.trip-title {
			font-size: 28rpx;
			font-weight: 500;
			color: #333333;
			margin-bottom: 8rpx;
		}

		.main-color {
			font-size: 28rpx;
			font-weight: 400;
			color: var(--view-theme);
			margin-bottom: 40rpx;
		}

		.bottom {
			display: flex;
			align-items: center;
			justify-content: center;
			flex-direction: column;

			.save,
			.reject {
				display: flex;
				align-items: center;
				justify-content: center;
				width: 670rpx;
				height: 80rpx;
				border-radius: 80rpx;
				background-color: #F5F5F5;
				color: #333;
				font-size: 30rpx;
				font-weight: 500;
			}

			.save {
				background-color: var(--view-theme);
				color: #fff;
				margin-bottom: 24rpx;
			}
		}
	}
</style>
