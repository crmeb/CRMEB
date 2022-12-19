<template>
	<view class="">

		<!-- #ifdef H5 -->
		<view class="live" v-if="isIframe">
			<view v-if="isIframe && liveList.length>0">
				<view class="title-box" v-if="titleInfo.length">
					<text class="title">{{$t(titleInfo[0].val)}}</text>
					<navigator class="more">{{$t(`查看更多`)}}<text class="iconfont icon-jiantou"></text></navigator>
				</view>
				<view class="live-wrapper-a">
					<navigator class="live-item-a" v-for="(item,index) in liveList" :key="index"
						:url="'plugin-private://wx2b03c6e691cd7370/pages/live-player-plugin?room_id=' + item.room_id+'&custom_params='+custom_params"
						hover-class="none">
						<view class="img-box">
							<view class="bgblue" v-if="item.live_status == 102">
								<view class="txt">{{$t(`预告`)}}</view>
								<view class="msg">{{item.show_time}}</view>
							</view>
							<view class="label bggary" v-if="item.live_status==103">
								<text class="iconfont icon-huifang"></text>
								<text>{{$t(`回放`)}}</text>
							</view>
							<view class="label bgred" v-if="item.live_status==101">
								<text class="iconfont icon-zhibozhong"></text>
								<text>{{$t(`进行中`)}}</text>
							</view>
							<image :src="item.share_img"></image>
						</view>
						<view class="info">
							<view class="title line1">{{$t(item.name)}}</view>
							<!-- <view class="people">
							<image :src="item.anchor_img" alt="">
								<text>{{item.anchor_name}}</text>
						</view> -->
							<!-- <view class="goods-wrapper">
							<block v-if="item.goods.length<=3">
								<view class="goods-item" v-for="(goods,index) in item.goods" :key="index">
									<image :src="goods.cover_img" alt="">
										<text class="line1">￥{{goods.price}}</text>
								</view>
							</block>
							<block v-if=" item.goods.length>3">
								<view class="goods-item" v-for="(goods,index) in item.goods" :key="index"
									v-if="index<2">
									<image :src="goods.cover_img" alt="">
										<text class="line1">￥{{goods.price}}</text>
								</view>
								<view class="goods-item">
									<image :src="item.goods[item.goods.length-1].cover_img" alt="">
										<view class="num">+{{item.goods.length}}</view>
								</view>
							</block>
							<block v-if="item.goods.length == 0">
								<view class="empty-goods">暂无商品</view>
							</block>
						</view> -->
						</view>
					</navigator>
				</view>
			</view>
			<view v-if="isIframe && !liveList.length">
				<view class="title-box" v-if="titleInfo.length">
					<text class="title">{{titleInfo[0].val}}</text>
					<navigator class="more">{{$t(`查看更多`)}}<text class="iconfont icon-jiantou"></text></navigator>
				</view>
				<view class="live-wrapper-a">
					<view class="empty-img">{{$t(`暂无数据`)}}</view>
				</view>
			</view>
		</view>
		<!-- #endif -->
		<!-- #ifdef MP -->
		<view class="live"  v-if="isShow && liveList.length>0">
			<view class="skeleton-rect">
				<view class="title-box" v-if="titleInfo.length">
					<view class='text'>
						<view class='title line1'>
							{{titleInfo[0].val}}
						</view>
						<view class='line1 txt-btn'>{{$t(`精彩内容`)}}</view>
					</view>
					<navigator hover-class="none" url="/pages/columnGoods/live_list/index" class="more">{{$t(`更多`)}}<text
							class="iconfont icon-jiantou"></text></navigator>
				</view>
				<view class="live-wrapper-a">
					<navigator class="live-item-a" v-for="(item,index) in liveList" :key="index"
						:url="'plugin-private://wx2b03c6e691cd7370/pages/live-player-plugin?room_id=' + item.room_id+'&custom_params='+custom_params"
						hover-class="none">
						<view class="img-box">
							<view class="bgblue" v-if="item.live_status == 102">
								<view class="txt">{{$t(`预告`)}}</view>
								<view class="msg">{{item.show_time}}</view>
							</view>
							<view class="label bggary" v-if="item.live_status==103">
								<text class="iconfont icon-huifang"></text>
								<text>{{$t(`回放`)}}</text>
							</view>
							<view class="label bgred" v-if="item.live_status==101">
								<text class="iconfont icon-zhibozhong"></text>
								<text>{{$t(`进行中`)}}</text>
							</view>
							<image :src="item.share_img"></image>
						</view>
						<view class="info">
							<view class="title line1">{{item.name}}</view>
							<!-- <view class="people">
							<image :src="item.anchor_img" alt="">
								<text>{{item.anchor_name}}</text>
						</view> -->
							<!-- <view class="goods-wrapper">
							<block v-if="item.goods.length<=3">
								<view class="goods-item" v-for="(goods,index) in item.goods" :key="index">
									<image :src="goods.cover_img" alt="">
										<text class="line1">￥{{goods.price}}</text>
								</view>
							</block>
							<block v-if=" item.goods.length>3">
								<view class="goods-item" v-for="(goods,index) in item.goods" :key="index"
									v-if="index<2">
									<image :src="goods.cover_img" alt="">
										<text class="line1">￥{{goods.price}}</text>
								</view>
								<view class="goods-item">
									<image :src="item.goods[item.goods.length-1].cover_img" alt="">
										<view class="num">+{{item.goods.length}}</view>
								</view>
							</block>
							<block v-if="item.goods.length == 0">
								<view class="empty-goods">暂无商品</view>
							</block>
						</view> -->
						</view>
					</navigator>
				</view>
			</view>
		</view>
		<!-- #endif -->
	</view>
	</view>
