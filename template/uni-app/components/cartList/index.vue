<template>
	<view>
		<view class="cartList" :class="cartData.iScart?'on':''">
			<view class="title acea-row row-between-wrapper">
				<view class="name">{{$t(`已选商品`)}}</view>
				<view class="del acea-row row-middle" @click="subDel">
					<view class="iconfont icon-shanchu1"></view>{{$t(`清空`)}}
				</view>
			</view>
			<view class="list">
				<view class="item acea-row row-between-wrapper" v-for="(item,index) in cartData.cartList" :key="index">
					<view class="pictrue">
						<image v-if="item.productInfo.attrInfo" :src='item.productInfo.attrInfo.image'></image>
						<image v-else :src='item.productInfo.image'></image>
						<view class="mantle" v-if="!item.status || !item.attrStatus"></view>
					</view>
					<view class="txtPic">
						<view class="name line2" :class="(item.attrStatus && item.status)?'':'on'">
							{{item.productInfo.store_name}}
						</view>
						<view v-if="item.attrStatus && item.status">
							<view class="info" v-if="item.productInfo.attrInfo">{{item.productInfo.attrInfo.suk}}</view>
							<view class="bottom acea-row row-between-wrapper">
								<view class="money">{{$t(`￥`)}}<text class="num">{{item.truePrice}}</text></view>
								<view class="cartNum acea-row row-middle">
									<view class="reduce iconfont icon-jianhao1" @click="leaveCart(index)"></view>
									<view class="num">{{item.cart_num}}</view>
									<view class="plus iconfont icon-jiahao1" @click="joinCart(index)"></view>
								</view>
							</view>
						</view>
						<view class="noBnt" v-else-if="!item.attrStatus">{{$t(`已售罄`)}}</view>
						<view class="noBnt" v-else-if="!item.status">{{$t(`已下架`)}}</view>
						<view class="delTxt acea-row row-right" v-if="!item.status || !item.attrStatus"><text
								@click="oneDel(item.id,index)">{{$t(`删除`)}}</text></view>
					</view>
				</view>
			</view>
		</view>
		<view class="mask" v-if="cartData.iScart" @click="closeList"></view>
	</view>
</template>

<script>
	export default {
		props: {
			cartData: {
				type: Object,
				default: () => {}
			}
		},
		data() {
			return {};
		},
		mounted() {},
		methods: {
			closeList() {
				this.$emit('closeList', false);
			},
			leaveCart(index) {
				this.$emit('ChangeCartNumDan', false, index);
			},
			joinCart(index) {
				this.$emit('ChangeCartNumDan', true, index);
			},
			subDel() {
				this.$emit('ChangeSubDel');
			},
			oneDel(id, index) {
				this.$emit('ChangeOneDel', id, index);
			}
		}
	}
</script>

<style lang="scss">
	.mask {
		z-index: 99;
	}

	.cartList {
		position: fixed;
		left: 0;
		// bottom: 0;
		width: 100%;
		background-color: #fff;
		z-index: 100;
		padding: 0 30rpx 100rpx 30rpx;
		box-sizing: border-box;
		border-radius: 16rpx 16rpx 0 0;
		transform: translate3d(0, 100%, 0);
		transition: all .3s cubic-bezier(.25, .5, .5, .9);
		bottom: 0rpx;
		bottom: calc(0rpx + constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
		bottom: calc(0rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/

		&.on {
			transform: translate3d(0, 0, 0);
		}

		.title {
			height: 108rpx;

			.name {
				font-size: 28rpx;
				color: #282828;
				font-weight: bold;
			}

			.del {
				font-size: 26rpx;
				color: var(--view-theme);

				.iconfont {
					margin-right: 5rpx;
					font-size: 34rpx;
				}
			}
		}

		.list {
			max-height: 720rpx;
			overflow-x: hidden;
			overflow-y: auto;

			.item {
				margin-bottom: 40rpx;

				.pictrue {
					width: 176rpx;
					height: 176rpx;
					border-radius: 16rpx;
					position: relative;

					image {
						width: 100%;
						height: 100%;
						border-radius: 16rpx;
					}

					.mantle {
						position: absolute;
						top: 0;
						left: 0;
						width: 100%;
						height: 100%;
						background: rgba(255, 255, 255, 0.65);
						border-radius: 16rpx;
					}
				}

				.txtPic {
					width: 486rpx;

					.name {
						font-size: 28rpx;
						color: #282828;

						&.on {
							color: #A3A3A3;
						}
					}

					.noBnt {
						width: 126rpx;
						height: 44rpx;
						background: rgba(242, 242, 242, 1);
						border-radius: 22rpx;
						text-align: center;
						line-height: 44rpx;
						font-size: 24rpx;
						color: #A3A3A3;
						margin-top: 10rpx;
					}

					.delTxt {
						margin-top: 48rpx;
						font-size: 24rpx;
						color: #E93323;

						text {
							width: 70rpx;
							height: 50rpx;
							text-align: center;
							line-height: 50rpx;
						}
					}

					.info {
						font-size: 23rpx;
						color: #989898;
						margin-top: 5rpx;
					}

					.bottom {
						margin-top: 11rpx;

						.money {
							font-weight: bold;
							font-size: 26rpx;
							color: var(--view-priceColor);

							.num {
								font-size: 34rpx;
							}
						}

						.cartNum {
							font-weight: bold;

							.num {
								font-size: 34rpx;
								color: #282828;
								width: 120rpx;
								text-align: center;
							}

							.reduce {
								color: #282828;
								font-size: 24rpx;
								width: 60rpx;
								height: 60rpx;
								text-align: center;
								line-height: 60rpx;
							}

							.plus {
								color: #282828;
								font-size: 24rpx;
								width: 60rpx;
								height: 60rpx;
								text-align: center;
								line-height: 60rpx;
							}
						}
					}
				}
			}
		}
	}
</style>