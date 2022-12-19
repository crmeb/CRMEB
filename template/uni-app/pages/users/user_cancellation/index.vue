<template>
	<view class="agreement" :style="colorStyle">
		<view class="top-msg" v-if="agreementData.avatar">
			<view class="avatar">
				<img :src="agreementData.avatar" alt="">
			</view>
			<view class="name">{{agreementData.name}}</view>
		</view>
		<view class="content" v-html="agreementData.content">
		</view>
		<view class="footer">
			<view class="trip">
				{{$t(`点击【立即注销】即代表您已经同意《用户注销协议》`)}}
			</view>
			<view class="cancellation flex-aj-center" @click="isCancellation = true">
				{{$t(`立即注销`)}}
			</view>
		</view>
		<view class="mark" v-show="isCancellation"></view>
		<view class="tipaddress" v-show="isCancellation">
			<view class="top"></view>
			<view class="bottom">
				<view class="font1">{{$t(`是否确认注销`)}}</view>
				<view class="font2">{{$t(`注销后无法恢复，请谨慎操作`)}}</view>
				<view class="btn">
					<view class="cancellation-btn btn-sty flex-aj-center" @tap="cancelUser">{{$t(`注销`)}}</view>
					<view class="cancel btn-sty flex-aj-center" @tap="isCancellation = false">
						{{$t(`取消`)}}
					</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	import colors from '@/mixins/color.js';
	import {
		getUserAgreement,
		cancelUser
	} from '@/api/user.js'
	const app = getApp();
	export default {
		mixins: [colors],
		data() {
			return {
				isCancellation: false,
				agreementData: ''
			}
		},
		onLoad() {
			this.getAgreement()
		},
		methods: {
			getAgreement() {
				getUserAgreement(5).then(res => {
					this.agreementData = res.data
				})
			},
			cancelUser() {
				cancelUser().then(res => {
					app.globalData.spid = '';
					app.globalData.pid = '';
					this.$store.commit("LOGOUT");
					uni.reLaunch({
						url: '/pages/index/index'
					})
				}).catch(msg => {
					return this.$util.Tips({
						title: msg
					});
				});
			}
		}
	}
</script>

<style lang="scss" scoped>
	.agreement {
		background-color: #fff;

		.content {
			padding: 10rpx 30rpx;
			overflow-y: scroll;
			height: calc(100vh - 242rpx);
		}
	}

	.top-msg {
		display: flex;
		align-items: center;
		background-color: #fff;
		padding: 40rpx 30rpx 40rpx 30rpx;

		.avatar {
			width: 76rpx;
			height: 76rpx;
			margin-right: 20rpx;

			img {
				width: 100%;
				height: 100%;
				border-radius: 50%;
			}
		}
	}

	.footer {
		text-align: center;
		z-index: 99;
		width: 100%;
		background-color: #fafafa;
		position: fixed;
		padding: 36rpx 30rpx;
		box-sizing: border-box;
		border-top: 1rpx solid #eee;
		bottom: 0rpx;

		.trip {
			color: #999999;
			font-size: 24rpx;
			margin: 24rpx 0;
		}

		.cancellation {
			height: 45px;
			color: #fff;
			font-size: 32rpx;
			background: #E93323;
			border-radius: 23px;
		}
	}

	.tipaddress {
		position: fixed;
		left: 13%;
		top: 25%;
		// margin-left: -283rpx;
		width: 560rpx;
		height: 614rpx;
		background-color: #fff;
		border-radius: 10rpx;
		z-index: 100;
		text-align: center;

		.top {
			width: 560rpx;
			height: 270rpx;
			border-top-left-radius: 10rpx;
			border-top-right-radius: 10rpx;
			background-image: url(../static/address.png);
			background-repeat: round;
			background-color: #E93323;

			.tipsphoto {
				display: inline-block;
				width: 200rpx;
				height: 200rpx;
				margin-top: 73rpx;
			}
		}

		.bottom {
			font-size: 32rpx;
			font-weight: 400;

			.font1 {

				font-size: 36rpx;
				font-weight: 600;
				color: #333333;
				margin: 32rpx 0rpx 22rpx;
			}

			.font2 {
				color: #666666;
				margin-bottom: 48rpx;
			}

			.btn {
				display: flex;
				margin: 0 20rpx;

				.btn-sty {
					height: 82rpx;
					border-radius: 42rpx;
					line-height: 82rpx;
					padding: 24rpx 78rpx;
					margin: 0 auto;
				}

				.cancellation-btn {
					background-color: #F5F5F5;
					color: #333333;
				}

				.cancel {
					color: #FFFFFF;
					background: linear-gradient(90deg, #F67A38 0%, #F11B09 100%);
				}

			}
		}

	}

	.mark {
		position: fixed;
		top: 0;
		left: 0;
		bottom: 0;
		right: 0;
		background: rgba(0, 0, 0, 0.5);
		z-index: 99;
	}
</style>
