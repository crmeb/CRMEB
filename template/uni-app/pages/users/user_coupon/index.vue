<template>
	<view :style="colorStyle">
		<view class="navbar acea-row row-around">
			<view class="item acea-row row-center-wrapper" :class="{ on: navOn === 1 }" @click="onNav(1)">{{$t(`未使用`)}}
			</view>
			<view class="item acea-row row-center-wrapper" :class="{ on: navOn === 2 }" @click="onNav(2)">
				{{$t(`已使用/过期`)}}</view>
		</view>
		<view class='coupon-list' v-if="couponsList.length">
			<view class='item acea-row row-center-wrapper' v-for='(item,index) in couponsList' :key="index"
				:class="{svip: item.receive_type === 4}" @click="useCoupon(item)">
				<view class="moneyCon acea-row row-center-wrapper">
					<view class='money' :class='item._type == 0 ? "moneyGray" : ""'>
						<view>{{$t(`￥`)}}<text class='num'>{{item.coupon_price}}</text></view>
						<view class="pic-num" v-if="item.use_min_price > 0">
							{{$t(`满`)}}{{item.use_min_price}}{{$t(`元可用`)}}</view>
						<view class="pic-num" v-else>{{$t(`无门槛券`)}}</view>
					</view>
				</view>
				<view class='text'>
					<view class='condition'>
						<view class="name line2">
							<view class="line-title" :class="item._type === 0 ? 'bg-color-huic' : 'bg-color-check'"
								v-if="item.applicable_type === 0">{{$t(`通用劵`)}}</view>
							<view class="line-title" :class="item._type === 0 ? 'bg-color-huic' : 'bg-color-check'"
								v-else-if="item.applicable_type === 1">{{$t(`品类券`)}}</view>
							<view class="line-title" :class="item._type === 0 ? 'bg-color-huic' : 'bg-color-check'"
								v-else>{{$t(`商品券`)}}</view>
							<image src="../../../static/images/fvip.png" class="pic" v-if="item.receive_type===4">
							</image>
							{{$t(item.coupon_title)}}
						</view>
					</view>
					<view class='data acea-row row-between-wrapper'>
						<view>{{item.add_time}}-{{item.end_time}}</view>
						<view class='bnt gray' v-if="item._type==0">{{$t(item._msg)}}</view>
						<view class='bnt bg-color' v-else>{{$t(item._msg)}}</view>
					</view>
				</view>
			</view>
		</view>
		<view class='noCommodity' v-if="!couponsList.length && page === 2">
			<view class='pictrue'>
				<image :src="imgHost + '/statics/images/noCoupon.png'"></image>
			</view>
		</view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
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
	import colors from '@/mixins/color.js';
	import {
		HTTP_REQUEST_URL
	} from '@/config/app';
	export default {
		components: {
			// #ifdef MP
			authorize,
			// #endif
			home
		},
		mixins: [colors],
		data() {
			return {
				imgHost: HTTP_REQUEST_URL,
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
			useCoupon(item) {
				let url = '';
				if (item.category_id == 0 && item.product_id == '') {
					url = '/pages/goods/goods_list/index?title=默认'
				}
				if (item.category_id != 0) {
					url = `/pages/goods/goods_list/index?title=${item.coupon_title}&coupon_category_id=${item.category_id}`
				}
				if (item.product_id != '') {
					let arr = item.product_id.split(',');
					let num = arr.length;
					if (num == 1) {
						url = '/pages/goods_details/index?id=' + item.product_id
					} else {
						url = '/pages/goods/goods_list/index?productId=' + item.product_id + '&title=默认'
					}
				}
				uni.navigateTo({
					url: url
				});
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
					title: that.$t(`正在加载…`)
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
		font-size: 24rpx;
		font-weight: 500;
		line-height: 28rpx;
		/* display: flex;
		align-items: center; */
	}

	.coupon-list .item .text .condition .pic {
		width: 30rpx;
		height: 30rpx;
		display: block;
		margin-right: 10rpx;
		display: inline-block;
		vertical-align: middle;
	}
	.condition .line-title {
		/* width: 70rpx; */
		height: 36rpx !important;
		padding: 0 5px;
		line-height: 32rpx;
		text-align: center;
		box-sizing: border-box;
		background: rgba(255, 247, 247, 1);
		border: 1px solid var(--view-theme);
		opacity: 1;
		border-radius: 20rpx;
		font-size: 18rpx !important;
		color: var(--view-theme);
		margin-right: 12rpx;
		text-align: center;
		display: inline-block;
		vertical-align: middle;
	}

	.condition .line-title.bg-color-huic {
		border-color: #BBB !important;
		color: #bbb !important;
		background-color: #F5F5F5 !important;
	}
</style>

<style lang="scss" scoped>
	.navbar {
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 90rpx;
		background-color: #FFFFFF;
		z-index: 9;

		.item {
			border-top: 5rpx solid transparent;
			border-bottom: 5rpx solid transparent;
			font-size: 30rpx;
			color: #999999;

			&.on {
				border-bottom-color: var(--view-theme);
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
