<template>
	<view :style="colorStyle">
		<view class="default" v-if="isIframe && !couponList.length">
			<text>{{$t(`优惠券，暂无数据`)}}</text>
		</view>
		<view class="index-wrapper coupon" v-if="couponList.length&&isShow&&!isIframe">
			<view class='title acea-row row-between-wrapper skeleton-rect'>
				<view class='text'>
					<view class='name line1'>{{$t(`优惠券`)}}</view>
					<view class='line1 txt-btn'>{{$t(`领取今日好券`)}}</view>
				</view>
				<view class='more' @click="gopage('/pages/users/user_get_coupon/index')">{{$t(`更多`)}}<text
						class='iconfont icon-jiantou'></text>
				</view>
			</view>
			<!-- <view class="title acea-row row-between-wrapper">
				<view class="acea-row row-middle">
					<view class="name">优惠券</view>
				</view>
				<navigator url="/pages/users/user_get_coupon/index" hover-class="none"
					class="more acea-row row-center-wrapper">更多<text class="iconfont icon-xiangyou"></text></navigator>
			</view> -->
			<view class="conter skeleton-rect">
				<scroll-view scroll-x="true" style="white-space: nowrap; vertical-align: middle;"
					show-scrollbar="false">

					<view class="itemCon" v-for="(item, index) in couponList" :key="index">
						<view class="item acea-row row-between-wrapper" :class="item.is_use?'on':'no'">
							<view class="iconfont icon-youhuiquantoumingbeijing"></view>
							<view class="cir"></view>
							<view class="cir2"></view>
							<view class="text">
								<view class="money line1">{{$t(`￥`)}}<text class="num">{{item.coupon_price}}</text></view>
								<view class="man line1">{{$t(`满`)}}{{item.use_min_price}}{{$t(`可用`)}}</view>
							</view>
							<view class="bnt" v-if="item.is_use===true"><text>{{$t(`已领取`)}}</text></view>
							<view class="bnt" v-else-if="item.is_use===false" @click="receiveCoupon(item)">
								<text>{{$t(`领取`)}}</text>
							</view>
							<view class="bnt" v-else-if="item.is_use===2"><text>{{$t(`已过期`)}}</text></view>
						</view>
					</view>
				</scroll-view>
			</view>
		</view>
		<view class="index-wrapper coupon" v-if="couponList.length && isIframe">
			<view class='title acea-row row-between-wrapper'>
				<view class='text'>
					<view class='name line1'>{{$t(`优惠券`)}}</view>
					<view class='line1 txt-btn'>{{$t(`领取今日好券`)}}</view>
				</view>
				<view class='more' @click="gopage('/pages/users/user_get_coupon/index')">{{$t(`更多`)}}<text
						class='iconfont icon-jiantou'></text>
				</view>
			</view>
			<view class="conter">
				<scroll-view scroll-x="true" style="white-space: nowrap; vertical-align: middle;"
					show-scrollbar="false">

					<view class="itemCon" v-for="(item, index) in couponList" :key="index">
						<view class="item acea-row row-between-wrapper" :class="item.is_use?'on':'no'">
							<view class="iconfont icon-youhuiquantoumingbeijing"></view>
							<view class="cir"></view>
							<view class="cir2"></view>
							<view class="text">
								<view class="money line1">{{$t(`￥`)}}<text class="num">{{item.coupon_price}}</text></view>
								<view class="man line1">{{$t(`满`)}}{{item.use_min_price}}{{$t(`可用`)}}</view>
							</view>
							<view class="bnt" v-if="item.is_use===true"><text>{{$t(`已领取`)}}</text></view>
							<view class="bnt" v-else-if="item.is_use===false" @click="receiveCoupon(item)">
								<text>{{$t(`领取`)}}</text>
							</view>
							<view class="bnt" v-else-if="item.is_use===2"><text>{{$t(`已过期`)}}</text></view>
						</view>
					</view>
				</scroll-view>
			</view>
		</view>
	</view>
</template>

