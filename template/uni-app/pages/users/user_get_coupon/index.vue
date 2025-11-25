<template>
	<view :style="colorStyle">
		<view v-if="count > 1" class="acea-row row-around nav">
			<template v-for="item in navList">
				<view v-if="item.count" :key="item.type" :class="['acea-row', 'row-middle', type === item.type ? 'on' : '']" @click="setType(item.type)">
					{{ item.name }}
				</view>
			</template>
		</view>
		<view v-if="count > 1" style="height: 106rpx"></view>
		<view class="coupon-list" v-if="couponsList.length">
			<view class="item acea-row row-center-wrapper" v-for="(item, index) in couponsList" :key="index" :class="{ svip: item.receive_type === 4 }">
				<view class="moneyCon acea-row row-center-wrapper">
					<view class="money" :class="item.is_use >= item.receive_limit ? 'moneyGray' : ''">
						<view>
							{{ $t(`￥`) }}
							<text class="num">{{ item.coupon_price }}</text>
						</view>
						<view class="pic-num" v-if="item.use_min_price > 0">
							{{ $t(`满`) }} {{ item.use_min_price }}
							{{ $t(`元可用`) }}
						</view>
						<view class="pic-num" v-else>{{ $t(`无门槛券`) }}</view>
					</view>
				</view>
				<view class="text">
					<view class="condition">
						<view class="name line2">
							<view class="line-title" :class="item.is_use >= item.receive_limit ? 'bg-color-huic' : ''" v-if="item.type === 0">{{ $t(`通用劵`) }}</view>
							<view class="line-title" :class="item.is_use >= item.receive_limit ? 'bg-color-huic' : ''" v-else-if="item.type === 1">{{ $t(`品类券`) }}</view>
							<view class="line-title" :class="item.is_use >= item.receive_limit ? 'bg-color-huic' : ''" v-else>{{ $t(`商品券`) }}</view>
							<image v-if="item.receive_type === 4" class="pic" src="/static/images/fvip.png"></image>
							<text class="title">{{ $t(item.title) }}</text>
						</view>
					</view>
					<view class="data acea-row row-between-wrapper">
						<view v-if="item.coupon_time">{{ $t(`领取后`) }} {{ item.coupon_time }} {{ $t(`天内可用`) }}</view>
						<view v-else-if="item.start_use_time || item.end_use_time">{{ item.start_use_time ? item.start_use_time + '-' : '' }}{{ item.end_use_time }}</view>
						<view v-else></view>
						<view class="bnt gray" v-if="item.is_use >= item.receive_limit">{{ $t(`已领取`) }}</view>
						<view class="bnt gray" v-else-if="item.is_permanent == 0 && item.remain_count == 0">
							{{ $t(`已领完`) }}
						</view>
						<view class="bnt bg-color" v-else @click="getCoupon(item.id, item)">{{ $t(`立即领取`) }}</view>
					</view>
				</view>
			</view>
		</view>
		<view class="loadingicon acea-row row-center-wrapper" v-if="couponsList.length">
			<text class="loading iconfont icon-jiazai" :hidden="loading == false"></text>
			{{ loadTitle }}
		</view>
		<view class="noCommodity" v-else-if="!couponsList.length && page === 2">
			<view class="pictrue">
				<image :src="imgHost + '/statics/images/noCoupon.png'"></image>
			</view>
		</view>
		<!-- #ifdef MP -->
		<authorize @onLoadFun="onLoadFun" @authColse="authColse"></authorize>
		<!-- #endif -->
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</view>
</template>