</template>

<script>
	let app = getApp().globalData
	import {
		getLiveList
	} from '@/api/api.js';
	export default {
		name: 'alive',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			}
		},
		watch: {
			dataConfig: {
				immediate: true,
				handler(nVal, oVal) {
					if (nVal) {
						this.numConfig = nVal.numConfig.val;
						this.titleInfo = nVal.titleInfo.list;
						this.isShow = nVal.isShow.val;
						this.getLiveList();
					}
				}
			}
		},
		data() {
			return {
				isIframe: false,
				liveList: [],
				numConfig: 0,
				limit: this.$config.LIMIT,
				name: this.$options.name,
				titleInfo: [],
				isShow: true,
				custom_params:''
			}
		},
		created() {
			this.isIframe = app.isIframe
		},
		mounted() {
			this.custom_params= encodeURIComponent(JSON.stringify({spid:this.$store.state.app.uid}))
		},
		methods: {
			getLiveList: function() {
				getLiveList(1, this.numConfig <= this.limit ? this.numConfig : this.limit)
					.then(res => {
						this.liveList = res.data;
					})
					.catch(res => {});
			}
		}
	}
</script>

<style lang="scss" scoped>
	.live {
		background-color: $uni-bg-color;
		margin: $uni-index-margin-row $uni-index-margin-col 0 $uni-index-margin-col;
		border-radius: $uni-border-radius-index;
		padding-bottom: 15rpx;
	}

	.live-wrapper {
		position: relative;
		width: 100%;
		overflow: hidden;
		border-radius: 16rpx;
		background-color: $uni-bg-color;

		image {
			width: 100%;
			height: 400rpx;
		}
		

		.live-top {
			z-index: 20;
			position: absolute;
			left: 0;
			top: 0;
			display: flex;
			align-items: center;
			justify-content: center;
			color: #fff;
			width: 180rpx;
			height: 54rpx;
			border-radius: 0rpx 0px 18rpx 0px;

			image {
				width: 30rpx;
				height: 30rpx;
				margin-right: 10rpx;
				/* #ifdef H5 */
				display: block;
				/* #endif */
			}
		}

		.live-title {
			position: absolute;
			left: 0;
			bottom: 6rpx;
			width: 100%;
			height: 70rpx;
			line-height: 70rpx;
			text-align: center;
			font-size: 30rpx;
			color: #fff;
			background: rgba(0, 0, 0, 0.35);
		}

		&.mores {
			width: 100%;

			.item {
				position: relative;
				width: 320rpx;
				display: inline-block;
				border-radius: 16rpx;
				overflow: hidden;
				margin-right: 20rpx;

				image {
					width: 320rpx;
					height: 180rpx;
					border-radius: 16rpx;
				}

				.live-title {
					height: 40rpx;
					line-height: 40rpx;
					text-align: center;
					font-size: 22rpx;
				}

				.live-top {
					width: 120rpx;
					height: 36rpx;
					font-size: 22rpx;

					image {
						width: 20rpx;
						height: 20rpx;
					}
				}
			}
		}
	}

	.live-wrapper-a {
		display: flex;
		width: 690rpx;
		padding: 0rpx 30rpx 0;
		overflow-x: scroll;

		.live-item-a {
			width: 280rpx;
			background: #fff;
			margin-right: 20rpx;
			border-radius: 16rpx;

			&:last-child {
				margin-right: 20rpx;
			}

			.img-box {
				position: relative;
				width: 280rpx;
				height: 180rpx;

				image {
					width: 100%;
					height: 100%;
					border-radius: 12rpx;
					object-fit: cover;
				}
			}

			.info {
				flex: 1;
				display: flex;
				flex-direction: column;
				justify-content: space-between;
				padding: 15rpx 0rpx;

				.title {
					font-size: 28rpx;
					color: #333;
				}

				.people {
					display: flex;
					align-items: center;
					color: #999;
					font-size: 24rpx;
					margin-top: 10rpx;

					image {
						width: 32rpx;
						height: 32rpx;
						border-radius: 50%;
						margin-right: 10rpx;
					}
				}

				.goods-wrapper {
					display: flex;

					.goods-item {
						position: relative;
						width: 96rpx;
						height: 96rpx;
						margin-right: 20rpx;
						overflow: hidden;
						border-radius: 16rpx;

						&:last-child {
							margin-right: 0;
						}

						image {
							width: 100%;
							height: 100%;
							border-radius: 16rpx;
						}

						.bg {
							position: absolute;
							left: 0;
							top: 0;
							width: 100%;
							height: 100%;
							border-radius: 16rpx;
							background: rgba(0, 0, 0, 0.3);
						}

						text {
							position: absolute;
							left: 0;
							bottom: 0;
							width: 100%;
							height: 60rpx;
							line-height: 70rpx;
							color: #fff;
							background: linear-gradient(180deg, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.75) 100%);
						}

						.num {
							display: flex;
							align-items: center;
							justify-content: center;
							position: absolute;
							left: 0;
							top: 0;
							width: 100%;
							height: 100%;
							background: rgba(0, 0, 0, 0.3);
							color: #fff;
							font-size: 28rpx;
						}
					}
				}

				.empty-goods {
					width: 96rpx;
					height: 96rpx;
					border-radius: 6rpx;
					background-color: #B2B2B2;
					color: #fff;
					font-size: 20rpx;
					text-align: center;
					line-height: 96rpx;
				}
			}
		}

		&.live-wrapper-c {
			.live-item-a {
				display: flex;
				flex-direction: column;

				.img-box {
					width: 100%;
					border-radius: 8px 8px 0 0;
				}

				.info {
					display: flex;
					justify-content: space-between;
					align-items: center;
					flex-direction: initial;

					.left {
						width: 69%;
					}

					.goods-wrapper {
						flex: 1;
					}
				}
			}
		}
	}

	.text {
		display: flex;
		align-items: flex-end;

		.name {
			font-size: 32rpx;
			font-weight: bold;
		}

		.txt-btn {
			font-size: 24rpx;
			display: flex;
			align-items: flex-end;
			margin-left: 12rpx;
			color: #999;
			margin-bottom: 4rpx;
		}

	}

	.live-wrapper-b {
		padding: 0rpx 20rpx 0;
		display: flex;
		justify-content: space-between;
		flex-wrap: wrap;

		.live-item-b {
			width: 345rpx;
			background-color: #fff;
			border-radius: 16rpx;
			overflow: hidden;
			margin-bottom: 20rpx;
			overflow: hidden;

			.img-box {
				position: relative;

				image {
					width: 100%;
					height: 274rpx;
				}
			}

			.info {
				display: flex;
				flex-direction: column;
				padding: 20rpx;

				.title {
					font-size: 30rpx;
					color: #333;
				}

				.people {
					display: flex;
					margin-top: 10rpx;
					color: #999;

					image {
						width: 36rpx;
						height: 36rpx;
						border-radius: 50%;
						margin-right: 10rpx;
					}
				}
			}
		}
	}

	.label {
		display: flex;
		align-items: center;
		justify-content: center;
		position: absolute;
		left: 10rpx;
		top: 10rpx;
		border-radius: 22rpx 0px 22rpx 22rpx;
		font-size: 24rpx;
		color: #fff;
		z-index: 1;

		image {
			margin-right: 10rpx;
		}

		text {
			font-size: 22rpx;
		}
	}

	.bgred {
		width: 132rpx;
		height: 38rpx;
		background: linear-gradient(270deg, #F5742F 0%, #FF1717 100%)
	}

	.bggary {
		width: 108rpx;
		height: 38rpx;
		background: linear-gradient(270deg, #999999 0%, #666666 100%);
		line-height: 38rpx;
	}

	.bggary .iconfont {
		margin-right: 8rpx;
		font-size: 24rpx;
		color: #FFFFFF;
	}

	.bgblue {
		display: flex;
		align-items: center;
		position: absolute;
		left: 4rpx;
		top: 10rpx;
		border-radius: 22rpx 0px 22rpx 22rpx;
		font-size: 24rpx;
		color: #fff;
		z-index: 1;
		width: 220rpx;
		height: 38rpx;
		background: rgba(0, 0, 0, 0.36);
		overflow: hidden;

		.txt {
			position: relative;
			left: -5rpx;
			display: flex;
			align-items: center;
			justify-content: center;
			width: 80rpx;
			height: 100%;
			text-align: center;
			background: linear-gradient(270deg, #2FA1F5 0%, #0076FF 100%);
		}
	}

	.title-box {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 20rpx 20rpx;
		font-size: 32rpx;

		.title {
			font-size: $uni-index-title-font-size;
			font-weight: bold;
		}



		// .text {
		// 	font-size: 24rpx;
		// 	color: #999;
		// 	width: 530rpx;
		// }

		// .name {
		// 	color: #282828;
		// 	font-size: 30rpx;
		// 	font-weight: bold;
		// 	margin-bottom: 5rpx;
		// }

		.more {
			display: flex;
			align-items: center;
			justify-content: center;

			font-size: 24rpx;
			color: #999999;

			.iconfont {
				margin-left: 9rpx;
				font-size: 26rpx;
				vertical-align: 3rpx;
			}
		}
	}

	.empty-img {
		width: 690rpx;
		height: 300rpx;
		border-radius: 14rpx;
		margin: 26rpx auto 0 auto;
		background-color: #ccc;
		text-align: center;
		line-height: 300rpx;

		.iconfont {
			font-size: 50rpx;
		}
	}
</style>
