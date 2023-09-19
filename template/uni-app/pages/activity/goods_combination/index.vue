<template>
	<view class="group-list" :style="colorStyle">
		<view class="swiper" v-if="bannerList.length">
			<swiper indicator-dots="true" :autoplay="true" :circular="circular" :interval="interval"
				:duration="duration" indicator-color="rgba(0,0,0,0.3)">
				<block v-for="(item,index) in bannerList" :key="index">
					<swiper-item>
						<view @click="goDetail(item)" class='slide-navigator acea-row row-between-wrapper'>
							<image :src="item.img" class="slide-image"></image>
						</view>
					</swiper-item>
				</block>
			</swiper>
		</view>
		<view class="groupMember acea-row row-center-wrapper">
			<view class="line">
				<image src="../static/groupLine.png"></image>
			</view>
			<view class="member acea-row row-center-wrapper">
				<view class="pictrue" v-for="(item,index) in pinkPeople" :key="index" v-if="index<6">
					<image :src="item"></image>
				</view>
				<text style="margin-left: 10rpx;">{{pinkCount}}{{$t(`人参与`)}}</text>
				<view class="pictrue" v-if="pinkPeople.length>5">
					<image :src="pinkPeople[pinkPeople.length-1]"></image>
					<view class="iconfont icon-gengduo1"></view>
				</view>
			</view>
			<view class="line right">
				<image src="../static/groupLine.png"></image>
			</view>
		</view>
		<view class="list" v-if="combinationList.length">
			<view class="item acea-row row-between-wrapper" v-for="(item,index) in combinationList" :key='index'
				@tap="openSubcribe(item)">
				<view class="pictrue">
					<image :src="item.image"></image>
				</view>
				<view class="text">
					<view class="name line2">{{item.title}}</view>
					<view class="bottom acea-row row-between row-bottom">
						<view class="y_money">
							<view class="price">{{$t(`￥`)}}{{item.product_price}}</view>
							<view class="money">{{$t(`￥`)}}<text class="num">{{item.price}}</text></view>
						</view>
						<view class="bnt acea-row row-center-wrapper" v-if="item.stock>0&&item.quota>0">
							<view class="light">
								<image src="../static/lightning.png"></image>
							</view>
							<view class="num">{{item.people}}{{$t(`人团`)}}</view>
							<view class="go">{{$t(`去拼团`)}}</view>
						</view>
						<view class="bnt gray acea-row row-center-wrapper" v-else>{{$t(`已售罄`)}}</view>
					</view>
				</view>
			</view>
		</view>
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</view>
</template>

<script>
	import {
		getCombinationList,
		getCombinationBannerList,
		getPink
	} from '@/api/activity.js';
	import {
		openPinkSubscribe
	} from '../../../utils/SubscribeMessage.js';
	import home from '@/components/home/index.vue'
	import colors from "@/mixins/color";
	let app = getApp();
	export default {
		components: {
			home
		},
		mixins: [colors],
		data() {
			return {
				pinkPeople: [],
				pinkCount: 0,
				bannerList: [],
				circular: true,
				autoplay: true,
				interval: 3000,
				duration: 500,
				combinationList: [],
				limit: 10,
				page: 1,
				loading: false,
				loadend: false
			}
		},
		onLoad() {
			uni.setNavigationBarTitle({
				title: this.$t(`拼团列表`)
			})
			this.getCombinationList();
			this.getBannerList();
			this.getPink();
		},
		methods: {
			getPink: function() {
				getPink().then(res => {
					this.pinkPeople = res.data.avatars;
					this.pinkCount = res.data.pink_count;
				})
			},
			getBannerList: function() {
				getCombinationBannerList().then(res => {
					this.bannerList = res.data;
				})
			},
			goDetail(item) {
				let url = item.link;
				this.$util.JumpPath(url);
			},
			openSubcribe: function(item) {
				let page = item;
				// #ifndef MP
				uni.navigateTo({
					url: `/pages/activity/goods_combination_details/index?id=${item.id}`
				});
				// #endif
				// #ifdef MP
				uni.showLoading({
					title: this.$t(`正在加载中`),
				})
				openPinkSubscribe().then(res => {
					uni.hideLoading();
					uni.navigateTo({
						url: `/pages/activity/goods_combination_details/index?id=${item.id}`
					});
				}).catch(() => {
					uni.hideLoading();
				});
				// #endif
			},
			getCombinationList: function() {
				var that = this;
				if (that.loadend) return;
				if (that.loading) return;
				var data = {
					page: that.page,
					limit: that.limit
				};
				this.loading = true
				getCombinationList(data).then(function(res) {
					var combinationList = that.combinationList;
					var limit = that.limit;
					that.page++;
					that.loadend = limit > res.data.length;
					that.combinationList = combinationList.concat(res.data);
					that.page = that.data.page;
					that.loading = false;
				}).catch(() => {
					that.loading = false
				})
			},
		},
		onReachBottom: function() {
			this.getCombinationList();
		},
	}