<script>
import { getCoupons, setCouponReceive } from '@/api/api.js';
import { toLogin } from '@/libs/login.js';
import { mapGetters } from 'vuex';
// #ifdef MP
import authorize from '@/components/Authorize';
// #endif
import home from '@/components/home';
import colors from '@/mixins/color.js';
import { HTTP_REQUEST_URL } from '@/config/app';
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
			loadend: false,
			loadTitle: this.$t(`加载更多`), //提示语
			page: 1,
			limit: 20,
			isAuto: false, //没有授权的不会自动授权
			isShowAuth: false, //是否隐藏授权
			type: 0,
			navList: [
				{
					type: 0,
					name: this.$t(`通用券`),
					count: 0
				},
				{
					type: 1,
					name: this.$t(`品类券`),
					count: 0
				},
				{
					type: 2,
					name: this.$t(`商品券`),
					count: 0
				}
			],
			count: 0,
			receiveLoading: false
		};
	},
	computed: mapGetters(['isLogin']),
	watch: {
		isLogin: {
			handler: function (newV, oldV) {
				if (newV) {
					this.getUseCoupons();
				}
			},
			deep: true
		}
	},
	onLoad() {
		if (this.isLogin) {
			// #ifdef H5 || APP-PLUS
			this.getUseCoupons();
			// #endif
		} else {
			toLogin();
		}
	},
	/**
	 * 页面上拉触底事件的处理函数
	 */
	onReachBottom: function () {
		this.getUseCoupons();
	},
	methods: {
		onLoadFun() {
			this.getUseCoupons();
		},
		// 授权关闭
		authColse: function (e) {
			this.isShowAuth = e;
		},
		getCoupon: function (id, item) {
			if (this.receiveLoading) return;
			let that = this;
			this.receiveLoading = true;
			//领取优惠券
			setCouponReceive(id)
				.then((res) => {
					item.is_use += 1;
					that.$util.Tips({
						title: that.$t(`领取成功`)
					});
					setTimeout((e) => {
						that.receiveLoading = false;
					}, 500);
				})
				.catch((error) => {
					that.receiveLoading = false;
					return that.$util.Tips({
						title: error
					});
				});
		},
		/**
		 * 获取领取优惠券列表
		 */
		getUseCoupons: function () {
			let that = this;
			if (this.loadend) return false;
			if (this.loading) return false;
			that.loading = true;
			that.loadTitle = that.$t(`加载更多`);
			getCoupons({
				type: that.type,
				page: that.page,
				limit: that.limit
			})
				.then((res) => {
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
					that.loadTitle = loadend ? that.$t(`我也是有底线的`) : that.$t(`加载更多`);
					that.page = that.page + 1;
				})
				.catch((err) => {
					that.loading = false;
					that.loadTitle = that.$t(`加载更多`);
				});
		},
		setType: function (type) {
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

<style scoped lang="scss">
.nav {
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: 106rpx;
	background-color: #ffffff;
	font-size: 30rpx;
	color: #999999;
	z-index: 9;
}

.coupon-list .item .money .num {
	font-size: 48rpx;
}

.coupon-list .item .text .data {
	margin-top: 10rpx;
}

.coupon-list .item .text {
	padding-bottom: 12rpx;
}

.nav .acea-row {
	border-top: 5rpx solid transparent;
	border-bottom: 5rpx solid transparent;
}

.nav .acea-row.on {
	border-bottom-color: var(--view-theme);
	color: #282828;
}

.coupon-list .pic-num {
	color: #ffffff;
	font-size: 24rpx;
}

.coupon-list .item .text .condition {
	display: flex;
	align-items: center;
	.title {
		font-size: 26rpx;
		font-weight: 500;
		line-height: 30rpx;
	}
}

.coupon-list .item .text .condition .name {
	font-size: 28rpx;
	font-weight: 500;
	/* 
	display: flex;
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
	width: 90rpx;
	height: 40rpx !important;
	line-height: 40rpx;
	text-align: center;
	box-sizing: border-box;
	background: rgba(255, 247, 247, 1);
	border: 1rpx solid var(--view-theme);
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
	border-color: #bbb !important;
	color: #bbb !important;
	background-color: #f5f5f5 !important;
}
</style>
