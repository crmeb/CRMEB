<template>
	<view>
		<view class="navbar acea-row row-around">
			<view class="item acea-row row-center-wrapper" :class="{ on: navOn === 1 }" @click="onNav(1)">未使用</view>
			<view class="item acea-row row-center-wrapper" :class="{ on: navOn === 2 }" @click="onNav(2)">已使用/过期</view>
		</view>
		<view class='coupon-list' v-if="couponsList.length">
			<view class='item acea-row row-center-wrapper' v-for='(item,index) in couponsList' :key="index" >
				<view class='money' :class='item._type == 0 ? "moneyGray" : ""'>
					<view>￥<text class='num'>{{item.coupon_price}}</text></view>
					<view class="pic-num" v-if="item.use_min_price > 0">满{{item.use_min_price}}元可用</view>
					<view class="pic-num" v-else>无门槛券</view>
				</view>
				<view class='text'>
					<view class='condition'>
						<view class="line-title" :class="item._type === 0 ? 'bg-color-huic' : 'bg-color-check'" v-if="item.applicable_type === 0">通用劵</view>
						<view class="line-title" :class="item._type === 0 ? 'bg-color-huic' : 'bg-color-check'" v-else-if="item.applicable_type === 1">品类券</view>
						<view class="line-title" :class="item._type === 0 ? 'bg-color-huic' : 'bg-color-check'" v-else>商品券</view>
						<view class="name line1">{{item.coupon_title}}</view>
					</view>
					<view class='data acea-row row-between-wrapper'>
						<view>{{item._add_time}}-{{item._end_time}}</view>
						<view class='bnt gray' v-if="item._type==0">{{item._msg}}</view>
						<view class='bnt bg-color' v-else>{{item._msg}}</view>
					</view>
				</view>
			</view>
		</view>
		<view class='noCommodity' v-if="!couponsList.length && page === 2">
			<view class='pictrue'>
				<image src='../../../static/images/noCoupon.png'></image>
			</view>
		</view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<home></home>
	</view>
</template>

<script>
	import {
		getUserCoupons
	} from '@/api/api.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		mapGetters
	} from "vuex";
	// #ifdef MP
	import authorize from '@/components/Authorize';
	// #endif
	import home from '@/components/home';
	export default {
		components: {
			// #ifdef MP
			authorize,
			// #endif
			home
		},
		data() {
			return {
				couponsList: [],
				loading: false,
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false, //是否隐藏授权
				navOn: 1,
				page: 1,
				limit: 15,
				finished: false
			};
		},
		computed: mapGetters(['isLogin']),
		watch: {
			isLogin: {
				handler: function(newV, oldV) {
					if (newV) {
						this.getUseCoupons();
					}
				},
				deep: true
			}
		},
		onLoad() {
			if (this.isLogin) {
				this.getUseCoupons();
			} else {
				toLogin();
			}
		},
		onReachBottom() {
			this.getUseCoupons();
		},
		methods: {
			onNav: function(type) {
				this.navOn = type;
				this.couponsList = [];
				this.page = 1;
				this.finished = false;
				this.getUseCoupons();
			},
			/**
			 * 授权回调
			 */
			onLoadFun: function() {
				this.getUseCoupons();
			},
			// 授权关闭
			authColse: function(e) {
				this.isShowAuth = e
			},
			/**
			 * 获取领取优惠券列表
			 */
			getUseCoupons: function() {
				let that = this;
				if (that.loading || that.finished) {
					return;
				}
				that.loading = true;
				uni.showLoading({
					title: '正在加载…'
				});
				getUserCoupons(this.navOn, {
					page: this.page,
					limit: this.limit
				}).then(res => {
					that.loading = false;
					uni.hideLoading();
					that.couponsList = that.couponsList.concat(res.data);
					that.finished = res.data.length < that.limit;
					that.page += 1;
				}).catch(err => {
					that.loading = false;
					uni.showToast({
						title: err,
						icon: 'none'
					});
				});
			}
		}
	}
</script>

<style>
	.money {
		display: flex;
		flex-direction: column;
		justify-content: center;
	}

	.pic-num {
		color: #ffffff;
		font-size: 24rpx;
	}

	.coupon-list .item .text .condition {
		display: flex;
		align-items: center;
	}

	.coupon-list .item .text .condition .name {
		width: 260rpx;
	}

	.coupon-list .item .text .condition .pic {
		width: 30rpx;
		height: 30rpx;
		display: block;
		margin-right: 10rpx;
	}

	.condition .line-title {
		width: 96rpx;
		height: 40rpx !important;
		line-height: 38rpx !important;
		padding: 0 10rpx;
		-webkit-box-sizing: border-box;
		box-sizing: border-box;
		background: rgba(255, 247, 247, 1);
		border: 1px solid rgba(232, 51, 35, 1);
		opacity: 1;
		border-radius: 22rpx;
		font-size: 20rpx !important;
		color: #e83323;
		margin-right: 12rpx;
		text-align: center;
	}

	.condition .line-title.bg-color-huic {
		border-color: #BBB;
		color: #bbb;
		background-color: #F5F5F5;
	}
</style>

<style lang="scss" scoped>
	.navbar {
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 106rpx;
		background-color: #FFFFFF;
		z-index: 5;

		.item {
			border-top: 5rpx solid transparent;
			border-bottom: 5rpx solid transparent;
			font-size: 30rpx;
			color: #999999;

			&.on {
				border-bottom-color: #E93323;
				color: #282828;
			}
		}
	}

	.coupon-list {
		margin-top: 122rpx;
	}

	.noCommodity {
		margin-top: 300rpx;
	}
</style>
