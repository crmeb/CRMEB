<template>
	<view :style="colorStyle">
		<view class="points-swiper">
			<image class="bag" src="../../static/images/jf-head.png" mode=""></image>
			<view class="swiper">
				<swiper indicator-dots="true" :autoplay="autoplay" :circular="circular" :interval="interval"
					:duration="duration" indicator-color="rgba(255,255,255,0.6)" indicator-active-color="#fff">
					<block v-for="(item, index) in imgUrls" :key="index">
						<swiper-item>
							<image :src="item.img" class="slide-image" @click="goPages(item)"></image>
						</swiper-item>
					</block>
				</swiper>
			</view>
		</view>
		<view class="model">
			<view class="model-list" v-for="(model,index) in modelList" :key="index" @click="jump(model.url)">
				<image class="img" :src="model.imgUrl" mode=""></image>
				<text>{{model.title}}</text>
			</view>
		</view>
		<view class="body">
			<view class="body-title">
				<text class="title">{{$t(`大家都在换`)}}</text>
				<text class="jump-trip" @click="jumpMore">{{$t(`查看更多`)}}
					<text class="iconfont icon-xiangyou"></text></text>
			</view>
			<view class="product-list" v-if="goodList.length">
				<view class="product-item" v-for="(item, index) in goodList" @click="goGoodsDetail(item)">
					<image :src="item.image"></image>
					<view class="info">
						<view class="title line1">{{ item.title }}</view>
						<view class="price-box">
							{{ item.price }} {{$t(`积分`)}}
						</view>
						<view class="sales">{{item.sales}}{{$t(`人兑换`)}}</view>
					</view>
				</view>
			</view>
			<view v-else class="no-goods">
				<image :src="imgHost + '/statics/images/no-thing.png'" mode=""></image>
				<view class="fontimg">{{$t(`暂无商品，去看点别的吧`)}}</view>
			</view>
			<view class="footer">
				<view class="body-title">
					<text class="title">{{$t(`轻松赚积分`)}}</text>
					<text></text>
				</view>
				<view class="footer-list">
					<view class="list-left">
						<image class="icon-sty" src="./static/go-shoping.png" mode=""></image>
						<view class="list-left-right">
							<view class="title">
								{{$t(`购买商品`)}}
							</view>
							<view class="trip">
								{{$t(`购买商品可获得积分奖励`)}}
							</view>
						</view>
					</view>
					<navigator url="/pages/index/index" open-type="switchTab" class="right-box">
						<text class="go-jump">
							{{$t(`去完成`)}}
						</text>
					</navigator>
				</view>
				<view class="footer-list">
					<view class="list-left">
						<image class="icon-sty" src="./static/everyday.png" mode=""></image>
						<view class="list-left-right">
							<view class="title">
								{{$t(`每日签到活动`)}}
							</view>
							<view class="trip">
								{{$t(`每日签到可获得积分奖励`)}}
							</view>
						</view>
					</view>
					<navigator url="/pages/users/user_sgin/index" class="right-box">
						<text class="go-jump">
							{{$t(`去完成`)}}
						</text>
					</navigator>
				</view>
				<view class="footer-list">
					<view class="list-left">
						<image class="icon-sty" src="./static/luck-draw.png" mode=""></image>
						<view class="list-left-right">
							<view class="title">
								{{$t(`九宫格抽奖活动`)}}
							</view>
							<view class="trip">
								{{$t(`幸运抽奖可获得积分奖励`)}}
							</view>
						</view>
					</view>
					<navigator url="/pages/goods/lottery/grids/index?type=1" class="right-box">
						<text class="go-jump">
							{{$t(`去完成`)}}
						</text>
					</navigator>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	// import swipers from './components/swiper.vue';
	import {
		mapGetters
	} from 'vuex';
	import {
		getStoreIntegral
	} from '@/api/points_mall.js'
	import {
		goShopDetail
	} from '@/libs/order.js';
	import colors from "@/mixins/color";
	import {
		HTTP_REQUEST_URL
	} from '@/config/app';
	export default {
		components: {
			// swipers
		},
		mixins: [colors],
		data() {
			return {
				imgHost: HTTP_REQUEST_URL,
				autoplay: true,
				circular: true,
				interval: 3000,
				duration: 500,
				imgUrls: [],
				goodList: [],
				modelList: [{
						title: this.$t(`我的积分`),
						imgUrl: './static/my-point.png',
						url: "/pages/users/user_integral/index"
					},
					{
						title: this.$t(`每日签到`),
						imgUrl: './static/sign-in.png',
						url: '/pages/users/user_sgin/index'
					}, {
						title: this.$t(`积分抽奖`),
						imgUrl: './static/points-lottery.png',
						url: '/pages/goods/lottery/grids/index?type=1'
					}, {
						title: this.$t(`兑换记录`),
						imgUrl: './static/exchange.png',
						url: "/pages/points_mall/exchange_record"
					},
				]
			}
		},
		computed: mapGetters(['isLogin']),
		onLoad() {
			this.getStoreIntegral()
		},
		watch: {
			isLogin: {
				handler: function(newV, oldV) {
					if (newV == true) {
						this.getStoreIntegral();
					}
				},
				deep: true
			},
		},
		methods: {
			getStoreIntegral() {
				getStoreIntegral().then(res => {
					this.imgUrls = res.data.banner
					this.goodList = res.data.list
				})
			},
			// 去商品详情
			goGoodsDetail(item) {
				goShopDetail(item).then(res => {
					uni.navigateTo({
						url: `/pages/points_mall/integral_goods_details?id=${item.id}`
					});
				});
			},
			jumpMore() {
				uni.navigateTo({
					url: '/pages/points_mall/integral_goods_list'
				})
			},
			goPages(item) {
				let url = item.link;
				this.$util.JumpPath(url);
			},
			jump(url) {
				uni.navigateTo({
					url
				})
			}
		}
	}