</script>

<style lang="scss">
	page {
		// background-color: var(--view-theme) !important;
	}

	.group-list {
		min-height: 100vh;
		background-color: var(--view-theme) !important;
		padding: 30rpx 0;

		.swiper {
			width: 100%;
			position: relative;
			box-sizing: border-box;
			padding: 0 30rpx;

			swiper {
				width: 100%;
				height: 300rpx;

				.slide-image {
					width: 100%;
					height: 300rpx;
					border-radius: 20rpx;
				}

				/deep/.uni-swiper-dot {
					width: 8rpx !important;
					height: 8rpx !important;
					border-radius: 50%;
				}

				/deep/.uni-swiper-dot-active {
					width: 18rpx !important;
					border-radius: 4rpx;
					background-color: var(--view-theme) !important;
				}
			}
		}

		.groupMember {
			height: 100rpx;

			.line {
				width: 102rpx;
				height: 4rpx;

				&.right {
					transform: rotate(180deg);
				}

				image {
					width: 100%;
					height: 100%;
					display: block;
				}
			}

			.member {
				margin: 0 30rpx;
				color: #fff;

				.pictrue {
					width: 46rpx;
					height: 46rpx;
					position: relative;

					image {
						border: 2rpx solid #fff;
						width: 100%;
						height: 100%;
						border-radius: 50%;
					}

					&~.pictrue {
						margin-left: -8rpx;
					}

					.iconfont {
						position: absolute;
						width: 43rpx;
						height: 43rpx;
						background: rgba(51, 51, 51, 0.6);
						border-radius: 50%;
						top: 2rpx;
						left: 2rpx;
						color: #fff;
						font-size: 10rpx;
						text-align: center;
						line-height: 43rpx;
					}
				}
			}
		}

		.list {
			.item {
				width: 690rpx;
				height: 230rpx;
				background-color: #fff;
				border-radius: 14rpx;
				padding: 0 22rpx;
				margin: 0 auto 18rpx auto;

				.pictrue {
					width: 186rpx;
					height: 186rpx;

					image {
						width: 100%;
						height: 100%;
						border-radius: 10rpx;
					}
				}

				.text {
					width: 440rpx;

					.name {
						color: #333;
						font-size: 30rpx;
						line-height: 38rpx;
						height: 70rpx;
					}

					.bottom {
						margin-top: 10rpx;

						.y_money {
							font-size: 24rpx;
							color: #999;

							.price {
								text-decoration: line-through;
							}

							.money {
								color: var(--view-priceColor);
								font-weight: 600;

								.num {
									font-size: 34rpx;
								}
							}
						}

						.bnt {
							height: 58rpx;
							font-size: 24rpx;
							text-align: center;
							position: relative;
							background-color: var(--view-theme);
							border-radius: 28rpx;

							.light {
								position: absolute;
								width: 28rpx;
								height: 58rpx;
								top: 0;
								left: 50%;
								margin-left: -8rpx;

								image {
									width: 100%;
									height: 100%;
								}
							}

							.num {
								width: 120rpx;
								background-color: rgba(255, 255, 255, 0.85);
								color: var(--view-theme);
								height: 100%;
								line-height: 58rpx;
								border-radius: 28rpx 0 14rpx 28rpx;
							}

							.go {
								width: 112rpx;
								background-color: var(--view-theme);
								height: 100%;
								line-height: 58rpx;
								border-radius: 0 28rpx 28rpx 0;
								color: #fff;
							}

							&.gray {
								width: 148rpx;
								background-color: #cccccc;
								color: #fff;
							}
						}
					}
				}
			}
		}
	}
</style>