<script>
	let app = getApp();
	import {
		getCouponsIndex,
		setCouponReceive
	} from '@/api/api.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		mapGetters
	} from 'vuex';
	import colors from "@/mixins/color";
	export default {
		name: 'coupon',
		mixins: [colors],
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			}
		},
		computed: {
			...mapGetters(['isLogin'])
		},
		watch: {
			dataConfig: {
				immediate: true,
				handler(nVal, oVal) {
					if (nVal) {
						this.numberConfig = nVal.numConfig.val;
						this.isShow = nVal.isShow.val;
						this.getCoupon();
					}
				}
			}
		},
		data() {
			return {
				couponList: [],
				numberConfig: 0,
				name: this.$options.name, //component组件固定写法获取当前name；
				isIframe: app.globalData.isIframe, //判断是前台还是后台；
				isShow: true //判断此模块是否显示；
			};
		},
		created() {},
		mounted() {},
		methods: {
			getCoupon: function() {
				let that = this;
				let lists = [];
				getCouponsIndex({
					type: -1,
					num: this.numberConfig
				}).then(res => {
					res.data.forEach(function(value, key, iterable) {
						if (!value.used) {
							lists.push(value);
						}
					});
					that.$set(that, 'couponList', lists);
				}).catch(err => {
					return that.$util.Tips({
						title: err
					});
				});
			},
			receiveCoupon: function(item) {
				let that = this;
				if (!that.isLogin) {
					toLogin();
				} else {
					setCouponReceive(item.id)
						.then(function() {
							item.is_use = true;
							that.$set(that, 'couponList', that.couponList);
							that.$util.Tips({
								title: that.$t(`领取成功`)
							});
						})
						.catch(function(err) {
							that.$util.Tips({
								title: err
							});
						});
				}
			},
			gopage(url) {
				uni.navigateTo({
					url: url
				})
			}
		}
	}
</script>

<style lang="scss">
	.default {
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

	.title .text {
		display: flex;
		align-items: flex-end;

		.txt-btn {
			margin-bottom: 6rpx;
			margin-left: 6px;
		}
	}

	.coupon {
		margin: $uni-index-margin-row $uni-index-margin-col $uni-index-margin-row  $uni-index-margin-col;
		padding: 0rpx 0 20rpx 0;
		background-color: #fff;
		border-radius: $uni-border-radius-index;



		.conter {
			margin-top: 25rpx;
			padding: 0 20rpx;

			.itemCon {
				background-size: 100% 100%;
				width: 226rpx;
				height: 108rpx;
				display: inline-block;
				margin-right: 24rpx;

				.on {
					// opacity: 0.6;

					.item {
						background-color: rgba(233, 51, 35, 0.1) !important;

						.text {
							// color: #CCCCCC;
						}
					}

					.icon-youhuiquantoumingbeijing {
						color: #f3f3f3 !important;
					}


				}

				.no {
					background: linear-gradient(135deg, var(--view-main-start) 0%, var(--view-main-over) 100%);

					.icon-youhuiquantoumingbeijing {
						color: rgba(255,255, 255, 0.8);
					}

					.man {
						background-color: var(--view-theme);
					}
					.bnt {
						color: var(--view-theme) !important;
					}
				}

				.item {
					width: 100%;
					height: 100%;
					position: relative;
					border-radius: 10rpx;
					background-color: #b9b9b9;

					.cir {
						position: absolute;
						left: -6rpx;
						top: -6rpx;
						width: 16rpx;
						height: 16rpx;
						border-radius: 50%;
						background: #fff;
						z-index: 100;
					}

					.cir2 {
						position: absolute;
						left: -6rpx;
						bottom: -6rpx;
						width: 16rpx;
						height: 16rpx;
						border-radius: 50%;
						background: #fff;
						z-index: 100;
					}

					.beijing2 {
						// position: absolute;
						// right: 0;
						// width: 60rpx;
						// height: 100%;
						// border-top-left-radius: 50rpx;
						// border-bottom-left-radius: 50rpx;
						// // background: var(--view-op-ten);
						// background-color: rgba(233, 51, 35, 0.1);
						// z-index: 99;
					}

					.icon-youhuiquantoumingbeijing {
						position: absolute;
						right: 0;
						width: 88rpx;
						height: 100%;
						font-size: 124rpx;
						line-height: 124rpx;
						height: 124rpx;
						// background: var(--view-op-ten);
						z-index: 99;
					}

					.text {
						padding-left: 20rpx;
						color: #fff;
						font-size: 22rpx;
						width: 168rpx;
						padding-bottom: 6rpx;
						z-index: 99;

						.money {
							font-size: 30rpx;
							font-weight: bold;

							.num {
								font-size: 44rpx;
							}
						}

						.man {
							width: max-content;
							border-radius: 20rpx;
							padding: 0 10rpx;
						}
					}

					.bnt {
						writing-mode: vertical-lr;
						font-size: 22rpx;
						margin: 0 12rpx;
						z-index: 99;
						color: #818181;
					}
				}
			}
		}
	}
</style>