</script>

<style lang="scss" scoped>
	.swiper,
	swiper,
	swiper-item,
	.slide-image {
		width: 100%;
		height: 280rpx;
	}

	/deep/ .uni-swiper-wrapper {
		border-radius: 10rpx;
	}

	.swiper {
		padding: 20rpx;
	}

	.points-swiper {
		position: relative;
		width: 100%;
		background-color: #fff;

		.bag {
			position: absolute;
			width: 100%;
			height: 285rpx;
		}

		.points-swiper-sty {
			padding: 20rpx 26rpx;
		}
	}

	.model {
		display: flex;
		justify-content: space-between;
		padding: 56rpx 42rpx;
		background-color: #fff;
	}

	.model-list {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		font-size: 26rpx;
		color: #333;
		margin-top: 27rpx;
		font-weight: 500;

		.img {
			width: 84rpx;
			height: 84rpx;
			margin-bottom: 19rpx;
		}

	}

	.body {
		background-color: #fff;
		padding: 0 30rpx;
		margin-top: 20rpx;

		.body-title {
			display: flex;
			justify-content: space-between;
			padding: 30rpx 0 10rpx 0;

			.title {
				font-size: 32rpx;
				font-family: PingFang SC;
				font-weight: bold;
			}

			.jump-trip,
			.icon-xiangyou {
				font-size: 24rpx;
				color: #999999;
			}
		}

		.product-list {
			display: flex;
			flex-wrap: wrap;
			justify-content: space-between;
			margin-top: 30rpx;

			.product-item {
				position: relative;
				width: 334rpx;
				background: #fff;
				border-radius: 10rpx;
				margin-bottom: 20rpx;
				box-shadow: 0px 0px 7px rgba(0, 0, 0, 0.06);

				image {
					width: 100%;
					height: 344rpx;
					border-radius: 10rpx 10rpx 0 0;
				}

				.info {
					padding: 14rpx 16rpx;

					.title {
						font-size: 28rpx;
					}

					.price-box {
						font-size: 26rpx;
						font-weight: 700;
						margin-top: 8px;
						color: var(--view-theme);
					}

					.sales {
						font-size: 22rpx;
						color: #999999;
					}
				}
			}
		}
	}

	.footer {
		margin-top: 20rpx;

		.footer-list {
			display: flex;
			justify-content: space-between;
			padding: 26rpx 0;

			.list-left {
				display: flex;
				align-items: center;

				.icon-sty {
					width: 80rpx;
					height: 80rpx;
					margin-right: 18rpx;
				}

				.list-left-right {
					.title {
						font-size: 28rpx;
						font-weight: bold;
						color: #333333;
					}

					.trip {
						font-size: 22rpx;
						color: #999999;
					}
				}
			}

			.right-box {
				display: flex;
				align-items: center;
			}

			.go-jump {
				display: flex;
				align-items: center;
				background-color: #E93323;
				border-radius: 26rpx;
				color: #fff;
				font-size: 28rpx;
				height: 48rpx;
				line-height: 48rpx;
				padding: 6rpx 24rpx;
			}
		}
	}

	.no-goods {
		display: flex;
		flex-direction: column;
		height: 472rpx;

		image {
			width: 100%;
			display: block;
			margin: 0 auto;
		}

		.fontimg {
			text-align: center;
			color: #bebebe;
		}
	}
</style>
