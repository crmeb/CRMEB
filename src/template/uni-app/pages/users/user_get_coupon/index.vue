<template>
	<view>
		<view v-if="count > 1" class="acea-row row-around nav">
			<template v-for="item in navList">
				<view v-if="item.count" :key="item.type" :class="['acea-row', 'row-middle', type === item.type ? 'on' : '']" @click="setType(item.type)">{{ item.name }}</view>
			</template>
		</view>
		<view v-if="count > 1" style="height: 106rpx;"></view>
		<view class='coupon-list' v-if="couponsList.length">
			<view class='item acea-row row-center-wrapper' v-for="(item,index) in couponsList" :key="index" >
				<view class='money' :class='item.is_use ? "moneyGray" : "" '>
					<view>￥<text class='num'>{{item.coupon_price}}</text></view>
					<view class="pic-num" v-if="item.use_min_price > 0">满{{item.use_min_price}}元可用</view>
					<view class="pic-num" v-else>无门槛券</view>
				</view>
				<view class="text">
					<view class="condition line1">
						<span class="line-title" :class="item.is_use == true || item.is_use == 2 ? 'gray' : ''" v-if="item.type === 0">通用劵</span>
						<span class="line-title" :class="item.is_use == true || item.is_use == 2 ? 'gray' : ''" v-else-if="item.type === 1">品类券</span>
						<span class="line-title" :class="item.is_use == true || item.is_use == 2 ? 'gray' : ''" v-else>商品券</span>
						<span>{{ item.title }}</span>
					</view>
					<view class="data acea-row row-between-wrapper">
						<view>{{ item.start_time ? item.start_time + '-' : '' }}{{ item.end_time }}</view>
						<view class="bnt gray" v-if="item.is_use == true">已领取</view>
						<view class="bnt gray" v-else-if="item.is_use == 2">已领完</view>
						<view class="bnt bg-color" v-else @click="getCoupon(item.id, index)">立即领取</view>
					</view>
				</view>
			</view>
		</view>
		<view class='loadingicon acea-row row-center-wrapper' v-if="couponsList.length">
			<text class='loading iconfont icon-jiazai' :hidden='loading==false'></text>{{loadTitle}}
		</view>
		<view class='noCommodity' v-else-if="!couponsList.length && page === 2">
			<view class='pictrue'>
				<image src='../../../static/images/noCoupon.png'></image>
			</view>
		</view>
		<!-- #ifdef MP -->
		<authorize @onLoadFun="onLoadFun" @authColse="authColse"></authorize>
		<!-- #endif -->
	</view>
</template>

<script>
	import {
		getCoupons,
		setCouponReceive
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
	export default {
		components: {
			// #ifdef MP
			authorize
			// #endif
		},
		data() {
			return {
				couponsList: [],
				loading: false,
				loadend: false,
				loadTitle: '加载更多', //提示语
				page: 1,
				limit: 20,
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false, //是否隐藏授权
				type: 0,
				navList: [{
						type: 0,
						name: '通用券',
						count: 0
					},
					{
						type: 1,
						name: '品类券',
						count: 0
					},
					{
						type: 2,
						name: '商品券',
						count: 0
					},
				],
				count: 0
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
		deep: true,

		onLoad() {
			if (this.isLogin) {
				// #ifdef H5
				this.getUseCoupons();
				// #endif
			} else {
				toLogin();
			}
		},
		/**
		 * 页面上拉触底事件的处理函数
		 */
		onReachBottom: function() {
			this.getUseCoupons();
		},
		methods: {
			onLoadFun() {
				this.getUseCoupons();
			},
			// 授权关闭
			authColse: function(e) {
				this.isShowAuth = e;
			},
			getCoupon: function(id, index) {
				let that = this;
				let list = that.couponsList;
				//领取优惠券
				setCouponReceive(id).then(function(res) {
					list[index].is_use = true;
					that.$set(that, 'couponsList', list);
					that.$util.Tips({
						title: '领取成功'
					});
				}).catch(error => {
					return that.$util.Tips({
						title: error
					});
				})
			},
			/**
			 * 获取领取优惠券列表
			 */
			getUseCoupons: function() {
				let that = this
				if (this.loadend) return false;
				if (this.loading) return false;
				that.loading = true;
				that.loadTitle = '加载更多';
				getCoupons({
					type: that.type,
					page: that.page,
					limit: that.limit
				}).then(res => {
					let list = res.data.list,
						loadend = list.length < that.limit;
					let couponsList = that.$util.SplitArray(list, that.couponsList);
					res.data.count.forEach((value, index) => {
						that.navList[index].count = value;
						if (value) {
							that.count++;
						}
					});
					that.$set(that, 'couponsList', couponsList);
					that.loadend = loadend;
					that.loading = false;
					that.loadTitle = loadend ? '我也是有底线的' : '加载更多';
					that.page = that.page + 1;
				}).catch(err => {
					that.loading = false;
					that.loadTitle = '加载更多';
				});
			},
			setType: function(type) {
				if (this.type !== type) {
					this.type = type;
					this.couponsList = [];
					this.page = 1;
					this.loadend = false;
					this.getUseCoupons();
				}
			}
		}
	};
</script>

<style scoped>
	.nav {
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 106rpx;
		background-color: #FFFFFF;
		font-size: 30rpx;
		color: #999999;
	}

	.nav .acea-row {
		border-top: 5rpx solid transparent;
		border-bottom: 5rpx solid transparent;
		cursor: pointer;
	}

	.nav .acea-row.on {
		border-bottom-color: #E93323;
		color: #282828;
	}

	.condition .line-title {
		width: 90rpx;
		padding: 0 10rpx;
		box-sizing: border-box;
		background: rgba(255, 247, 247, 1);
		border: 1px solid rgba(232, 51, 35, 1);
		opacity: 1;
		border-radius: 20rpx;
		font-size: 20rpx;
		color: #E83323;
		margin-right: 12rpx;
	}

	.condition .line-title.gray {
		border-color: #BBB;
		color: #bbb;
		background-color: #F5F5F5;
	}

	.coupon-list .pic-num {
		color: #FFFFFF;
		font-size: 24rpx;
	}

	.condition .line-title {
		width: 90rpx;
		padding: 0 10rpx;
		box-sizing: border-box;
		background: rgba(255, 247, 247, 1);
		border: 1px solid rgba(232, 51, 35, 1);
		opacity: 1;
		border-radius: 20rpx;
		font-size: 20rpx;
		color: #e83323;
		margin-right: 12rpx;
	}

	.condition .line-title.gray {
		border-color: #bbb;
		color: #bbb;
		background-color: #f5f5f5;
	}

	.coupon-list .pic-num {
		color: #ffffff;
		font-size: 24rpx;
	}
</style>
