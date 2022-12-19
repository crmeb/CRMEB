<template>
	<view v-show="!isSortType">
		<view class="explosion" :style="'margin-top:' + mbConfig*2 +'rpx;background-color:' + boxColor+';'"
			v-if="explosiveMoney.length">
			<view class="hd skeleton-rect">
				<!-- <image src="/static/images/explosion-title.png" mode=""></image> -->
				<view class="title" :style="'color:'+themeColor+';'">{{$t(titleConfig)}}</view>
				<view class="txt"
					:style="'background: linear-gradient(90deg, '+ bgColor[0].item +' 0%, '+ bgColor[1].item +' 100%);'">
					{{$t(desConfig)}}
				</view>
			</view>
			<view class="bd">
				<view class="item skeleton-rect" @click="goDetail(item)" v-for="(item,index) in explosiveMoney" :key="index">
					<view class="con-box">
						<view class="title line1">{{$t(item.info[0].value)}}</view>
						<view class="con line2">{{$t(item.info[1].value)}}</view>
						<view class="go">GO！<image src="/static/images/right-icon.png" mode=""></image>
						</view>
					</view>
					<image :src="item.img" mode="aspectFill"></image>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		name: 'activeParty',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			},
			isSortType: {
				type: String | Number,
				default: 0
			}
		},
		data() {
			return {
				titleConfig: this.dataConfig.titleConfig.value,
				desConfig: this.dataConfig.desConfig.value,
				explosiveMoney: this.dataConfig.menuConfig.list,
				themeColor: this.dataConfig.themeColor.color[0].item,
				bgColor: this.dataConfig.bgColor.color,
				mbConfig: this.dataConfig.mbConfig.val,
				boxColor: this.dataConfig.boxColor.color[0].item
			};
		},
		created() {},
		methods: {
			goDetail(item) {
				let urls = item.info[2].value
				if (['/pages/goods_cate/goods_cate', '/pages/order_addcart/order_addcart', '/pages/user/index']
					.indexOf(urls) == -1) {
					uni.navigateTo({
						url: urls
					})
				} else {
					uni.switchTab({
						url: urls
					})
				}
			}
		}
	}
</script>

<style lang="scss">
	.explosion {
		width: 710rpx;
		margin-top: 20rpx;
		margin: 20rpx auto 0 auto;
		padding: 30rpx 20rpx;
		padding: 30rpx 20rpx 6rpx 20rpx;
		background-color: #FFE5E3;
		background-size: 100% 100%;
		border-radius: 13px;
		box-sizing: border-box;

		.hd {
			display: flex;
			align-items: center;

			.title {
				font-size: 32rpx;
				font-weight: bold;
				margin-right: 12rpx;
			}

			image {
				width: 147rpx;
				height: 35rpx;
				margin-right: 20rpx;
			}

			.txt {
				padding: 0 10rpx;
				height: 36rpx;
				// background: linear-gradient(90deg, rgba(255, 168, 0, 1) 0%, rgba(255, 34, 15, 1) 100%);
				border-radius: 26rpx 0px 26rpx 0px;
				color: #fff;
				text-align: center;
				font-size: 22rpx;
				box-shadow: 3px 1px 1px 1px var(--view-minorColorT);
			}
		}

		.bd {
			display: flex;
			flex-wrap: wrap;
			margin-top: 28rpx;

			.item {
				display: flex;
				align-items: center;
				justify-content: space-between;
				position: relative;
				width: 325rpx;
				height: 180rpx;
				margin-bottom: 20rpx;
				margin-right: 20rpx;
				background-color: #fff;
				border-radius: 16rpx;
				padding: 0 20rpx;
				box-sizing: border-box;

				image {
					width: 140rpx;
					height: 140rpx;
				}

				.con-box {
					display: flex;
					flex-direction: column;
					justify-content: center;
					width: 130rpx;
					height: 100%;

					.title {
						color: #282828;
						font-size: 28rpx;
					}

					.con {
						color: #999999;
						font-size: 20rpx;
						margin-top: 2rpx;
					}

					.go {
						display: flex;
						align-items: center;
						justify-content: center;
						margin-top: 10rpx;
						width: 112rpx;
						height: 36rpx;
						border-radius: 18rpx;
						color: #fff;
						font-size: 26rpx;
						font-weight: bold;
						font-style: italic;

						image {
							width: 26rpx;
							height: 26rpx;
						}
					}
				}

				&:first-child .go {
					background: linear-gradient(90deg, rgba(75, 196, 255, 1) 0%, rgba(32, 126, 255, 1) 100%);
				}

				&:nth-child(2) .go {
					background: linear-gradient(90deg, rgba(255, 144, 67, 1) 0%, rgba(255, 83, 29, 1) 100%);
				}

				&:nth-child(3) .go {
					background: linear-gradient(90deg, rgba(150, 225, 135, 1) 0%, rgba(72, 206, 44, 1) 100%);
				}

				&:nth-child(4) .go {
					background: linear-gradient(90deg, rgba(255, 197, 96, 1) 0%, rgba(255, 156, 0, 1) 100%);
				}

				&:nth-child(2n) {
					margin-right: 0;
				}
			}
		}
	}
</style>