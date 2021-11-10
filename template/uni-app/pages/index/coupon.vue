<template>
	<view :style="colorStyle">
		<view class="default" v-if="isIframe && !couponList.length">
			<text>优惠券，暂无数据</text>
		</view>
		<view class="index-wrapper coupon" v-if="couponList.length&&isShow&&!isIframe">
			<view class='title acea-row row-between-wrapper'>
				<view class='text'>
					<view class='name line1'>优惠券</view>
					<view class='line1 txt-btn'>分享今日好物</view>
				</view>
				<view class='more' @click="gopage('/pages/users/user_get_coupon/index')">更多<text
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
			<view class="conter">
				<scroll-view scroll-x="true" style="white-space: nowrap; vertical-align: middle;"
					show-scrollbar="false">

					<view class="itemCon" :class="item.is_use?'on':''" v-for="(item, index) in couponList" :key="index">
						<view class="item acea-row row-between-wrapper">
							<view class="beijing"></view>
							<view class="beijing2"></view>
							<view class="cir"></view>
							<view class="cir2"></view>
							<view class="text">
								<view class="money line1">¥<text class="num">{{item.coupon_price}}</text></view>
								<view class="line1">满{{item.use_min_price}}可用</view>
							</view>
							<view class="bnt" v-if="item.is_use===true"><text>已领取</text></view>
							<view class="bnt" v-else-if="item.is_use===false" @click="receiveCoupon(item)">
								<text>领取</text>
							</view>
							<view class="bnt" v-else-if="item.is_use===2"><text>已过期</text></view>
						</view>
					</view>
				</scroll-view>
			</view>
		</view>
		<view class="coupon" v-if="couponList.length && isIframe">
			<view class="title acea-row row-between-wrapper">
				<view class="acea-row row-middle">
					<view class="sign">
						<image src="../../../static/images/sign01.png"></image>
					</view>
					<view class="name">领专享<text>优惠券</text></view>
				</view>
				<navigator url="/pages/users/user_get_coupon/index" hover-class="none"
					class="more acea-row row-center-wrapper">更多<text class="iconfont icon-xiangyou"></text></navigator>
			</view>
			<view class="conter">
				<scroll-view scroll-x="true" style="white-space: nowrap; vertical-align: middle;"
					show-scrollbar="false">
					<view class="itemCon" :class="item.is_use?'on':''" v-for="(item, index) in couponList" :key="index">
						<view class="item acea-row row-between-wrapper">
							<view class="text">
								<view class="money line1">¥<text class="num">{{item.coupon_price}}</text></view>
								<view class="line1">满{{item.use_min_price}}可用</view>
							</view>
							<view class="bnt" v-if="item.is_use===true"><text>已领取</text></view>
							<view class="bnt" v-else-if="item.is_use===false"><text>领取</text></view>
							<view class="bnt" v-else-if="item.is_use===2"><text>已过期</text></view>
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
								title: "领取成功"
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
				goPage().then(res => {
					uni.navigateTo({
						url: url
					})
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
			margin-bottom: 3px;
			margin-left: 6px;
		}
	}

	.coupon {
		margin: 0 20rpx;
		padding: 0rpx 0 32rpx 0;
		background-color: #fff;
		border-radius: $uni-border-radius-index;



		.conter {
			margin-top: 25rpx;
			padding-left: 20rpx;

			.itemCon {

				background-size: 100% 100%;
				width: 226rpx;
				height: 108rpx;
				display: inline-block;
				margin-right: 14rpx;

				&.on {


					.item {
						.text {
							color: #BFBFBF;
						}
					}
				}



				.item {
					width: 100%;
					height: 100%;
					position: relative;
					background: linear-gradient(135deg, var(--view-main-start) 0%, var(--view-main-over) 100%);

					.cir {
						position: absolute;
						left: -4rpx;
						bottom: -4rpx;
						width: 18rpx;
						height: 18rpx;
						border-radius: 50%;
						background: #fff;
						z-index: 100;
					}
					.beijing {
						position: absolute;
						left: -4rpx;
						bottom: -4rpx;
						width: 18rpx;
						height: 18rpx;
						border-radius: 50%;
						background: #fff;
						z-index: 100;
					}


					.beijing2 {
						position: absolute;
						right: 0;
						width: 60rpx;
						height: 100%;
						border-top-left-radius: 50rpx;
						border-bottom-left-radius: 50rpx;
						background: var(--view-op-ten);
						// background-color: red;
					}

					.text {
						text-align: center;
						color: #FD502F;
						font-size: 22rpx;
						width: 168rpx;
						padding-bottom: 6rpx;
						z-index: 99;

						.money {
							font-size: 30rpx;
							font-weight: bold;

							.num {
								font-size: 46rpx;
							}
						}
					}

					.bnt {
						writing-mode: vertical-lr;
						font-size: 22rpx;
						color: #fff;
						margin: 0 auto;
						z-index: 99;
					}
				}
			}
		}
	}
</style>
