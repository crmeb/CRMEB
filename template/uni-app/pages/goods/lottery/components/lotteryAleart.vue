<template>
	<view class="aleart" v-if="aleartStatus">
		<image src="../../../../static/images/poster-close.png" class="close" @click="posterImageClose"></image>
		<view class="title">
			{{aleartData.title}}
		</view>
		<view class="aleart-body">
			<image v-if="aleartData.img" class="goods-img" :src="aleartData.img" mode=""></image>
			<text class="msg">{{aleartData.msg}}</text>
		</view>
		<view class="btn" @click="posterImageClose()">
			{{aleartData.btn}}
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				aleartData: {}
			}
		},
		props: {
			aleartType: {
				type: Number
			},
			alData: {
				type: Object
			},
			aleartStatus: {
				type: Boolean,
				default: false
			}
		},
		watch: {
			aleartType(type) {
				if (type === 2) {
					this.aleartData = {
						title: '抽奖结果',
						img: this.alData.image,
						msg: this.alData.prompt,
						btn: '好的',
						type: this.alData.type
					}
				}
			},
			aleartStatus(status) {
				if (!status) {
					this.aleartData = {}
				}
			}
		},
		methods: {
			//隐藏弹窗
			posterImageClose() {
				this.$emit("close", false)
			},
		}

	}
</script>

<style lang="scss" scoped>
	.aleart {
		width: 500rpx;
		// height: 714rpx;
		position: fixed;
		left: 50%;
		transform: translateX(-50%);
		z-index: 9999;
		top: 50%;
		margin-top: -357rpx;
		background-color: #fff;
		padding: 30rpx;
		border-radius: 12rpx;

		.title {
			font-size: 18px;
			color: #E82C27;
			font-weight: bold;
			text-align: center;
			padding-bottom: 10rpx;
			border-bottom: 1px solid rgba(#E82C27, 0.2);
		}

		.aleart-body {
			display: flex;
			align-items: center;
			justify-content: center;
			flex-direction: column;
			padding: 60rpx 0;

			.goods-img {
				width: 150rpx;
				height: 150rpx;
			}

			.msg {
				font-size: 30rpx;
				color: #282828;
			}
		}

		.btn {
			width: 100%;
			padding: 15rpx 0;
			color: #fff;
			background: linear-gradient(90deg, #F34A46 0%, #FA9532 100%);
			border-radius: 20px;
			text-align: center;
		}

		.close {
			width: 46rpx;
			height: 75rpx;
			position: fixed;
			right: 0;
			top: -73rpx;
			display: block;
		}
	}
</style>
